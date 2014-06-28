--[[
http module

@author kim https://github.com/xqpmjh
@link https://github.com/liseen/lua-resty-http
]]

local g                     = require "lib.g"
local hosts                 = require "lib.hosts"

local setmetatable          = setmetatable
local tonumber              = tonumber
local tostring              = tostring
local string                = string
local type                  = type

local ngx                   = ngx
local print                 = ngx.print
local header                = ngx.header
local tcp                   = ngx.socket.tcp

--[[ error logging ]]
local log                   = g.log

--[[ init module ]]
module(...)
_VERSION = '1.0.0'

--[[ indexed by current module env. ]]
local mt = {__index = _M}

--[[-------------------------------------------------------------------------]]

--[[
instantiation
@return table
]]
function new(self, request_from)
    return setmetatable({
        REQUEST_FROM = request_from
    }, mt)
end

--[[
receive status line
@return int|nil, string
--]]
local function receivestatusline(sock)
    local status_reader = sock:receiveuntil("\r\n")
    local data, err, partial = status_reader()
    if not data then
        return nil, "read status line failed 001 " .. err
    end
    local t1, t2, code
    if data ~= '' then
        if string.find(data, "HTTP/") then
            t1, t2, code = string.find(data, "HTTP/%d*%.%d* (%d%d%d)")
        else
            code = 200
        end
    end
    return tonumber(code), data
end

--[[
check if should receive body
--]]
local function shouldreceivebody(code)
    if code == 204 or code == 304 then return nil end
    if code >= 100 and code < 200 then return nil end
    return 1
end

--[[
read body data
--]]
local function read_body_data(sock, max_size, fetch_size, callback)
    local p_size = fetch_size
    while max_size and max_size > 0 do
        if max_size < p_size then
            p_size = max_size
        end
        local data, err, partial = sock:receive(p_size)
        if not err then
            if data then
                callback(data)
            end
        elseif err == "closed" then
            if partial then
                callback(partial)
            end
            return 1 -- 'closed'
        else
            return nil, err
        end
        max_size = max_size - p_size
    end
    return 1
end

--[[
receive body
]]
local function receivebody(sock, headers, nreqt)
    local t = headers["transfer-encoding"] -- shortcut
    local body = ''
    local callback = nreqt.body_callback
    if not callback then
        local function bc(data, chunked_header, ...)
            if chunked_header then return end
            body = body .. data
        end
        callback = bc
    end
    if t and t ~= "identity" then
        -- chunked
        while true do
            local chunk_header = sock:receiveuntil("\r\n")
            local data, err, partial = chunk_header()
            if not err then
                if data == "0" then
                    return body -- end of chunk
                else
                    local length = tonumber(data, 16)
                    local ok, err = read_body_data(sock,length, nreqt.fetch_size, callback)
                    if err then
                        return nil,err
                    end
                end
            end
        end
    elseif headers["content-length"] ~= nil and headers["content-length"] ~= "0" then
        -- content length
        local length = tonumber(headers["content-length"])
        if length > nreqt.max_body_size then
            ngx.log(ngx.INFO, 'content-length > nreqt.max_body_size !! Tail it !')
            length = nreqt.max_body_size
        end

        local ok, err = read_body_data(sock,length, nreqt.fetch_size, callback)
        if not ok then
            return nil,err
        end
    else
        -- connection close
        local ok, err = read_body_data(sock,nreqt.max_body_size, nreqt.fetch_size, callback)
        if not ok then
            return nil,err
        end
    end
    return body
end

--[[
receive headers
]]
local function receiveheaders(sock, headers)
    local line, name, value, err, tmp1, tmp2
    headers = headers or {}
    -- get first line
    line, err = sock:receive()
    if err then return nil, err end
    -- headers go until a blank line is found
    while line ~= "" do
        -- get field-name and value
        tmp1, tmp2, name, value = string.find(line, "^(.-):%s*(.*)")
        if not (name and value) then return nil, "malformed reponse headers" end
        name = string.lower(name)
        -- get next line (value might be folded)
        line, err  = sock:receive()
        if err then return nil, err end
        -- unfold any folded values
        while string.find(line, "^%s") do
            value = value .. line
            line = sock:receive()
            if err then return nil, err end
        end
        -- save pair in table
        if headers[name] then headers[name] = headers[name] .. ", " .. value
        else headers[name] = value end
    end
    return headers
end

--[[
get socket connection
@return table|nil, string|nil
--]]
function getConnection(self, host, port, timeout)
    local conn      = nil
    local msg       = nil

    local host      = host and tostring(host) or false
    local port      = port and tonumber(port) or 80
    local timeout   = timeout and tonumber(timeout) or 3000

    if host then
        local sock = tcp()
        if not sock then
            msg = "create sock failed!"
        else
            local hosts = hosts:new()
            host = hosts:parse(host)

            sock:settimeout(timeout)
            local ok, err = sock:connect(host, port)
            if not ok then
                msg = "sock connected failed: " .. (err and err or '')
            else
                conn = sock
                if header and ngx.var.arg_dg == '1' then
                    self.ccnt = self.ccnt and (self.ccnt + 1) or 1
                    header['-htgc'] = self.ccnt
                end
            end
        end
    end

    return conn, msg
end

--[[
do http get
@return string,string
--]]
function get(self, host, uri, port, timeout)
    local body = ''
    local sock, msg = self:getConnection(host, port, timeout)
    if not (string.find(uri, '/') == 1) then
        msg = "missing '/' before uri - " .. host .. ':' .. port
    elseif sock then
        if not string.find(uri, 'request_from') then
            local requestFrom = self.REQUEST_FROM and tostring(self.REQUEST_FROM) or (ngx.var.server_name or '')
            uri = uri .. '&request_from=' .. requestFrom
        end
        --print(uri .. '<br/>')

        --[[ send http get headers --]]
        local reqline = string.format("GET %s HTTP/1.1\r\n", uri) ..
                        string.format("Host: %s\r\n", host) ..
                        "Connection: Close\r\n"
        local bytes, err = sock:send(reqline .. "\r\n")
        if err then
            sock:close()
            msg = "send http get headers failed: " .. err
        else
            --[[ receive status line --]]
            local code, status = receivestatusline(sock)
            --print (code, status)
            if not code then
                sock:close()
                msg = "read status line failed 002 " .. (status and status or '')
            else
                --[[ ignore any 100-continue messages --]]
                while code == 100 do
                    code, status = receivestatusline(sock)
                end
                if not code then
                    sock:close()
                    msg = "read status line failed 003 " .. (status and status or '')
                elseif shouldreceivebody(code) then
                    --[[ new request ]]
                    local nreqt = {
                        max_body_size = 1024 * 1024 * 1024,
                        fetch_size = 1024 * 16,
                    }

                    -- @todo we should add some params here to determine whether to receive headers
                    local headers = {}
                    headers, err = receiveheaders(sock, {})

                    if err then
                        sock:close()
                        msg = "read headers failed " .. err
                    else
                        --[[ do receive body ]]
                        body, err = receivebody(sock, headers, nreqt)
                        if err then
                            sock:close()
                            if code == 200 then
                                msg = "it seems empty body!"
                            else
                                msg = "read body failed " .. err
                            end
                        end
                        if body ~= '' then
                            local pos = string.find(body, "\r\n\r\n")
                            if pos then
                                body = string.sub(body, pos + 4)
                            end
                        end
                    end
                end
            end
        end
        sock:close()
    end
    if msg and msg ~= '' then
        log(msg)
    end

    return body, msg
end

--[[-------------------------------------------------------------------------]]

--[[ to prevent use of casual module global variables --]]
setmetatable(_M, {
    __newindex = function (table, key, val)
        log('attempt to write to undeclared variable "' .. key .. '" in ' .. table._NAME)
    end
})


