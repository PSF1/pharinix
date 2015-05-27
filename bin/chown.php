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

if (!class_exists("commandChown")) {
    class commandChown extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "cmd" => "",
                "owner" => null, // To detect defaults
                "group" => null,
            ), $params);
            $cmd = $params["cmd"];
            $owner = null;
            $group = null;
            // Detect wrong values
            if ($params["owner"] != null) {
                if (!is_int($params["owner"])) {
                    // Owner is a mail
                    $resp = driverUser::getUserIDByMail($params["owner"]);
                    if ($resp === false) {
                        return array("ok" => false, "msg" => "Bad user id.");
                    } else {
                        $owner = $resp;
                    }
                } else {
                    // Owner is a ID
                    $resp = driverUser::getUserName($params["owner"]);
                    if ($resp == "unknown") {
                        return array("ok" => false, "msg" => "Bad user id.");
                    }
                    $owner = $params["owner"];
                }
            }
            if ($params["group"] != null) {
                if (!is_int($params["group"])) {
                    // group is a title
                    $resp = driverUser::getGroupID($params["group"]);
                    if ($resp === false) {
                        return array("ok" => false, "msg" => "Bad group id.");
                    } else {
                        $group = $resp;
                    }
                } else {
                    // group is a ID
                    $resp = driverUser::getGroupName($params["group"]);
                    if ($resp == "unknown") {
                        return array("ok" => false, "msg" => "Bad group id.");
                    }
                    $group = $params["group"];
                }
            }

            //Change
            foreach (driverCommand::$paths as $path) {
                if (is_file($path . $cmd . ".php")) {
                    $object = include($path . $cmd . ".php");
                    $can = $object->getAccessData($path . $cmd . ".php");
                    if (driverUser::getID() == 0 || driverUser::getID() == $can["owner"]) {
                        // Calculate the new ownership
                        if ($params["owner"] != null) {
                            $can["owner"] = $owner;
                        }
                        if ($params["group"] != null) {
                            $can["group"] = $group;
                        }
                        // Change ownership
                        driverUser::secFileSetAccess(
                                $path . $cmd . ".php", 
                                $can["flags"], $can["owner"], $can["group"]
                            );
                        $resp = array("ok" => true);
                    } else {
                        $resp = array("ok" => false, "msg" => "You need ownership.");
                    }
                    return $resp;
                }
            }
        }

        public static function getHelp() {
            return array(
                "description" => "To change owner, and/or group, of the command.", 
                "parameters" => array(
                    "cmd" => "Command that you need change ownership.",
                    "owner" => "Mail of the user or ID of the new owner. If it's null, or it is not set, command don't change it, to set  to root you must value how 0, zero.",
                    "group" => "Title or ID of the group. If it's null, or it is not set, command don't change it, to set  to root you must value how 0, zero.",
                ), 
                "response" => array(
                    "ok" => "TRUE if changed."
                ),
                "type" => array(
                    "parameters" => array(
                        "cmd" => "string",
                        "owner" => "string",
                        "group" => "string",
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
return new commandChown();