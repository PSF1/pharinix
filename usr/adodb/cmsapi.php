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
        $cfg = driverConfig::getCFG();
        if (!$cfg->getSection('[safe_mode]')->getAsBoolean('active')) {
            self::get();
            if (self::$conn != null && self::$conn->IsConnected()) {
                $resp = self::$connected;
            }
        }
        return $resp;
    }
    
    public static function Execute($sql, $cached = null) {
        // TODO: Compatibility with other database engines.
        $resp = null;
        if (self::haveConnection()) {
            if ($cached == null) {
                $cached = driverConfig::getCFG()->getSection('[mysql]')->getAsBoolean('ADODB_DEFAULT_CACHE');
            }
            if (!$cached) {
                $resp = self::get()->Execute($sql);
            } else {
                $resp = self::get()->cacheExecute(intval(driverConfig::getCFG()->getSection('[mysql]')->get('ADODB_MEMCACHE_LIFE')), $sql);
            }
        } else {
            $resp = new fakeRecordset($sql);
        }
        return $resp;
    }
    
    public static function get() {
        $cfg = driverConfig::getCFG();
        if (self::$conn == null) {
            self::$conn = NewADOConnection('mysqli');
            if($cfg->getSection('[mysql]')->getAsBoolean('ADODB_MEMCACHE_USAGE')) {
                $mcHosts = explode(',', $cfg->getSection('[mysql]')->get('ADODB_MEMCACHE_HOSTS'));
                $memCacheHost = array();
                foreach($mcHosts as $mcHost) {
                    $memCacheHost[] = trim($mcHost);
                }
                if (count($memCacheHost) > 0) {
                    self::$conn->memCache = true;
                    self::$conn->memCacheHost = $memCacheHost;
                    self::$conn->memCachePort = $cfg->getSection('[mysql]')->get('ADODB_MEMCACHE_PORT');
                    self::$conn->memCacheCompress = $cfg->getSection('[mysql]')->getAsBoolean('ADODB_MEMCACHE_COMPRESS');
                }
            }
            try {
                @self::$conn->Connect(
                        $cfg->getSection('[mysql]')->get('MYSQL_HOST'), 
                        $cfg->getSection('[mysql]')->get('MYSQL_USER'), 
                        $cfg->getSection('[mysql]')->get('MYSQL_PASS'), 
                        $cfg->getSection('[mysql]')->get('MYSQL_DBNAME')
                    );
                self::$connected = self::$conn->isConnected();
            } catch (Exception $exc) {
                self::$connected = false;
//                echo $exc->getTraceAsString();
            }
            if (CMS_DEBUG) {
                self::$perf =& NewPerfMonitor(self::$conn);
            }
            self::$conn->LogSQL($cfg->getSection('[mysql]')->getAsBoolean('CMS_DEBUG_LOG_SQL'));
        }
        if (self::$connected) {
            $charset = $cfg->getSection('[mysql]')->get('charset');
            if ($charset != null) {
                self::$conn->EXECUTE("set names '".$charset."'", false);
            }
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
        if (self::haveConnection()) {
            $sql = "SELECT LAST_INSERT_ID()";
            $rs = dbConn::Execute($sql, false);
            return $rs->fields[0];
        } else {
            return 0;
        }
    }

}

class fakeRecordset {
    /**
     *
     * @var driverConfigIni 
     */
    protected static $ini = null;
    public $EOF = true;
    protected $index = 0;
    protected $count = 0;
    public $records = array();
    public $fields = array();
    
    public function __construct($sql) {
        if (self::$ini == null) {
            self::$ini = new driverConfigIni('etc/templates/pharinix/default_recordsets.ini');
            self::$ini->parse();
        }
        $tables = json_decode(self::$ini->getSection('[recordset]')->get('tables'));
        $this->EOF = true;
        $this->index = 0;
        $this->count = 0;
        $this->records = array();
        foreach($tables as $table) {
            if (strpos($sql, $table) !== false ) {
                $data = self::$ini->getSection('[recordset]')->get('table_'.$table);
                if ($table == 'node_user') {
                    $cfg = driverConfig::getCFG();
                    $data = str_replace(
                            'fake@localhost', 
                            $cfg->getSection('[safe_mode]')->get('user'), 
                            $data);
                    $data = str_replace(
                            '[{ms5_pass}]', 
                            driverUser::passwordObfuscation($cfg->getSection('[safe_mode]')->get('pass')), 
                            $data);
                }
                if ($data != null) {
                    $data = json_decode(utf8_encode($data));
                    $this->records = $data->recordset;
                    include_once 'usr/php_sql_parser/PHPSQLParser.php';
                    $sqlP = new PHPSQLParser($sql);
                    if (count($this->records) > 0) {
                        $where = $this->getWhere($sqlP, $sql);
                        if ($where != '') {
                            $aux = array();
                            foreach ($this->records as $record) {
                                $accept = false;
                                $jrec = json_encode($record);
                                $accept = (eval("\$record = json_decode('$jrec'); return $where;"));
                                if ($accept) {
                                    $aux[] = $record;
                                }
                            }
                            $this->records = $aux;
                        }
                    }
                    $this->index = 0;
                    $this->count = count($this->records);
                    $this->EOF = ($this->count == 0);
                    if (isset($sqlP->parsed['ORDER'])) {
                        if (isset($sqlP->parsed['ORDER']['direction']) && 
                            $sqlP->parsed['ORDER']['direction']=='ASC') {
                            usort($this->records, 'fakeRecordset::compASC');
                        } else {
                            usort($this->records, 'fakeRecordset::compDESC');
                        }
                    }
                    $this->setRecord();
                }
                break;
            }
        }
    }
    
    public static function compASC($a, $b) {
        return 1;
    }
    
    public static function compDESC($a, $b) {
        return -1;
    }
    
    public function MoveNext() {
        if ($this->index + 1 < $this->count) {
            ++$this->index;
        } else {
            $this->EOF = true;
        }
        $this->setRecord();
    }
    
    public function MoveFirst() {
        $this->index = 0;
        $this->setRecord();
    }
    
    protected function setRecord() {
        $this->fields = array();
        if (!$this->EOF) {
            foreach ($this->records[$this->index] as $key => $value) {
                $this->fields[$key] = $value;
            }
        }
    }
    
    protected function getWhere($sqlP, $sql) {
        $where = "";
        if (!isset($sqlP->parsed['WHERE'])) {
            return '';
        }
        foreach($sqlP->parsed['WHERE'] as $cond) {
            $where .= $this->parseWhere($cond)." ";
        }
        $where = str_replace(" and ", " && ", $where);
        $where = str_replace(" AND ", " && ", $where);
        $where = str_replace(" or ", " || ", $where);
        $where = str_replace(" OR ", " || ", $where);
        $where = str_replace("`", "", $where);
        return $where;
    }
    
    protected function parseWhere($whereP) {
        if ($whereP['sub_tree'] !== false) {
            $where = "";
            foreach($whereP['sub_tree'] as $cond) {
                $where .= $this->parseWhere($cond)." ";
            }
            return "($where)";
        } else {
            switch ($whereP['expr_type']) {
                case 'colref':
                    return '$record->'.$whereP['no_quotes'];
                case 'operator':
                    return str_replace("=", "==", $whereP['base_expr']);
                    return str_replace("<>", "!=", $whereP['base_expr']);
                case 'const':
                    return $whereP['base_expr'];
            }
        }
    }
}
