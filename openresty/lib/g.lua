--[[
common global functions

@author kim https://github.com/xqpmjh
@version 1.0.0
]]

local cjson                 = require "cjson"

local io                    = io
local table                 = table
local string                = string
local math                  = math
local type                  = type
local next                  = next
local pairs                 = pairs
local ipairs                = ipairs
local tonumber              = tonumber
local setmetatable          = setmetatable

local ngx                   = ngx

--[[ init module ]]
module(...)
_VERSION = '1.0.0'

--[[-------------------------------------------------------------------------]]

--[[ error logging ]]
local logsUniquify = {}
function log(message, level)
    if ngx and type(ngx.log) == 'function' then
        local level = level or ngx.EMERG
        if not logsUniquify[level .. message] then
            ngx.log(level, message)
            logsUniquify[level .. message] = true
        end
    end
    return nil
end

--[[
string trim
@param string s
@return string
--]]
function trim(s)
    if type(s) == 'string' then
        local match = string.match
        return match(s,'^()%s*$') and '' or match(s,'^%s*(.*%S)')
    end
    return s
end

--[[
check if obj is empty
@return bool
]]
function empty(obj)
    if not obj or obj == '' or obj == 0 then
        return true
    elseif type(obj) == 'table' and next(obj) == nil then
        return true
    else
        return false
    end
end

--[[
get length of table
@return int
]]
function length(tbl)
    local count = 0
    if type(tbl) == 'table' then
        for _ in pairs(tbl) do count = count + 1 end
    end
    return count
end

--[[
escape string magic characters
@return string|nil
]]
function escapeMagic(s)
    local rs
    if type(s) == 'string' then
        rs = (s:gsub('[%-%.%+%[%]%(%)%$%^%%%?%*]', '%%%1'):gsub('%z', '%%z'))
    end
    return rs
end

--[[
explode string by delimiter, with result uniquify
@return table
]]
function explode(delimiter, str, filter)
    local rs = {}
    if delimiter and type(delimiter) == 'string' and str and type(str) == 'string' then
        local callback = function(w)
            if filter and type(filter) == 'function' then
                w = filter(w)
            end
            if w then
                table.insert(rs, w)
            end
        end
        string.gsub(str, delimiter, callback)
    end
    return rs
end

--[[
Exchanges all keys with their associated values in an table
@return table|nil
]]
function array_flip(tbl)
    local rs = tbl
    if type(tbl) == 'table' then
        rs = {}
        for k,v in pairs(tbl) do
            rs[v] = k
        end
    end
    return rs
end

--[[
This function will return a new table containing values from the original
table (array) in a shuffled/random order. It is recommended to call math.
randomseed(os.time()) at the program start to get different results on every run
@param table tbl
@return table
]]
function shuffle(tbl)
    local rs
    if type(tbl) == 'table' and next(tbl) ~= nil then
        rs = {}
        local order = {}
        local n = #tbl
        for i = 1, n do
            order[i] = {rnd = math.random(), idx = i}
        end
        table.sort(order, function(a,b) return a.rnd < b.rnd end)
        for i = 1, n do
            rs[i] = tbl[order[i].idx]
        end
    end
    return rs
end

--[[
array_rand — Pick one or more random entries out of an array
@return table|nil
]]
function array_rand(tbl, m)
    local rs
    if type(tbl) == 'table' and next(tbl) ~= nil then
        rs = {}
        local order = {}
        local n = #tbl
        for i = 1, n do
            order[i] = {rnd = math.random(), idx = i}
        end
        table.sort(order, function(a,b) return a.rnd < b.rnd end)
        for i = 1, m do
            if order[i] then
                rs[i] = order[i].idx
            end
        end
    end
    return rs
end

--[[
return the intersection which 'key' exists in t1 but not in t2
@return table|nil
]]
function array_intersect_key(t1, t2)
    local rs = t1
    if type(t1) == 'table' and type(t2) == 'table' then
        rs = {}
        for k,v in pairs(t1) do
            if t2[k] ~= nil then
                rs[k] = v
            end
        end
    end
    return rs
end

--[[
check if two tables are the same
@return bool
]]
function deepcompare(t1, t2)
    local ty1 = type(t1)
    local ty2 = type(t2)
    if ty1 ~= ty2 then return false end
    -- non-table types can be directly compared
    if ty1 ~= 'table' and ty2 ~= 'table' then return t1 == t2 end
    -- as well as tables which have the metamethod __eq
    for k1,v1 in pairs(t1) do
        local v2 = t2[k1]
        if v2 == nil or not deepcompare(v1,v2) then return false end
    end
    for k2,v2 in pairs(t2) do
        local v1 = t1[k2]
        if v1 == nil or not deepcompare(v1,v2) then return false end
    end
    return true
end

--[[
create table sorting iterator
@return function
]]
function pairsByKeys(tb, f)
    local a = {}
    for n in pairs(tb) do table.insert(a, n) end
    table.sort(a, f)
    local i = 0 -- iterator variable
    local iter = function() -- iterator function
        i = i + 1
        if a[i] == nil then return nil
        else return a[i], tb[a[i]]
        end
    end
    return iter
end

--[[
sort table by value
@example {aa = 1, bb = 3, cc = 2} => {{aa, 1}, {cc, 2}, {bb, 3}}
@return nil|table
]]
function sortAssoc(tb, order, limit)
    local rs = nil
    if type(tb) == 'table' then
        rs = {}
        local tmp = {}
        for k,v in pairs(tb) do
            table.insert(tmp, {key = k, val = tonumber(v)})
        end
        if next(tmp) ~= nil then
            if order and order == 'DESC' then
                table.sort(tmp, function(a, b) return b.val < a.val end)
            else
                table.sort(tmp, function(a, b) return b.val > a.val end)
            end
        end
        for i,v in ipairs(tmp) do
            table.insert(rs, {v['key'], v['val']})
            if limit and (i >= limit) then break end
        end
    end
    return rs
end

--[[
Convert special characters to HTML entities
'&' (ampersand) becomes '&amp;'
'"' (double quote) becomes '&quot;'
"'" (single quote) becomes '&#039;' (or &apos;)
'<' (less than) becomes '&lt;'
'>' (greater than) becomes '&gt;'
@return string
]]
function htmlspecialchars(str)
    local rs = str or nil
    if rs and type(rs) == 'string' then
        rs = string.gsub(rs, '&', '&amp;')
        rs = string.gsub(rs, '"', '&quot;')
        rs = string.gsub(rs, "'", '&#039;')
        rs = string.gsub(rs, '<', '&lt;')
        rs = string.gsub(rs, '>', '&gt;')
    end
    return rs
end

--[[
all characters which have HTML character entity equivalents
are translated into these entities.
@return string
]]
function htmlentities(str)
    if type(str) ~= "string" then
        return nil
    end

    local entities = {
        --[' '] = '&nbsp;' ,
        ['¡'] = '&iexcl;' ,
        ['¢'] = '&cent;' ,
        ['£'] = '&pound;' ,
        ['¤'] = '&curren;',
        ['¥'] = '&yen;' ,
        ['¦'] = '&brvbar;' ,
        ['§'] = '&sect;' ,
        ['¨'] = '&uml;' ,
        ['©'] = '&copy;' ,
        ['ª'] = '&ordf;' ,
        ['«'] = '&laquo;' ,
        ['¬'] = '&not;' ,
        ['­'] = '&shy;' ,
        ['®'] = '&reg;' ,
        ['¯'] = '&macr;' ,
        ['°'] = '&deg;' ,
        ['±'] = '&plusmn;' ,
        ['²'] = '&sup2;' ,
        ['³'] = '&sup3;' ,
        ['´'] = '&acute;' ,
        ['µ'] = '&micro;' ,
        ['¶'] = '&para;' ,
        ['·'] = '&middot;' ,
        ['¸'] = '&cedil;' ,
        ['¹'] = '&sup1;' ,
        ['º'] = '&ordm;' ,
        ['»'] = '&raquo;' ,
        ['¼'] = '&frac14;' ,
        ['½'] = '&frac12;' ,
        ['¾'] = '&frac34;' ,
        ['¿'] = '&iquest;' ,
        ['À'] = '&Agrave;' ,
        ['Á'] = '&Aacute;' ,
        ['Â'] = '&Acirc;' ,
        ['Ã'] = '&Atilde;' ,
        ['Ä'] = '&Auml;' ,
        ['Å'] = '&Aring;' ,
        ['Æ'] = '&AElig;' ,
        ['Ç'] = '&Ccedil;' ,
        ['È'] = '&Egrave;' ,
        ['É'] = '&Eacute;' ,
        ['Ê'] = '&Ecirc;' ,
        ['Ë'] = '&Euml;' ,
        ['Ì'] = '&Igrave;' ,
        ['Í'] = '&Iacute;' ,
        ['Î'] = '&Icirc;' ,
        ['Ï'] = '&Iuml;' ,
        ['Ð'] = '&ETH;' ,
        ['Ñ'] = '&Ntilde;' ,
        ['Ò'] = '&Ograve;' ,
        ['Ó'] = '&Oacute;' ,
        ['Ô'] = '&Ocirc;' ,
        ['Õ'] = '&Otilde;' ,
        ['Ö'] = '&Ouml;' ,
        ['×'] = '&times;' ,
        ['Ø'] = '&Oslash;' ,
        ['Ù'] = '&Ugrave;' ,
        ['Ú'] = '&Uacute;' ,
        ['Û'] = '&Ucirc;' ,
        ['Ü'] = '&Uuml;' ,
        ['Ý'] = '&Yacute;' ,
        ['Þ'] = '&THORN;' ,
        ['ß'] = '&szlig;' ,
        ['à'] = '&agrave;' ,
        ['á'] = '&aacute;' ,
        ['â'] = '&acirc;' ,
        ['ã'] = '&atilde;' ,
        ['ä'] = '&auml;' ,
        ['å'] = '&aring;' ,
        ['æ'] = '&aelig;' ,
        ['ç'] = '&ccedil;' ,
        ['è'] = '&egrave;' ,
        ['é'] = '&eacute;' ,
        ['ê'] = '&ecirc;' ,
        ['ë'] = '&euml;' ,
        ['ì'] = '&igrave;' ,
        ['í'] = '&iacute;' ,
        ['î'] = '&icirc;' ,
        ['ï'] = '&iuml;' ,
        ['ð'] = '&eth;' ,
        ['ñ'] = '&ntilde;' ,
        ['ò'] = '&ograve;' ,
        ['ó'] = '&oacute;' ,
        ['ô'] = '&ocirc;' ,
        ['õ'] = '&otilde;' ,
        ['ö'] = '&ouml;' ,
        ['÷'] = '&divide;' ,
        ['ø'] = '&oslash;' ,
        ['ù'] = '&ugrave;' ,
        ['ú'] = '&uacute;' ,
        ['û'] = '&ucirc;' ,
        ['ü'] = '&uuml;' ,
        ['ý'] = '&yacute;' ,
        ['þ'] = '&thorn;' ,
        ['ÿ'] = '&yuml;' ,
        ['"'] = '&quot;' ,
        ["'"] = '&#39;' ,
        ['<'] = '&lt;' ,
        ['>'] = '&gt;' ,
        ['&'] = '&amp;'
    }

    local ret = str:gsub("(.)", entities)
    return ret
end

--[[
scan directory
@param string directory
@return table
]]
function scandir(directory)
    local i, t, popen = 0, {}, io.popen
    for filename in popen('ls -a "' .. directory .. '"'):lines() do
        i = i + 1
        t[i] = filename
    end
    return t
end

--[[
output json with/without callback
@param table data
@return void - print result
]]
function outJsonCallback(data)
    if type(data) == 'table' and next(data) ~= nil then
        local jsonData
        local arg_callback = ngx.var.arg_callback and
                                ngx.unescape_uri(ngx.var.arg_callback) or nil
        arg_callback = htmlspecialchars(arg_callback)
        if arg_callback then
            jsonData = cjson.encode(data)
            ngx.print(arg_callback .. '(' .. jsonData .. ')')
        else
            jsonData = cjson.encode(data)
            ngx.print(jsonData)
        end
    end
end

--[[
exit 输出变量并退出
@param mixed obj
@return void
]]
function outExit(obj)
    if type(obj) == 'table' then
        printTab(obj)
	else
		ngx.print(obj)
    end
	ngx.exit(200)
end

--[[
打印table
@param table tab
]]
function printTab(tab)
    if type(tab) == 'table' then
        for k,v in pairs(tab) do
	        if type(v) == 'table' then
			    ngx.print(k .. ' : ' .. '(')
			    printTab(v)
			    ngx.print(')<br/>')
		    else
			    ngx.print(k .. ' : ' .. v)
		    end
        end
    end
end

--[[
在 haystack 中搜索 needle，如果找到则返回 true，否则返回 false
@param mixed needle
@param table haystack
@return bool
]]
function in_array(needle, haystack)
    local res = false
    if type(haystack) == 'table' then
        for k,v in pairs(haystack) do
            if needle == v then
                res = true
                break
            end
        end
    end
    return res
end

--[[
url 编码
]]
function decodeURI(str)
    str = string.gsub(str, '%%(%x%x)', function(h) return string.char(tonumber(h, 16)) end)
    return str
end

--[[
url 解码
]]
function encodeURI(str)
    str = string.gsub(str, "([^%w%.%- ])", function(c) return string.format("%%%02X", string.byte(c)) end)
    return string.gsub(str, " ", "+")
end

--[[-------------------------------------------------------------------------]]

--[[ to prevent use of casual module global variables ]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME)
    end
})


