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

//TODO: Securizate access

if (!class_exists("commandDelNode")) {

    class commandDelNode extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $resp = array("ok" => false, "msg" => "");

            // Default values
            $params = array_merge(array(
                "nodetype" => "",
                "nid" => "",
                    ), $params);
            if ($params["nodetype"] == "") { // Node type defined?
                $resp["msg"] = "Node type required";
            } else if ($params["nid"] == "") { 
                $resp["msg"] = "Node ID required";
            } else {
                try {
                    $def = driverCommand::run("getNodeTypeDef", $params);
                    // Delete relations
                    foreach($def["fields"] as $field) {
                        if ($field["multi"]) {
                            $table = '`node_relation_'.$params["nodetype"].'_'
                                    .$field["name"].'_'.$field["type"].'`';
                            $sql = "delete from $table where `type1` = ".$params["nid"];
                            dbConn::Execute($sql);
                        }
                    }
                    // Delete node
                    $sql = "delete from `node_{$params["nodetype"]}` where `id` = ".$params["nid"];
                    dbConn::Execute($sql);
                    // Delete page
                    driverCommand::run("delPage", array(
                        'name' => "node_type_" . $params["nodetype"] . "_" . $params["nid"],
                    ));
                    $resp["ok"] = true;
                } catch (Exception $exc) {
                    $resp["msg"] = $exc->getMessage();
                }
            }
            return $resp;
        }
        
        public static function getAccess() {
            return parent::getAccess(__FILE__);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
        
        public static function getHelp() {
            return array(
                "description" => "Delete a node.",
                "parameters" => array(
                    "nid" => "Node ID to erase.",
                    "nodetype" => "Node type is from.",
                ),
                "response" => array(
                    "ok" => "True/False node erased",
                    "msg" => "If error, it's a message about error",
                )
            );
        }

    }

}
return new commandDelNode();
