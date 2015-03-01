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

class commandRewriteURLTest extends PHPUnit_Framework_TestCase {

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
//        include_once 'commandTools.php';
        while (!is_file("config/config.php")) {
            chdir("../");
        }
        include_once 'config/config.php';
        include_once("libs/adodb/cmsapi.php");
        include_once("drivers/tools.php");
        include_once("drivers/command.php");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public function testCommandAddDelURL() {
        // New URL
        $resp = driverCommand::run("addUrl", array("url" => "phpunit/test", "cmd" => "command=nothing"));
        $this->assertEquals(true, $resp["ok"]);
        // Duplicate URL
        $resp = driverCommand::run("addUrl", array("url" => "phpunit/test", "cmd" => "command=nothing"));
        $this->assertEquals(false, $resp["ok"]);
        // Del URL
        driverCommand::run("delUrl", array("url" => "phpunit/test"));
        $sql = "SELECT `id` FROM `url_rewrite` where `url` = 'phpunit/test'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(true, $q->EOF);
    }
}
