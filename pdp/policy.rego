package payshield.authz

import future.keywords.in
import future.keywords.if

# Deny-by-defaul
default allow = false

token_payload = payload if {
    auth_header := input.request.http.headers.authorization
    token := trim_space(replace(replace(auth_header, "Bearer ", ""), "bearer ", ""))
    [_, payload, _] := io.jwt.decode(token)
}

user_roles := token_payload.realm_access.roles

# =====================================================================
# POLICY: MANAGER - RBAC
# =====================================================================
allow if {
    "manager" in user_roles
    startswith(input.request.http.path, "/api/")
}

# =====================================================================
# POLICY: EMPLOYEE - ABAC
# =====================================================================
allow if {
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