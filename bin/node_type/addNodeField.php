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
  `multi` varchar(1) NOT NULL DEFAULT '0' COMMENT 'Multivalue',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

if (!class_exists("commandAddNodeField")) {
    class commandAddNodeField extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $resp = array("ok" => true, "msg" => "");

            // Default values
            $params = array_merge(array(
                        "name" => "",
                        "type" => "",
                        "iskey" => false,
                        "len" => 0,
                        "required" => false,
                        "readonly" => false,
                        "locked" => false,
                        "multi" => false,
                        "node_type" => 0,
                        "default" => "",
                        "label" => "Field",
                        "help" => "",
                    ), $params);
            $params["name"] = strtolower($params["name"]);
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
                $sql = "select id from `node_type_field` where `node_type` = {$ntype["id"]} && `name` = '{$params["name"]}'";
                $q = dbConn::Execute($sql);
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
                    } else {
                        $params["multi"] = false;
                    }
                    if ($resp["ok"]) {
                        switch (strtolower($params["type"])) {
                            case "htmltext":
                                break;
                            case "longtext":
                                break;
                            case "bool":
                                $params["default"] = ((bool)($params["default"]) ? "1" : "0");
                                break;
                            case "datetime":
                                break;
                            case "double":
                                break;
                            case "integer":
                                break;
                            case "string":
                            case "password":
                                if ($params["len"] <= 0 || $params["len"] >= 250) {
                                    $params["len"] = 250;
                                }
                                break;
                            default:
                                $params["default"] = "0";
                                break;
                        }
                        // Insert new field
                        $sql = "insert into `node_type_field` set ";
                        $sql .= "`name` = '{$params["name"]}', ";
                        $sql .= "`type` = '{$params["type"]}', ";
                        $sql .= "`iskey` = '".($params["iskey"]?1:0)."', ";
                        $sql .= "`len` = '{$params["len"]}', ";
                        $sql .= "`required` = '".($params["required"]?1:0)."', ";
                        $sql .= "`readonly` = '".($params["readonly"]?1:0)."', ";
                        $sql .= "`locked` = '".($params["locked"]?1:0)."', ";
                        $sql .= "`multi` = '".($params["multi"]?1:0)."', ";
                        $sql .= "`node_type` = '{$ntype["id"]}', ";
                        $sql .= "`default` = '{$params["default"]}',";
                        $sql .= "`label` = '{$params["label"]}',";
                        $sql .= "`help` = '{$params["help"]}'";
                        dbConn::Execute($sql);
                        $resp["ok"] = true;
                        // alter table
                        $sql = self::getAddFieldString($params);
                        dbConn::Execute($sql);
                        // Create relation table if multivalue field 
                        if ($params["multi"]) {
                            $sql  = 'CREATE TABLE `node_relation_'.$params["node_type"].'_'.$params["name"].'_'.$params["type"].'` ( ';
                            $sql .= '`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT, ';
                            $sql .= '`type1` INTEGER UNSIGNED NOT NULL, ';
                            $sql .= '`type2` INTEGER UNSIGNED NOT NULL, ';
                            $sql .= 'PRIMARY KEY (`id`), ';
                            $sql .= 'INDEX `type1`(`type1`), '; // type1 to type2 relation
                            $sql .= 'INDEX `type2`(`type2`) ';
                            $sql .= ') ENGINE = MyISAM';
                            dbConn::Execute($sql);
                        }
                        // Modificated
                        $sql = "update `node_type` set `modified` = NOW() where `id` = ".$ntype["id"];
                        dbConn::Execute($sql);
                        // TODO: Add modificator user
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
                    "type" => "Field type: longtext, bool, datetime, double, integer, string, password, htmltext or other node type",
                    "iskey" => "Any other record can have some value. This funcionality is not using database implementation.",
                    "len" => "Field lenght if need it",
                    "required" => "True/false Required field",
                    "readonly" => "True/false Not writable field",
                    "locked" => "True/false System field",
                    "multi" => "True/false multivalue field, only applicable on relations with other node types.",
                    "node_type" => "Node type name",
                    "default" => "Default value",
                    "label" => "Label to show",
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
            switch (strtolower($params["type"])) {
                case "htmltext":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` LONGTEXT AFTER `id`";
                break;
                case "longtext":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` LONGTEXT AFTER `id`";
                break;
                case "bool":
                    $def = ((bool)($params["default"])?"1":"0");
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` VARCHAR(1) DEFAULT '".$def."' AFTER `id`";
                break;
                case "datetime":
                    $def = "";
                    if ($params["default"] != "") {
                        $def = "DEFAULT '{$params["default"]}'";
                    }
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` DATETIME $def AFTER `id`";
                break;
                case "double":
                    $def = "";
                    if ($params["default"] != "") {
                        $def = "DEFAULT {$params["default"]}";
                    }
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` DECIMAL(20, 6) $def AFTER `id`";
                break;
                case "integer":
                    $def = "";
                    if ($params["default"] != "") {
                        $def = "DEFAULT {$params["default"]}";
                    }
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` int(11) $def AFTER `id`";
                break;
                case "string":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` VARCHAR({$params["len"]}) DEFAULT '{$params["default"]}' AFTER `id`";
                break;
                case "password":
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` VARCHAR({$params["len"]}) AFTER `id`";
                break;
                default:
                    $resp = "ALTER TABLE `node_{$params["node_type"]}` ADD COLUMN `{$params["name"]}` int(10) UNSIGNED DEFAULT 0 AFTER `id`";
                break;
            }
            return $resp;
        }
    }
}
return new commandAddNodeField();