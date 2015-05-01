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

if (!class_exists("commandAddBlockToPage")) {
    class commandAddBlockToPage extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            include_once("etc/drivers/pages.php");
            $params = array_merge(array(
                        'page' => '',
                        'idcol' => 'content',
                        'command' => 'nothing',
                        'parameters' => '',
                        'priority' => 0, 
                    ), $params);
            $resp = array("ok" => false, "msg" => "");
            $page = driverPages::getPage($params["page"]);
            if ($page === false) {
                $resp["msg"] = "Page not found";
            } else {
                $sql = "insert into `page-blocks` set ";
                $sql .= "`idpage` = {$page->fields["id"]}, ";
                $sql .= "`idcol` = '{$params["idcol"]}', ";
                $sql .= "`command` = '{$params["command"]}', ";
                $sql .= "`parameters` = '{$params["parameters"]}', ";
                $sql .= "`priority` = {$params["priority"]}";
                dbConn::Execute($sql);
                $resp["ok"] = true;
            }
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getHelp() {
            return array(
                "description" => "Add a block to a page.", 
                "parameters" => array(
                    'page' => 'Name ID of page.',
                    'idcol' => 'Column ID where put the block.',
                    'command' => 'Command to execute.',
                    'parameters' => 'Parameters of command, it must be a URL encoded string.',
                    'priority' => 'Order of blocks. Default 0, first.',
                ),
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'page' => 'string',
                        'idcol' => 'integer',
                        'command' => 'string',
                        'parameters' => 'string',
                        'priority' => 'integer',
                    ), 
                    "response" => array(),
                )
            );
        }
    }
}
return new commandAddBlockToPage();