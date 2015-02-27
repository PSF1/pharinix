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
