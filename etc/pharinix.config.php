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
CMS_DEBUG_LOG_SQL = false
CMS_MIN_PHP_VER = "5.3"
CMS_DEFAULT_URL_BASE = "http://127.0.0.1/pharinix/"
path = 'bin/;bin/node_type/;bin/router/;bin/user/;bin/html/;bin/cfg/;bin/gettext/;bin/lpmonitor/'

[mysql]
MYSQL_USER = "root"
MYSQL_PASS = ''
MYSQL_HOST = "127.0.0.1"
MYSQL_DBNAME = "miana"
ADODB_PERF_NO_RUN_SQL = 1

[safe_mode]
; Ignore database connection
active = false
; Root access without data base connection
user = 'aaaaa976@gmail.com'
pass = '1234'

[pageToHTML]
show_areas = false
