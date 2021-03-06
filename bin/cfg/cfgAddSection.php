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

if (!class_exists("commandCfgAddSection")) {
    class commandCfgAddSection extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'section' => ' ',
            ), $params);
            $section = driverConfig::getCFG()->getSection($params['section']);
            if ($section != null) {
                return array('ok' => false, 'msg' => __('Section found.'));
            }
            driverConfig::getCFG()->addSection($params['section']);
            driverConfig::getCFG()->save();
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Add a new section to configuration."), 
                "parameters" => array(
                    'section' => __('Section name, it must be "[name]".'),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'section' => 'string',
                    ), 
                    "response" => array(),
                ),
                "echo" => false
            );
        }
    }
}
return new commandCfgAddSection();