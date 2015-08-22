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
            $paths = driverCommand::getPaths();
            echo "<legend>".__("Command's list")."</legend>";
            foreach ($paths as $path) {
                echo "<h2>".sprintf(__("Package path '%s'"), $path)."</h2>";
                $cmds = driverTools::lsDir($path, "*.php");
                foreach($cmds["files"] as $cmd) {
                    $cmd = str_replace($path, "", $cmd);
                    $cmd = str_replace(".php", "", $cmd);
                    echo "<h3>".sprintf(__("Command '%s'"), $cmd)."</h3>";
                    $object = include($path.$cmd.".php");
                    $hlp = $object->getHelp();
                    echo "<h4>".__("Description")."</h4>";
                    echo "<p>{$hlp["description"]}</p>";
                    if (count($hlp["parameters"]) > 0) {
                        echo "<h4>".__("Parameters")."</h4>";
                        echo "<ul>";
                        foreach ($hlp["parameters"] as $key => $value) {
                            echo "<li>";
                            echo '<span class="label label-success">';
                            if (isset($hlp["type"]["parameters"][$key])) {
                                echo '<span class="badge">'.$hlp["type"]["parameters"][$key].'</span>&nbsp;';
                            }
                            echo "$key</span>: $value";
                            echo "</li>";
                        }
                        echo "</ul>";
                    }
                    if (count($hlp["response"]) > 0) {
                        echo "<h4>".__("Responses")."</h4>";
                        echo "<ul>";
                        foreach ($hlp["response"] as $key => $value) {
                            echo "<li>";
                            echo '<span class="label label-success">';
                            if (isset($hlp["type"]["response"][$key])) {
                                echo '<span class="badge">'.$hlp["type"]["response"][$key].'</span>&nbsp;';
                            }
                            echo "$key</span>: $value";
                            echo "</li>";
                        }
                        echo "</ul>";
                    }
                    // Access data
                    echo "<h4>".__("Permissions")."</h4>";
                    $acc = $object->getAccessData($path.$cmd.".php");
                    echo "<ul>";
                    echo "<li><b>".__("Owner")."</b>: ".driverUser::getUserName($acc["owner"])."</li>";
                    echo "<li><b>".__("Group")."</b>: ".driverUser::getGroupName($acc["group"])."</li>";
                    echo "<li><b>".__("Flags")."</b>: ".driverUser::secFileToString($acc["flags"], true)."</li>";
                    echo "</ul>";
                }
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
                "package" => 'core',
                "description" => __("Display commands help."), 
                "parameters" => array(), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(), 
                    "response" => array(),
                )
            );
        }
    }
}
return new commandCommandHelp();