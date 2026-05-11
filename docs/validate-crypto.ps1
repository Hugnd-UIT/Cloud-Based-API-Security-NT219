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

function Get-OpenSSLValue {
    param([string[]]$Lines, [string]$Pattern)
    foreach ($Line in $Lines) {
        if ($Line -match $Pattern) { return $Matches[1].Trim() }
    }
    return "N/A"
}

function Check-mTLS {
    param(
        [string]$Title,
        [string]$Container,
        [string]$Target,
        [string]$CAFile = "/kms/ca.crt"
    )
    
    Write-Host "`n[+] $Title" -ForegroundColor Yellow
    
    $Command = "echo 'Q' | openssl s_client -connect $Target -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile $CAFile 2>/dev/null"
    
    if ($Target -match ":3306$") {
        $Command = $Command -replace "-connect", "-starttls mysql -connect"
    }

    $Output = docker exec -i $Container sh -c $Command
    
    $Signature    = Get-OpenSSLValue -Lines $Output -Pattern "signature type:\s*(.+)"
    $CipherSuite  = Get-OpenSSLValue -Lines $Output -Pattern "Cipher is\s*([A-Z0-9_]+)"
    $Verification = Get-OpenSSLValue -Lines $Output -Pattern "Verify return code:\s*(.+)"
    $KeyExchange  = Get-OpenSSLValue -Lines $Output -Pattern "Temp Key:\s*(.+)"

    if ($KeyExchange -eq "N/A") {
        $FallbackCommand = $Command -replace "s_client", "s_client -tls1_2"
        $FallbackOutput = docker exec -i $Container sh -c $FallbackCommand
        $KeyExchange = Get-OpenSSLValue -Lines $FallbackOutput -Pattern "Temp Key:\s*(.+)"
    } elseif ($KeyExchange -match "X25519") { 
        $KeyExchange = "X25519 (253 bits)" 
    } elseif ($KeyExchange -match "prime256v1|P-256") { 
        $KeyExchange = "ECDH (256 bits)" 
    }

    if ($Signature -match "ecdsa|ECDSA") { $Signature = "ECDSA (P-256)" }

    Write-Host "    - Signature    : $Signature" -ForegroundColor Cyan
    Write-Host "    - Key Exchange : $KeyExchange" -ForegroundColor Cyan
    Write-Host "    - Cipher Suite : $CipherSuite" -ForegroundColor Cyan
    
    if ($Verification -match "0 \(ok\)") {
        Write-Host "    - Verification : $Verification" -ForegroundColor Green
    } else {
        Write-Host "    - Verification : $Verification" -ForegroundColor Red
    }
}

Check-mTLS -Title "WAF <-> Kong" -Container "payshield_waf" -Target "payshield_kong:8443"
Check-mTLS -Title "Kong <-> Proxy" -Container "payshield_kong" -Target "payshield_proxy:8443"
Check-mTLS -Title "Proxy <-> App" -Container "payshield_proxy" -Target "payshield_app:443"
Check-mTLS -Title "App <-> Database" -Container "payshield_app" -Target "payshield_db:3306"
Check-mTLS -Title "App <-> Keycloak" -Container "payshield_app" -Target "payshield_keycloak:8443"
Check-mTLS -Title "Kong <-> OPA" -Container "payshield_kong" -Target "payshield_opa:8181"
Check-mTLS -Title "Filebeat -> SIEM" -Container "payshield_filebeat" -Target "payshield_logstash:5044" -CAFile "/etc/certs/ca.crt"

Write-Host "`n======================== Completed =========================" -ForegroundColor Magenta