{{ with secret "pki/issue/payshield-role" "common_name=payshield.local" "alt_names=localhost,payshield_app,payshield_kong,elasticsearch,logstash,kibana,payshield_db,payshield_keycloak,payshield_opa,payshield_waf,payshield_webserver" "ttl=24h" }}
{{ .Data.private_key }}
{{ end }}