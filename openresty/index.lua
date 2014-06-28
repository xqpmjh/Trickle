--[[
index example

@author kim https://github.com/xqpmjh
]]

--[[ default Content-Type ]]
ngx.header["Cache-Control"] = "no-cache"
ngx.header["Expires"] = "Mon, 20 Jul 1999 23:00:00 GMT"
ngx.header["Content-Type"] = 'text/html; charset=UTF-8'
ngx.header["lualu"] = '2014a'

--[[ libs ]]
local g                 = require "lib.g"
local indexModel        = require "model.index"
local config            = require "config_example"

--[[ ngx functions ]]
local exit              = ngx.exit
local unescape_uri      = ngx.unescape_uri

--[[----------------------------------------------------------------------]]

--[[ return data ]]
local data = {}
local act = ngx.var.arg_act and unescape_uri(ngx.var.arg_act) or ngx.var.act
if not act then exit(ngx.HTTP_BAD_REQUEST) end

local idx = indexModel:new({config = config:new()})

if act == 'index' then
    data = idx:getList()
end

g.outJsonCallback(data)

exit(ngx.HTTP_OK)


