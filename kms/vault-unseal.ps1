Write-Host "--- Loading Environment Variables from services folder ---" -ForegroundColor Gray
$envPath = "../services/.env" 

if (Test-Path $envPath) {
    Get-Content $envPath | Where-Object { $_ -match '=' -and $_ -notmatch '^#' } | ForEach-Object {
        $name, $value = $_.Split('=', 2)
        [System.Environment]::SetEnvironmentVariable($name.Trim(), $value.Trim(), "Process")
    }
} else {
    Write-Error "File .env không tìm thấy tại: $envPath ! Vui lòng kiểm tra lại."
    exit
}

$common_flags = @(
    "-ca-cert=/vault/certs/ca.crt",
    "-client-cert=/vault/certs/vault.crt",
    "-client-key=/vault/certs/vault.key"
)

Write-Host "Unsealing Vault..." -ForegroundColor Yellow

docker exec payshield_vault vault operator unseal $common_flags $env:VAULT_KEY1
docker exec payshield_vault vault operator unseal $common_flags $env:VAULT_KEY2
docker exec payshield_vault vault operator unseal $common_flags $env:VAULT_KEY3
docker exec payshield_vault vault operator unseal $common_flags $env:VAULT_KEY4
docker exec payshield_vault vault operator unseal $common_flags $env:VAULT_KEY5

Write-Host "--- Success! Vault is open. ---" -ForegroundColor Green