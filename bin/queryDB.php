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

if (!class_exists("commandQueryDB")) {
    class commandQueryDB extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "host" => "",
                "name" => "",
                "user" => "",
                "pass" => "",
                "sql" => "",
            ), $params);
            
            $resp = array( "recordset" => array() );
            $conn = NewADOConnection('mysqli');
            try {
                @$conn->Connect(
                    $params['host'], 
                    $params['user'], 
                    $params['pass'], 
                    $params['name']
                );
                if(!$conn->IsConnected()){
                    $resp = array('ok' => false, 'msg' => __('Connection error.'));
                } else {
                    $q = $conn->Execute($params['sql']);
                    while (!$q->EOF) {
                        $resp["recordset"][] = $q->fields;
                        $q->MoveNext();
                    }
                }
            } catch (Exception $exc) {
                $resp = array('ok' => false, 'msg' => __('Connection error.'));
            }
            
            return $resp;
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Do a direct query to a data base. IMPORTANT: This command is so dangerous !! Protect it with restrictive permissions, only root must execute it."), 
                "parameters" => array(
                    "host" => __("DB host name."),
                    "name" => __("Database name."),
                    "user" => __("Username."),
                    "pass" => __("Password."),
                    "sql" => __("Query."),
                ), 
                "response" => array(
                    "recordset" => __("Array with results.")
                ),
                "type" => array(
                    "parameters" => array(
                    "host" => "string",
                    "name" => "string",
                    "user" => "string",
                    "pass" => "string",
                    "sql" => "string",
                    ), 
                    "response" => array(
                        "recordset" => "array"
                    ),
                ),
                "echo" => false
            );
        }
    }
}
return new commandQueryDB();
