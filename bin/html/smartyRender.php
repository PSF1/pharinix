<?php

/* 
 * Pharinix Copyright (C) 2016 Pedro Pelaez <aaaaa976@gmail.com>
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

if (!class_exists("commandSmartyRender")) {
    class commandSmartyRender extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "page" => '',
                "tpl" => 'index.tpl',
            ), $params);
            
            if (!defined('SMARTY_DIR')) {
                define('SMARTY_DIR', getcwd().'/etc/smarty/libs/');
            }
            require_once SMARTY_DIR.'Smarty.class.php';
            
            $context = &driverCommand::getRegister("url_context");
            $def = driverPages::getPage($params["page"]);
            $smarty = new Smarty;

            $smarty->force_compile = driverConfig::getCFG()
                    ->getSection('[core]')
                    ->getAsBoolean('CMS_ALWAYS_COMPILE_TEMPLATE');
            $smarty->debugging = driverConfig::getCFG()
                    ->getSection('[core]')
                    ->getAsBoolean('CMS_DEBUG_TEMPLATE');
            //@TODO: Allow set this by configuration file.
            $smarty->caching = true;
            $smarty->cache_lifetime = 120;
            //@TODO: Allow replace theme dir, but if not found the required TPL use the default one.
            $smarty->template_dir = getcwd().'/etc/templates/smarty/';
            $smarty->config_dir = getcwd().'/var/smarty/configs/';
            // Autocreated folders of work
            $smarty->compile_dir = getcwd().'/var/smarty/templates_c/';
            $smarty->cache_dir = getcwd().'/var/smarty/cache/';
            //@TODO: Render native page using TPL files how base.
            $page_title = driverConfig::getCFG()->getSection('[core]')->get('CMS_TITLE');
            $smarty->assign("base_url", CMS_DEFAULT_URL_BASE);
            $smarty->assign("page_title", $page_title, true);
            $smarty->assign("page_charset", 'utf-8');
            $user_language = driverUser::getLangOfUser();
            $smarty->assign("user_language", $user_language[0], true);
            if (!$def->EOF) {
                if (!empty($page_title) && !empty($def->fields['title'])) {
                    $page_title = ' :: '.$page_title;
                }
                $smarty->assign("page_title", $def->fields['title'].$page_title, true);
                $block = array();
                $sql = "SELECT * FROM `page-blocks` where idpage = {$def->fields['id']} || idpage = 0 order by idcol";
                $b = dbConn::get()->Execute($sql);
                while (!$b->EOF) {
                    var_dump($b->fields);
                    $b->MoveNext();
                }
                $smarty->assign("block", $block, true);
            }
            //@TODO: Add all available contextual variables.
            $smarty->assign("url_context", $context, true);
            $smarty->assign("customscripts", self::getRegister("customscripts"), true);
            $smarty->assign("customcss", self::getRegister("customcss"), true);
            // Hook
            $tpl = $params['tpl']; // Render the correct template.
            driverHook::CallHook('smartyRenderBeforeDisplay', array(
                        'page' => $params['page'],
                        'smarty' => &$smarty,
                        'tpl' => &$tpl,
                    ));
            
            $smarty->display($tpl);
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Render a page using Smarty engine."), 
                "parameters" => array(
                    "page" => __("Page to convert, see 'url_rewrite' table."),
                    "tpl" => __("Template file to render, Default file index.tpl."),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "page" => "string",
                        "tpl" => "string",
                    ), 
                    "response" => array(),
                ),
                "echo" => true,
                "interface" => false,
                "hooks" => array(
                        array(
                            "name" => "smartyRenderBeforeDisplay",
                            "description" => __("Allow change configuration of the Smarty instance before render the template."),
                            "parameters" => array(
                                'page' => __("Readonly, rendered page ID."),
                                'smarty' => __("Configured Smarty instance before display the template."),
                                'tpl' => __("Template file to render."),
                            )
                        ),
                    ),
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
return new commandSmartyRender();