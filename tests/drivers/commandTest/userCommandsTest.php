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

class userCommandsTest extends PHPUnit_Framework_TestCase {
    
    public static function setUpBeforeClass() {
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
    }
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        driverUser::logOut();
        driverUser::sessionStart();
        driverUser::sudo();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        driverUser::logOut();
    }
    
    public static function tearDownAfterClass() {
        
    }

    public function testCreateUser_empty_mail_Fail() {
        $resp = driverCommand::run("addUser", array(
            "mail" => "",
            "pass" => "1",
            "name" => "aa",
            "title" => "aa",
        ));
        $this->assertNotTrue($resp["ok"]);
    }
    public function testCreateUser_empty_pass_Fail() {
        $resp = driverCommand::run("addUser", array(
            "mail" => "example@localhost",
            "pass" => "",
            "name" => "aa",
            "title" => "aa",
        ));
        $this->assertNotTrue($resp["ok"]);
    }
    public function testCreateUser_empty_name_Fail() {
        $resp = driverCommand::run("addUser", array(
            "mail" => "example@localhost",
            "pass" => "1",
            "name" => "",
            "title" => "aa",
        ));
        $this->assertNotTrue($resp["ok"]);
    }
    public function testCreateUser_title_mail_Fail() {
        $resp = driverCommand::run("addUser", array(
            "mail" => "example@localhost",
            "pass" => "1",
            "name" => "aa",
            "title" => "",
        ));
        $this->assertNotTrue($resp["ok"]);
    }
    
    public function testCreateUser_root_Fail() {
        $resp = driverCommand::run("addUser", array(
            "mail" => "root@localhost",
            "pass" => "1",
            "name" => "aa",
            "title" => "aa",
        ));
        $this->assertNotTrue($resp["ok"]);
    }

    public function testCreateUser_AddAndDel_byMail_ok() {
        $resp = driverCommand::run("addUser", array(
            "mail" => "example@localhost",
            "pass" => "1",
            "name" => "Example",
            "title" => "Example user",
        ));
        $this->assertTrue($resp["ok"]);
        // Exist default group?
        $sql = "SELECT `id` FROM `node_group` where `title` = 'Example'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Exist the new user?
        $sql = "SELECT `id` FROM `node_user` where `mail` = 'example@localhost'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Each user own himself
        $sql = "SELECT `user_owner` FROM `node_user` where `mail` = 'example@localhost'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        $this->assertEquals($resp["nid"], $q->fields["user_owner"]);
        // Delete user
        driverCommand::run("delUser", array(
            "mail" => "example@localhost",
        ));
        // Exist default group?
        $sql = "SELECT `id` FROM `node_group` where `title` = 'Example'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        // Exist the new user?
        $sql = "SELECT `id` FROM `node_user` where `mail` = 'example@localhost'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
    }
    
    public function testCreateUser_DuplicatedMail_Fail() {
        $resp = driverCommand::run("addUser", array(
            "mail" => "example@localhost",
            "pass" => "1",
            "name" => "test",
            "title" => "Test user",
        ));
        $this->assertTrue($resp["ok"]);
        $resp = driverCommand::run("addUser", array(
            "mail" => "example@localhost",
            "pass" => "1",
            "name" => "Example",
            "title" => "Example",
        ));
        $this->assertNotTrue($resp["ok"]);
        driverCommand::run("delUser", array("mail" => "example@localhost"));
    }
}
