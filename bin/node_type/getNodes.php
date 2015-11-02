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

if (!class_exists("commandGetNodes")) {
    class commandGetNodes extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            return driverNodes::getNodes($params, true);
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
                "description" => __("Return list of nodes from a node type. All field's names must be enclosed with '`'"), 
                "parameters" => array(
                    "nodetype" => __("Node type."),
                    "count" => __("Bool, If true then return number, in a 'amount' field, of nodes but without node data."),
                    "fields" => __("Comma separated string list. Optional, default '*'."),
                    "where" => __("Where condition."),
                    "order" => __("Order by fields."),
                    "group" => __("Group by fields."),
                    "offset" => __("Index of first node to return. Optional, default 0."),
                    "length" => __("Number of nodes to return from the offset. Optional, default 100."),
                ), 
                "response" => array(
                    "rs" => __("Node array with the ID how index. Multivalued fields will be returned how related ID's array."),
                ),
                "type" => array(
                    "parameters" => array(
                        "nodetype" => "string",
                        "count" => "boolean",
                        "fields" => "string",
                        "where" => "string",
                        "order" => "string",
                        "group" => "string",
                        "offset" => "integer",
                        "length" => "integer",
                    ), 
                    "response" => array(
                        "rs" => "array",
                    ),
                ),
                "echo" => false
            );
        }
    }
}
return new commandGetNodes();