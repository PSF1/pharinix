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

// TODO: A attacker can call this method until full the server disk.

if (!class_exists("commandLPStart")) {
    class commandLPStart extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'label' => __('Long process'),
            ), $params);
            
            if (!driverUser::isLoged()) {
                return array('ok' => false, 'msg' => __('You need login.'));
            }
            
            include_once 'etc/drivers/longProcessMonitor.php';
            return array('monitor' => driverLPMonitor::start(0, $params['label']));
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Start a new monitor process. Monitors are safe mode compatible.").' '.__('Only login users can call this command.'), 
                "parameters" => array(
                    'label' => __('Global process label.'),
                ), 
                "response" => array(
                    'monitor' => __('Monitor object. The ID is in the id attribute.'),
                ),
                "type" => array(
                    "parameters" => array(
                        'label' => 'string',
                    ), 
                    "response" => array(
                        'monitor' => 'object',
                    ),
                ),
                "echo" => false
            );
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
    }
}
return new commandLPStart();