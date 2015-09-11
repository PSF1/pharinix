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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandCommandHelp")) {
    class commandCommandHelp extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $hlp = driverCommand::run('getCommandHelp');
            echo "<legend>".__("Command's list")."</legend>";
            foreach($hlp as $madule => $info) {
                echo "<h2>".sprintf(__("Module '%s'"), $info->package['name'])."</h2>";
                echo "<h6>".sprintf(
                        __("Licence: %s"), 
                        $info->package['meta']['meta']->licence)."</h6>";
                echo "<h6>".sprintf(
                        __("%s version %s, %s"), 
                        $info->package['slugname'], 
                        $info->package['version'],
                        $info->package['meta']['meta']->autor)."</h6>";
                echo '<p>';
                echo $info->package['meta']['meta']->description;
                if (!driverTools::str_end('.', $info->package['meta']['meta']->description)) echo ". ";
                echo '&nbsp;<a href="'.$info->package['meta']['meta']->website.'" target="_blank">'.__('See more information.').'</a>';
                echo '</p>';
                echo '<p>';
                echo sprintf(__("It add %s command(s)"), count($info->commands)).':';
                echo '</p>';
                foreach($info->commands as $command) {
                    $hlp = $command;
                    echo "<h3>".$command['command']."</h3>";
//                    echo "<h4>".__("Description")."</h4>";
                    echo "<p>{$hlp["description"]}</p>";
                    echo ($hlp["echo"]?'<h6>('.__('Do a direct output to the client.').')</h6>':'');
                    echo ($hlp["interface"]?'<h6>('.__('Designed to be a comunication interface.').')</h6>':'');
                    if (isset($hlp["parameters"]) && count($hlp["parameters"]) > 0) {
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
                    if (isset($hlp["response"]) && count($hlp["response"]) > 0) {
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
                    echo "<ul>";
                    echo "<li><b>".__("Owner")."</b>: ".driverUser::getUserName($hlp["owner"])."</li>";
                    echo "<li><b>".__("Group")."</b>: ".driverUser::getGroupName($hlp["group"])."</li>";
                    echo "<li><b>".__("Flags")."</b>: ".driverUser::secFileToString($hlp["flags"], true)."</li>";
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
                ),
                "echo" => true
            );
        }
    }
}
return new commandCommandHelp();