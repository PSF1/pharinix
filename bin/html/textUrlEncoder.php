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

if (!class_exists("commandTextUrlEncoder")) {
    class commandTextUrlEncoder extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $fid = driverCommand::run("newID");
            $fid = $fid["id"];
?>
<script type="text/javascript">
function encode(fid) {
	var obj = document.getElementById('dencoder'+fid);
	var unencoded = obj.value;
	obj.value = encodeURIComponent(unencoded).replace(/'/g,"%27").replace(/"/g,"%22");	
}
function decode(fid) {
	var obj = document.getElementById('dencoder'+fid);
	var encoded = obj.value;
	obj.value = decodeURIComponent(encoded.replace(/\+/g,  " "));
}
</script>
                <form onsubmit="return false;">
                    <legend><?php __e('URL Decoder/Encoder'); ?></legend>
                    <div class="form-group">
                        <textarea class="form-control" style="width: 100%;" id="dencoder<?php echo $fid;?>"></textarea>
                    </div>
                    <div>
                        <input type="button" class="btn btn-primary" onclick="decode('<?php echo $fid;?>')" value="<?php __e('Decode'); ?>">
                        <input type="button" class="btn btn-primary" onclick="encode('<?php echo $fid;?>')" value="<?php __e('Encode'); ?>">
                    </div>
                    <div class="help-block">
                        <ul>
                            <li><?php __e('Input a string of text and encode or decode it as you like.'); ?></li>
                        </ul>
                        <?php
                        echo sprintf(__('The URL Decoder/Encoder is licensed under a Creative Commons %s License.'), '<a href="http://creativecommons.org/licenses/by-sa/2.0/" rel="license">'.__("Attribution-ShareAlike 2.0").'</a>');
                        ?> <img alt="Creative Commons License" border="0" src="http://creativecommons.org/images/public/somerights.gif">
                    </div>
                </form>
<?php
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
                "description" => __("Encode/Decode a string as a URL.<p>The URL Decoder/Encoder is licensed under a Creative Commons Attribution-ShareAlike 2.0 License, http://creativecommons.org/licenses/by-sa/2.0/, by Eric Meyer, http://meyerweb.com/eric/tools/dencoder/."), 
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
return new commandTextUrlEncoder();