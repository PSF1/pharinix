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

/*
 * Echo html
 * Parameters:
 * html = HTML code to echo.
 */
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}
//echo urlencode('<h3>Pharinix Copyright © <?php echo date("Y"); ? > Pedro Pelaez</h3>
//<div>This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.<br/>
//<br/>
//This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.<br/>
//<br/>
//You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//</div>');
if (!class_exists("commandShowAreas")) {
    class commandShowAreas extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'show' => false
            ), $params);
            $sec = driverConfig::getCFG()->getSection('[pageToHTML]');
            if ($sec != null) {
                $sec->set('show_areas', ($params['show'] == true));
                driverConfig::getCFG()->save();
                return array('ok' => true);
            }
            return array('ok' => false, 'msg' => __('show_areas configuration not found.'));
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
//        public static function getAccessFlags() {
//            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
//        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Change the debug show_areas configuration to pageToHTML."), 
                "parameters" => array('show' => __('True to show areas.')), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'show' => 'boolean',
                    ), 
                    "response" => array(),
                ),
                "echo" => false
            );
        }
    }
}
return new commandShowAreas();