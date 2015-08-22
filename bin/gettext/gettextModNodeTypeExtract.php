<?php

/* 
 * Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
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

if (!class_exists("commandGettextModNodeTypeExtract")) {
    class commandGettextModNodeTypeExtract extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'slugname' => '',
                'language' => '',
                "reportMsgidBugsTo" => '',
                "lastTranslator" => '',
                "languageTeam" => '',
            ), $params);
            
            if ($params['slugname'] == '') {
                return array('ok' => false, 'msg' => __('Node type is required.'));
            }
            
            $path = driverCommand::run('modGetPath', array(
                'name' => $params['slugname']
            ));
            $path = $path['path'];
            if ($path == '') {
                return array('ok' => false, 'msg' => __('Module not found.'));
            }
            if (!driverTools::str_end('/', $path)) {
                $path .= '/';
            }
            if (!is_dir($path . 'i18n/')) {
                mkdir($path . 'i18n/');
            }
            
            // Node types
            $json = json_decode(file_get_contents($path . "meta.json"));
            $resp = array();
            foreach ($json->nodetypes as $name => $nodes) {
                $item = new stdClass();
                $item->nodetype = $name;
                $item->resp = driverCommand::run('gettextNodeTypeExtract', array(
                    'nodetype' => $name,
                    'language' => $params['language'],
                    "projectIdVersion" => $params['slugname'] . '/' . $json->meta->version,
                    "reportMsgidBugsTo" => $params['reportMsgidBugsTo'],
                    "lastTranslator" => $params['lastTranslator'],
                    "languageTeam" => $params['languageTeam'],
                    'po' => $path . 'i18n/' . $params['language'] . '.po',
                ));
                $resp[] = $item;
            }
            return $resp;
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Scan the module's node types to find text in labels and help literals. This use the meta.json file to select node types to explore."), 
                "parameters" => array(
                    'slugname' => __('Module to explore.'),
                    'language' => __('Language code of the file.'),
                    "reportMsgidBugsTo" => __('Contact information to report translation bugs.'),
                    "lastTranslator" => __('Last translator name and mail.'),
                    "languageTeam" => __('Language team.'),
                ), 
                "response" => array(
                    'previous' => __('Number of items before scan.'),
                    'items' => __('Number of items after scan.')
                ),
                "type" => array(
                    "parameters" => array(
                        'slugname' => 'string',
                        'language' => 'string',
                        "reportMsgidBugsTo" => 'string',
                        "lastTranslator" => 'string',
                        "languageTeam" => 'string',
                    ), 
                    "response" => array(
                        'previous' => 'string',
                        'items' => 'string'
                    ),
                )
            );
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
//        public static function getAccessFlags() {
//            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
//        }
    }
}
return new commandGettextModNodeTypeExtract();