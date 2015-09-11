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

if (!class_exists("commandTemplateEditor")) {
    class commandTemplateEditor extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            // Secure access to _POST
            $globalParams = driverCommand::getPOSTParams($_POST);
?>
    <link href="<?php echo CMS_DEFAULT_URL_BASE; ?>usr/bootstrap-grid-edit/css/jquery.gridmanager.css" rel="stylesheet">
    
    <script src="<?php echo CMS_DEFAULT_URL_BASE; ?>usr/jquery/1.11.2/jquery-ui.js"></script>
    <script src="<?php echo CMS_DEFAULT_URL_BASE; ?>usr/bootstrap-grid-edit/js/jquery.gridmanager.js"></script>
    <style>
        #footer .gm-colSettingsID, #footer .gm-rowSettingsID {color:#000;}
    </style>
    <!-- Form Name -->
    <legend><?php __e('Template editor'); ?></legend>

    

<div class="col-md-8">
    <div class="help-block"><?php __e("With this editor you can define the page distribution, In it you can define spaces to put one or more blocks, with help of commands. If you like a footer, you only need define her ID to 'footer', to define duplicate contents can put the some ID to two or more columns. If not ID is defined the column not can get blocks on it."); ?></div>
<div class="help-block"><?php __e('To start creating the template press any of the numeric buttons.'); ?></div>
    <!--<div class="container">-->
        <div id="template">
<?php
    $tpl = array(
        "name" => "",
        "title" => "Pharinix",
        "head" => '<script src="usr/jquery/1.11.1/jquery.min.js" type="text/javascript"></script>
<script src="usr/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<!-- Bootstrap -->
<link href="usr/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
<link href="etc/templates/pharinix/general.css" rel="stylesheet"/>
<link rel="shortcut icon" href="etc/templates/pharinix/favicon.ico" />',
        "body" => "",
            );
    if (isset($globalParams["selectTemplate"])) {
        // Load meta form.
        $tpl = driverCommand::run("templateToArray", array("template" => $globalParams["selectTemplate"]));
        echo $tpl["body"];
    }
?>
        </div> <!-- /#template -->
    <!--</div>  /.container -->
</div>
    <script>
    $(document).ready(function() {
        $("#template").gridmanager({
                    debug: 0,
                    cssInclude: "<?php echo CMS_DEFAULT_URL_BASE; ?>usr/bootstrap-grid-edit/fonts/font-awesome.min.css",
                    colSelectEnabled: false,
                    editableRegionEnabled: false,
                    rowCustomClasses: [],
                    colCustomClasses: [],
                    controlAppend: "<div class='btn-group pull-right'><button title='<?php __e('Edit Source Code'); ?>' type='button' class='btn btn-xs btn-primary gm-edit-mode'><span class='fa fa-code'></span></button><button title='<?php __e('Preview'); ?>' type='button' class='btn btn-xs btn-primary gm-preview'><span class='fa fa-eye'></span></button>     <div class='dropdown pull-right gm-layout-mode'><button type='button' class='btn btn-xs btn-primary dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button> <ul class='dropdown-menu' role='menu'><li><a data-width='auto' title='<?php __e('Desktop');?>'><span class='fa fa-desktop'></span> <?php __e('Desktop'); ?></a></li><li><a title='<?php __e('Tablet'); ?>' data-width='768'><span class='fa fa-tablet'></span> <?php __e('Tablet'); ?></a></li><li><a title='<?php __e('Phone'); ?>' data-width='640'><span class='fa fa-mobile-phone'></span> <?php __e('Phone'); ?></a></li></ul></div>    <button type='button' class='btn  btn-xs  btn-primary dropdown-toggle' data-toggle='dropdown'><span class='caret'></span><span class='sr-only'><?php __e('Toggle Dropdown'); ?></span></button><ul class='dropdown-menu' role='menu'><li><a title='<?php __e('Reset Grid'); ?>' href='#' class='gm-resetgrid'><span class='fa fa-trash-o'></span> <?php __e('Reset'); ?></a></li></ul></div>",
                });
        var gm = $("#template").data('gridmanager');
        gm.originalCreateRow = gm.createRow;
        gm.originalCreateCol = gm.createCol;
//        gm.oldSwitchLayoutMode = gm.switchLayoutMode;
//        gm.switchLayoutMode = function(mode) {
//            console.log(mode);
//        }
        gm.rowCount = 0;
        gm.createRow = function(colWidths) {
            var resp = gm.originalCreateRow(colWidths);
            resp.attr("id", "row"+(++this.rowCount));
            resp.attr("tpltype", "row");
            return resp;
        };
        gm.colCount = 0;
        gm.createCol = function(size) {
            var resp = gm.originalCreateCol(size);
            resp.attr("id", "col"+(++this.colCount));
            resp.attr("tpltype", "col");
            return resp;
        };
        
        $("#btnSave").on("click", function(){
            var name = $("#tplName").val();
            if (name == "") {
                alert("<?php __e('This template need a name.');?>");
            } else {
                gm.deinitCanvas();
                var canvas=gm.$el.find("#" + gm.options.canvasId);
                var tpl = canvas.html().trim();
                if (tpl == "") {
                    alert("<?php __e('This template is empty.'); ?>");
                } else {
                    $.ajax({
                        type: "POST",
                        url:  "<?php echo CMS_DEFAULT_URL_BASE; ?>",
                        data: {
                            command: "templateEditorSave",
                            interface: 0,
                            tpl: window.btoa("<tpl>"+tpl+"</tpl>"),
                            name: name,
                            title: $("#tplTitle").val(),
                            head: window.btoa($("#txtHead").val()),
                        }
                    });
                }
                gm.initCanvas();
            }
        });
        // Load list of templates
        var loadTemplateList = function() {
            $("#selectTemplate").empty();
            $.ajax({
                type: "POST",
                url:  "<?php echo CMS_DEFAULT_URL_BASE; ?>",
                data: {
                    command: "templateEditorList",
                    interface: "toJson",

                }
            }).done(function ( data ) {
                var opts = "";
                $("#selectTemplate").append('<option></option>');
                $.each(data, function(i, item){
                    $("#selectTemplate").append('<option>'+item+'</option>');
                });
            });
        }
        loadTemplateList();
    });
    </script>
<div class="col-md-4">
    <div class="form-horizontal">
                <fieldset>
                    <legend><?php __e('Meta'); ?></legend>
                    <!-- Text input-->
                    <div class="form-group required-control">
                        <label class="col-md-3 control-label" for="tplName"><?php __e('Template name'); ?></label>
                        <div class="col-md-9">
                            <input id="tplName" name="tplName" type="text" placeholder="<?php __e('name'); ?>" class="form-control " required="" value="<?php echo $tpl["name"]; ?>">
                        </div>
                    </div>
                    
                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="tplTitle"><?php __e('Default title'); ?></label>
                        <div class="col-md-9">
                            <input id="tplTitle" name="tplTitle" type="text" placeholder="<?php __e('Title'); ?>" class="form-control "  value="<?php echo $tpl["title"]; ?>">
                        </div>
                    </div>
                    
                    <!-- Textarea -->
                    <div class="form-group">
                      <label class="col-md-3 control-label" for="txtHead"><?php __e('Head includes'); ?></label>
                      <div class="col-md-9">
                          <textarea id="txtHead" name="txtHead" class="form-control"><?php echo $tpl["head"]; ?></textarea>
                          <div class="help-block"><?php __e('The default values are required, You don\'t must change this if you don\'t know how.'); ?></div>
                      </div>
                    </div>

                    <!-- Button -->
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="singlebutton"></label>
                        <div class="col-lg-9">
                            <button type="button" id="btnSave" name="singlebutton" class="btn btn-success"><span class="glyphicon glyphicon-cloud-upload" aria-hidden="true"></span> <?php __e('Save'); ?></button>
                        </div>
                    </div>

                </fieldset>
    </div>
    <form class="form-horizontal" role="form" action="" method="post" enctype="application/x-www-form-urlencoded">
                    <fieldset>
                        
                        <!-- Form Name -->
                        <legend><?php __e('Load template'); ?></legend>

                        <!-- Select Basic -->
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="selectTemplate"><?php __e('Load template'); ?></label>
                            <div class="col-md-9">
                                <select id="selectTemplate" name="selectTemplate" class="form-control "></select>
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="cmdLoad"></label>
                            <div class="col-lg-9">
                                <button id="cmdLoad" name="cmdLoad" class="btn btn-warning"><span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span> <?php __e('Load'); ?></button>
                                <div class="help-block"><?php __e('Changes will be loosed.'); ?></div>
                            </div>
                        </div>

                    </fieldset>
                </form>
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
                "description" => __("Show template grid editor."), 
                "parameters" => array(), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(), 
                    "response" => array(),
                ),
                "echo" => true
            );
        }
    }
}
return new commandTemplateEditor();