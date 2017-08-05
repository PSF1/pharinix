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

interface iCache {
    /**
     * Clear node's cache
     */
    public static function cacheClear();
    /**
     * Is the node cached?
     * @param string $nodetype
     * @param integer $nid
     */
    public static function isCached($nodetype, $nid);
    /**
     * Is the node cached?
     * @param string $nodetype
     * @param integer $nid
     */
    public static function cached($nodetype, $nid);
    /**
     * Add the node to the cache
     * @param string $nodetype
     * @param integer $nid
     */
    public static function cacheAdd($nodetype, $node);
    /**
     * Del a node from cache
     * @param string $nodetype
     * @param integer $nid Node ID
     */
    public static function cacheDel($nodetype, $nid);
    /**
     * Get a node from cache
     * @param string $nodetype
     * @param integer $nid Node ID
     * @return array The node information, FALSE if not cached
     */
    public static function cacheGet($nodetype, $nid);
    
}