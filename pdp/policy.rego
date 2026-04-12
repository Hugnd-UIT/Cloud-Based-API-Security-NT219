package payshield.authz

import future.keywords.in
import future.keywords.if

# Deny-by-default
default allow = false

# 1. Trích xuất Payload JWT
token_payload := payload if {
    auth_header := input.request.http.headers.authorization
    token := trim_space(replace(auth_header, "Bearer ", ""))
    [_, payload, _] := io.jwt.decode(token)
}

# 2. Lấy vân tay thực tế từ Header (BẢN FIX CHUẨN)
mtls_fingerprint := actual_hex if {
    cert_escaped := input.request.http.headers["x-forwarded-client-cert"]
    cert := crypto.x509.parse_certificates(urlquery.decode(cert_escaped))[0]
    
    # FIX: Phải decode cái chuỗi Base64 (cert.Raw) ra byte thô thì băm mới giống Keycloak
    raw_bytes := base64.decode(cert.Raw)
    
    # FIX: crypto.sha256 đã trả về Hex, không được encode thêm lần nữa
    actual_hex := lower(crypto.sha256(raw_bytes))
}

# 3. So sánh vân tay
mtls_is_valid if {
    expected_hex := lower(hex.encode(base64url.decode(token_payload.cnf["x5t#S256"])))
    mtls_fingerprint == expected_hex
}

# 4. Gom Debug vào một Rule để soi lỗi
debug_info := {
    "actual": actual,
    "expected": expected,
    "has_cert_header": has_cert
} if {
    actual := object.get({ "v": mtls_fingerprint }, "v", "NOT_FOUND_OR_INVALID_CERT")
    expected := lower(hex.encode(base64url.decode(token_payload.cnf["x5t#S256"])))
    has_cert := object.get(input.request.http.headers, "x-forwarded-client-cert", "") != ""
}

user_roles := token_payload.realm_access.roles

# =====================================================================
# POLICY: MANAGER - RBAC
# =====================================================================
allow if {
    mtls_is_valid
    "manager" in user_roles
    startswith(input.request.http.path, "/api/")
}

# =====================================================================
# POLICY: EMPLOYEE - ABAC
# =====================================================================
allow if {
    mtls_is_valid
    "employee" in user_roles

    # [ABAC] - Method
    valid_methods := ["GET", "POST"]
    input.request.http.method in valid_methods

    # [ABAC] - Resource
    allowed_paths := [
        "/api/dashboard/employee-data",
        "/api/profile",
        "/api/salary",
        "/api/vnpay/create"
    ]
    some path in allowed_paths
    startswith(input.request.http.path, path)

    # [ABAC] - Contextual Time-based
    utc_hour := time.clock(time.now_ns())[0]
    vn_hour := (utc_hour + 7) % 24
    vn_hour >= 6
    vn_hour <= 22
}