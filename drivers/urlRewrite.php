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
/**
 * .htaccess
 *# Mod_Rewite
 * RewriteEngine on  
 * RewriteCond %{REQUEST_FILENAME} !-f  
 * RewriteCond %{REQUEST_FILENAME} !-d  
 * RewriteRule ^(.*)$ /index.php?rewrite=$1 [L,QSA]
 * 
 * Example:
 * `url` = 'page', `rewriteto` = 'command=showpage&id=page'
 * Rewrite 'http://127.0.0.1/page' to 
 * 'http://127.0.0.1/?GETPASSWORD&command=showpage&id=page'
 */
class driverUrlRewrite {
    
    /**
     * Intercept URL rewriting and parse it.
     */
    public function __construct() {
        if (isset($_GET["rewrite"])) { // Is rewrite context?
            $url = $_GET["rewrite"];
            $nUrl = $this->getRewritedUrl($url);
            if ($nUrl !== false) {
                $this->parseUrl($nUrl);
            } else {
                header("HTTP/1.0 404 Not Found");
            }
        }
    }
    
    /**
     * Parse rewrited URL to set GET params.
     * @param string $url
     */
    public function parseUrl($url) {
        $aux = array();
        parse_str($url, $aux);
        foreach ($aux as $key => $value) {
            $_GET[$key] = $value;
        }
        $_GET[CMS_GET_PASS] = "";
    }
    
    /**
     * Tranlate original URL to the rewrite one.
     * @param string $url
     * @return boolean
     */
    public function getRewritedUrl($url) {
        $db = dbConn::get();
        $sql = "SELECT rewriteto FROM `url_rewrite` where `url` = '$url'";
        $q = $db->Execute($sql);
        if (!$q->EOF) {
            return $q->fields['rewriteto'];
        }
        return false;
    }
}