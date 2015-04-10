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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandCommandHelp")) {
    class commandCommandHelp extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $sql = "SELECT * FROM `bin-path`";
            $q = dbConn::Execute($sql);
            echo "<legend>Command's list</legend>";
            while(!$q->EOF) {
                echo "<h2>Package path '{$q->fields["path"]}'</h2>";
                $cmds = driverTools::lsDir($q->fields["path"], "*.php");
                foreach($cmds["files"] as $cmd) {
                    $cmd = str_replace($q->fields["path"], "", $cmd);
                    $cmd = str_replace(".php", "", $cmd);
                    echo "<h3>Command '$cmd'</h3>";
                    $object = include($q->fields["path"].$cmd.".php");
                    $hlp = $object->getHelp();
                    echo "<h4>Description</h4>";
                    echo "<p>{$hlp["description"]}</p>";
                    if (count($hlp["parameters"]) > 0) {
                        echo "<h4>Parameters</h4>";
                        echo "<ul>";
                        foreach ($hlp["parameters"] as $key => $value) {
                            echo "<li><b>$key</b>: $value</li>";
                        }
                        echo "</ul>";
                    }
                    if (count($hlp["response"]) > 0) {
                        echo "<h4>Responses</h4>";
                        echo "<ul>";
                        foreach ($hlp["response"] as $key => $value) {
                            echo "<li><b>$key</b>: $value</li>";
                        }
                        echo "</ul>";
                    }
                    // Access data
                    echo "<h4>Permissions</h4>";
                    $acc = $object->getAccessData($q->fields["path"].$cmd.".php");
                    echo "<ul>";
                    echo "<li><b>Owner</b>: ".driverUser::getUserName($acc["owner"])."</li>";
                    echo "<li><b>Group</b>: ".driverUser::getGroupName($acc["group"])."</li>";
                    echo "<li><b>Flags</b>: ".driverUser::secFileToString($acc["flags"])."</li>";
                    echo "</ul>";
                }
                $q->MoveNext();
            }
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
                "description" => "Display commands help.", 
                "parameters" => array(), 
                "response" => array()
            );
        }
    }
}
return new commandCommandHelp();