# HƯỚNG DẪN VẬN HÀNH HỆ THỐNG - OPERATIONAL RUNBOOK

**Project:** Cloud API-Based Network Application Security

---

## 1. Mục đích
Tài liệu này cung cấp hướng dẫn từng bước để khởi tạo, vận hành và khắc phục sự cố cơ bản cho hệ thống trên môi trường Docker.

## 2. Hướng dẫn khởi tạo
Hệ thống bao gồm nhiều dịch vụ phụ thuộc lẫn nhau nên phải khởi động theo đúng thứ tự sau:

### Bước 2.1: Khởi động hệ thống

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker-compose up -d
```

*Lưu ý: Sau khi chạy lệnh thì vault agent sẽ đợi vault có dữ liệu và mở khóa*

### Bước 2.2: Kích hoạt HashiCorp Vault

Do Vault bị niêm phong, bắt buộc phải mở khóa.

* **Nếu chưa có Unseal Keys => Khởi tạo:**

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker exec -it `
  -e VAULT_ADDR="https://127.0.0.1:8200" `
  -e VAULT_CACERT="/vault/certs/ca.crt" `
  -e VAULT_CLIENT_CERT="/vault/certs/vault.crt" `
  -e VAULT_CLIENT_KEY="/vault/certs/vault.key" `
  -e VAULT_SKIP_VERIFY="true" `
  payshield_vault `
  vault operator init
```
*Lưu ý: Lưu ngay 5 Unseal Keys và Root Token vào nơi an toàn*

3: Tạo biến chưa key trong .env

```bash
VAULT_APP_TOKEN="Nhập Token Root vào đây"
VAULT_KEY1="Nhập Unseal Key 1 vào đây"
VAULT_KEY2="Nhập Unseal Key 2 vào đây"
VAULT_KEY3="Nhập Unseal Key 3 vào đây"
VAULT_KEY4="Nhập Unseal Key 4 vào đây"
VAULT_KEY5="Nhập Unseal Key 5 vào đây"
```

* **Nếu đã có Unseal Keys => Mở khóa:**

1: Chạy terminal tại thư mục kms
2: Chạy lệnh sau:

```bash
./vault-unseal
```

*Sau khi mở khóa vault phải thêm dữ liệu vào vault nếu chưa có*

1: Chạy terminal tại thư mục services
2: Truy cập môi trường shell của Vault:

```bash
docker exec -it payshield_vault sh
```

3: Cấp phát biến môi trường cho CLI:

```bash
export VAULT_SKIP_VERIFY=true
export VAULT_CLIENT_CERT=/vault/certs/vault.crt
export VAULT_CLIENT_KEY=/vault/certs/vault.key
```

4: Cấu hình PKI engine:

```bash
vault login "ROOT TOKEN"

vault secrets enable pki
vault secrets tune -max-lease-ttl=87600h pki

vault write pki/config/ca \
  pem_bundle="$(cat /vault/certs/ca.key /vault/certs/ca.crt)"

vault write pki/roles/payshield-role \
    allow_any_name=true \
    enforce_hostnames=false \
    max_ttl="720h"
```

5: Cấu hình AppRole:

```bash
vault auth enable approle

echo 'path "pki/issue/payshield-role" { capabilities = ["create", "update"] }' > /tmp/policy.hcl

vault policy write payshield-agent-policy \
  /tmp/policy.hcl

vault write auth/approle/role/payshield-agent \
  token_policies="payshield-agent-policy" \
  token_ttl=1h \
  token_max_ttl=4h

vault read auth/approle/role/payshield-agent/role-id
vault write -f auth/approle/role/payshield-agent/secret-id
```

*Lưu ý: sau khi có role id và secret id đưa vào env*

```bash
VAULT_ROLE_ID="Nhập role id ở đây"
VAULT_SECRET_ID="Nhập secret id ở đây"
```

### Bước 2.3: Trích xuất dữ liệu Vault

1: Chạy terminal tại thư mục kms
2: Chạy lệnh sau:

```bash
docker cp payshield_vault_agent:/tmpfs/certs/client/server.crt ./client.crt
docker cp payshield_vault_agent:/tmpfs/certs/client/server.key ./client.key
```

*Sau khi chạy xong sẽ xuất hiện client.crt và client.key ở thư mục kms, chứng chỉ và khóa này sẽ dùng cho client gọi API*

### Bước 2.4: Thêm chứng chỉ vào Chrome

* **Tạo chứng chỉ định dạng PKCS cho Chrome**

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
openssl pkcs12 -export -out client.p12 -inkey server.key -in server.crt -certfile ca.crt
```

=> Lệnh yêu cầu nhập mật khẩu hãy nhập "HugndUIT" hoặc khác tùy ý. Sau đó sẽ xuất hiện file client.p12.

* **Nhập chứng chỉ định dạng PKCS cho Chrome**
1: Truy cập "chrome://certificate-manager/clientcerts"
2: Truy cập "Manage imported certificates from Windows"
3: Chọn Import -> Next -> Browse -> Chọn file `client.p12`
4: Nhập mật khẩu đã đặt ở bước trên -> Next -> Next -> Finish

### Bước 2.5: Thêm dữ liệu vào Keycloak

1: Truy cập "https://localhost:8444/admin/" và đăng nhập bằng username (admin), pass (admin)
2: Chọn Create Realm -> Browse -> Chọn file keycloak-export.json trong thư mục idp
3: Chọn Realm settings -> Key -> Lưu Public key của thuật toán RS256 cho bước tiếp theo
*Lưu ý: Chọn đúng realm là payshield-realm* 

### Bước 2.6: Khởi động Postman

1: Mở Postman và import collections bằng file "postman-collection.json" trong thư mục api
2: Import enviroment bằng file "postman-environment" trong thư mục api
3: Mở Settings -> App settings -> Certificates -> Tích chọn CA certificates -> Import file ca.cert trong thư mục cert vào PEM file
4: Chọn Add Certificate -> Tạo cert cho 2 đường dẫn là localhost:8444 và localhost:8888, CRT file và KEY file chọn server.crt và server.key trong thư mục kms
*Lưu ý: Client ID là payshield-app và Client Secret là Client Secret của Keycloak hãy sửa lại*

---

## 3. Hướng dẫn vận hành

**Call API**
1: Mở Postman ở tab Authorization -> Get new access token -> Đăng nhập vào bằng tài khoản sau 

*Tài khoản manager*
```bash
username: manager-example@payshield.com
password: example
```

*Tài khoản employee*
```bash
username: employee-example@payshield.com
password: example
```

2: Chọn use token -> Chọn API muốn gọi đến và nhấn Send.

**Call Webhook**
1: Nhấn send API POST/VNPay 
2: Nhận phản hồi 200OK và nhận được link payment_url
3: Copy link vào trình duyệt -> Chọn "Thẻ nội địa và tài khoản ngân hàng" -> Chọn NCB -> Nhập tài khoản sau:

```bash
Số thẻ: 9704198526191432198
Tên chủ thẻ: NGUYEN VAN A
Ngày phát hành: 07/15
```

4: Nhấn tiếp tục -> nhập OPT là: "123456" -> Trình duyệt trả về một URL -> Copy URL vào và dán vào API GET/VNPay
5: Sửa vnpay-return thành vnpay-ipn và nhấn send nếu thấy Confirm success là thành công

**Use ELK**
1: Truy cập https://localhost:5601
2: Đăng nhập bằng tài khoản username: elastic và pass: NguyenDuyHung
*Mật khẩu ELK có thể khác kiểm tra ELK_PASSWORD trong env*

---

## 4. Một số lưu ý

### Tắt hệ thống an toàn

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker-compose down
```
*Lưu ý: Không dùng cờ `-v` trừ khi muốn xóa sạch toàn bộ dữ liệu Database và Vault để làm lại từ đầu*

### Xuất cấu hình keycloak

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker exec -it payshield_keycloak `
  /opt/keycloak/bin/kc.sh export `
  --file /tmp/keycloak-export.json `
  --realm payshield-realm `
  --users realm_file

docker cp payshield_keycloak:/tmp/keycloak-export.json `
  ../idp/keycloak-export.json
```

### Khởi tạo dữ liệu database

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker exec -it payshield_app php artisan config:clear
docker exec -it payshield_app php artisan cache:clear
docker exec -it payshield_app php artisan migrate
```

### Kiểm tra thuật toán mã hóa
1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker run --rm --network services_default instrumentisto/nmap -sV --script ssl-enum-ciphers -p 8443 payshield_kong | Select-String "Nmap scan report|PORT|TLSv|TLS_|_  least strength|^\|"

docker run --rm --network services_default instrumentisto/nmap -sV --script ssl-enum-ciphers -p 8200 payshield_vault | Select-String "Nmap scan report|PORT|TLSv|TLS_|_  least strength|^\|"

docker run --rm --network services_default instrumentisto/nmap -sV --script ssl-enum-ciphers -p 3306 payshield_db | Select-String "Nmap scan report|PORT|TLSv|TLS_|_  least strength|^\|"

docker run --rm --network services_default instrumentisto/nmap -sV --script ssl-enum-ciphers -p 8181 payshield_opa | Select-String "Nmap scan report|PORT|TLSv|TLS_|_  least strength|^\|"

docker run --rm --network services_default instrumentisto/nmap -sV --script ssl-enum-ciphers -p 8443 payshield_proxy | Select-String "Nmap scan report|PORT|TLSv|TLS_|_  least strength|^\|"

docker run --rm --network services_default instrumentisto/nmap -sV --script ssl-enum-ciphers -p 8443 payshield_waf | Select-String "Nmap scan report|PORT|TLSv|TLS_|_  least strength|^\|"

docker run --rm --network services_default instrumentisto/nmap -sV --script ssl-enum-ciphers -p 5601 payshield_kibana | Select-String "Nmap scan report|PORT|TLSv|TLS_|_  least strength|^\|"

docker exec -i payshield_kong openssl s_client -connect payshield_keycloak:8443 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /kms/ca.crt -brief | Select-String "Protocol", "Cipher", "Verify"

docker exec -i payshield_kong openssl s_client -connect payshield_elasticsearch:9200 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /kms/ca.crt -brief | Select-String "Protocol", "Cipher", "Verify"

docker exec -i payshield_kong openssl s_client -connect payshield_logstash:5044 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /kms/ca.crt -brief | Select-String "Protocol", "Cipher", "Verify"

docker exec -i payshield_kong openssl s_client -connect payshield_app:443 -cert /tmpfs/certs/server.crt -key /tmpfs/certs/server.key -CAfile /kms/ca.crt -brief | Select-String "Protocol", "Cipher", "Verify"
```

### Tạo file truststore cho Keycloak

1: Chạy terminal tại thư mục kms
2: Chạy lệnh sau:

```bash
keytool -importcert -file ca.crt -alias RootCA -keystore truststore.p12 -storetype PKCS12 -storepass password
```