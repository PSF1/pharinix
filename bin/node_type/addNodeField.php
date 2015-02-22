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

//TODO: SECURITY !!
/*
 * Add a new field to a node type
 * CREATE TABLE `node_type_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `type` varchar(250) NOT NULL,
  `len` int(10) unsigned NOT NULL,
  `required` varchar(1) NOT NULL DEFAULT '0' COMMENT 'Field required',
  `readonly` varchar(1) NOT NULL DEFAULT '0' COMMENT 'Not writeble field',
  `locked` varchar(1) NOT NULL DEFAULT '0' COMMENT 'The cant be erased of the type',
  `node_type` int(10) unsigned NOT NULL,
  `default` longtext NOT NULL COMMENT 'Default value',
  `label` varchar(250) NOT NULL,
  `help` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
 */

if (!class_exists("commandAddNodeField")) {
    class commandAddNodeField {

        public static function runMe($params = array(), $debug = true) {
            $resp = array("ok" => true, "msg" => "");

            // Default values
            $params = array_merge(array(
                        "name" => "",
                        "type" => "",
                        "len" => 0,
                        "required" => false,
                        "readonly" => false,
                        "locked" => false,
                        "node_type" => 0,
                        "default" => "",
                        "label" => "Field",
                        "help" => "A field",
                    ), $params);
            if ($params["name"] == "") {
                $resp["msg"] = "Field name is required. ";
            }
            if ($params["type"] == "") {
                $resp["msg"] .= "Field type is required. ";
            }
            if ($params["node_type"] == "") {
                $resp["msg"] .= "Node type is required. ";
            }
            if ($resp["msg"] != "") return $resp;

            // Verify node type
            $ntype = driverCommand::run("getNodeTypeId", array("name" => $params["node_type"]));
            if ($ntype !== false) {
                // Verify that name is unique
                $sql = "select id from `node_type_field` where name = '{$params["name"]}'";
                $q = dbConn::get()->Execute($sql);
                if ($q->EOF) {
                    $isbasic = driverCommand::run("isBasicNodeFieldType", array("type" => $params["type"]));
                    if (!$isbasic["basic"]) {
                        $subtimeId = driverCommand::run("getNodeTypeId", array("name" => $params["type"]));
                        if ($subtimeId === false) {
                            $resp["ok"] = FALSE;
                            $resp["msg"] = "Node field sub type '{$params["type"]}' don't exist.";
                        } else {
                            $resp["ok"] = true;
                        }
                    }
                    if ($resp["ok"]) {
                        // Insert new field
                        $sql = "insert into `node_type_field` set ";
                        $sql .= "`name` = '{$params["name"]}', ";
                        $sql .= "`type` = '{$params["type"]}', ";
                        $sql .= "`len` = '{$params["len"]}', ";
                        $sql .= "`required` = '".($params["required"]?1:0)."', ";
                        $sql .= "`readonly` = '".($params["readonly"]?1:0)."', ";
                        $sql .= "`locked` = '".($params["locked"]?1:0)."', ";
                        $sql .= "`node_type` = '{$ntype["id"]}', ";
                        $sql .= "`default` = '{$params["default"]}',";
                        $sql .= "`label` = '{$params["label"]}',";
                        $sql .= "`help` = '{$params["help"]}'";
                        dbConn::get()->Execute($sql);
                        $resp["ok"] = true;
                        // alter table
                        $sql = self::getAddFieldString($params);
                        dbConn::get()->Execute($sql);
                    }
                } else {
                    $resp["ok"] = false;
                    $resp["msg"] = "Node field name '{$params["name"]}' already exist.";
                }
            } else {
                $resp["ok"] = false;
                $resp["msg"] = "Node type '{$params["node_type"]}' don't exist.";
            }
            return $resp;
        }

        public static function getHelp() {
            return array(
                "description" => "Add a new field to a node type", 
                "parameters" => array(
                    "name" => "Field name",
                    "type" => "Field type: longtext, bool, datetime, double, integer, string or other node type",
                    "len" => "Field lenght if need it",
                    "required" => "True/false Required field",
                    "readonly" => "True/false Not writable field",
                    "locked" => "True/false System field",
                    "node_type" => "Node type name",
                    "default" => "Default value",
                    "label" => "Label name to show",
                    "help" => "Help about field",
                ), 
                "response" => array(
                    "ok" => "True/False field added",
                    "msg" => "If error, it's a message about error"
                )
            );
        }
        
        public static function getAddFieldString($params) {
            $resp = "";
            switch ($params["type"]) {
                case "longtext":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` LONGTEXT DEFAULT '{$params["default"]}' AFTER `id`";
                break;
                case "bool":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` VARCHAR(1) DEFAULT '".($params["default"]?"1":"0")."' AFTER `id`";
                break;
                case "datetime":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` DATETIME DEFAULT '{$params["default"]}' AFTER `id`";
                break;
                case "double":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` DECIMAL(20, 6) DEFAULT {$params["default"]} AFTER `id`";
                break;
                case "integer":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` INTEGER DEFAULT {$params["default"]} AFTER `id`";
                break;
                case "string":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` VARCHAR({$params["len"]}) DEFAULT '{$params["default"]}' AFTER `id`";
                break;
                default:
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` INTEGER UNSIGNED DEFAULT 0 AFTER `id`";
                break;
            }
            return $resp;
        }
    }
}
return new commandAddNodeField();