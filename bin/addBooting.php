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

if (!class_exists("commandAddBooting")) {
    class commandAddBooting extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                    "cmd" => null,
                    "parameters" => "",
                    "priority" => "0",
                ), $params);
            if ($params["cmd"] != null) {
                $uid = uniqid("", true);
                $sql = "insert into `booting` set `command` = '{$params["cmd"]}', ";
                $sql .= "`parameters` = '{$params["parameters"]}', ";
                $sql .= "`priority` = '{$params["priority"]}', ";
                $sql .= "`ref` = '".$uid."'";
                dbConn::Execute($sql);
                return array("uid" => $uid);
            } else {
                return array("ok" => false, "msg" => __("'cmd' parameter is required."));
            }
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }

        public static function getHelp() {
            return array(
                "description" => __("Add a command, with parameters, to the boot process. This will executed with each user petition."), 
                "parameters" => array(
                    "cmd" => __("Command to execute"),
                    "parameters" => __("Post string to put in the command."),
                    "priority" => __("Priority of execution."),
                ), 
                "response" => array(
                    "uid" => __("Unique ID to we can delete the boot command.")
                ),
                "type" => array(
                    "parameters" => array(
                        "cmd" => "string",
                        "parameters" => "string",
                        "priority" => "integer",
                    ), 
                    "response" => array(
                        "uid" => "string"
                    ),
                )
            );
        }
    }
}
return new commandAddBooting();