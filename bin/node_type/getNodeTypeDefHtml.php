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
            echo "<legend>Node type '".$def["name"]."'</legend>";
            echo "<h3>Fields</h3>";
            echo "<table class=\"table\">";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Name</th>";
            echo "<th>Type</th>";
            echo "<th>Is key</th>";
            echo "<th>Lenght</th>";
            echo "<th>Required</th>";
            echo "<th>Read only</th>";
            echo "<th>System</th>";
            echo "<th>Multivalued</th>";
            echo "<th>Default</th>";
            echo "<th>Label</th>";
            echo "<th>Help</th>";
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
                echo "<td>{$field["iskey"]}</td>";
                echo "<td>{$field["len"]}</td>";
                echo "<td>{$field["required"]}</td>";
                echo "<td>{$field["readonly"]}</td>";
                echo "<td>{$field["locked"]}</td>";
                echo "<td>{$field["multi"]}</td>";
                echo "<td>{$field["default"]}</td>";
                echo "<td>{$field["label"]}</td>";
                echo "<td>{$field["help"]}</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            if ($def["locked"]) {
                echo "<p><span class=\"glyphicon glyphicon-exclamation-sign\"></span>&nbsp;This is a system node type.</p>";
            }
            echo "<p><span class=\"glyphicon glyphicon-info-sign\"></span>&nbsp;Created by '".
                    driverUser::getUserName($def["creator_node_user"])."' in {$def["created"]}, modified by '".
                    driverUser::getUserName($def["modifier_node_user"])."' in {$def["modified"]}.</p>";
            $sql = "select count(*) from `node_{$params["nodetype"]}`";
            $q = dbConn::Execute($sql);
            echo "<p><span class=\"glyphicon glyphicon-info-sign\"></span>&nbsp;Contains {$q->fields[0]} record/s.</p>";
            echo "<p><span class=\"glyphicon glyphicon-lock\"></span>";
            echo "&nbsp;Permisions: ";
            echo "<ul>";
            echo "<li><b>Owner</b>: ".driverUser::getUserName($def["user_owner"])."</li>";
            echo "<li><b>Group</b>: ".driverUser::getGroupName($def["group_owner"])."</li>";
            echo "<li><b>Flags</b>: ".driverUser::secNodeToString($def["access"])."</li>";
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
                "description" => "Display definition of node type.", 
                "parameters" => array(
                    "nodetype" => "Node type name",
                ), 
                "response" => array()
            );
        }
    }
}
return new commandGetNodeTypeDefHtml();