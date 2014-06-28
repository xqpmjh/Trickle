--[[
syntax: lua_shared_dict <name> <size>

Declares a shared memory zone, <name>, to serve as storage for the shm based Lua dictionary ngx.shared.<name>.
The <size> argument accepts size units such as k and m:
http {
    lua_shared_dict lcache 64m;
    ...
}

@see http://wiki.nginx.org/HttpLuaModule#ngx.shared.DICT
@author kim https://github.com/xqpmjh
]]

--[[ error logging ]]
local g                     = require('lib.g')
local log                   = g.log

local type                  = type
local setmetatable          = setmetatable

local ngx                   = ngx
local shared                = ngx.shared
local print                 = ngx.print
local header                = ngx.header
local null                  = ngx.null

--[[ init module ]]
module(...)
_VERSION = '1.0.0'

--[[ indexed by current module env. ]]
local mt = {__index = _M}

--[[-------------------------------------------------------------------------]]

--[[ instantiation ]]
function new(self, dictionary)
    local dict = shared[dictionary]
    if not dict then
        log('failed to instantiate, make sure you had defined ' ..
            '"lua_shared_dict ' .. dictionary .. '" in your http section!')
    end
    return setmetatable({
        dict = dict
    }, mt)
end

--[[
get value
@param string key
@return mixed, int - booleans, numbers, strings, or nil
                     flags could be int
]]
function get(self, key)
    if not key then
        log('invalid shared memory key')
    end
    local dict, res, flags = self.dict
    if dict then
        local r, err = dict:get(key)
        if r == null then
            r = nil
        end
        if not r and err and type(err) == 'string' then
            log('failed fetching shared memory by ' .. key .. ': ' .. err)
        end

        if header and ngx.var.arg_dg == '1' then
            self.getcnt = self.getcnt and (self.getcnt + 1) or 1
            header['-' .. _NAME .. '-getc'] = self.getcnt
        end
        res = r
        flags = err
    end
    return res, flags
end

--[[
set value
@param string key
@param string mixed - booleans, numbers, strings, or nil
@param int exptime - int seconds, 0 which is the default means never be expired.
@param int flags - a user flags value associated with the entry to be stored
@return nil|boolean
]]
function set(self, key, value, exptime, flags)
    if not key then
        log('invalid shared memory key')
    end
    local exptime = exptime or 0
    local flags = flags or 0
    local dict, res = self.dict
    if dict then
        local r, err = dict:set(key, value, exptime, flags)
        if not r and err and type(err) == 'string' then
            log('failed set shared memory, key:' .. key .. ', value:' .. value
                .. ', exptime:' .. exptime .. ', flags:' .. flags)
        end
        if header and ngx.var.arg_dg == '1' then
            self.setcnt = self.setcnt and (self.setcnt + 1) or 1
            header['-' .. _NAME .. '-setc'] = self.setcnt
        end
        res = r
    end
    return res
end

--[[
delete value
@param string key
@return int|nil
]]
function delete(self, key)
    local dict = self.dict
    local res
    if dict then
        res = dict:delete(key)
        if header and ngx.var.arg_dg == '1' then
            self.delcnt = self.delcnt and (self.delcnt + 1) or 1
            header['-' .. _NAME .. '-delc'] = self.delcnt
        end
    end
    return res
end

--[[ keep some conns when try to close connection ]]
function close(self)
    self.dict = nil
    return true
end

--[[-------------------------------------------------------------------------]]

--[[ to prevent use of casual module global variables ]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME)
    end
})


