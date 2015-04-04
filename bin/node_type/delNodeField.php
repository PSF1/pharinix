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

if (!class_exists("commandDelNodeField")) {
    class commandDelNodeField extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $nid = driverCommand::run("getNodeTypeId", array("name" => $params["nodetype"]));
            $nid = $nid["id"];
            // Exist node type?
            if ($nid !== false) {
                $sql = "select `locked` from `node_type_field` where `node_type` = $nid && `name` = '{$params["name"]}'";
                $q = dbConn::Execute($sql);
                // TODO: Is a system type? If true, I can't change it...
                if (!$q->EOF /*&& $q->fields["locked"] == "1"*/) {
                    $sql = "delete from `node_type_field` where `node_type` = $nid && `name` = '{$params["name"]}'";
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
                           "`modifier_node_user` = ".driverUser::getID()." where `id` = ".$nid;
                    dbConn::Execute($sql);
                }
            }
        }

        public static function getHelp() {
            return array(
                "description" => "Erase a node field", 
                "parameters" => array(
                    "nodetype" => "Node type name",
                    "name" => "Node field name to erase"
                ), 
                "response" => array()
            );
        }
    }
}
return new commandDelNodeField();