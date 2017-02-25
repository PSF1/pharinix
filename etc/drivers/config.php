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

class driverConfig {
    /**
     * Cached configuration
     * @var driverConfigIni 
     */
    public static $cfg = null;
    
    /**
     * Pharinix meta data
     * @var stdClass
     */
    public static $coreMeta = null;
    
    /**
     * Return Pharinix meta data
     * @return \stdClass
     */
    public static function getMeta() {
        if (self::$coreMeta == null) {
            self::$coreMeta = json_decode(file_get_contents('etc/meta.json'));
        }
        return self::$coreMeta;
    }
    
    /**
     * Return configuration control
     * @return driverConfigIni
     */
    public static function getCFG() {
        if (driverConfig::$cfg == null) {
            driverConfig::$cfg = new driverConfigIni(driverConfig::getConfigFilePath());
            driverConfig::$cfg->parse();
            if (!defined('CMS_DEBUG')) {
                define('CMS_DEBUG', driverConfig::$cfg->getSection('[core]')->getAsBoolean('CMS_DEBUG'));
            }
            $baseURL = driverConfig::$cfg->getSection('[core]')->get('CMS_DEFAULT_URL_BASE');
            if ($baseURL == "auto" || $baseURL == "") {
                $baseURL = driverTools::base_url(true, true);
            } else if ($baseURL == "root") {
                $baseURL = driverTools::base_url();
            }
            if (!defined('CMS_DEFAULT_URL_BASE')) {
                define('CMS_DEFAULT_URL_BASE', $baseURL);
            }
            if (!defined('ADODB_PERF_NO_RUN_SQL')) {
                define('ADODB_PERF_NO_RUN_SQL', driverConfig::$cfg->getSection('[mysql]')->get('ADODB_PERF_NO_RUN_SQL'));
            }
        }
        return driverConfig::$cfg;
    }
    
    /**
     * Get a config value or default if not defined
     * 
     * @param string $section Section name with []
     * @param string $key Key name to read
     * @param string $default If key is not defined create it with this value
     * @param boolean $asBool Return a boolean value
     * @return string/boolean Key value or default one
     */
    public static function getCfgValue($section, $key, $default, $asBool = false) {
        $_section = self::getCFG()->getSection($section);
        if ($_section == null) {
            self::getCFG()->addSection($section);
            self::getCFG()->save();
            $_section = self::getCFG()->getSection($section);
        }
        if ($asBool) {
            $resp = $_section->getAsBoolean($key);
        } else {
            $resp = $_section->get($key);
        }
        if ($resp == null) {
            //$_section->set($key, $default);
            //self::getCFG()->save();
            $resp = $default;
        }
        return $resp;
    }
    
    /**
     * Return the best config file path to this call.
     * 
     * @return string Config file path to load
     */
    public static function getConfigFilePathDefault() {
        return "etc/".$_SERVER["HTTP_HOST"].".pharinix.config.php";
    }
    
    /**
     * Search the best config file to load.
     * 
     * @return string Config file path to load
     */
    public static function getConfigFilePath() {
        if (isset($_SERVER["HTTP_HOST"])) {
            if (is_file("etc/".$_SERVER["HTTP_HOST"].".pharinix.config.php")) {
                return "etc/".$_SERVER["HTTP_HOST"].".pharinix.config.php";
            }
        }
        if (!is_file("etc/pharinix.config.php")) {
            copy("etc/pharinix.config.DEFAULT.php", "etc/pharinix.config.php");
        }
        return "etc/pharinix.config.php";
    }
}

/**
 * Ini files parser
 */
class driverConfigIni {
    const LEX_MODE_COMMENT = 'comment';
    const LEX_MODE_STRING_VALUE = 'string_value';
    const LEX_MODE_STRING_SINGLE_VALUE = 'string_single_value';
    const LEX_MODE_VALUE = 'value';
    const LEX_MODE_SECTION_NAME = 'section_name';
    const LEX_MODE_KEY = 'key';
    
    /**
     * When this value is TRUE all the methods return false.
     * @var boolean 
     */
    protected $withError = false;
    protected $errorMsg = '';
    /**
     * File to read/write
     * @var string
     */
    protected $file = "";
    /**
     * INI raw content
     * @var string
     */
    protected $fileContent = "";
    protected $lexMaxInd = 0;
    protected $lexInd = 0;
    
    protected $sections;
    
    public function __construct($file) {
        if (!is_file($file)) {
            $this->withError = true;
            $this->errorMsg = 'File not exist.';
            return;
        }
        $this->file = $file;
        $this->fileContent = str_replace("\r","",file_get_contents($this->file));
        $this->lexInd = 0;
        $this->lexMaxInd = strlen($this->fileContent);
        //
        $this->sections = array(
            ' ' => new driverConfigIniSection(' '),
        );
    }
    
    public function getError() {
        return $this->errorMsg;
    }
    
    public function &getSections() {
        return $this->sections;
    }
    
    public function getSectionsNames() {
        return array_keys($this->sections);
    }
    
    /**
     * Return a section
     * @param string $name Section name. Start with '[' and end with ']'
     * @return driverConfigIniSection
     */
    public function getSection($name) {
        if (array_key_exists($name, $this->sections)) {
            return $this->sections[$name];
        } else {
            return null;
        }
    }
    
    public function delSection($name) {
        if (array_key_exists($name, $this->sections)) {
            unset($this->sections[$name]);
        }
    }
    
    public function save($file = '') {
        if ($file == '') {
            $file = $this->file;
        }
        $h = fopen($file, 'wb+');
        foreach($this->sections as $name => $section) {
            if ($name != ' ') {
                fwrite($h, "\n".$name."\n");
            }
            foreach($section->getLines() as $line) {
                if ($line instanceof driverConfigIniComment) {
                    if ($line->line != "\n") {
                        fwrite($h, $line->line."\n");
                    }
                } else if ($line instanceof driverConfigIniPair) {
                    fwrite($h, $line->key . " = " . $line->value."\n");
                }
            }
        }
        fclose($h);
        driverConfig::$cfg = null; // Force reload in the next usage.
    }
    
    public function addSection($name) {
        if (!isset($this->sections[$name])) {
            $activeSection = new driverConfigIniSection();
            $activeSection->setName($name);
            $this->sections[$name] = $activeSection;
        }
    }
    /**
     * Parse the file to memory
     */
    public function parse() {
        if ($this->withError) {
            return false;
        }
        $activeSection = $this->sections[' '];
        $waitingKey = true;
        $lastKey = '';
        $line = $this->lex();
        while ($line !== false){
            if (driverTools::str_start("[", $line)) {
                if (!array_key_exists($line, $this->sections)) {
                    $activeSection = new driverConfigIniSection();
                    $activeSection->setName($line);
                    $this->sections[$line] = $activeSection;
                }
                $activeSection = $this->sections[$line];
                $waitingKey = true;
            } else if (driverTools::str_start(";", $line) || $line == "\n") {
                $cm = new driverConfigIniComment();
                $cm->line = $line;
                $activeSection->add($cm);
                $waitingKey = true;
            } else {
                if ($waitingKey) {
                    if ($line == "=") {
                        $this->withError = true;
                        $this->errorMsg = '"=" not espected.';
                        return false;
                    } else {
                        $lastKey = $line;
                        $waitingKey = false;
                    }
                } else {
                    if ($line != '=') {
                        if ($lastKey == '') {
                            $this->withError = true;
                            $this->errorMsg = 'Value without key.';
                            return false;
                        } else {
                            $activeSection->set($lastKey, $line);
                            $lastKey = '';
                            $waitingKey = true;
                        }
                    }
                }
            }
            $line = $this->lex();
        };
    }
    
    /**
     * Return a token or false at the end
     * @return string or false
     */
    public function lex() {
        if ($this->withError) {
            return false;
        }
        $mode = self::LEX_MODE_KEY;
        $resp = '';
        while ($this->lexInd < $this->lexMaxInd) {
            $c = substr($this->fileContent, $this->lexInd, 1);
            switch($c) {
                case ';': // Comment
                    if ($mode == self::LEX_MODE_COMMENT || $mode == self::LEX_MODE_STRING_VALUE || $mode == self::LEX_MODE_STRING_SINGLE_VALUE) {
                        $resp .= $c;
                        continue;
                    }
                    $mode = self::LEX_MODE_COMMENT;
                    if ($resp != '') {
                        return $resp;
                    }
                    $resp .= ';';
                    break;
                case "'": // String value
                    if ($mode == self::LEX_MODE_STRING_SINGLE_VALUE) {
                        ++$this->lexInd;
                        return "'".$resp."'";
                    }
                    if ($mode != self::LEX_MODE_STRING_VALUE) {
                        $mode = self::LEX_MODE_STRING_SINGLE_VALUE;
                    } else {
                        $resp .= $c;
                    }
                    break;
                case '"': // String value
                    if ($mode == self::LEX_MODE_STRING_VALUE) {
                        ++$this->lexInd;
                        return '"'.$resp.'"';
                    }
                    if ($mode != self::LEX_MODE_STRING_SINGLE_VALUE) {
                        $mode = self::LEX_MODE_STRING_VALUE;
                    } else {
                        $resp .= $c;
                    }
                    break;
                case '['; // Section name
                    if ($mode == self::LEX_MODE_KEY) {
                        $mode = self::LEX_MODE_SECTION_NAME;
                    } else {
                        $resp .= $c;
                    }
                    break;
                case ']':
                    if ($mode == self::LEX_MODE_SECTION_NAME) {
                        ++$this->lexInd;
                        return "[$resp]";
                    }
                    $resp .= $c;
                    break;
                case "\n": // New line
                    if ($mode == self::LEX_MODE_COMMENT) {
                        return $resp;
                    } else if ($mode == self::LEX_MODE_SECTION_NAME) {
                        
                    } else if ($mode == self::LEX_MODE_STRING_VALUE || $mode == self::LEX_MODE_STRING_SINGLE_VALUE) {
                        $resp .= $c;
                    } else if ($mode == self::LEX_MODE_KEY) {
                        if ($resp != '') {
                            return $resp;
                        } else {
                            ++$this->lexInd;
                            return "\n";
                        }
                    }
                    break;
                case '=':
                    if ($mode != self::LEX_MODE_KEY) {
                        $resp .= $c;
                    } else {
                        if (trim($resp) == '') {
                            ++$this->lexInd;
                            return '=';
                        }
                        return $resp;
                    }
                    break;
                default:
                    if ($mode != self::LEX_MODE_KEY || $c != ' ') {
                        $resp .= $c;
                    }
                    break;
            }
            ++$this->lexInd;
        }
        if (trim($resp) != '') {
            return $resp;
        }
        return false;
    }
}

class driverConfigIniComment {
    public $line;
}

class driverConfigIniPair {
    public $key;
    public $value;
}

class driverConfigIniSection {
    protected $name;
    protected $lines = array();
    protected $keys = array();
    
    public function __construct($name = null) {
        $this->name = $name;
    }
    
    public function &getLines() {
        return $this->lines;
    }
    
    public function &getKeys() {
        return $this->keys;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Read a configuration key
     * @param string $key Key to read from the section
     * @return string
     */
    public function get($key) {
        if (array_key_exists($key, $this->keys)) {
            $val = trim($this->keys[$key]->value);
            if (driverTools::str_start("'", $val) || driverTools::str_start('"', $val)) {
                $val = substr($val, 1, strlen($val)-2);
            }
            return $val;
        } else {
            return null;
        }
    }
    
    public function getAsBoolean($key) {
        $val = strtolower(trim($this->get($key)));
        $resp = ($val == 'true' || $val == 'on' || $val == '1');
        return $resp;
    }
    
    public function set($key, $value) {
        if (array_key_exists($key, $this->keys)) {
            $this->keys[$key]->value = $value;
        } else {
            $pair = new driverConfigIniPair();
            $pair->key = $key;
            $pair->value = $value;
            $this->add($pair);
        }
    }
    
    public function add(&$line) {
        $this->lines[] = $line;
        if ($line instanceof driverConfigIniPair) {
            $this->keys[$line->key] = $line;
        }
    }
    
    public function del($line) {
        $max = count($this->lines);
        for($i = 0; $i < $max; ++$i) {
            $ref = $this->lines[$i];
            if ($line instanceof driverConfigIniComment && $ref instanceof driverConfigIniComment) {
                if ($ref->line == $line->line) {
                    unset($this->lines[$i]);
                    return;
                }
            } if ($line instanceof driverConfigIniPair && $ref instanceof driverConfigIniPair) {
                if ($ref->key == $line->key) {
                    unset($this->lines[$i]);
                    unset($this->keys[$line->key]);
                    return;
                }
            }
        }
    }
}

$meta = driverConfig::getMeta();
define("CMS_VERSION", $meta->meta->version);
@header("Author: Pedro Pelaez <aaaaa976@gmail.com>");
@header("X-Generator: Pharinix/".CMS_VERSION.' (https://github.com/PSF1/pharinix/)');
