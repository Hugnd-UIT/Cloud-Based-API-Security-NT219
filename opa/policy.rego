package payshield.authz
import future.keywords

default allow = false

token_payload = payload if {
    auth_header := input.request.http.headers.authorization
    token := trim_space(replace(replace(auth_header, "Bearer ", ""), "bearer ", ""))
    [_, payload, _] := io.jwt.decode(token)
}

allow if {
    "manager" in token_payload.realm_access.roles
}

allow if {
    "employee" in token_payload.realm_access.roles
    allowed_paths := [
        "/api/dashboard/employee-data",
        "/api/profile",
        "/api/salary"
    ]
    some path in allowed_paths
    startswith(input.request.http.path, path)
}