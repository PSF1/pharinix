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

if (!class_exists("commandBatch")) {
    class commandBatch extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "starter" => array(),
                "commands" => array(),
                "echoed" => "",
            ), $params);
            
            $lastCommand = "";
            $echoed = $params["echoed"];
            try {
                $output = is_array($params["starter"])?$params["starter"]:array();
                $cnt = 0; // Executed commands counter
                foreach($params["commands"] as $line) {
                    $cmd = null;
                    $params = null;
                    // We need a method to list commands with duplicates
                    foreach($line as $acmd => $apar) {
                        $cmd = $acmd;
                        $params = $apar;
                        break;
                    }
                    $lastCommand = "'$cmd' => '$params'";
                    if (self::isMeta($cmd)) {
                        if (self::isValidMeta($cmd)) {
                            switch ($cmd) { // TODO: Add more meta commands
                                case "#clean":
                                    $output = array();
                                break;
                            }
                        } else {
                            throw new Exception(sprintf(__("Meta '%s' is unknowed."), $cmd));
                        }
                    } else {
                        $aux = array();
                        parse_str($params, $aux);
                        $params = array_merge($output, $aux);
                        unset($aux);
                        $out = driverCommand::run($cmd, $params);
                        if (is_array($out)) {
                            $output = array_merge($output, $out);
                        }
                    }
                    ++$cnt;
                }
            } catch (Exception $exc) {
                $output["ok"] = false;
                $output["msg"] = $exc->getMessage();
                $output["error"] = "cmd $cnt - ".$lastCommand;
            }
            driverCommand::run("captureEndAll");
            if (!is_string($echoed)) {
                $echoed = "";
            }
            if ($echoed != "") {
                driverCommand::run($echoed, $output);
            } else {
                return $output;
            }
         }

        private static function isMeta($cmd) {
            $test = strpos($cmd, "#");
            if ($test === false) return $test;
            return ($test == 0);
        }
        
        private static function isValidMeta($cmd) {
            switch ($cmd) {
                case "#clean":
                    return true;
            }
            return false;
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getHelp() {
            return array(
                "description" => __("Execute a serial of commands. The output of a command will be the input parameters of the next command. Always call to captureEndAll at the end."), 
                "parameters" => array(
                    "starter" => __("Array of parameters to merge at the start."),
                    "commands" => __("Array of commands and default parameters. This parameters, if any, will be merged with de output of previous command, with priority to this, and passed how combined parameters. Ex, [['nothing' => 'ignoredparam1=A&ignoredparam2=B'], ['nothing' => 'ignoredparam1=A&ignoredparam2=B'], ...]. If you pass how command '#clean' then batch clear the merged output."),
                    "echoed" => __("If is empty or not define the output will out in response, else will be passed how parameters to the command in this parameter. Ex: 'echoed' => 'echoJson' will echo to the browser a json representation of the response."),
                ), 
                "response" => array(
                    "any" => __("The final response of the batch."),
                    "ok" => __("If error is set to FALSE, else will be unset."),
                    "msg" => __("If error is set to the error message, else will be unset. "),
                    "error" => __("If error is set to a string with the command and her input parameters, else will be unset."),
                ),
                "type" => array(
                    "parameters" => array(
                        "starter" => "array",
                        "commands" => "array",
                        "echoed" => "string",
                    ), 
                    "response" => array(
                        "any" => "args",
                        "ok" => "boolean",
                        "msg" => "string",
                        "error" => "string",
                    ),
                )
            );
        }
    }
}
return new commandBatch();