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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandGetCommandHelp")) {
    class commandGetCommandHelp extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $resp = array("help" => array());
            $sql = "SELECT * FROM `bin-path`";
            $q = dbConn::Execute($sql);
            while(!$q->EOF) {
                $resp["help"][$q->fields["path"]] = array();
                $cmds = driverTools::lsDir($q->fields["path"]);
                foreach($cmds["files"] as $cmd) {
                    $cmd = str_replace($q->fields["path"], "", $cmd);
                    $cmd = str_replace(".php", "", $cmd);
                    $resp["help"][$q->fields["path"]][$cmd] = array();
                    $object = include($q->fields["path"].$cmd.".php");
                    $hlp = $object->getHelp();
                    $resp["help"][$q->fields["path"]][$cmd]["description"] = $hlp["description"];
                    if (count($hlp["parameters"]) > 0) {
                        $resp["help"][$q->fields["path"]][$cmd]["parameters"] = array();
                        foreach ($hlp["parameters"] as $key => $value) {
                            $resp["help"][$q->fields["path"]][$cmd]["parameters"][$key] = $value;
                        }
                    }
                    if (count($hlp["response"]) > 0) {
                        $resp["help"][$q->fields["path"]][$cmd]["response"] = array();
                        foreach ($hlp["response"] as $key => $value) {
                            $resp["help"][$q->fields["path"]][$cmd]["response"][$key] = $value;
                        }
                    }
                }
                $q->MoveNext();
            }
            return $resp;
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
        
        public static function getHelp() {
            return array(
                "description" => "Command's help as array.", 
                "parameters" => array(), 
                "response" => array("help" => "Help grouped by bin path. array('bin/html' => array...)")
            );
        }
    }
}
return new commandGetCommandHelp();