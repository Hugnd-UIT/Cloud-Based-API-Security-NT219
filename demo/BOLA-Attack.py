import requests
import time
import uuid
import hmac
import hashlib
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# --- CONFIGURATION ---
BASE_URL = "https://localhost:8888"

TOKEN = "eyJhbGciOiJFUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJqbm1MNU1CQ3ZXelR2V2xaVFo0S0hXU0gwV2V5ZmhzaUduLVUwSkNmdWN3In0.eyJleHAiOjE3ODEwOTE3NTMsImlhdCI6MTc4MTA5MTQ1MywiYXV0aF90aW1lIjoxNzgxMDkxNDA0LCJqdGkiOiI0OGRlOTgzYi0zMDI5LTRhNWEtYTZkYy0wZTVhMDhjNzVkYTciLCJpc3MiOiJodHRwczovL3BheXNoaWVsZC5kdWNrZG5zLm9yZzo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJhdWQiOiJhY2NvdW50Iiwic3ViIjoiMzY4NmEwYTEtNGQ5NS00ZTEwLWI5ZGUtNzQ2ZTYxYmUwNGZhIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoicGF5c2hpZWxkLWFwcCIsInNlc3Npb25fc3RhdGUiOiIzMDUzMDVhZi02NmY4LTQxZjQtOTBkOS0zNDUwZDI1MGY1NTgiLCJhY3IiOiIwIiwiYWxsb3dlZC1vcmlnaW5zIjpbIioiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbIm9mZmxpbmVfYWNjZXNzIiwiZGVmYXVsdC1yb2xlcy1wYXlzaGllbGQtcmVhbG0iLCJ1bWFfYXV0aG9yaXphdGlvbiIsImVtcGxveWVlIl19LCJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCIsInNpZCI6IjMwNTMwNWFmLTY2ZjgtNDFmNC05MGQ5LTM0NTBkMjUwZjU1OCIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJlbXBsb3llZS1leGFtcGxlQHBheXNoaWVsZC5jb20iLCJlbWFpbCI6ImVtcGxveWVlLWV4YW1wbGVAcGF5c2hpZWxkLmNvbSJ9.PFJDtZJtfjBVjjhYtHdy0ZciXLaqafVjz9mBh_-m9vSYpDwUOu8pAYuApRDzNC1F61IqZjaHE8BMcBTm_-5Zyw"

SECRET = "gTPf9LYwK20ngQcRSd9mTiCxmXLKze7n" 

def payload(method, uri, ts, nonce, token, secret):
    payload_str = f"{method}|{uri}|{ts}|{nonce}|{token}"
    return hmac.new(secret.encode(), payload_str.encode(), hashlib.sha256).hexdigest()

def send(label, method, uri, json_data=None):
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
        response = requests.request(method, url, headers=headers, json=json_data, verify=False, timeout=10)
        print(f"Status: {response.status_code}")
        preview = response.text[:200] + ("..." if len(response.text) > 200 else "")
        print(f"Response: {preview}")
        
    except requests.exceptions.SSLError as e:
        print(f"❌ SSL Error: mTLS bị từ chối - {e}")
    except Exception as e:
        print(f"❌ Error: {e}")

# SCENARIO 1: VALID ACCESS 
send(
    label="SCENARIO 1: VALID ACCESS (NV001 xem lương của chính mình)", 
    method="GET", 
    uri="/api/salary"
)

# SCENARIO 2: HORIZONTAL BOLA 
send(
    label="SCENARIO 2: HORIZONTAL BOLA (NV001 cố xem lương của NV002)", 
    method="GET", 
    uri="/api/salary?manv=NV002"
)

# SCENARIO 3: VERTICAL BOLA 
send(
    label="SCENARIO 3: VERTICAL BOLA / ESCALATION (NV001 truy cập Dashboard Sếp)", 
    method="GET", 
    uri="/api/dashboard/manager-data"
)

# SCENARIO 4: ACTION ESCALATION 
malicious_payload = {
    "CHUCVU": "Manager",
    "TIENLUONGCB": 50000000
}
send(
    label="SCENARIO 4: ACTION ESCALATION (NV001 cố tình sửa chức vụ/lương)", 
    method="PUT", 
    uri="/api/employees/NV001",
    json_data=malicious_payload
)