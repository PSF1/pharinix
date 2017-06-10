<?php

/* 
 * Pharinix Copyright (C) 2017 Pedro Pelaez <aaaaa976@gmail.com>
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

if (!class_exists("commandModGen")) {
    class commandModGen extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "path" => "usr/",
                "name" => "",
                "slugname" => "",
                "author" => "",
                "website" => "",
                "description" => "",
                "licence" => "GNU General Public License v2.0",
            ), $params);
            
            // Simple verifications
            if (empty($params['name'])) return array('ok' => false, 'msg' => __('Module human name required.'), 'path' => '');
            if (empty($params['slugname'])) return array('ok' => false, 'msg' => __('Module code name required.'), 'path' => '');
            if (empty($params['author'])) return array('ok' => false, 'msg' => __('Author name and contact required.'), 'path' => '');
            
            $newModPath = $params['path'].$params['slugname'].'/';
            $resul = array(
                "ok" => false,
                "path" => $newModPath,
            );
            
            if (is_dir($newModPath)) {
                $resul['msg'] = __('Module folder is in use.');
            }
            
            $metaJson = new stdClass();
            $metaJson->meta = new stdClass();
            $metaJson->meta->name = $params['name'];
            $metaJson->meta->slugname = $params['slugname'];
            $metaJson->meta->version = "0.1";
            $metaJson->meta->autor = $params['author'];
            $metaJson->meta->website = $params['website'];
            $metaJson->meta->description = $params['description'];
            $metaJson->meta->licence = $params['licence'];
            $metaJson->configuration = new stdClass();
            $metaJson->booting = array();
            $metaJson->bin_paths = array("bin/");
            $metaJson->nodetypes = new stdClass();
            $metaJson->sql = new stdClass();
            $metaJson->install = array();
            $metaJson->uninstall = array();
            $metaJson->requirements = new stdClass();
            $metaJson->requirements->pharinix = CMS_VERSION;
            $metaJson->platforms = array("win", "linux");
            
            // Create module folder
            if (mkdir($newModPath, 0777)) {
                // Create folders bin and drivers
                mkdir($newModPath.'bin/', 0777);
                mkdir($newModPath.'drivers/', 0777);
                // Create meta.json
                file_put_contents($newModPath.'meta.json', json_encode($metaJson, JSON_PRETTY_PRINT));
                // All OK
                $resul['ok'] = true;
            }
            
            return $resul;
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Generate new module."), 
                "parameters" => array(
                    "path" => __("Path where make the new module. Default 'usr/'"),
                    "name" => __("Module human name"),
                    "slugname" => __("Module code name"),
                    "author" => __("Author name, and contact information. ex. Pedro Pelaez &lt;aaaaa976@gmail.com>"),
                    "website" => __("Module website"),
                    "description" => __("Module description"),
                    "licence" => __("Module licencia type. Default 'GNU General Public License v2.0'"),
                ), 
                "response" => array(
                        "ok" => __("TRUE if the module is generated."),
                        "path" => __("Path to the new module."),
                    ),
                "type" => array(
                    "parameters" => array(
                        "path" => "string",
                        "name" => "string",
                        "slugname" => "string",
                        "author" => "string",
                        "website" => "string",
                        "description" => "string",
                        "licence" => "string",
                    ), 
                    "response" => array(
                        "ok" => "boolean",
                        "path" => "string",
                    ),
                ),
                "echo" => false
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
return new commandModGen();