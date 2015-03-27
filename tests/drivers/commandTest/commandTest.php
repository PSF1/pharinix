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

class commandTest extends PHPUnit_Framework_TestCase {

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
//        include_once 'commandTools.php';
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'etc/pharinix.config.php';
        include_once("usr/adodb/cmsapi.php");
        include_once("etc/drivers/tools.php");
        include_once("etc/drivers/command.php");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    public function testCommandNothingResponse() {
        $resp = driverCommand::run("nothing");
        $this->assertNull($resp);
    }
    
    public function testCommandNothingHelp() {
        $help = commandNothing::getHelp();
        $this->assertEquals(0, count($help["parameters"]));
        $this->assertEquals(0, count($help["response"]));
        $this->assertEquals(true, is_string($help["description"]));
    }
    
    public function testCommandTraceResponse() {
        $resp = driverCommand::run("trace");
        $this->assertNull($resp);
    }
    
    public function testCommandTraceOutput() {
        global $output;
        $output = array();
        $resp = driverCommand::run("trace");
        $this->assertArrayHasKey("trace", $output);
        $this->assertArrayHasKey(0, $output["trace"]);
    }
    
    public function testCommandTraceHelp() {
        $help = commandTrace::getHelp();
        $this->assertEquals(3, count($help["parameters"]));
        $this->assertEquals(0, count($help["response"]));
        $this->assertEquals(true, is_string($help["description"]));
    }
    
    public function testCommandEchoHtmlResponse() {
        ob_clean();
        ob_start();
        driverCommand::run("echoHTML", array("html" => "Hi world"));
        $resp = ob_get_contents();
        ob_end_clean();
        $this->assertEquals("Hi world", $resp);
    }
    
    public function testCommandEchoHtmlPHPResponse() {
        ob_clean();
        ob_start();
        driverCommand::run("echoHTML", array("html" => "<?php echo 'Hi world';?>"));
        $resp = ob_get_contents();
        ob_end_clean();
        $this->assertEquals("Hi world", $resp);
    }
}
