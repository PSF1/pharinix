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

/*
 * Echo html
 * Parameters:
 * html = HTML code to echo.
 */
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

if (!class_exists("commandPoweredBy")) {
    class commandPoweredBy extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'align' => 'left',
            ), $params);
            
            $align = '';
            switch ($params['align']) {
                case 'right':
                    $align = 'text-right';
                    break;
                case "center":
                    $align = 'text-center';
                    break;
                default:
                    $align = 'text-left';
                    break;
            }
            echo '<div class="row">';
            echo '<div class="col-md-12">';
            echo '<div class="'.$align.'">';
            echo __('Powered by ');
            echo '<a href="https://github.com/PSF1/pharinix" target="_blank">';
            echo 'Pharinix';
            echo '</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
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
                "description" => __("Show the power by Pharinix label."), 
                "parameters" => array('align' => __('Text align: left, center, right. Default "left".')), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'align' => 'string',
                    ), 
                    "response" => array(),
                ),
                "echo" => true
            );
        }
    }
}
return new commandPoweredBy();