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

if (!class_exists("commandHooksAddPermanent")) {
    class commandHooksAddPermanent extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'hook' => '',
                'file' => '',
                'func' => '',
            ), $params);
            
            foreach($params as $param) {
                if ($param == '')
                    return array('ok' => false, 'msg' => __('All parameters must have a value'));
            }
            return driverHook::saveHandler($params['hook'], $params['file'], $params['func']);
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Add a permanent hook handler."), 
                "parameters" => array(
                    'hook' => __('The hook to hand.'),
                    'file' => __('File path with the handler code.'),
                    'func' => __('Function or method that handle the hook. Ex. foo, or clas::foo'),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'hook' => 'string',
                        'file' => 'string',
                        'func' => 'string',
                    ), 
                    "response" => array(),
                ),
                "echo" => false
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
return new commandHooksAddPermanent();