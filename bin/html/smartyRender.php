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
                "tpl" => 'document.tpl',
                "debug" => false,
            ), $params);
            
            if (!defined('SMARTY_DIR')) {
                define('SMARTY_DIR', getcwd().'/etc/smarty/libs/');
            }
            require_once SMARTY_DIR.'Smarty.class.php';
            include_once("etc/drivers/pages.php");
            
            $context = &driverCommand::getRegister("url_context");
            $def = driverPages::getPage($params["page"]);
            $smarty = new Smarty;

            $smarty->force_compile = driverConfig::getCFG()
                    ->getSection('[core]')
                    ->getAsBoolean('CMS_ALWAYS_COMPILE_TEMPLATE');
            $smarty->debugging = driverConfig::getCFG()
                    ->getSection('[core]')
                    ->getAsBoolean('CMS_DEBUG_TEMPLATE') || $params['debug'];
            $smarty->caching = driverConfig::getCFG()
                    ->getSection('[core]')
                    ->getAsBoolean('CMS_CACHING_TEMPLATE');
            $smarty->cache_lifetime = driverConfig::getCFG()
                    ->getSection('[core]')
                    ->get('CMS_CACHE_LIFETIME_TEMPLATE');
            $smarty->template_dir = getcwd().'/etc/templates/smarty/';
            $smarty->config_dir = getcwd().'/var/smarty/configs/';
            // Autocreated folders of work
            $smarty->compile_dir = getcwd().'/var/smarty/templates_c/';
            $smarty->cache_dir = getcwd().'/var/smarty/cache/';
            // Render native page using TPL files how base.
            $page_title = driverConfig::getCFG()->getSection('[core]')->get('CMS_TITLE');
            $smarty->assign("base_url", CMS_DEFAULT_URL_BASE);
            $smarty->assign("page_title", $page_title, true);
            $smarty->assign("page_charset", 'utf-8');
            $user_language = driverUser::getLangOfUser();
            $smarty->assign("user_language", $user_language[0], true);
            if ($def !== false) {
                if (!empty($page_title) && !empty($def->fields['title'])) {
                    $page_title = ' :: '.$page_title;
                }
                $smarty->assign("page_title", $def->fields['title'].$page_title, true);
                $block = array();
                $sql = "SELECT idcol FROM `page-blocks` where idpage = {$def->fields['id']} || idpage = 0 group by idcol";
                $b = dbConn::get()->Execute($sql);
                while (!$b->EOF) {
                    $cmd = driverPages::getCommands($def->fields['id'], $b->fields['idcol']);
                    ob_start();
                    while ($cmd !== false && !$cmd->EOF) {
                        $cmdParams = array();
                        // Change URL context variables in parameters
                        $context = &driverCommand::getRegister("url_context");
                        $rawParams = driverUrlRewrite::mapReplace($context, $cmd->fields["parameters"]);
                        parse_str($rawParams, $cmdParams);
                        if (driverPages::showAreas()) {
                            $iParams = ' ()';
                            if (count($cmdParams) > 0) {
                                $iParams = str_replace("<", "&lt;", print_r($cmdParams, 1));
                                $iParams = str_replace("\t", "&nbsp;", $iParams);
                                $iParams = str_replace("\n", "<br>", $iParams);
                                $iParams = " <br>$iParams";
                            }
                            echo "<div class=\"alert alert-success\" role=\"alert\"><h6><b>" . __("Command") . "</b>: {$cmd->fields["command"]}" . $iParams . "</h6></div>";
                        }
                        driverCommand::run($cmd->fields["command"], $cmdParams);
                        $cmd->MoveNext();
                    }
                    $rendered = ob_get_clean();
                    if (!isset($block[$b->fields['idcol']])) {
                        $block[$b->fields['idcol']] = $rendered;
                        unset($rendered);
                    }
                    $b->MoveNext();
                }
                $smarty->assign("block", $block, true);
            }
            // Add all available contextual variables.
            $aux = array();
            foreach($context as $key => $ctx) {
                $aux[str_replace('$', '', $key)] = $ctx;
            }
            $smarty->assign("url_context", $aux, true);
            
            $cssFiles = &self::getRegister("filecss");
            $cssFilesStr = "";
            if ($cssFiles != null) {
                foreach($cssFiles as $cssFile) {
                    $cssFilesStr .= '<link href="'.CMS_DEFAULT_URL_BASE.$cssFile.'" rel="stylesheet" type="text/css"/>'."\n";
                }
            }
            $smarty->assign("filecss", $cssFilesStr, true);
            
            $cssFiles = &self::getRegister("filescripts");
            $cssFilesStr = "";
            if ($cssFiles != null) {
                foreach($cssFiles as $cssFile) {
                    $cssFilesStr .= '<script src="'.CMS_DEFAULT_URL_BASE.$cssFile.'"></script>'."\n";
                }
            }
            $smarty->assign("filescripts", $cssFilesStr, true);
            
            $smarty->assign("customscripts", self::getRegister("customscripts"), true);
            $smarty->assign("customcss", self::getRegister("customcss"), true);
            $auxparams = $params;
            unset($auxparams['page']);
            unset($auxparams['tpl']);
            unset($auxparams['command']);
            unset($auxparams['interface']);
            $smarty->assign("render_params", $auxparams, true);
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
                    "page" => __("Optional. Page to convert, see 'url_rewrite' table."),
                    "tpl" => __("Template file to render, Default file document.tpl."),
                    "debug" => __("Debug this template, default false."),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "page" => "string",
                        "tpl" => "string",
                        "debug" => "bool",
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
