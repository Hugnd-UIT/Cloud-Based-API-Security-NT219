import requests
import urllib3
import time
from collections import defaultdict

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

URL = "https://localhost:8888/api"

TOKEN = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJIbDVqRUNMbGxic29pTkp1ZUVGV3BaVnlhcldFeGxHNFBKVE54QnloN2tBIn0.eyJleHAiOjE3NzY2OTU2MTgsImlhdCI6MTc3NjY5NTMxOCwiYXV0aF90aW1lIjoxNzc2Njg5NTQwLCJqdGkiOiIyNmUwNWEzMi00OWY4LTRhNDItOTE2OC1hMGNjMTI2NGVkMDMiLCJpc3MiOiJodHRwczovL2xvY2FsaG9zdDo4NDQ0L3JlYWxtcy9wYXlzaGllbGQtcmVhbG0iLCJzdWIiOiJiZWJiNjk1NC0wZTBmLTQ3ZmMtODkzZS0yNmFmZGY1NjYyNmEiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJwYXlzaGllbGQtYXBwIiwic2Vzc2lvbl9zdGF0ZSI6IjQwZTBjMDFkLTkxZjEtNGM1Ni04NjBiLTUzOTI4ZDdjY2FiOCIsImFjciI6IjEiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cDovL2xvY2FsaG9zdDo2ODY4IiwiaHR0cHM6Ly9vYXV0aC5wc3Rtbi5pbyIsImh0dHBzOi8vbG9jYWxob3N0Ojg4ODgiXSwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbImVtcGxveWVlIl19LCJjbmYiOnsieDV0I1MyNTYiOiJwVXFsaU50U3hGckl2LUgyQ01xMVNsYTFMekhMLW9xZHYxbkZtV1JQclYwIn0sInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwiLCJzaWQiOiI0MGUwYzAxZC05MWYxLTRjNTYtODYwYi01MzkyOGQ3Y2NhYjgiLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwibmFtZSI6Ikh1bmcgTmd1eWVuIiwicHJlZmVycmVkX3VzZXJuYW1lIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIiwiZ2l2ZW5fbmFtZSI6Ikh1bmciLCJmYW1pbHlfbmFtZSI6Ik5ndXllbiIsImVtYWlsIjoiZW1wbG95ZWUtZXhhbXBsZUBwYXlzaGllbGQuY29tIn0.ebI1VmwOdrcIR2i5cNx4rySPzm-dtuGNv72-LRrTEksG1lXSINtZcuMN6eeIXy3ZCYzBoDyvO1QQ2KSgnmGC9gScdj4P00hf5XvKRca9d3cWVuRD42cQN26XEaay4LKosnA1ybLUspFzvspL-roHEdpadfrhKtzlsEVT7zUlPQd-lDayEIViizjWI5wJ_MnVkisFGUqPP5P4m-EQBbLq5Ba05QR03Bqx1kFN6Xh4FJP4RC1TkQqUMhHcRxr1_9oDA1DAqLGZ7gmwPfj2pNar4nLFymgQNFaki5GJdVSai3tlfgDGdTpd8Jo3RjI_BBqGIvtDyEG5T1rrsRzJdE7vnQ"

CERT = ('services/cert/client.crt', 'services/key/client.key')

HEADERS = {"Authorization": f"Bearer {TOKEN}", "Content-Type": "application/json"}

TARGET = [f"NV{str(i).zfill(3)}" for i in range(0, 51)]

def bola_attack():
    stats = defaultdict(int)
    
    for id in TARGET:
        url = f"{URL}/employees/{id}"
        for method in ["GET", "PUT", "DELETE"]:
            try:
                time.sleep(1) 
                res = requests.request(method, url, headers=HEADERS, cert=CERT, verify=False, timeout=5)
                stats[res.status_code] += 1
            except Exception:
                stats["Errors"] += 1

    total_attempts = sum(stats.values())
    print(f"=== BOLA ATTACK ===")
    print(f"Tổng số request: {total_attempts}")
    
    for code, count in sorted(stats.items(), key=lambda x: str(x[0])):
        if code != "Errors":
            print(f"Số lần phản hồi HTTP {code}: {count}")
    
    if total_attempts > 0:
        blocked = stats.get(403, 0)
        leaked = stats.get(200, 0)
        
        denominator = blocked + leaked
        if denominator > 0:
            pass_rate = (blocked / denominator) * 100
            print(f"\nTỷ lệ Pass-rate (AuthZ): {pass_rate:.2f}%")
            print("=> Invariant I5: ĐẠT" if pass_rate >= 95 else "=> BOLA ATTACK DETECTED")
        else:
            print("\n=> Không thể tính tỷ lệ Pass-rate")

bola_attack()