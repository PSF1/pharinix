<?php

/* 
 * Pharinix Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
 * Sources https://github.com/PSF1/pharinix
 * 
 * mnuDel: (C) 2016 Domingo Llanes <domingollanes.dev@gmail.com>
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

if (!class_exists("commandMnuDel")) {
    class commandMnuDel extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            unset($params['nodetype']);
            
            $params = array_merge(array(
                        'nodetype' => 'menu',
                        'slugname' => '',
                        'recursive' => false,
                    ), $params);
            if (!is_numeric($params['slugname'])) {
                $parent = driverCommand::run('getNodes', array(
                    'nodetype' => 'menu',
                    'fields' => 'id',
                    'where' => "`slugname` = '{$params['slugname']}'",
                ));
                if (count($parent) > 0) {
                    foreach($parent as $key => $what) {
                        $params['nid'] = $key;
                        break;
                    }
                    unset($params['slugname']);
                } else {
                    return array('ok' => false, 'msg' => __('Parent menu not found.'));
                }
            }
            if ($params['recursive']) {
                // Remove submenus too
                $parent = driverCommand::run('getNodes', array(
                    'nodetype' => 'menu',
                    'fields' => 'id',
                    'where' => "`parent` = '{$params['nid']}'",
                ));
                foreach($parent as $key => $what) {
                    driverCommand::run('mnuDel', array(
                        'slugname' => $key,
                        'recursive' => $params['recursive'],
                    ));
                }
            }
            return driverCommand::run('delNode', $params);
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Remove a menu option."), 
                "parameters" => array(
                        'slugname' => __('A required unique name used how reference'),
                        'recursive' => __('If TRUE remove submenus too. Default FALSE.'),
                    ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'slugname' => 'string',
                        'recursive' => 'boolean',
                    ), 
                    "response" => array(),
                ),
                "echo" => false,
                "interface" => false,
            );
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }

    }
}
return new commandMnuDel();