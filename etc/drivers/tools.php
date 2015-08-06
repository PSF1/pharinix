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
//if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

 class driverTools {

     /**
     * Extraer informacion de una ruta, nombre y extension de un archivo dado.<br/>
     * Posibilidades de uso (o mal uso) de una funcion de este tipo:
     * <ul>
     * <li>Extension sin nombre: .htaccess</li>
     * <li>Nombre sin extension: name</li>
     * <li>Nombre simplon: name.jpeg</li>
     * <li>Nombre complejo: name.surname.gif</li>
     * <li>Ruta absoluta: /path/to/name.surname.tar.gz</li>
     * <li>Ruta relativa: ../../path/to/name.surname.tar.gz</li>
     * <li>BONUS: Cadena vacia para romper la funcion Comillas vacias "</li>
     * <li>BONUS 2: Cadena malformada para romper la funcion "/\/.path///file/.gif"</li>
     * <li>BONUS 3: Ruta sin archivo "/path/to/folder/"</li>
     * </ul>
     * <a href="http://www.propiedadprivada.com/funcion-php-extraer-ruta-nombre-y-extension-de-un-archivo/746/">Fuente</a>
     * @param string $path Ruta a analizar
     * @return array Extension sin nombre: .htaccess
     * <ul>
     * <li>Array (7)</li>
     * <li>(</li>
     * <li>|    ["exists"] = Boolean(1) TRUE</li>
     * <li>|    ["writable"] = Boolean(0) FALSE</li>
     * <li>|    ["chmod"] = String(4) " 0644 "</li>
     * <li>|    ["ext"] = String(8) " htaccess "</li>
     * <li>|    ["path"] = Boolean(0) FALSE</li>
     * <li>|    ["name"] = Boolean(0) FALSE</li>
     * <li>|    ["filename"] = String(9) " .htaccess "</li>
     * <li>)
     */
    public static function pathInfo($path) {
        $path = str_replace("\\", "/", $path);
        // Vaciamos la cachÃ© de lectura de disco
        clearstatcache();
        // Comprobamos si el fichero existe
        $data["exists"] = is_file($path) || is_dir($path);
        $data["isfile"] = $data["exists"] && is_file($path);
        $data["isdir"] = $data["exists"] && is_dir($path);
        // Comprobamos si el fichero es escribible
        $data["writable"] = is_writable($path);
        // Leemos los permisos del fichero
        $data["chmod"] = ($data["exists"] ? substr(sprintf("%o", fileperms($path)), -4) : FALSE);
        // Extraemos la extension, un solo paso
        if (!$data["isdir"]) {
            $data["ext"] = substr(strrchr($path, "."), 1);
        } else {
            $data["ext"] = false;
        }
        // Primer paso de lectura de ruta
        $pt = explode("." . $data["ext"], $path);
        $data["path"] = array_shift($pt);
        // Primer paso de lectura de nombre
        if (!$data["isdir"]) {
            $pt = explode("/", $data["path"]);
            $data["name"] = array_pop($pt);
            // Ajustamos nombre a FALSE si esta vacio
            $data["name"] = ($data["name"] ? $data["name"] : "");
            // Ajustamos el nombre a FALSE si esta vacio o a su valor en caso contrario
            $data["filename"] = (($data["name"] OR $data["ext"]) ? $data["name"] . ($data["ext"] ? "." : "") . $data["ext"] : FALSE);
        } else {
            $data["name"] = "";
            $data["filename"] = basename($path);
        }
        // Ajustamos la ruta a FALSE si esta vacia
        $p1 = @explode($data["name"], $data["path"]);
        if ($p1 === false) $p1 = array();
        $p2 = @explode($data["ext"], $data["path"]);
        if ($p2 === false) $p2 = array();
        $p3 = @explode($data["name"], $data["path"]);
        if ($p3 === false) $p3 = array();
        $p4 = @explode($data["ext"], $data["path"]);
        if ($p4 === false) $p4 = array();
        $data["path"] = ($data["exists"] ? 
                ($data["name"] ? 
                    realpath(array_shift($p1)) : 
                    realpath(array_shift($p2))) : 
                ($data["name"] ? 
                    array_shift($p3) : 
                    ($data["ext"] ? 
                            array_shift($p4) : 
                            rtrim($data["path"], "/"))));
        // Devolvemos los resultados
        return $data;
    }
    
     /**
     * http://www.programacionweb.net/articulos/articulo/listar-archivos-de-un-directorio/
     * @param string $path Folder path to explore. Must include "/" at the end.
     * @return array ("folders" => array(string, ...), "files" => array(string, ...))
     */
    public static function lsDir($path, $pattern = "*.*") {
        $resp = array("files" => array(), "folders" => array());
        $directorio = opendir($path);
        while ($archivo = readdir($directorio)) {
            if ($archivo != '.' && $archivo != '..') {
                if (fnmatch($pattern, $archivo)) {
                    if (is_dir("$path/$archivo")) {
                        $resp["folders"][] = $path.$archivo;
                    } else {
                        $resp["files"][] = $path.$archivo;
                    }
                }
            }
        }
        closedir($directorio);
        return $resp;
    }
    
    /**
     +-------------------------------------------------------------------------+
     | Revive Adserver                                                         |
     | http://www.revive-adserver.com                                          |
     |                                                                         |
     | Copyright: See the COPYRIGHT.txt file.                                  |
     | License: GPLv2 or later, see the LICENSE.txt file.                      |
     +-------------------------------------------------------------------------+
     * Attempts to remove the file indicated by the $sFilename path from the
     * filesystem. If the $filename indicates non-empty directory the function
     * will remove it along with all its content.
     *
     * @param string $sFilename
     * @return boolean True if the operation is successful, Exception if there
     * was a failure.
    */
    public static function fileRemove($sFilename) {
        if (file_exists($sFilename)) {
            if (is_dir($sFilename)) {
                $directory = opendir($sFilename);
                if (false === $directory) {
                    $error = new Exception("Can't open the directory: '$sFilename'.");
                    return $error;
                }
                while (($sChild = readdir($directory)) !== false) {
                    if ($sChild == '.' or $sChild == '..') {
                        continue;
                    }
                    $result = self::fileRemove($sFilename . '/' . $sChild);
                    if ($result instanceof Exception) {
                        return $result;
                    }
                }
                closedir($directory);
                $result = rmdir($sFilename);
                if ($result === false) {
                    $error = new Exception("Can't delete the directory: '$sFilename'.");
                    return $error;
                }
            } else {
                if(!unlink($sFilename)) {
                    return new Exception("Can't remove the file: '$sFilename'.");
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Compare two versions strings. Version numbers must have de format <mayor>.<minor>.<revision>, and all parts must be numbers. Ex. '1.2.3' . In $need, minor or revision can be 'x' to allow any value.
     * @param string $need Version number
     * @param string $have Version number
     * @return boolean TRUE if $have is greater or equal to $need
     */
    public static function versionIsGreaterOrEqual($need, $have) {
        $need = explode(".", $need);
        $have = explode(".", $have);
        for($i = 0; $i < 3; $i++) {
            if (!isset($need[$i])) {
                $need[$i] = 0;
            } else {
                $need[$i] = intval($need[$i]);
            }
            if (!isset($have[$i])) {
                $have[$i] = 0;
            } else {
                $have[$i] = intval($have[$i]);
            }
            if (!($have[$i] >= $need[$i])) return false;
            if ($have[$i] > $need[$i]) break;
        }
        return true;
    }
    
    public static function formatDate($mysqlDate, $withTime = true) {
        if ($mysqlDate == "")
            return "";
        $tmp = strtotime($mysqlDate);
        $tmp = date("d-m-Y H:i:s", $tmp);
        $sep = explode(" ", $tmp);
        $tmp = $sep[0];
        if ($withTime) {
            $tmp .= "<br/>" . $sep[1] . " H.";
        }

        return "<span class=\"text-nowrap\">$tmp</span>";
    }

    public static function formatDateInline($mysqlDate, $withTime = true) {
        $resp = self::formatDate($mysqlDate, $withTime);
        return str_replace("<br/>", " ", $resp);
    }

    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        //$bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * start $string with $start ?
     * @param string $start Start string
     * @param string $string String that would start with $start
     * @return boolean
     */
    public static function str_start($start, $string) {
        if ($start == "") return true;
        $cap = substr($string, 0, strlen($start));
        return ($cap == $start);
    }
    
    /**
     * end $string with $end ?
     * @param string $end End string
     * @param string $string String that would end with $end
     * @return boolean
     */
    public static function str_end($end, $string) {
        if ($end == "") return true;
        $cap = substr($string, -1 * strlen($end));
        return ($cap == $end);
    }
}
