<?php

/*
 * Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
 * Sources https://github.com/PSF1/pharinix
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

if (!isset($_SERVER["HTTP_HOST"])) {
    $_SERVER["HTTP_HOST"] = "localhost";
}
phpInfo();
// Requires
include_once 'etc/drivers/config.php';
include_once(driverConfig::getConfigFilePath());

include_once("usr/adodb/cmsapi.php");
include_once("etc/drivers/tools.php");
include_once("etc/drivers/command.php");
include_once("etc/drivers/user.php");
include_once("etc/drivers/urlRewrite.php");

$sql = "SELECT * FROM `node_group` where `title` = 'sudoers'";
$q = dbConn::Execute($sql);
if ($q->EOF) {
    $sql = "insert into `node_group` set `title` = 'sudoers'";
    dbConn::Execute($sql);
}