return {
  name = "opa",
  fields = {
    {
      config = {
        type = "record",
        fields = {
          { opa_server = { type = "string", required = true, default = "https://opa:8181" } },
          { opa_path   = { type = "string", required = true, default = "/v1/data/payshield/authz" } },
        },
      },
    },
  },
}