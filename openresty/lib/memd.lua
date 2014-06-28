--[[
lua-resty-memcached wrapped module

@author kim https://github.com/xqpmjh
@link https://github.com/agentzh/lua-resty-memcached
--]]

local g                     = require "lib.g"
local memcached             = require "resty.memcached"

local setmetatable          = setmetatable
local ngx                   = ngx
local print                 = ngx.print
local header                = ngx.header
local null                  = ngx.null

--[[ error logging --]]
local log                   = g.log

--[[ init module --]]
module(...)
_VERSION = '1.0.0'

--[[ indexed by current module env. --]]
local mt = {__index = _M}

--[[-------------------------------------------------------------------------]]

--[[
instantiation
]]
function new(self, cfg)
    local memd, err = memcached:new()
    if not memd then
        memd = log('failed to instantiate memcached: ' .. (err and err or ''))
    end
    return setmetatable({
        memd = memd,
        cfg = cfg,
    }, mt)
end

--[[
lazying connecting
@return 1|nil
]]
function connect(self)
    if not self.connected then
        local memd = self.memd
        local cfg = self.cfg
        --[[ no need to reconnect if refused ever once --]]
        if memd and not self.connect_refused then
            --[[ connect timeout --]]
            if cfg.connect_timeout then
                memd:set_timeout(cfg.connect_timeout)
            end

            local ok, err = memd:connect(cfg.host, cfg.port)
            if not ok then
                self.connect_refused = true
                log('failed connecting memcached: ' .. cfg.host 
                    .. ':' .. cfg.port .. ' - ' .. (err and err or ''))
            end
            self.connected = ok
        end
    end
    return self.connected
end

--[[
get
@return string|nil
--]]
function get(self, key)
    local memd, res = self.memd
    if memd then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = memd:get(key)
            if res == null then
                res = nil
            end
            if header and ngx.var.arg_dg == '1' then
                self.getcnt = self.getcnt and (self.getcnt + 1) or 1
                header['-memd-gc'] = self.getcnt
            end
        end
    end
    return res
end

--[[
set
@return bool
]]
function set(self, ...)
    local memd, res = self.memd
    if memd then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res = memd:set(...)
            if header and ngx.var.arg_dg == '1' then
                self.setcnt = self.setcnt and (self.setcnt + 1) or 1
                header['-memd-sc'] = self.setcnt
            end
        end
    end
    return res
end

--[[
delete
@param string key
@return int|nil - 0: timeout or object uninitialized
                  1: success or NOT_FOUND
                  nil: delete failed
]]
function delete(self, key)
    local memd = self.memd
    local res = 0
    if memd then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            res, self.lasterr = memd:delete(key)
            --[[ we consider NOT_FOUND as successed ]]
            if self.lasterr and self.lasterr == 'NOT_FOUND' then
                res = 1
            end
            if header and ngx.var.arg_dg == '1' then
                self.delcnt = self.delcnt and (self.delcnt + 1) or 1
                header['-memd-dc'] = self.delcnt
            end
        end
    end
    return res
end

--[[
keep some conns when try to close connection
]]
function close(self)
    local memd = self.memd
    local res = nil
    local err = ''
    if memd and self.connected then
        local cfg = self.cfg
        --[[ keepalive timeout and connection pool size --]]
        if cfg.max_idle_timeout and cfg.pool_size then
            res, err = memd:set_keepalive(cfg.max_idle_timeout, cfg.pool_size)
        else
            res, err = memd:close()
        end
        if res then
            self.connected = nil
        else
            log("unable to close memcached: " .. (err and err or ''))
        end
    end
    return res
end

--[[
Returns memcached server statistics information
]]
function stats(self, ...)
    local memd, res = self.memd
    if memd and self.connected then
        res = memd:stats(...)
    end
    return res
end

--[[-------------------------------------------------------------------------]]

--[[ to prevent use of casual module global variables --]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME)
    end
})


