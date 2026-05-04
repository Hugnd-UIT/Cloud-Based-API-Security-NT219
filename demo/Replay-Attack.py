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
TOKEN = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJIbDVqRUNMbGxic29pTkp1ZUVGV3BaVnlhcldFeGxHNFBKVE54QnloN2tBIn0.eyJleHAiOjE3Nzc4MjI3NzEsImlhdCI6MTc3NzgyMjQ3MSwiYXV0aF90aW1lIjoxNzc3ODIxMTUxLCJqdGkiOiIyNjk5MTU2YS05NjA3LTRhMGEtYjIxZC0wMzk2NzU0OTRiMWQiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdDo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJzdWIiOiJmMjhkMmIzZi0wM2U4LTRjZDAtOWExNi03Njc3MGNlNjc5ZmQiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJwYXlzaGllbGQtYXBwIiwic2Vzc2lvbl9zdGF0ZSI6Ijk1ZmNmOTY0LTUwZGYtNGM3Yi1iMDdlLWUzMmVmMzViMjVjMCIsImFjciI6IjEiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cHM6Ly9vYXV0aC5wc3Rtbi5pbyIsImh0dHBzOi8vbG9jYWxob3N0Ojg4ODgiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbIm1hbmFnZXIiXX0sImNuZiI6eyJ4NXQjUzI1NiI6IjRaa054RFpmVW9zQzhkQ0szTllza01fcGlTTUdrcG8zTDQyd284WlkyeFUifSwic2NvcGUiOiJvcGVuaWQgcHJvZmlsZSBlbWFpbCIsInNpZCI6Ijk1ZmNmOTY0LTUwZGYtNGM3Yi1iMDdlLWUzMmVmMzViMjVjMCIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYW1lIjoiSHVuZyBOZ3V5ZW4iLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJtYW5hZ2VyLWV4YW1wbGVAcGF5c2hpZWxkLmNvbSIsImdpdmVuX25hbWUiOiJIdW5nIiwiZmFtaWx5X25hbWUiOiJOZ3V5ZW4iLCJlbWFpbCI6Im1hbmFnZXItZXhhbXBsZUBwYXlzaGllbGQuY29tIn0.kPY3cBSn76L_vomkFkBoB5635emzv9rr5_rleKSynPAo_T6xXZcUpYPqaL9qaUQ5vw8tgQ2UGGbR_NBWZhJrhUkkzFnN7IduOg0O3BEYZ9-Jc8a_feRGofachGkwuZxTwQIXdsDZwIP5p-eGrKMjhsXMMCyej53JOA0kzVhcsgzCMsdl7b2-x26cMg7XHS8aVk2wCxgzvWltFfvEnDuCFQihdJsUZQRfxz_fmOaSWboZPJ9c1tZLFpwEik6tlOSkAGL_EVZp1FUcJsF1ZM4562I_M-OEMb6IpNJAvrU2388QoOY8a7fF2cDa4ZOOHq6oVQS8soexodx1_QvZ9Cn-tg" 
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