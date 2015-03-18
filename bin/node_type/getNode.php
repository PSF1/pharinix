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

// TODO: SECURITY !!
// TODO: Load multi value fields.

if (!class_exists("commandGetNode")) {
    class commandGetNode extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(
                    array(
                        "nodetype" => "",
                        "node" => 0,
                    ),$params);
            $sql = "select * from `node_{$params["nodetype"]}` where id = ".$params["node"];
            try {
                $q = dbConn::get()->Execute($sql);
            } catch (Exception $ex) {
                $q = null;
            }
            
            if ($q != null) {
                // TODO: Format the output a bit, please... :D
                return $q->fields;
            }
        }

        public static function getHelp() {
            return array(
                "description" => "Return the node content.", 
                "parameters" => array(
                    "nodetype" => "Node type.",
                    "node" => "Node ID.",
                ), 
                "response" => array(
                    "node" => "Array with node content indexed by field name.",
                )
            );
        }
    }
}
return new commandGetNode();