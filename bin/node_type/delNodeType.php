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

if (!class_exists("commandDelNodeType")) {
    class commandDelNodeType extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            if (isset($params["name"])) {
                $nid = driverCommand::run("getNodeTypeId", $params);
                $nid = $nid["id"];
                if ($nid !== false) {
                    // Del pages of nodes...
                    //RW: rewriteto = command=pageToHTML&page=node_type_testtype_1
                    // NOTE: Erased by delPage command
//                    $sql = "delete from `url_rewrite` where `rewriteto` like 'command=pageToHTML&page=node_type_{$params["name"]}_%'";
//                    dbConn::Execute($sql);
                    //PG: name = node_type_testtype_1
                    $sql = "select `name` from `pages` where `name` like 'node_type_{$params["name"]}_%'";
                    $q = dbConn::Execute($sql);
                    set_time_limit(0);
                    while(!$q->EOF) {
                        driverCommand::run("delPage", array(
                            'name' => $q->fields["name"],
                        ));
                        $q->MoveNext();
                    }
                    set_time_limit(ini_get('max_execution_time'));
                    // Delete tables
                    $sql = "delete from `node_type_field` where `node_type` = $nid";
                    dbConn::Execute($sql);
                    $sql = "delete from `node_type` where `id` = $nid";
                    dbConn::Execute($sql);
                    $sql = "DROP TABLE IF EXISTS `node_{$params["name"]}`";
                    dbConn::Execute($sql);
                    // Delete multi relation tables
                    $sql = "show tables like 'node_relation_{$params["name"]}%'";
                    $q = dbConn::Execute($sql);
                    set_time_limit(0);
                    while (!$q->EOF) {
                        $sql = "DROP TABLE IF EXISTS `{$q->fields[0]}`";
                        dbConn::Execute($sql);
                        $q->MoveNext();
                    }
                    set_time_limit(ini_get('max_execution_time'));
                    // Del page of node type
                    driverCommand::run("delPage", array(
                        'name' => "node_type_".$params["name"],
                    ));
                }
            }
        }

        public static function getHelp() {
            return array(
                "description" => "Erase a node type. Note: This command alter the execution time limit and reset it to the php.ini default value.", 
                "parameters" => array(
                    "name" => "Node type to erase."
                ), 
                "response" => array()
            );
        }
    }
}
return new commandDelNodeType();