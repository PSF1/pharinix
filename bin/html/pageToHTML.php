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

if (!class_exists("commandPageToHTML")) {

    class commandPageToHTML extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            include_once("libs/xml2array/xml2array.php");
            include_once("etc/drivers/pages.php");
            if (!function_exists("pageToHTMLParseBlock")) {

                function pageToHTMLParseBlock($pageId, $blk) {
                    foreach ($blk as $key => $rows) {
                        if ($key != '@attributes') {
                            foreach ($rows as $row) {
                                echo "<div class=\"row\" ";
                                foreach ($row['@attributes'] as $name => $attr) {
                                    echo " $name=\"$attr\"";
                                }
                                echo ">";
                                if (CMS_DEBUG)
                                    echo "<h6><span class=\"label label-success\">row ID: " . $row['@attributes']["id"] . "</span></h6>";
                                foreach ($row["col"] as $col) {
                                    echo "<div";
                                    foreach ($col['@attributes'] as $name => $attr) {
                                        echo " $name=\"$attr\"";
                                    }
                                    echo ">";
                                    if (CMS_DEBUG)
                                        echo "<h6><span class=\"label label-success\">Col ID: " . $col['@attributes']["id"] . "</span></h6>";
                                    // Call command list
                                    $cmd = driverPages::getCommands($pageId, $col['@attributes']["id"]);
                                    while ($cmd !== false && !$cmd->EOF) {
                                        $params = array();
                                        parse_str($cmd->fields["parameters"], $params);
                                        driverCommand::run($cmd->fields["command"], $params);
                                        $cmd->MoveNext();
                                    }
                                    if (isset($col['row'])) {
                                        pageToHTMLParseBlock($pageId, $col);
                                    }
                                    echo "</div>";
                                }
                                echo "</div>";
                            }
                        }
                    }
                }

            }

            $def = driverPages::getPage($params["page"]);
            if ($def !== false) {
                if (is_file($def->fields["template"])) {
                    $page = file_get_contents($def->fields["template"]);
                    $struct = xml_string_to_array($page);
                    $htmlLang = "";
                    $charset = "";
                    foreach ($struct["page"][0]["@attributes"] as $key => $value) {
                        switch ($key) {
                            case "lang":
                                $htmlLang = ' lang="' . $value . '"';
                                break;
                            case "charset":
                                $charset = "<meta charset=\"$value\">";
                                break;
                        }
                    }
                    echo '<html' . $htmlLang . '>';
                    echo '<head>';
                    echo '<meta charset="utf-8">';
                    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
                    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
                    if ($charset != "")
                        echo $charset;
                    if (isset($struct["page"][0]["title"][0])) {
                        echo '<title>' . $def->fields["title"];
                        if ($struct["page"][0]["title"][0] != "") {
                            echo " :: ";
                            echo $struct["page"][0]["title"][0];
                        }
                        echo '</title>';
                    }
                    foreach ($struct["page"][0]["head"][0] as $tag => $attr) {
                        foreach ($attr as $value) {
                            if ($tag != "#comment" && isset($value['@attributes']) && count($value['@attributes']) > 0) {
                                echo "<$tag";
                                foreach ($value['@attributes'] as $name => $val) {
                                    if ($name == "src" || $name == "href") {
                                        $val = CMS_DEFAULT_URL_BASE . $val;
                                    }
                                    echo " $name=\"$val\"";
                                }
                                echo "></$tag>";
                            }
                        }
                    }
                    echo '<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
            <!--[if lt IE 9]>
              <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
              <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->';
                    echo '</head>';
                    echo '<body>';
                    echo '<div class="container-fluid">';
                    if (CMS_DEBUG)
                        echo "<h6><span class=\"label label-success\">Body</span></h6>";
                    pageToHTMLParseBlock($def->fields["id"], $struct["page"][0]["body"][0]);
                    echo "</div>";
//                    echo '<div id="footer">';
//                    echo '<div class="container-fluid">';
//                    if (CMS_DEBUG)
//                        echo "<h6><span class=\"label label-success\">Foot</span></h6>";
//                    pageToHTMLParseBlock($def->fields["id"], $struct["page"][0]["foot"][0]);
//                    echo "</div>";
//                    echo "</div>";
                    echo '</body>';
                } else {
                    throw new Exception("Page template '{$def->fields["template"]}' not found.");
                }
            } else {
                throw new Exception("Page '{$params["page"]}' not found.");
            }
        }

        public static function getHelp() {
            return array(
                "description" => "Transform a page to HTML",
                "parameters" => array("page" => "Page to convert, see 'url_rewrite' in table."),
                "response" => array()
            );
        }

    }

}
return new commandPageToHTML();
