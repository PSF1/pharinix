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

class commandTest extends PHPUnit_Framework_TestCase {
    protected static $userID = null;
    protected static $grpID = null;
    
    public static function setUpBeforeClass() {
//        error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
        //        include_once 'commandTools.php';
        while (!is_file("etc/pharinix.config.DEFAULT.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
        include_once 'tests/drivers/etc/commandTools.php';
        driverUser::sessionStart();
        driverUser::sudo();
        $resp = driverCommand::run("addUser", array(
            "mail" => "testlogin@localhost",
            "pass" => "testlogin",
            "name" => "testlogin",
            "title" => "testlogin",
            "group" => "testlogin",
        ));
        self::$userID = $resp["nid"];
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "group",
            "fields" => "id",
            "where" => "`title` = 'testlogin'",
        ));
        $k = array_keys($resp);
        self::$grpID = $k[0];
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
        // Return to defaults
        $fSec = getcwd()."/bin/nothing.sec";
        if (is_file($fSec)) unlink($fSec);
    }
    
    public static function tearDownAfterClass() {
        driverUser::sessionStart();
        driverUser::sudo();
        driverCommand::run("delUser", array(
            "mail" => "testlogin@localhost",
        ));
        driverUser::logOut();
    }

    public function testCommandChMod_create_sec_file() {
        driverCommand::run("chmod",array(
            "cmd" => "nothing",
            "flags" => 0,
        ));
        $fSec = getcwd()."/bin/nothing.sec";
        $this->assertTrue(is_file($fSec));
        // Return to defaults
        unlink($fSec);
    }
    
    public function testCommandChMod_access_changed_777() {
        driverCommand::run("chmod",array(
            "cmd" => "nothing",
            "flags" => driverUser::PERMISSION_FILE_OWNER_READ | 
                       driverUser::PERMISSION_FILE_OWNER_WRITE | 
                       driverUser::PERMISSION_FILE_OWNER_EXECUTE | 
                       driverUser::PERMISSION_FILE_GROUP_READ | 
                       driverUser::PERMISSION_FILE_GROUP_WRITE | 
                       driverUser::PERMISSION_FILE_GROUP_EXECUTE | 
                       driverUser::PERMISSION_FILE_ALL_READ | 
                       driverUser::PERMISSION_FILE_ALL_WRITE | 
                       driverUser::PERMISSION_FILE_ALL_EXECUTE,
        ));
        $fSec = getcwd()."/bin/nothing.sec";
        
        $acc = driverUser::secFileGetAccess(getcwd()."/bin/nothing.php");
        $this->assertTrue(decoct($acc["flags"]) == "777");
        // Return to defaults
        unlink($fSec);
    }
    
    public function testCommandChMod_access_changed_666() {
        driverCommand::run("chmod",array(
            "cmd" => "nothing",
            "flags" => 0664,
        ));
        $fSec = getcwd()."/bin/nothing.sec";
        $acc = driverUser::secFileGetAccess(getcwd()."/bin/nothing.php");
        $this->assertTrue(decoct($acc["flags"]) == "664");
        // Return to defaults
        unlink($fSec);
    }
    
    public function testCommandChOwn_bad_user_mail() {
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "owner" => "bad@test.mail",
        ));
        $this->assertFalse($resp["ok"]);
    }
    
    public function testCommandChOwn_bad_user_id() {
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "owner" => 9999999,
        ));
        $this->assertFalse($resp["ok"]);
    }
    
    public function testCommandChOwn_bad_group_title() {
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "group" => "bad@test.mail",
        ));
        $this->assertFalse($resp["ok"]);
    }
    
    public function testCommandChOwn_bad_group_id() {
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "group" => 9999999,
        ));
        $this->assertFalse($resp["ok"]);
    }
    
    public function testCommandChOwn_bad_ownership() {
        driverUser::sudo(false);
        driverUser::logIn("testlogin@localhost", "testlogin");
        // Change ownership
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "owner" => self::$userID,
        ));
        $this->assertFalse($resp["ok"]);
        driverUser::sudo();
    }
    
    public function testCommandChOwn_change_owner_by_id() {
        // Change ownership
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "owner" => (int)self::$userID,
        ));
        $this->assertTrue($resp["ok"]);
        // Verify change
        $sec = driverUser::secFileGetAccess(getcwd()."/bin/nothing.php");
        $this->assertTrue($sec["owner"] == self::$userID);
    }
    
    public function testCommandChOwn_change_owner_by_mail() {
        // Change ownership
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "owner" => "testlogin@localhost",
        ));
        $this->assertTrue($resp["ok"]);
        // Verify change
        $sec = driverUser::secFileGetAccess(getcwd()."/bin/nothing.php");
        $this->assertTrue($sec["owner"] == self::$userID);
    }
    
    public function testCommandChOwn_change_group_by_id() {
        // Change ownership
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "group" => (int)self::$grpID,
        ));
        $this->assertTrue($resp["ok"]);
        // Verify change
        $sec = driverUser::secFileGetAccess(getcwd()."/bin/nothing.php");
        $this->assertTrue($sec["group"] == self::$grpID);
    }
    
    public function testCommandChOwn_change_group_by_title() {
        // Change ownership
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "group" => "testlogin",
        ));
        $this->assertTrue($resp["ok"]);
        // Verify change
        $sec = driverUser::secFileGetAccess(getcwd()."/bin/nothing.php");
        $this->assertTrue($sec["group"] == self::$grpID);
    }
    
    public function testCommandChOwn_change_group_and_owner() {
        // Change ownership
        $resp = driverCommand::run("chown",array(
            "cmd" => "nothing",
            "owner" => (int)self::$userID,
            "group" => (int)self::$grpID,
        ));
        $this->assertTrue($resp["ok"]);
        // Verify change
        $sec = driverUser::secFileGetAccess(getcwd()."/bin/nothing.php");
        $this->assertTrue($sec["owner"] == self::$userID);
        $this->assertTrue($sec["group"] == self::$grpID);
    }
    
    public function testCommandNothingResponse() {
        $resp = driverCommand::run("nothing");
        $this->assertNull($resp);
    }
    
    public function testCommandNothingHelp() {
        $cmd = include 'bin/nothing.php';
        $help = $cmd->getHelp();
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
    
    // FAIL IN TRAVIS !!!
//    public function testRemoteGetCommand() {
//        $resp = commandTools::getURL(CMS_DEFAULT_URL_BASE."?command=getSession&interface=echoJson");
//        $this->assertContains("HTTP/1.1 200 OK", $resp["header"]);
//        $this->assertContains("Content-Type: application/json", $resp["header"]);
//        
//        $json = json_decode($resp["body"]);
//        $this->assertTrue($json->started);
//    }
//    
//    public function testRemotePostCommand() {
//        $resp = commandTools::getURL(CMS_DEFAULT_URL_BASE, array(
//            "command" => "getSession",
//            "interface" => "echoJson",
//        ));
//        
//        $this->assertContains("HTTP/1.1 200 OK", $resp["header"]);
//        $this->assertContains("Content-Type: application/json", $resp["header"]);
//        
//        $json = json_decode($resp["body"]);
//        $this->assertTrue($json->started);
//    }
}
