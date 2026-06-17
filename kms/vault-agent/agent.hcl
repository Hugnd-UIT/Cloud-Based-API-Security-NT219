vault {
  address = "https://payshield_vault:8200"
  ca_cert = "/kms/ca.crt"
  client_cert = "/kms/vault.crt"
  client_key = "/kms/vault.key"
}

auto_auth {
  method "approle" {
    config = {
      role_id_file_path   = "/tmp/roleid"
      secret_id_file_path = "/tmp/secretid"
      remove_secret_id_file_after_reading = false
    }
  }
}

# ==========================================
# KONG
# ==========================================
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_kong" "ttl=24h" }}
{{ .Data.certificate }}
{{ .Data.issuing_ca }}
{{ end }}
EOF
  destination = "/tmpfs/certs/kong/server.crt"
}
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_kong" "ttl=24h" }}
{{ .Data.private_key }}
{{ end }}
EOF
  destination = "/tmpfs/certs/kong/server.key"
}

# ==========================================
# WAF
# ==========================================
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_waf" "ttl=24h" }}
{{ .Data.certificate }}
{{ .Data.issuing_ca }}
{{ end }}
EOF
  destination = "/tmpfs/certs/waf/server.crt"
}
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_waf" "ttl=24h" }}
{{ .Data.private_key }}
{{ end }}
EOF
  destination = "/tmpfs/certs/waf/server.key"
}

# ==========================================
# PROXY
# ==========================================
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_proxy" "alt_names=api.payshield.local,localhost" "ttl=24h" }}
{{ .Data.certificate }}
{{ .Data.issuing_ca }}
{{ end }}
EOF
  destination = "/tmpfs/certs/nginx/server.crt"
}
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_proxy" "alt_names=api.payshield.local,localhost" "ttl=24h" }}
{{ .Data.private_key }}
{{ end }}
EOF
  destination = "/tmpfs/certs/nginx/server.key"
}

# ==========================================
# APP
# ==========================================
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_app" "ttl=24h" }}
{{ .Data.certificate }}
{{ .Data.issuing_ca }}
{{ end }}
EOF
  destination = "/tmpfs/certs/app/server.crt"
}
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_app" "ttl=24h" }}
{{ .Data.private_key }}
{{ end }}
EOF
  destination = "/tmpfs/certs/app/server.key"
}

# ==========================================
# DATABASE
# ==========================================
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_db" "ttl=24h" }}
{{ .Data.certificate }}
{{ .Data.issuing_ca }}
{{ end }}
EOF
  destination = "/tmpfs/certs/db/server.crt"
}
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_db" "ttl=24h" }}
{{ .Data.private_key }}
{{ end }}
EOF
  destination = "/tmpfs/certs/db/server.key"
}

# ==========================================
# Keycloak
# ==========================================
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_keycloak" "alt_names=payshield_keycloak" "ttl=24h" }}
{{ .Data.certificate }}
{{ .Data.issuing_ca }}
{{ end }}
EOF
  destination = "/tmpfs/certs/keycloak/server.crt"
}
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_keycloak" "alt_names=payshield_keycloak" "ttl=24h" }}
{{ .Data.private_key }}
{{ end }}
EOF
  destination = "/tmpfs/certs/keycloak/server.key"
}

# ==========================================
# SIEM
# ==========================================
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_siem" "alt_names=elasticsearch,logstash,kibana,filebeat,localhost" "ttl=24h" }}
{{ .Data.certificate }}
{{ .Data.issuing_ca }}
{{ end }}
EOF
  destination = "/tmpfs/certs/siem/server.crt"
}
template {
  contents = <<EOF
{{ with secret "pki/issue/payshield-role" "common_name=payshield_siem" "alt_names=elasticsearch,logstash,kibana,filebeat,localhost" "ttl=24h" }}
{{ .Data.private_key }}
{{ end }}
EOF
  destination = "/tmpfs/certs/siem/server.key"
}