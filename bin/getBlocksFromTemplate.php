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
 * Transform a XML page to HTML
 * Parameters:
 * page = XML page to convert
 */
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

if (!class_exists("commandGetBlocksFromTemplate")) {

    class commandGetBlocksFromTemplate extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $resp = array();
            include_once("usr/xml2array/xml2array.php");
            include_once("etc/drivers/pages.php");
            if (!function_exists("GBFTpageToHTMLParseBlock")) {

            function GBFTpageToHTMLParseBlock($pageId, $blk, &$resp) {
                foreach ($blk as $key => $rows) {
                    if ($key != '@attributes') {
                        foreach ($rows as $row) {
                            foreach ($row["col"] as $col) {
                                $resp[] = $col['@attributes']["id"];
                                if (isset($col['row'])) {
                                    GBFTpageToHTMLParseBlock($pageId, $col, $resp);
                                }
                            }
                        }
                    }
                }
            }

            }

            if (is_file($params["page"])) {
                $page = file_get_contents($params["page"]);
                $struct = xml_string_to_array($page);
                GBFTpageToHTMLParseBlock(0, $struct["page"][0]["body"][0], $resp);
                GBFTpageToHTMLParseBlock(0, $struct["page"][0]["foot"][0], $resp);
            }
            
            $resp = array_unique($resp);
            return array("blocks" => $resp);
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
                "description" => __("Parse a page template and return her blocks places."),
                "parameters" => array(
                    "page" => __("XML page to parse")
                    ),
                "response" => array(
                    "blocks" => __("Array of blocks places.")
                    ),
                "type" => array(
                    "parameters" => array(
                        "page" => "string"
                        ),
                    "response" => array(
                        "blocks" => "array"
                        ),
                )
            );
        }

    }

}
return new commandGetBlocksFromTemplate();