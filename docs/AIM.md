# AIM

## HỆ THỐNG BẢO MẬT API CHO PAYSHIELD- Overview

Hệ thống PayShield được xây dựng để quản lý lương nhân viên, tập trung vào việc bảo vệ các nhóm tài sản quan trọng trong hệ thống. Các cơ chế bảo mật được áp dụng xuyên suốt từ lưu trữ, xử lý đến truyền dữ liệu.

### A1 - Dữ liệu (Data)

**Đối tượng**
- Bao gồm thông tin nhân viên, dữ liệu bảng lương.

**Trạng thái bảo vệ**
- At-rest: Dữ liệu được mã hóa ở cả mức ứng dụng và cơ sở dữ liệu thông qua HashiCorp Vault.  
- In-transit: Dữ liệu được bảo vệ bằng mTLS trong toàn bộ luồng để đảm bảo không bị nghe lén hoặc chỉnh sửa.  
- In-process: Hạn chế việc giữ dữ liệu nhạy cảm trong RAM khi xử lý tính lương, đồng thời tránh ghi log các thông tin nhạy cảm để giảm rủi ro rò rỉ.

### A2 - Bí mật & Khóa (Secrets & Keys)

**Mã hóa dữ liệu**
- Sử dụng mô hình KEK/DEK - Key Encryption Key / Data Encryption Key, trong đó key được lưu và quản lý tại Vault để bảo vệ dữ liệu bảng lương.

**Khóa chữ ký**
- Sử dụng cặp khóa bất đối xứng - ES256 do Keycloak quản lý để ký và xác thực token.

**Thông tin xác thực**
- Bao gồm thông tin đăng nhập database, API key của bên thứ ba - VNPay và private key dùng cho mTLS giữa các service nội bộ.

### A3 - Danh tính (Identity)

**Người dùng**
- Người dùng được chia thành nhân viên và quản trị viên, được quản lý tập trung bởi Identity Provider (Keycloak).

**Dịch vụ**
- Các service trong hệ thống giao tiếp với nhau thông qua cơ chế machine-to-machine, được xác thực bằng chứng chỉ số do hệ thống CA nội bộ cấp.

**Thiết bị**
- Các client muốn kết nối vào hệ thống cần có client certificate hợp lệ để thiết lập kết nối tin cậy.

### A4 - Trạng thái & Chính sách (State & Policy)

**Session & Claims**
- Thông tin phiên đăng nhập và quyền truy cập được lưu trong JWT dưới dạng claims.

**Policy Engine**
- Các luật kiểm soát truy cập được định nghĩa bằng Rego và thực thi thông qua OPA, hỗ trợ cả RBAC và ABAC để kiểm soát chi tiết hơn.

### A5 - Hạ tầng tin cậy (Trusted Infrastructure)

**Root CA & Vault**
- Đóng vai trò cấp phát chứng chỉ cho mTLS và quản lý các key/mật mã trong hệ thống.

**API Gateway & WAF**
- Là lớp bảo vệ đầu vào của hệ thống, giúp kiểm soát request, chặn các tấn công phổ biến như SQL Injection, XSS và quản lý lưu lượng truy cập.