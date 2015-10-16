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

if (!class_exists("commandModInstall")) {
    class commandModInstall extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "path" => "usr/",
                "zip" => "",
            ), $params);
            
            if (!is_file($params['zip'])) {
                return array("ok" => false, "msg" => __('Module zip file not found.'));
            }
            if (!is_dir($params['path'])) {
                return array("ok" => false, "msg" => __('Install path not found.'));
            }
            if (!driverTools::str_end('/', $params['path'])) {
                $params['path'] .= '/';
            }
            $zip = driverTools::pathInfo($params['zip']);
            if (is_dir($params['path'].$zip['name'])) {
                return array("ok" => false, "msg" => __('Install path is in use.'));
            }
            $fZip = new ZipArchive();
            $fZip->open($params['zip']);
            $uid = uniqid();
            $tmpFolder = 'var/tmp/'.$uid.'/';
            if (!is_dir('var/tmp/')) mkdir('var/tmp/');
            mkdir($tmpFolder);
            $fZip->extractTo($tmpFolder);
            $resul = driverCommand::run('modInstallFromLocalFolder', array(
                "path" => $params['path'],
                "folder" => $tmpFolder,
            ));
            driverTools::fileRemove($tmpFolder);
            return $resul;
        }

        public static function closeMonitor($lp) {
            // Closing progressbar
            driverLPMonitor::close($lp->id);
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Install a module."), 
                "parameters" => array(
                    "path" => __("Optional path where install the module, relative to Pharinix root path. If not defined the default path is 'usr/'"),
                    "zip" => __("Path to the ZIP file with the new module."),
                ), 
                "response" => array(
                        "ok" => __("TRUE if the installation is OK."),
                        "msg" => __("If install error this contains the error message."),
                        "path" => __("If install ok contains the install path."),
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
return new commandModInstall();