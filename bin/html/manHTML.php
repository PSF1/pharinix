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

if (!class_exists("commandManHTML")) {
    class commandManHTML extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(
                    array(
                        "cmd" => "",
                    ), $params);
            
            $hlp = driverCommand::run("man", $params);
            foreach($hlp["help"] as $cmd => $inf) {
                echo "<h3>Command '".$cmd."'</h3>";
                echo "<h4>Description</h4>";
                echo "<p>{$inf["description"]}</p>";
                if (isset($inf["parameters"])) {
                    echo "<h4>Parameters</h4>";
                    echo "<ul>";
                    foreach ($inf["parameters"] as $key => $value) {
                        echo "<li>";
                        echo '<span class="label label-success">';
                        if (isset($inf["type"]["parameters"][$key])) {
                            echo '<span class="badge">'.$inf["type"]["parameters"][$key].'</span>&nbsp;';
                        }
                        echo "$key</span>: $value";
                        echo "</li>";
                    }
                    echo "</ul>";
                }
                if (isset($inf["response"])) {
                    echo "<h4>Responses</h4>";
                    echo "<ul>";
                    foreach ($inf["response"] as $key => $value) {
                        echo "<li>";
                        echo '<span class="label label-success">';
                        if (isset($inf["type"]["response"][$key])) {
                            echo '<span class="badge">'.$inf["type"]["response"][$key].'</span>&nbsp;';
                        }
                        echo "$key</span>: $value";
                        echo "</li>";
                    }
                    echo "</ul>";
                }
                // Access data
                echo "<h4>Permissions</h4>";
                echo "<ul>";
                echo "<li><b>Owner</b>: ".driverUser::getUserName($inf["owner"])."</li>";
                echo "<li><b>Group</b>: ".driverUser::getGroupName($inf["group"])."</li>";
                echo "<li><b>Flags</b>: ".driverUser::secFileToString($inf["flags"], true)."</li>";
                echo "</ul>";
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
                "description" => "Echo help about a command how HTML. Ex. manHTML ('cmd' => 'manHTML'), echo this help.", 
                "parameters" => array(
                    "cmd" => "The command to query."
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "cmd" => "string"
                    ), 
                    "response" => array(),
                )
            );
        }
    }
}
return new commandManHTML();