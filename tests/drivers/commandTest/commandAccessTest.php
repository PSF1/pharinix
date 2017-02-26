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

class commandAccessTest extends PHPUnit_Framework_TestCase {

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
//        include_once 'commandTools.php';
        while (!is_file("etc/pharinix.config.DEFAULT.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
        driverUser::sessionStart();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        driverUser::logOut();
    }

    public function testCommand_Default_Access_Guest_fail() {
        $resp = driverCommand::getAccess();
        $this->assertFalse($resp);
    }

    public function testCommand_Default_Access_Root_ok() {
        driverUser::sudo();
        $resp = driverCommand::getAccess();
        $this->assertTrue($resp);
    }

    public function testCommand_Access_Root_Unsudo_ok() {
        // Not
        $resp = driverCommand::getAccess();
        $this->assertFalse($resp);
        // Yes
        driverUser::sudo();
        $resp = driverCommand::getAccess();
        $this->assertTrue($resp);
        // Not
        driverUser::sudo(false);
        $resp = driverCommand::getAccess();
        $this->assertFalse($resp);
    }
}
