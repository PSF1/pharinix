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

if (!class_exists("commandStartSession")) {
    class commandStartSession extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "user" => "guest@localhost",
                "pass" => ""
            ), $params);
            driverUser::logIn($params["user"], $params["pass"]);
            $_SESSION["log_error"] = !driverUser::isLoged();
            return array(
                "ok" => driverUser::isLoged(),
                "id" => session_id(),
            );
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
                "description" => __("Start the user session."), 
                "parameters" => array(
                    "user" => __("User email to login."),
                    "pass" => __("User password to login."),
                ), 
                "response" => array(
                    "ok" => __("TRUE if session started."),
                    "id" => __("Session ID to use how auth_token.")
                ),
                "type" => array(
                    "parameters" => array(
                        "user" => "string",
                        "pass" => "string",
                    ), 
                    "response" => array(
                        "ok" => "boolean",
                        "id" => "string"
                    ),
                ),
                "echo" => false
            );
        }
    }
}
return new commandStartSession();