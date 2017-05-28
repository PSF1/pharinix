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

//error_reporting(E_ALL | E_STRICT);
error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

if (isset($_SERVER["TRAVIS"]) && $_SERVER["TRAVIS"]) {
    $_SERVER["HTTP_HOST"] = "localhost";
    phpInfo();
}
// Build default configuration file
if (!is_file('etc/pharinix.config.php')) {
    copy('etc/pharinix.config.DEFAULT.php', 'etc/pharinix.config.php');
}
// Requires
include_once("etc/drivers/tools.php");
include_once('etc/drivers/txtlog.php');
include_once 'etc/php-fslock/src/FSLockInterface.php';
include_once 'etc/php-fslock/src/FSLock.php"';
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
//use Gettext\Translator;
//$t = new Translator();
////if (is_file('etc/i18n/es.po')) {
////    $po = Gettext\Extractors\Mo::fromFile('etc/i18n/es.mo');
////    $t->loadTranslations($po);
////}
//Translator::initGettextFunctions($t);

include_once("etc/drivers/nodes.php");
include_once("etc/drivers/command.php");
include_once("etc/drivers/urlRewrite.php");
include_once 'etc/drivers/longProcessMonitor.php';

// Create sudoers default group
$sql = "SELECT * FROM `node_group` where `title` = 'sudoers'";
$q = dbConn::Execute($sql);
if ($q->EOF) {
    $sql = "insert into `node_group` set `title` = 'sudoers'";
    dbConn::Execute($sql);
}