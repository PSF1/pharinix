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

if (!class_exists("commandGetNodeTypeDefHtml")) {
    class commandGetNodeTypeDefHtml extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $def = driverCommand::run("getNodeTypeDef", array("nodetype" => $params["nodetype"]));
            echo "<legend>".sprintf(__("Node type '%s'"), $def["name"])."</legend>";
            echo "<h3>".__('Fields')."</h3>";
            echo "<table class=\"table\">";
            echo "<thead>";
            echo "<tr>";
            echo "<th>".__('Name')."</th>";
            echo "<th>".__('Type')."</th>";
            echo "<th>".__('Is key')."</th>";
            echo "<th>".__('length')."</th>";
            echo "<th>".__('Required')."</th>";
            echo "<th>".__('Locked')."</th>";
            echo "<th>".__('Read only')."</th>";
            echo "<th>".__('System')."</th>";
            echo "<th>".__('Multivalued')."</th>";
            echo "<th>".__('Default')."</th>";
            echo "<th>".__('Label')."</th>";
            echo "<th>".__('Help')."</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            foreach ($def["fields"] as $field) {
                echo "<tr>";
                echo "<td>";
                echo $field["name"];
                if ($def["label_field"] == $field["name"]) {
                    echo "&nbsp;<span class=\"glyphicon glyphicon-tags\"></span>";
                }
                echo "</td>";
                
                $type = driverCommand::run("isBasicNodeFieldType", array("type" => $field["type"]));
                echo "<td>";
                if ($type["basic"]) {
                    echo "{$field["type"]}";
                } else {
                    echo "<a href=\"".CMS_DEFAULT_URL_BASE."node/type/{$field["type"]}\">{$field["type"]}</a>";
                }
                echo "</td>";
                echo "<td>";
                driverCommand::run("formatFieldBool", array(
                    "toread" => true,
                    "value" => $field["iskey"],
                ));
                echo "</td>";
                echo "<td>{$field["len"]}</td>";
                echo "<td>";
                driverCommand::run("formatFieldBool", array(
                    "toread" => true,
                    "value" => $field["required"],
                ));
                echo "</td>";
                echo "<td>";
                driverCommand::run("formatFieldBool", array(
                    "toread" => true,
                    "value" => $field["locked"],
                ));
                echo "</td>";
                echo "<td>";
                driverCommand::run("formatFieldBool", array(
                    "toread" => true,
                    "value" => $field["readonly"],
                ));
                echo "</td>";
                echo "<td>";
                driverCommand::run("formatFieldBool", array(
                    "toread" => true,
                    "value" => $field["locked"],
                ));
                echo "</td>";
                echo "<td>";
                driverCommand::run("formatFieldBool", array(
                    "toread" => true,
                    "value" => $field["multi"],
                ));
                echo "</td>";
                echo "<td>{$field["default"]}</td>";
                echo "<td>{$field["label"]}</td>";
                echo "<td>{$field["help"]}</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            if ($def["locked"]) {
                echo "<p><span class=\"glyphicon glyphicon-exclamation-sign\"></span>&nbsp;".__('This is a system node type.')."</p>";
            }
            echo "<p><span class=\"glyphicon glyphicon-info-sign\"></span>&nbsp;";
            echo sprintf(__("Created by '%s' in %s, modified by '%s' in %s."), 
                    driverUser::getUserName($def["creator_node_user"]), 
                    $def["created"], 
                    driverUser::getUserName($def["modifier_node_user"]),
                    $def["modified"]
                    );
            echo "</p>";
            $sql = "select count(*) from `node_{$params["nodetype"]}`";
            $q = dbConn::Execute($sql);
            echo "<p><span class=\"glyphicon glyphicon-info-sign\"></span>&nbsp;";
            echo sprintf(__("Contains %s record/s."), $q->fields[0]);
            echo "</p>";
            echo "<p><span class=\"glyphicon glyphicon-lock\"></span>";
            echo "&nbsp;".__('Permisions').": ";
            echo "<ul>";
            echo "<li><b>".__('Owner')."</b>: ".driverUser::getUserName($def["user_owner"])."</li>";
            echo "<li><b>".__('Group')."</b>: ".driverUser::getGroupName($def["group_owner"])."</li>";
            echo "<li><b>".__('Flags')."</b>: ".driverUser::secNodeToString($def["access"])."</li>";
            echo "</ul>";
            echo "</p>";
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
                "package" => 'core',
                "description" => __("Display definition of node type."), 
                "parameters" => array(
                    "nodetype" => __("Node type name"),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "nodetype" => "string",
                    ), 
                    "response" => array(),
                )
            );
        }
    }
}
return new commandGetNodeTypeDefHtml();