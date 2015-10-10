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

if (!class_exists("commandInlineGetRootForm")) {
    class commandInlineGetRootForm extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            echo '<form class="navbar-form" role="form" action="'.CMS_DEFAULT_URL_BASE.'" method="post" enctype="application/x-www-form-urlencoded">';
            echo        '<input type="hidden" name="cmd" value="startSession"/>';
            echo        '<input type="hidden" name="command" value="goTo"/>';
            echo        '<input type="hidden" name="interface" value="nothing"/>';
            echo        '<input type="hidden" name="gtpath" class="menuInlineToHTMLReloadURL" value=""/>';
            echo        '<div class="form-group">';
            echo            '<input type="text" class="form-control" name="user" placeholder="'.__('mail').'">';
            echo        '</div>';
            echo        '<div class="form-group">';
            echo            '<input type="password" class="form-control" name="pass" placeholder="'.__('password').'">';
            echo        '</div>';
            echo        '<button type="submit" class="btn btn-default">'.__('Login').'</button>';
            echo    '</form>';
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __(""), 
                "parameters" => array(), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(), 
                    "response" => array(),
                ),
                "echo" => true,
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
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
    }
}
return new commandInlineGetRootForm();