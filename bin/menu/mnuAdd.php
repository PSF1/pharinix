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

if (!class_exists("commandMnuAdd")) {
    class commandMnuAdd extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            unset($params['nodetype']);
            
            $params = array_merge(array(
                        'nodetype' => 'menu',
                        'slugname' => '',
                        'isbrand' => false,
                        'isnotsudoed' => false,
                        'isnotloged' => false,
                        'havegroup' => '',
                        'issudoed' => false,
                        'linkto' => '',
                        'parent' => 0,
                        'title' => '',
                        'isloged' => false,
                        'params' => '',
                        'cmd' => '',
                        'opennew' => false,
                        'onlyparent' => false,
                        'order' => 100,
                        'aling' => 'left'
                    ), $params);
            if (!is_numeric($params['parent'])) {
                $parent = driverCommand::run('getNodes', array(
                    'nodetype' => 'menu',
                    'fields' => 'id',
                    'where' => "`slugname` = '{$params['parent']}'",
                ));
                if (count($parent) > 0) {
                    foreach($parent as $key => $what) {
                        $params['parent'] = $key;
                        break;
                    }
                } else {
                    return array('ok' => false, 'msg' => __('Parent menu not found.'));
                }
            }
			// TODO: Change permissions to allow any user read the menu entry.
            return driverCommand::run('addNode', $params);
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Add a new menu option."), 
                "parameters" => array(
                        'slugname' => __('A required unique name used how reference'),
                        'isbrand' => __('Show this option how a menu title, or brand'),
                        'isnotsudoed' => __('Show option if the user is not root'),
                        'isnotloged' =>	__('Show option if the user is not loged'),
                        'havegroup' => __('Show option if the user have the group setup in the field params'),
                        'issudoed' => __('Show option if the user is root'),
                        'linkto' => __('URL linked from this option, if set value \'command\' it execute the command, and parameters, associated'),
                        'parent' => __('Owner option'),
                        'title' => __('A title string for this node.'),
                        'isloged' => __('Show option if the user is logged'),
                        'params' => __('Command parameters if linkto value is \'command\''),
                        'cmd' => __('Command to execute if linkto value is \'command\''),
                        'opennew' => __('Open in a new window or tab'),
                        'onlyparent' => __('TRUE if it\'s only a parent option without action'),
                        'order' => 'Order value, 0 first.',
                        'aling' => 'Menu alignment, it could be center, left or right'
                    ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'slugname' => 'string',
                        'isbrand' => 'bool',
                        'isnotsudoed' => 'bool',
                        'isnotloged' => 'bool',
                        'havegroup' => 'bool',
                        'issudoed' => 'bool',
                        'linkto' => 'string',
                        'parent' => 'integer',
                        'title' => 'string',
                        'isloged' => 'bool',
                        'params' => 'longtext',
                        'cmd' => 'string',
                        'opennew' => 'bool',
                        'onlyparent' => 'bool',
                        'order' => 'integer',
                        'aling' => 'string'
                    ), 
                    "response" => array(),
                ),
                "echo" => false,
                "interface" => false,
//                "hooks" => array(
//                        array(
//                            "name" => "nothingHook",
//                            "description" => "sadasdaAllow rewrite a HTML alert message.",
//                            "parameters" => array(
//                                "alert" => "asdasdResponse to be echoed to the client.",
//                                "msg" => "asdasdThe message to show."
//                            )
//                        )
//                )
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
return new commandMnuAdd();