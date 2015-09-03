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

if (!class_exists("commandLPDemo")) {
    class commandLPDemo extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'step' => 1,
            ), $params);
            
            if (!driverUser::isLoged()) {
                return array('ok' => false, 'msg' => __('You need login.'));
            }
            
            include_once 'etc/drivers/longProcessMonitor.php';
            $lp = driverLPMonitor::start(100, __('Demo process'));
            for($i = 0; $i < 100; ++$i) {
                sleep(1);
                driverLPMonitor::update($lp->id, $i, sprintf(__('Step %s'), $i), 100);
                if ($i == 50) {
                    driverLPMonitor::update($lp->id, $i, __('I\'m sleeping 10 seconds'), 0);
                    driverLPMonitor::setError($lp->id, TRUE);
                    sleep(10);
                    driverLPMonitor::setError($lp->id, false);
                }
            }
            return array('ok' => driverLPMonitor::close($lp->id));
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Start a long time process to allow see Monitor alive."), 
                "parameters" => array(
                    'step' => __('Sleep seconds in each step. This demo have 100 steps.'),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'step' => 'integer',
                    ), 
                    "response" => array(),
                )
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
return new commandLPDemo();