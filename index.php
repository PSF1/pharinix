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

header("author: Pedro Pelaez <aaaaa976@gmail.com>");
// Requires
include_once("config/config.php");
header("generator: Pharinix ".CMS_VERSION);
include_once("libs/adodb/cmsapi.php");
include_once("etc/drivers/tools.php");
include_once("etc/drivers/command.php");
//include_once("etc/drivers/user.php");
include_once("etc/drivers/urlRewrite.php");

// Main user interface
$output = array(); // Global output tree
if (CMS_DEBUG) {
    $output["used_ram"] = array();
    $output["used_ram"]["start"] = memory_get_usage();
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $output["used_time"] = array();
    $output["used_time"]["start"] = $mtime;
}
//$cmsUsr = new driverUser();
new driverUrlRewrite();

if (!isset($_POST["interface"])) {
    $_POST["interface"] = "1";
}
if (isset($_GET[CMS_GET_PASS]) && isset($_GET["interface"])) {
    $_POST["interface"] = $_GET["interface"];
}
if (CMS_GET_PASS != "" && isset($_GET[CMS_GET_PASS])) {
    foreach ($_GET as $key => $value) {
        $_POST[$key] = $value;
    }
}
// Default command
if ($_POST["interface"] != "0") {
    $page = "home"; // Default page
    $params = driverCommand::getPOSTParams($_POST);
    $cmd = "pageToHTML";
    if (isset($_POST["command"])) {
        $cmd = $_POST["command"];
    } else {
        if (isset($_POST["rewrite"])) {
            $page = $_POST["rewrite"];
        } else if(isset($_GET["rewrite"])) {
            $page = "404";
        }
        $params = array(
            "page" => $page,
            "params" => $params
        );
    }
    driverCommand::run($cmd, $params);
} else {
    if (!isset($_POST["command"])) {
        $_POST["command"] = "nothing";
    }
    driverCommand::run($_POST["command"], driverCommand::getPOSTParams($_POST));
}

// Init system
//if ($_POST["interface"] != "0") {
//    if ($cmsUsr->isLoged()) {
//        driverCommand::run("pageToHTML", array(
//            "page" => "default.xml",
//            "params" => driverCommand::getPOSTParams($_POST)
//            ));
//    } else {
//        driverCommand::run("pageToHTML", array("page" => "default.xml"));
//    }
//} else {
//    driverCommand::run($_POST["command"], driverCommand::getPOSTParams($_POST));
//}
if (CMS_DEBUG) {
    driverCommand::run("usageEnd");
    driverCommand::run("usageToHTML");
    driverCommand::run("traceToHTML");
}