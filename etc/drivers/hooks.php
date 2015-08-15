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

class driverHook {
    /**
     * Global static configuration
     * 
     * @var array
     */
    protected static $Config = Array('extensions' => array());

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
     * @param string $name The call name. Module name.
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
     * Determine if a hook has a registered handler
     * 
     * @param string $hook The hook name
     * @return boolean Has handler?
     */
    public static function HasHookHandler($hook) {
        return isset(self::$Config['extensions'][$hook]);
    }

}
