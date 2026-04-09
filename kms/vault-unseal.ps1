Write-Host ">>> Reseting... <<<" -ForegroundColor Yellow

docker exec -it payshield_vault vault operator unseal -ca-cert=/vault/certs/ca.crt -reset

docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt 'lzKSZCVAcUJPetWgsAuCOI3GBQj7CAhxNk/PsAmHcHii'"
docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt 'F9gZ4tHXpOinWl0TZkm6gNntjlyvibYJqfyZQuxk0YYs'"
docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt 'rzIHBFwOBmuEtJgCO4qYL1VegNn5NOyfrLsL5PsnC3xU'"
docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt '1fPY1hB1/SA2En0I48uZ4yagHWcf9yQzBr7OpM1bhcaH'"
docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt 'Dhc2mGDQ2jMVTX48F1f/SXsOyLjiQwh83DE+urkOL7B+'"
docker exec -it payshield_vault vault status -ca-cert=/vault/certs/ca.crt

Write-Host ">>> Vault Unsealed Successfully! <<<" -ForegroundColor Green