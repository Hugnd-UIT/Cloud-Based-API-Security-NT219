Write-Host "Unsealing Vault..." -ForegroundColor Yellow

docker exec payshield_vault vault operator unseal -reset '-ca-cert=/vault/certs/ca.crt' '-client-cert=/vault/certs/vault.crt' '-client-key=/vault/certs/vault.key'

docker exec payshield_vault vault operator unseal '-ca-cert=/vault/certs/ca.crt' '-client-cert=/vault/certs/vault.crt' '-client-key=/vault/certs/vault.key' lzKSZCVAcUJPetWgsAuCOI3GBQj7CAhxNk/PsAmHcHii
docker exec payshield_vault vault operator unseal '-ca-cert=/vault/certs/ca.crt' '-client-cert=/vault/certs/vault.crt' '-client-key=/vault/certs/vault.key' F9gZ4tHXpOinWl0TZkm6gNntjlyvibYJqfyZQuxk0YYs
docker exec payshield_vault vault operator unseal '-ca-cert=/vault/certs/ca.crt' '-client-cert=/vault/certs/vault.crt' '-client-key=/vault/certs/vault.key' rzIHBFwOBmuEtJgCO4qYL1VegNn5NOyfrLsL5PsnC3xU
docker exec payshield_vault vault operator unseal '-ca-cert=/vault/certs/ca.crt' '-client-cert=/vault/certs/vault.crt' '-client-key=/vault/certs/vault.key' 1fPY1hB1/SA2En0I48uZ4yagHWcf9yQzBr7OpM1bhcaH
docker exec payshield_vault vault operator unseal '-ca-cert=/vault/certs/ca.crt' '-client-cert=/vault/certs/vault.crt' '-client-key=/vault/certs/vault.key' Dhc2mGDQ2jMVTX48F1f/SXsOyLjiQwh83DE+urkOL7B+

Write-Host "Success!" -ForegroundColor Green