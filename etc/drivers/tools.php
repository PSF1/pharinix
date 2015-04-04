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
        // Vaciamos la cachÃ© de lectura de disco
        clearstatcache();
        // Comprobamos si el fichero existe
        $data["exists"] = is_file($path) || is_dir($path);
        // Comprobamos si el fichero es escribible
        $data["writable"] = is_writable($path);
        // Leemos los permisos del fichero
        $data["chmod"] = ($data["exists"] ? substr(sprintf("%o", fileperms($path)), -4) : FALSE);
        // Extraemos la extension, un solo paso
        $data["ext"] = substr(strrchr($path, "."), 1);
        // Primer paso de lectura de ruta
        $pt = explode("." . $data["ext"], $path);
        $data["path"] = array_shift($pt);
        // Primer paso de lectura de nombre
        $pt = explode("/", $data["path"]);
        $data["name"] = array_pop($pt);
        // Ajustamos nombre a FALSE si esta vacio
        $data["name"] = ($data["name"] ? $data["name"] : FALSE);
        // Ajustamos la ruta a FALSE si esta vacia
        $p1 = explode($data["name"], $data["path"]);
        $p2 = explode($data["ext"], $data["path"]);
        $p3 = explode($data["name"], $data["path"]);
        $p4 = explode($data["ext"], $data["path"]);
        $data["path"] = ($data["exists"] ? 
                ($data["name"] ? 
                    realpath(array_shift($p1)) : 
                    realpath(array_shift($p2))) : 
                ($data["name"] ? 
                    array_shift($p3) : 
                    ($data["ext"] ? 
                            array_shift($p4) : 
                            rtrim($data["path"], "/"))));
        // Ajustamos el nombre a FALSE si esta vacio o a su valor en caso contrario
        $data["filename"] = (($data["name"] OR $data["ext"]) ? $data["name"] . ($data["ext"] ? "." : "") . $data["ext"] : FALSE);
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
}
