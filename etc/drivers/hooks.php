<?php
/*
Based on PhpWsdl - Generate WSDL from PHP
Copyright (C) 2011  Andreas Zimmermann, wan24.de 

This program is free software; you can redistribute it and/or modify it under 
the terms of the GNU General Public License as published by the Free Software 
Foundation; either version 3 of the License, or (at your option) any later 
version. 

This program is distributed in the hope that it will be useful, but WITHOUT 
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. 

You should have received a copy of the GNU General Public License along with 
this program; if not, see <http://www.gnu.org/licenses/>.
*/

/*
 * Pharinix
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

class driverHook {
    /**
     * Global static configuration
     * 
     * @var array
     */
    protected static $Config = Array('extensions' => array());
    protected static $loadingHook = array();
    protected static $permanent = '';
    protected static $basePath = '';

    public function __construct($permanent = 'etc/hookHandlers.inc') {
        ob_start('driverHook::fatal_error_handler');
        self::$basePath = getcwd().'/';
        
        self::$permanent = $permanent;
        $hf = self::getHandlersFile(self::$permanent);
        foreach ($hf as $hl) {
            if (is_file($hl['file'])) {
                try {
                    self::$loadingHook = $hl;
                    include_once $hl['file'];
                    self::RegisterHook($hl['hook'], $hl['file'], $hl['handler']);
                } catch (Exception $ex) {
                    // Take note about ofender handler
                    $ofe = new driverHook('etc/hookOfenders.inc');
                    $ofe::saveHandler($hl['hook'], $hl['file'], $hl['handler']);
                    unset($ofe);
                    // And remove it
                    self::removeHandler($hl['hook'], $hl['file'], $hl['handler']);
                }
            }
        }

        ob_end_clean(); //'driverHook::fatal_error_handler'
    }
    
    /**
     * http://stackoverflow.com/a/5192011
     * 
     * @param string $buffer
     * @return string
     */
    public static function fatal_error_handler($buffer) {
        $error = error_get_last();
        if ($error['type'] == 1) {
            // type, message, file, line
            $newBuffer = '<html><header><title>'.__('Fatal Error').' </title></header>
                    <style>                 
                    .error_content{                     
                        background: ghostwhite;
                        vertical-align: middle;
                        margin:0 auto;
                        padding:10px;
                        width:50%;                              
                     } 
                     .error_content label{color: red;font-family: Georgia;font-size: 16pt;font-style: italic;}
                     .error_content ul li{ background: none repeat scroll 0 0 FloralWhite;                   
                                border: 1px solid AliceBlue;
                                display: block;
                                font-family: monospace;
                                padding: 2%;
                                text-align: left;
                      }
                    </style>
                    <body style="text-align: center;">  
                      <div class="error_content">
                          <label >'.__('Fatal Error').' </label>
                          <ul>
                            <li><b>'.__('Line').':</b> ' . $error['line'] . '</li>
                            <li><b>'.__('Message').':</b> ' . $error['message'] . '</li>
                            <li><b>'.__('File').':</b> ' . $error['file'] . '</li>                             
                          </ul>';
                if (is_array(self::$loadingHook)) {
                    $newBuffer .= '<label >'.__('Ofender').' </label>';
                    $newBuffer .= '<ul>';
                    foreach(self::$loadingHook as $key => $val) {
                        $newBuffer .= "<li><b>$key:</b> $val</li>";
                    }
                    $newBuffer .= '</ul>';
                    // Remove ofender
                    driverHook::$permanent = 'etc/hookHandlers.inc';
                    driverHook::removeHandler(self::$loadingHook['hook'], self::$loadingHook['file'], self::$loadingHook['handler'], false);
                    // Take note about
                    driverHook::$permanent = 'etc/hookOfenders.inc';
                    driverHook::saveHandler(self::$loadingHook['hook'], self::$loadingHook['file'], self::$loadingHook['handler'], false);
                    // Restore normal permanent list
                    driverHook::$permanent = 'etc/hookHandlers.inc';
                    $newBuffer .= '<p>'.__('Ofender hook was removed.').'</p>';
                }
                $newBuffer .= '</div>
                      </body></html>';

            return $newBuffer;
        }

        return $buffer;
    }

    public static function reset() {
        self::$Config = Array('extensions' => array());
    }
    
    public static function setPermanentFile($file) {
        self::$basePath = getcwd().'/';
        self::$permanent = $file;
    }


    /**
     * Call a hook function
     * Ex.:
     * self::CallHook(
     *      'ConstructorHook',
     *      Array(
     *          'server'    =>	$this,
     *          'output'    =>	&$outputOnRequest,
     *          'run'       =>	&$runServer,
     *          'quickmode' =>	&$quickRun
     *      )
     * );
     * 
     * @param string $name The hook name
     * @param mixed $data The parameter (default: NULL)
     * @return boolean Response
     */
    public static function CallHook($name, $data = null) {
//        self::Debug('Call hook '.$name);
	if (!self::HasHookHandler($name))
            return true;
        $keys = array_keys(self::$Config['extensions'][$name]);
        $i = -1;
        $len = sizeof($keys);
        while (++$i < $len) {
//            self::Debug('Call ' . self::$Config['extensions'][$name][$keys[$i]]);
            if (!call_user_func(self::$Config['extensions'][$name][$keys[$i]], $data)) {
//                self::Debug('Handler stopped hook execution');
                return false;
            }
        }
        return true;
    }

    /**
     * Register a hook
     * Ex.:
     * self::RegisterHook('InterpretKeywordserviceHook','internal','PhpWsdl::InterpretService');
     * 
     * @param string $hook The hook name.
     * @param string $name The call name. Module name, it's necesary to disallow duplicated handlers.
     * @param mixed $data The hook call data. Function name or static method name.
     */
    public static function RegisterHook($hook, $name, $data) {
        if (!self::HasHookHandler($hook))
            self::$Config['extensions'][$hook] = Array();
//        if (self::$Debugging) {
//            $handler = $data;
//            if (is_array($handler)) {
//                $class = $handler[0];
//                $method = $handler[1];
//                if (is_object($class))
//                    $class = get_class($class);
//                $handler = $class . '.' . $method;
//            }
//            self::Debug('Register hook ' . $hook . ' handler ' . $name . ': ' . $handler);
//        }
        self::$Config['extensions'][$hook][$name] = $data;
    }

    /**
     * Register a permanent handler
     * @param string $hook Hook to handle.
     * @param string $file Path relative to pharinix root.
     * @param string $func Handler function. 
     * @return boolean TRUE if it's saved.
     */
    public static function saveHandler($hook, $file, $func, $lazy = true) {
        $hf = driverHook::getHandlersFile(self::$basePath.driverHook::$permanent);
        foreach($hf as $hl) {
            if ($hl['file'] == $file && $hl['hook'] == $hook && $hl['handler'] == $func) {
                return false;
            }
        }
        $hn = array(
            'file' => $file,
            'hook' => $hook,
            'handler' => $func
        );
        if ($lazy) driverHook::RegisterHook($hook, $file, $func);
        $hf[] = $hn;
        $data = '';
        foreach($hf as $hl) {
            $data .= "{$hl['hook']};{$hl['file']};{$hl['handler']}\n";
        }
        //file_put_contents(driverHook::$permanent, $data);
        $fp = fopen(self::$basePath.driverHook::$permanent , 'wb');
        fwrite($fp, $data);
        fclose($fp);
        return true;
    }
    
    /**
     * Unregister a hook
     * 
     * @param string $hook The hook name
     * @param string $name The call name or NULL to unregister the whole hook. Module name.
     */
    public static function UnregisterHook($hook, $name = null) {
        if (!self::HasHookHandler($hook))
            return;
        if (!is_null($name)) {
            if (!isset(self::$Config['extensions'][$hook][$name]))
                return;
        } else {
            unset(self::$Config['extensions'][$hook]);
            return;
        }
        unset(self::$Config['extensions'][$hook][$name]);
//        if (self::$Debugging)
//            self::Debug('Unregister hook ' . $hook . ' handler ' . $name);
        if (sizeof(self::$Config['extensions'][$hook]) < 1)
            unset(self::$Config['extensions'][$hook]);
    }
    
    /**
     * Unregister a permanent handler
     * @param string $hook Hook to handle.
     * @param string $file Path relative to pharinix root.
     * @param string $func Handler function. 
     * @return boolean TRUE if it's removed.
     */
    public static function removeHandler($hook, $file, $func, $lazy = true) {
        $hf = self::getHandlersFile(self::$basePath.self::$permanent);
        if ($lazy) self::UnregisterHook($hook, $file);
        $ndata = array();
        foreach($hf as $hl) {
            if ($hl['file'] != $file || $hl['hook'] != $hook || $hl['handler'] != $func) {
                $ndata[] = $hl;
            }
        }
        $data = '';
        foreach($ndata as $hl) {
            $data .= "{$hl['hook']};{$hl['file']};{$hl['handler']}\n";
        }
        file_put_contents(self::$basePath.self::$permanent, $data);
        return true;
    }

    /**
     * Determine if a hook has a registered handler
     * 
     * @param string $hook The hook name
     * @return boolean Has handler?
     */
    public static function HasHookHandler($hook) {
        return isset(self::$Config['extensions'][$hook]);
    }

    /**
     * Read the permanent handlers.
     * @param string $file Hook permanent handlers.
     * @return array ( array('hook' => '', 'file' => '', 'handler' => ''), ... )
     */
    public static function getHandlersFile($file) {
        $resp = array();
        if (is_file($file)) {
            $hf = explode("\n", file_get_contents($file));
            foreach($hf as $hl) {
                $line = explode(";", $hl);
                if (count($line) == 3) {
                    $resp[] = array(
                        'hook' => $line[0],
                        'file' => $line[1],
                        'handler' => $line[2],
                    );
                }
            }
        }
        return $resp;
    }
}
