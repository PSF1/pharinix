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

if (!class_exists("commandSetMyLang")) {
    class commandSetMyLang extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "lang" => ''
            ), $params);
            $resp = array(
                "ok" => true
            );
            if ($params['lang'] != '') {
                $_SESSION['lang'] = explode(",", $params['lang']);
            } else {
                driverCommand::run('updateNode', array(
                    'nodetype' => 'user',
                    'nid' => driverUser::getID(true),
                    'language' => '',
                ));
                unset($_SESSION['lang']);
                driverUser::getLangOfUser();
            }
            if ($params['lang'] != '' && isset($_SESSION['lang']) && 
                    is_array($_SESSION['lang']) && count($_SESSION['lang']) > 0) {
                driverCommand::run('updateNode', array(
                    'nodetype' => 'user',
                    'nid' => driverUser::getID(true),
                    'language' => $_SESSION['lang'][0],
                ));
            }
            $resp['lang'] = $_SESSION['lang'];
            return $resp;
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
                "description" => __("Set the session language."), 
                "parameters" => array(
                    "lang" => __("The language code, if is '' then set the client default languages.")
                ), 
                "response" => array(
                    "ok" => __("TRUE if ok."),
                    "lang" => __("The session language set.")
                ),
                "type" => array(
                    "parameters" => array(
                        "lang" => "string"
                    ), 
                    "response" => array(
                        "ok" => "boolean",
                        "lang" => "array"
                    ),
                ),
                "echo" => false
            );
        }
    }
}
return new commandSetMyLang();
