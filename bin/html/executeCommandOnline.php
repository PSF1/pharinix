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
// TODO: SECURITY !!
if (!class_exists("commandExecuteCommandOnline")) {

    class commandExecuteCommandOnline extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            ?>
            <form class="form-horizontal" role="form" action="." method="post" enctype="application/x-www-form-urlencoded">
                <fieldset>

                    <!-- Form Name -->
                    <legend>Direct command</legend>

                    <!-- Text input-->
                    <div class="form-group">
                        <input type="hidden" name="interface" value="1">
                        <label class="col-md-4 control-label" for="cmd">Command</label>
                        <div class="col-md-5">
                            <input id="cmd" name="cmd" type="text" placeholder="nothing" class="form-control input-md" required="">
                            <span class="help-block">Command to execute</span>
                        </div>
                    </div>

                    <!-- Textarea -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="post">POST's parameters</label>
                        <div class="col-md-4">
                            <textarea class="form-control" id="post" name="post"></textarea>
                            <span class="help-block">Post's parameters encoded as string.</span>
                        </div>
                    </div>

                    <!-- Button -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="submit"></label>
                        <div class="col-md-4">
                            <button id="submit" name="submit" class="btn btn-primary">Execute</button>
                        </div>
                    </div>
                    <a href="help/command" class="btn btn-xs btn-info" target="_blank">
                        <span class="glyphicon glyphicon-info-sign"></span> Command's help
                    </a>
                </fieldset>
            </form>
            <?php
            if (isset($_POST["post"])) {
                echo '<label class="col-md-4 control-label">Order detected:</label>';
                var_dump($_POST);
                $aux = array();
                parse_str($_POST["post"], $aux);
                $resp = driverCommand::run($_POST["cmd"], $aux);
                echo '<label class="col-md-4 control-label">Response:</label>';
                var_dump($resp);
            }
        }

        public static function getHelp() {
            return array(
                "description" => "Show a form to define the command to execute.",
                "parameters" => array("post" => "Post's parameters encoded as string."),
                "response" => array()
            );
        }

    }

}
return new commandExecuteCommandOnline();
