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

class driverMemcached implements iCache {
    
    protected static $conn = null;
    protected static $MEMCACHE_LIFE = 2400;
    
    /**
     * Enable and return a valid memcached conection, if it's posible.
     * @return Memcached NULL if it's not posible
     */
    protected static function getConn() {
        // Memcached extension is required
        if (!class_exists('Memcached')) {
            return null;
        }
        $cfgNodetypeGrp = driverConfig::getCFG()->getSection('[nodetypes]');
        if ($cfgNodetypeGrp == null) {
            return null;
        }
        $cfgNodeTypeCacheActive = $cfgNodetypeGrp->getAsBoolean('USAGE');
        if (!$cfgNodeTypeCacheActive) {
            return null;
        }
                
        if (self::$conn == null) {
            self::$conn = new Memcached();
            $MEMCACHE_HOSTS = explode(',', $cfgNodetypeGrp->get('MEMCACHE_HOSTS'));
            $MEMCACHE_PORT = intval($cfgNodetypeGrp->get('MEMCACHE_PORT'));
            $MEMCACHE_COMPRESS = $cfgNodetypeGrp->getAsBoolean('MEMCACHE_COMPRESS');
            self::$MEMCACHE_LIFE = intval($cfgNodetypeGrp->get('MEMCACHE_LIFE'));
            foreach($MEMCACHE_HOSTS as $MEMCACHE_HOST) {
                self::$conn->addServer($MEMCACHE_HOST, $MEMCACHE_PORT);
            }
            self::$conn->setOption(Memcached::OPT_COMPRESSION, $MEMCACHE_COMPRESS);
        }
        return self::$conn;
    }
    
    /**
     * Clear node's cache
     */
    public static function cacheClear() {
        $m = self::getConn();
        if ($m != null) {
            $m->flush();
        }
    }
    
    public static function isCached($nodetype, $nid) {
        return self::cached($nodetype, $nid);
    }
    
    public static function cached($nodetype, $nid) {
        $m = self::getConn();
        if ($m != null) {
            return $m->get("node.$nodetype.$nid") !== false;
        }
        return false;
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
        
        $m = self::getConn();
        if ($m != null) {
            if (self::isCached($nodetype, $node['id'])) {
                $m->delete("node.$nodetype.{$node['id']}");
            }
            $m->add("node.$nodetype.{$node['id']}", $node, self::$MEMCACHE_LIFE);
            return true;
        }
        return false;
    }
    
    /**
     * Del a node from cache
     * @param type $nodetype
     * @param integer $nid Node ID
     */
    public static function cacheDel($nodetype, $nid) {
        $m = self::getConn();
        if ($m != null) {
            return $m->delete("node.$nodetype.$nid") !== false;
        }
        return false;
    }
    
    /**
     * Get a node from cache
     * @param string $nodetype
     * @param integer $nid Node ID
     * @return array The node information, FALSE if not cached
     */
    public static function cacheGet($nodetype, $nid) {
        $m = self::getConn();
        if ($m != null) {
            return $m->get("node.$nodetype.$nid");
        }
        return false;
    }
}