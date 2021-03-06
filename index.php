<?php

/* 
 * Pharinix Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
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

// Requires
include_once("etc/drivers/tools.php");
include_once('etc/drivers/txtlog.php');
require_once 'etc/php-fslock/src/FSLockInterface.php';
require_once 'etc/php-fslock/src/FSLock.php';
include_once 'etc/drivers/config.php';
driverConfig::getCFG();

include_once 'etc/drivers/hooks.php';
new driverHook();

//Create a translator instance
include_once "etc/gettext/src/autoloader.php";
include_once "etc/cldr-to-gettext-plural-rules/src/autoloader.php";

include_once("usr/adodb/cmsapi.php");
include_once("etc/drivers/user.php");
driverUser::loadTranslations();

include_once("etc/drivers/nodes.php");
include_once("etc/drivers/command.php");
include_once("etc/drivers/urlRewrite.php");
include_once 'etc/drivers/longProcessMonitor.php';

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
new driverUrlRewrite();

if (!isset($_POST["interface"])) {
    $interface = "echoHtml";
    driverHook::CallHook('coreDefaultInterfaceHook', array(
        'command' => &$interface,
    ));
    $_POST["interface"] = $interface;
}

driverHook::CallHook('coreCatchParametersHook', array());
// "GET" fusion with "POST"
foreach ($_GET as $key => $value) {
    $_POST[$key] = $value;
}
// "FILES" fused with "POST"
foreach ($_FILES as $key => $value) {
    $_POST[$key] = $value;
}

// Boot process
$boot = driverCommand::run("listBooting");
foreach($boot as $cmd) {
    $prms = array();
    parse_str($cmd["parameters"], $prms);
    driverCommand::run($cmd["command"], $prms);
}
unset($boot);

// Default command
if ($_POST["interface"] == "1" || $_POST["interface"] == "echoHtml") {
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
    driverHook::CallHook('coreShowDefaultPageHook', array(
        'command' => &$cmd,
        'parameters' => &$params,
    ));
    driverCommand::run($cmd, $params);
} else {
    if (!isset($_POST["command"])) {
        $com = "nothing";
        driverHook::CallHook('coreDefaultCommandHook', array(
            'command' => &$com,
        ));
        $_POST["command"] = $com;
    }
    $params = driverCommand::getPOSTParams($_POST);
    unset($params["command"]);
    unset($params["interface"]);
    $resp = driverCommand::run($_POST["command"], $params);
    driverCommand::run($_POST["interface"], $resp);
}

if (CMS_DEBUG) {
    driverCommand::run("usageEnd");
    driverCommand::run("usageToHTML");
    driverCommand::run("traceToHTML");
}
