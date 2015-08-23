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

class driverConfigTest extends PHPUnit_Framework_TestCase {
    
    public static function setUpBeforeClass() {
        error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
        define("CMS_VERSION", "test");
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'etc/drivers/config.php';
    }
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        chdir("tests/drivers/");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        chdir("../");
    }
    
    public static function tearDownAfterClass() {
        
    }
    
    /**
     * Test that get default if not HTTP_HOST defined
     */
    public function testLoadDefaultConfig() {
        $resp = driverConfig::getConfigFilePath();
        $this->assertEquals("etc/pharinix.config.php", $resp);
    }
    
    /**
     * Test that get default if not exist config to this HTTP_HOST
     */
    public function testLoadDefaultConfigAtNoOtherOption() {
        $_SERVER["HTTP_HOST"] = "www.example.com";
        $resp = driverConfig::getConfigFilePath();
        $this->assertEquals("etc/pharinix.config.php", $resp);
    }
    
    /**
     * Test that get the correct path if exist config to this HTTP_HOST
     */
    public function testLoadDefaultConfigAtExistOption() {
        $_SERVER["HTTP_HOST"] = "127.0.0.1";
        $resp = driverConfig::getConfigFilePath();
        $this->assertEquals("etc/127.0.0.1.pharinix.config.php", $resp);
    }
}
