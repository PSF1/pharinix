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

/*
 * Print trace information in HTML format
 */
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

if (!class_exists("commandTraceToHTML")) {

    class commandTraceToHTML extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            global $output;
            ?>
            <div class="row">
                <div class="col-sm-12">
                    <?php
                    echo "<h3>".__("Trace information")."</h3>";
                    self::parseTrace("trace", $output["trace"]);
                    ?>
                </div>
            </div>
            <?php
        }

        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Print trace information in HTML format"),
                "parameters" => array(),
                "response" => array(),
                "type" => array(
                    "parameters" => array(), 
                    "response" => array(),
                )
            );
        }

        public static function parseTrace($key, $value) {
            ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php __e('Key'); ?></th>
                        <th><?php __e('Value'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    echo "<tr><td>";
                    echo "<br>$key</b>";
                    echo "</td><td>";
                    if (is_array($value)) {
                        foreach ($value as $key1 => $value1) {
                            self::parseTrace($key1, $value1);
                        }
                    } else {
                        echo $value;
                    }
                    echo "</td></tr>";
                    ?>
                </tbody>
            </table>
            <?php
        }

    }

}
return new commandTraceToHTML();