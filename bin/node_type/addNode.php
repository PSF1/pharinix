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
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

//TODO: Securizate access
//TODO: Save multivalued fields

if (!class_exists("commandAddNode")) {

    class commandAddNode extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $resp = array("ok" => false, "nid" => 0, "msg" => "");

            // Default values
            $params = array_merge(array(
                "nodetype" => "",
                    ), $params);
            if ($params["nodetype"] == "") { // Node type defined?
                $resp["msg"] = "Node type required";
            } else {
                $ntid = driverCommand::run("getNodeTypeId", array("name" => $params["nodetype"]));
                $ntid = $ntid["id"];
                if ($ntid === false) { // I dont know the node type
                    $resp["msg"] = "Unknowed node type '{$params["nodetype"]}'";
                } else {
                    // Required fields presents? (required or iskey)
                    $ndefFields = driverCommand::run("getNodeTypeDef", $params);
                    $ndefFields = $ndefFields["fields"];
                    $okRequired = true;
                    foreach ($ndefFields as $ndefField) {
                        if (($ndefField["required"] || $ndefField["iskey"]) && !isset($params[$ndefField["name"]])) {
                            $okRequired = false;
                            break;
                        }
                    }
                    if (!$okRequired) {
                        // You miss a required node field
                        $resp["msg"] = "Missing node field required.";
                    } else {
                        // All selected items are fields of the node?
                        $allOk = true;
                        foreach ($params as $name => $value) {
                            if ($name != "nodetype") {
                                $nameOk = false;
                                foreach ($ndefFields as $ndefField) {
                                    if ($ndefField["name"] == $name) {
                                        $nameOk = true;
                                        break;
                                    }
                                }
                                if (!$nameOk) {
                                    $allOk = false;
                                    break;
                                }
                            }
                        }
                        if (!$allOk) {
                            $resp["msg"] = "Some fields are not fields of this node type.";
                        } else {
                            // Duplicated keys?
                            $where = "";
                            foreach ($ndefFields as $ndefField) {
                                if ($ndefField["iskey"]) {
                                    if ($where != "")
                                        $where .= " || ";
                                    $where .= "`{$ndefField["name"]}` = '" . dbConn::qstr($params[$ndefField["name"]])."'";
                                }
                            }
                            // Some?
                            $ctrl = 0;
                            if ($where != "") {
                                $sql = "select count(*) from `node_{$params["nodetype"]}` where $where";
                                $q = dbConn::get()->Execute($sql);
                                $ctrl = $q->fields[0];
                            }
                            if ($ctrl > 0) {
                                // Duplicate keys
                                $resp["msg"] = "Duplicate keys.";
                            } else {
                                // NOW, we can save node !! :D :D
                                $sql = "";
                                foreach ($params as $name => $value) {
                                    if ($name != "nodetype") {
                                        if ($sql != "") $sql .= ", ";
                                        $sql .= "`$name` = '".dbConn::qstr($value)."'";
                                    }
                                }
                                $sql = "insert into `node_{$params["nodetype"]}` set ".$sql;
                                dbConn::get()->Execute($sql);
                                $last = dbConn::lastID();
                                $resp["nid"] = $last;
                                $resp["ok"] = true;
                                // Add personalized page
                                driverCommand::run("addPage", array(
                                    'name' => "node_type_".$params["nodetype"]."_".$last,
                                    'template' => "etc/templates/pages/default.xml",
                                    'title' => "Node {$last}",
                                    'description' => "",
                                    'keys' => "",
                                    'url' => "node/type/{$params["nodetype"]}/{$last}",
                                ));
                                driverCommand::run("addBlockToPage", array(
                                    'page' => "node_type_".$params["nodetype"]."_".$last,
                                    'command' => "getNodeHtml",
                                    'parameters' => "nodetype=".$params["nodetype"]."&node=$last",
                                ));
                            }
                        }
                    }
                }
            }
            return $resp;
        }

        public static function getHelp() {
            return array(
                "description" => "Add a new node.",
                "parameters" => array(
                    "any" => "A parameter for each field of the type.",
                    "nodetype" => "Node type is from.",
                ),
                "response" => array(
                    "ok" => "True/False node added",
                    "msg" => "If error, it's a message about error",
                    "nid" => "ID of new node",
                )
            );
        }

    }

}
return new commandAddNode();
