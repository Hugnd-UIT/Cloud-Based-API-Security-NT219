Write-Host ">>> Reseting... <<<" -ForegroundColor Yellow

docker exec -it payshield_vault vault operator unseal -ca-cert=/vault/certs/ca.crt -reset

docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt '6VAzjVmCfZSUoLiPF4u6d8EG8S/p6z3U6RIY+8uFUknI'"
docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt 'cDEQYawiYYD/oqShIOJAtSemgarCKMEgmAjs+Zfh3WPt'"
docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt '2EyotQ9LN2QorbSQFC65jH59ThL94m8uIW1HxIClO18h'"
docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt '8W07WWudkXahJA59J1VLmM8xZIAubcywfYIMqaWIHvyk'"
docker exec -it payshield_vault sh -c "vault operator unseal -ca-cert=/vault/certs/ca.crt 'aL486Bfn3D3h2LyJFiDx/jf/7+IktQ/orlghVogGc808'"
docker exec -it payshield_vault vault status -ca-cert=/vault/certs/ca.crt

Write-Host ">>> Vault Unsealed Successfully! <<<" -ForegroundColor Green