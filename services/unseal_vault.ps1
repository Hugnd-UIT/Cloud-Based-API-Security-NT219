$Key1 = "TOVQkxwi0o7ucmnKz0YiNFGjiSI2h4j4QgGBjKGJFZ82"
$Key2 = "AbdNnjDvPni/EU+nL1anAjgAR9MS5m51j2jm8m8xmGTf"
$Key3 = "s3zoceSj/z+NTdKE94l8iL8T4cnXKcj67RcoLIFpGJRA"

Write-Host "Automatically unsealing Vault..." -ForegroundColor Cyan

docker exec -it payshield_vault vault operator unseal $Key1
docker exec -it payshield_vault vault operator unseal $Key2
docker exec -it payshield_vault vault operator unseal $Key3

Write-Host "Vault unsealed successfully!" -ForegroundColor Green