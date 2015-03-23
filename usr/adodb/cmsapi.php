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

if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

include_once("usr/adodb/adodb-exceptions.inc.php");
include_once("usr/adodb/adodb.inc.php");

class dbConn {
    /**
     * Database connection
     * @var ADOConnection 
     */
    public static $conn = null;
    /**
     * We connection?
     * @var bool 
     */
    public static $connected = false;
    /**
     * ADOdb monitor
     * @var type 
     */
    public static $perf = null;
    /**
     * Stop connection with database with test purpose
     * @var bool
     */
    public static $lockConnection = false;

    /**
     * We connection?
     * @return boolean We connection?
     */
    public static function haveConnection() {
        if (self::$lockConnection) return false;
        $resp = false;
        self::get();
        if (self::$conn != null) {
            $resp = self::$connected;
        }
        return $resp;
    }
    
    public static function Execute($sql) {
        // TODO: Introduce cache
        // TODO: Compatibility with other database engines.
        $resp = null;
        if (self::haveConnection()) {
            $resp = self::get()->Execute($sql);
        } else {
            $resp = new fakeRecordset();
        }
        return $resp;
    }
    
    public static function get() {
        if (self::$conn == null) {
            self::$conn = NewADOConnection('mysql');
            try {
                self::$conn->Connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DBNAME);
                self::$connected = true;
            } catch (Exception $exc) {
                self::$connected = false;
//                echo $exc->getTraceAsString();
            }
            if (CMS_DEBUG) {
                self::$perf =& NewPerfMonitor(self::$conn);
            }
            self::$conn->LogSQL(CMS_DEBUG_LOG_SQL);
        }
        return self::$conn;
    }
    
    public static function getPerf() {
        return self::$perf;
    }

    public static function qstr($str) {
        dbConn::get();
        return self::qstrEXT($str);
    }

    /**
     * Correctly quotes a string so that all strings are escaped. We prefix and append
     * to the string single-quotes.
     * An example is  $db->qstr("Don't bother",magic_quotes_runtime());
     *
     * @param s			the string to quote
     * @param [magic_quotes]	if $s is GET/POST var, set to get_magic_quotes_gpc().
     * 				This undoes the stupidity of magic quotes for GPC.
     *
     * @return  quoted string to be sent back to database
     */
    private static function qstrEXT($s, $magic_quotes = false) {
        if (!$magic_quotes) {
            if (self::$conn->replaceQuote[0] == '\\') {
                // only since php 4.0.5
                $s = adodb_str_replace(array('\\', "\0"), array('\\\\', "\\\0"), $s);
                //$s = str_replace("\0","\\\0", str_replace('\\','\\\\',$s));
            }
            return str_replace("'", self::$conn->replaceQuote, $s);
        }

        // undo magic quotes for "
        $s = str_replace('\\"', '"', $s);

        if (self::$conn->replaceQuote == "\\'" || ini_get('magic_quotes_sybase'))  // ' already quoted, no need to change anything
            return $s;
        else {// change \' to '' for sybase/mssql
            $s = str_replace('\\\\', '\\', $s);
            return str_replace("\\'", self::$conn->replaceQuote, $s);
        }
    }

    public static function lastID() {
        $db = dbConn::get();
        $sql = "SELECT LAST_INSERT_ID()";
        $rs = $db->Execute($sql);
        return $rs->fields[0];
    }

}

class fakeRecordset {
    public $EOF = true;
    
    public function MoveFirst() {
        
    }
}