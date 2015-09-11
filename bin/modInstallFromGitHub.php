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
                $file = $resp['file'];
                // Unzip files
                $fZip = new ZipArchive();
                $fZip->open($file);
                $uid = uniqid();
                $gitFolder = 'var/tmp/'.$uid.'/';
                if (!is_dir('var/tmp/')) mkdir('var/tmp/');
                mkdir($gitFolder);
                $fZip->extractTo($gitFolder);
                $fZip->close();
                @unlink($file);
                // Re-zip with the correct structure
                $auxFolderContent = driverTools::lsDir($gitFolder);
                if (count($auxFolderContent['folders']) > 0) {
                    if (is_file($auxFolderContent['folders'][0].'/meta.json')) {
                        $tmpZip = new ZipArchive();
                        $tmpZipuid = uniqid();
                        $tmpZipFilePath = 'var/tmp/'.$tmpZipuid.".tmp";
                        $tmpZip->open($tmpZipFilePath, ZipArchive::CREATE);
                        self::addFiles($tmpZip, $auxFolderContent['folders'][0]);
                        $tmpZip->close();
                        driverTools::fileRemove($gitFolder);
                        // Do the real install
                        $resp = driverCommand::run('modInstall', array(
                            'zip' => $tmpZipFilePath
                        ));
                        // Remove temporal package file
                        @unlink($tmpZipFilePath);
                        // Response
                        return $resp;
                    } else {
                        driverTools::fileRemove($gitFolder);
                        return array('ok' => false, 'msg' => __('Wrong git zip structure, meta file not found.'));
                    }
                } else {
                    driverTools::fileRemove($gitFolder);
                    return array('ok' => false, 'msg' => __('Wrong git zip structure.'));
                }
            }
        }
        
        /**
         * 
         * @param ZipArchive $zip
         * @param string $path
         */
        public static function addFiles($zip, $path, $basePath = "") {
            if(!driverTools::str_end("/", $path)) $path .= '/';
            if ($basePath == "") {
                $basePath = $path;
            }
            $content = driverTools::lsDir($path, '*');
            foreach($content['files'] as $file) {
                $nFile = str_replace($basePath, "", $file);
                $zip->addFile($file, $nFile);
            }
            foreach($content['folders'] as $folder) {
                self::addFiles($zip, $folder, $basePath);
            }
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Install a module from GitHub project, it must be from a tag release ZIP file. (Requires cURL.)"), 
                "parameters" => array(
                    "path" => __("Optional path where install the module, relative to Pharinix root path. If not defined the default path is 'usr/'"),
                    "url" => __("URL of the module's GitHub ZIP file."),
                ), 
                "response" => array(
                        "ok" => __("TRUE if the installation is OK."),
                        "msg" => __("If install error this contains the error message."),
                        "path" => __("If install ok contains the install path."),
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
return new commandModInstallFromGitHub();