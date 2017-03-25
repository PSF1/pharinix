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

if (!class_exists("commandChCRUDNode")) {
    class commandChCRUDNode extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "nodetype" => "",
                "nid" => null,
                'segment' => driverNodes::CHANGECRUD_ALL,
                'create' => false, 
                'read' => false, 
                'update' => false, 
                'delete' => false
            ), $params);
            return driverNodes::chCRUDNode(
                    $params['nodetype'], 
                    $params['nid'], 
                    $params['segment'], 
                    $params['create'], 
                    $params['read'], 
                    $params['update'], 
                    $params['delete']
                   );
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("To change permission of node type or node that is owned by you."), 
                "parameters" => array(
                    "nodetype" => __("Node type that you need change permission."),
                    "nid" => __("Node ID of the node that you need change. Optional, if it's set try change a node, else try change a node type."),
                    'segment' => sprintf(__('Flag segment to alter, the segments index are ALL = %s, GROUP = %s, OWNER = %s.'),driverNodes::CHANGECRUD_ALL,driverNodes::CHANGECRUD_GROUP, driverNodes::CHANGECRUD_OWNER),
                    'create' => __('Create flag.'), 
                    'read' => __('Read flag.'), 
                    'update' => __('Update flag.'), 
                    'delete' => __('Delete flag.')
                ), 
                "response" => array(
                    "ok" => __("TRUE if changed."),
                    "flags" => __("If ok = TRUE contains the seted value."),
                ),
                "type" => array(
                    "parameters" => array(
                        "nodetype" => "string",
                        "nid" => "integer",
                        'segment' => 'integer',
                        'create' => 'boolean', 
                        'read' => 'boolean', 
                        'update' => 'boolean', 
                        'delete' => 'boolean'
                    ), 
                    "response" => array(
                        "ok" => "boolean",
                        "flags" => "integer",
                    ),
                ),
                "echo" => false
            );
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
    }
}
return new commandChCRUDNode();