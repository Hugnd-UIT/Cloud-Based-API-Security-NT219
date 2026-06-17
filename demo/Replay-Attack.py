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

TOKEN = "eyJhbGciOiJFUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJqbm1MNU1CQ3ZXelR2V2xaVFo0S0hXU0gwV2V5ZmhzaUduLVUwSkNmdWN3In0.eyJleHAiOjE3ODEwODYwMjksImlhdCI6MTc4MTA4NTcyOSwiYXV0aF90aW1lIjoxNzgxMDg1NzI0LCJqdGkiOiIwMjBlNGI5My04YWRhLTQwNDctYjQ0Ny03NmU0YjkxYTVhNDkiLCJpc3MiOiJodHRwczovL3BheXNoaWVsZC5kdWNrZG5zLm9yZzo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJhdWQiOiJhY2NvdW50Iiwic3ViIjoiMGQyNjE3ZDYtNzAxNC00ZGRjLTliYmEtY2FjNGFiMGU1MzIwIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoicGF5c2hpZWxkLWFwcCIsInNlc3Npb25fc3RhdGUiOiI3OTUyZjRiMi0yMDEzLTQxNzAtYjk2OS1iNjIwNjY1YzRlZWMiLCJhY3IiOiIxIiwiYWxsb3dlZC1vcmlnaW5zIjpbIioiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbIm1hbmFnZXIiLCJvZmZsaW5lX2FjY2VzcyIsImRlZmF1bHQtcm9sZXMtcGF5c2hpZWxkLXJlYWxtIiwidW1hX2F1dGhvcml6YXRpb24iXX0sInJlc291cmNlX2FjY2VzcyI6eyJhY2NvdW50Ijp7InJvbGVzIjpbIm1hbmFnZS1hY2NvdW50IiwibWFuYWdlLWFjY291bnQtbGlua3MiLCJ2aWV3LXByb2ZpbGUiXX19LCJzY29wZSI6Im9wZW5pZCBwcm9maWxlIGVtYWlsIiwic2lkIjoiNzk1MmY0YjItMjAxMy00MTcwLWI5NjktYjYyMDY2NWM0ZWVjIiwiZW1haWxfdmVyaWZpZWQiOnRydWUsInByZWZlcnJlZF91c2VybmFtZSI6Im1hbmFnZXItZXhhbXBsZUBwYXlzaGllbGQuY29tIiwiZW1haWwiOiJtYW5hZ2VyLWV4YW1wbGVAcGF5c2hpZWxkLmNvbSJ9.pOKIj8pp0VIYKgFEfuEtRLXSobR5vGW64aJCngRuBvF190RxqrKF8nQHh3Hh7T5iVrppDGP7V7Tm4ME2eWABaQ"

SECRET = "gTPf9LYwK20ngQcRSd9mTiCxmXLKze7n"

def payload(method, uri, ts, nonce, token, secret):
    payload = f"{method}|{uri}|{ts}|{nonce}|{token}"
    return hmac.new(secret.encode(), payload.encode(), hashlib.sha256).hexdigest()

def send(label, use_mtls=True, custom_headers=None):
    print(f"\n--- {label} ---")
    headers = custom_headers if custom_headers else {}
    
    try:
        response = requests.get(URL, headers=headers, verify=False)
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