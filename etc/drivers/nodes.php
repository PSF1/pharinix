<?php

/*
 *  Pharinix Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
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

/**
 * Data management
 *
 * @author Pedro Pelaez <aaaaa976@gmail.com>
 */
class driverNodes {

    const CHANGECRUD_OWNER = 8;
    const CHANGECRUD_GROUP = 4;
    const CHANGECRUD_ALL = 0;

    /**
     * Return the "where" condition to filter by access level
     * @param string $nodetype The node type to verify
     * @param integer $usrId User ID that must be node's owner, the default is active user
     * @param array $usrGrps ID of groups that could be owner, the default is the active user groups
     * @return string A chainable SQL "where" condition string. IMPORTANT: This method don't verify $nodetype.
     */
    public static function getAccessFilter($nodetype, $usrId = null, $usrGrps = null) {
        $secWhere = '';
        if ($usrId == null) {
            $usrId = driverUser::getID();
        }
        if ($usrGrps == null) {
            $usrGrps = driverUser::getGroupsID();
        }
        $grpQuery = "";
        foreach ($usrGrps as $grp) {
            if ($grpQuery != "")
                $grpQuery .= " || ";
            if ($grp == "")
                $grp = -1;
            $grpQuery .= "`node_$nodetype`.`group_owner` = $grp";
        }
        $secWhere = "(( IF(`node_$nodetype`.`user_owner` = " . $usrId . ",1024,0) | ";
        $secWhere .= "IF($grpQuery,64,0) | 4) ";
        $secWhere .= "& `node_$nodetype`.`access`)";
        return $secWhere;
    }

    /**
     * Change access flags of a node.
     * 
     * @param string $nodetype Node type name
     * @param integer $idnode Node ID
     * @param integer $segment See constants CHANGECRUD_*
     * @param boolean $create
     * @param boolean $read
     * @param boolean $update
     * @param boolean $delete
     * @return array ('ok' => boolean)
     */
    public static function chCRUDNode($nodetype, $idnode, $segment, $create, $read, $update, $delete) {
        $me = driverCommand::run('getNodes', array(
                    'nodetype' => $nodetype,
                    'fields' => '`access`',
                    'where' => '`id` = ' . $idnode,
        ));
        if ((!isset($me['ok']) || $me['ok'] !== false) && count($me) > 0) {
            $ncrud = decbin($me[$idnode]['access']);
            $require = ($create ? 1 : 0) . ($read ? 1 : 0) . ($update ? 1 : 0) . ($delete ? 1 : 0);
            switch ($segment) {
                case self::CHANGECRUD_ALL:
                    $require = substr($ncrud, 0, 8) . $require;
                    break;
                case self::CHANGECRUD_GROUP:
                    $require = substr($ncrud, 0, 4) . $require . substr($ncrud, 8);
                    break;
                case self::CHANGECRUD_OWNER:
                    $require = $require . substr($ncrud, 4);
                    break;
            }
            $require = bindec($require);
            return driverCommand::run('chmodNode', array(
                        'nodetype' => $nodetype,
                        'nid' => $idnode,
                        'flags' => $require,
            ));
        } else {
            $msg = __("Bad node id.");
            if (isset($me['msg'])) {
                $msg = $me['msg'];
            }
            return array("ok" => false, "msg" => $msg);
        }
    }

    /**
     * Read nodes from data base
     * 
     * @param array $params see getNodes command.
     * @param boolean $secured If FALSE don't filter by access security
     * @return array
     */
    public static function getNodes($params, $secured = true) {
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
                if ($fieldList != "")
                    $fieldList .= ",";
                $field = str_replace("`", "", trim($field));
                $fDef = self::getFieldDef($field, $nodeFields);
                if ($fDef === false && strpos($field, "*") === false && $field != "id") {
                    $fieldList .= "'error' as `$field`";
                } else if (strpos($field, "*") !== false) {
                    $fieldList .= "$field";
                } else {
                    $fieldList .= "`$field`";
                }
                if ($field == "id")
                    $haveId = true;
            }
            if (!$haveId) {
                if ($fieldList != "")
                    $fieldList .= ",";
                $fieldList .= "`id`";
            }
            // Build segments
            if ($params["where"] != "")
                $params["where"] = " where " . $params["where"];
            // Security control
            if ($secured) {
                if (!driverUser::isSudoed()) {
                    $usrGrps = driverUser::getGroupsID();
                    $grpQuery = "";
                    foreach ($usrGrps as $grp) {
                        if ($grpQuery != "")
                            $grpQuery .= " || ";
                        if ($grp == "")
                            $grp = -1;
                        $grpQuery .= "`group_owner` = $grp";
                    }
                    $secWhere = "(( IF(`user_owner` = " . driverUser::getID() . ",1024,0) | ";
                    $secWhere .= "IF($grpQuery,64,0) | 4) ";
                    $secWhere .= "& `access`)";
                    if ($params["where"] != "") {
                        $params["where"] .= " && " . $secWhere;
                    } else {
                        $params["where"] = " where " . $secWhere;
                    }
                }
            }
            if ($params["order"] != "")
                $params["order"] = " order by " . $params["order"];
            if ($params["group"] != "")
                $params["group"] = " group by " . $params["group"];
            $limit = $params["offset"];
            if ($params["length"] != "") {
                if ($limit == "")
                    $limit = "0";
                $limit = $limit . ", " . $params["length"];
            }
            if ($limit != "")
                $limit = " limit " . $limit;
            // Build query
            if ($params["count"] == true) {
                $fieldList = "count(*) as amount";
            }
            $sql = "select {$fieldList} from `node_{$params["nodetype"]}` ";
            $sql .= "{$params["where"]} {$params["group"]} {$params["order"]} {$limit}";
            // Return data
            try {
                $q = dbConn::Execute($sql);
                $resp = array();
                // Load direct data from recordset
                while (!$q->EOF) {
                    $item = array();
                    foreach ($q->fields as $field => $value) {
                        if (!is_numeric($field) && $field != "id") {
                            $item[$field] = $value;
                        }
                    }
                    if ($params["count"] == true) {
                        $resp[] = $item;
                    } else {
                        $item['id'] = $q->fields["id"];
                        $resp[$q->fields["id"]] = $item;
                    }
                    $q->MoveNext();
                }
                // Add the multivalue data
                if ($params["count"] != true) {
                    $multis = self::getFieldsMulti($nodeFields);
                    foreach ($multis as $multi) {
                        $fDef = self::getFieldDef($multi, $nodeFields);
                        $relTable = '`node_relation_' . $params["nodetype"] . '_' . $multi . '_' . $fDef["type"] . '`';
                        foreach ($resp as $id => $item) {
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
     * Add a new node
     * 
     * @param array $params see addNode command.
     * @param boolean $secured If FALSE don't filter by access security
     */
    public static function addNode($params, $secured = true) {
        $resp = array("ok" => false, "nid" => 0, "msg" => "");

        // Default values
        $params = array_merge(array(
            "nodetype" => "",
                ), $params);
        if ($params["nodetype"] == "") { // Node type defined?
            $resp["msg"] = __("Node type required");
        } else {
            // Erase insecure parameters for nodes
            unset($params["access"]);
            unset($params["user_owner"]);
            unset($params["group_owner"]);
            unset($params["modifier"]);
            unset($params["modified"]);
            unset($params["creator"]);
            unset($params["created"]);
            $params["modifier"] = driverUser::getID(true);
            $params["modified"] = date("Y-m-d H:i:s");
            $params["created"] = date("Y-m-d H:i:s");
            $params["creator"] = driverUser::getID();
            $params["user_owner"] = driverUser::getID();
            $params["group_owner"] = driverUser::getDefaultGroupID();

            // Verify node type
            $typeDef = driverCommand::run("getNodeTypeDef", array(
                        "nodetype" => $params["nodetype"],
            ));
            if ($typeDef["id"] === false) { // I dont know the node type
                $resp["msg"] = sprintf(__("Unknowed node type '%s'"), $params["nodetype"]);
            } else {
                // Access control
                $usrGrps = driverUser::getGroupsID();
                $allowed = !$secured || driverUser::secNodeCanCreate($typeDef["access"], $typeDef["user_owner"] == driverUser::getID(), array_search($typeDef["group_owner"], $usrGrps) !== FALSE);
                if ($allowed) {
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
                        $resp["msg"] = __("Missing node field required.");
                    } else {
                        // All selected items are fields of the node?
                        $allOk = true;
                        foreach ($params as $name => $value) {
                            if ($name != "nodetype") {
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
                            $resp["msg"] = sprintf(__("Field '%s' are not fields of '%s' node type."), $allOk, $params["nodetype"]);
                        } else {
                            // Duplicated keys?
                            $where = "";
                            foreach ($ndefFields as $ndefField) {
                                if ($ndefField["iskey"]) {
                                    if ($where != "")
                                        $where .= " || ";
                                    $where .= "`{$ndefField["name"]}` = '" . dbConn::qstr($params[$ndefField["name"]]) . "'";
                                }
                            }
                            // Some?
                            $ctrl = 0;
                            if ($where != "") {
                                $sql = "select count(*) from `node_{$params["nodetype"]}` where $where";
                                $q = dbConn::Execute($sql);
                                $ctrl = $q->fields[0];
                            }
                            if ($ctrl > 0) {
                                // Duplicate keys
                                $resp["msg"] = __("Duplicate keys.");
                            } else {
                                // NOW, we can save node !! :D :D
                                $sql = "";
                                $sqlMultis = array();
                                foreach ($params as $name => $value) {
                                    // Ignore nodetype parameter because isn't a field
                                    if ($name != "nodetype") {
                                        $fieldDef = self::getFieldDef($name, $ndefFields);
                                        if ($fieldDef["multi"]) {
                                            if ($value != '') {
                                                // Prepare all multivalue inserts.
                                                $vals = explode(",", $value);
                                                $table = '`node_relation_' . $params["nodetype"] . '_' . $name . '_' . $fieldDef["type"] . '`';
                                                $multi = "";
                                                foreach ($vals as $val) {
                                                    if ($multi != "")
                                                        $multi .= ", ";
                                                    $multi .= " (null, {NID}, $val)";
                                                }
                                                $sqlMultis[] = "insert into $table values " . $multi;
                                            }
                                        } else {
                                            // Single value fields
                                            if ($sql != "")
                                                $sql .= ", ";
                                            if ($fieldDef["type"] == "password") { // Type password
                                                $fVal = driverUser::passwordObfuscation($value);
                                            } else { // Type other
                                                $fVal = dbConn::qstr($value);
                                            }
                                            $sql .= "`$name` = '" . $fVal . "'";
                                        }
                                    }
                                }
                                $sql = "insert into `node_{$params["nodetype"]}` set " . $sql;
                                dbConn::Execute($sql);
                                $last = dbConn::lastID();
                                $resp["nid"] = $last;
                                $resp["ok"] = true;
                                // Add Multi values
                                foreach ($sqlMultis as $sqlMulti) {
                                    $sqlMulti = str_replace("{NID}", $last, $sqlMulti);
                                    dbConn::Execute($sqlMulti);
                                }
                                // Add personalized page
                                // since Pharinix 1.12.04 node types use context URL mapping.
//                                    driverCommand::run("addPage", array(
//                                        'name' => "node_type_".$params["nodetype"]."_".$last,
//                                        'template' => "etc/templates/pages/default.xml",
//                                        'title' => "Node {$last}",
//                                        'description' => "",
//                                        'keys' => "",
//                                    ));
//                                    driverCommand::run("addBlockToPage", array(
//                                        'page' => "node_type_".$params["nodetype"]."_".$last,
//                                        'command' => "getNodeHtml",
//                                        'parameters' => "nodetype=".$params["nodetype"]."&node=$last",
//                                    ));
                            }
                        }
                    }
                } else {
                    $resp["msg"] = __("You can't add nodes.");
                }
            }
        }
        return $resp;
    }

    /**
     * Update a node
     * 
     * @param array $params see updateNode command.
     * @param boolean $secured If FALSE don't filter by access security
     */
    public static function updateNode($params, $secured = true) {
        $resp = array("ok" => false, "msg" => "");

        // Default values
        $params = array_merge(array(
            "nodetype" => "",
            "nid" => "",
                ), $params);

        if ($params["nodetype"] == "") { // Node type defined?
            $resp["msg"] = __("Node type required");
        } else {
            // Erase insecure parameters for user nodes
            if ($secured && !driverUser::isSudoed() && $params["nodetype"] == "user") {
                unset($params["groups"]);
            }
            // Erase insecure parameters for nodes
            unset($params["access"]);
            unset($params["user_owner"]);
            unset($params["group_owner"]);
            unset($params["modifier"]);
            unset($params["modified"]);
            unset($params["creator"]);
            unset($params["created"]);
            $params["modifier"] = driverUser::getID(true);
            $params["modified"] = date("Y-m-d H:i:s");

            // 
            $nodeAccess = 0;
            $nodeUser_owner = 0;
            $nodeGroup_owner = 0;
            if ($params["nid"] == "") {
                $resp["msg"] = __("Node ID required");
                return $resp;
            } else {
                $eof = true;
                try {
                    $sql = "select `id`, `access`, `user_owner`, `group_owner` from `node_{$params["nodetype"]}` where `id` = " . $params["nid"];
                    $q = dbConn::Execute($sql);
                    $eof = $q->EOF;
                    if (!$eof) {
                        $nodeAccess = $q->fields["access"];
                        $nodeUser_owner = $q->fields["user_owner"];
                        $nodeGroup_owner = $q->fields["group_owner"];
                    }
                } catch (Exception $ex) {
                    $eof = true;
                }
                if ($eof) {
                    $resp["msg"] = __("Unknowed node ID");
                    return $resp;
                }
            }
            $ntid = driverCommand::run("getNodeTypeId", array("name" => $params["nodetype"]));
            $ndefFields = driverCommand::run("getNodeTypeDef", $params);
            $ntid = $ndefFields["id"];
            if ($ntid === false) { // I dont know the node type
                $resp["msg"] = sprintf(__("Unknowed node type '%s'"), $params["nodetype"]);
            } else {
                // -------------------------------
                // Access control
                $usrGrps = driverUser::getGroupsID();
                $allowed = !$secured || driverUser::secNodeCanUpdate($nodeAccess, $nodeUser_owner == driverUser::getID(), array_search($nodeGroup_owner, $usrGrps) !== FALSE);
                if (!$allowed) {
                    $allowed = driverUser::secNodeCanUpdate($ndefFields["access"], $ndefFields["user_owner"] == driverUser::getID(), array_search($ndefFields["group_owner"], $usrGrps) !== FALSE);
                }
                if ($allowed) {
                    $ndefFields = $ndefFields["fields"];
                    // Required fields presents? (required or iskey)
                    $okRequired = true;
                    //                    foreach ($ndefFields as $ndefField) {
                    //                        if (($ndefField["required"] || $ndefField["iskey"]) && !isset($params[$ndefField["name"]])) {
                    //                            $okRequired = false;
                    //                            break;
                    //                        }
                    //                    }
                    if (!$okRequired) {
                        // You miss a required node field
                        $resp["msg"] = __("Missing node field required.");
                    } else {
                        // All selected items are fields of the node?
                        $allOk = true;
                        foreach ($params as $name => $value) {
                            if ($name != "nodetype" && $name != "nid" && $name != "id") {
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
                            $resp["msg"] = sprintf(__("Field '%s' are not fields of '%s' node type."), $allOk, $params["nodetype"]);
                        } else {
                            // Duplicated keys?
                            $where = "";
                            foreach ($ndefFields as $ndefField) {
                                if ($ndefField["iskey"] && isset($params[$ndefField["name"]])) {
                                    if ($where != "")
                                        $where .= " || ";
                                    $where .= "`{$ndefField["name"]}` = '" . dbConn::qstr($params[$ndefField["name"]]) . "'";
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
                                $resp["msg"] = __("Duplicate keys.");
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
                                            if ($value != '') {
                                                // Prepare all multivalue inserts.
                                                $vals = explode(",", $value);
                                                $table = '`node_relation_' . $params["nodetype"] . '_' . $name . '_' . $fieldDef["type"] . '`';
                                                $tableMultis[] = $table;
                                                $multi = "";
                                                foreach ($vals as $val) {
                                                    if ($val != '') {
                                                        if ($multi != "")
                                                            $multi .= ", ";
                                                        $multi .= " (null, {NID}, $val)";
                                                    }
                                                }
                                                if ($multi != '') {
                                                    $sqlMultis[] = "insert into $table values " . $multi;
                                                }
                                            }
                                        } else {
                                            // Single value fields
                                            if ($sql != "")
                                                $sql .= ", ";
                                            if ($fieldDef["type"] == "password") { // Type password
                                                $fVal = driverUser::passwordObfuscation($value);
                                            } else { // Type other
                                                $fVal = dbConn::qstr($value);
                                            }
                                            $sql .= "`$name` = '" . $fVal . "'";
                                        }
                                    }
                                }
                                $sql = "update `node_{$params["nodetype"]}` set " . $sql;
                                $sql .= " where `id` = {$params["nid"]}";
                                dbConn::Execute($sql);
                                $last = $params["nid"];
                                $resp["ok"] = true;
                                // Clear multi values tables
                                foreach ($tableMultis as $table) {
                                    $sql = "delete from $table where `type1` = $last";
                                    dbConn::Execute($sql);
                                }
                                // Add Multi values
                                foreach ($sqlMultis as $sqlMulti) {
                                    $sqlMulti = str_replace("{NID}", $last, $sqlMulti);
                                    dbConn::Execute($sqlMulti);
                                }
                            }
                        }
                    }
                } else {
                    $resp["msg"] = __("You can't update nodes.");
                }
            }
        }
        return $resp;
    }

    /**
     * Get field definition
     * @param string $name Queried field name
     * @param string $fields Fields definition array
     * @return array Field definition or FALSE if not found.
     */
    protected static function getFieldDef($name, &$fields) {
        foreach ($fields as $field) {
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
        foreach ($fields as $field) {
            if ($field["multi"]) {
                $resp[] = $field["name"];
            }
        }
        return $resp;
    }

}
