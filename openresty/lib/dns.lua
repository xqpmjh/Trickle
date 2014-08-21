--[[
dns parser module

@author jinhuan.li https://github.com/lijinhuan
        kim https://github.com/xqpmjh
@version 1.0.0
]]

local cjson                 = require "cjson"
local resolver              = require "resty.dns.resolver"

local dict                  = require "lib.dict"
local g                     = require "lib.g"

local io                    = io
local string                = string
local type                  = type
local next                  = next
local pairs                 = pairs
local ipairs                = ipairs
local tonumber              = tonumber
local setmetatable          = setmetatable

local ngx                   = ngx
local header                = ngx.header

local log                   = g.log

--[[ init module ]]
module(...)
_VERSION = '1.0.0'

--[[ indexed by current module env. ]]
local mt = {__index = _M}

--[[-------------------------------------------------------------------------]]

--[[ instantiation ]]
function new(self)
    return setmetatable({
        RESOLV_FILE_PATH                = '/etc/resolv.conf',
        NAMESERVER                      = "nameserver",
        LCACHE_RESOLV_KEY               = 'key_etc_resolv',
        LCACHE_RESOLV_EXPIRES           = 10,
        LCACHE_RESOLV_ERR_EXPIRES       = 30, -- maybe bigger?
        RETRANS                         = 2,
        TIMEOUT                         = 500,
        LCACHE_DNS_ERROR                = 'key_dns_errip'
    }, mt)
end

--[[
@desc connect
@return obj|nil
]]
function _connect(self)
    if not self.connected then
        -- get all resolv ips
        local resolv_conf = self:_getResolvIps()
        if g.empty(resolv_conf) then
            log("resolv conf empty ....\n")
        else
            local err_key = self.LCACHE_DNS_ERROR
            local lcache = self:_getDict('lcache')
            if lcache then
                -- failed ips record
                local dns_err_ips = lcache:get(err_key)
                if not g.empty(dns_err_ips) then
                    dns_err_ips = cjson.decode(dns_err_ips)
                else
                    dns_err_ips = {}
                end

                -- try connect from the first dns
                local rsl, err
                local dns_ip_count = #resolv_conf
                for i = 1, dns_ip_count do
                    if g.in_array(resolv_conf[i], dns_err_ips) then
                        --nothing
                    else
                        rsl, err = resolver:new({
                            nameservers     = {resolv_conf[i]},
                            retrans         = self.RETRANS,
                            timeout         = self.TIMEOUT,
                        })
                        if not rsl then
                            -- prevent connectting the error dns
                            if not g.in_array(resolv_conf[i], dns_err_ips) then
                                table.insert(dns_err_ips, resolv_conf[i])
                            end
                        else
                            --ngx.say("dns_ip:"..resolv_conf[i].."<hr/>")
                            self.connected = rsl
                            break
                        end
                    end
                end
                if not rsl then
                    lcache:set(err_key, cjson.encode(dns_err_ips), self.LCACHE_RESOLV_ERR_EXPIRES)
                    log("failed to instantiate the resolver: ".. (err and err or ''))
                end
            end
        end
    end
    return self.connected
end

--[[
switch dns to ip address by /etc/resolv.conf file

@param string host
@return string
]]
function parse(self, host)
    local rs = host
    if type(host) == 'string' then
        -- check if host is a "real" host address
        if not host:match("(%d+)%.(%d+)%.(%d+)%.(%d+)") then
            local rsl = self:_connect()
            if rsl then 
                local answers, err = rsl:query(host)
                if not answers then
                    log("failed to query DNS : " .. host .. ' - ' .. (err and err or ''))
                    return
                end
                if answers.errcode then
                    log("server returned error code: " .. answers.errcode ..
                        ": " .. (answers.errstr and answers.errstr or ''))
                end
                -- @todo cache the query result?
                for i, ans in ipairs(answers) do
                    if ans.address then
                        rs = ans.address
                        break
                    end
                end 
            end
        end
    end
    return rs
end

--[[
获取resolv文件的内容
@return @table
]]
function _getResolvIps(self)
    -- try get cache
    local resolvIps
    local dkey = self.LCACHE_RESOLV_KEY
    local lcache = self:_getDict('lcache')
    if lcache then
        resolvIps = lcache:get(dkey)
        if not g.empty(resolvIps) then
            resolvIps = cjson.decode(resolvIps)
        end
    end
    -- try parse file
    local resolvfile = self.RESOLV_FILE_PATH
    if not resolvIps then
        local hf, err = io.open(resolvfile, 'r')
        header['-hfopn'] = 1
        if not hf then
            log('failed open resolv file: ' .. resolvfile .. ' - ' .. (err and err or ''))
        else
            resolvIps = {}
            for line in hf:lines() do
                -- try to remove the commentted
                local spos, epos = string.find(line, '#')
                if spos then
                    line = string.sub(line, 1, spos - 1)
                end
                if string.find(line, self.NAMESERVER) then
                    local gln = string.gsub(line, self.NAMESERVER, "")
                    if not g.empty(gln) then
                        resolvIps[#resolvIps + 1] = g.trim(gln)
                    end
                end
            end
            hf:close()
            lcache:set(self.LCACHE_RESOLV_KEY, cjson.encode(resolvIps), self.LCACHE_RESOLV_EXPIRES)
        end
    end
    return resolvIps
end

--[[
get local cache object
]]
function _getDict(self, dictionary)
    if not self.dict then
        self.dict = dict:new(dictionary)
    end
    return self.dict
end

--[[-------------------------------------------------------------------------]]

--[[ to prevent use of casual module global variables ]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME)
    end
})


