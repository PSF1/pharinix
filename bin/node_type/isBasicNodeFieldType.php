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
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

/*
 * Identify a node field type how basic type or it's a reference to another node type
 * Parameters:
 * type = Node field type to test
 */
if (!class_exists("commandIsBasicFieldType")) {
    class commandIsBasicFieldType extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            switch ($params["type"]) {
                case 'htmltext': // HTML long text
                case 'longtext': // Very long text
                case 'bool': // Boolean value
                case 'datetime': // Date and time
                case 'double': // Number with decimals
                case 'integer': // Integer number
                case 'string': // Text string
                case 'password': // Text string
                case 'nodesec': // Integer
                    return array("basic" => true);
                default:
                    return array("basic" => false);
            }
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
                "description" => "Identify a field's node type how basic type or it's a reference to another node type.", 
                "parameters" => array(
                    "type" => "The type name to test."
                ), 
                "response" => array(
                    "basic" => "True/False is a basic type"
                ),
                "type" => array(
                    "parameters" => array(
                        "type" => "string"
                    ), 
                    "response" => array(
                        "basic" => "boolean"
                    ),
                )
            );
        }
    }
}
return new commandIsBasicFieldType();