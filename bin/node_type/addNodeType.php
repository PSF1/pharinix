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

//TODO: Securizate access
/*
 * Add a new node type
 * Parameters:
 * name = Node type name
 * 
 * CREATE TABLE `node_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `created` datetime NOT NULL,
  `creator_node_user` int(10) unsigned NOT NULL COMMENT '''User ID''',
  `modified` datetime NOT NULL,
  `modifier_node_user` int(10) unsigned NOT NULL COMMENT '''User ID''',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */
if (!class_exists("commandAddNodeType")) {
    class commandAddNodeType extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $resp = array("ok" => false, "nid" => 0, "msg" => "");

            // Default values
            $params = array_merge(array(
                        "name" => "",
                        "locked" => false,
                        "label_field" => "title",
                      ), $params);
            $params["name"] = strtolower($params["name"]);
            if ($params["name"] == "type" || $params["name"] == "type_field"  || $params["name"] == "formats") {
                $resp["msg"] = __("Node type name is a protected name.")." ";
            }
            if ($params["name"] == "") {
                $resp["msg"] = __("Node type name is required.")." ";
            }
            if ($resp["msg"] != "") return $resp;

            // We dont like various types with some name
            $sql = "select id from `node_type` where name = '{$params["name"]}'";
            $q = dbConn::Execute($sql);
            if ($q->EOF) {
                // Insert the new type
                $sql = "insert into `node_type` set ".
                       "name = '{$params["name"]}', ".
                       "creator_node_user = ".driverUser::getID().", ".
                       "created = NOW(), ".
                       "modifier_node_user = ".driverUser::getID().", ".
                       "modified = NOW(), ".
                       "user_owner = ".driverUser::getID().", ".
                       "group_owner = ".driverUser::getDefaultGroupID().", ".
                       "`access` = ".(PERMISSION_NODE_DEFAULT).", ".
                       "`label_field` = '{$params["label_field"]}', ".
                       "`locked` = '".($params["locked"]?"1":"0")."' ";
                dbConn::Execute($sql);
                $id = dbConn::lastID();
                // Add table
                $sql = "CREATE TABLE `node_{$params["name"]}` ( ";
                $sql .= "`id` int(10) unsigned NOT NULL AUTO_INCREMENT, ";
                $sql .= "PRIMARY KEY (`id`) ";
                $sql .= ") ENGINE=MyISAM ";
                //ALTER TABLE `onlinedescargartorrent`.`booting` CHARACTER SET utf8 COLLATE utf8_general_ci;
                $cfg = driverConfig::getCFG();
                $charset = $cfg->getSection('[mysql]')->get('charset');
                if ($charset != null) {
                    $sql .= 'CHARACTER SET '.$charset.' ';
                }
                $tableCharset = $cfg->getSection('[mysql]')->get('table_charset');
                if ($tableCharset != null) {
                    $sql .= 'COLLATE '.$tableCharset.' ';
                }
                dbConn::Execute($sql);
                // Add default fields
                $nField = array(
                    "name" => "title",
                    "type" => "string",
                    "len" => 250,
                    "required" => true,
                    "readonly" => false,
                    "locked" => false,
                    "node_type" => $params["name"],
                    "default" => "",
                    "label" => "Title",
                    "help" => "A title string for this node.",
                    );
                driverCommand::run("addNodeField", $nField);
                $nField = array(
                    "name" => "user_owner",
                    "type" => "user",
                    "len" => 0,
                    "required" => false,
                    "readonly" => false,
                    "locked" => true,
                    "node_type" => $params["name"],
                    "default" => 0,
                    "label" => "Owner",
                    "help" => "Owner user",
                    );
                driverCommand::run("addNodeField", $nField);
                $nField = array(
                    "name" => "group_owner",
                    "type" => "group",
                    "len" => 0,
                    "required" => false,
                    "readonly" => false,
                    "locked" => true,
                    "node_type" => $params["name"],
                    "default" => 0,
                    "label" => "Group",
                    "help" => "Owner group",
                    );
                driverCommand::run("addNodeField", $nField);
                $nField = array(
                    "name" => "access",
                    "type" => "nodesec",
                    "len" => 0,
                    "required" => false,
                    "readonly" => false,
                    "locked" => true,
                    "node_type" => $params["name"],
                    "default" => PERMISSION_NODE_DEFAULT,
                    "label" => "Access",
                    "help" => "Access control flags.",
                    );
                driverCommand::run("addNodeField", $nField);
                $nField = array(
                    "name" => "created",
                    "type" => "datetime",
                    "len" => 0,
                    "required" => false,
                    "readonly" => true,
                    "locked" => true,
                    "node_type" => $params["name"],
                    "default" => "",
                    "label" => "Creation date",
                    "help" => "",
                    );
                driverCommand::run("addNodeField", $nField);
                $nField = array(
                    "name" => "creator",
                    "type" => "user",
                    "len" => 0,
                    "required" => false,
                    "readonly" => true,
                    "locked" => true,
                    "node_type" => $params["name"],
                    "default" => "",
                    "label" => "User creator",
                    "help" => "",
                    );
                driverCommand::run("addNodeField", $nField);
                $nField = array(
                    "name" => "modified",
                    "type" => "datetime",
                    "len" => 0,
                    "required" => false,
                    "readonly" => true,
                    "locked" => true,
                    "node_type" => $params["name"],
                    "default" => "",
                    "label" => "Modified date",
                    "help" => "",
                    );
                driverCommand::run("addNodeField", $nField);
                $nField = array(
                    "name" => "modifier",
                    "type" => "user",
                    "len" => 0,
                    "required" => false,
                    "readonly" => true,
                    "locked" => true,
                    "node_type" => $params["name"],
                    "default" => "",
                    "label" => "Modifier user",
                    "help" => "",
                    );
                driverCommand::run("addNodeField", $nField);

                $resp["ok"] = true;
                $resp["nid"] = $id;
                // Add page
                // since Pharinix 1.12.04 node types use context URL mapping.
//                driverCommand::run("addPage", array(
//                    'name' => "node_type_".$params["name"],
//                    'template' => "etc/templates/pages/default.xml",
//                    'title' => "{$params["name"]} node type",
//                    'description' => "",
//                    'keys' => "",
//                ));
//                driverCommand::run("addBlockToPage", array(
//                    'page' => "node_type_".$params["name"],
//                    'command' => "getNodeTypeDefHtml",
//                    'parameters' => "nodetype=".$params["name"],
//                ));
            } else {
                $resp["ok"] = false;
                $resp["msg"] = "Node type '{$params["name"]}' already exist.";
            }

            return $resp;
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Add a new node type, with a default string field with name 'title'."), 
                "parameters" => array(
                    "name" => __("Node type name"),
                    "locked" => __("True/false System field. Optional, default false."),
                    "label_field" => __("Name of the field used how label to list nodes. Optional, default 'title'."),
                ), 
                "response" => array(
                    "ok" => __("True/False field added"),
                    "msg" => __("If error, it's a message about error"),
                    "nid" => __("ID of new node type"),
                ),
                "type" => array(
                    "parameters" => array(
                        "name" => "string",
                        "locked" => "boolean",
                        "label_field" => "string",
                    ), 
                    "response" => array(
                        "ok" => "boolean",
                        "msg" => "string",
                        "nid" => "integer",
                    ),
                ),
                "echo" => false
            );
        }
    }
}
return new commandAddNodeType();