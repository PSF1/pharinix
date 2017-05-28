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

if (!class_exists("commandCfgTestLock")) {
    class commandCfgTestLock extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(), $params);
            $nThreads = 100;
            $mysessionID = session_id();

            $sql = "CREATE TABLE IF NOT EXISTS `testCFGLocks` (`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,`size` INTEGER UNSIGNED NOT NULL,PRIMARY KEY (`id`))";
            dbConn::Execute($sql);

            for ($index = 0; $index < $nThreads; $index++) {
                driverTools::apiCallMS('http://127.0.0.1/?auth_token='.$mysessionID.'&command=cfgTestLockWriter&inteface=echoText', array(), true, false, null, 1);
            }
            
//            $sql = "select count(*) from `testCFGLocks`";
//            do {
//                $q = dbConn::Execute($sql);
//                var_dump($q->fields[0]);
//            } while ($q->fields[0] < $nThreads);
            
//            $resp = array();
//            while (!$q->EOF) {
//                $resp[$q->fields['size']] = '';
//                $q->MoveNext();
//            }
//            
//            $sql = "DROP TABLE `testCFGLocks`";
//            dbConn::Execute($sql);
            
            return;
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Try if a configuration file is thread-safe in server environment."), 
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
return new commandCfgTestLock();