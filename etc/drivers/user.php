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

// TODO: For multidomain session information must be necessary asociate session with domain.

 class driverUser {
    
// Nodes -----------------------------------------------------------------------
/*
     * O = Owner, G = Group, A = All
     * C: Create, R: Read, U: Update, D: Delete
     * Unused:OC.OR.OU.OD:GC.GR.GU.GD:AC.AR.AU.AD:
     * 00000000000000000000:0.0.0.0:0.0.0.0:0.0.0.0
     */
    const PERMISSION_NODE_OWNER_CREATE = 2048;
    const PERMISSION_NODE_OWNER_READ = 1024;
    const PERMISSION_NODE_OWNER_UPDATE = 512;
    const PERMISSION_NODE_OWNER_DEL = 256;

    const PERMISSION_NODE_GROUP_CREATE = 128;
    const PERMISSION_NODE_GROUP_READ = 64;
    const PERMISSION_NODE_GROUP_UPDATE = 32;
    const PERMISSION_NODE_GROUP_DEL = 16;

    const PERMISSION_NODE_ALL_CREATE = 8;
    const PERMISSION_NODE_ALL_READ = 4;
    const PERMISSION_NODE_ALL_UPDATE = 2;
    const PERMISSION_NODE_ALL_DEL = 1;
    
    /**
     * Verify if the user can create nodes
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can create?
     */
    public static function secNodeCanCreate($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_NODE_OWNER_CREATE:0) | 
                ($group?self::PERMISSION_NODE_GROUP_CREATE:0) | 
                (self::PERMISSION_NODE_ALL_CREATE);
        return (bool)($user & $key);
    }
    
    /**
     * Verify if the user can read nodes
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can read?
     */
    public static function secNodeCanRead($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_NODE_OWNER_READ:0) | 
                ($group?self::PERMISSION_NODE_GROUP_READ:0) | 
                (self::PERMISSION_NODE_ALL_READ);
        return (bool)($user & $key);
    }
    
    /**
     * Verify if the user can update nodes
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can update?
     */
    public static function secNodeCanUpdate($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_NODE_OWNER_UPDATE:0) | 
                ($group?self::PERMISSION_NODE_GROUP_UPDATE:0) | 
                (self::PERMISSION_NODE_ALL_UPDATE);
        return (bool)($user & $key);
    }
    
    /**
     * Verify if the user can delete nodes
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can delete?
     */
    public static function secNodeCanDelete($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_NODE_OWNER_DEL:0) | 
                ($group?self::PERMISSION_NODE_GROUP_DEL:0) | 
                (self::PERMISSION_NODE_ALL_DEL);
        return (bool)($user & $key);
    }
// End Nodes -------------------------------------------------------------------

// Files -----------------------------------------------------------------------
    /*
     * O = Owner, G = Group, A = All
     * R = Read, W = Write, X = Execute
     * Unused:OR.OW.OX:GR.GW.GX:AR.AW.AX:
     * 00000000000000000000000:0.0.0:0.0.0:0.0.0
     */
    const PERMISSION_FILE_OWNER_READ = 256;
    const PERMISSION_FILE_OWNER_WRITE = 128;
    const PERMISSION_FILE_OWNER_EXECUTE = 64;

    const PERMISSION_FILE_GROUP_READ = 32;
    const PERMISSION_FILE_GROUP_WRITE = 16;
    const PERMISSION_FILE_GROUP_EXECUTE = 8;

    const PERMISSION_FILE_ALL_READ = 4;
    const PERMISSION_FILE_ALL_WRITE = 2;
    const PERMISSION_FILE_ALL_EXECUTE = 1;
    
    public static function secFileToString($key) {
        $resp = "";
        
        $resp .= "Owner:";
        $resp .= $key & self::PERMISSION_FILE_OWNER_READ?"Read,":"";
        $resp .= $key & self::PERMISSION_FILE_OWNER_WRITE?"Write,":"";
        $resp .= $key & self::PERMISSION_FILE_OWNER_EXECUTE?"Execute":"";
        
        $resp .= " Group:";
        $resp .= $key & self::PERMISSION_FILE_GROUP_READ?"Read,":"";
        $resp .= $key & self::PERMISSION_FILE_GROUP_WRITE?"Write,":"";
        $resp .= $key & self::PERMISSION_FILE_GROUP_EXECUTE?"Execute":"";
        
        $resp .= " All:";
        $resp .= $key & self::PERMISSION_FILE_ALL_READ?"Read,":"";
        $resp .= $key & self::PERMISSION_FILE_ALL_WRITE?"Write,":"";
        $resp .= $key & self::PERMISSION_FILE_ALL_EXECUTE?"Execute":"";
        
        return $resp;
    }
    
    /**
     * Verify if the user can read
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can read?
     */
    public static function secFileCanRead($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_FILE_OWNER_READ:0) | 
                ($group?self::PERMISSION_FILE_GROUP_READ:0) | 
                (self::PERMISSION_FILE_ALL_READ);
        return (bool)($user & $key);
    }

    /**
     * Verify if the user can write
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can read?
     */
    public static function secFileCanWrite($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_FILE_OWNER_WRITE:0) | 
                ($group?self::PERMISSION_FILE_GROUP_WRITE:0) | 
                (self::PERMISSION_FILE_ALL_WRITE);
        return (bool)($user & $key);
    }
    
    /**
     * Verify if the user can execute
     * @param int $key Security integer to verify
     * @param bool $owner is owner?
     * @param type $group is group?
     * @return boolean I can read?
     */
    public static function secFileCanExecute($key = 0, $owner = false, $group = false) {
        $user = ($owner?self::PERMISSION_FILE_OWNER_EXECUTE:0) | 
                ($group?self::PERMISSION_FILE_GROUP_EXECUTE:0) | 
                (self::PERMISSION_FILE_ALL_EXECUTE);
        return (bool)($user & $key);
    }
// End Files -------------------------------------------------------------------
    
    public static function sessionStart() {
        @session_start();
        if (!isset($_SESSION["started"])) {
            // We cache root and guest information
            $_SESSION["user_root_id"] = 0;
            $_SESSION["group_root_id"] = 0;
            
            $sql = "SELECT `node_user`.`id` from `node_user` where `node_user`.`name` = 'guest'";
            $q = dbConn::Execute($sql);
            $_SESSION["user_id"] = -1;
            if (!$q->EOF) {
                $_SESSION["user_guest_id"] = $q->fields["id"];
                $_SESSION["user_id"] = $q->fields["id"];
            } else {
                // Without database connection
                $_SESSION["user_guest_id"] = -1;
                $_SESSION["user_id"] = -1;
            }
            $_SESSION["user_groups"] = array("");
            $_SESSION["is_loged"] = 0;
        }
    }
    
    /**
     * Identify user
     * @param string $user
     * @param string $pass md5 password
     */
    public static function logIn($user, $pass) {
        $user = strtolower($user);
        if ($user == "root@localhost") {
            $user = ""; // Root can't start session
        }
        $node = driverCommand::run("getNodes", array(
                    "nodetype" => "user",
                    "where" => "`mail` = '$user' && `pass` = '$pass'",
        ));

        if (!isset($node["ok"]) && count($node) > 0) {
            $_SESSION["is_loged"] = 1;
            $_SESSION["user_id"] = array_keys($node)[0];
            $_SESSION["user_groups"] = implode(",", $node[$_SESSION["user_id"]]["groups"]);
        }
    }
    
    public static function sudo($get = true) {
        if ($get) {
            $_SESSION["sudo_user_id"] = $_SESSION["user_id"];
            $_SESSION["sudo_user_groups"] = $_SESSION["user_groups"];
            $_SESSION["user_id"] = 0;
            $_SESSION["user_groups"] = array(0);
        } else {
            $_SESSION["user_id"] = $_SESSION["sudo_user_id"];
            $_SESSION["user_groups"] = $_SESSION["sudo_user_groups"];
            unset($_SESSION["sudo_user_id"]);
            unset($_SESSION["sudo_user_groups"]);
        }
    }

    /**
     * Close user session
     */
    public static function logOut() {
        session_destroy();
        driverUser::sessionStart();
    }
    
    /**
     * Is user loged?
     * @return boolean
     */
    public static function isLoged() {
        return ($_SESSION["is_loged"] == 1);
    }
    
    /**
     * User ID.<br>
     * ID of -1 is root user.
     * @return int
     */
    public static function getID() {
        if (!isset($_SESSION) || !isset($_SESSION["user_id"])) {
            return 0;
        }
        return $_SESSION["user_id"];
    }
    
    public static function getDefaultGroupID() {
        if (!isset($_SESSION) || !isset($_SESSION["user_groups"])) {
            return 0;
        }
        if (count($_SESSION["user_groups"]) == 0) {
            return 0;
        }
        return $_SESSION["user_groups"][0];
    }
    
    public static function getGroupsID() {
        if (!isset($_SESSION) || !isset($_SESSION["user_groups"])) {
            return array(0);
        }
        return $_SESSION["user_groups"];
    }
    
    public static function getUserName($id) {
        if ($id == 0) return "root";
        $name = driverCommand::run("getNode", array("nodetype" => "user", "node" => $id));
        if (!isset($name["ok"])) {
            return $name[$id]["name"];
        }
        return "unknow";
    }
    
    public static function getGroupName($id) {
        if ($id == 0) return "root";
        $name = driverCommand::run("getNode", array("nodetype" => "group", "node" => $id));
        if (!isset($name["ok"])) {
            return $name[$id]["name"];
        }
        return "unknow";
    }
}

define('PERMISSION_NODE_DEFAULT', (driverUser::PERMISSION_NODE_OWNER_CREATE | 
                          driverUser::PERMISSION_NODE_OWNER_DEL | 
                          driverUser::PERMISSION_NODE_OWNER_READ |
                          driverUser::PERMISSION_NODE_OWNER_UPDATE |
                          driverUser::PERMISSION_NODE_GROUP_READ));
define('PERMISSION_FILE_DEFAULT', (driverUser::PERMISSION_FILE_OWNER_READ | 
                          driverUser::PERMISSION_FILE_OWNER_WRITE | 
                          driverUser::PERMISSION_FILE_OWNER_EXECUTE |
                          driverUser::PERMISSION_FILE_GROUP_READ));
driverUser::sessionStart();