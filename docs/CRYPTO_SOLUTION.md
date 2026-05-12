## 1. Crypto (Bảo vệ dữ liệu)

### 1.1. Dữ liệu khi truyền (In-transit)

Trong hệ thống, dữ liệu không được truyền một cách bình thường mà được bảo vệ bằng cơ chế mTLS cho toàn bộ các kết nối nội bộ

- Cụ thể, các thành phần như WAF → Kong Gateway → Proxy → Application → Database đều phải xác thực hai chiều bằng certificate. Nghĩa là cả hai bên đều phải chứng minh danh tính của mình trước khi giao tiếp. Nhờ vậy, chỉ những service hợp lệ mới có thể kết nối với nhau, tránh việc bị giả mạo
- Ngoài ra, hệ thống sử dụng TLS 1.3 với các bộ mã hóa hiện đại như: TLS_AES_256_GCM_SHA384, TLS_AES_128_GCM_SHA256. Các thuật toán này giúp đảm bảo dữ liệu khi truyền đi sẽ không bị nghe lén hoặc bị thay đổi nội dung
- Trong quá trình triển khai, nhóm sử dụng Vault Agent để cấp phát certificate cho các service. Các certificate này chỉ được lưu trong RAM thay vì ổ đĩa, giúp giảm nguy cơ bị lộ private key

### 1.2. Dữ liệu khi lưu trữ (At-rest)

Đối với dữ liệu lưu trong database, hệ thống không lưu ở dạng plaintext mà áp dụng cơ chế mã hóa nhiều lớp

Cụ thể là sử dụng Envelope Encryption:
- Dữ liệu sẽ được mã hóa bằng DEK (Data Encryption Key) với thuật toán AES-256-GCM  
- Sau đó DEK sẽ được mã hóa tiếp bằng KEK (Key Encryption Key) được lưu trong HashiCorp Vault  

=> Cách này giúp nếu dữ liệu trong database bị lộ thì cũng không thể đọc được nếu không có key. Ngoài ra, toàn bộ các thông tin nhạy cảm như password, API key, secret key đều không được lưu trong source code. Các service sẽ lấy các thông tin này từ Vault khi cần sử dụng

### 1.3. Chữ ký & xác thực (Signing & Verification)

Hệ thống sử dụng thuật toán ES256 - ECC + SHA256 được cấu hình trên Keycloak để thực hiện việc ký và xác thực token.

Cụ thể:
- Token (JWT) sẽ được ký bằng private key  
- Khi nhận request, hệ thống sẽ dùng public key để kiểm tra chữ ký  

=> Việc này giúp đảm bảo token không bị chỉnh sửa hoặc giả mạo trong quá trình sử dụng.

## 2. Authentication (AuthN)

### 2.1. Người dùng

Hệ thống sử dụng Keycloak làm Identity Provider để quản lý người dùng. Người dùng khi đăng nhập phải nhập username/password. Ngoài ra hệ thống áp dụng rate-limit (20 request/phút) tại Kong/Gateway để chống brute-force  

### 2.2. Dịch vụ (Machine-to-Machine)

Các service nội bộ không dùng password để xác thực mà sử dụng mTLS. Mỗi service sẽ có certificate riêng và chỉ những certificate được ký bởi Root CA nội bộ mới được chấp nhận. Nhờ vậy, chỉ các service hợp lệ mới có thể giao tiếp với nhau

### 2.3. Quản lý phiên (Session)

Sau khi đăng nhập, hệ thống sử dụng JWT để quản lý phiên. Đặc điểm: Token có thời gian sống ngắn (short-lived, 5 phút). Giúp giảm rủi ro bị tấn công như XSS hoặc session hijacking

## 3. Authorization (AuthZ)

### 3.1. Mô hình phân quyền

Nguyên tắc cốt lõi
- Least Privilege (Quyền hạn tối thiểu): Hệ thống chỉ cấp đúng và đủ những quyền cần thiết để người dùng hoặc dịch vụ thực hiện công việc của mình
- Deny-by-default (Mặc định từ chối): Mọi request truy cập đều mặc định bị từ chối, bị chặn trừ khi có quyền hạn được khai báo (role)

Hệ thống dùng mô hình phân quyền kết hợp: 
- RBAC (Role-Based Access Control): Phân quyền dựa trên vai trò của người dùng (Manager và Employee). VD: Chỉ có manager mới được thêm nhân viên
- ABAC (Attribute-Based Access Control): Phân quyền dựa trên các thuộc tính (role, action, resources)

### 3.2. Cơ chế thực thi

**PEP (Policy Enforcement Poin)**: Kong mặc định chặn mọi request để kiểm tra rồi hỏi OPA phân quyền
**PDP (Policy Decision Point - Điểm quyết định)**: Open Policy Agent (OPA) là bộ não phân quyền cho các request, chỉ huy Kong cho phép request đó đi qua hay không, cơ chế đối chiếu các bản khai của request với các quy tắc RBAC, ABAC được nạp sẵn để quyết định