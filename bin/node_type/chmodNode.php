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

if (!class_exists("commandChmodNode")) {
    class commandChmodNode extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "nodetype" => "",
                "nid" => null,
                "flags" => 0,
            ), $params);
            $nodetype = null;
            // Detect wrong values
            if ($params["nodetype"] != null) {
                if (!is_int($params["nodetype"])) {
                    // Node type is a name
                    $resp = driverCommand::run("getNodeTypeId", array(
                        "name" => $params["nodetype"],
                    ));
                    if ($resp === false) {
                        return array("ok" => false, "msg" => "Bad node type id.");
                    } else {
                        $nodetype = $params["nodetype"];
                    }
                } else {
                    // Node type is a ID
                    $sql = "SELECT * FROM `node_type` where `id` = ".$params["nodetype"];
                    $q = dbConn::Execute($sql);
                    if ($q->EOF) {
                        return array("ok" => false, "msg" => "Bad node type id.");
                    }
                    $nodetype = $q->fields["name"];
                }
            } else {
                return array("ok" => false, "msg" => "Node type is required.");
            }
            
            //Change
            if ($params["nid"] == null) {
                // Change node type
                $can = driverCommand::run("getNodeTypeDef", array(
                    "nodetype" => $nodetype,
                ));
                if (driverUser::getID() == 0 || driverUser::getID() == $can["user_owner"]) {
                    // Change node type flags
                    $sql = "update `node_type` set ";
                    $sql .= "`access` = {$params["flags"]} ";
                    $sql .= "where `name` = '".$params["nodetype"]."'";
                    dbConn::Execute($sql);
                    $resp = array("ok" => true);
                } else {
                    $resp = array("ok" => false, "msg" => "You need ownership.");
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
                        // Change node flags
                        $sql = "update `node_$nodetype` set ";
                        $sql .= "`access` = {$params["flags"]} ";
                        $sql .= "where `id` = '".$params["nid"]."'";
                        dbConn::Execute($sql);
//                        if (isset($resp["ok"]) && $resp["ok"] === false) {
//                            return $resp;
//                        }
                        $resp = array("ok" => true);
                    } else {
                        $resp = array("ok" => false, "msg" => "You need ownership.");
                    }
                } else {
                    $resp = array("ok" => false, "msg" => "unknown node or you can't read.");
                }
            }
            
            return $resp;
        }

        public static function getHelp() {
            return array(
                "description" => "To change permission of node type or node that is owned by you.", 
                "parameters" => array(
                    "nodetype" => "Node type that you need change permission.",
                    "nid" => "Node ID of the node that you need change. Optional, if it's set try change a node, else try change a node type.",
                    "flags" => "Integer with the new permissions.",
                ), 
                "response" => array(
                    "ok" => "TRUE if changed."
                ),
                "type" => array(
                    "parameters" => array(
                        "nodetype" => "string",
                        "nid" => "integer",
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
return new commandChmodNode();