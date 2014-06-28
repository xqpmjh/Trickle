Name
====

Trickle - Being a fast, light weight, full stack web development framework based on OpenResty/Lua

Table of Contents
=================

* [Name](#name)
* [Status](#status)
* [Synopsis](#synopsis)
* [Description](#description)
* [Prerequisites](#prerequisites)
* [Caveat](#caveat)
* [Author](#author)
* [Copyright and License](#copyright-and-license)
* [See Also](#see-also)

Status
======

This framework is production on Billion-Level service and under active development.

Synopsis
========

```nginx
    # nginx.conf

	http {
		# usually you need to configure these path
	    lua_package_path "?.lua;./?.lua;/path/to/Trickle/openresty/?.lua;;";
	    # usually you need to local cache for high performance
	    lua_shared_dict lcache 10m;

		server {
		    listen 80;
		    server_name www.example.com;
		    ......

		    location / {
		    	# you need to turn it on when developing
	            #lua_code_cache off;
	            content_by_lua_file '/path/to/Trickle/openresty/index.lua';
		    }
		}
	}

```

Description
===========

This framework is aim at replacing the very slow PHP at the beginning.
After 2 years of gradually improvement and under billion level service practicing.
It's time to let it exposure to help more people.

[Back to TOC](#table-of-contents)

Prerequisites
=============

* ngx_openresty-1.4.3.9 or later [http://openresty.org/#Download]

And nothing else...

[Back to TOC](#table-of-contents)


Caveat
======

The framework is not panacea, but depends on how you use it wisely.

[Back to TOC](#table-of-contents)

Author
======

Hao Jin "kim" (金昊) <xqpmjh@gmail.com>, kimbs.cn

[Back to TOC](#table-of-contents)

Copyright and License
=====================

This module is licensed under the BSD license.

Copyright (C) 2014, by Hao Jin "kim", kimbs.cn

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

[Back to TOC](#table-of-contents)

See Also
========
* A fast and scalable web application platform by extending NGINX with Lua: https://github.com/openresty
* Official website: http://openresty.org/
* Xssfilter implement: https://github.com/yuri/lua-xssfilter
* Resty mysql handlersocket implement: https://github.com/zhhchen/lua-resty-handlersocket
* Neturl parser: https://github.com/golgote/neturl

[Back to TOC](#table-of-contents)
