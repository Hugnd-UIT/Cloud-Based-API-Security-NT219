Write-Host "===========================================================" -ForegroundColor Magenta
Write-Host "               Encryption Algorithm Security               " -ForegroundColor Magenta
Write-Host "===========================================================" -ForegroundColor Magenta

Write-Host "`n[+] Issue mTLS Certificate and Key" -ForegroundColor Green
docker exec -i payshield_vault_agent sh -c "find /tmpfs/certs -type f -name 'server.*' | sort" | ForEach-Object { 
    if ($_ -match "\.key$") {
        Write-Host "    - Private Key : $_" -ForegroundColor DarkYellow 
    } else {
        Write-Host "    - Certificate : $_" -ForegroundColor DarkGreen 
    }
}

Write-Host "`n[+] WAF <-> Kong" -ForegroundColor Yellow
docker exec -i payshield_waf sh -c "echo 'Q' | openssl s_client -connect payshield_kong:8443 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /kms/ca.crt 2>/dev/null | grep -E 'Cipher|Verify return code'"

Write-Host "`n[+] Kong <-> Proxy" -ForegroundColor Yellow
docker exec -i payshield_kong sh -c "echo 'Q' | openssl s_client -connect payshield_proxy:8443 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /kms/ca.crt 2>/dev/null | grep -E 'Cipher|Verify return code'"

Write-Host "`n[+] Proxy <-> App" -ForegroundColor Yellow
docker exec -i payshield_proxy sh -c "echo 'Q' | openssl s_client -connect payshield_app:443 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /kms/ca.crt 2>/dev/null | grep -E 'Cipher|Verify return code'"

Write-Host "`n[+] App <-> Database" -ForegroundColor Yellow
docker exec -i payshield_app sh -c "echo 'Q' | openssl s_client -starttls mysql -connect payshield_db:3306 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /kms/ca.crt 2>/dev/null | grep -E 'Cipher|Verify return code'"

Write-Host "`n[+] Kong <-> OPA" -ForegroundColor Yellow
docker exec -i payshield_kong sh -c "echo 'Q' | openssl s_client -connect payshield_opa:8181 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /kms/ca.crt 2>/dev/null | grep -m 2 -E 'Cipher|Verify return code'"

Write-Host "`n[+] Filebeat -> SIEM" -ForegroundColor Yellow
docker exec -i payshield_filebeat sh -c "echo 'Q' | openssl s_client -connect payshield_logstash:5044 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /etc/certs/ca.crt 2>/dev/null | grep -E 'Cipher|Verify return code'"

Write-Host "`n======================== Completed =========================" -ForegroundColor Magenta