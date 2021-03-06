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
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

if (!class_exists("commandDelNodeField")) {
    class commandDelNodeField extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "nodetype" => "",
                "name" => "",
            ), $params);
            $params["name"] = strtolower($params["name"]);
            
            $typeDef = driverCommand::run("getNodeTypeDef", array(
                "nodetype" => $params["nodetype"],
            ));
            $nid = $typeDef;
            // Exist node type?
            if ($nid["id"] !== false) {
                // Access control
                $usrGrps = driverUser::getGroupsID();
                $allowed = driverUser::secNodeCanDelete($typeDef["access"], 
                        $typeDef["user_owner"] == driverUser::getID(), 
                        array_search($typeDef["group_owner"], $usrGrps) !== FALSE);
                if ($allowed) {
                    // Delete it
                    $sql = "select `locked` from `node_type_field` where `node_type` = {$nid["id"]} && `name` = '{$params["name"]}'";
                    $q = dbConn::Execute($sql);
                    // TODO: Is a system type? If true, I can't change it...
                    if (!$q->EOF /*&& $q->fields["locked"] == "1"*/) {
                        $sql = "delete from `node_type_field` where `node_type` = {$nid["id"]} && `name` = '{$params["name"]}'";
                        dbConn::Execute($sql);
                        $sql = "ALTER TABLE `node_{$params["nodetype"]}` DROP COLUMN `{$params["name"]}`";
                        dbConn::Execute($sql);
                        // Delete multi relation table
                        $sql = "show tables like 'node_relation_{$params["nodetype"]}_{$params["name"]}%'";
                        $q = dbConn::Execute($sql);
                        while (!$q->EOF) {
                            $sql = "DROP TABLE IF EXISTS `{$q->fields[0]}`";
                            dbConn::Execute($sql);
                            $q->MoveNext();
                        }
                        // Modificated
                        $sql = "update `node_type` set `modified` = NOW(), ".
                               "`modifier_node_user` = ".driverUser::getID()." where `id` = ".$nid["id"];
                        dbConn::Execute($sql);
                    }
                }
            }
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Erase a node field. It need delete permission over the node type."),
                "parameters" => array(
                    "nodetype" => __("Node type name"),
                    "name" => __("Node field name to erase")
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "nodetype" => "string",
                        "name" => "string"
                    ), 
                    "response" => array(),
                ),
                "echo" => false
            );
        }
    }
}
return new commandDelNodeField();