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

if (!class_exists("commandIsUrl")) {
    class commandIsUrl extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $resp = array("ok" => false);
            if (isset($params["url"]) && $params["url"] != "") {
                $sql = "SELECT `id` FROM `url_rewrite` where `url` = '{$params["url"]}'";
                $q = dbConn::Execute($sql);
                if (!$q->EOF) {
                    $resp["ok"] = true;
                }
            }
            
            return $resp;
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
                "description" => "Query the rewrite list and return if the url is rewrited.", 
                "parameters" => array(
                    "url" => "The URL to test.", 
                    ), 
                "response" => array(
                    "ok" => "TRUE if the URL exist.", 
                    ),
                "type" => array(
                    "parameters" => array(
                        "url" => "string", 
                        ), 
                    "response" => array(
                        "ok" => "boolean", 
                        ),
                )
            );
        }
    }
}
return new commandIsUrl();