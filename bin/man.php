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

if (!class_exists("commandMan")) {
    class commandMan extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(
                    array(
                        "cmd" => "",
                    ), $params);
            
            $resp = array("help" => array(
                    $params["cmd"] =>array("description" => "Command '{$params["cmd"]}' not found."))
                    );
            
            $paths = driverCommand::getPaths();
            foreach($paths as $path) {
                $cmds = driverTools::lsDir($path);
                foreach($cmds["files"] as $cmd) {
                    $cmd = str_replace($path, "", $cmd);
                    $cmd = str_replace(".php", "", $cmd);
                    if ($cmd == $params["cmd"]) {
                        $resp["help"] = array();
                        $resp["help"][$cmd] = array();
                        $object = include($path.$cmd.".php");
                        $hlp = $object->getHelp();
                        $resp["help"][$cmd]["description"] = $hlp["description"];
                        if (count($hlp["parameters"]) > 0) {
                            $resp["help"][$cmd]["parameters"] = array();
                            foreach ($hlp["parameters"] as $key => $value) {
                                $resp["help"][$cmd]["parameters"][$key] = $value;
                            }
                        }
                        if (count($hlp["response"]) > 0) {
                            $resp["help"][$cmd]["response"] = array();
                            foreach ($hlp["response"] as $key => $value) {
                                $resp["help"][$cmd]["response"][$key] = $value;
                            }
                        }
                        $resp["help"][$cmd]["type"] = $hlp["type"];
                        $acc = $object->getAccessData($path.$cmd.".php");
                        $resp["help"][$cmd]["owner"] = $acc["owner"];
                        $resp["help"][$cmd]["group"] = $acc["group"];
                        $resp["help"][$cmd]["flags"] = $acc["flags"];
                        return $resp;
                    }
                }
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
                "description" => "Get help about a command how array. Ex. man ('cmd' => 'man'). Get this help.", 
                "parameters" => array(
                    "cmd" => "The command to query."
                ), 
                "response" => array(
                    'help' => "The help array."
                ),
                "type" => array(
                    "parameters" => array(
                        "cmd" => "string"
                    ), 
                    "response" => array(
                        'help' => "array."
                    ),
                )
            );
        }
    }
}
return new commandMan();