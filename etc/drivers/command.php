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

/**
 * Command execution class and base class to commands
 */
class driverCommand extends driverHook {
    /**
     * Array to pass delayed information between commands.
     * @var array 
     */
    protected static $register = array();
    
    /**
     * Recordset of paths
     * @var adoRecorset 
     */
    protected static $paths = null;
    
    public static function getPaths() {
        if (driverCommand::$paths == null) {
            driverCommand::$paths = array();
                driverCommand::$paths = explode(";", driverConfig::getCFG()->getSection('[core]')->get('path'));
//            }
        }
        return driverCommand::$paths;
    }
    
    /**
     * Clear paths cache
     */
    public static function refreshPaths() {
        driverCommand::$paths = null;
    }
    
    /**
     * Return the module base path
     * @param string $mod Module slugname
     * @return string Module path
     */
    public static function getModPath($mod) {
        //TODO: Swap funcional code with the command.
        $resp = driverCommand::run('modGetPath', array(
            'name' => $mod
        ));
        return $resp['path'];
    }

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
        driverCommand::getPaths();
        $resp = array();
        foreach(driverCommand::$paths as $path) {
            if (is_file($path.$cmd.".php")) {
                $object = include($path.$cmd.".php");
                $canExe = $object->getAccess($path.$cmd.".php");
                if ($canExe) {
                    self::CallHook('before'.$cmd.'Hook', array(
                        'parameters' => &$params,
                    ));
                    $resp = $object->runMe($params);
                    self::CallHook('after'.$cmd.'Hook', array(
                        'parameters' => &$params,
                        'response' => &$resp,
                    ));
                    if (CMS_DEBUG && $debug) {
                        var_dump($cmd." < ".self::formatParamsArray($params)." => ".self::formatParamsArray($resp));
                        driverCommand::run("trace", array("command" => $cmd, "parameters" => $params, "return" => $resp), false);
                    }
                } else {
                    $resp = array(
                        "ok" => false,
                        "msg" => sprintf(__("You can't execute '%s'."), $cmd),
                    );
                }
                
                unset($params);
                return $resp;
            }
        }
        if (isset($_POST["interface"]) && $_POST["interface"] == "echoHtml") {
            echo self::getAlert(sprintf(__("Command '%s' not found"), $cmd));
        } else {
            return array("ok" => false, "msg" => sprintf(__("Command '%s' not found", $cmd)));
        }
    }
    
    public static function getAlert($msg) {
            $resp = <<<EOT
<div class="alert alert-danger" role="alert">
  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
  <span class="sr-only">Error:</span> $msg
</div>
EOT;
            self::CallHook('driverCommandGetAlertHook', array(
                'alert' => &$resp,
                'msg' => &$msg,
            ));
            return $resp;
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
            "description" => __("Command base class"), 
            "parameters" => array(), 
            "response" => array(),
            "type" => array(
                "parameters" => array(), 
                "response" => array(),
            )
        );
    }
    
    /**
     * Return TRUE if the access required to use the command is matched
     * @param string $path Path to the command
     * @param integer $defAccess Default access flags
     * @return booean 
     */
    public static function getAccess($path = "") {
        // Root have all the power !!
        if (driverUser::getID() == 0) return true;
        // Mortals don't have it... :S
        $accData = static::getAccessData($path);
        
        $usrGrps = driverUser::getGroupsID();
        return driverUser::secFileCanExecute($accData["flags"], 
                $accData["owner"] == driverUser::getID(), 
                array_search($accData["group"], $usrGrps) !== FALSE );
    }
    
    public static function getAccessData($path = "") {
        $resp = array(
            "flags" => static::getAccessFlags(),
            "owner" => 0,
            "group" => 0,
        );
        if ($path != "") {
            $aux = driverUser::secFileGetAccess($path);
            if ($aux !== false) {
                $resp = $aux;
            }
        }
        return $resp;
    }
    
    public static function getAccessFlags() {
        return PERMISSION_FILE_DEFAULT;
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

    public static function &getRegister($name) {
        if (!isset(self::$register[$name])) {
            self::$register[$name] = null;
        }
        return self::$register[$name];
    }
}
