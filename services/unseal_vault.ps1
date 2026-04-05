$Key1 = "3nQ77WINkGel4vckD/cKuU4yLMKQZo7Ozlvjm/eud17/"
$Key2 = "8/fC2Lq71N4x+/8Kaa9wNAM2/gfCantwSt3Ew7Mv2bPe"
$Key3 = "dc0ophQEL4u2uhB+0u79jFVEOMjiiR1helpx6NxZRSkN"

Write-Host "Dang tu dong mo ket sat Vault..." -ForegroundColor Cyan

docker exec -it payshield_vault vault operator unseal $Key1
docker exec -it payshield_vault vault operator unseal $Key2
docker exec -it payshield_vault vault operator unseal $Key3

Write-Host "Ket da mo !" -ForegroundColor Green