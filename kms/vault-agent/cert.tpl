{{ with secret "pki/issue/payshield-role" "common_name=payshield.local" "alt_names=localhost,payshield.local,payshield_app,payshield_kong,payshield_waf,payshield_keycloak,payshield_proxy,payshield_db,payshield_elasticsearch,payshield_logstash,payshield_kibana,payshield_filebeat" "ttl=24h" }}
{{ .Data.certificate }}
{{ .Data.issuing_ca }}
{{ end }}