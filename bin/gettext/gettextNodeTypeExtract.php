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

if (!class_exists("commandGettextNodeTypeExtract")) {
    class commandGettextNodeTypeExtract extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'nodetype' => '',
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
            
            if ($params['nodetype'] == '') {
                return array('ok' => false, 'msg' => __('Node type is required.'));
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
                $po = Gettext\Extractors\Po::fromFile($params['po']);
                $po->setHeader('Project-Id-Version', $params['projectIdVersion']);
                $po->setHeader('Report-Msgid-Bugs-To', $params['reportMsgidBugsTo']);
                $po->setHeader('Last-Translator', $params['lastTranslator']);
                $po->setHeader('Language-Team', $params['languageTeam']);
                $prev = $po->count();
                
                $translations = new Gettext\Translations();
                // Node type fields
                $nodedef = driverCommand::run('getNodeTypeDef', array(
                    'nodetype' => $params['nodetype']
                ));
                if ($nodedef['id'] !== false) {
                    foreach ($nodedef['fields'] as $field) {
                        // Label
                        $original = $field['label'];
                        if ($original !== '') {
                            $translation = $translations->insert('', $original);
                            $translation->addReference('NodeType_'.$params['nodetype'], 'field_'.$field['name'].'_label');
                        }
                        // Help
                        $original = $field['help'];
                        if ($original !== '') {
                            $translation = $translations->insert('', $original);
                            $translation->addReference('NodeType_'.$params['nodetype'], 'field_'.$field['name'].'_help');
                        }
                    }
                }
                $translations->mergeWith($po);
                
                Gettext\Generators\Po::toFile($translations, $params['po']);
                
                return array(
                    'ok' => true, 
                    'previous' => $prev,
                    'items' => $translations->count()
                );
            } else {
                return array('ok' => false, 'msg' => __('PO file is not writable.'));
            }
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Scan a node type to find text in labels and help literals."), 
                "parameters" => array(
                    'nodetype' => __('Node type to explore.'),
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
                        'nodetype' => 'string',
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
return new commandGettextNodeTypeExtract();