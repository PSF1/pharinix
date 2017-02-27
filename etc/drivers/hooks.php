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
 * Pharinix Copyright (C) 2016 Pedro Pelaez <aaaaa976@gmail.com>
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
    /**
     * Hook loader control
     * @var array
     */
    protected static $loadingHook = null;
    protected static $permanent = '';
    protected static $basePath = '';
    protected static $lastError = '';

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
                self::$loadingHook = null;
            }
        }

        ob_end_clean(); //'driverHook::fatal_error_handler'
//        if (trim(self::$lastError) != '') {
//            echo self::$lastError;
//            self::$lastError = '';
//        }
    }

    /**
     * http://stackoverflow.com/a/5192011
     *
     * @param string $buffer
     * @return string
     */
    public static function fatal_error_handler($buffer) {
        // Prevend undefined constansts
        if (!defined('E_ERROR')) define('E_ERROR',1);
        if (!defined('E_WARNING')) define('E_WARNING',2);
        if (!defined('E_PARSE')) define('E_PARSE',4);
        if (!defined('E_NOTICE')) define('E_NOTICE',8);
        if (!defined('E_CORE_ERROR')) define('E_CORE_ERROR',16);
        if (!defined('E_CORE_WARNING')) define('E_CORE_WARNING',32);
        if (!defined('E_COMPILE_ERROR')) define('E_COMPILE_ERROR',64);
        if (!defined('E_COMPILE_WARNING')) define('E_COMPILE_WARNING',128);
        if (!defined('E_USER_ERROR')) define('E_USER_ERROR',256);
        if (!defined('E_USER_WARNING')) define('E_USER_WARNING',512);
        if (!defined('E_USER_NOTICE')) define('E_USER_NOTICE',1024);
        if (!defined('E_STRICT')) define('E_STRICT',2048);
        if (!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR',4096);
        if (!defined('E_DEPRECATED')) define('E_DEPRECATED',8192);
        if (!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED',16384);
        if (!defined('E_ALL')) define('E_ALL',32767);
        // Capture error if any
        $error = error_get_last();
        switch ($error['type']) {
                case E_ERROR:
                    // Fatal run-time errors. These indicate errors that can not be recovered from, such as a memory allocation problem. Execution of the script is halted.
                case E_PARSE:
                    // Compile-time parse errors. Parse errors should only be generated by the parser.
                case E_CORE_ERROR:
                    // Fatal errors that occur during PHP's initial startup. This is like an E_ERROR, except it is generated by the core of PHP.
                case E_COMPILE_ERROR:
                    // Fatal compile-time errors. This is like an E_ERROR, except it is generated by the Zend Scripting Engine.
                case E_RECOVERABLE_ERROR:
                    // Catchable fatal error. It indicates that a probably dangerous error occurred, but did not leave the Engine in an unstable state. If the error is not caught by a user defined handle (see also set_error_handler()), the application aborts as it was an E_ERROR.	Since PHP 5.2.0
                    // type, message, file, line
                    $txtLog = driverTools::getErrorLevelLabelByType($error['type']).": \n";
                    $newBuffer = '<html><header><title>Fatal Error </title></header>
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
                                  <label >'.'Fatal Error'.' </label>
                                  <ul>
                                    <li><b>'.'Line'.':</b> ' . $error['line'] . '</li>
                                    <li><b>'.'Message'.':</b> ' . $error['message'] . '</li>
                                    <li><b>'.'File'.':</b> ' . $error['file'] . '</li>
                                  </ul>';
                        $txtLog .= "Message: {$error['message']}\n";
                        $txtLog .= "File: {$error['file']}\n";
                        $txtLog .= "Line: {$error['line']}\n";
                        if (is_array(self::$loadingHook)) {
                            $txtLog .= 'Ofender'."\n";
                            $newBuffer .= '<label >'.'Ofender'.' </label>';
                            $newBuffer .= '<ul>';
                            foreach(self::$loadingHook as $key => $val) {
                                $newBuffer .= "<li><b>$key:</b> $val</li>";
                                $txtLog .= "\t* $key => $val\n";
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
                            $newBuffer .= '<p>'.'Ofender hook was removed.'.'</p>';
                            $txtLog .= 'Ofender hook was removed.'."\n";
                        }
                        $newBuffer .= '</div>
                              </body></html>';

                    driverLogTXT::logNow('error_fatal.log', $txtLog);
                    return $newBuffer;
                    break;
                case E_WARNING:
                    // Run-time warnings (non-fatal errors). Execution of the script is not halted.
                case E_NOTICE:
                    // Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script.
                case E_CORE_WARNING:
                    // Warnings (non-fatal errors) that occur during PHP's initial startup. This is like an E_WARNING, except it is generated by the core of PHP.
                case E_COMPILE_WARNING:
                    // Compile-time warnings (non-fatal errors). This is like an E_WARNING, except it is generated by the Zend Scripting Engine.
                case E_USER_ERROR:
                    // User-generated error message. This is like an E_ERROR, except it is generated in PHP code by using the PHP function trigger_error().
                case E_USER_WARNING:
                    // User-generated warning message. This is like an E_WARNING, except it is generated in PHP code by using the PHP function trigger_error().
                case E_USER_NOTICE:
                    // User-generated notice message. This is like an E_NOTICE, except it is generated in PHP code by using the PHP function trigger_error().
                case E_DEPRECATED:
                    // Run-time notices. Enable this to receive warnings about code that will not work in future versions.	Since PHP 5.3.0
                case E_USER_DEPRECATED:
                    // User-generated warning message. This is like an E_DEPRECATED, except it is generated in PHP code by using the PHP function trigger_error().	Since PHP 5.3.0
                    $txtLog = driverTools::getErrorLevelLabelByType($error['type']).": \n";
                    $txtLog .= "Message: {$error['message']}\n";
                    $txtLog .= "File: {$error['file']}\n";
                    $txtLog .= "Line: {$error['line']}\n";
                    $txtLog .= "Trace: ".print_r(debug_backtrace(), 1)."\n";
                    $buffer .= $txtLog;
                    self::$lastError = $txtLog;
                    return false;
                    break;
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
            try {
                if (call_user_func(self::$Config['extensions'][$name][$keys[$i]], $data) === false) {
//                self::Debug('Handler stopped hook execution');
                    return false;
                }
            } catch (Exception $exc) {
                $pm = self::$permanent;
                // Take note about ofender handler
                $ofe = new driverHook('etc/hookOfenders.inc');
                $ofe::saveHandler($name, $keys[$i], self::$Config['extensions'][$name][$keys[$i]]);
                unset($ofe);
                // And remove it
                self::$permanent = $pm;
                self::removeHandler($name, $keys[$i], self::$Config['extensions'][$name][$keys[$i]]);
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
