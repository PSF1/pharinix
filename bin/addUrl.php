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

if (!class_exists("commandAddUrl")) {
    class commandAddUrl extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $resp = array("ok" => false, "msg" => "");
            if ($params["url"] == "" || $params["cmd"] == "") {
                $resp["msg"] = "URL or CMD is empty.";
            } else {
                $sql = "SELECT `id` FROM `url_rewrite` where `url` = '{$params["url"]}'";
                $q = dbConn::Execute($sql);
                if (!$q->EOF) {
                    $resp["msg"] = "URL just existe.";
                } else {
                    $sql = "insert into `url_rewrite` set `url` = '{$params["url"]}', `rewriteto` = '{$params["cmd"]}'";
                    dbConn::Execute($sql);
                    $resp["ok"] = true;
                }
            }
            
            return $resp;
        }

        public static function getAccess() {
            return parent::getAccess(__FILE__);
        }
        
        public static function getHelp() {
            return array(
                "description" => "Add a new URL to the rewrite list.", 
                "parameters" => array(
                    "url" => "The new URL, relative at root. Ex. home to http://127.0.0.1/home", 
                    "cmd" => "POST's encoded string with command and parameters. Ex. command=pageToHTML&page=home"), 
                "response" => array(
                    "ok" => "TRUE if new URL added.", 
                    "msg" => "If FALSE contain the error message."),
            );
        }
    }
}
return new commandAddUrl();