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

/*
 * Capture final resources consumed
 */
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandUsageEnd")) {
    class commandUsageEnd extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            if (CMS_DEBUG) {
                global $output;
                $output["used_ram"]["end"] = memory_get_usage();
                $output["used_ram"]["used"] = $output["used_ram"]["end"] - $output["used_ram"]["start"];

                // http://www.developerfusion.com/code/2058/determine-execution-time-in-php/
                $mtime = microtime();
                $mtime = explode(" ", $mtime);
                $mtime = $mtime[1] + $mtime[0];
                $output["used_time"]["end"] = $mtime;
                $output["used_time"]["used"] = ($output["used_time"]["end"] - $output["used_time"]["start"]);
            }
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getHelp() {
            return array(
                "description" => "Capture final resources consumed", 
                "parameters" => array(), 
                "response" => array()
            );
        }
    }
}
return new commandUsageEnd();