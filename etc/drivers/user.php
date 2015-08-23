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

// TODO: For multidomain session information must be necessary asociate session with domain.

use Gettext\Translator;

 class driverUser {
     /**
      * sudoers group ID
      * @var string 
      */
    protected static $sudoersID = null;
    
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
        
        $resp .= __("Owner").":[";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_OWNER_CREATE, __("Create")).", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_OWNER_READ, __("Read")).", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_OWNER_UPDATE, __("Update")).", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_OWNER_DEL, __("Delete"));
        $resp .= "] ";
        $resp .= " ".__("Group").":[";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_GROUP_CREATE, __("Create")).", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_GROUP_READ, __("Read")).", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_GROUP_UPDATE, __("Update")).", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_GROUP_DEL, __("Delete"));
        $resp .= "] ";
        $resp .= " ".__("All").":[";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_ALL_CREATE, __("Create")).", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_ALL_READ, __("Read")).", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_ALL_UPDATE, __("Update")).", ";
        $resp .= self::secFormatString($key & self::PERMISSION_NODE_ALL_DEL, __("Delete"));
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
        if (driverUser::isSudoed()) return true;
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
        if (driverUser::isSudoed()) return true;
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
        if (driverUser::isSudoed()) return true;
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
        if (driverUser::isSudoed()) return true;
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
            $sec = file_get_contents($fInfo["path"].DIRECTORY_SEPARATOR.$fInfo["filename"]);
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
    
    public static function secFileToString($key, $html = false) {
        $resp = "";
        
        if (!$html) {
            $resp .= __("Owner").":";
            $resp .= $key & self::PERMISSION_FILE_OWNER_READ?__("Read").",":"";
            $resp .= $key & self::PERMISSION_FILE_OWNER_WRITE?__("Write").",":"";
            $resp .= $key & self::PERMISSION_FILE_OWNER_EXECUTE?__("Execute"):"";

            $resp .= " ".__("Group").":";
            $resp .= $key & self::PERMISSION_FILE_GROUP_READ?__("Read").",":"";
            $resp .= $key & self::PERMISSION_FILE_GROUP_WRITE?__("Write").",":"";
            $resp .= $key & self::PERMISSION_FILE_GROUP_EXECUTE?__("Execute"):"";

            $resp .= " ".__("All").":";
            $resp .= $key & self::PERMISSION_FILE_ALL_READ?__("Read").",":"";
            $resp .= $key & self::PERMISSION_FILE_ALL_WRITE?__("Write").",":"";
            $resp .= $key & self::PERMISSION_FILE_ALL_EXECUTE?__("Execute"):"";
        } else {
            $resp .= __("Owner").":";
            $resp .= self::secFormatString($key & self::PERMISSION_FILE_OWNER_READ, __("Read"));
            $resp .= self::secFormatString($key & self::PERMISSION_FILE_OWNER_WRITE, __("Write"));
            $resp .= self::secFormatString($key & self::PERMISSION_FILE_OWNER_EXECUTE, __("Execute"));

            $resp .= " ".__("Group").":";
            $resp .= self::secFormatString($key & self::PERMISSION_FILE_GROUP_READ, __("Read"));
            $resp .= self::secFormatString($key & self::PERMISSION_FILE_GROUP_WRITE, __("Write"));
            $resp .= self::secFormatString($key & self::PERMISSION_FILE_GROUP_EXECUTE, __("Execute"));

            $resp .= " ".__("All").":";
            $resp .= self::secFormatString($key & self::PERMISSION_FILE_ALL_READ, __("Read"));
            $resp .= self::secFormatString($key & self::PERMISSION_FILE_ALL_WRITE, __("Write"));
            $resp .= self::secFormatString($key & self::PERMISSION_FILE_ALL_EXECUTE, __("Execute"));
        }
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
            
            $sql = "SELECT `id` from `node_user` where `name` = 'guest'";
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
            self::getLangOfUser();
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
     * Change active user, it supplant the identity of the new user.
     * @param boolean $get TRUE to get user, FALSE to exit.
     * @param integer $userID User ID to get.
     */
    public static function sudo($get = true, $userID = 0) {
        if ($get) {
            $usr = $userID;
            $grp = array(0);
            if ($userID != 0) {
                // Is a valid user
                $sql = "select `id` from `node_user` where `id` = ".$userID;
                $q = dbConn::Execute($sql);
                if ($q->EOF) return;
                $usr = $userID;
                
                $relTable = '`node_relation_user_groups_group`';
                $sql = "select `type2` from $relTable where `type1` = $userID";
                $q = dbConn::Execute($sql);
                $grp = array();
                while (!$q->EOF) {
                    $grp[] = $q->fields["type2"];
                    $q->MoveNext();
                }
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
    
    /**
     * Have the user this group?
     * @param string $grp Group name
     * @return boolean
     */
    public static function haveGroup($grp) {
        $sid = self::getGroupID($grp);
        return array_search($sid, $_SESSION["user_groups"]) !== false;
    }
    /**
     * Group ID
     * @param string $grp Group name
     * @return integer FALSE if not found.
     */
    public static function getGroupID($grp) {
        $sql = "select `id` from `node_group` where `title` = '$grp'";
        $q = dbConn::Execute($sql);
        if (!$q->EOF) {
            return $q->fields["id"];
        } else {
            return false;
        }
    }
    
    /**
     * Have user sudoers group?
     * @return boolean
     */
    public static function haveSudoersGroup() {
        $sid = self::getSudoersGroupID();
        return array_search($sid, $_SESSION["user_groups"]) !== false;
    }
    /**
     * Sudoers group ID
     * @return boolean
     */
    public static function getSudoersGroupID() {
        if (self::$sudoersID != null) return self::$sudoersID;
        
        $sql = "select `id` from `node_group` where `title` = 'sudoers'";
        $q = dbConn::Execute($sql);
        if (!$q->EOF) {
            self::$sudoersID = $q->fields["id"];
            return $q->fields["id"];
        } else {
            return false;
        }
    }
    /**
     * Have the user root powers?
     * @return boolean
     */
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
     * @param boolean $real If true always it return real ID, if false and the user is sudoed return 0. Default false.
     * @return int
     */
    public static function getID($real = false) {
        if (!isset($_SESSION) || !isset($_SESSION["user_id"])) {
            return 0;
        }
        if ($real && self::isSudoed()) {
            return $_SESSION["sudo_user_id"];
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
        $sql = "select `name` from `node_user` where `id` = ".$id;
        $q = dbConn::Execute($sql);
        if ($q->EOF) {
            return "unknown";
        } else {
            return $q->fields["name"];
        }
    }
    
    /**
     * Get list of languages listed by the HTTP/S client.
     * @return array List of prefered languages set by browser, or client. 
     */
    public static function getLangOfClient() {
        //_SERVER["HTTP_ACCEPT_LANGUAGE"]	es-ES,es;q=0.8,en;q=0.6
        $resp = array();
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            $langs = explode(";", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            foreach($langs as $lang) {
                $lang = explode(",", $lang);
                foreach ($lang as $sublang) {
                    $resp[] = str_replace('-', '_', $sublang);
                }
            }
        }
        $resp[] = 'en';
        return $resp;
    }
    
    /**
     * Get list of prefered languages
     * @return array List of prefered languages set by user
     */
    public static function getLangOfUser() {
        $resp = array();
        $sql = 'select `language` from `node_user` where `id` = '.self::getID(true);
        $q = dbConn::Execute($sql);
        if (!$q->EOF && $q->fields['language'] != '') {
            $resp[] = $q->fields['language'];
        } else if (!isset($_SESSION)) {
            $resp[] = 'en';
        } else {
            // Select language of the client
            $resp = self::getLangOfClient();
        }
        if (isset($_SESSION)) $_SESSION['lang'] = $resp;
        return $resp;
    }
    
    public static function loadTranslations() {
        $t = new Translator();
        $reqs = self::getLangOfUser();
        $po = null;
        // Load core translations
        foreach($reqs as $req) {
            if ($req == 'en') {
                break; // English is the default language, it don't need load translations
            }
            if (is_file('etc/i18n/'.$req.'.mo')) {
                $po = Gettext\Extractors\Mo::fromFile('etc/i18n/'.$req.'.mo');
                break;
            }
            if (is_file('etc/i18n/'.$req.'.po')) {
                $po = Gettext\Extractors\Po::fromFile('etc/i18n/'.$req.'.po');
                break;
            }
        }
        // Load modules translations
        $sql = "select `path` from `node_modules`";
        $q = dbConn::Execute($sql);
        while (!$q->EOF) {
            foreach($reqs as $req) {
                if ($req == 'en') {
                    break; // English is the default language, it don't need load translations
                }
                if (is_file($q->fields['path'].'i18n/'.$req.'.mo')) {
                    $po2 = Gettext\Extractors\Mo::fromFile($q->fields['path'].'i18n/'.$req.'.mo');
                    $po->mergeWith($po2);
                    break;
                }
                if (is_file($q->fields['path'].'i18n/'.$req.'.po')) {
                    $po2 = Gettext\Extractors\Po::fromFile($q->fields['path'].'i18n/'.$req.'.po');
                    $po->mergeWith($po2);
                    break;
                }
            }
            $q->MoveNext();
        }
        if ($po != null) $t->loadTranslations($po);
        Translator::initGettextFunctions($t);
    }
    
    /**
     * Get user ID searching by mail.
     * @param string $mail User mail to find
     * @return string|boolean False if not found.
     */
    public static function getUserIDByMail($mail) {
        $sql = "select `id` from `node_user` where `mail` = '".$mail."'";
        $q = dbConn::Execute($sql);
        if ($q->EOF) {
            return false;
        } else {
            return $q->fields["id"];
        }
    }
    
    public static function getGroupName($id) {
        if ($id == 0) return "root";
        $sql = "select `title` from `node_group` where `id` = ".$id;
        $q = dbConn::Execute($sql);
        if ($q->EOF) {
            return "unknown";
        } else {
            return $q->fields["title"];
        }
    }
}

define('PERMISSION_NODE_DEFAULT', (driverUser::PERMISSION_NODE_OWNER_CREATE | 
                          driverUser::PERMISSION_NODE_OWNER_DEL | 
                          driverUser::PERMISSION_NODE_OWNER_READ |
                          driverUser::PERMISSION_NODE_OWNER_UPDATE |
                          driverUser::PERMISSION_NODE_GROUP_READ));
define('PERMISSION_NODE_OWNER_ALL', (driverUser::PERMISSION_NODE_OWNER_CREATE | 
                          driverUser::PERMISSION_NODE_OWNER_DEL | 
                          driverUser::PERMISSION_NODE_OWNER_READ |
                          driverUser::PERMISSION_NODE_OWNER_UPDATE));
define('PERMISSION_NODE_GROUP_ALL', (driverUser::PERMISSION_NODE_GROUP_CREATE | 
                          driverUser::PERMISSION_NODE_GROUP_DEL | 
                          driverUser::PERMISSION_NODE_GROUP_READ |
                          driverUser::PERMISSION_NODE_GROUP_UPDATE));
define('PERMISSION_NODE_ALL_ALL', (driverUser::PERMISSION_NODE_ALL_CREATE | 
                          driverUser::PERMISSION_NODE_ALL_DEL | 
                          driverUser::PERMISSION_NODE_ALL_READ |
                          driverUser::PERMISSION_NODE_ALL_UPDATE));
define('PERMISSION_FILE_DEFAULT', (driverUser::PERMISSION_FILE_OWNER_READ | 
                          driverUser::PERMISSION_FILE_OWNER_WRITE | 
                          driverUser::PERMISSION_FILE_OWNER_EXECUTE |
                          driverUser::PERMISSION_FILE_GROUP_READ));
if (isset($_POST["auth_token"])) {
    session_id($_POST["auth_token"]);
}
driverUser::sessionStart();