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

class userAuthTokenTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
        include_once 'tests/drivers/etc/commandTools.php';
        
        // Add user
        driverUser::sessionStart();
        driverUser::sudo();
        $user = driverCommand::run("addUser", array(
            "mail" => "testlogin@localhost",
            "pass" => "testlogin",
            "name" => "testlogin",
            "title" => "testlogin",
        ));
        driverUser::sudo(false);
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
        driverUser::logOut();
    }
    
    public static function tearDownAfterClass() {
        // Del user
        driverUser::sessionStart();
        driverUser::sudo();
        driverCommand::run("delUser", array(
            "mail" => "testlogin@localhost",
        ));
    }

    public function testInternalGetSessionIDAtStart() {
        $resp = driverCommand::run("startSession", array(
            "user" => "testlogin@localhost",
            "pass" => "testlogin",
        ));
        $this->assertTrue($resp["ok"]);
        $this->assertEquals(session_id(), $resp["id"]);
    }
    
    public function testReuseAuthToken() {
        // Start session and capture session ID.
        $resp = commandTools::getURL(CMS_DEFAULT_URL_BASE, array(
            "command" => "startSession",
            "user" => "testlogin@localhost",
            "pass" => "testlogin",
            "interface" => "echoJson",
        ));
        $json = json_decode($resp["body"]);
        $this->assertTrue($json->ok);
        $auth = $json->id;
        // Capture user ID
        $resp = commandTools::getURL(CMS_DEFAULT_URL_BASE, array(
            "command" => "getSession",
            "auth_token" => $auth,
            "interface" => "echoJson",
        ));
        $json = json_decode($resp["body"]);
        $usrID = $json->user_id;
        // If not set auth_token the user ID must be diferent
        $resp = commandTools::getURL(CMS_DEFAULT_URL_BASE, array(
            "command" => "getSession",
            "interface" => "echoJson",
        ));
        $json = json_decode($resp["body"]);
        $guestID = $json->user_id;
        
        $this->assertNotEquals($guestID, $usrID);
        
        // Close session
        commandTools::getURL(CMS_DEFAULT_URL_BASE, array(
            "command" => "endSession",
            "auth_token" => $auth,
            "interface" => "nothing", // I don't like the response
        ));
    }
    
}
