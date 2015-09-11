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

if (!class_exists("commandGettextExtract")) {
    class commandGettextExtract extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'path' => './',
                'language' => '',
                "projectIdVersion" => 'Pharinix/'.CMS_VERSION,
                "reportMsgidBugsTo" => '',
                "lastTranslator" => '',
                "languageTeam" => '',
                'po' => '',
            ), $params);
            
            if ($params['po'] == '') {
                return array('ok' => false, 'msg' => __('PO file is required.'));
            }
            
            if (!driverTools::str_end('/', $params['path'])) {
                $params['path'] = $params['path'].'/';
            }
            
            if (!is_file($params['po'])) {
                $po = fopen($params['po'], 'wb+');
                fclose($po);
                $po = Gettext\Extractors\Po::fromFile($params['po']);
                $po->setLanguage($params['language']);
                $po->setHeader('Project-Id-Version', $params['projectIdVersion']);
                $po->setHeader('Report-Msgid-Bugs-To', $params['reportMsgidBugsTo']);
                $po->setHeader('Last-Translator', $params['lastTranslator']);
                $po->setHeader('Language-Team', $params['languageTeam']);
                Gettext\Generators\Po::toFile($po, $params['po']);
            }
            $fInfo = driverTools::pathInfo($params['po']);
            if ($fInfo['writable']) {
                $translations = new Gettext\Translations();
                $po = Gettext\Extractors\Po::fromFile($params['po']);
                $po->setHeader('Project-Id-Version', $params['projectIdVersion']);
                $po->setHeader('Report-Msgid-Bugs-To', $params['reportMsgidBugsTo']);
                $po->setHeader('Last-Translator', $params['lastTranslator']);
                $po->setHeader('Language-Team', $params['languageTeam']);
                $prev = $po->count();
                
                $filetypes = array(
                    array('php', '*.php'), 
                    array('php', '*.html'), 
                    array('php', '*.htm'),
                    array('js', '*.js'));
                foreach($filetypes as $expCmd) {
                    $files = self::getFiles($params['path'], $expCmd[1]);
                    if (count($files)) {
                        $translations = null;
                        switch ($expCmd[0]) {
                            case 'php':
                                $translations = Gettext\Extractors\PhpCode::fromFile($files);
                                break;
                            case 'js':
                                $translations = Gettext\Extractors\JsCode::fromFile($files);
                                break;
                        }
                        if ($translations != null) {
                            $po = Gettext\Extractors\Po::fromFile($params['po']);
                            $translations->mergeWith($po);
                            Gettext\Generators\Po::toFile($translations, $params['po']);
                        }
                    }
                }
                
                $po = Gettext\Extractors\Po::fromFile($params['po']);
                return array(
                    'ok' => true, 
                    'previous' => $prev,
                    'items' => $po->count()
                );
            } else {
                return array('ok' => false, 'msg' => __('PO file is not writable.'));
            }
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
                "package" => 'core',
                "description" => __("Scan a folder to find text in gettext functions: __(), __e(), n__(), n__e(), p__(), p__e(). This explore all PHP, HTML, HTM and JS files, HTML and HTM files will be explored how a PHP file, please, don't insert JavaScript in it."), 
                "parameters" => array(
                    'path' => __('Root file path to scan, relative to Pharinix root folder.'),
                    'language' => __('Language code of the file.'),
                    "projectIdVersion" => __('Translated software version.'),
                    "reportMsgidBugsTo" => __('Contact information to report translation bugs.'),
                    "lastTranslator" => __('Last translator name and mail.'),
                    "languageTeam" => __('Language team.'),
                    'po' => __('PO file where write results.'),
                ), 
                "response" => array(
                    'previous' => __('Number of items before scan.'),
                    'items' => __('Number of items after scan.')
                ),
                "type" => array(
                    "parameters" => array(
                        'path' => 'string',
                        'language' => 'string',
                        "projectIdVersion" => 'string',
                        "reportMsgidBugsTo" => 'string',
                        "lastTranslator" => 'string',
                        "languageTeam" => 'string',
                        'po' => 'string',
                    ), 
                    "response" => array(
                        'previous' => 'string',
                        'items' => 'string'
                    ),
                ),
                "echo" => false
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
return new commandGettextExtract();