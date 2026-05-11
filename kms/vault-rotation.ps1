param (
    [string]$EnvFilePath = "../services/.env",
    [string]$ComposeFilePath = "../services/docker-compose.yml",
    [string]$KeystorePassword = "password"
)

if (Test-Path $EnvFilePath) {
    foreach ($ConfigLine in Get-Content $EnvFilePath) {
        if ($ConfigLine -match "^(?<name>[^=]+)=(?<value>.+)$") {
            Set-Variable -Name "ENV_$($Matches['name'].Trim())" -Value $Matches['value'].Trim()
        }
    }
}

$RootToken = $ENV_VAULT_APP_TOKEN
$UnsealKeys = @($ENV_VAULT_KEY1, $ENV_VAULT_KEY2, $ENV_VAULT_KEY3)

Write-Host "Generating new cert and key..." -ForegroundColor Cyan

openssl ecparam -name prime256v1 -genkey -noout -out ca.key
openssl req -x509 -new -nodes -key ca.key -sha256 -days 3650 -out ca.crt -subj "/CN=PayShield Root CA"

openssl ecparam -name prime256v1 -genkey -noout -out vault.key
openssl req -new -key vault.key -out vault.csr -subj "/CN=payshield_vault"
Set-Content -Path tmp.ext -Value "subjectAltName=IP:127.0.0.1,DNS:payshield_vault,DNS:localhost"
openssl x509 -req -in vault.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out vault.crt -days 3650 -sha256 -extfile tmp.ext

Write-Host "Recreating Vault..." -ForegroundColor Cyan
docker-compose -f $ComposeFilePath up -d --force-recreate vault

$VaultTlsEnvironment = @(
    "-e", "VAULT_ADDR=https://127.0.0.1:8200",
    "-e", "VAULT_CACERT=/vault/certs/ca.crt",
    "-e", "VAULT_CLIENT_CERT=/vault/certs/vault.crt",
    "-e", "VAULT_CLIENT_KEY=/vault/certs/vault.key"
)

Write-Host "Waiting for Vault to wake up..." -ForegroundColor Yellow
while ($true) {
    $VaultStatusCheck = docker exec $VaultTlsEnvironment payshield_vault vault status 2>&1
    if ($VaultStatusCheck -match "Sealed" -or $VaultStatusCheck -match "Unseal Progress" -or $VaultStatusCheck -match "initialized: true") { break }
    Start-Sleep -Seconds 2
}

Write-Host "Unsealing Vault..." -ForegroundColor Cyan
foreach ($UnsealKey in $UnsealKeys) {
    docker exec $VaultTlsEnvironment payshield_vault vault operator unseal $UnsealKey
}

Write-Host "Configuring PKI Secrets Engine..." -ForegroundColor Cyan
docker exec $VaultTlsEnvironment -e "VAULT_TOKEN=$RootToken" payshield_vault vault secrets disable pki
docker exec $VaultTlsEnvironment -e "VAULT_TOKEN=$RootToken" payshield_vault vault secrets enable pki
docker exec $VaultTlsEnvironment -e "VAULT_TOKEN=$RootToken" payshield_vault vault secrets tune -max-lease-ttl=87600h pki

Get-Content ca.crt, ca.key | Out-File -FilePath bundle.pem -Encoding ascii

$ImportResult = docker exec $VaultTlsEnvironment -e "VAULT_TOKEN=$RootToken" payshield_vault vault write -format=json pki/config/ca pem_bundle=@/vault/certs/bundle.pem | ConvertFrom-Json
$IssuerId = if ($null -eq $ImportResult.imported_issuers) { (docker exec $VaultTlsEnvironment -e "VAULT_TOKEN=$RootToken" payshield_vault vault list -format=json pki/issuers | ConvertFrom-Json)[0] } else { $ImportResult.imported_issuers[0] }

docker exec $VaultTlsEnvironment -e "VAULT_TOKEN=$RootToken" payshield_vault vault write pki/config/issuers default="$IssuerId"
docker exec $VaultTlsEnvironment -e "VAULT_TOKEN=$RootToken" payshield_vault vault write pki/roles/payshield-role issuer_ref="$IssuerId" allow_any_name=true enforce_hostnames=false client_flag=true server_flag=true ttl="8760h" key_type="ec" key_bits="256"

Write-Host "Restarting internal services..." -ForegroundColor Cyan
docker-compose -f $ComposeFilePath up -d --force-recreate vault-agent-controller
Start-Sleep -Seconds 10 

docker cp payshield_vault_agent:/tmpfs/certs/client/server.crt ./client.crt
docker cp payshield_vault_agent:/tmpfs/certs/client/server.key ./client.key

Remove-Item truststore.p12 -ErrorAction SilentlyContinue
keytool -importcert -file ca.crt -alias RootCA -keystore truststore.p12 -storetype PKCS12 -storepass "$KeystorePassword" -noprompt
openssl pkcs12 -export -out client.p12 -inkey client.key -in client.crt -certfile ca.crt -passout "pass:$KeystorePassword" -name "PayShield-Client-v2"

$TargetServices = (docker-compose -f $ComposeFilePath config --services) | Where-Object { $_ -ne "vault" -and $_ -ne "vault-agent-controller" } 
docker-compose -f $ComposeFilePath up -d --force-recreate $TargetServices

Remove-Item vault.csr, bundle.pem, ca.srl, tmp.ext -ErrorAction SilentlyContinue

Write-Host "`n=== ROTATION FINISHED ===" -ForegroundColor Green -BackgroundColor Black