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

if (!class_exists("commandFormatFieldCRUDOwner")) {
    class commandFormatFieldCRUDOwner extends driverCommand {

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
                    $p['value'] = intval($p['value']);
                    if ($p["toread"] || $p["readonly"]) { // to read
                        echo '<!-- Field "'.$p["fieldname"].'" -->';
?>
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <?php echo __("Owner"); ?>
                            </div>
                            <div class="panel-body">
<?php
                        echo self::secFormatString($params['value'] & driverUser::PERMISSION_NODE_OWNER_CREATE, __("Create"))." ";
                        echo self::secFormatString($params['value'] & driverUser::PERMISSION_NODE_OWNER_READ, __("Read"))." ";
                        echo self::secFormatString($params['value'] & driverUser::PERMISSION_NODE_OWNER_UPDATE, __("Update"))." ";
                        echo self::secFormatString($params['value'] & driverUser::PERMISSION_NODE_OWNER_DEL, __("Delete"));
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
                        <div class="panel panel-info" id="<?php echo $p["fieldname"]; ?>" name="<?php echo $p["fieldname"]; ?>">
                            <div class="panel-heading">
                                <?php echo __("Owner"); ?>
                            </div>
                            <div class="panel-body">
<?php
                        echo self::secInputString(
                                $params['value'] & driverUser::PERMISSION_NODE_OWNER_CREATE, 
                                __("Create"),
                                'create',
                                $p)." ";
                        echo self::secInputString(
                                $params['value'] & driverUser::PERMISSION_NODE_OWNER_READ, 
                                __("Read"),
                                'read',
                                $p)." ";
                        echo self::secInputString(
                                $params['value'] & driverUser::PERMISSION_NODE_OWNER_UPDATE, 
                                __("Update"),
                                'update',
                                $p)." ";
                        echo self::secInputString(
                                $params['value'] & driverUser::PERMISSION_NODE_OWNER_DEL, 
                                __("Delete"),
                                'delete',
                                $p);
?>
                            </div>
                            <div class="panel-footer" id="<?php echo $p["fieldname"] . '_owner_flags'; ?>">
                                <?php echo __("Decimal flags").": " ?>
                            </div>
                            <script>
                                function <?php echo $p["fieldname"] . '_owner_parseflags'; ?>() {
                                    var flagsId = '#<?php echo $p["fieldname"] . '_owner_flags'; ?>';
                                    var createId = '#<?php echo $p["fieldname"] . '_owner_create'; ?>';
                                    var readId = '#<?php echo $p["fieldname"] . '_owner_read'; ?>';
                                    var updateId = '#<?php echo $p["fieldname"] . '_owner_update'; ?>';
                                    var deleteId = '#<?php echo $p["fieldname"] . '_owner_delete'; ?>';
                                    var bits = $(createId).is(':checked')?'1':'0';
                                    bits += $(readId).is(':checked')?'1':'0';
                                    bits += $(updateId).is(':checked')?'1':'0';
                                    bits += $(deleteId).is(':checked')?'1':'0';
                                    bits += '00000000';
                                    $(flagsId).html(__("Decimal flags")+": "+parseInt(bits,2));
                                    $('#<?php echo $p["fieldname"]; ?>').attr('value', parseInt(bits,2));
                                }
                                $(document).ready(function() {
                                    $('[id^=<?php echo $p["fieldname"] . '_owner_]'; ?>').change(function(e){
                                        if ($(this).is(':checked')) {
                                            $(this).parent().addClass('text-success');
                                            $(this).parent().removeClass('text-danger');
                                        } else {
                                            $(this).parent().removeClass('text-success');
                                            $(this).parent().addClass('text-danger');
                                        }
                                        <?php echo $p["fieldname"] . '_owner_parseflags'; ?>();
                                    });
                                    <?php echo $p["fieldname"] . '_owner_parseflags'; ?>();
                                    });
                            </script>
                        </div>
<?php
                        echo '</div>';
                    }
                }
                
            }
        }
        
        private static function secInputString($value, $label, $name, $p) {
            echo '<label class="checkbox-inline ' . ($value ? "text-success" : "text-danger") . '" for="' . $p["fieldname"] . '_owner_' . $name . '">';
            echo '<input type="checkbox" name="' . $p["fieldname"] . '_owner_' . $name . '" id="' . $p["fieldname"] . '_owner_' . $name . '" value="1" ' . ($value ? "checked" : "") . '>';
            echo $label;
            echo '</label>';
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
                "description" => __("Format nodesec owner field to read or write."), 
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
return new commandFormatFieldCRUDOwner();