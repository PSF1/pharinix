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
if (!class_exists("commandManHelpOnline")) {

    class commandManHelpOnline extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            ?>
            <form class="form-horizontal" id="manHelpOnline" role="form">
                <fieldset>

                    <!-- Form Name -->
                    <legend>Command help</legend>

                    <!-- Select Basic -->
                    <div class="form-group">
                      <label class="col-md-4 control-label" for="selectCmd">Command</label>
                      <div class="col-md-5">
                        <select id="selectCmd" name="selectCmd" class="form-control ">
                            <?php
                            $cmds = driverCommand::run("getCommandList");
                            foreach($cmds["commands"] as $cmd) {
                                echo "<option>$cmd</option>";
                            }
                            ?>
                        </select>
                      </div>
                    </div>
                    
                    <!-- Button -->
                    <div class="form-group">
                        <label class="col-md-4 control-label"></label>
                        <div class="col-md-4">
                            <button id="getHelp" class="btn btn-primary">Help</button>
                        </div>
                    </div>
                </fieldset>
            </form>
            <div class="row">
                <div class="col-md-12" id="manHelpBlock">
                    
                </div>
            </div>
            <?php
            $burl = CMS_DEFAULT_URL_BASE;
            $script = <<<EOT
$(document).ready(function(){
        $("#getHelp").click(function(e){
            $("#manHelpBlock").empty();
            $.ajax({
                method: "POST",
                url: "$burl",
                data: { 
                    command: "manHTML",
                    cmd: $("#selectCmd").val(),
                    interface: "nothing"
                }
            })
             .done(function( msg ) {
                $("#manHelpBlock").html(msg);
            });
            return false;
        });
   });
EOT;
            if ($_POST["interface"] == "echoHtml") {
                $reg = &self::getRegister("customscripts");
                $reg .= $script;
            } else {
                echo "<script>$script</script>";
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
                "description" => "Show a form to get help about a command.",
                "parameters" => array(),
                "response" => array()
            );
        }

    }

}
return new commandManHelpOnline();
