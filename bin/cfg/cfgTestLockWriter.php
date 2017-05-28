<?php
/* 
 * Pharinix Copyright (C) 2017 Pedro Pelaez <aaaaa976@gmail.com>
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

if (!class_exists("commandCfgTestLockWriter")) {
    class commandCfgTestLockWriter extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(), $params);
            usleep(250);
            $testfile = 'tests/drivers/cfg_ini/threadsafe.ini';
            $cfg = new driverConfigIni($testfile);
            $cfg->parse();
            $cfg->addSection('[long section]');
            for ($index = 0; $index < 5000; $index++) {
                $cfg->getSection('[long section]')->set('key_' . $index, $index);
            }
            $cfg->save($testfile);
            
            $size = filesize($testfile);
            if (!is_file($testfile)) {
                $size = 0;
            }
            $sql = "insert into `testCFGLocks` set `size` = ".$size;
            dbConn::Execute($sql);
            
            return array('ok' => true);
        }
        
//        public static function getAccessFlags() {
//            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
//        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Write a dummy configuration file."), 
                "parameters" => array(), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(), 
                    "response" => array(),
                ),
                "echo" => false
            );
        }
    }
}
return new commandCfgTestLockWriter();