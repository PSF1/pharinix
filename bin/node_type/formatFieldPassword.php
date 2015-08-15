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

if (!class_exists("commandFormatFieldPassword")) {
    class commandFormatFieldPassword extends driverCommand {
        protected static $firstPasswordWrite = true;
        
        public static function runMe(&$params, $debug = true) {
            $p = array_merge(array(
                    "fieldname" => "",
                    "toread" => false,
                    "towrite" => false,
                    "value" => "",
                    "length" => 0,
                    "required" => false,
                    "readonly" => false,
                    "system" => false,
                    "multivalued" => false,
                    "default" => "",
                    "label" => "",
                    "help" => "",
                ), $params);
            
            if ($p["toread"] == $p["towrite"]) {
                echo self::getAlert(__("Object of call must be read or write."));
            } else {
                if ($p["multivalued"]) {
                    // Basic types dont have multivalue.
                } else {
                    if ($p["toread"] || $p["readonly"]) { // to read
                        echo '<!-- Field "'.$p["fieldname"].'" -->';
                        echo '<div class="col-md-12 col-sm-12 col-xs-12">';
                        echo '<div class="form-group">';
                        echo '<label class="control-label" for="'.$p["fieldname"].'">';
                        echo $p["label"];
                        echo '</label>';
                        echo "<div class=\"\">*******</div>";
                        echo '</div>';
                        echo '</div>';
                    } else { // to write
                        echo '<!-- Field "'.$p["fieldname"].'" -->';
                        echo '<div class="col-md-12 col-sm-12 col-xs-12">';
                        echo '<div class="form-group">';
                        echo '<label class="control-label" for="'.$p["fieldname"].'">';
                        echo $p["label"];
                        if ($p["required"]) {
                            echo '&nbsp;<span class="glyphicon glyphicon-asterisk text-danger" aria-hidden="true"></span>';
                        }
                        echo '</label>';
                        echo '<input id="'.$p["fieldname"].'" name="'.$p["fieldname"].
                                '" type="password" placeholder="'.__('Password').'" '.
                                'class="form-control " '.($p["required"]?"required":"").
                                ' value="'.$p["value"].'"'.
                                'data-toggle="popover" '.
                                'title="Password Strength" data-content="'.__('Enter Password...').'">';
                        echo "<div class=\"help help-block\">".$p["help"]."</div>";
                        echo '</div>';
                        echo '</div>';
                        if (self::$firstPasswordWrite) {
                            $reg = &self::getRegister("customcss");
                            $reg .= <<<EOT
.popover.primary {
    border-color:#337ab7;
}
.popover.primary>.arrow {
    border-top-color:#337ab7;
}
.popover.primary>.popover-title {
    color:#fff;
    background-color:#337ab7;
    border-color:#337ab7;
}
.popover.success {
    border-color:#d6e9c6;
}
.popover.success>.arrow {
    border-top-color:#d6e9c6;
}
.popover.success>.popover-title {
    color:#3c763d;
    background-color:#dff0d8;
    border-color:#d6e9c6;
}
.popover.info {
    border-color:#bce8f1;
}
.popover.info>.arrow {
    border-top-color:#bce8f1;
}
.popover.info>.popover-title {
    color:#31708f;
    background-color:#d9edf7;
    border-color:#bce8f1;
}
.popover.warning {
    border-color:#faebcc;
}
.popover.warning>.arrow {
    border-top-color:#faebcc;
}
.popover.warning>.popover-title {
    color:#8a6d3b;
    background-color:#fcf8e3;
    border-color:#faebcc;
}
.popover.danger {
    border-color:#ebccd1;
}
.popover.danger>.arrow {
    border-top-color:#ebccd1;
}
.popover.danger>.popover-title {
    color:#a94442;
    background-color:#f2dede;
    border-color:#ebccd1;
}
EOT;
                            $reg = &self::getRegister("customscripts");
                            $VeryStrong = 'Very Strong';
                            $Strong = 'Strong';
                            $AlmostStrong = 'Almost Strong';
                            $Weak = 'Weak';
                            $VeryWeak = 'Very Weak';
                            $reg .= <<<EOT
$(document).ready(function(){

//minimum 8 characters
var bad = /(?=.{8,}).*/;
//Alpha Numeric plus minimum 8
var good = /^(?=\S*?[a-z])(?=\S*?[0-9])\S{8,}$/;
//Must contain at least one upper case letter, one lower case letter and (one number OR one special char).
var better = /^(?=\S*?[A-Z])(?=\S*?[a-z])((?=\S*?[0-9])|(?=\S*?[^\w\*]))\S{8,}$/;
//Must contain at least one upper case letter, one lower case letter and (one number AND one special char).
var best = /^(?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[^\w\*])\S{8,}$/;

$('#{$p["fieldname"]}').on('keyup', function () {
    var password = $(this);
    var pass = password.val();
    var passLabel = $('[for="password"]');
    var stength = 'Weak';
    var pclass = 'danger';
    if (best.test(pass) == true) {
        stength = '{$VeryStrong}';
        pclass = 'success';
    } else if (better.test(pass) == true) {
        stength = '{$Strong}';
        pclass = 'warning';
    } else if (good.test(pass) == true) {
        stength = '{$AlmostStrong}';
        pclass = 'warning';
    } else if (bad.test(pass) == true) {
        stength = '{$Weak}';
    } else {
        stength = '{$VeryWeak}';
    }

    var popover = password.attr('data-content', stength).data('bs.popover');
    popover.setContent();
    popover.\$tip.addClass(popover.options.placement).removeClass('danger success info warning primary').addClass(pclass);

});

$('input[data-toggle="popover"]').popover({
    placement: 'top',
    trigger: 'focus'
});

});
EOT;
                        self::$firstPasswordWrite = false;
                        }
                    }
                }
                
            }
        }

        public static function getHelp() {
            return array(
                "description" => __("Format password field to read or write."), 
                "parameters" => array(
                    "fieldname" => __("Field name to the form control."),
                    "toread" => __("Caller need a read form."),
                    "towrite" => __("Caller need a write form."),
                    "value" => __("Field value."),
                    "length" => __("Field max length."),
                    "required" => __("Is a required field."),
                    "readonly" => __("Is a read only field."),
                    "system" => __("Is a system field, it isn't allow write."),
                    "multivalued" => __("Is a multi valued field."),
                    "default" => __("Default value."),
                    "label" => __("Label."),
                    "help" => __("Help to write forms."),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "fieldname" => "string",
                        "toread" => "boolean",
                        "towrite" => "boolean",
                        "value" => "string",
                        "length" => "integer",
                        "required" => "boolean",
                        "readonly" => "boolean",
                        "system" => "boolean",
                        "multivalued" => "boolean",
                        "default" => "string",
                        "label" => "string",
                        "help" => "string",
                    ), 
                    "response" => array(),
                )
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
return new commandFormatFieldPassword();