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

if (!class_exists("commandGetBaseJS")) {
    class commandGetBaseJS extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            header("Content-Type:application/javascript");
            $js = file_get_contents('etc/templates/pharinix/baseJS.js');
            $js = str_replace('{$$CMS_DEFAULT_URL_BASE}', CMS_DEFAULT_URL_BASE, $js);
            echo $js;
            echo "\n// Translations\n\n";
            $po = driverUser::getTranslations();
            if ($po == null) $po = new Gettext\Translations();
            echo "var PHARINIX_TRANSLATIONS = ".Gettext\Generators\JsonDictionary::toString($po).";";
            
            // sprintf
            echo "\n\n";
            echo file_get_contents('etc/templates/pharinix/sprintf.js');
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
                "description" => __("Echo base javascript assets with a content-type header of application/javascript."), 
                "parameters" => array(), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(), 
                    "response" => array(),
                ),
                "echo" => true
            );
        }
    }
}
return new commandGetBaseJS();