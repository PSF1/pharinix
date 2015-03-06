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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandPageEditor")) {
    class commandPageEditor extends driverCommand {

        public static function runMe(&$params, $debug = true) {
?>
    <link href="<?php echo CMS_DEFAULT_URL_BASE; ?>libs/bootstrap-grid-edit/css/jquery.gridmanager.css" rel="stylesheet">
    
    <script src="<?php echo CMS_DEFAULT_URL_BASE; ?>libs/jquery/1.11.2/jquery-ui.js"></script>
    <script src="<?php echo CMS_DEFAULT_URL_BASE; ?>libs/bootstrap-grid-edit/js/jquery.gridmanager.js"></script>
    <!-- Form Name -->
    <legend>Template editor</legend>

    <div class="container">
        <div id="template">

        </div> <!-- /#template -->
    </div> <!-- /.container -->
    <script>
    $(document).ready(function() {
        $("#template").gridmanager({
                    debug: 0,
                    cssInclude: "<?php echo CMS_DEFAULT_URL_BASE; ?>libs/bootstrap-grid-edit/fonts/font-awesome.min.css",
                    rowCustomClasses: [],
                    colCustomClasses: [],
                    controlAppend: "<div class='btn-group pull-right'><button title='Preview' type='button' class='btn btn-xs btn-primary gm-preview'><span class='fa fa-eye'></span></button>     <div class='dropdown pull-left gm-layout-mode'><button type='button' class='btn btn-xs btn-primary dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button> <ul class='dropdown-menu' role='menu'><li><a data-width='auto' title='Desktop'><span class='fa fa-desktop'></span> Desktop</a></li><li><a title='Tablet' data-width='768'><span class='fa fa-tablet'></span> Tablet</a></li><li><a title='Phone' data-width='640'><span class='fa fa-mobile-phone'></span> Phone</a></li></ul></div>    <button type='button' class='btn  btn-xs  btn-primary dropdown-toggle' data-toggle='dropdown'><span class='caret'></span><span class='sr-only'>Toggle Dropdown</span></button><ul class='dropdown-menu' role='menu'><li><a title='Reset Grid' href='#' class='gm-resetgrid'><span class='fa fa-trash-o'></span> Reset</a></li></ul></div>",
                });
        var gm = $("#template").data('gridmanager');
        gm.originalCreateRow = gm.createRow;
        gm.originalCreateCol = gm.createCol;
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
                alert("This template need a name.");
            } else {
                gm.deinitCanvas();
                var canvas=gm.$el.find("#" + gm.options.canvasId);
                alert(canvas.html());
                gm.initCanvas();
            }
        });
    });
    </script>
    <div class="form-inline">
                <fieldset>

                    <!-- Text input-->
                    <div class="form-group required-control">
                        <label class="col-md-3 control-label" for="tplName">Name</label>
                        <div class="col-md-6">
                            <input id="tplName" name="tplName" type="text" placeholder="name" class="form-control " required="">

                        </div>
                    </div>

                    <!-- Button -->
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="singlebutton"></label>
                        <div class="col-lg-6">
                            <button id="btnSave" name="singlebutton" class="btn btn-success">Save</button>
                        </div>
                    </div>

                </fieldset>
    </div>
    <div class="help-block">With this editor you can define the page distribution, In it you can define spaces to put one or more blocks, with help of commands. If you like a footer, you only need define her ID to 'footer', to define duplicate contents can put the some ID to two or more columns. If not ID is defined the column not can get blocks on it.</div>
<div class="help-block">To start creating the template press any of the numeric buttons.</div>
<?php
        }

        public static function getHelp() {
            return array(
                "description" => "Show template grid editor.", 
                "parameters" => array(), 
                "response" => array()
            );
        }
    }
}
return new commandPageEditor();