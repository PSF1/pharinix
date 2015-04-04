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

if (!class_exists("commandAddPage")) {
    class commandAddPage extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            // Default values
            $params = array_merge(array(
                        'name' => '',
                        'template' => 'etc/templates/pages/default.xml',
                        'title' => '',
                        'description' => '',
                        'keys' => '',
                        'url' => '',
                    ), $params);
            
            $resp = array("ok" => false, "msg" => '');
            if (!is_file($params["template"])) {
                $resp["ok"] = false;
                $resp["msg"] = "Page template '{$params["template"]}' not found.";
            } else {
                if ($params["name"] == '') {
                    $resp["ok"] = false;
                    $resp["msg"] = "Page need a unique name.";
                } else {
                    $sql = "SELECT * FROM `pages` where `name` = '{$params["name"]}'";
                    $q = dbConn::Execute($sql);
                    if (!$q->EOF) {
                        $resp["ok"] = false;
                        $resp["msg"] = "Page name just exist.";
                    } else {
                        $sql = "insert into `pages` set ";
                        $sql .= "`name`= '{$params["name"]}', ";
                        $sql .= "`template`= '{$params["template"]}', ";
                        $sql .= "`title`= '{$params["title"]}', ";
                        $sql .= "`description`= '{$params["description"]}', ";
                        $sql .= "`keys`= '{$params["keys"]}' ";
                        dbConn::Execute($sql);
                        if ($params["url"] != "") {
                            driverCommand::run("addUrl", array(
                                "url" => $params["url"],
                                "cmd" => "command=pageToHTML&page=".$params["name"],
                            ));
                        }
                        $resp["ok"] = true;
                    }
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
                "description" => "Add a new page.", 
                "parameters" => array(
                    'name' => 'ID of page.',
                    'template' => 'Optional, path to the XML template file.',
                    'title' => 'Page title.',
                    'description' => 'Page description.',
                    'keys' => 'Page meta key words.',
                    'url' => 'Optional page URL.',
                ),
                "response" => array()
            );
        }
    }
}
return new commandAddPage();