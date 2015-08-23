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

// TODO: SECURITY !!

if (!class_exists("commandGetNodeTypeList")) {
    class commandGetNodeTypeList extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $sql = "SELECT `name`, `user_owner`, `group_owner`, `access` FROM `node_type`";
            $where = "";
            if (!driverUser::isSudoed()) {
                $usrGrps = driverUser::getGroupsID();
                $grpQuery = "";
                foreach ($usrGrps as $grp) {
                    if ($grpQuery != "")
                        $grpQuery .= " || ";
                    if ($grp == "")
                        $grp = -1;
                    $grpQuery .= "`group_owner` = $grp";
                }
                $secWhere = "(( IF(`user_owner` = " . driverUser::getID() . ",1024,0) | ";
                $secWhere .= "IF($grpQuery,64,0) | 4) ";
                $secWhere .= "& `access`)";
                $where = " where " . $secWhere;
            }
            $sql .= $where;
            $q = dbConn::Execute($sql);
            $resp = array();
            while(!$q->EOF) {
                $resp[] = $q->fields["name"];
                $q->MoveNext();
            }
            return $resp;
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Return a list of node types."), 
                "parameters" => array(), 
                "response" => array(
                    "types" => __("Array with node type names."),
                ),
                "type" => array(
                    "parameters" => array(), 
                    "response" => array(
                        "types" => "array",
                    ),
                )
            );
        }
    }
}
return new commandGetNodeTypeList();