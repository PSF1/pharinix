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

if (!class_exists("commandAddFieldFormat")) {
    class commandAddFieldFormat extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                    "cmd" => "",
                    "type" => "",
                    "isread" => false,
                    "iswrite" => false,
                    "isdefault" => false,
                ), $params);
            
            $resp = array("ok" => false, "msg" => "");
            if ($params["cmd"] == "") {
                $resp["msg"] = __("'cmd' is required.");
            }
            if ($params["type"] == "") {
                $resp["msg"] = __("'type' is required.");
            }
            if (!$params["isread"] && !$params["iswrite"]) {
                $resp["msg"] = __("This formatter can't do nothing.");
            }
            
            if ($resp["msg"] == "") {
                $sql = "SELECT count(*) FROM `node_formats` where `command` = '{$params["cmd"]}' && `type` = '{$params["type"]}'";
                $q = dbConn::Execute($sql);
                if ($q->EOF) {
                    $sql = "insert into `node_formats` set ";
                    $sql .= "`command` = '{$params["cmd"]}', ";
                    $sql .= "`type` = '{$params["type"]}', ";
                    $sql .= "`read` = '".($params["isread"]?"1":"0")."', ";
                    $sql .= "`write` = '".($params["iswrite"]?"1":"0")."', ";
                    $sql .= "`default` = '".($params["isdefault"]?"1":"0")."' ";
                    dbConn::Execute($sql);
                } else {
                    $resp["msg"] = __("Duplicated formatter.");
                }
            }
            return $resp;
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Add a new field formatter."), 
                "parameters" => array(
                    "cmd" => __("Command that can format the field."),
                    "type" => __("Basic type to format."),
                    "isread" => __("Can format field to read only."),
                    "iswrite" => __("Can format field to read & write."),
                    "isdefault" => __("This formatter is the default formatter to this type."),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "cmd" => "string",
                        "type" => "string",
                        "isread" => "boolean",
                        "iswrite" => "boolean",
                        "isdefault" => "boolean",
                    ), 
                    "response" => array(),
                )
            );
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return parent::getAccessFlags();
        }
    }
}
return new commandAddFieldFormat();