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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandEchoSimpleXML")) {
    class commandEchoSimpleXML extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            @header("Content-type: application/xml");
            /**
             * http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/#.VfL-jBGvE_4
             * By Sean Barton
             * 
             */
            $xml = self::generate_valid_xml_from_array($params);
            echo $xml;
        }

        public static function generate_xml_from_array($array, $node_name) {
            $xml = '';

            if (is_array($array) || is_object($array)) {
                foreach ($array as $key => $value) {
                    if (is_numeric($key)) {
                        $key = $node_name;
                    }
                    $xml .= '<' . $key . '>' . 
                            self::generate_xml_from_array($value, $node_name) . 
                            '</' . $key . '>';
                }
            } else {
                $xml = '<![CDATA['.str_replace(']]>', ']]>', htmlspecialchars($array, ENT_QUOTES)).']]>';
            }

            return $xml;
        }

        public static function generate_valid_xml_from_array($array, $node_block = 'nodes', $node_name = 'node') {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

            $xml .= '<' . $node_block . '>';
            $xml .= self::generate_xml_from_array($array, $node_name);
            $xml .= '</' . $node_block . '>';

            return $xml;
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
                "description" => __("Echo to browser the XML representation of parameters, try to change the HTTP header to Content-type: application/xml."), 
                "parameters" => array("some" => __("It can receive any amount of parameters.")), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "some" => "args"
                    ), 
                    "response" => array(),
                ),
                "echo" => true,
                "interface" => true
            );
        }
    }
}
return new commandEchoSimpleXML();