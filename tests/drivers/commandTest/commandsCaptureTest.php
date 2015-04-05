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

class commandsCaptureTest extends PHPUnit_Framework_TestCase {

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
        driverUser::sessionStart();
        driverUser::sudo();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public function testCommandBatch_Capture_cleanAuto() {
        $resp = driverCommand::run("batch", array(
            "starter" => array(),
            "commands" => array(
                array("captureStart" => ""),
                array("echoHTML" => "html=Hi all"),
            ),
            "echoed" => "",
        ));
        ob_end_clean();
        $this->assertTrue(is_array($resp));
        $this->assertTrue(count($resp) == 0);
    }
    
    public function testCommandBatch_Capture_1buffer() {
        $resp = driverCommand::run("batch", array(
            "starter" => array(),
            "commands" => array(
                array("captureStart" => ""),
                array("echoHTML" => "html=Hi all"),
                array("captureEnd" => ""),
                /* If the next two lines are uncomment PHPUnit halt... :\ */
//                array("registerDebug" => ""),
//                array("echoJson" => ""),
            ),
            "echoed" => "",
        ));
        $this->assertEquals("Hi all", $resp["buffer"]);
    }
    
    public function testCommandBatch_Capture_Stacked_buffer() {
        $resp = driverCommand::run("batch", array(
            "starter" => array(),
            "commands" => array(
                array("captureStart" => ""), // Init first capture
                array("captureStart" => ""), // Init second capture
                array("echoHTML" => "html=Hi all"), // Echo something in second capture
                array("captureEnd" => ""), // End second capture, buffer contains "Hi all"
                array("#clean" => ""), // Clear batch parameters stack
                array("captureEnd" => ""), // End first capture, buffer must be empty 
            ),
            "echoed" => "",
        ));
        $this->assertEquals("", $resp["buffer"]);
    }
}
