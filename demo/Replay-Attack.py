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
TOKEN = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJIbDVqRUNMbGxic29pTkp1ZUVGV3BaVnlhcldFeGxHNFBKVE54QnloN2tBIn0.eyJleHAiOjE3Nzc4MTc3MDAsImlhdCI6MTc3NzgxNzQwMCwiYXV0aF90aW1lIjoxNzc3ODE0Njc0LCJqdGkiOiI1ODgyMWExYS0wOTJmLTRlMzMtYWEwZC01N2M4YTdjZTMyZTYiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdDo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJzdWIiOiJmMjhkMmIzZi0wM2U4LTRjZDAtOWExNi03Njc3MGNlNjc5ZmQiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJwYXlzaGllbGQtYXBwIiwic2Vzc2lvbl9zdGF0ZSI6Ijc4MDIxZmU3LTJlZjgtNDkzNy1iNDlhLTM4ODEzOTEzOWQzMCIsImFjciI6IjEiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cHM6Ly9vYXV0aC5wc3Rtbi5pbyIsImh0dHBzOi8vbG9jYWxob3N0Ojg4ODgiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbIm1hbmFnZXIiXX0sImNuZiI6eyJ4NXQjUzI1NiI6ImZTYXJXZ090cUJFVi1Ld2ZidWFBSFh5TjJTMFMtRWlUcjZJWnc1SVpCS2sifSwic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCIsInNpZCI6Ijc4MDIxZmU3LTJlZjgtNDkzNy1iNDlhLTM4ODEzOTEzOWQzMCIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYW1lIjoiSHVuZyBOZ3V5ZW4iLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJtYW5hZ2VyLWV4YW1wbGVAcGF5c2hpZWxkLmNvbSIsImdpdmVuX25hbWUiOiJIdW5nIiwiZmFtaWx5X25hbWUiOiJOZ3V5ZW4iLCJlbWFpbCI6Im1hbmFnZXItZXhhbXBsZUBwYXlzaGllbGQuY29tIn0.EvJQdSNP5HXWC2EdF2JuXfCEB_v_kwKHbaBme5SpG1c1EdkuX_5vEq4gH-uEBc5RRhcsYkNIPZM5HsItYOk_y-2GELwwDsfQLWDFiMh7hoI3HKjr0jYHwS5_6cX7WxEUQwQM8o4BLFpCKKZuGmjoqkBByUg1dHGavYbQwuP5YM7ye2a2ukG1mVTOkD2GsHu3nHjs6vt3yxz8i8a6mWzr8mxM7W3E7ZIiY5vwwl4xv_R2onUYLmhstxzkK84Fc55iIJb27qNR8ZrkKXhOaZ7kaR1_tH8LSuK8MNh_cmnEWdUEceTI7UD_menz820X0iiu7qwSTQz4LMsVoQHqZNSLyw" 
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
send("SCENARIO 3: ROGUE INSIDER (Replay Original Packet)", use_mtls=True, custom_headers=valid_headers)

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

send("SCENARIO 5: EXPIRED REQUEST (Timestamp > 60s)", use_mtls=True, custom_headers=expired_headers)