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

if (!class_exists("commandModInstallFromGitHub")) {
    class commandModInstallFromGitHub extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "path" => "usr/",
                "url" => "",
            ), $params);
            
            $resp = driverCommand::run('curlGetFile', array(
                'url' => $params['url']
            ));
            
            if (!$resp['ok']) {
                return $resp;
            } else { // Prepare the temporal package.
                // Unzip files
                
                // Re-zip with the correct structure
                
                // Do the real install
                
                // Remove temporal package and downloaded file
                
            }
        }

        public static function getHelp() {
            return array(
                "description" => "Install a module from GitHub project, it can be from master ZIP or tag release ZIP file. (Requires cURL.)", 
                "parameters" => array(
                    "path" => "Optional path where install the module, relative to Pharinix root path. If not defined the default path is 'usr/'",
                    "url" => "URL of the module's GitHub ZIP file.",
                ), 
                "response" => array(
                        "ok" => "TRUE if the installation is OK.",
                        "msg" => "If install error this contains the error message.",
                        "path" => "If install ok contains the install path.",
                    ),
                "type" => array(
                    "parameters" => array(
                        "path" => "string",
                        "url" => "string",
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
return new commandModInstallFromGitHub();