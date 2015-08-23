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

if (!class_exists("commandCurlGetFile")) {
    class commandCurlGetFile extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "url" => "",
            ), $params);
            
            if ($params['url'] == '') {
                return array('ok' => false, 'msg' => __('URL is required.'));
            }
            if (function_exists('curl_exec')) {
                $uid = uniqid();
                $tmpFile = 'var/tmp/'.$uid.'.tmp';
                if (!is_dir('var/tmp/')) mkdir('var/tmp/');
                // http://stackoverflow.com/a/6409531
                set_time_limit(0);
                $fp = fopen ($tmpFile, 'w+');
                $ch = curl_init(str_replace(" ","%20",$params['url']));
                curl_setopt($ch, CURLOPT_TIMEOUT, 50);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
                fclose($fp);
                if ($err != '') {
                    return array('ok' => false, 'msg' => $err);
                } else {
                    return array('ok' => true, 'file' => $tmpFile);
                }
            } else {
                return array('ok' => false, 'msg' => __('cURL not installed.'));
            }
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Download a remote file from a HTTP or HTTPS URL. (Requires cURL installed in PHP.)"), 
                "parameters" => array(
                    "url" => __("URL of the file to download."),
                ), 
                "response" => array(
                        "ok" => __("TRUE if the download is OK."),
                        "msg" => __("If download error this contains the error message."),
                        "file" => __("If download ok, contains the downloaded file path. Please remove the temporal file when finish with it."),
                    ),
                "type" => array(
                    "parameters" => array(
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
return new commandCurlGetFile();