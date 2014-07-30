--[[
lua-resty-mysql wrapped module

@author kim https://github.com/xqpmjh
@link https://github.com/agentzh/lua-resty-mysql
]]

local cjson                 = require "cjson"
local mysql                 = require "resty.mysql"
local hosts                 = require "lib.hosts"
local g                     = require "lib.g"

local string                = string
local table                 = table
local setmetatable          = setmetatable
local type                  = type
local tostring              = tostring
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

--[[ indexed by current module env. ]]
local mt = {__index = _M}

--[[-------------------------------------------------------------------------]]

--[[ instantiation ]]
function new(self, cfg)
    local db, err = mysql:new()
    if not db then
        db = log('failed to instantiate mysql: ' .. (err and err or ''))
    end
    return setmetatable({
        db  = db,
        cfg = cfg,

        ERROR_NOT_FOUND = 'NOT_FOUND'
    }, mt)
end

--[[
lazying connecting
@return 1|nil
]]
function connect(self)
    if not self.connected then
        local db = self.db
        local cfg = self.cfg
        if db and not self.connect_refused then
            -- connect timeout
            db:set_timeout(cfg.connect_timeout)

            local hosts = hosts:new()
            local host = hosts:parse(cfg.host)

            local ok, err, errno, sqlstate = db:connect({
                host                = host,
                port                = cfg.port,
                database            = cfg.database,
                user                = cfg.user,
                password            = cfg.password,
                max_packet_size     = cfg.max_packet_size,
            })
            if not ok then
                self.connect_refused = true
                log("failed connecting mysql: " .. cfg.host .. 
                    ':' .. cfg.port .. ' - ' .. (err and err or '') .. 
                    " - " .. (errno and errno or '') .. 
                    " - " .. (sqlstate and sqlstate or ''))
            else
                local charset = cfg.charset or 'utf8'
                db:query("SET NAMES " .. charset)
                -- show connection counts
                if header and ngx.var.arg_dg == '1' then
                    local fname = cfg.fname and cfg.fname or ''
                    self.ccnt = self.ccnt and (self.ccnt + 1) or 1
                    header['_msc_' .. fname] = self.ccnt
                end
            end
            self.connected = ok
        end
    end
    return self.connected
end

--[[
bind the sql with params
@param string sql
@param table binds
@return string
]]
function bind(self, sql, binds)
    if type(sql) == 'string' and type(binds) == 'table' then
        for k,v in pairs(binds) do
            sql = string.gsub(sql, ':' .. k, quote_sql_str(v))
        end
    end
    return sql
end

--[[
query
@return table|nil
]]
function query(self, sql, binds)
    local db, res = self.db
    if db then
        if not self.connected then
            self:connect()
        end
        if self.connected then
            if binds then
                sql = self:bind(sql, binds)
            end
            self.lastsql = sql

            local ok, err, errno, sqlstate = db:query(sql)
            if not ok then
                log("failed to query mysql: " .. (err and err or '') .. ": " 
                    .. (errno and errno or '') .. " " 
                    .. (sqlstate and sqlstate or ''))
            else
                if header and ngx.var.arg_dg == '1' then
                    local cfg = self.cfg
                    local fname = cfg.fname and cfg.fname or ''
                    self.qcnt = self.qcnt and (self.qcnt + 1) or 1
                    header['_msq_' .. fname] = self.qcnt
                end
            end
            res = ok
        end
    end
    return res
end

--[[
get the first result
@return table|null|nil, err
        table:  success
        null:   not found
        nil:    failed or connect error
        err:    error message
]]
function findOne(self, sql, binds)
    local res, err = self:query(sql, binds)
    if res and type(res) == 'table' then
        res = table.remove(res, 1)
        if not res or res == null then
            err = self.ERROR_NOT_FOUND
            res = nil
        end
    elseif res == null then
        err = self.ERROR_NOT_FOUND
    end
    self.lasterr = err
    return res, err
end

--[[
update one record

result could be:
{"insert_id":0,"server_status":2,"warning_count":0,"affected_rows":1,"message":"(Rows matched: 1 Changed: 0 Warnings: 0"}

@return int|nil - affected rows
]]
function updateOne(self, tbName, data, where, binds)
    local res
    if tbName and type(data) == 'table' and next(data) ~= nil then
        local setCond = {}
        for k,v in pairs(data) do
            table.insert(setCond, '`' .. k .. '` = ' .. ':' .. k)
        end
        setCond = table.concat(setCond, ', ')
        if where then
            where = self:bind(where, binds)
        else
            where = '1'
        end
        local sql = 'UPDATE `' .. tbName .. '` SET ' .. setCond ..
                    ' WHERE ' .. where .. ' LIMIT 1'
        local rs = self:query(sql, data)
        if rs and type(rs) == 'table' then
            res = rs['affected_rows']
        end
    end
    return res
end

--[[
close connection
@return 1|nil, string - return number 1 on success
]]
function close(self)
    local db = self.db
    local res = nil
    local err = ''
    if db and self.connected then
        local cfg = self.cfg
        if cfg.max_idle_timeout and cfg.pool_size then
            res, err = db:set_keepalive(cfg.max_idle_timeout, cfg.pool_size)
            if not res then
                log("failed to set mysql keepalive: " .. (err and err or ''))
            end
        else
            res, err = db:close()
            if not res then
                log("failed to close mysql: " .. ' - ' .. (err and err or ''))
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


