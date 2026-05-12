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
    payload = f"{method}|{uri}|{ts}|{nonce}|{token}"
    return hmac.new(secret.encode(), payload.encode(), hashlib.sha256).hexdigest()

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
        response = requests.request(method, url, headers=headers, json=json_data, cert=CLIENT, verify=False, timeout=20)
        print(f"Status: {response.status_code}")
        print(f"Response: {response.text[:150]}" + ("..." if len(response.text) > 150 else ""))
    except requests.exceptions.SSLError as e:
        print(f"❌ SSL Error: {e}")
    except Exception as e:
        print(f"❌ Error: {e}")

# SCENARIO 1: VALID ACCESS 
send(
    label="SCENARIO 1: VALID ACCESS", 
    method="GET", 
    uri="/api/profile"
)

# SCENARIO 2: ABAC - TIME CONSTRAINT
send(
    label="SCENARIO 2: ABAC CONSTRAINT", 
    method="GET", 
    uri="/api/salary"
)

# SCENARIO 3: HORIZONTAL BOLA / PRIVILEGE ESCALATION
send(
    label="SCENARIO 3: ESCALATION", 
    method="GET", 
    uri="/api/dashboard/manager-data"
)

# SCENARIO 4: VERTICAL BOLA
send(
    label="SCENARIO 4: VERTICAL BOLA", 
    method="DELETE", 
    uri="/api/salary"
)