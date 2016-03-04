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

if (!class_exists("commandFormatFieldCRUD")) {
    class commandFormatFieldCRUD extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $p = array_merge(array(
                    "fieldname" => "",
                    "toread" => false,
                    "towrite" => false,
                    "value" => 0,
                    "length" => 0,
                    "required" => false,
                    "readonly" => false,
                    "system" => false,
                    "multivalued" => false,
                    "default" => 3904,
                    "label" => __("Access"),
                    "help" => "",
                ), $params);
            
            if ($p["toread"] == $p["towrite"]) {
                echo self::getAlert(__("Object of call must be read or write."));
            } else {
                if ($p["multivalued"]) {
                    // Basic types dont have multivalue.
                } else {
                    $p['value'] = intval($p['value']);
                    if ($p["toread"] || $p["readonly"]) { // to read
                        echo '<!-- Field "'.$p["fieldname"].'" -->';
?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?php echo $p["label"]; ?>
                            </div>
                            <div class="panel-body">
<?php
                        echo '<div class="col-md-4">';
                        echo driverCommand::run('formatFieldCRUDOwner', $p);
                        echo '</div>';
                        echo '<div class="col-md-4">';
                        echo driverCommand::run('formatFieldCRUDGroup', $p);
                        echo '</div>';
                        echo '<div class="col-md-4">';
                        echo driverCommand::run('formatFieldCRUDAll', $p);
                        echo '</div>';
?>
                            </div>
                        </div>
<?php
                    } else { // to write
                        if ($p["value"] == "") {
                            $p["value"] = $p["default"];
                        }
                        
                        echo '<!-- Field "'.$p["fieldname"].'" -->';
                        echo '<div class="form-group">';
?>
                        <div class="panel panel-default" id="<?php echo $p["fieldname"]; ?>" name="<?php echo $p["fieldname"]; ?>">
                            <div class="panel-heading">
                                <?php echo $p["label"]; ?>
                            </div>
                            <div class="panel-body">
<?php
                        echo '<div class="col-md-4">';
                        $op = $p;
                        $op['fieldname'] = $op['fieldname'].'_input_owner';
                        echo driverCommand::run('formatFieldCRUDOwner', $op);
                        echo '</div>';
                        echo '<div class="col-md-4">';
                        $op = $p;
                        $op['fieldname'] = $op['fieldname'].'_input_group';
                        echo driverCommand::run('formatFieldCRUDGroup', $op);
                        echo '</div>';
                        echo '<div class="col-md-4">';
                        $op = $p;
                        $op['fieldname'] = $op['fieldname'].'_input_all';
                        echo driverCommand::run('formatFieldCRUDAll', $op);
                        echo '</div>';
?>
                            </div>
                            <div class="panel-footer" id="<?php echo $p["fieldname"] . '_flags'; ?>">
                                <?php echo __("Decimal flags").": " ?>
                            </div>
                            <script>
                                function <?php echo $p["fieldname"] . '_parseflags'; ?>() {
                                    var flagsId = '#<?php echo $p["fieldname"] . '_flags'; ?>';
                                    var ownerId = '#<?php echo $p["fieldname"] . '_input_owner'; ?>';
                                    var groupId = '#<?php echo $p["fieldname"] . '_input_group'; ?>';
                                    var allId = '#<?php echo $p["fieldname"] . '_input_all'; ?>';
                                    var bits = parseInt($(ownerId).attr('value'));
                                    bits += parseInt($(groupId).attr('value'));
                                    bits += parseInt($(allId).attr('value'));
                                    $(flagsId).html(__("Decimal flags")+": "+bits);
                                    $('#<?php echo $p["fieldname"]; ?>').attr('value', bits);
                                }
                                $(document).ready(function() {
                                    $('[id^=<?php echo $p["fieldname"] . '_input_]'; ?>').change(function(e){
                                        <?php echo $p["fieldname"] . '_parseflags'; ?>();
                                    });
                                    <?php echo $p["fieldname"] . '_parseflags'; ?>();
                                    });
                            </script>
                        </div>
<?php
                        echo '</div>';
                    }
                }
                
            }
        }
        
        /**
        * Format the label in green if value is true, otherway format in red.
        * @param boolean $value 
        * @param string $label
        * @return boolean
        */
       private static function secFormatString($value, $label) {
           $lab = ($value?"success":"danger");
           return "<span class=\"label label-$lab\">$label</span>";
       }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Format nodesec field to read or write."), 
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
                ),
                "echo" => true
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
return new commandFormatFieldCRUD();