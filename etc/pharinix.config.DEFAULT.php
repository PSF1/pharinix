;<?php die(); ?>
;
; Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
; Sources https://github.com/PSF1/pharinix
;
; This program is free software; you can redistribute it and/or
; modify it under the terms of the GNU General Public License
; as published by the Free Software Foundation; either version 2
; of the License, or (at your option) any later version.
;
; This program is distributed in the hope that it will be useful,
; but WITHOUT ANY WARRANTY; without even the implied warranty of
; MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
; GNU General Public License for more details.
;
; You should have received a copy of the GNU General Public License
; along with this program; if not, write to the Free Software
; Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
;

[core]
CMS_TITLE = Pharinix
CMS_DEBUG = false
CMS_DEBUG_TEMPLATE = false
CMS_ALWAYS_COMPILE_TEMPLATE = false
CMS_CACHING_TEMPLATE = true
CMS_CACHE_LIFETIME_TEMPLATE = 120
CMS_DEBUG_LOG_SQL = false
CMS_MIN_PHP_VER = "5.3"
CMS_DEFAULT_URL_BASE = "auto"
CURLOPT_USERAGENT = "Mozilla/5.0 (compatible; Pharinix/{version}; +{url_base})"
path = 'bin/;bin/node_type/;bin/router/;bin/user/;bin/html/;bin/cfg/;bin/gettext/;bin/lpmonitor/;bin/menu/'

[mysql]
MYSQL_USER = "root"
MYSQL_PASS = ''
MYSQL_HOST = "127.0.0.1"
MYSQL_DBNAME = "miana"
ADODB_PERF_NO_RUN_SQL = 1
charset = 'utf8'
table_charset = 'utf8_general_ci'
; ADODB_DEFAULT_CACHE allow use the query cache by default
ADODB_DEFAULT_CACHE = false
; ADODB_MEMCACHE_USAGE allow the use of memcached if the query allow cache
ADODB_MEMCACHE_USAGE = false
ADODB_MEMCACHE_HOSTS = '192.168.0.78,192.168.0.79,192.168.0.80'
ADODB_MEMCACHE_PORT = 11211
ADODB_MEMCACHE_COMPRESS = false
ADODB_MEMCACHE_LIFE = 2400

[nodetypes]
; You can use driverMemcached in CACHE_CLASS to memcached, or driverBasicCache for local memory cache.
CACHE_CLASS = driverBasicCache
USAGE = true
MEMCACHE_HOSTS = '192.168.0.78,192.168.0.79,192.168.0.80'
MEMCACHE_PORT = 11211
MEMCACHE_COMPRESS = false
MEMCACHE_LIFE = 2400

[safe_mode]
; Ignore database connection
active = false
; Root access without data base connection
user = 'root@pharinix'
pass = '1234'

[pageToHTML]
show_areas = false
