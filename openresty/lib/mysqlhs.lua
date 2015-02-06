--[[
mysql handlersocket model

handlersocket:
https://github.com/DeNA/HandlerSocket-Plugin-for-MySQL/blob/master/docs-en/protocol.en.txt
now support open_index,find,find_modify,insert,auth

@author kim https://github.com/xqpmjh
@link https://github.com/zhhchen/lua-resty-handlersocket
]]

local cjson                 = require "cjson"
local handlersocket         = require "lib.handlersocket"
local g                     = require "lib.g"
local hosts                 = require "lib.hosts"

local string                = string
local table                 = table
local setmetatable          = setmetatable
local type                  = type
local tostring              = tostring
local tonumber              = tonumber
local pairs                 = pairs
local next                  = next

local ngx                   = ngx
local print                 = ngx.print
local header                = ngx.header
local quote_sql_str         = ngx.quote_sql_str
local null                  = ngx.null

--[[ error logging ]]
local log                   = g.log

--[[ init module ]]
module(...)
_VERSION = '1.0.0'

--[[ indexed by current module env. --]]
local mt = {__index = _M}

--[[-------------------------------------------------------------------------]]

--[[
instantiation
]]
function new(self, cfg)
    local hs, err = handlersocket:new()
    --hs = nil
    if not hs then
        hs = log('failed to instantiate ' .. _NAME .. ': ' .. (err and err or ''))
    end
    return setmetatable({
        hs  = hs,
        cfg = cfg,
    }, mt)
end

--[[
lazying connecting
@return 1|nil
--]]
function connect(self)
    if not self.connected then
        local hs = self.hs
        local cfg = self.cfg
        if hs and not self.connect_refused then
            --[[ connect timeout --]]
            hs:set_timeout(cfg.connect_timeout)

            local hosts = hosts:new()
            local host = hosts:parse(cfg.host)

            local ok, err = hs:connect(host, cfg.port)
            if not ok then
                self.connect_refused = true
                log('failed connecting ' .. _NAME .. ': ' .. cfg.host .. 
                    ':' .. cfg.port .. ' - ' .. (err and err or ''))
            else
                -- database for finding
                hs.dbname = cfg.database
                -- show connection counts
                if header and ngx.var.arg_dg == '1' then
                    local fname = cfg.fname and cfg.fname or ''
                    self.ccnt = self.ccnt and (self.ccnt + 1) or 1
                    header['-' .. fname .. '-conn'] = self.ccnt
                end
            end
            self.connected = ok
        end
    end
    return self.connected
end

--[[
auth
@return int|nil, string|nil
]]
function auth(self)
    local hs, rs = self.hs
    if hs then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            local cfg = self.cfg
            if cfg.password then
                local ok, err = hs:auth({1, cfg.password})
                if not ok then
                    log("failed to auth: " .. (err and err or ''))
                else
                    rs = ok
                    if header and ngx.var.arg_dg == '1' then
                        local fname = cfg.fname and cfg.fname or ''
                        self.acnt = self.acnt and (self.acnt + 1) or 1
                        header['-' .. fname .. '-auth'] = self.acnt
                    end
                end
            end
        end
    end
    return rs
end

--[[
open index
@param string tablename
@param string columns
@return int|nil - indexid
]]
function open(self, tablename, indexname, columns)
    local hs, rs = self.hs
    if hs then
        if not tablename or not indexname or not columns then
            log("failed open " .. _NAME .. ": " .. tostring(tablename) ..
                    ' - ' .. tostring(columns))
        end
        if not self.connected then
            self:connect()
        end
        if self.connected then
            hs.indexid = hs.indexid and tostring(tonumber(hs.indexid) + 1) or '1'
            --print(hs.indexid, hs.dbname, tablename, indexname, columns) ngx.exit(200)
            local ok, err = hs:open_index({hs.indexid, hs.dbname, tablename, indexname, columns})
            if ok then
                rs = hs.indexid
            else
                log("failed open " .. _NAME .. ": " .. tostring(tablename) ..
                    ' - ' .. tostring(columns) .. ' - ' .. (err and err or ''))
            end
        end
    end
    return rs
end

--[[
find
@return table|nil
--]]
function find(self, ...)
    local hs, rs = self.hs
    if hs then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            local ok, err = hs:find({...})
            if not ok then
                log("failed to find: " .. (err and err or ''))
            else
                if header and ngx.var.arg_dg == '1' then
                    local cfg = self.cfg
                    local fname = cfg.fname and cfg.fname or ''
                    self.fcnt = self.fcnt and (self.fcnt + 1) or 1
                    header['-' .. fname .. '-fnd'] = self.fcnt
                end
            end
            rs = ok
        end
    end
    return rs
end

--[[
insert
@return bool
]]
function insert(self, ...)
    local hs, res = self.hs
    if hs then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            local ok, err = hs:insert({...})
            if not ok then
                log("failed to insert: " .. (err and err or '') .. ": {" .. ...  .. "}")
            else
                if header and ngx.var.arg_dg == '1' then
                    local cfg = self.cfg
                    local fname = cfg.fname and cfg.fname or ''
                    self.icnt = self.icnt and (self.icnt + 1) or 1
                    header['-' .. fname .. '-ins'] = self.icnt
                end
            end
            res = ok
        end
    end
    return res
end

--[[
close connection
@return 1|nil, string - return number 1 on success
--]]
function close(self)
    local hs = self.hs
    local res = nil
    local err = ''
    if hs and self.connected then
        local cfg = self.cfg
        if cfg.max_idle_timeout and cfg.pool_size then
            res, err = hs:set_keepalive(cfg.max_idle_timeout, cfg.pool_size)
            if not res then
                log("failed to set " .. _NAME .. " keepalive: " .. (err and err or ''))
            end
        else
            res, err = db:close()
            if not res then
                log("failed to close " .. _NAME .. ": " .. ' - ' .. (err and err or ''))
            end
        end
        if res then
            self.connected = nil
        end
    end
    return res, err
end

--[[-------------------------------------------------------------------------]]

--[[ to prevent use of casual module global variables --]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME)
    end
})



