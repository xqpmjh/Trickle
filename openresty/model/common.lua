--[[
common corporation functions

@author kim https://github.com/xqpmjh
]]

local g                     = require "lib.g"
local table                 = table
local setmetatable          = setmetatable

local ngx                   = ngx
local log                   = g.log

--[[ init module ]]
module(...)
_VERSION = '1.0.0'

--[[-------------------------------------------------------------------------]]

--[[
description here
]]
function hello()
    print('hello')
end

--[[-------------------------------------------------------------------------]]

--[[ to prevent use of casual module global variables ]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME)
    end
})


