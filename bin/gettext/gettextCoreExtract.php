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

if (!class_exists("commandGettextCoreExtract")) {
    class commandGettextCoreExtract extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'language' => '',
                "reportMsgidBugsTo" => 'aaaaa976@gmail.com',
                "lastTranslator" => '',
                "languageTeam" => '',
            ), $params);
            
            $paths = array(
                'bin/', // Command folders
                'etc/', // System tools
                'usr/execForm/', // Commands console
            );
            // Extract from source code
            foreach($paths as $path) {
                $item = new stdClass();
                $item->path = $path;
                $item->resp = driverCommand::run('gettextExtract', array(
                    'path' => $path,
                    'language' => $params['language'],
                    "projectIdVersion" => 'Pharinix/'.CMS_VERSION,
                    "reportMsgidBugsTo" => $params['reportMsgidBugsTo'],
                    "lastTranslator" => $params['lastTranslator'],
                    "languageTeam" => $params['languageTeam'],
                    'po' => 'etc/i18n/'.$params['language'].'.po',
                ));
                $resp[] = $item;
            }
            // Extract from node types
            $nodes = array(
                'user',
                'group',
                'modules',
            );
            foreach($nodes as $node) {
                $item = new stdClass();
                $item->nodetype = $node;
                $item->resp = driverCommand::run('gettextNodeTypeExtract', array(
                    'nodetype' => $node,
                    'language' => $params['language'],
                    "projectIdVersion" => 'Pharinix/'.CMS_VERSION,
                    "reportMsgidBugsTo" => $params['reportMsgidBugsTo'],
                    "lastTranslator" => $params['lastTranslator'],
                    "languageTeam" => $params['languageTeam'],
                    'po' => 'etc/i18n/'.$params['language'].'.po',
                ));
                $resp[] = $item;
            }
            // 
            return $resp;
        }

        /**
         * Get a list of files in the folder and subfolders.
         * @param string $path
         * @param string $pattern File pattern like *.*
         * @return array
         */
        public static function getFiles($path, $pattern) {
            if (!driverTools::str_end('/', $path)) {
                $path .= '/';
            }
            $resp = array();
            $ls = driverTools::lsDir($path, $pattern);
            foreach($ls['files'] as $file) {
                $resp[] = $file;
            }
            $ls = driverTools::lsDir($path, '*');
            foreach($ls['folders'] as $folder) {
                $subls = self::getFiles($folder, $pattern);
                foreach($subls as $file) {
                    $resp[] = $file;
                }
            }
            return $resp;
        }
        
        public static function getHelp() {
            return array(
                "description" => __("Scan Pharinix to find text in gettext functions: __(), __e(), n__(), n__e(), p__(), p__e(). This explore all PHP and JS files."), 
                "parameters" => array(
                    'language' => __('Language code of the file.'),
                    'reportMsgidBugsTo' => __('Contact information to report translation bugs.'),
                    'lastTranslator' => __('Last translator name and mail.'),
                    'languageTeam' => __('Language team.'),
                ), 
                "response" => array(
                    'info' => __('Information about each folder scan.'),
                ),
                "type" => array(
                    "parameters" => array(
                        'language' => 'string',
                        'reportMsgidBugsTo' => 'string',
                        'lastTranslator' => 'string',
                        'languageTeam' => 'string',
                    ), 
                    "response" => array(
                        'info' => 'array',
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
return new commandGettextCoreExtract();