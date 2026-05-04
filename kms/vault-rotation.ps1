param (
    [string]$ENV = "../services/.env",
    [string]$FILE = "../services/docker-compose.yml",
    [string]$PASS = "password"
)

if (Test-Path $ENV) {
    foreach ($line in Get-Content $ENV) {
        if ($line -match "^(?<name>[^=]+)=(?<value>.+)$") {
            Set-Variable -Name "ENV_$($Matches['name'].Trim())" -Value $Matches['value'].Trim()
        }
    }
}

$RootToken = $ENV_VAULT_APP_TOKEN
$UnsealKeys = @($ENV_VAULT_KEY1, $ENV_VAULT_KEY2, $ENV_VAULT_KEY3)

Write-Host "Generating new cert and key..." -ForegroundColor Cyan

openssl genrsa -out ca.key 4096
openssl req -x509 -new -nodes -key ca.key -sha256 -days 3650 -out ca.crt -subj "/CN=PayShield Root CA v2"

openssl genrsa -out vault.key 2048
openssl req -new -key vault.key -out vault.csr -subj "/CN=payshield_vault"
Set-Content -Path tmp.ext -Value "subjectAltName=IP:127.0.0.1,DNS:payshield_vault,DNS:localhost"
openssl x509 -req -in vault.csr -CA ca.crt -CAkey ca.key -CAcreateserial -out vault.crt -days 3650 -sha256 -extfile tmp.ext

Write-Host "Recreating Vault..." -ForegroundColor Cyan
docker-compose -f $FILE up -d --force-recreate vault

$tls_env = @(
    "-e", "VAULT_ADDR=https://127.0.0.1:8200",
    "-e", "VAULT_CACERT=/vault/certs/ca.crt",
    "-e", "VAULT_CLIENT_CERT=/vault/certs/vault.crt",
    "-e", "VAULT_CLIENT_KEY=/vault/certs/vault.key"
)

Write-Host "Waiting for Vault to wake up..." -ForegroundColor Yellow
while ($true) {
    $check = docker exec $tls_env payshield_vault vault status 2>&1
    if ($check -match "Sealed" -or $check -match "Unseal Progress" -or $check -match "initialized: true") { break }
    Start-Sleep -Seconds 2
}

Write-Host "Unsealing Vault..." -ForegroundColor Cyan
foreach ($key in $UnsealKeys) {
    docker exec $tls_env payshield_vault vault operator unseal $key
}

Write-Host "Configuring PKI Secrets Engine..." -ForegroundColor Cyan
docker exec $tls_env -e "VAULT_TOKEN=$RootToken" payshield_vault vault secrets disable pki
docker exec $tls_env -e "VAULT_TOKEN=$RootToken" payshield_vault vault secrets enable pki
docker exec $tls_env -e "VAULT_TOKEN=$RootToken" payshield_vault vault secrets tune -max-lease-ttl=87600h pki

Get-Content ca.crt, ca.key | Out-File -FilePath bundle.pem -Encoding ascii
docker cp bundle.pem payshield_vault:/vault/certs/bundle.pem

$ImportResult = docker exec $tls_env -e "VAULT_TOKEN=$RootToken" payshield_vault vault write -format=json pki/config/ca pem_bundle=@/vault/certs/bundle.pem | ConvertFrom-Json
$IssuerID = if ($null -eq $ImportResult.imported_issuers) { (docker exec $tls_env -e "VAULT_TOKEN=$RootToken" payshield_vault vault list -format=json pki/issuers | ConvertFrom-Json)[0] } else { $ImportResult.imported_issuers[0] }

docker exec $tls_env -e "VAULT_TOKEN=$RootToken" payshield_vault vault write pki/config/issuers default="$IssuerID"
docker exec $tls_env -e "VAULT_TOKEN=$RootToken" payshield_vault vault write pki/roles/payshield-role issuer_ref="$IssuerID" allow_any_name=true enforce_hostnames=false client_flag=true server_flag=true ttl="8760h"

Write-Host "Restarting internal services..." -ForegroundColor Cyan
docker-compose -f $FILE up -d --force-recreate vault-agent-controller
Start-Sleep -Seconds 10 

docker cp payshield_vault_agent:/tmpfs/certs/client/server.crt ./client.crt
docker cp payshield_vault_agent:/tmpfs/certs/client/server.key ./client.key

Remove-Item truststore.p12 -ErrorAction SilentlyContinue
keytool -importcert -file ca.crt -alias RootCA -keystore truststore.p12 -storetype PKCS12 -storepass "$PASS" -noprompt
openssl pkcs12 -export -out client.p12 -inkey client.key -in client.crt -passout "pass:$PASS" -name "PayShield-Client-v2"

$TargetServices = (docker-compose -f $FILE config --services) | Where-Object { $_ -ne "vault" -and $_ -ne "vault-agent-controller" } 
docker-compose -f $FILE up -d --force-recreate $TargetServices

Remove-Item vault.csr, bundle.pem, ca.srl, tmp.ext -ErrorAction SilentlyContinue

Write-Host "`n=== ROTATION FINISHED ===" -ForegroundColor Green -BackgroundColor Black