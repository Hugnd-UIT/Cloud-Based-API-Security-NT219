# HƯỚNG DẪN VẬN HÀNH HỆ THỐNG - OPERATIONAL RUNBOOK

**Project:** Cloud API-Based Network Application Security

---

## 1. Mục đích
Tài liệu này cung cấp hướng dẫn từng bước để khởi tạo, vận hành và khắc phục sự cố cơ bản cho hệ thống trên môi trường Docker.

## 2. Hướng dẫn khởi tạo
Hệ thống bao gồm nhiều dịch vụ phụ thuộc lẫn nhau nên phải khởi động theo đúng thứ tự sau:

### Bước 2.1: Khởi động HashiCorp Vault

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker-compose up -d vault
```

### Bước 2.2: Khởi động Database MySQL

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker-compose up -d db
docker exec -it payshield_app php artisan config:clear                                                    
docker exec -it payshield_app php artisan cache:clear
docker exec -it payshield_app php artisan migrate
```

### Bước 2.3: Kích hoạt HashiCorp Vault

Do Vault bị niêm phong, bắt buộc phải mở khóa.

* **Nếu chưa có Unseal Keys => Khởi tạo:**

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker exec -it -e VAULT_ADDR="https://127.0.0.1:8200" -e VAULT_CACERT="/vault/certs/ca.crt" -e VAULT_CLIENT_CERT="/vault/certs/vault.crt" -e VAULT_CLIENT_KEY="/vault/certs/vault.key" -e VAULT_SKIP_VERIFY="true" payshield_vault vault operator init
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

### Bước 2.4: Thêm chứng chỉ vào Chrome

* **Tạo chứng chỉ định dạng PKCS cho Chrome**

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
openssl pkcs12 -export -out client.p12 -inkey key/client.key -in cert/client.crt -certfile cert/ca.crt
```

=> Lệnh yêu cầu nhập mật khẩu hãy nhập "HugndUIT" hoặc khác tùy ý. Sau đó sẽ xuất hiện file client.p12.

* **Nhập chứng chỉ định dạng PKCS cho Chrome**
1: Truy cập chrome://certificate-manager/clientcerts"
2: Truy cập "Manage imported certificates from Windows"
3: Chọn Import -> Next -> Browse -> Chọn file `client.p12`
4: Nhập mật khẩu đã đặt ở bước trên -> Next -> Next -> Finish

### Bước 2.5: Thêm dữ liệu vào HashiCorp Vault


### Bước 2.6: Khởi động Keycloak

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker-compose up -d keycloak
```

### Bước 2.7: Thêm dữ liệu vào Keycloak

1: Truy cập "https://localhost:8444/admin/" và đăng nhập bằng username (admin), pass (admin)
2: Chọn Create Realm -> Browse -> Chọn file keycloak-export.json trong thư mục idp
3: Chọn Realm settings -> Key -> Lưu Public key của thuật toán RS256 cho bước tiếp theo
*Lưu ý: Chọn đúng realm là payshield-realm* 

### Bước 2.8: Thêm dữ liệu vào Kong

1: Truy cập thư mục gateway chọn file kong.yml
2: Nhập Public key của Keycloak vừa lầy được ở bước trên vào biến "rsa_public_key"

### Bước 2.9: Khởi động các dịch vụ còn lại

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:

```bash
docker-compose up -d
```

### Bước 2.10: Khởi động Postman

1: Mở Postman và import collections bằng file "postman-collection.json" trong thư mục api
2: Import enviroment bằng file "postman-environment" trong thư mục api
3: Mở Settings -> App settings -> Certificates -> Tích chọn CA certificates -> Import file ca.cert trong thư mục cert vào PEM file
4: Chọn Add Certificate -> Tạo cert cho 2 đường dẫn là localhost:8444 và localhost:8888, CRT file và KEY file chọn client.crt và client.key trong thư mục cert và key
*Lưu ý: Client ID là payshield-app và Client Secret là Client Secret của Keycloak hãy sửa lại*

---

## 3. Hướng dẫn vận hành

**Gọi API**
1: Mở Postman ở tab Authorization -> Get new access token -> Đăng nhập vào bằng tài khoản được cấp ở mục 4
2: Chọn use token -> Chọn API muốn gọi đến và nhấn Send.

**Gọi Webhook**
1: Nhấn send API POST/VNPay 
2: Nhận phản hồi 200OK và nhận được link payment_url
3: Copy link vào trình duyệt -> Chọn "Thẻ nội địa và tài khoản ngân hàng" -> Chọn NCB -> Nhập tài khoản được cấp ở mục 4
4: Nhấn tiếp tục -> nhập OPT là: "123456" -> Trình duyệt trả về một URL -> Copy URL vào và dán vào API GET/VNPay
5: Sửa vnpay-return thành vnpay-ipn và nhấn send nếu thấy Confirm success là thành công

**Sử dụng ELK**
1: Truy cập https://localhost:5601
2: Đăng nhập bằng tài khoản được cấp ở mục 4

---

## 4. Tài khoản và mật khẩu

---

## 5. Một số lưu ý

### Tắt hệ thống an toàn

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:
```bash
docker-compose down
```
*Lưu ý: Không dùng cờ `-v` trừ khi muốn xóa sạch toàn bộ dữ liệu Database và Vault để làm lại từ đầu*

### Export keycloak

1: Chạy terminal tại thư mục services
2: Chạy lệnh sau:
```bash
docker exec -it payshield_keycloak /opt/keycloak/bin/kc.sh export --file /tmp/keycloak-export.json --realm payshield-realm --users realm_file
docker cp payshield_keycloak:/tmp/keycloak-export.json ../idp/keycloak-export.json
```