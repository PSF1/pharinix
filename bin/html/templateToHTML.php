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

if (!class_exists("commandTemplateToHTML")) {

    class commandTemplateToHTML extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            include_once("libs/xml2array/xml2array.php");
            include_once("drivers/pages.php");
            
            if (!function_exists("templateToHTMLParseBlock")) {

                function templateToHTMLParseBlock($pageId, $blk) {
                    foreach ($blk as $key => $rows) {
                        if ($key != '@attributes') {
                            foreach ($rows as $row) {
                                echo "<div class=\"row\" tpltype=\"row\" ";
                                foreach ($row['@attributes'] as $name => $attr) {
                                    echo " $name=\"$attr\"";
                                }
                                echo ">";
                                foreach ($row["col"] as $col) {
                                    echo "<div";
                                    foreach ($col['@attributes'] as $name => $attr) {
                                        echo " $name=\"$attr\"";
                                    }
                                    echo " tpltype=\"col\">";
                                    if (isset($col['row'])) {
                                        templateToHTMLParseBlock($pageId, $col);
                                    }
                                    echo "</div>";
                                }
                                echo "</div>";
                            }
                        }
                    }
                }

            }

            if (is_file($params["template"])) {
                $page = file_get_contents($params["template"]);
                $struct = xml_string_to_array($page);
                templateToHTMLParseBlock(0, $struct["page"][0]["body"][0]);
            } else {
                throw new Exception("Template '{$params["template"]}' not found.");
            }
        }

        public static function getHelp() {
            return array(
                "description" => "Transform a XML template to HTML, and echo, it not build blocks.",
                "parameters" => array(
                    "template" => "XML to convert",
                ),
                "response" => array()
            );
        }

    }

}
return new commandTemplateToHTML();
