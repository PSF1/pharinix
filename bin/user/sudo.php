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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandSudo")) {
    class commandSudo extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "user" => "",
            ), $params);
            if ($params["user"] == "root@localhost") { // Root access
                driverUser::sudo(true);
            } else if ($params["user"] == "") { // Exit sudo
                driverUser::sudo(false);
            } else { // Sudoing other user
                //TODO: Sudoing other user
            }
            return array("ok" => true);
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_GROUP_EXECUTE;
        }
        
        public static function getAccessData($path = "") {
            $me = __FILE__;
            $resp = parent::getAccessData($me);
            if ($resp["group"] == 0) {
                // We change default group from root to sudoers.
                $sql = "select `id` from `node_group` where `title` = 'sudoers'";
                $q = dbConn::Execute($sql);
                if (!$q->EOF) {
                    $resp["group"] = $q->fields["id"];
                }
            }
            return $resp;
        }

        public static function getHelp() {
            return array(
                "description" => "Change active user without logout and login. This is the unique method to get root access, super administration. A user can do sudo when it have sudoers group.", 
                "parameters" => array(
                    "user" => "User email to supplant. Root is 'root@localhost'. If this parameter is empty then we exit sudo.",
                ), 
                "response" => array(
                    "ok" => "TRUE if session started."
                )
            );
        }
    }
}
return new commandSudo();