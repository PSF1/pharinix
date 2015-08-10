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

if (!class_exists("commandModInstall")) {
    class commandModInstall extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "path" => "usr/",
                "zip" => "",
            ), $params);
            
            if (!is_file($params['zip'])) {
                return array("ok" => false, "msg" => 'Module zip file not found.');
            }
            if (!is_dir($params['path'])) {
                return array("ok" => false, "msg" => 'Install path not found.');
            }
            if (!driverTools::str_end('/', $params['path'])) {
                $params['path'] .= '/';
            }
            $zip = driverTools::pathInfo($params['zip']);
            if (is_dir($params['path'].$zip['name'])) {
                return array("ok" => false, "msg" => 'Install path is in use.');
            }
            $fZip = new ZipArchive();
            $fZip->open($params['zip']);
            $uid = uniqid();
            $tmpFolder = 'var/tmp/'.$uid.'/';
            if (!is_dir('var/tmp/')) mkdir('var/tmp/');
            mkdir($tmpFolder);
            $fZip->extractTo($tmpFolder);
            // Get meta data
            if (is_file($tmpFolder.'meta.json')) {
                $jsonMeta = file_get_contents($tmpFolder.'meta.json');
                driverTools::fileRemove($tmpFolder);
                $meta = json_decode($jsonMeta);
                if (json_last_error() != 0) {
                    return array("ok" => false, "msg" => json_last_error_msg());
                }
                $installPath = $params['path'].$meta->meta->slugname.'/';
                // Module installed?
                $mods = driverCommand::run('getNodes', array(
                    'nodetype' => 'modules',
                    'where' => "`title` = '{$meta->meta->slugname}'",
                ));
                if (count($mods) > 0) {
                    $ids = array_keys($mods);
                    return array("ok" => false, "msg" => "Module '{$meta->meta->slugname}' is installed with version '{$mods[$ids[0]]['version']}'.");
                }
                // Verify requirements
                foreach($meta->requirements as $need => $version) {
                    if ($need == 'pharinix') {
                        $ver = driverCommand::run('getVersion');
                        $ver = $ver['version'];
                        if(!driverTools::versionIsGreaterOrEqual($version, $ver)) {
                            return array("ok" => false, "msg" => "This module requires '$need' version '$version' and you have version '$ver'.");
                        }
                    } else {
                        $ver = driverCommand::run('getNodes', array(
                            'nodetype' => 'modules',
                            'where' => "`title` = '$need'",
                        ));
                        if (count($ver) == 0) {
                            return array("ok" => false, "msg" => "This module requires '$need' version '$version' and you don't have it.");
                        } else {
                            $ids = array_keys($ver);
                            if(!driverTools::versionIsGreaterOrEqual($version, $ver[$ids[0]]['version'])) {
                                return array("ok" => false, "msg" => "This module requires '$need' version '$version' and you have version '$ver'.");
                            }
                        }
                    }
                }
                // Copy module to final path
                $fZip->extractTo($installPath);
                // Apply configuration
                if (isset($meta->configuration)) {
                    foreach($meta->configuration as $group => $values) {
                        switch ($group) { // System configuration can't be changed by modules meta
                            case '[core]':
                            case '[mysql]':
                            case '[safe_mode]':
                                break;
                            default:
                                driverConfig::getCFG()->addSection($group);
                                foreach($values as $key => $value) {
                                    driverConfig::getCFG()->getSection($group)->set($key, $value);
                                }
                                break;
                        }
                    }
                    driverConfig::getCFG()->save();
                }
                // Install command's paths
                if (isset($meta->bin_paths)) {
                    foreach($meta->bin_paths as $cpath) {
                        driverCommand::run('cfgAddPath', array(
                            'path' => $installPath.$cpath
                        ));
                    }
                    driverCommand::refreshPaths();
                }
                // Install booting
                if (isset($meta->booting)) {
                    $narr = array();
                    foreach($meta->booting as $bootObj) {
                        $cmd = '';
                        $pars = array();
                        $priority = 0;
                        foreach($bootObj as $key => $value) {
                            switch ($key) {
                                case 'priority':
                                    $priority = $value;
                                    break;
                                default: // Is a command
                                    $cmd = $key;
                                    foreach($value as $par => $val) {
                                        $pars[] = $par."=".$val;
                                    }
                                    break;
                            }
                        }
                        $boot = driverCommand::run('addBooting', array(
                            'cmd' => $cmd,
                            'parameters' => implode("&", $pars),
                            'priority' => $priority,
                        ));
                        $bootObj->id = $boot['uid'];
                        $narr[] = $bootObj;
                    }
                    $meta->booting = $narr;
                }
                // Install meta to modules table
                driverCommand::run('addNode', array(
                    'nodetype' => 'modules',
                    'title' => $meta->meta->slugname,
                    'path' => $installPath,
                    'meta' => json_encode($meta),
                    'version' => $meta->meta->version,
                ));
                // Install node types
                if (isset($meta->nodetypes)) {
                    foreach($meta->nodetypes as $nodetype => $def) {
                        $nodetype = strtolower($nodetype);
                        // Find especial field names like __removetitle
                        $haveTitle = true;
                        $labelField = '';
                        foreach($def as $name => $fieldDef) {
                            switch ($name) {
                                case '__removetitle':
                                    $haveTitle = !$fieldDef;
                                    break;
                                case '__labelfield':
                                    $labelField = $fieldDef;
                                    break;
                            }
                        }
                        // Create node types
                        $conf = array( 'name' => $nodetype );
                        if ($labelField != '') {
                            $conf['label_field'] = $labelField;
                        }
                        driverCommand::run('addNodeType', $conf);
                        if (!$haveTitle) {
                            driverCommand::run('delNodeField', array(
                                'nodetype' => $nodetype,
                                'name' => 'title'
                            ));
                        }
                        // Create fields
                        foreach($def as $name => $fieldDef) {
                            switch ($name) {
                                case '__removetitle':
                                case '__labelfield':
                                    break;
                                default:
                                    $field = array(
                                        'node_type' => $nodetype,
                                        'name' => $name
                                    );
                                    if (isset($fieldDef->type)) $field['type'] = $fieldDef->type;
                                    if (isset($fieldDef->iskey)) $field['iskey'] = $fieldDef->iskey;
                                    if (isset($fieldDef->len)) $field['len'] = $fieldDef->len;
                                    if (isset($fieldDef->required)) $field['required'] = $fieldDef->required;
                                    if (isset($fieldDef->readonly)) $field['readonly'] = $fieldDef->readonly;
                                    if (isset($fieldDef->locked)) $field['locked'] = $fieldDef->locked;
                                    if (isset($fieldDef->multi)) $field['multi'] = $fieldDef->multi;
                                    if (isset($fieldDef->default)) $field['default'] = $fieldDef->default;
                                    if (isset($fieldDef->label)) $field['label'] = $fieldDef->label;
                                    if (isset($fieldDef->help)) $field['help'] = $fieldDef->help;

                                    driverCommand::run('addNodeField', $field);
                                    break;
                            }
                        }
                    }
                }
                // Run SQL queries
                if (isset($meta->sql)) {
                    if (isset($meta->sql->install)) {
                        foreach ($meta->sql->install as $sql) {
                            dbConn::Execute($sql);
                        }
                    }
                }
                // Execute install commands
                if (isset($meta->install)) {
                    foreach($meta->install as $bootObj) {
                        $pars = array();
                        foreach($bootObj as $key => $value) {
                            $cmd = $key;
                            foreach($value as $par => $val) {
                                $pars[$par] = $val;
                            }
                        }
                        driverCommand::run($cmd, $pars);
                    }
                }

                return array("ok" => true, "path" => $installPath);
            } else {
                driverTools::fileRemove($tmpFolder);
                return array("ok" => false, "msg" => "Meta file not found at '".$tmpFolder.'meta.json'."'. Have the package the correct structure?.");
            }
        }

        public static function getHelp() {
            return array(
                "description" => "Install a module.", 
                "parameters" => array(
                    "path" => "Optional path where install the module, relative to Pharinix root path. If not defined the default path is 'usr/'",
                    "zip" => "Path to the ZIP file with the new module.",
                ), 
                "response" => array(
                        "ok" => "TRUE if the installation is OK.",
                        "msg" => "If install error this contains the error message.",
                        "path" => "If install ok contains the install path.",
                    ),
                "type" => array(
                    "parameters" => array(
                        "path" => "string",
                        "zip" => "string",
                    ), 
                    "response" => array(
                        "ok" => "booelan",
                        "msg" => "string",
                        "path" => "string",
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
return new commandModInstall();