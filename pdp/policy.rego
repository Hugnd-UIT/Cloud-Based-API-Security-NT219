package payshield.authz

import future.keywords.in
import future.keywords.if

default allow := false

jwks_request := http.send({
    "url": "https://keycloak:8443/realms/payshield-realm/protocol/openid-connect/certs",
    "tls_insecure_skip_verify": true, 
    "method": "GET"
})

token_payload := payload if {
    auth_header := object.get(input.request.http.headers, "authorization", "")
    token := trim_space(replace(auth_header, "Bearer ", ""))
    [valid, _, payload] := io.jwt.decode_verify(token, {"cert": jwks_request.raw_body})
    valid == true
} else := {}

mtls_fingerprint := actual_hex if {
    cert_escaped := object.get(input.request.http.headers, "x-forwarded-client-cert", "")
    cert_escaped != ""
    cert := crypto.x509.parse_certificates(urlquery.decode(cert_escaped))[0]
    raw_bytes := base64.decode(cert.Raw)
    actual_hex := lower(crypto.sha256(raw_bytes))
} else := "NO_CERT_FOUND"

expected_hex := hex_val if {
    x5t := object.get(token_payload, ["cnf", "x5t#S256"], "")
    x5t != ""
    hex_val := lower(hex.encode(base64url.decode(x5t)))
} else := "NO_TOKEN_FOUND"

mtls_is_valid := true if {
    mtls_fingerprint != "NO_CERT_FOUND"
    expected_hex != "NO_TOKEN_FOUND"
    trim_space(mtls_fingerprint) == trim_space(expected_hex)
} else := false

user_roles := object.get(object.get(token_payload, "realm_access", {}), "roles", [])

# =====================================================================
# POLICY: MANAGER - RBAC
# =====================================================================
allow if {
    mtls_is_valid == true
    "manager" in user_roles
    startswith(input.request.http.path, "/api/")
}

debug := {
    "1_is_token_valid": count(token_payload) > 0,
    "2_mtls_match": mtls_is_valid,
    "3_roles": user_roles,
    "4_cert_postman": mtls_fingerprint,
    "5_cert_token": expected_hex
}

# =====================================================================
# POLICY: EMPLOYEE - ABAC
# =====================================================================
allow if {
    mtls_is_valid == true
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