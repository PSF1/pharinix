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

class routingTest extends PHPUnit_Framework_TestCase {
    public static $driver;
    
    public static function setUpBeforeClass() {
//        error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
        //        include_once 'commandTools.php';
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
        include_once 'tests/drivers/etc/commandTools.php';
        self::$driver = new driverUrlRewrite();
    }
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public static function tearDownAfterClass() {
        
    }

    public function dummyGetRewritedUrl($url, $map, $cmd) {
        $mapping = false;
        $parts = explode("/", $url);
        $nparts = count($parts);
        $mapResponse = self::$driver->mapParse($parts, $map);
        if ($mapResponse !== false) {
            // I find a match, I go out.
            $mapping = self::$driver->mapReplace($mapResponse, $cmd);
        }
        return $mapping;
    }

    public function testURLMapping() {
        $mapped = "";
        // Some number of variable items
        $mapped = $this->dummyGetRewritedUrl("node/user/23/json", 
                'node/$type/$id/json', 'nodetype=$type&node=$id');
        $this->assertEquals("nodetype=user&node=23", $mapped);
        $mapped = $this->dummyGetRewritedUrl("node/user", 
                'node/$type/$id/json', 'nodetype=$type&node=$id');
        $this->assertFalse($mapped);
        $mapped = $this->dummyGetRewritedUrl("node/user/23/json", 
                'node/$type/$id/json', 'node=$id');
        $this->assertEquals("node=23", $mapped);
        // Some number of items
        $mapped = $this->dummyGetRewritedUrl("node/user/23/json", 
                'node/item/$id/json', 'node=$id');
        $this->assertFalse($mapped);
        $mapped = $this->dummyGetRewritedUrl("node/user/23/json", 
                'node/user/$id/json', 'node=$type');
        $this->assertEquals("node=", $mapped);
        // Some item order
        $mapped = $this->dummyGetRewritedUrl("node/user/23/json", 
                'user/node/$id/json', 'node=$id');
        $this->assertFalse($mapped);
        $mapped = $this->dummyGetRewritedUrl("node/user/23/json", 
                'node/$id/$type/json', 'node=$id');
        $this->assertEquals("node=user", $mapped);
    }
}
