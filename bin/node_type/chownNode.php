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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandChownNode")) {
    class commandChownNode extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "nodetype" => "",
                "nid" => null,
                "owner" => null, // To detect defaults
                "group" => null,
            ), $params);
            $nodetype = null;
            $owner = null;
            $group = null;
            if ($params["owner"] == 0) {
                $owner = 0;
            }
            if ($params["group"] == 0) {
                $group = 0;
            }
            // Detect wrong values
            if ($params["nodetype"] != null) {
                if (!is_numeric($params["nodetype"])) {
                    // Node type is a name
                    $resp = driverCommand::run("getNodeTypeId", array(
                        "name" => $params["nodetype"],
                    ));
                    if ($resp === false) {
                        return array("ok" => false, "msg" => __("Bad node type id."));
                    } else {
                        $nodetype = $params["nodetype"];
                    }
                } else {
                    // Node type is a ID
                    $sql = "SELECT * FROM `node_type` where `id` = ".$params["nodetype"];
                    $q = dbConn::Execute($sql);
                    if ($q->EOF) {
                        return array("ok" => false, "msg" => __("Bad node type id."));
                    }
                    $nodetype = $q->fields["name"];
                }
            } else {
                return array("ok" => false, "msg" => __("Node type is required."));
            }
            if ($params["owner"] != null && $params["owner"] != "0") {
                if (!is_numeric($params["owner"])) {
                    // Owner is a mail
                    $resp = driverUser::getUserIDByMail($params["owner"]);
                    if ($resp === false) {
                        return array("ok" => false, "msg" => __("Bad user id."));
                    } else {
                        $owner = $resp;
                    }
                } else {
                    // Owner is a ID
                    $resp = driverUser::getUserName($params["owner"]);
                    if ($resp == "unknown") {
                        return array("ok" => false, "msg" => __("Bad user id."));
                    }
                    $owner = $params["owner"];
                }
            }
            if ($params["group"] != null && $params["group"] != "0") {
                if (!is_numeric($params["group"])) {
                    // group is a title
                    $resp = driverUser::getGroupID($params["group"]);
                    if ($resp === false) {
                        return array("ok" => false, "msg" => __("Bad group id."));
                    } else {
                        $group = $resp;
                    }
                } else {
                    // group is a ID
                    $resp = driverUser::getGroupName($params["group"]);
                    if ($resp == "unknown") {
                        return array("ok" => false, "msg" => __("Bad group id."));
                    }
                    $group = $params["group"];
                }
            }
            //Change
            if ($params["nid"] == null) {
                // Change node type
                $can = driverCommand::run("getNodeTypeDef", array(
                    "nodetype" => $nodetype,
                ));
                if (driverUser::getID() == 0 || driverUser::getID() == $can["user_owner"]) {
                    // Calculate the new ownership
                    if ($params["owner"] != null) {
                        $can["user_owner"] = $owner;
                    }
                    if ($params["group"] != null) {
                        $can["group_owner"] = $group;
                    }
                    // Change ownership
                    $sql = "update `node_type` set ";
                    $sql .= "`user_owner` = {$can["user_owner"]}, ";
                    $sql .= "`group_owner` = {$can["group_owner"]}, ";
                    $sql .= "`access` = {$can["access"]} ";
                    $sql .= "where `name` = '".$params["nodetype"]."'";
                    dbConn::Execute($sql);

                    $resp = array("ok" => true);
                } else {
                    $resp = array("ok" => false, "msg" => __("You need ownership."));
                }
            } else {
                // Change node
                $can = driverCommand::run("getNodes", array(
                    "nodetype" => $nodetype,
                    "fields" => "group_owner,user_owner,access",
                    "where" => "`id` = ".$params["nid"],
                ));
                if (count($can) > 0) {
                    $can = $can[$params["nid"]];
                    $ids = array_keys($can);
                    if (driverUser::getID() == 0 || driverUser::getID() == $can["user_owner"]) {
                        // Calculate the new ownership
                        if ($params["owner"] != null) {
                            $can["user_owner"] = $owner;
                        }
                        if ($params["group"] != null) {
                            $can["group_owner"] = $group;
                        }
                        // Change ownership
                        $sql = "update `node_$nodetype` set ";
                        $sql .= "`user_owner` = {$can["user_owner"]}, ";
                        $sql .= "`group_owner` = {$can["group_owner"]}, ";
                        $sql .= "`access` = {$can["access"]} ";
                        $sql .= "where `id` = '".$params["nid"]."'";
                        dbConn::Execute($sql);
//                        if (isset($resp["ok"]) && $resp["ok"] === false) {
//                            return $resp;
//                        }
                        $resp = array("ok" => true);
                    } else {
                        $resp = array("ok" => false, "msg" => __("You need ownership."));
                    }
                } else {
                    $resp = array("ok" => false, "msg" => __("unknown node or you can't read."));
                }
            }
            return $resp;
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("To change owner, and/or group, of the node types or nodes."), 
                "parameters" => array(
                    "nodetype" => __("Node type that you need change ownership or type of the node to change."),
                    "nid" => __("Node ID of the node that you need change. Optional, if it's set try change a node, else try change a node type."),
                    "owner" => __("Mail of the user or ID of the new owner. If it's null, or it is not set, command don't change it, to set to root you must value how 0, zero."),
                    "group" => __("Title or ID of the group. If it's null, or it is not set, command don't change it, to set to root you must value how 0, zero."),
                ), 
                "response" => array(
                    "ok" => __("TRUE if changed.")
                ),
                "type" => array(
                    "parameters" => array(
                        "nodetype" => "string",
                        "nid" => "integer",
                        "owner" => "string",
                        "group" => "string",
                    ), 
                    "response" => array(
                        "ok" => "boolean"
                    ),
                ),
                "echo" => false
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
return new commandChownNode();