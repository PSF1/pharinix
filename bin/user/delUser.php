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

if (!class_exists("commandDelUser")) {
    class commandDelUser extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "mail" => "",
                "uid" => "",
            ), $params);
            
            if ($params["uid"] == "" && $params["mail"] == "") {
                return;
            }
            
            // Get user ID
            if ($params["mail"] != "") {
                $node = driverCommand::run("getNodes", array(
                    "nodetype" => "user",
                    "fields" => "`id`",
                    "where" => "`mail` = '{$params["mail"]}'",
                ));
                if (count($node) == 0) return;
                $params["uid"] = array_keys($node)[0];
            }
            $node = driverCommand::run("getNode", array(
                "nodetype" => "user",
                "node" => $params["uid"],
            ));
            
            // Del default group
            $grp = driverCommand::run("getNodes", array(
                "nodetype" => "group",
                "fields" => "`id`",
                "where" => "`title` = '".$node[$params["uid"]]["name"]."'",
            ));
            if (count($grp) > 0) {
                driverCommand::run("delNode", array(
                    "nodetype" => "group",
                    "nid" => array_keys($grp)[0],
                ));
            }
            // Del user node
            driverCommand::run("delNode", array(
                "nodetype" => "user",
                "nid" => $params["uid"],
            ));
        }

        public static function getAccess() {
            return parent::getAccess(__FILE__);
        }
        
        public static function getHelp() {
            return array(
                "description" => "Delete a user by ID or mail.", 
                "parameters" => array(
                    "uid" => "The user id.",
                    "mail" => "The user mail.",
                ), 
                "response" => array()
            );
        }
    }
}
return new commandDelUser();