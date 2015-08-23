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

if (!class_exists("commandGetVersion")) {
    class commandGetVersion extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $meta = new stdClass();
            $meta->name = 'Pharinix';
            $meta->slugname = 'core';
            $meta->version = CMS_VERSION;
            $meta->autor = 'Pharinix Copyright (c), 2015, Pedro Pelaez (aaaaa976@gmail.com)';
            $meta->website = 'https://github.com/PSF1/pharinix';
            $meta->description = __('Light weight framework with many interesting features. Data model with unix like security, configurable with it self, URL rewrite and mapping, etc... it\'s the perfect backend to your application.');
            $meta->licence = 'GNU GENERAL PUBLIC LICENSE Version 2';
            return array("version" => CMS_VERSION, 'meta' => $meta);
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
                "description" => __("Return Pharinix version."), 
                "parameters" => array(), 
                "response" => array(
                    "version" => __("Pharinix version."),
                    "meta" => __("Meta data information.")
                ),
                "type" => array(
                    "parameters" => array(), 
                    "response" => array(
                        "version" => "string",
                        "meta" => "string"
                    ),
                )
            );
        }
    }
}
return new commandGetVersion();