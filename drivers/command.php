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

/**
 * Command execution class and base class to commands
 */
class driverCommand {
    /**
     * Recordset of paths
     * @var adoRecorset 
     */
    private static $paths = null;
    
    /**
     * Execute a command
     * @param string $cmd
     * @param array $params
     */
    public static function run($cmd, $params = array(), $debug = true) {
        global $output;
        $cmd = str_replace("/", "", $cmd);
        $cmd = str_replace("\\", "", $cmd);
        $cmd = str_replace(".", "", $cmd);
        if (driverCommand::$paths == null) {
            $sql = "SELECT * FROM `bin-path`";
            driverCommand::$paths = dbConn::get()->Execute($sql);
        }
        driverCommand::$paths->MoveFirst();
        $resp = array();
        while(!driverCommand::$paths->EOF) {
            $path = driverCommand::$paths->fields["path"];
            if (is_file($path.$cmd.".php")) {
                $object = include($path.$cmd.".php");
                $resp = $object->runMe($params);
                if (CMS_DEBUG && $debug) {
                    var_dump($cmd." < ".self::formatParamsArray($params)." => ".self::formatParamsArray($resp));
                    driverCommand::run("trace", array("command" => $cmd, "parameters" => $params, "return" => $resp), false);
                }
                unset($params);
                return $resp;
            }
            driverCommand::$paths->MoveNext();
        }
        throw new Exception("Command '{$cmd}' not found");
    }
    
    /**
     * Each command must override it
     * @param array $params Parameters
     * @param boolean $debug Log in trace
     * @return array Response
     */
    public static function runMe(&$params, $debug = true) {
        // 
    }
    
    /**
     * Return command help info.
     * @return array array("parameters" => array("arg1" => "value"), "description" => "Help text", "response" => array("resp1" => "value"))
     */
    public static function getHelp() {
        return array(
            "description" => "Command base class", 
            "parameters" => array(), 
            "response" => array()
        );
    }
    
    /**
     * print_r wrapper, translate bool values to "TRUE" or "FALSE" string.
     * @param array $arr
     * @return string
     */
    public static function formatParamsArray($arr) {
        if (!is_array($arr)) return $arr;
        $resp = array();
        foreach($arr as $key => $value) {
            if (is_bool($value)) $value = ($value?"True":"False");
            $resp[$key] = $value;
        }
        return print_r($resp, 1);
    }

    /**
     * Get parameters from POST
     * @return array
     */
    public static function getPOSTParams($in) {
        $params = array();
        foreach ($in as $key => $value) {
            if (is_array($value)) {
                $params[$key] = self::getPOSTParams($value);
            } else {
                $params[$key] = @strip_tags($value);
                $params[$key] = str_replace("]]>", "]--]>", $value);
            }
        }
        return $params;
    }

}