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

Write-Host "[*] Phase 1: Generating assets and starting Vault..." -ForegroundColor Yellow
(Get-Content $FILE) -replace '"tls_require_and_verify_client_cert":\s*true', '"tls_require_and_verify_client_cert": false' | Set-Content $FILE

openssl genrsa -out ca.key 4096

openssl req -x509 -new -nodes -key ca.key -sha256 -days 3650 -out ca.crt -subj "/CN=PayShield Root CA v2"

docker-compose -f $FILE up -d --force-recreate vault

while ($true) {
    $check = docker exec -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_SKIP_VERIFY="true" payshield_vault vault status 2>&1
    if ($check -match "Sealed" -or $check -match "Unseal Progress" -or $check -match "initialized: true") { break }
    Start-Sleep -Seconds 2
}

foreach ($key in $UnsealKeys) {
    docker exec -it -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_SKIP_VERIFY="true" payshield_vault vault operator unseal $key
}

Write-Host "[*] Phase 2: Updating Vault PKI..." -ForegroundColor Cyan

docker exec -it -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_SKIP_VERIFY="true" -e VAULT_TOKEN="$RootToken" payshield_vault vault secrets disable pki
docker exec -it -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_SKIP_VERIFY="true" -e VAULT_TOKEN="$RootToken" payshield_vault vault secrets enable pki
docker exec -it -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_SKIP_VERIFY="true" -e VAULT_TOKEN="$RootToken" payshield_vault vault secrets tune -max-lease-ttl=87600h pki

Get-Content ca.crt, ca.key | Out-File -FilePath bundle.pem -Encoding ascii

docker cp bundle.pem payshield_vault:/vault/certs/bundle.pem

$ImportResult = docker exec -it -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_SKIP_VERIFY="true" -e VAULT_TOKEN="$RootToken" payshield_vault vault write -format=json pki/config/ca pem_bundle=@/vault/certs/bundle.pem | ConvertFrom-Json
$IssuerID = if ($null -eq $ImportResult.imported_issuers) { (docker exec -it -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_SKIP_VERIFY="true" -e VAULT_TOKEN="$RootToken" payshield_vault vault list -format=json pki/issuers | ConvertFrom-Json)[0] } else { $ImportResult.imported_issuers[0] }

docker exec -it -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_SKIP_VERIFY="true" -e VAULT_TOKEN="$RootToken" payshield_vault vault write pki/config/issuers default="$IssuerID"
docker exec -it -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_SKIP_VERIFY="true" -e VAULT_TOKEN="$RootToken" payshield_vault vault write pki/roles/payshield-role issuer_ref="$IssuerID" allow_any_name=true enforce_hostnames=false client_flag=true server_flag=true ttl="8760h"

Write-Host "[*] Phase 3: Harvesting artifacts and preparing Truststore..." -ForegroundColor Magenta
docker-compose -f $FILE up -d --force-recreate vault-agent-controller
Start-Sleep -Seconds 10 

docker cp payshield_vault_agent:/tmpfs/certs/client/server.crt ./client.crt
docker cp payshield_vault_agent:/tmpfs/certs/client/server.key ./client.key

Remove-Item truststore.p12 -ErrorAction SilentlyContinue

keytool -importcert -file ca.crt -alias RootCA -keystore truststore.p12 -storetype PKCS12 -storepass "$PASS" -noprompt

openssl pkcs12 -export -out client.p12 -inkey client.key -in client.crt -passout "pass:$PASS" -name "PayShield-Client-v2"

Write-Host "[*] Phase 4: Enforcing Zero-Trust and refreshing Keycloak..." -ForegroundColor Yellow

(Get-Content $FILE) -replace '"tls_require_and_verify_client_cert":\s*false', '"tls_require_and_verify_client_cert": true' | Set-Content $FILE

$TargetServices = (docker-compose -f $FILE config --services) | Where-Object { $_ -ne "vault" -and $_ -ne "vault-agent-controller" } 
docker-compose -f $FILE up -d --force-recreate $TargetServices

Remove-Item vault.cnf, vault.csr, bundle.pem, ca.srl -ErrorAction SilentlyContinue

Write-Host "`n=== ROTATION FINISHED ===" -ForegroundColor Green -BackgroundColor Black