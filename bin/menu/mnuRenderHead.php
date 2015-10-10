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

if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

if (!class_exists("commandMnuRenderHead")) {

    class commandMnuRenderHead extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "menu" => ''
            ), $params);
            if (!is_numeric($params['menu'])) {
                $parent = driverCommand::run('getNodes', array(
                    'nodetype' => 'menu',
                    'fields' => 'id',
                    'where' => "`slugname` = '{$params['menu']}'",
                ));
                if (count($parent) > 0) {
                    foreach($parent as $key => $what) {
                        $params['menu'] = $key;
                        break;
                    }
                } else {
                    echo driverCommand::getAlert(
                        sprintf(__('mnuRenderHead error: menu \'%s\' not found.'), $params['menu'])
                     );
                    return;
                }
            }
            $menu = driverCommand::run('getNodes', array(
                'nodetype' => 'menu',
                'where' => '`id` = \''.$params['menu'].'\'',
            ));
            if (count($menu) <= 0) {
                echo driverCommand::getAlert(
                        sprintf(__('mnuRenderHead error: menu \'%s\' not found.'), $params['menu'])
                     );
                return;
            }
            
            foreach($menu as $mnu) {
                if (!self::ICanShow($mnu)) {
                    return;
                }
                break;
            }
            echo '<style>body {padding-top: 61px;}</style>';
            echo '<div id="top">';
            echo '<nav class="navbar navbar-fixed-top navbar-default ">';
            echo '<div class="container-fluid">';
            
            
?>
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only"><?php echo __('Toggle navigation'); ?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
<?php
        // BRAND
        $brands = driverCommand::run('getNodes', array(
                'nodetype' => 'menu',
                'where' => '`parent` = \''.$params['menu'].'\' && `isbrand` = \'1\'',
                'order' => '`order`',
        ));
        foreach($brands as $brand) {
            if (self::ICanShow($brand)) {
                echo self::renderMenu($brand);
            }
        }
?>
    </div>
<?php
            echo '<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">';
            // LEFT
            $lefts = driverCommand::run('getNodes', array(
                    'nodetype' => 'menu',
                    'where' => '`parent` = \''.$params['menu'].'\' && `isbrand` = \'0\' && `aling` <> \'right\'',
                    'order' => '`order`',
            ));
            echo '<ul class="nav navbar-nav">';
            foreach($lefts as $left) {
                if (self::ICanShow($left)) {
                    if ($left['title'] == '-') {
                        echo '<li class="divider">';
                    } else {
                        echo '<li>';
                        echo self::renderMenu($left);
                    }
                    echo '</li>';
                }
            }
            echo '</ul>';
            // RIGHT
            $rights = driverCommand::run('getNodes', array(
                    'nodetype' => 'menu',
                    'where' => '`parent` = \''.$params['menu'].'\' && `isbrand` = \'0\' && `aling` = \'right\'',
                    'order' => '`order`',
            ));
            echo '<ul class="nav navbar-nav navbar-right">';
            foreach($rights as $right) {
                if (self::ICanShow($right)) {
                    if ($right['title'] == '-') {
                        echo '<li class="divider">';
                    } else {
                        echo '<li>';
                        echo self::renderMenu($right);
                    }
                    echo '</li>';
                }
            }
            echo '</ul>';
            echo '</div>';
            echo '</div>';
            echo '</nav>';
            echo '</div>';
?>
<script>
$(document).ready(function(){
    $('.menuInlineToHTMLReloadURL').each(function(i, item){
        $(this).val(PHARINIX_CURRENT_PATH);
    });
});

</script>
<?php
        }

        public static function ICanShow($menu) {
            if ($menu['active'] != '1') return false;
            if ($menu['isnotloged'] == '1' && !driverUser::isLoged()) return true;
            if ($menu['issudoed'] == '1' && driverUser::isSudoed()) return true;
            if ($menu['isnotsudoed'] == '1' && driverUser::isLoged() && !driverUser::isSudoed()) return true;
            if ($menu['isloged'] == '1' && driverUser::isLoged()) return true;
            if ($menu['havegroup'] != '' && driverUser::haveGroup($menu['havegroup'])) return true;
            return false;
        }
        
        public static function renderMenu($menu) {
            $resp = '';
            if ($menu['linkto'] != '') {
                $aclass = '';
                if ($menu['isbrand'] == '1') {
                    $aclass = 'navbar-brand';
                }
                if ($aclass != '') {
                    $aclass = 'class="'.$aclass.'"';
                }
                $resp .= '<a '.$aclass.' href="'.self::formatURL($menu['linkto']).'">';
                if ($menu['icon'] != '') {
                    $resp .= '<span class="'.$menu['icon'].'" aria-hidden="true"></span>';
                }
                $resp .= '&nbsp;'.__($menu['title']);
                $resp .= '</a>';
            } else if ($menu['onlyparent'] == '1') {
                $resp .= '<li class="dropdown">';
                $resp .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">';
                if ($menu['icon'] != '') {
                  $resp .= '<span class="'.$menu['icon'].'" aria-hidden="true"></span>';
                }
                  $resp .= '&nbsp;'.__($menu['title']).' <span class="caret"></span>';
                $resp .= '</a>';
                $resp .= '<ul class="dropdown-menu" role="menu">';
                      // Render submenus
                      $rights = driverCommand::run('getNodes', array(
                            'nodetype' => 'menu',
                            'where' => '`parent` = \'' . $menu['id'] . '\' && `isbrand` = \'0\'',
                            'order' => '`order`',
                        ));
                        foreach ($rights as $right) {
                            if (self::ICanShow($right)) {
                                if ($right['title'] == '-') {
                                    $resp .= '<li class="divider">';
                                } else {
                                    $resp .= '<li>';
                                    $resp .= self::renderMenu($right);
                                }
                                $resp .= '</li>';
                            }
                        }
                $resp .= '</ul>';
                $resp .= '</li>';
            } else if ($menu['cmd'] != '') {
                $pars = array();
                parse_str($menu['params'], $pars);
                ob_start();
                driverCommand::run($menu['cmd'], $pars);
                $resp = ob_get_clean();
            } else {
                $resp = __($menu['title']);
            }
            return $resp;
        }
        
        public static function formatURL($url) {
            $resp = $url;
            if (!driverTools::str_start('http', $url)) {
                if (driverTools::str_start('/', $url)) {
                    $url = substr($url, -1, strlen($url) - 1);
                }
                $resp = CMS_DEFAULT_URL_BASE.$url;
            }
            return $resp;
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __('Render a menu in HTML how inline navigation bar'),
                "parameters" => array(
                    "menu" => __("Menu ID, slugname, to render.")
                    ),
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "menu" => "string"
                    ), 
                    "response" => array(),
                ),
                "echo" => true
            );
        }

    }

}
return new commandMnuRenderHead();
