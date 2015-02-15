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
    
    /**
     * Start user session
     * @param string $user
     * @param string $pass
     */
    public function __construct($user = "", $pass = "") {
        session_start();
        if (!isset($_SESSION["user"])) {
            $_SESSION["user"] = "";
            $_SESSION["pass"] = "";
            $_SESSION["remember"] = false;
        } elseif ($user == "" && $pass == "") {
            $user = $_SESSION["user"];
            $pass = $_SESSION["pass"];
        }
        $this->logIn($user, $pass);
    }
    
    /**
     * Close user session
     */
    public function logOut() {
        session_destroy();
    }
    
    /**
     * Identify user
     * @param string $user
     * @param string $pass
     * @param boolean $remember
     */
    public function logIn($user, $pass, $remember = false) {
        $db = dbConn::get();
        $rs = $db->Execute("select * from `user` where mail = '$user'");
        if (!$rs->EOF) {
            if ($rs->fields["active"] != "1") {
                $this->logOut();
            } else {
                if ($rs->fields["mail"] == $user && $pass == $rs->fields["password"]) {
                    $this->isLoged = true;
                    $_SESSION["userID"] = $rs->fields["id"];
                    $_SESSION["user"] = $user;
                    $_SESSION["pass"] = $pass;
                    $_SESSION["remember"] = $remember;
                }
            }
        }
    }
    
    /**
     * Is user loged?
     * @return boolean
     */
    public function isLoged() {
        return $this->isLoged;
    }
    
    /**
     * User ID
     * @return int
     */
    public function getID() {
        if (!isset($_SESSION["userID"])) return 0;
        return $_SESSION["userID"];
    }
    
    /**
     * User name
     * @return string
     */
    public function getMyName() {
        return self::getName($this->getID());
    }
    
    public function getMyUser() {
        return $_SESSION["user"];
    }
    
    /**
     * Add a new user
     * @param string $login eMail
     * @param string $pass Real, no MD5, user password
     * @param string $name User name
     * @return int User ID of new user
     */
    public static function add($login, $pass, $name) {
        $db = dbConn::get();
        $sql = "insert into `user` set mail='$login', password='".md5($pass).
                "', `name`='$name', `created` = NOW()";
        $db->Execute($sql);
        return dbConn::lastID();
    }
    
    /**
     * Change user activation mark
     * @param int $usrID
     * @param boolean $value
     */
    public static function setActive($usrID, $value) {
        $db = dbConn::get();
        $sql = "update `user` set `active`='".($value?"1":"0")."' where id = $usrID";
        $db->Execute($sql);
    }
    
    /**
     * Is user activated?
     * @param int $usrID
     * @return boolean
     */
    public static function isActive($usrID) {
        $db = dbConn::get();
        $sql = "select `active` from `user` where id = $usrID";
        $rs = $db->Execute($sql);
        if (!$rs->EOF) {
            return ($rs->fields["active"] == "1");
        } else {
            return false;
        }
    }
    
    /**
     * Change the user name
     * @param int $usrID
     * @param string $value
     */
    public static function setName($usrID, $value) {
        $db = dbConn::get();
        $sql = "update `user` set `name`='".$value."' where id = $usrID";
        $db->Execute($sql);
    }
    
    /**
     * User name
     * @param int $usrID
     * @return boolean
     */
    public static function getName($usrID) {
        $db = dbConn::get();
        $sql = "select `name` from `user` where id = $usrID";
        $rs = $db->Execute($sql);
        if (!$rs->EOF) {
            return $rs->fields["name"];
        } else {
            return false;
        }
    }
}