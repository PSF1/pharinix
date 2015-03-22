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

 class driverUser {
    private $isLoged = false;
    
    /*
     * O = Owner, G = Group, A = All
     * R = Read, W = Write, X = Execute
     * Unused:OR.OW.OX:GR.GW.GX:AR.AW.AX:
     * 00000000000000000000000:0.0.0:0.0.0:0.0.0
     */
    const PERMISSION_OWNER_READ = 256;
    const PERMISSION_OWNER_WRITE = 128;
    const PERMISSION_OWNER_EXECUTE = 64;

    const PERMISSION_GROUP_READ = 32;
    const PERMISSION_GROUP_WRITE = 16;
    const PERMISSION_GROUP_EXECUTE = 8;

    const PERMISSION_ALL_READ = 4;
    const PERMISSION_ALL_WRITE = 2;
    const PERMISSION_ALL_EXECUTE = 1;
    
    /**
     * Verify if the user can read
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can read?
     */
    public static function secTestRead($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_OWNER_READ:0) | 
                ($group?self::PERMISSION_GROUP_READ:0) | 
                (self::PERMISSION_ALL_READ);
        return (bool)($user & $key);
    }

    /**
     * Verify if the user can write
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can read?
     */
    public static function secTestWrite($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_OWNER_WRITE:0) | 
                ($group?self::PERMISSION_GROUP_WRITE:0) | 
                (self::PERMISSION_ALL_WRITE);
        return (bool)($user & $key);
    }
    
    /**
     * Verify if the user can execute
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can read?
     */
    public static function secTestExecute($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_OWNER_EXECUTE:0) | 
                ($group?self::PERMISSION_GROUP_EXECUTE:0) | 
                (self::PERMISSION_ALL_EXECUTE);
        return (bool)($user & $key);
    }
    
    public static function sessionStart() {
        session_start();
        if (!isset($_SESSION["started"])) {
            // We cache root and guest information
            $_SESSION["started"] = 1;
            $sql = "SELECT `node_user`.`id` as `iduser`, `node_group`.`id` as ".
                   "`idgroup` FROM `node_user` left join `node_group` on ".
                   "(`node_user`.`name` = `node_group`.`title`) where ".
                   "`node_user`.`name` = 'root'";
            $q = dbConn::get()->Execute($sql);
            if (!$q->EOF) {
                $_SESSION["user_root_id"] = $q->fields["iduser"];
                $_SESSION["group_root_id"] = $q->fields["idgroup"];
            }
            $sql = "SELECT `node_user`.`id` from `node_user` where `node_user`.`name` = 'guest'";
            $q = dbConn::get()->Execute($sql);
            $_SESSION["user_id"] = 0;
            if (!$q->EOF) {
                $_SESSION["user_guest_id"] = $q->fields["id"];
                $_SESSION["user_id"] = $q->fields["id"];
            }
            $_SESSION["is_loged"] = 0;
        }
    }
    
    /**
     * Identify user
     * @param string $user
     * @param string $pass
     */
    public static function logIn($user, $pass) {
        if (!isset($_SESSION["started"])) {
            self::sessionStart();
            $user = strtolower($user);
            if ($user == "root@localhost") {
                $user = ""; // Root can't start session
            }
            $node = driverCommand::run("getNodes", array(
                "nodetype" => "user",
                "where" => "`mail` = '$user' && `pass` = '$pass'",
            ));

            if (count($node) > 0) {
                $_SESSION["is_loged"] = 1;
                $_SESSION["user_id"] = array_keys($node)[0];
                $_SESSION["user_groups"] = implode(",", $node[$_SESSION["user_id"]]["groups"]);
            }
        }
    }
    
    /**
     * Close user session
     */
    public static function logOut() {
        session_destroy();
    }
    
    /**
     * Is user loged?
     * @return boolean
     */
    public static function isLoged() {
        return ($_SESSION["is_loged"] == 1);
    }
    
    /**
     * User ID
     * @return int
     */
    public static function getID() {
        return $_SESSION["user_id"];
    }
}