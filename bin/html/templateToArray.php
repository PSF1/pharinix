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

if (!class_exists("commandTemplateToArray")) {

    class commandTemplateToArray extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            include_once("usr/xml2array/xml2array.php");
            include_once("etc/drivers/pages.php");
            
            if (!function_exists("templateToHTMLParseBlock")) {

                function templateToHTMLParseBlock($pageId, $blk) {
                    $resp = "";
                    foreach ($blk as $key => $rows) {
                        if ($key != '@attributes') {
                            foreach ($rows as $row) {
                                $resp .= "<div class=\"row\" tpltype=\"row\" ";
                                foreach ($row['@attributes'] as $name => $attr) {
                                    $resp .= " $name=\"$attr\"";
                                }
                                $resp .= ">";
                                foreach ($row["col"] as $col) {
                                    $resp .= "<div";
                                    foreach ($col['@attributes'] as $name => $attr) {
                                        $resp .= " $name=\"$attr\"";
                                    }
                                    $resp .= " tpltype=\"col\">";
                                    if (isset($col['row'])) {
                                        $resp .= templateToHTMLParseBlock($pageId, $col);
                                    }
                                    $resp .= "</div>";
                                }
                                $resp .= "</div>";
                            }
                        }
                    }
                    return $resp;
                }

            }

            if (is_file($params["template"])) {
                $page = file_get_contents($params["template"]);
                $struct = xml_string_to_array($page);
                $resp = array(
                    "name" => "",
                    "title" => "",
                    "head" => "",
                    "body" => "",
                );
                if ($struct["page"][0]["title"][0] != "") {
                    $resp["title"] = $struct["page"][0]["title"][0];
                }
                if ($struct["page"][0]["name"][0] != "") {
                    $resp["name"] = $struct["page"][0]["name"][0];
                }
                foreach ($struct["page"][0]["head"][0] as $tag => $attr) {
                    foreach ($attr as $value) {
                        if ($tag != "#comment" && isset($value['@attributes']) && count($value['@attributes']) > 0) {
                            $resp["head"] .= "<$tag";
                            foreach ($value['@attributes'] as $name => $val) {
                                if ($name == "src" || $name == "href") {
                                    $val = CMS_DEFAULT_URL_BASE . $val;
                                }
                                $resp["head"] .= " $name=\"$val\"";
                            }
                            $resp["head"] .= "></$tag>";
                        }
                    }
                }
                $resp["body"] = templateToHTMLParseBlock(0, $struct["page"][0]["body"][0]);
                return $resp;
            } else {
                throw new Exception("Template '{$params["template"]}' not found.");
            }
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
                "description" => __("Transform a XML template to HTML, it not build blocks."),
                "parameters" => array(
                    "template" => __("XML to convert"),
                ),
                "response" => array(
                    "name" => __("Template name."),
                    "title" => __("Default page title."),
                    "head" => __("Head content"),
                    "body" => __("Body structure in HTML, it's parsed to template editor."),
                ),
                "type" => array(
                    "parameters" => array(
                        "template" => "string",
                    ),
                    "response" => array(
                        "name" => "string",
                        "title" => "string",
                        "head" => "string",
                        "body" => "string",
                    ),
                )
            );
        }

    }

}
return new commandTemplateToArray();
