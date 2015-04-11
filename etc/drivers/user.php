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
    
    public static function secNodeToString($key) {
        $resp = "";
        
        $resp .= "Owner:[";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_OWNER_CREATE, "Create").", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_OWNER_READ, "Read").", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_OWNER_UPDATE, "Update").", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_OWNER_DEL, "Delete");
        $resp .= "] ";
        $resp .= " Group:[";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_GROUP_CREATE, "Create").", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_GROUP_READ, "Read").", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_GROUP_UPDATE, "Update").", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_GROUP_DEL, "Delete");
        $resp .= "] ";
        $resp .= " All:[";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_ALL_CREATE, "Create").", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_ALL_READ, "Read").", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_ALL_UPDATE, "Update").", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_ALL_DEL, "Delete");
        $resp .= "] ";
        return $resp;
    }
    
    /**
     * Format the label in green if value is true, otherway format in red.
     * @param boolean $value 
     * @param string $label
     * @return boolean
     */
    private static function secFormatString($value, $label) {
        $lab = ($value?"success":"danger");
        return "<span class=\"label label-$lab\">$label</span>";
    }
    
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
    
    /**
     * Load access data about a file
     * @param string $path File path
     * @return array FALSE if the file don't exist
     */
    public static function secFileGetAccess($path) {
        $resp = false;
        $fInfo = driverTools::pathInfo($path);
        $secFile = $fInfo["path"] ."/". $fInfo["name"] . ".sec";
        $fInfo = driverTools::pathInfo($secFile);
        if ($fInfo["exists"]) {
            $sec = file_get_contents($secFile);
            $sec = explode(":", $sec);
            if (count($sec) == 3) {
                $resp = array();
                $resp["flags"] = intval($sec[0]);
                $resp["owner"] = intval($sec[1]);
                $resp["group"] = intval($sec[2]);
            }
        }
        return $resp;
    }
    
    /**
     * Save access data about a file
     * @param string $path File path
     * @param integer $flags Access flags
     * @param integer $ownerID Owner user ID
     * @param integer $groupID Owner group ID
     * @return boolean ¿Changed?
     */
    public static function secFileSetAccess($path, $flags, $ownerID, $groupID) {
        $resp = false;
        $flags = intval($flags);
        $ownerID = intval($ownerID);
        $groupID = intval($groupID);
        if (is_int($flags) && is_int($ownerID) && is_int($groupID)) {
            $fInfo = driverTools::pathInfo($path);
            $secFile = $fInfo["path"] ."/". $fInfo["name"] . ".sec";
            $data = "$flags:$ownerID:$groupID";
            $sec = file_put_contents($secFile, $data);
            $resp = true;
        }
        return $resp;
    }
    
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
            $_SESSION["started"] = true;
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
        $sql = "select * from `node_user` where `mail` = '$user' && `pass` = '$pass'";
        $q = dbConn::Execute($sql);
        if (!$q->EOF) {
            $_SESSION["is_loged"] = 1;
            $_SESSION["user_id"] = $q->fields["id"];
            $sql = "SELECT * FROM `node_relation_user_groups_group` where `type1` = ".$q->fields["id"];
            $q = dbConn::Execute($sql);
            $_SESSION["user_groups"] = array();
            while(!$q->EOF) {
                $_SESSION["user_groups"][] = $q->fields["type2"];
                $q->MoveNext();
            }
        }
    }
    
    /**
     * Change active user, it suplant the identity of the new user.
     * @param boolean $get TRUE to get user, FALSE to exit.
     * @param integer $userID User ID to get.
     */
    public static function sudo($get = true, $userID = 0) {
        if ($get) {
            $usr = $userID;
            $grp = array(0);
            if ($userID != 0) {
                $resp = driverCommand::run("getNode", array(
                    "nodetype" => "user",
                    "node" => $userID,
                ));
                if (!isset($resp[$userID])) return;
                $usr = $userID;
                $grp = $resp[$userID]["groups"];
            }
            if (!self::isSudoed()) {
                $_SESSION["sudo_user_id"] = $_SESSION["user_id"];
                $_SESSION["sudo_user_groups"] = $_SESSION["user_groups"];
            }
            $_SESSION["user_id"] = $usr;
            $_SESSION["user_groups"] = $grp;
        } else {
            $_SESSION["user_id"] = $_SESSION["sudo_user_id"];
            $_SESSION["user_groups"] = $_SESSION["sudo_user_groups"];
            unset($_SESSION["sudo_user_id"]);
            unset($_SESSION["sudo_user_groups"]);
        }
    }
    
    public static function isSudoed() {
        return isset($_SESSION["sudo_user_id"]);
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
            return $name[$id]["title"];
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
if (isset($_POST["auth_token"])) {
    session_id($_POST["auth_token"]);
}
driverUser::sessionStart();