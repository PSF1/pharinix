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

class commandBatchTest extends PHPUnit_Framework_TestCase {

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
//        include_once 'commandTools.php';
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public function testCommandBatch_EmptyParams_Ok() {
        $resp = driverCommand::run("batch", array(
//            "starter" => array(),
//            "commands" => array(),
//            "echoed" => "",
        ));
        $this->assertTrue(count($resp) == 0);
    }
    
    public function testCommandBatch_BadParams() {
        $resp = driverCommand::run("batch", array(
            "starter" => 1,
            "commands" => 1,
            "echoed" => 1,
        ));
        $this->assertTrue(isset($resp["ok"]));
        $this->assertNotTrue($resp["ok"]);
    }
    
    public function testCommandBatch_StarterParams() {
        $resp = driverCommand::run("batch", array(
            "starter" => array(
                "html" => "AAA",
            ),
            "commands" => array(
                array("captureStart" => ""),
                array("echoHTML" => ""),
                array("captureEnd" => ""),
                ),
            "echoed" => "",
        ));
        $this->assertTrue(isset($resp["html"]));
        $this->assertEquals("AAA", $resp["html"]);
        $this->assertTrue(isset($resp["buffer"]));
        $this->assertEquals("AAA", $resp["buffer"]);
    }
    
    public function testCommandBatch_StarterParams_WithClean() {
        $resp = driverCommand::run("batch", array(
            "starter" => array(
                "html" => "AAA",
            ),
            "commands" => array(
                array("captureStart" => ""),
                array("echoHTML" => ""),
                array("#clean" => ""),
                array("captureEnd" => ""),
                ),
            "echoed" => "",
        ));
        $this->assertNotTrue(isset($resp["html"]));
        $this->assertTrue(isset($resp["buffer"]));
        $this->assertEquals("AAA", $resp["buffer"]);
    }
    
    public function testCommandBatch_DefaultParams() {
        ob_start();
        $resp = driverCommand::run("batch", array(
            "starter" => array(),
            "commands" => array(array("echoHTML" => "html=AAA")),
            "echoed" => "",
        ));
        $echoed = ob_get_clean();
        $this->assertEquals("AAA", $echoed);
    }
    
    public function testCommandBatch_DefaultOverrideStarterParams() {
        ob_start();
        $resp = driverCommand::run("batch", array(
            "starter" => array(
                "html" => "BBB",
            ),
            "commands" => array(array("echoHTML" => "html=AAA")),
            "echoed" => "",
        ));
        $echoed = ob_get_clean();
        $this->assertEquals("AAA", $echoed);
    }
    
    public function testCommandBatch_NoEcho() {
        ob_start();
        $resp = driverCommand::run("batch", array(
            "starter" => array(),
            "commands" => array(array("newID" => "")),
            "echoed" => "",
        ));
        $echoed = ob_get_clean();
        $this->assertEquals("", $echoed);
        $this->assertTrue(count($resp) != 0);
    }
    
    public function testCommandBatch_AutoEcho() {
        ob_start();
        $resp = driverCommand::run("batch", array(
            "starter" => array(),
            "commands" => array(array("newID" => "")),
            "echoed" => "echoJson",
        ));
        $echoed = ob_get_clean();
        $this->assertNotEquals("", $echoed);
        $this->assertTrue(count($resp) == 0);
    }
}
