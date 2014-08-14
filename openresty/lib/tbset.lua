--[[
table set object

@author kim https://github.com/xqpmjh
--]]

local g                     = require "lib.g"

local setmetatable          = setmetatable
local getmetatable          = getmetatable
local table                 = table
local type                  = type
local pairs                 = pairs
local print                 = ngx.print

--[[ error logging --]]
local log                   = g.log

--[[ init module --]]
module(...)
_VERSION = '1.0.0'

--[[ indexed by current module env. --]]
local mt = {__index = _M}

--[[-------------------------------------------------------------------------]]


--[[ instantiation --]]
function new(self, tb)
    local set = {}
    setmetatable(set, mt)
    for _, v in pairs(tb) do
        set[v] = true
    end
    return set
end

--[[ union --]]
mt.__add = function(s1, s2)
    local res = new(self, {})
    if getmetatable(s1) ~= mt or getmetatable(s2) ~= mt then
        log("attempt to 'add' a set with a non-set value")
    else
        for k in pairs(s1) do
            res[k] = true
        end
        for k in pairs(s2) do
            res[k] = true
        end
    end
    return res
end

--[[ intersection --]]
mt.__mul = function(s1, s2)
    local res = new(self, {})
    if getmetatable(s1) ~= mt or getmetatable(s2) ~= mt then
        log("attempt to 'mul' a set with a non-set value")
    else
        for k in pairs(s1) do
            res[k] = s2[k]
        end
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


