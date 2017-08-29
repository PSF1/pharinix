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

if (!class_exists("commandGetPageBlocks")) {
    class commandGetPageBlocks extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            include_once("etc/drivers/pages.php");
            $params = array_merge(array(
                        'page' => '',
                    ), $params);
            $resp = array("ok" => false, "msg" => "", 'data' => array());
            $page = driverPages::getPage($params["page"]);
            if ($page === false) {
                $resp["msg"] = __("Page not found");
            } else {
                $sql = "SELECT * FROM `page-blocks` where idpage={$page->fields["id"]} order by priority";
                $q = dbConn::Execute($sql);
                $resp["ok"] = true;
                while (!$q->EOF) {
                    $item = new stdClass();
                    $item->id = $q->fields['id'];
                    $item->idCol = $q->fields['idcol'];
                    $item->command = $q->fields['command'];
                    $item->parameters = $q->fields['parameters'];
                    $item->priority = $q->fields['priority'];
                    $resp['data'][] = $item;
                    $q->MoveNext();
                }
            }
            return $resp;
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Get page's blocks list."), 
                "parameters" => array(
                    'page' => __('Name ID of page.'),
                ),
                "response" => array(
                    "ok" => __("TRUE if the page is found"),
                    "msg" => __("If not OK the error message"),
                    'data' => __("Blocks list"),
                ),
                "type" => array(
                    "parameters" => array(
                        'page' => 'string',
                    ), 
                    "response" => array(
                        "ok" => "boolean",
                        "msg" => "string",
                        'data' => "array",
                    ),
                ),
                "echo" => false
            );
        }
    }
}
return new commandGetPageBlocks();