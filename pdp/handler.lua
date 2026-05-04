local http = require "resty.http"
local cjson = require "cjson.safe"

local OpaHandler = {
  PRIORITY = 900,
  VERSION = "1.0.0",
}

function OpaHandler:access(conf)
  local httpc = http.new()
  httpc:set_timeout(2000)

  local jwt_header = kong.request.get_header("Authorization")
  if not jwt_header then return kong.response.exit(401, { message = "Missing Token" }) end

  local headers = kong.request.get_headers()
  
  local payload = {
    input = {
      request = {
        http = {
          method = kong.request.get_method(),
          path = kong.request.get_path(),
          headers = headers 
        }
      }
    }
  }

  local res, err = httpc:request_uri(conf.opa_server .. conf.opa_path, {
    method = "POST",
    body = cjson.encode(payload),
    headers = { ["Content-Type"] = "application/json" },
    ssl_server_name = "payshield_opa",
    ssl_verify = true
  })

  if not res then return kong.response.exit(500, { message = "OPA Unavailable", reason = err }) end

  local opa_response = cjson.decode(res.body)
  
  if not opa_response or (opa_response.result and opa_response.result.allow ~= true) then
    
    -- local debug_info = "Không có data debug"
    -- if opa_response and opa_response.result and opa_response.result.debug then
    --   debug_info = opa_response.result.debug
    -- end

    return kong.response.exit(403, { 
      message = "Access Denied by OPA Policy",
      -- opa_debug_log = debug_info
    })
  end
end

return OpaHandler