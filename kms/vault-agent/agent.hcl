vault {
  address = "https://payshield_vault:8200"
  ca_cert = "/kms/ca.crt"
  client_cert = "/kms/vault.crt"
  client_key = "/kms/vault.key"
}

auto_auth {
  method "approle" {
    config = {
      role_id_file_path = "/kms/vault-agent/roleid"
      secret_id_file_path = "/kms/vault-agent/secretid"
      remove_secret_id_file_after_reading = false
    }
  }
}

template {
  source      = "/kms/vault-agent/cert.tpl"
  destination = "/tmpfs/certs/server.crt"
}

template {
  source      = "/kms/vault-agent/key.tpl"
  destination = "/tmpfs/certs/server.key"
}