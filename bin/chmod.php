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

if (!class_exists("commandChmod")) {
    class commandChmod extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "cmd" => "",
                "flags" => 0,
            ), $params);
            $cmd = $params["cmd"];
            
            foreach (driverCommand::$paths as $path) {
                if (is_file($path . $cmd . ".php")) {
                    $object = include($path . $cmd . ".php");
                    $can = $object->getAccessData($path . $cmd . ".php");
                    if (driverUser::getID() == 0 || driverUser::getID() == $can["owner"]) {
                        // Change permissions
                        driverUser::secFileSetAccess(
                                $path . $cmd . ".php", 
                                $params["flags"], $can["owner"], $can["group"]
                            );
                        $resp = array("ok" => true);
                    } else {
                        $resp = array("ok" => false);
                    }
                    return $resp;
                }
            }
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("To change permission of command that is owned by you. To execute commands only Execution is considered."), 
                "parameters" => array(
                    "cmd" => __("Command that you need change permission."),
                    "flags" => __("Integer with the new permissions."),
                ), 
                "response" => array(
                    "ok" => __("TRUE if changed.")
                ),
                "type" => array(
                    "parameters" => array(
                        "cmd" => "string",
                        "flags" => "integer",
                    ), 
                    "response" => array(
                        "ok" => "boolean"
                    ),
                )
            );
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
    }
}
return new commandChmod();