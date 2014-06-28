--[[
config module example

@author kim https://github.com/xqpmjh
--]]

local pairs                 = pairs
local type                  = type
local setmetatable          = setmetatable
local print                 = ngx.print

--[[ init module --]]
module(...)
_VERSION = '1.0.0'

--[[ indexed by current module env. --]]
local mt = {__index = _M}

--[[-------------------------------------------------------------------------]]

local tbConfig = {

    memcached = {
        host = "127.0.0.1",
        port = 11211,
        connect_timeout = 3000,
        max_idle_timeout = 10000,
        pool_size = 10
    },

    mysql = {
        db_test = {
            host = "127.0.0.1",
            port = 3307,
            database = "test",
            user = "kim",
            password = "123456",
            charset = "utf8",
            max_packet_size = 1048576,
            connect_timeout = 3000,
            max_idle_timeout = 10000,
            pool_size = 10,
            fname = "TEST"
        }
    },

    redis = {
        rds_test = {
            host = "127.0.0.1", port = 6380, connect_timeout = 3000
        }
    }

}

--[[
instantiation
@return table
]]
function new(self, debug)
    if debug and type(debug) == 'table' then
        for k,v in pairs(debug) do
            tbConfig[k] = v
        end
    end
    return setmetatable({}, mt)
end

--[[
get config
@param string key
@return table
]]
function get(self, key)
    if key then
        if tbConfig[key] then
            return tbConfig[key]
        else
            return {}
        end
    end
    return tbConfig
end


