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

if (!class_exists("commandModUninstal")) {
    class commandModUninstal extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "name" => "",
            ), $params);
            
            if ($params['name'] == '') {
                return array("ok" => false, "msg" => 'Module name is required.');
            }
            
            $mods = driverCommand::run('getNodes', array(
                'nodetype' => 'modules',
                'where' => "`title` = '{$params['name']}'"
            ));
            if (count($mods) == 0) {
                return array("ok" => false, "msg" => 'Module not found.');
            }
            if (count($mods) > 1) {
                return array("ok" => false, "msg" => 'Module name is not unique.');
            }
            
            // TODO: If this modules is required by others installed modules don't allow uninstall.
            
            $ids = array_keys($mods);
            $mod = $mods[$ids[0]];
            unset($mods);
            unset($ids);
            // Get meta data
            $jsonMeta = $mod['meta'];
            $meta = json_decode($jsonMeta);
            if (json_last_error() != 0) {
                return array("ok" => false, "msg" => json_last_error_msg());
            }
            $installPath = $mod['path'];
            // Execute uninstall commands
            
            // Run SQL queries
            if (isset($meta->sql)) {
                if (isset($meta->sql->uninstall)) {
                    foreach ($meta->sql->uninstall as $sql) {
                        dbConn::Execute($sql);
                    }
                }
            }
            
            // Remove node types
            if (isset($meta->nodetypes)) {
                foreach($meta->nodetypes as $nodetype => $def) {
                    // Remove node types
                    $conf = array( 'name' => $nodetype );
                    driverCommand::run('delNodeType', $conf);
                }
            }
            // Remove booting
            if (isset($meta->booting)) {
                foreach($meta->booting as $bootObj) {
                    foreach($bootObj as $key => $value) {
                        switch ($key) {
                            case 'id':
                                driverCommand::run('delBooting', array(
                                    'uid' => $value,
                                ));
                                break;
                        }
                    }
                }
            }
            // Remove command's paths
            if (isset($meta->bin_paths)) {
                foreach($meta->bin_paths as $cpath) {
                    driverCommand::run('cfgDelPath', array(
                        'path' => $cpath
                    ));
                }
                driverCommand::refreshPaths();
            }
            // Remove configuration
            if (isset($meta->configuration)) {
                foreach($meta->configuration as $group => $values) {
                    switch ($group) {
                        case '[core]':
                        case '[mysql]':
                        case '[safe_mode]':
                            break;
                        default:
                            driverConfig::getCFG()->delSection($group);
                            break;
                    }
                }
                driverConfig::getCFG()->save();
            }
            // Remove meta in modules table
            driverCommand::run('delNode', array(
                'nodetype' => 'modules',
                'nid' => $mod['id'],
            ));
            
            // Remove module from final path
            driverTools::fileRemove($installPath);
            
            return array("ok" => true);
        }

        public static function getHelp() {
            return array(
                "description" => "Remove a module.", 
                "parameters" => array(
                    "name" => "Slugname of the module to uninstall.",
                ), 
                "response" => array(
                        "ok" => "TRUE if the uninstallation is OK.",
                        "msg" => "If uninstall error this contains the error message.",
                    ),
                "type" => array(
                    "parameters" => array(
                        "name" => "string",
                    ), 
                    "response" => array(
                        "ok" => "booelan",
                        "msg" => "string",
                    ),
                )
            );
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
//        public static function getAccessFlags() {
//            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
//        }
    }
}
return new commandModUninstal();