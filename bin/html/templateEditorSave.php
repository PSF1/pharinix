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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandTemplateEditorSave")) {
    class commandTemplateEditorSave extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array("tpl" => "", "name" => ""), $params);
            
            if ($params["name"] == "" || $params["tpl"] == "") {
                throw new Exception("templateEditorSave: Name or tpl is empty.");
            } else {
                include_once("libs/xml2array/xml2array.php");
                if (!function_exists("commandTemplateEditorSaveParseDiv")) {
                    function commandTemplateEditorSaveParseDiv($divs, $tabs) {
                        $xml = $tabs;
                        $tplType = "";
                        if (isset($divs["@attributes"])) {
                            if (isset($divs["@attributes"]["tpltype"])) {
                                $xml .= '<'.$divs["@attributes"]["tpltype"].' ';
                            } else {
                                $divs["@attributes"]["tpltype"] = "row";
                                $xml .= '<row ';
                            }
                            $tplType = $divs["@attributes"]["tpltype"];
                            if (isset($divs["@attributes"]["id"])) {
                                $xml .= 'id="'.$divs["@attributes"]["id"].'" ';
                            }
                            if (isset($divs["@attributes"]["class"])) {
                                $dClass = explode(" ", $divs["@attributes"]["class"]);
                                $xml .= "class=\"";
                                foreach($dClass as $class) {
                                    if ($class != "row" && $class != "column") {
                                        $xml .= $class." ";
                                    }
                                }
                                $xml .= "\" ";
                            }
                        }
                        $xml .= ">\n";
                        foreach($divs as $type => $block) {
                            if ($type != "@attributes" && $type != "#comment") {
                                foreach ($block as $key => $div) {
                                    if(is_array($div)) {
                                        $xml .= $tabs.commandTemplateEditorSaveParseDiv($div, $tabs."\t");
                                    }
                                }
                            }
                        }
                        $xml .= $tabs.'</'.$tplType.">\n";
                        return $xml;
                    }
                }
                $params["tpl"] = base64_decode($params["tpl"]);
                $struct = xml_string_to_array($params["tpl"]);
                $xml = "";
                $xml .= '<page lang="en" charset="utf-8">'."\n";
                $xml .= "\t".'<name>'.$params["name"].'</name>'."\n";
                $xml .= "\t".'<title>'.$params["title"].'</title>'."\n";
                $xml .= "\t".'<head>'.  base64_decode($params["head"]).'</head>'."\n";
                $xml .= "\t".'<body>'."\n";
                foreach ($struct["tpl"][0]["div"] as $key => $value) {
                    $xml .= "\t".commandTemplateEditorSaveParseDiv($value, "\t");
                }
                $xml .= "\t".'</body>'."\n";
                $xml .= '</page>';
                
                $resp = array(
                    "ok" => false,
                    "msg" => "",
                );
                
                $r = file_put_contents("templates/pages/".$params["name"].".xml", $xml);
                $resp["ok"] = ($r !== FALSE);
                echo driverCommand::run("toJson", $resp);
            }
        }

        public static function getHelp() {
            return array(
                "description" => "Save a user template from the template editor.", 
                "parameters" => array(
                    "tpl" => "Base 64 encoded HTML definition of the template.",
                    "name" => "Template name, It must be a compatible file name.",
                    "title" => "Template default title.",
                    "head" => "Base 64 encoded meta information to add to the HTML head area.",
                    ), 
                "response" => array(
                    "ok" => "TRUE if is ok.",
                    "msg" => "In case of error the error message.",
                )
            );
        }
    }
}
return new commandTemplateEditorSave();