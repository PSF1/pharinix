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

if (!class_exists("commandCommandHelpWikiExport")) {
    class commandCommandHelpWikiExport extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "path" => "C:\Users\psf\Documents\github\pharinix.wiki\Command's-help.md", 
            ), $params);
            $fInfo = driverTools::pathInfo($params["path"]);
            if (!is_dir($fInfo["path"])) {
                return;
            }
            try {
//                $paths = driverCommand::getPaths();
                $hlp = driverCommand::run('getCommandHelp');
                ob_start();
                echo "# Command's list\n\n";
                $ncmds = 0;
                foreach ($hlp as $madule => $info) {
                    echo "## " . sprintf(__("Module '%s'"), $info->package['name']) . "\n\n";
                    echo "###### " . sprintf(
                            __("Licence: %s"), $info->package['meta']['meta']->licence) . "\n\n";
                    echo "###### " . sprintf(
                            __("%s version %s, %s"), $info->package['slugname'], $info->package['version'], str_replace("\n", ', ', $info->package['meta']['meta']->autor)) . "\n\n";
                    echo $info->package['meta']['meta']->description;
                    if (!driverTools::str_end('.', $info->package['meta']['meta']->description))
                        echo ". ";
                    echo ' [' . __('See more information.') . '](' . $info->package['meta']['meta']->website . ' "' . __('See more information.') . "\")\n\n";
                    echo "\n";
                    echo sprintf(__("It add %s command(s)"), count($info->commands)) . ":\n\n";
                    $ncmds += count($info->commands);
                    foreach ($info->commands as $command) {
                        $hlp = $command;
                        echo "### " . $command['command'] . "\n\n";
                        echo str_replace('\n', '', $hlp["description"])."\n\n";
                        if (isset($hlp["parameters"]) && count($hlp["parameters"]) > 0) {
                            echo "#### " . __("Parameters") . "\n\n";
                            foreach ($hlp["parameters"] as $key => $value) {
                                echo "* ";
                                if (isset($hlp["type"]["parameters"][$key])) {
                                    echo '`'.$hlp["type"]["parameters"][$key] . '` / ';
                                }
                                echo "`$key` : $value\n";
                            }
                            echo "\n";
                        }
                        if (isset($hlp["response"]) && count($hlp["response"]) > 0) {
                            echo "#### " . __("Responses") . "\n\n";
                            foreach ($hlp["response"] as $key => $value) {
                                echo "* ";
                                if (isset($hlp["type"]["response"][$key])) {
                                    echo '`'.$hlp["type"]["response"][$key] . '` / ';
                                }
                                echo "`$key` : $value\n";
                            }
                            echo "\n";
                        }
                        // Access data
                        echo "#### " . __("Permissions") . "\n\n";
                        echo "* `" . __("Owner") . "`: " . driverUser::getUserName($hlp["owner"]) . "\n";
                        echo "* `" . __("Group") . "`: " . driverUser::getGroupName($hlp["group"]) . "\n";
                        echo "* `" . __("Flags") . "`: " . driverUser::secFileToString($hlp["flags"]) . "\n";
                        echo "\n";
                    }
                }
                echo "\n\n";
                echo "Exported $ncmds commands by `commandHelpWikiExport` at ".date("Y-m-d H:i:s");
                $output = ob_get_clean();
                file_put_contents($params["path"], $output);
            } catch (Exception $exc) {
                
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
                "description" => __("Export commands help to a GitHub Wiki file."), 
                "parameters" => array(
                    "path" => __("File path where export."),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "path" => "string",
                    ), 
                    "response" => array(),
                )
            );
        }
    }
}
return new commandCommandHelpWikiExport();