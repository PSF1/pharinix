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
     * List of core node types
     * @return array 
     */
    public static function getSystemNodeTypes() {
        return array('user', 'group', 'menu', 'modules');
    }
    
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
            $resp = driverCommand::run('chmodNode', array(
                        'nodetype' => $nodetype,
                        'nid' => $idnode,
                        'flags' => $require,
            ));
            $resp['flags'] = $require;
            return $resp;
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
        driverHook::CallHook('driverNodesBeforeAddNodeHook', array(
            'params' => &$params,
            'secured' => &$secured,
        ));
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
        driverHook::CallHook('driverNodesAfterAddNodeHook', array(
            'resp' => &$resp,
            'params' => &$params,
            'secured' => &$secured,
        ));
        return $resp;
    }

    /**
     * Update a node
     * 
     * @param array $params see updateNode command.
     * @param boolean $secured If FALSE don't filter by access security
     */
    public static function updateNode($params, $secured = true) {
        driverHook::CallHook('driverNodesBeforeUpdateNodeHook', array(
            'params' => &$params,
            'secured' => &$secured,
        ));
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
                driverHook::CallHook('driverNodesAfterUpdateNodeHook', array(
                    'resp' => &$resp,
                    'params' => &$params,
                    'secured' => &$secured,
                ));
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
                    driverHook::CallHook('driverNodesAfterUpdateNodeHook', array(
                        'resp' => &$resp,
                        'params' => &$params,
                        'secured' => &$secured,
                    ));
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
        driverHook::CallHook('driverNodesAfterUpdateNodeHook', array(
            'resp' => &$resp,
            'params' => &$params,
            'secured' => &$secured,
        ));
        return $resp;
    }

    /**
     * Delete a node.
     * 
     * @param array $params
     * @param boolean $secured  If FALSE don't filter by access security
     * @return array
     */
    public static function delNode($params, $secured = true) {
        driverHook::CallHook('driverNodesBeforeDelNodeHook', array(
            'params' => &$params,
            'secured' => &$secured,
        ));
        $resp = array("ok" => false, "msg" => "");

        // Default values
        $params = array_merge(array(
            "nodetype" => "",
            "nid" => "",
                ), $params);
        if ($params["nodetype"] == "") { // Node type defined?
            $resp["msg"] = __("Node type required");
        } else if ($params["nid"] == "") {
            $resp["msg"] = __("Node ID required");
        } else {
            try {
                $def = driverCommand::run("getNodeTypeDef", $params);
                $nodeAccess = 0;
                $nodeUser_owner = 0;
                $nodeGroup_owner = 0;
                $sql = "select `id`, `access`, `user_owner`, `group_owner` from `node_{$params["nodetype"]}` where `id` = " . $params["nid"];
                $q = dbConn::Execute($sql);
                if (!$q->EOF) {
                    $nodeAccess = $q->fields["access"];
                    $nodeUser_owner = $q->fields["user_owner"];
                    $nodeGroup_owner = $q->fields["group_owner"];
                }
                $usrGrps = driverUser::getGroupsID();
                $allowed = driverUser::secNodeCanDelete($nodeAccess, $nodeUser_owner == driverUser::getID(), array_search($nodeGroup_owner, $usrGrps) !== FALSE);
                if (!$allowed) {
                    $allowed = driverUser::secNodeCanDelete($def["access"], $def["user_owner"] == driverUser::getID(), array_search($def["group_owner"], $usrGrps) !== FALSE);
                }
                if (!$secured || $allowed) {
                    // Delete relations
                    foreach ($def["fields"] as $field) {
                        if ($field["multi"]) {
                            $table = '`node_relation_' . $params["nodetype"] . '_'
                                    . $field["name"] . '_' . $field["type"] . '`';
                            $sql = "delete from $table where `type1` = " . $params["nid"];
                            dbConn::Execute($sql);
                        }
                    }
                    // Delete node
                    $sql = "delete from `node_{$params["nodetype"]}` where `id` = " . $params["nid"];
                    dbConn::Execute($sql);
                    // Delete page
                    // since Pharinix 1.12.04 node types use context URL mapping.
//                        driverCommand::run("delPage", array(
//                            'name' => "node_type_" . $params["nodetype"] . "_" . $params["nid"],
//                        ));
                    $resp["ok"] = true;
                } else {
                    $resp["msg"] = __("You can't delete nodes.");
                }
            } catch (Exception $exc) {
                $resp["msg"] = $exc->getMessage();
            }
        }
        driverHook::CallHook('driverNodesAfterDelNodeHook', array(
            'resp' => &$resp,
            'params' => &$params,
            'secured' => &$secured,
        ));
        return $resp;
    }
    
    /**
     * Generate two drivers, first a general controler and, if not exist, a user controler.<br>
     * Files that will be generated:<br>
     * Base: driverNodeType[NodeType Name]Base<br>
     * User: driverNodeType[NodeType Name]<br>
     * <br>
     * Example with group nodetype:<br>
     * Base: driverNodeTypeGroupBase<br>
     * User: driverNodeTypeGroup <-- Human modificable<br>
     * <br>
     * @param string $nodetype Node type to control
     * @param string $module_slugname Owner module slugname
     * @param boolean $debug Show generated code and node type definition.
     * @return boolean TRUE if generate the code
     */
    public static function generateDriver($nodetype, $module_slugname, $debug = false) {
        $resp = false;
        $nodetype = strtolower($nodetype);
        $modPath = driverCommand::getModPath($module_slugname);
        if ($modPath != '') {
            $nodetype = driverCommand::run('getNodeTypeDef', array(
                'nodetype' => $nodetype,
            ));
            if ($nodetype['id'] !== false) {
                if(!is_dir($modPath.'drivers')) {
                    mkdir($modPath.'drivers');
                }
                // New class names
                $human = 'driverNodeType'.strtoupper(substr($nodetype['name'], 0, 1)).substr($nodetype['name'], 1);
                $base = $human.'Base';
                
                // Generate base class
                $lines = array();
                $lines[] = "<?php";
                $lines[] = "/**";
                $lines[] = " * Autogenerated file, PLEASE DON'T MODIFY IT. Changes will be overwrited.";
                $lines[] = " * To apply logic edit the file $human.php";
                $lines[] = " *";
                $lines[] = " * Generator: Pharinix/" . CMS_VERSION;
                $lines[] = " * Generated: " . date('Y-m-d H:i:s');
                $lines[] = " * Node type: " . $nodetype['name'];
                $lines[] = " * Module: " . $module_slugname;
                $lines[] = " */";
                $lines[] = "";
                $lines[] = "/**";
                $lines[] = " * Driver base class to the Node type: " . $nodetype['name'];
                $lines[] = " */";
                $lines[] = "class $base extends driverNodeBase {";
                $lines[] = "\t// Attributes";
//                $lines[] = "\tvar \$id = 0;";
                $constructorInit = array();
                foreach($nodetype['fields'] as $field) {
                    $type = 'string';
                    $default = "''";
                    switch ($field['type']) {
                        case "htmltext":
                        case "longtext":
                        case "string":
                        case "password":
                            $type = "string [{$field['name']}] ({$field['type']})";
                            $default = "'{$field['default']}'";
                            break;
                        case "bool":
                            $type = "boolean [{$field['name']}]";
                            $default = $field['default'] == '1'?"true":"false";
                            break;
                        case "datetime":
                            $type = "integer [{$field['name']}] ({$field['type']} -> Unix time stamp)";
                            $default = '0';
                            $constructorInit[] = array(
                                'attr' => $field['name'],
                                'value' => "time()",
                            );
                            break;
                        case "integer":
                        case "nodesec":
                            $type = "integer [{$field['name']}] ({$field['type']})";
                            $default = intval($field['default']);
                            break;
                        case "double":
                            $type = "float [{$field['name']}]";
                            $default = "{$field['default']}";
                            break;
                        default: // Node type
                            if ($field['multi']) {
                                $type = "array [{$field['name']}] ({$field['type']})";
                                $default = "array()";
                            } else {
                                $type = "integer [{$field['name']}] ({$field['type']})";
                                $default = "{$field['default']}";
                            }
                            break;
                    }
                    $lines[] = "\t/** @var $type {$field['help']} */";
                    $lines[] = "\tvar \${$field['name']} = $default;";
                }
                $lines[] = "";
                // Constructor
                $lines[] = "\t/**";
                $lines[] = "\t * Constructor";
                $lines[] = "\t *";
                $lines[] = "\t * @param integer \$id Node ID, 0 to new instances.";
                $lines[] = "\t * @param integer \$node getNode response, it's needed to start the instance.";
                $lines[] = "\t */";
                $lines[] = "\tpublic function __construct(\$id = 0, \$node = null) {";
                $lines[] = "\t\t\$this->nodetype = '{$nodetype['name']}';";
                foreach($constructorInit as $init) {
                    $lines[] = "\t\t\$this->{$init['attr']} = {$init['value']};";
                }
                $lines[] = "\t\tif (\$id != 0 && \$node != null) {";
                $lines[] = "\t\t\t// Set data";
                $lines[] = "\t\t\t\$this->id = \$id;";
                // Add assign datafields
                foreach($nodetype['fields'] as $field) {
                    $lines[] = "\t\t\t\$this->{$field['name']} = \$node['{$field['name']}'];";
                }
                $lines[] = "\t\t}";
                $lines[] = "";
                $lines[] = "\t}";
                $lines[] = "";
                $lines[] = "\t// Methods";
                $lines[] = "";
                // Save method
                $lines[] = "\tpublic function save() {";
                $lines[] = "\t\t\$params = array(";
                $lines[] = "\t\t\t'nodetype' => '{$nodetype['name']}',";
                foreach($nodetype['fields'] as $field) {
                    if (!$field['locked']) {
                        switch ($field['type']) {
                            case "htmltext":
                            case "longtext":
                            case "string":
                            case "password":
                                $lines[] = "\t\t\t'{$field['name']}' => \$this->{$field['name']},";
                                break;
                            case "bool":
                                $lines[] = "\t\t\t'{$field['name']}' => \$this->{$field['name']}?'1':'0',";
                                break;
                            case "datetime":
                                $lines[] = "\t\t\t'{$field['name']}' => date('Y-m-d H:i:s', \$this->{$field['name']}),";
                                break;
                            case "integer":
                            case "nodesec":
                            case "double":
                                $lines[] = "\t\t\t'{$field['name']}' => \$this->{$field['name']},";
                                break;
                            default: // Node type
                                if ($field['multi']) {
                                    $lines[] = "\t\t\t'{$field['name']}' => join(',', \$this->{$field['name']}),";
                                } else {
                                    $lines[] = "\t\t\t'{$field['name']}' => \$this->{$field['name']},";
                                }
                                break;
                        }
                    }
                }
                $lines[] = "\t\t);";
                $lines[] = "\t\tif (\$this->id != 0) {";
                $lines[] = "\t\t\t\$params['nid'] = \$this->id;";
                $lines[] = "\t\t\t\$resp = driverNodes::updateNode(\$params, true);";
                $lines[] = "\t\t\tdriverNodeBase::cacheDel('{$nodetype['name']}', \$this->id);";
                $lines[] = "\t\t\treturn \$resp;";
                $lines[] = "\t\t} else {";
                $lines[] = "\t\t\t\$resp = driverNodes::addNode(\$params, true);";
                $lines[] = "\t\t\tif(\$resp['ok']) {";
                $lines[] = "\t\t\t\t\$this->id = \$resp['nid'];";
                $lines[] = "\t\t\t}";
                $lines[] = "\t\t\treturn \$resp;";
                $lines[] = "\t\t}";
                $lines[] = "\t\treturn false;";
                $lines[] = "\t}";
                $lines[] = "";
                // Remove method
                $lines[] = "\t/**";
                $lines[] = "\t * Remove this instance and set ID to cero, 0.";
                $lines[] = "\t */";
                $lines[] = "\tpublic function remove() {";
                $lines[] = "\t\t\$resp = driverNodes::delNode(array('nodetype' => \$this->nodetype, 'nid' => \$this->id));";
                $lines[] = "\t\tif (\$resp['ok'] === true) {";
                $lines[] = "\t\t\t\$this->id = 0;";
                $lines[] = "\t\t}";
                $lines[] = "\t}";
                $lines[] = "";
                // findAll method
                $lines[] = "\t/**";
                $lines[] = "\t * Find all instances.";
                $lines[] = "\t *";
                $lines[] = "\t * @param string \$order Order results";
                $lines[] = "\t * @param integer \$offset ";
                $lines[] = "\t * @param integer \$len ";
                $lines[] = "\t * @return array of $human Instances";
                $lines[] = "\t */";
                $lines[] = "\tpublic static function findAll(\$order = '', \$offset = 0, \$len = -1) {";
                $lines[] = "\t\t\$resp = array();";
                $lines[] = "\t\t\$query = array('nodetype' => '{$nodetype['name']}');";
                $lines[] = "\t\t\$query['order'] = \$order;";
                $lines[] = "\t\t\$query['offset'] = \$offset;";
                $lines[] = "\t\tif (\$len > 0) \$query['length'] = \$len;";
                $lines[] = "\t\t\$nodes = driverNodes::getNodes(\$query);";
                $lines[] = "\t\tforeach(\$nodes as \$id => \$node) {";
                $lines[] = "\t\t\t\$item = new $human(\$id, \$node);";
                $lines[] = "\t\t\t\$resp[] = \$item;";
                $lines[] = "\t\t}";
                $lines[] = "\t\treturn \$resp;";
                $lines[] = "\t}";
                $lines[] = "";
                // findByID method
                $lines[] = "\t/**";
                $lines[] = "\t * Find instance by ID.";
                $lines[] = "\t *";
                $lines[] = "\t * @param integer \$id Node ID";
                $lines[] = "\t * @return $human Instance or null if not found";
                $lines[] = "\t */";
                $lines[] = "\tpublic static function findByID(\$id) {";
                $lines[] = "\t\t\$nodes = driverNodes::getNodes(array('nodetype' => '{$nodetype['name']}', 'where' => '`id` = '.\$id));";
                $lines[] = "\t\tforeach(\$nodes as \$id => \$node) {";
                $lines[] = "\t\t\t\$item = new $human(\$id, \$node);";
                $lines[] = "\t\t\treturn \$item;";
                $lines[] = "\t\t}";
                $lines[] = "\t\treturn null;";
                $lines[] = "\t}";
                $lines[] = "";
                // findByID method
                $lines[] = "\t/**";
                $lines[] = "\t * Find instance by field.";
                $lines[] = "\t *";
                $lines[] = "\t * @param string \$condition SQL where conditions";
                $lines[] = "\t * @param string \$order Order results";
                $lines[] = "\t * @param integer \$offset ";
                $lines[] = "\t * @param integer \$len ";
                $lines[] = "\t * @return array of $human Instances";
                $lines[] = "\t */";
                $lines[] = "\tpublic static function findBy(\$condition, \$order = '', \$offset = 0, \$len = -1) {";
                $lines[] = "\t\t\$resp = array();";
                $lines[] = "\t\t\$query = array('nodetype' => '{$nodetype['name']}', 'where' => \$condition);";
                $lines[] = "\t\t\$query['order'] = \$order;";
                $lines[] = "\t\t\$query['offset'] = \$offset;";
                $lines[] = "\t\tif (\$len > 0) \$query['length'] = \$len;";
                $lines[] = "\t\t\$nodes = driverNodes::getNodes(\$query);";
                $lines[] = "\t\tforeach(\$nodes as \$id => \$node) {";
                $lines[] = "\t\t\t\$item = new $human(\$id, \$node);";
                $lines[] = "\t\t\t\$resp[] = \$item;";
                $lines[] = "\t\t}";
                $lines[] = "\t\treturn \$resp;";
                $lines[] = "\t}";
                $lines[] = "";
                // Getters and setters
                $lines[] = "\t// Getters & Setters";
                $lines[] = "";
//                $lines[] = "\tpublic function getID() {";
//                $lines[] = "\t\treturn \$this->id;";
//                $lines[] = "\t}";
//                $lines[] = "\tprotected function setID(\$value) {";
//                $lines[] = "\t\t\$this->id = \$value;";
//                $lines[] = "\t}";
//                $lines[] = "";
                foreach($nodetype['fields'] as $field) {
                    // Getters
                    $type = 'string';
                    $default = "''";
                    switch ($field['type']) {
                        case "htmltext":
                        case "longtext":
                        case "string":
                        case "password":
                            $type = "string";
                            $default = "'{$field['default']}'";
                            break;
                        case "bool":
                            $type = "boolean";
                            $default = $field['default'] == '1'?"true":"false";
                            break;
                        case "datetime":
                            $type = "integer";
                            $default = 'time()';
                            break;
                        case "integer":
                        case "nodesec":
                            $type = "integer";
                            $default = "{$field['default']}";
                            break;
                        case "double":
                            $type = "float";
                            $default = "{$field['default']}";
                            break;
                        default: // Node type
                            if ($field['multi']) {
                                $type = "array";
                                $default = "array()";
                            } else {
                                $type = "integer";
                                $default = "{$field['default']}";
                            }
                            break;
                    }
                    $lines[] = "\t/**";
                    $lines[] = "\t * @return $type {$field['help']}";
                    $lines[] = "\t */";
                    $lines[] = "\tpublic function get".strtoupper(substr($field['name'], 0, 1)).substr($field['name'], 1)."() {";
                    $lines[] = "\t\treturn \$this->{$field['name']};";
                    $lines[] = "\t}";
                    $lines[] = "";
                    // Special getters
                    switch ($field['name']) {
                        case 'user_owner':
                        case 'group_owner':
                            $lines[] = "\t/**";
                            $lines[] = "\t * This method only return the node if the user is a root user.";
                            $lines[] = "\t */";
                            $lines[] = "\tpublic function get".strtoupper(substr($field['name'], 0, 1)).substr($field['name'], 1)."Node() {";
                            if ($field['name'] == 'user_owner') {
                                $lines[] = "\t\treturn driverNodes::getNodes(array('nodetype' => 'user', 'where' => '`id` = '.\$this->user_owner));";
                            } else {
                                $lines[] = "\t\treturn driverNodes::getNodes(array('nodetype' => 'group', 'where' => '`id` = '.\$this->group_owner));";
                            }
                            $lines[] = "\t}";
                            $lines[] = "";
                            break;
                        case 'access':
                        case 'created':
                        case 'creator':
                        case 'modified':
                        case 'modifier':
                            break;
                        default:
                            // Nothing to do
                            break;
                    }
                    // Setters
                    $visibility = 'public';
                    if ($field['readonly']) {
                        $visibility = 'protected';
                    }
                    $lines[] = "\t/**";
                    switch ($field['name']) {
                        case 'user_owner':
                        case 'group_owner':
                        case 'access':
                            $lines[] = "\t * Update and save, only if the node exist.";
                            break;
                    }
                    $lines[] = "\t * @param $type \$value {$field['help']}";
                    $lines[] = "\t */";
                    $lines[] = "\t$visibility function set".strtoupper(substr($field['name'], 0, 1)).substr($field['name'], 1)."(\$value) {";
                    switch ($field['name']) {
                        case 'user_owner':
                            $lines[] = "\t\tif(\$this->id == 0) return;";
                            $lines[] = "\t\t\$resp = driverCommand::run('chownNode', array('nodetype' => '{$nodetype['name']}', 'nid' => \$this->id, 'owner' => \$value));";
                            $lines[] = "\t\tif (\$resp['ok'] === true) {";
                            $lines[] = "\t\t\t\$this->user_owner = \$value;";
                            $lines[] = "\t\t}";
                            break;
                        case 'group_owner':
                            $lines[] = "\t\tif(\$this->id == 0) return;";
                            $lines[] = "\t\t\$resp = driverCommand::run('chownNode', array('nodetype' => '{$nodetype['name']}', 'nid' => \$this->id, 'group' => \$value));";
                            $lines[] = "\t\tif (\$resp['ok'] === true) {";
                            $lines[] = "\t\t\t\$this->group_owner = \$value;";
                            $lines[] = "\t\t}";
                            break;
                        case 'access':
                            $lines[] = "\t\tif(\$this->id == 0) return;";
                            $lines[] = "\t\t\$resp = driverCommand::run('chmodNode', array('nodetype' => '{$nodetype['name']}', 'nid' => \$this->id, 'flags' => \$value));";
                            $lines[] = "\t\tif (\$resp['ok'] === true) {";
                            $lines[] = "\t\t\t\$this->access = \$value;";
                            $lines[] = "\t\t}";
                            break;
                        case 'created':
                        case 'creator':
                        case 'modified':
                        case 'modifier':
                            $lines[] = "\t\t// Can't be modified...";
                            break;
                        default:
                            if ($field['multi']) {
                                $lines[] = "\t\t\$this->{$field['name']} = array_unique(\$value);";
                            } else {
                                $lines[] = "\t\t\$this->{$field['name']} = \$value;";
                            }
                            break;
                    }
                    $lines[] = "\t}";
                    $lines[] = "";
                    if ($field['name'] == 'access') {
                        $segments = array('All' => 0, 'Group' => 4, 'Owner' => 8);
                        foreach($segments as $segment => $id) {
                            $lines[] = "\t/**";
                            $lines[] = "\t * Update, and save, the {$segment} access segment, only if the node exist.";
                            $lines[] = "\t *";
                            $lines[] = "\t * @param boolean \$c Can create?";
                            $lines[] = "\t * @param boolean \$r Can read?";
                            $lines[] = "\t * @param boolean \$u Can update?";
                            $lines[] = "\t * @param boolean \$d Can delete?";
                            $lines[] = "\t */";
                            $lines[] = "\t$visibility function setAccess".$segment."(\$c, \$r, \$u, \$d) {";
                            $lines[] = "\t\t\$resp = driverCommand::run('chCRUDNode', array('nodetype' => '{$nodetype['name']}', 'nid' => \$this->id, 'segment' => {$id}, 'create' => \$c, 'read' => \$r, 'update' => \$u, 'delete' => \$d));";
                            $lines[] = "\t\tif (\$resp['ok'] === true) {";
                            $lines[] = "\t\t\t\$this->access = \$resp['flags'];";
                            $lines[] = "\t\t}";
                            $lines[] = "\t}";
                            $lines[] = "";
                        }
                    }
                }
                // Multi value fields
                foreach ($nodetype['fields'] as $field) {
                    switch ($field['type']) {
                        case "htmltext":
                        case "longtext":
                        case "string":
                        case "password":
                        case "bool":
                        case "datetime":
                        case "integer":
                        case "nodesec":
                        case "double":
                        default: // Node type
                            if ($field['multi']) {
                                $lines[] = "\t/**";
                                $lines[] = "\t * @return array Instances of {$field['type']}, {$field['help']}";
                                $lines[] = "\t */";
                                $lines[] = "\tpublic function getInstancesOf" . strtoupper(substr($field['name'], 0, 1)) . substr($field['name'], 1) . "() {";
                                $lines[] = "\t\t\$modPath = driverCommand::getModPath('$module_slugname');";
                                $lines[] = "\t\treturn driverNodeBase::getInstancesOf(\$modPath, '{$field['type']}', \$this->get" . strtoupper(substr($field['name'], 0, 1)) . substr($field['name'], 1) . "());";
                                $lines[] = "\t}";
                                $visibility = 'public';
                                if ($field['locked']) {
                                    $visibility = 'protected';
                                }
                                $lines[] = "\t/**";
                                $lines[] = "\t * @param array \$value {$field['help']}";
                                $lines[] = "\t */";
                                $lines[] = "\t$visibility function setInstancesOf" . strtoupper(substr($field['name'], 0, 1)) . substr($field['name'], 1) . "(\$value) {";
                                $lines[] = "\t\t\$ids = array();";
                                $lines[] = "\t\tforeach(\$value as \$node) {";
                                $lines[] = "\t\t\tif(is_array(\$node)) {";
                                $lines[] = "\t\t\t\t\$ids[] = \$node['id'];";
                                $lines[] = "\t\t\t} elseif (\$node instanceof driverNodeBase) {";
                                $lines[] = "\t\t\t\t\$ids[] = \$node->getID();";
                                $lines[] = "\t\t\t}";
                                $lines[] = "\t\t}";
                                $lines[] = "\t\t\$this->set" . strtoupper(substr($field['name'], 0, 1)) . substr($field['name'], 1) . "(\$ids);";
                                $lines[] = "\t}";
                                $lines[] = "";
                            } else {
                                
                            }
                            break;
                    }
                }
                $lines[] = "}";
                $fBase = fopen($modPath.'drivers/'.$base.'.php', 'w');
                foreach($lines as $line) {
                    fwrite($fBase, str_replace("\t", "    ", $line)."\n");
                }
                fclose($fBase);
                if ($debug) {
                    echo '<h3>'.$modPath.'drivers/'.$base.'.php'.'</h3>';
                    echo '<pre>';
                    $file = file_get_contents($modPath.'drivers/'.$base.'.php');
                    echo str_replace('<?php', '', $file);
                    echo '</pre>';
                }
                // Generate human class
                if (!is_file($modPath.'drivers/'.$human.'.php')) {
                    $lines = array();
                    $lines[] = "<?php";
                    $lines[] = "/**";
                    $lines[] = " * Autogenerated file, you can modify it without worry.";
                    $lines[] = " *";
                    $lines[] = " * Generator: Pharinix/".CMS_VERSION;
                    $lines[] = " * Generated: ".date('Y-m-d H:i:s');
                    $lines[] = " * Node type: ".$nodetype['name'];
                    $lines[] = " * Module: ".$module_slugname;
                    $lines[] = " * @author <Your name here>";
                    $lines[] = " * @licence <Your licence here>";
                    $lines[] = " */";
                    $lines[] = "";
                    $lines[] = "// Load base class driver";
                    $lines[] = "\$modPath = driverCommand::getModPath('$module_slugname');";
                    $lines[] = "include_once(\$modPath.'drivers/{$base}.php');";
                    $lines[] = "unset(\$modPath);";
                    $lines[] = "";
                    $lines[] = "/**";
                    $lines[] = " * Usage:";
                    $lines[] = " * \$modPath = driverCommand::getModPath('$module_slugname');";
                    $lines[] = " * include_once(\$modPath.'drivers/{$human}.php');";
                    $lines[] = " */";
                    $lines[] = "class $human extends $base {";
                    $lines[] = "\t/**";
                    $lines[] = "\t *";
                    $lines[] = "\t * @param integer \$id Node ID, 0 to new instances.";
                    $lines[] = "\t * @param integer \$node getNode response, it's needed to start the instance.";
                    $lines[] = "\t */";
                    $lines[] = "\tpublic function __construct(\$id = 0, \$node = null) {";
                    $lines[] = "\t\tparent::__construct(\$id, \$node);";
                    $lines[] = "\t\t";
                    $lines[] = "\t}";
                    $lines[] = "}";
                    
                    $fHuman = fopen($modPath.'drivers/'.$human.'.php', 'w');
                    foreach($lines as $line) {
                        fwrite($fHuman, str_replace("\t", "    ", $line)."\n");
                    }
                    fclose($fHuman);
                    if ($debug) {
                        echo '<h3>'.$modPath.'drivers/'.$human.'.php'.'</h3>';
                        echo '<pre>';
                        $file = file_get_contents($modPath.'drivers/'.$human.'.php');
                        echo str_replace('<?php', '', $file);
                        echo '</pre>';
                    }
                }
                $output = "<?php\n";
                $output .= "// Augenerated loader\n";
                $files = driverTools::lsDir($modPath.'drivers/', 'driverNodeType*.php');
                foreach($files['files'] as $file) {
                    $output .= "require_once '$file';\n";
                }
                $fLoader = fopen($modPath.'drivers/nodeTypeLoader.php', 'w');
                fwrite($fLoader, str_replace("\t", "    ", $output));
                fclose($fLoader);
                if ($debug) {
                    echo '<h3>' . $modPath . 'drivers/nodeTypeLoader.php' . '</h3>';
                    echo '<pre>';
                    $file = file_get_contents($modPath . 'drivers/nodeTypeLoader.php');
                    echo str_replace('<?php', '', $file);
                    echo '</pre>';
                }
                $resp = true;
            }
        }
        if ($debug) {
            var_dump($nodetype);
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
    
    /**
     * Determines the maximum file upload size by querying the PHP settings.
     * 
     * Released under the terms of the GNU General Public License, version 2 and later. Drupal is a registered trademark of Dries Buytaert.
     * @link http://stackoverflow.com/a/25370978/6385708
     * 
     * @staticvar type $max_size
     * @return string A file size limit in bytes based on the PHP upload_max_filesize and post_max_size
     */
    public static function file_upload_max_size() {
        static $max_size = -1;
        if ($max_size < 0) {
            // Start with post_max_size.
            $max_size = self::parse_size(ini_get('post_max_size'));
            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }
    
    /**
     * Parses a given byte count.
     * 
     * Released under the terms of the GNU General Public License, version 2 and later. Drupal is a registered trademark of Dries Buytaert.
     * 
     * @param string $size A size expressed as a number of bytes with optional SI or IEC binary unit prefix (e.g. 2, 3K, 5MB, 10G, 6GiB, 8 bytes, 9mbytes).
     * @return float An integer representation of the size in bytes.
     */
    public static function parse_size($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }
}

/**
 * Base class to generated drivers
 */
class driverNodeBase extends driverNodes {
    /**
     * @var array Node's cache.
     */
    protected static $nodeCache = array();
    
    /**
     * @var integer Node ID.
     */
    var $id = 0;
    /**
     * @var string Actual node type name
     */
    var $nodetype = '';
    
    /**
     * Persist instance data
     */
    public function save() {
        
    }
    
    public function getID() {
        return $this->id;
    }
    protected function setID($value) {
        $this->id = $value;
    }
    
    /**
     * Return instances by her IDs
     * @param string $modPath Module path with the driver definitions
     * @param string $nodetype
     * @param array $ids ID's list
     * @param boolean $tryDriver If the default driver is included return instances of it.
     * @return array of nodes by getNode
     */
    public static function getInstancesOf($modPath, $nodetype, $ids, $tryDriver = true) {
        $resp = array();
        $lids = array();
        // Get cached nodes, non cached nodes will be loader later.
        foreach($ids as $nid) {
            $bnode = self::cacheGet($nodetype, $nid);
            if ($bnode === false) {
                $lids[] = $nid; // Need get cached
            } else {
                $resp[] = $bnode;
            }
        }
        // Load non cached nodes and cache it
        if (count($lids) > 0) {
            $lnodes = driverCommand::run('getNodes', array(
                'nodetype' => $nodetype,
                'where' => '`id` in ('.join(',', $lids).')',
            ));
            foreach($lnodes as $nkey => $nnode) {
                self::cacheAdd($nodetype, $nnode);
                $resp[] = $nnode;
            }
        }
        $humanClass = 'driverNodeType'.strtoupper(substr($nodetype, 0, 1)).substr($nodetype, 1);
        if ($tryDriver && count($resp) > 0 && class_exists($humanClass)) {
            $iResp = array();
            foreach($resp as $node) {
                $iResp[] = new $humanClass($node['id'], $node);
            }
            $resp = $iResp;
        }
        return $resp;
    }
    
    /**
     * Clear node's cache
     */
    public static function cacheClear() {
        self::$nodeCache = array();
    }
    
    public static function cached($nodetype, $nid) {
        return isset(self::$nodeCache[$nodetype][$nid]);
    }
    
    /**
     * Add a node to the cache.
     * @param string $nodetype
     * @param array $node Node information returned by getNode.
     * @return boolean FALSE if fail
     */
    public static function cacheAdd($nodetype, $node) {
        if (empty($nodetype)) {
            return false;
        }
        if (!isset($node['id'])) {
            return false;
        }
        if (!isset(self::$nodeCache[$nodetype])) {
            self::$nodeCache[$nodetype] = array();
        }
        self::$nodeCache[$nodetype][$node['id']] = $node;
    }
    
    /**
     * Del a node from cache
     * @param type $nodetype
     * @param integer $nid Node ID
     */
    public static function cacheDel($nodetype, $nid) {
        unset(self::$nodeCache[$nodetype][$nid]);
    }
    
    /**
     * Get a node from cache
     * @param string $nodetype
     * @param integer $nid Node ID
     * @return array The node information, FALSE if not cached
     */
    public static function cacheGet($nodetype, $nid) {
        if (!isset(self::$nodeCache[$nodetype])) {
            return false;
        }
        if (!isset(self::$nodeCache[$nodetype][$nid])) {
            return false;
        } else {
            return self::$nodeCache[$nodetype][$nid];
        }
    }

    /**
     * 
     * @return string Default owner group, if not persistent instance return FALSE.
     */
    public function getSysGroup() {
        return 'user';
    }
}