{{ with secret "pki/issue/payshield-role" "common_name=payshield.local" "alt_names=localhost,payshield.local,payshield_app,payshield_kong,payshield_opa,payshield_waf,payshield_keycloak" "ttl=24h" }}
{{ .Data.certificate }}
{{ .Data.issuing_ca }}
{{ end }}