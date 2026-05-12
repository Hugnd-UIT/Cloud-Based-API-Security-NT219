import requests
import time
import uuid
import hmac
import hashlib
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# --- CONFIGURATION ---
URL = "https://localhost:8888/api/dashboard/manager-data"
URI = "/api/dashboard/manager-data"
# Thay token vào đây
TOKEN = "eyJhbGciOiJFUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICIwSGtPQy1TVE1pSE44aGlTZkhBU1p5Z1pUbHpLQXVyMmU0S3VSb291VW5BIn0.eyJleHAiOjE3Nzg1NjQ4OTgsImlhdCI6MTc3ODU2NDU5OCwiYXV0aF90aW1lIjoxNzc4NTY0MTYyLCJqdGkiOiI5MTgzZmRhZC04MjA4LTQyNDEtODQ1Ni02NTAxMWZmZTA2NmIiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdDo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJzdWIiOiJiZWJiNjk1NC0wZTBmLTQ3ZmMtODkzZS0yNmFmZGY1NjYyNmEiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJwYXlzaGllbGQtYXBwIiwic2Vzc2lvbl9zdGF0ZSI6IjljZTg4NDAyLWFiOWEtNDBlNy04NmNjLWNkODgwNzAwNmYxNCIsImFjciI6IjEiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cHM6Ly9vYXV0aC5wc3Rtbi5pbyIsImh0dHBzOi8vbG9jYWxob3N0Ojg4ODgiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbImVtcGxveWVlIl19LCJjbmYiOnsieDV0I1MyNTYiOiJ5NEJRWmNRTEJIeVcyQ09wbnA0ejhwWmJTaHM3cy1aemszRG1UczcxVzBBIn0sInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwiLCJzaWQiOiI5Y2U4ODQwMi1hYjlhLTQwZTctODZjYy1jZDg4MDcwMDZmMTQiLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwibmFtZSI6Ikh1bmcgTmd1eWVuIiwicHJlZmVycmVkX3VzZXJuYW1lIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIiwiZ2l2ZW5fbmFtZSI6Ikh1bmciLCJmYW1pbHlfbmFtZSI6Ik5ndXllbiIsImVtYWlsIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIn0.-GM75P0wQX1czMWAF662w1aoy5akb6HHzCaSc-UKLZE9w0p4tuNO7Jmbo81qSL7Kc_EaaDHXnxFN_MxN7C_fCQ" 
# Thay client secret vào đây
SECRET = "HoHjwBR7aSEJtzQ1DylcppVxZ4CWnVmQ" 

CA = 'kms/ca.crt'
CLIENT = ('kms/client.crt', 'kms/client.key')

def payload(method, uri, ts, nonce, token, secret):
    payload = f"{method}|{uri}|{ts}|{nonce}|{token}"
    return hmac.new(secret.encode(), payload.encode(), hashlib.sha256).hexdigest()

def send(label, use_mtls=True, custom_headers=None):
    print(f"\n--- {label} ---")
    headers = custom_headers if custom_headers else {}
    cert = CLIENT if use_mtls else None
    
    try:
        response = requests.get(URL, headers=headers, cert=cert, verify=False)
        print(f"Status: {response.status_code}")
        print(f"Response: {response.text}")
        return headers
    except requests.exceptions.SSLError as e:
        print(f"❌ SSL Error: {e}")
    except Exception as e:
        print(f"❌ Error: {e}")

# TRƯỜNG HỢP 1: NGƯỜI DÙNG HỢP LỆ
ts = str(int(time.time()))
nonce = str(uuid.uuid4())
sig = payload("GET", URI, ts, nonce, TOKEN, SECRET)

valid_headers = {
    "Authorization": f"Bearer {TOKEN}",
    "X-Timestamp": ts,
    "X-Nonce": nonce,
    "X-Signature": sig
}

send("SCENARIO 1: VALID USER", use_mtls=True, custom_headers=valid_headers)

# TRƯỜNG HỢP 2: HACKER NGOÀI MẠNG
send("SCENARIO 2: EXTERNAL HACKER", use_mtls=False, custom_headers=valid_headers)

# TRƯỜNG HỢP 3: NHÂN VIÊN XẤU
send("SCENARIO 3: ROGUE INSIDER", use_mtls=True, custom_headers=valid_headers)

# TRƯỜNG HỢP 4: HACK NEW NONCE
tampered_headers = valid_headers.copy()
tampered_headers["X-Nonce"] = str(uuid.uuid4())
send("SCENARIO 4: TAMPERING ATTACK", use_mtls=True, custom_headers=tampered_headers)

# TRƯỜNG HỢP 5: TIMESTAMP QUÁ HẠN 
old_ts = str(int(time.time()) - 120) 
old_sig = payload("GET", URI, old_ts, nonce, TOKEN, SECRET)
expired_headers = {
    "Authorization": f"Bearer {TOKEN}",
    "X-Timestamp": old_ts,
    "X-Nonce": str(uuid.uuid4()),
    "X-Signature": old_sig
}

send("SCENARIO 5: EXPIRED REQUEST", use_mtls=True, custom_headers=expired_headers)