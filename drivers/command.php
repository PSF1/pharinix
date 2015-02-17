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

class driverCommand {
    
    /**
     * Execute a command
     * @param string $cmd
     * @param array $params
     */
    public static function run($cmd, $params = array(), $debug = true) {
        global $output;
        if (CMS_DEBUG && $debug) {
            echo "<h6><span class=\"label label-warning\">$cmd ".print_r($params,1)."</span></h6>";
            driverCommand::run("trace", array("command" => $cmd, "parameters" => $params), false);
        }
        $cmd = str_replace("/", "", $cmd);
        $cmd = str_replace("\\", "", $cmd);
        $cmd = str_replace(".", "", $cmd);
        $sql = "SELECT * FROM `bin-path`";
        $q = dbConn::get()->Execute($sql);
        $resp = array();
        while(!$q->EOF && !$executed) {
            $path = $q->fields["path"];
            if (is_file($path.$cmd.".php")) {
                $resp = include($path.$cmd.".php");
                return $resp;
            }
            $q->MoveNext();
        }
        throw new Exception("Command '{$cmd}' not found");
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