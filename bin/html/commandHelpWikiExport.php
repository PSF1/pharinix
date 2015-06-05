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
                $sql = "SELECT * FROM `bin-path`";
                $q = dbConn::Execute($sql);
                ob_start();
                echo "# Command's list\n\n";
                $ncmds = 0;
                while(!$q->EOF) {
                    echo "## Package path '{$q->fields["path"]}'\n\n";
                    $cmds = driverTools::lsDir($q->fields["path"], "*.php");
                    foreach($cmds["files"] as $cmd) {
                        $cmd = str_replace($q->fields["path"], "", $cmd);
                        $cmd = str_replace(".php", "", $cmd);
                        echo "### Command `$cmd`\n\n";
                        ++$ncmds;
                        $object = include($q->fields["path"].$cmd.".php");
                        $hlp = $object->getHelp();
                        echo "Description\n\n";
                        echo "{$hlp["description"]}\n\n";
                        if (count($hlp["parameters"]) > 0) {
                            echo "Parameters\n\n";
                            foreach ($hlp["parameters"] as $key => $value) {
                                $tp = "";
                                if (isset($hlp["type"]["parameters"][$key])) {
                                    $tp = '`'.$hlp["type"]["parameters"][$key].'` / ';
                                }
                                echo "* $tp`$key`: $value\n";
                            }
                            echo "\n\n";
                        }
                        if (count($hlp["response"]) > 0) {
                            echo "Responses\n\n";
                            foreach ($hlp["response"] as $key => $value) {
                                $tp = "";
                                if (isset($hlp["type"]["response"][$key])) {
                                    $tp = '`'.$hlp["type"]["response"][$key].'` / ';
                                }
                                echo "* $tp`$key`: $value\n";
                            }
                            echo "\n\n";
                        }
                        // Access data
                        echo "Permissions\n\n";
                        $acc = $object->getAccessData($q->fields["path"].$cmd.".php");
                        echo "* `Owner`: ".driverUser::getUserName($acc["owner"])."\n";
                        echo "* `Group`: ".driverUser::getGroupName($acc["group"])."\n";
                        echo "* `Flags`: ".driverUser::secFileToString($acc["flags"])."\n";
                        echo "\n\n";
                    }
                    $q->MoveNext();
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
                "description" => "Export commands help to a GitHub Wiki file.", 
                "parameters" => array(
                    "path" => "File path where export.",
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