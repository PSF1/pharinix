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

if (!class_exists("commandToJSON")) {
    class commandToJSON extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = self::cleanItem($params);
            $resp = array("json" => json_encode($params, JSON_FORCE_OBJECT));
            if (json_last_error_msg() != "No error") {
                $resp = array('json' => '"'.json_last_error_msg().'"');
            }
            return $resp;
        }
        
        private static function cleanItem($params) {
            if (!is_array($params) && !($params instanceof stdClass)) return $params;
            foreach ($params as $key => $value) {
                if (is_string($value)) $value = utf8_encode($value);
                if (is_string($key)) {
                    $key1 = utf8_encode($key);
                    unset($params[$key]);
                    $params[$key1] = $value;
                }
                if (is_array($value)) {
                    self::cleanItem($params[$key]);
                }
            }
            return $params;
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
                "description" => __("Translate parameters to JSON string."), 
                "parameters" => array(
                    "some" => __("It can receive any amount of parameters.")
                    ), 
                "response" => array(
                    "json" => __("The json string.")
                    ),
                "type" => array(
                    "parameters" => array(
                        "some" => "args"
                        ), 
                    "response" => array(
                        "json" => "string"
                        ),
                )
            );
        }
    }
}
return new commandToJSON();