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
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}
if (!class_exists("commandExecuteCommandOnline")) {

    class commandExecuteCommandOnline extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            ?>
            <form class="form-horizontal" role="form" action="." method="post" enctype="application/x-www-form-urlencoded">
                <fieldset>

                    <!-- Form Name -->
                    <legend><?php __e('Direct command'); ?></legend>

                    <!-- Text input-->
                    <div class="form-group">
                        <input type="hidden" name="interface" value="1">
                        <label class="col-md-4 control-label" for="cmd"><?php __e('Command'); ?></label>
                        <div class="col-md-5">
                            <input id="cmd" name="cmd" type="text" placeholder="nothing" class="form-control input-md" required="">
                            <span class="help-block"><?php __e('Command to execute'); ?></span>
                        </div>
                    </div>

                    <!-- Textarea -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="post"><?php __e('POST\'s parameters'); ?></label>
                        <div class="col-md-4">
                            <textarea class="form-control" id="post" name="post"></textarea>
                            <span class="help-block"><?php __e('Post\'s parameters encoded as string.'); ?></span>
                        </div>
                    </div>

                    <!-- Button -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="submit"></label>
                        <div class="col-md-4">
                            <button id="submit" name="submit" class="btn btn-primary"><?php __e('Execute'); ?></button>
                        </div>
                    </div>
                    <a href="help/command" class="btn btn-xs btn-info" target="_blank">
                        <span class="glyphicon glyphicon-info-sign"></span> <?php __e('Command\'s help'); ?>
                    </a>
                </fieldset>
            </form>
            <?php
            if (isset($_POST["post"])) {
                echo '<label class="col-md-4 control-label">'.__("Executed").':</label>';
                echo "<pre>";
                var_dump($_POST);
                echo "</pre>";
                $aux = array();
                parse_str($_POST["post"], $aux);
                $resp = driverCommand::run($_POST["cmd"], $aux);
                echo '<label class="col-md-4 control-label">'.__("Response").':</label>';
                echo "<pre>";
                var_dump($resp);
                echo "</pre>";
            }
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
                "description" => __("Show a form to define the command to execute."),
                "parameters" => array("post" => __("Post's parameters encoded as string.")),
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "post" => "string"
                    ), 
                    "response" => array(),
                )
            );
        }

    }

}
return new commandExecuteCommandOnline();
