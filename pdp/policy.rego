package payshield.authz

import future.keywords.in
import future.keywords.if

default allow = false

token_payload := payload if {
    auth_header := input.request.http.headers.authorization
    token := trim_space(replace(auth_header, "Bearer ", ""))
    [_, payload, _] := io.jwt.decode(token)
}

mtls_fingerprint := actual_hex if {
    cert_escaped := input.request.http.headers["x-forwarded-client-cert"]
    cert := crypto.x509.parse_certificates(urlquery.decode(cert_escaped))[0]
    raw_bytes := base64.decode(cert.Raw)
    actual_hex := lower(crypto.sha256(raw_bytes))
}

mtls_is_valid if {
    expected_hex := lower(hex.encode(base64url.decode(token_payload.cnf["x5t#S256"])))
    mtls_fingerprint == expected_hex
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