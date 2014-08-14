--[[
index model
index 模型

@description below is the example of:
    getting data from api
    getting data from mysql
    set/get data from memcached
    set/get data from local cache
    return data

@author kim https://github.com/xqpmjh
--]]

local cjson                 = require "cjson"

local mysql                 = require "lib.mysql"
local http                  = require "lib.http"
local memd                  = require "lib.memd"
local dict                  = require "lib.dict"
local neturl                = require "lib.neturl"
local xssfilter             = require "lib.xssfilter"
local g                     = require "lib.g"

local cm                    = require "model.common"

local os                    = os
local table                 = table
local string                = string
local math                  = math

local setmetatable          = setmetatable
local type                  = type
local pairs                 = pairs
local ipairs                = ipairs
local tostring              = tostring
local tonumber              = tonumber
local next                  = next
local unpack                = unpack

local ngx                   = ngx
local print                 = ngx.print
local header                = ngx.header
local unescape_uri          = ngx.unescape_uri

--[[ error logging ]]
local log                   = g.log

--[[ init module ]]
module(...)

--[[ indexed by current module env. ]]
local mt = {__index = _M}

--[[----------------------------------------------------------------]]

--[[
instantiation
@return table
]]
function new(self, utils)
    if not utils.config then
        log("utils configs missing!")
        return
    end
    return setmetatable({
        config = utils.config,
    }, mt)
end

--[[
get list
@return table
]]
function getList(self)
    local rs

    local data = self:_wrapMemdCache('testkey1', 60, function ()
        return self:_getData()
    end)

    local data2 = self:_getData2()

    local data3 = self:_wrapMemdCache('testkey3', 60, function ()
        return self:_getData3()
    end)

    rs = {data = data, data2 = data2, data3 = data3}

    return rs
end

--[[ below is private functions -------------------------------------]]

--[[
wrap memcached cachings
@param string mkey
@param int expires
@param userdata func
@return mixed|nil
]]
function _wrapMemdCache(self, mkey, expires, func)
    local rs
    if mkey and expires and func then
        local memd = self:_getMemd()
        if self:_checkIpAllowed() and ngx.var.arg_mm == 'clear' then
            memd:delete(mkey)
        else
            rs = memd:get(mkey)
            if not g.empty(rs) then
                rs = cjson.decode(rs)
            end
        end
        if not rs then
            rs = func()
            if rs then
                memd:set(mkey, cjson.encode(rs), expires)
            end
        end
    end
    return rs
end

--[[
get data from api
@return table|nil
]]
function _getData(self)
    local rs
    local hc = self:_getHttpObj()
    local host = 'api.example.com'
    local uri = '/getdata?num=10'
    rs = hc:get(host, uri)
    if not g.empty(rs) then
        rs = cjson.decode(rs)
    end
    return rs
end

--[[
get/set data from local cache
@return table|nil
]]
function _getData2(self)
    local rs
    local lcache = self:_getDict('lcache')
    if lcache then
        if self:_checkIpAllowed() and ngx.var.arg_lc == 'clear' then
            lcache:delete('keydata')
        else
            rs = lcache:get('keydata')
        end
    end
    if not rs then
        local hc = self:_getHttpObj()
        rs = hc:get('api.example.com', '/getdata2?num=10')
        if not g.empty(rs) then
            lcache:set('keydata', rs, 60)
        end
    end
    return rs
end

--[[
get/set data from local cache
@return table|nil
]]
function _getData3(self)
    local rs
    local dbtest = self:_getDbTest()
    if dbtest then
        local sql = "SELECT * FROM test WHERE 1 LIMIT 1"
        rs = dbtest:findOne(sql)
    end
    return rs
end

--[[
check if remote_addr is allowed
@return bool
]]
function _checkIpAllowed()
    local rs = false
    local remote_addr = ngx.var.remote_addr
    local ipWhiteList = {
        ['127.0.0.1'] = true
    }
    if remote_addr and ipWhiteList[remote_addr] then
        rs = true
    end
    return rs
end

--[[
get http object
]]
function _getHttpObj(self)
    if not self.hc then
        self.hc = http:new('example_com')
    end
    return self.hc
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

--[[
get memcached object
@return table
--]]
function _getMemd(self)
    if not self.memd then
        local config = self.config
        if config and config:get('memcached') then
            self.memd = memd:new(config:get('memcached'))
        else
            log("memcached config missing!")
        end
    end
    return self.memd
end

--[[
get db instance
@return table|nil
]]
function _getDbTest(self)
    if not self.dbtest then
        local config = self.config
        if config and config:get('mysql').db_test then
            self.dbtest = mysql:new(config:get('mysql').db_test)
        else
            log("db config missing!")
        end
    end
    return self.dbtest
end

--[[----------------------------------------------------------------]]

--[[ to prevent use of casual module global variables --]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. 
            '" in ' .. table._NAME)
    end
})


