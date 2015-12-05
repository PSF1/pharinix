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
    const REWRITE_TYPE_REDIRECT = 'r';
    const REWRITE_TYPE_MAPPING = 'm';
    
    /**
     * Intercept URL rewriting and parse it.
     */
    public function __construct() {
        if (isset($_GET["rewrite"])) { // Is rewrite context?
            $canceled = false;
            driverHook::CallHook('urlRewriteHook', array(
                'url' => &$_GET["rewrite"],
                'canceled' => &$canceled,
            ));
            if ($canceled) return;
            $url = $_GET["rewrite"];
            $mapping = "";
            $rew = $this->getRewritedUrl($url, $mapping);
            if ($rew !== false) {
                switch ($rew->fields['type']) {
                    case self::REWRITE_TYPE_REDIRECT:
                        $nUrl = $rew->fields['rewriteto'];
                        $this->parseUrl($nUrl);
                        break;
                    case self::REWRITE_TYPE_MAPPING:
                        $this->parseUrl($mapping);
                        break;
                }
            } else {
                $status = "HTTP/1.0 404 Not Found";
                $body = '';
                driverHook::CallHook('urlRewriteNotFoundHook', array(
                    'body' => &$body,
                    'status' => &$status,
                ));
                header($status);
                echo $body;
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
    }
    
    /**
     * Tranlate original URL to the rewrite one.
     * @param string $url
     * @param string $mapping
     * @return Recordset
     */
    public function getRewritedUrl($url, &$mapping) {
        $db = dbConn::get();
        
        // Search rewrite URL
        $sql = "SELECT * FROM `url_rewrite` where `url` = '$url'";
        $q = $db->Execute($sql);
        if (!$q->EOF) {
            return $q;
        }
        // If not found search mapped URL
        // http://stackoverflow.com/a/12344881
        $parts = explode("/", $url);
        $nparts = count($parts);
        $where = '(@nvars:=ROUND((CHAR_LENGTH(`url`)-CHAR_LENGTH(REPLACE(`url`,"$","")))/CHAR_LENGTH("$"))) and @nvars > 0 && @nvars <= '.$nparts;
        $sql = "SELECT * FROM `url_rewrite` where $where order by priority desc";
        $q = $db->Execute($sql);
        if (!$q->EOF) {
            // Find the correct map
            while (!$q->EOF) {
                $mapResponse = self::mapParse($parts, $q->fields["url"]);
                if ($mapResponse !== false) {
                    // I find a match, I go out.
                    $mapping = self::mapReplace($mapResponse, $q->fields["rewriteto"]);
                    // Create the URL context in driverCommad register
                    $reg = &driverCommand::getRegister("url_context");
                    // Clear $ from variables
                    $clear = array();
                    foreach($mapResponse as $key => $value) {
//                        $nkey = str_replace('$', '', $key);
                        $clear[$key] = $value;
                    }
                    $reg = $clear;
                    break;
                }
                $q->MoveNext();
            }
            return $q;
        }
        return false;
    }
    
    /**
     * Try map a URL to the selected map URL
     * @param array $origParts Parts of the query URL
     * @param string $mUrl Map to test
     * @return array FALSE if no math to the map
     */
    public static function mapParse($origParts, $mUrl) {
        $mapResponse = false;
        $nparts = count($origParts);
        $mUrlParts = explode("/", $mUrl);
        if ($nparts == count($mUrlParts)) {
            for ($i = 0; $i < $nparts; ++$i) {
                if (!isset($mUrlParts[$i])) {
                    $mapResponse = false;
                    break; // Dont match
                }
                if ($origParts[$i] != $mUrlParts[$i]) {
                    $dolar = @substr($mUrlParts[$i], 0, 1);
                    if (!$dolar || $dolar != "$") {
                        $mapResponse = false;
                        break; // Dont match
                    }
                    // Assign value to variable
                    $mapResponse[$mUrlParts[$i]] = $origParts[$i];
                }
            }
        }
        return $mapResponse;
    }
    
    /**
     * Replace variables in the parameters string
     * @param array $mapVar List of variables, with values, to replace
     * @param string $params Map parameters
     * @return string $params with variables replaced
     */
    public static function mapReplace($mapVar, $params) {
        foreach($mapVar as $search => $value) {
            $params = str_replace($search, $value, $params);
        }
        // Clear empty variables
        $parts = explode("&", $params);
        $params = "";
        foreach($parts as $pair) {
            if (strpos($pair, "$") !== false) {
                $var = explode("=", $pair);
                $pair = $var[0]."=";
            }
            if ($params != "") $params .= "&";
            $params .= $pair;
        }
        return $params;
    }
}