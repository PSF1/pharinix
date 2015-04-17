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

// TODO: SECURITY !!

if (!class_exists("commandUpdateNodes")) {
    class commandUpdateNodes extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $resp = array("ok" => false, "msg" => "");

            // Default values
            $params = array_merge(array(
                "nodetype" => "",
                "nid" => "",
                "modified" => date("Y-m-d H:i:s"), 
                "modifier" => driverUser::getID(),
                    ), $params);
            
            if ($params["nid"] == "") {
                $resp["msg"] = "Node ID required";
                return $resp;
            } else {
                $node = driverCommand::run("getNodes", array(
                    "nodetype" => $params["nodetype"],
                    "count" => true,
                    "where" => "`id` = ".$params["nid"],
                ));
                if (isset($node["ok"]) && !$node["ok"]) {
                    $resp["msg"] = $node["msg"];
                    return $resp;
                }
                if ($node[0]["ammount"] == 0) {
                    $resp["msg"] = "Unknowed node ID";
                    return $resp;
                }
            }
            
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
//                    foreach ($ndefFields as $ndefField) {
//                        if (($ndefField["required"] || $ndefField["iskey"]) && !isset($params[$ndefField["name"]])) {
//                            $okRequired = false;
//                            break;
//                        }
//                    }
                    if (!$okRequired) {
                        // You miss a required node field
                        $resp["msg"] = "Missing node field required.";
                    } else {
                        // All selected items are fields of the node?
                        $allOk = true;
                        foreach ($params as $name => $value) {
                            if ($name != "nodetype" && $name != "nid") {
                                $nameOk = $name;
                                foreach ($ndefFields as $ndefField) {
                                    if ($ndefField["name"] == $name) {
                                        $nameOk = true;
                                        break;
                                    }
                                }
                                if ($nameOk !== true) {
                                    $allOk = $nameOk;
                                    break;
                                }
                            }
                        }
                        if ($allOk !== true) {
                            $resp["msg"] = "Field '{$allOk}' are not fields of '{$params["nodetype"]}' node type.";
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
                                $sql = "select count(*) from `node_{$params["nodetype"]}` where ($where) && `id` <> {$params["nid"]}";
                                $q = dbConn::Execute($sql);
                                $ctrl = $q->fields[0];
                            }
                            if ($ctrl > 0) {
                                // Duplicate keys
                                $resp["msg"] = "Duplicate keys.";
                            } else {
                                // NOW, we can save node !! :D :D
                                $sql = "";
                                $sqlMultis = array();
                                $tableMultis = array();
                                foreach ($params as $name => $value) {
                                    // Ignore nodetype parameter because isn't a field
                                    if ($name != "nodetype" && $name != "nid") {
                                        $fieldDef = self::getFieldDef($name, $ndefFields);
                                        if ($fieldDef["multi"]) {
                                            // Prepare all multivalue inserts.
                                            $vals = explode(",", $value);
                                            $table = '`node_relation_'.$params["nodetype"].'_'.$name.'_'.$fieldDef["type"].'`';
                                            $tableMultis[] = $table;
                                            $multi = "";
                                            foreach($vals as $val) {
                                                if ($multi != "") $multi .= ", ";
                                                $multi .= " (null, {NID}, $val)";
                                            }
                                            $sqlMultis[] = "insert into $table values ".$multi;
                                        } else {
                                            // Single value fields
                                            if ($sql != "") $sql .= ", ";
                                            if ($fieldDef["type"] == "password") { // Type password
                                                $fVal = md5($value);
                                            } else { // Type other
                                                $fVal = dbConn::qstr($value);
                                            }
                                            $sql .= "`$name` = '".$fVal."'";
                                        }
                                    }
                                }
                                $sql = "update `node_{$params["nodetype"]}` set ".$sql;
                                $sql .= " where `id` = {$params["nid"]}";
                                dbConn::Execute($sql);
                                $last = $params["nid"];
                                $resp["ok"] = true;
                                // Clear multi values tables
                                foreach($tableMultis as $table) {
                                    $sql = "delete from $table where `type1` = $last";
                                    dbConn::Execute($sql);
                                }
                                // Add Multi values
                                foreach($sqlMultis as $sqlMulti) {
                                    $sqlMulti = str_replace("{NID}", $last, $sqlMulti);
                                    dbConn::Execute($sqlMulti);
                                }
                            }
                        }
                    }
                }
            }
            return $resp;
        }

        private static function getFieldDef($name, $nodeDef) {
            foreach($nodeDef as $fieldDef) {
                if ($name == $fieldDef["name"]) {
                    return $fieldDef;
                }
            }
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
                "description" => "Update a node", 
                "parameters" => array(
                    "nodetype" => "Node type of node to update.",
                    "nid" => "ID of node.",
                    "any" => "A parameter for each field of the type.",
                ), 
                "response" => array(
                    "ok" => "True/False node updated",
                    "msg" => "If error, it's a message about error",
                )
            );
        }
    }
}
return new commandUpdateNodes();