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

if (!class_exists("commandAddUser")) {
    class commandAddUser extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "mail" => "",
                "pass" => "",
                "name" => "",
                "title" => "",
            ), $params);
            $resp = array(
                "ok" => false,
                "msg" => "",
                "nid" => 0,
            );
            if ($params["name"] == "") {
                $resp["msg"] .= "User name required. ";
            }
            if ($params["mail"] == "") {
                $resp["msg"] .= "User mail required. ";
            }
            $params["mail"] = strtolower($params["mail"]);
            if ($params["mail"] == "root@localhost") {
                $resp["msg"] .= "'root@localhost' is in use. ";
            }
            if ($params["title"] == "") {
                $resp["msg"] .= "User title required. ";
            }
            if ($params["pass"] == "") {
                $resp["msg"] .= "User password required. ";
            }
            if ($resp["msg"] == "") {
                try {
                    // If dont exist the user name how group
                    $grp = driverCommand::run("getNodes", array(
                        "nodetype" => "group",
                        "count" => true,
                        "where" => "`title` = '{$params["name"]}'",
                    ));
                    $mail = driverCommand::run("getNodes", array(
                        "nodetype" => "user",
                        "count" => true,
                        "where" => "`mail` = '{$params["mail"]}'",
                    ));
                    if ($grp[0]["amount"] == 0 && $mail[0]["amount"] == 0) {
                        // Create group
                        $gid = driverCommand::run("addNode", array(
                            "nodetype" => "group",
                            "title" => $params["name"],
                        ));
                        if ($gid["ok"]) {
                            // Create user with default group
                            $uid = driverCommand::run("addNode", array(
                                "nodetype" => "user",
                                "mail" => $params["mail"],
                                "pass" => $params["pass"],
                                "name" => $params["name"],
                                "title" => $params["title"],
                                "groups" => $gid["nid"],
                            ));
                            // 
                            if ($uid["ok"]) {
                                $resp["nid"] = $uid["nid"];
                                // Each user own himself
                                driverCommand::run("chownNode", array(
                                    "nodetype" => "user",
                                    "nid" => $resp["nid"],
                                    "owner" => $params["mail"],
                                ));
                                $resp["ok"] = true;
                            } else {
                                $resp = $uid;
                            }
                        } else {
                            $resp = $gid;
                        }
                    } else {
                        $resp["msg"] = "Mail or group in use.";
                    }
                } catch (Exception $exc) {
                    $resp["msg"] = $exc->getMessage();
                }
            }
            return $resp;
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getHelp() {
            return array(
                "description" => "Add a new user. All parameters are requires.", 
                "parameters" => array(
                    "mail" => "The user mail.",
                    "pass" => "The password in plain text.",
                    "name" => "User name or nick, used how default group name too.",
                    "title" => "User complete name.",
                ), 
                "response" => array(
                    "ok" => "TRUE if the user is added.",
                    "msg" => "If ok is FALSE contains the error message.",
                    "nid" => "If ok is TRUE contains the new user ID.",
                ),
                "type" => array(
                    "parameters" => array(
                        "mail" => "string",
                        "pass" => "string",
                        "name" => "string",
                        "title" => "string",
                    ), 
                    "response" => array(
                        "ok" => "boolean",
                        "msg" => "string",
                        "nid" => "integer",
                    ),
                )
            );
        }
    }
}
return new commandAddUser();
