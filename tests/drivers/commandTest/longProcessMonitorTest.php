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

class longProcessMonitorTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
        include_once 'tests/drivers/etc/commandTools.php';
        include_once 'etc/drivers/longProcessMonitor.php';
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

    public function testMonitorLiveCicle() {
        $mon = driverLPMonitor::start(10, 'Same');
        $this->assertTrue(isset($mon->id));
        $this->assertTrue(is_file(driverLPMonitor::getPath().$mon->id.'.lpm'));
        $this->assertEquals(10, $mon->stepsTotal);
        $this->assertEquals(0, $mon->step);
        
        $mon = driverLPMonitor::update($mon->id, 5, 'Step');
        $this->assertTrue(isset($mon->id));
        $this->assertEquals(10, $mon->stepsTotal);
        $this->assertEquals(5, $mon->step);
        
        $mon = driverLPMonitor::read($mon->id);
        $this->assertTrue(isset($mon->id));
        $this->assertEquals(10, $mon->stepsTotal);
        $this->assertEquals(5, $mon->step);
        
        $id = $mon->id;
        $mon = driverLPMonitor::close($mon->id);
        $this->assertTrue($mon);
        $this->assertFalse(is_file(driverLPMonitor::getPath().$id.'.lpm'));
    }
    
    public function testMonitorUpdateStepsTotalZero() {
        $mon = driverLPMonitor::start(0, 'Same');
        
        $mon = driverLPMonitor::update($mon->id, 5, 'Step');
        $this->assertEquals(0, $mon->percent);
        
        $mon = driverLPMonitor::close($mon->id);
    }
}
