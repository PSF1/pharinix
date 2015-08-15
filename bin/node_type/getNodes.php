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

if (!class_exists("commandGetNodes")) {
    class commandGetNodes extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            // Default parameters
            $params = array_merge(array(
                "nodetype" => "",
                "count" => false,
                "fields" => "*",
                "where" => "",
                "order" => "",
                "group" => "",
                "offset" => "0",
                "length" => "100",
                    ), $params);
            // Get node definition
            $nodeFields = driverCommand::run("getNodeTypeDef", array("nodetype" => $params["nodetype"]));
            $nodeFields = $nodeFields["fields"];
            if (count($nodeFields) > 0) {
                // Clear field list
                $params["fields"] = str_replace("*", "`node_{$params["nodetype"]}`.*", $params["fields"]);
                $params["fields"] = explode(",", $params["fields"]);
                $fieldList = "";
                $haveId = false;
                foreach ($params["fields"] as $field) {
                    if ($fieldList != "") $fieldList .= ",";
                    $field = str_replace("`", "", trim($field));
                    $fDef = self::getFieldDef($field, $nodeFields);
                    if ($fDef === false && strpos($field, "*") === false 
                            && $field != "id") {
                        $fieldList .= "'error' as `$field`";
                    } else if (strpos($field, "*") !== false) {
                        $fieldList .= "$field";
                    } else {
                        $fieldList .= "`$field`";
                    }
                    if ($field == "id") $haveId = true;
                }
                if (!$haveId) {
                    if ($fieldList != "") $fieldList .= ",";
                    $fieldList .= "`id`";
                }
                // Build segments
                if ($params["where"] != "") $params["where"] = " where ".$params["where"];
                // Security control
                if (!driverUser::isSudoed()) {
                    $usrGrps = driverUser::getGroupsID();
                    $grpQuery = "";
                    foreach($usrGrps as $grp) {
                        if ($grpQuery != "") $grpQuery .= " || ";
                        if ($grp == "") $grp = -1;
                        $grpQuery .= "`group_owner` = $grp";
                    }
                    $secWhere  = "(( IF(`user_owner` = ".driverUser::getID().",1024,0) | ";
                    $secWhere .= "IF($grpQuery,64,0) | 4) ";
                    $secWhere .= "& `access`)";
                    if ($params["where"] != "") {
                        $params["where"] .= " && ".$secWhere;
                    } else {
                        $params["where"] = " where ".$secWhere;
                    }
                }
                if ($params["order"] != "") $params["order"] = " order by ".$params["order"];
                if ($params["group"] != "") $params["group"] = " group by ".$params["group"];
                $limit = $params["offset"];
                if ($params["length"] != "") {
                    if ($limit == "") $limit = "0";
                    $limit = $limit.", ".$params["length"];
                }
                if ($limit != "") $limit = " limit ".$limit;
                // Build query
                if ($params["count"] === true) {
                    $fieldList = "count(*) as amount";
                }
                $sql = "select {$fieldList} from `node_{$params["nodetype"]}` ";
                $sql .= "{$params["where"]} {$params["order"]} {$params["group"]} {$limit}";
                // Return data
                try {
                    $q = dbConn::Execute($sql);
                    $resp = array();
                    // Load direct data from recordset
                    while (!$q->EOF) {
                        $item = array();
                        foreach($q->fields as $field => $value) {
                            if (!is_numeric($field) && $field != "id") {
                                $item[$field] = $value;
                            }
                        }
                        if ($params["count"] === true) {
                            $resp[] = $item;
                        } else {
                            $item['id'] = $q->fields["id"];
                            $resp[$q->fields["id"]] = $item;
                        }
                        $q->MoveNext();
                    }
                    // Add the multivalue data
                    if ($params["count"] !== true) {
                        $multis = self::getFieldsMulti($nodeFields);
                        foreach($multis as $multi) {
                            $fDef = self::getFieldDef($multi, $nodeFields);
                            $relTable = '`node_relation_'.$params["nodetype"].'_'.$multi.'_'.$fDef["type"].'`';
                            foreach($resp as $id => $item) {
                                $sql = "select `type2` from $relTable where `type1` = $id";
                                $q = dbConn::Execute($sql);
                                $resp[$id][$multi] = array();
                                while (!$q->EOF) {
                                    $resp[$id][$multi][] = $q->fields["type2"];
                                    $q->MoveNext();
                                }
                            }
                        }
                    }
                    return $resp;
                } catch (Exception $exc) {
                    return array(
                        "ok" => false,
                        "msg" => $exc->getMessage(),
                        );
                }
            } else {
                return array(
                        "ok" => false,
                        "msg" => sprintf(__("Node type '%s' not found."), $params["nodetype"]),
                        );
            }
        }
        
        /**
         * Get field definition
         * @param string $name Queried field name
         * @param string $fields Fields definition array
         * @return array Field definition or FALSE if not found.
         */
        protected static function getFieldDef($name, &$fields) {
            foreach($fields as $field) {
                if ($field["name"] == $name) {
                    return $field;
                }
            }
            return false;
        }
        
        /**
         * Return a list of multivalue fields.
         * @param array $fields Fields definitions
         * @return array
         */
        protected static function getFieldsMulti(&$fields) {
            $resp = array();
            foreach($fields as $field) {
                if ($field["multi"]) {
                    $resp[] = $field["name"];
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
                "description" => __("Return list of nodes from a node type. All field's names must be enclosed with '`'"), 
                "parameters" => array(
                    "nodetype" => __("Node type."),
                    "count" => __("Bool, If true then return number, in a 'amount' field, of nodes but without node data."),
                    "fields" => __("Comma separated string list. Optional, default '*'."),
                    "where" => __("Where condition."),
                    "order" => __("Order by fields."),
                    "group" => __("Group by fields."),
                    "offset" => __("Index of first node to return. Optional, default 0."),
                    "length" => __("Number of nodes to return from the offset. Optional, default 100."),
                ), 
                "response" => array(
                    "rs" => __("Node array with the ID how index. Multivalued fields will be returned how related ID's array."),
                ),
                "type" => array(
                    "parameters" => array(
                        "nodetype" => "string",
                        "count" => "boolean",
                        "fields" => "string",
                        "where" => "string",
                        "order" => "string",
                        "group" => "string",
                        "offset" => "integer",
                        "length" => "integer",
                    ), 
                    "response" => array(
                        "rs" => "array",
                    ),
                )
            );
        }
    }
}
return new commandGetNodes();