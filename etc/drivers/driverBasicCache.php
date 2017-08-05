<?php

/*
 * Pharinix Copyright (C) 2017 Pedro Pelaez <aaaaa976@gmail.com>
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
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

class driverBasicCache implements iCache {
    /**
     * @var array Node's cache.
     */
    protected static $nodeCache = array();
    
    /**
     * Clear node's cache
     */
    public static function cacheClear() {
        self::$nodeCache = array();
    }
    
    public static function isCached($nodetype, $nid) {
        return self::cached($nodetype, $nid);
    }
    
    public static function cached($nodetype, $nid) {
        return isset(self::$nodeCache[$nodetype][$nid]);
    }
    
    /**
     * Add a node to the cache.
     * @param string $nodetype
     * @param array $node Node information returned by getNode.
     * @return boolean FALSE if fail
     */
    public static function cacheAdd($nodetype, $node) {
        if (empty($nodetype)) {
            return false;
        }
        if (!isset($node['id'])) {
            return false;
        }
        if (!isset(self::$nodeCache[$nodetype])) {
            self::$nodeCache[$nodetype] = array();
        }
        self::$nodeCache[$nodetype][$node['id']] = $node;
    }
    
    /**
     * Del a node from cache
     * @param type $nodetype
     * @param integer $nid Node ID
     */
    public static function cacheDel($nodetype, $nid) {
        unset(self::$nodeCache[$nodetype][$nid]);
    }
    
    /**
     * Get a node from cache
     * @param string $nodetype
     * @param integer $nid Node ID
     * @return array The node information, FALSE if not cached
     */
    public static function cacheGet($nodetype, $nid) {
        if (!isset(self::$nodeCache[$nodetype])) {
            return false;
        }
        if (!isset(self::$nodeCache[$nodetype][$nid])) {
            return false;
        } else {
            return self::$nodeCache[$nodetype][$nid];
        }
    }
}