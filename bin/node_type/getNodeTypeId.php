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
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

/*
* Search ID of node type by name
* Parameters:
* name = Node type name to search. False if not found.
*/
if (!class_exists("commandGetNodeTypeID")) {
    class commandGetNodeTypeID extends driverCommand {

        public static function runMe(&$params, $debug = true) {
           $sql = "select id from `node_type` where `name` = '{$params["name"]}'";
           $q = dbConn::Execute($sql);
           if ($q->EOF) {
               return array("id" => false);
           } else {
               return array("id" => $q->fields["id"]);
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
                "package" => 'core',
                "description" => __("Search ID of node type by name"), 
                "parameters" => array(
                    "name" => __("Node type name to search."),
                ), 
                "response" => array(
                    "id" => __("ID of the node type. False if not found.")
                ),
                "type" => array(
                    "parameters" => array(
                        "name" => "string",
                    ), 
                    "response" => array(
                        "id" => "integer"
                    ),
                )
            );
        }
    }
}
return new commandGetNodeTypeID();