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

class userPermissionsTest extends PHPUnit_Framework_TestCase {
    
    public static function setUpBeforeClass() {
        error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
        while (!is_file("etc/pharinix.config.DEFAULT.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
        include_once 'tests/drivers/etc/commandTools.php';
    }
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        // Add user
        driverUser::sessionStart();
        driverUser::sudo();
        $user = driverCommand::run("addUser", array(
            "mail" => "testlogin@localhost",
            "pass" => "testlogin",
            "name" => "testlogin",
            "title" => "testlogin",
            "group" => "testlogin",
        ));
        driverUser::sudo(false);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        // Delete user
        driverUser::sessionStart();
        driverUser::sudo();
        driverCommand::run("delUser", array(
            "mail" => "testlogin@localhost",
        ));
    }
    
    public static function tearDownAfterClass() {
        driverUser::logOut();
    }

    // Sessions
    public function testUserHaveGroup() {
        driverUser::logOut();
        driverUser::logIn("testlogin@localhost", "testlogin");
        // Tests
        $this->assertTrue(driverUser::haveGroup("testlogin"));
        $this->assertFalse(driverUser::haveGroup("sudoers"));
    }
    
    public function testSessionAutoStart_Guest() {
        driverUser::logOut();
        driverUser::sessionStart();
        $this->assertTrue($_SESSION["user_guest_id"] == $_SESSION["user_id"]);
    }
    
    public function testSessionLogin_Guest() {
        driverUser::logOut();
        driverUser::logIn("guest@localhost", "");
        $this->assertTrue($_SESSION["user_guest_id"] == $_SESSION["user_id"]);
    }
    
    public function testSessionStarted() {
        driverUser::logOut();
        driverUser::sessionStart();
        $this->assertTrue(isset($_SESSION["started"]));
        $this->assertEquals(0, $_SESSION["user_root_id"]);
        $this->assertTrue(is_array($_SESSION["user_groups"]));
        $this->assertEquals(0, $_SESSION["is_loged"]);
    }
    
    public function testSessionLoged() {
        driverUser::logOut();
        driverUser::sessionStart();
        driverUser::sudo();
        $user = driverCommand::run("getNodes", array(
            "nodetype" => "user",
            "where" => "`mail` = 'testlogin@localhost'",
        ));
        $user = array_keys($user);
        // Login with it
        driverUser::logOut();
        driverUser::logIn("testlogin@localhost", "testlogin");
        
        $this->assertTrue(isset($_SESSION["started"]));
        $this->assertEquals(0, $_SESSION["user_root_id"]);
        $this->assertEquals($user[0], $_SESSION["user_id"]);
        $this->assertTrue(is_array($_SESSION["user_groups"]));
        $this->assertTrue(driverUser::isLoged());
        // Logout
        driverUser::logOut();
    }
    
    public function testSessionSudo() {
        driverUser::logOut();
        driverUser::sessionStart();
        driverUser::sudo();
        // Add sudoers to testlogin@localhost
        $user = driverCommand::run("getNodes", array(
            "nodetype" => "user",
            "where" => "`mail` = 'testlogin@localhost'",
        ));
        $usrKeys = array_keys($user);
        $group = driverCommand::run("getNodes", array(
            "nodetype" => "group",
            "where" => "`title` = 'sudoers'",
        ));
        $grpKeys = array_keys($group);
        $ngrps = implode(",",$user[$usrKeys[0]]["groups"]).",".$grpKeys[0];
        $user[$usrKeys[0]]["groups"] = $ngrps;
        $nnode = array_merge($user[$usrKeys[0]],
            array(
                "nodetype" => "user",
                "nid" => $usrKeys[0],
            ));
        unset($nnode["pass"]);
        driverCommand::run("updateNode", $nnode);
        driverUser::logOut();
        // Command sudo
        driverUser::logIn("testlogin@localhost", "testlogin");
        $this->assertEquals($usrKeys[0], $_SESSION["user_id"]);
        driverCommand::run("sudo", array(
            "user" => "root@localhost",
        ));
        $this->assertEquals($usrKeys[0], $_SESSION["sudo_user_id"]);
        $this->assertEquals(0, $_SESSION["user_id"]);
        // Test driverUser::getID(true)
        $this->assertEquals($usrKeys[0], driverUser::getID(true));
        // Test driverUser::getID(false)
        $this->assertEquals(0, driverUser::getID());
        
        $this->assertTrue(count($_SESSION["user_groups"]) > 0);
        $this->assertEquals(0, $_SESSION["user_groups"][0]);
    }
    
    // FAIL IN TRAVIS !!!
    // Use of sudo remote command
//    public function testSudoAndUnsudo() {
//        driverUser::logOut();
//        driverUser::sessionStart();
//        driverUser::sudo();
//        // Add sudoers to testlogin@localhost
//        $user = driverCommand::run("getNodes", array(
//            "nodetype" => "user",
//            "where" => "`mail` = 'testlogin@localhost'",
//        ));
//        $usrKeys = array_keys($user);
//        $group = driverCommand::run("getNodes", array(
//            "nodetype" => "group",
//            "where" => "`title` = 'sudoers'",
//        ));
//        $grpKeys = array_keys($group);
//        $ngrps = implode(",",$user[$usrKeys[0]]["groups"]).",".$grpKeys[0];
//        $user[$usrKeys[0]]["groups"] = $ngrps;
//        $nnode = array_merge($user[$usrKeys[0]],
//            array(
//                "nodetype" => "user",
//                "nid" => $usrKeys[0],
//            ));
//        unset($nnode["pass"]);
//        driverCommand::run("updateNode", $nnode);
//        driverUser::logOut();
//        // Login
//        $resp = commandTools::getURL(CMS_DEFAULT_URL_BASE, array(
//            "command" => "startSession",
//            "user" => "testlogin@localhost",
//            "pass" => "testlogin",
//            "interface" => "echoJson",
//        ));
//        $json = json_decode($resp["body"]);
//        $auth = $json->id;
//        $this->assertTrue($json->ok);
//        // Get user ID
//        $usr = commandTools::getSessionObject($auth);
//        // Command sudo
//        $resp = commandTools::getURL(CMS_DEFAULT_URL_BASE, array(
//            "command" => "sudo",
//            "auth_token" => $auth,
//            "user" => "root@localhost",
//        ));
//        $sudo = commandTools::getSessionObject($auth);
//        
//        $this->assertEquals($usrKeys[0], $sudo->sudo_user_id);
//        $this->assertEquals(0, $sudo->user_id);
//        
//        $this->assertTrue(count($sudo->user_groups) > 0);
//        $grps = (array)$sudo->user_groups;
//        $this->assertEquals(0, $grps[0]);
//        // Command un-sudo
//        $resp = commandTools::getURL(CMS_DEFAULT_URL_BASE, array(
//            "command" => "sudo",
//            "auth_token" => $auth,
//            "interface" => "echoJson",
//        ));
//        $unsudo = json_decode($resp["body"]);
//        $this->assertTrue($unsudo->ok);
//        
//        $sudo = commandTools::getSessionObject($auth);
//        $this->assertEquals($usrKeys[0], $sudo->user_id);
//        $this->assertFalse(isset($sudo->sudo_user_id));
//    }
    
    public function testSessionLogin_NoDataBase() {
        dbConn::$lockConnection = true; // Simulate no database connection
        $this->assertNotTrue(dbConn::haveConnection());
        $_SESSION = array(); // Reset session information
        driverUser::logOut();
        driverUser::logIn("guest@localhost", "");
        $this->assertTrue($_SESSION["user_guest_id"] == $_SESSION["user_id"]);
        $this->assertTrue($_SESSION["user_guest_id"] == 1);
        dbConn::$lockConnection = false;
    }
    // END Session
    
    // Files Security ----------------------------------------------------------
    // READ
    /**
     * Can owner read in each case?
     */
    public function testCanOwnerRead() {
        $b = "000000000";
        for($i = 8; $i >= 0; --$i) {
            $b1 = $b;
            $b1[$i] = 1;
            $can = driverUser::secFileCanRead(bindec($b1), true, false);
            if ($i == 0 || $i == 6) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerRead_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanRead($i, true, false);
            if ((bool)($i & driverUser::PERMISSION_FILE_ALL_READ) || 
                (bool)($i & driverUser::PERMISSION_FILE_OWNER_READ)) {
                $this->assertTrue($can, decbin($i));
            } else {
                $this->assertNotTrue($can, decbin($i));
            }
        }
    }
    
    public function testCanOwnerGroupREAD_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanRead($i, true, true);
            if ($i & (driverUser::PERMISSION_FILE_ALL_READ | driverUser::PERMISSION_FILE_GROUP_READ | driverUser::PERMISSION_FILE_OWNER_READ)) {
                $this->assertTrue($can, decbin($i));
            } else {
                $this->assertNotTrue($can, decbin($i));
            }
        }
    }
    
    /**
     * Can group read in each case?
     */
    public function testCanGroupRead() {
        $b = "000000000";
        for($i = 8; $i >= 0; --$i) {
            $b1 = $b;
            $b1[$i] = 1;
            $can = driverUser::secFileCanRead(bindec($b1), false, true);
            if ($i == 3 || $i == 6) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanGroupRead_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanRead($i, false, true);
            if ($i & driverUser::PERMISSION_FILE_ALL_READ || $i & driverUser::PERMISSION_FILE_GROUP_READ) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    /**
     * Can all read in each case?
     */
    public function testCanAllRead() {
        $b = "000000000";
        for($i = 8; $i >= 0; --$i) {
            $b1 = $b;
            $b1[$i] = 1;
            $can = driverUser::secFileCanRead(bindec($b1), false, false);
            if ($i == 6) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    /**
     * Can all read in each case?
     */
    public function testCanAllRead_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanRead($i, false, false);
            if ($i & driverUser::PERMISSION_FILE_ALL_READ) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    // END READ
    // WRITE
    /**
     * Can owner WRITE in each case?
     */
    public function testCanOwnerWRITE() {
        $b = "000000000";
        for($i = 8; $i >= 0; --$i) {
            $b1 = $b;
            $b1[$i] = 1;
            $can = driverUser::secFileCanWrite(bindec($b1), true, false);
            if ($i == 1 || $i == 7) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerWRITE_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanWrite($i, true, false);
            if ((bool)($i & driverUser::PERMISSION_FILE_ALL_WRITE) || 
                (bool)($i & driverUser::PERMISSION_FILE_OWNER_WRITE)) {
                $this->assertTrue($can, decbin($i));
            } else {
                $this->assertNotTrue($can, decbin($i));
            }
        }
    }
    
    public function testCanOwnerGroupWRITE_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanWrite($i, true, true);
            if ($i & (driverUser::PERMISSION_FILE_ALL_WRITE | driverUser::PERMISSION_FILE_GROUP_WRITE | driverUser::PERMISSION_FILE_OWNER_WRITE)) {
                $this->assertTrue($can, decbin($i));
            } else {
                $this->assertNotTrue($can, decbin($i));
            }
        }
    }
    
    /**
     * Can group WRITE in each case?
     */
    public function testCanGroupWRITE() {
        $b = "000000000";
        for($i = 8; $i >= 0; --$i) {
            $b1 = $b;
            $b1[$i] = 1;
            $can = driverUser::secFileCanWrite(bindec($b1), false, true);
            if ($i == 4 || $i == 7) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanGroupWRITE_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanWrite($i, false, true);
            if ($i & driverUser::PERMISSION_FILE_ALL_WRITE || $i & driverUser::PERMISSION_FILE_GROUP_WRITE) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    /**
     * Can all WRITE in each case?
     */
    public function testCanAllWRITE() {
        $b = "000000000";
        for($i = 8; $i >= 0; --$i) {
            $b1 = $b;
            $b1[$i] = 1;
            $can = driverUser::secFileCanWrite(bindec($b1), false, false);
            if ($i == 7) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    /**
     * Can all WRITE in each case?
     */
    public function testCanAllWRITE_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanWrite($i, false, false);
            if ($i & driverUser::PERMISSION_FILE_ALL_WRITE) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    // END WRITE
    // EXECUTE
    /**
     * Can owner EXECUTE in each case?
     */
    public function testCanOwnerEXECUTE() {
        $b = "000000000";
        for($i = 8; $i >= 0; --$i) {
            $b1 = $b;
            $b1[$i] = 1;
            $can = driverUser::secFileCanExecute(bindec($b1), true, false);
            if ($i == 2 || $i == 8) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerEXECUTE_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanExecute($i, true, false);
            if ((bool)($i & driverUser::PERMISSION_FILE_ALL_EXECUTE) || 
                (bool)($i & driverUser::PERMISSION_FILE_OWNER_EXECUTE)) {
                $this->assertTrue($can, decbin($i));
            } else {
                $this->assertNotTrue($can, decbin($i));
            }
        }
    }
    
    public function testCanOwnerGroupEXECUTE_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanExecute($i, true, true);
            if ($i & (driverUser::PERMISSION_FILE_ALL_EXECUTE | driverUser::PERMISSION_FILE_GROUP_EXECUTE | driverUser::PERMISSION_FILE_OWNER_EXECUTE)) {
                $this->assertTrue($can, decbin($i));
            } else {
                $this->assertNotTrue($can, decbin($i));
            }
        }
    }
    
    /**
     * Can group EXECUTE in each case?
     */
    public function testCanGroupEXECUTE() {
        $b = "000000000";
        for($i = 8; $i >= 0; --$i) {
            $b1 = $b;
            $b1[$i] = 1;
            $can = driverUser::secFileCanExecute(bindec($b1), false, true);
            if ($i == 5 || $i == 8) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanGroupEXECUTE_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanExecute($i, false, true);
            if ($i & driverUser::PERMISSION_FILE_ALL_EXECUTE || $i & driverUser::PERMISSION_FILE_GROUP_EXECUTE) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    /**
     * Can all EXECUTE in each case?
     */
    public function testCanAllEXECUTE() {
        $b = "000000000";
        for($i = 8; $i >= 0; --$i) {
            $b1 = $b;
            $b1[$i] = 1;
            $can = driverUser::secFileCanExecute(bindec($b1), false, false);
            if ($i == 8) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    /**
     * Can all EXECUTE in each case?
     */
    public function testCanAllEXECUTE_Combined() {
        for($i = 0; $i < 512; ++$i) {
            $can = driverUser::secFileCanExecute($i, false, false);
            if ($i & driverUser::PERMISSION_FILE_ALL_EXECUTE) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    // END EXECUTE

    // NODES Security ----------------------------------------------------------
    // Node create
    public function testCanAllCreateNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanCreate($i, false, false);
            if ($i & driverUser::PERMISSION_NODE_ALL_CREATE) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanGroupCreateNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanCreate($i, false, true);
            if ($i & (driverUser::PERMISSION_NODE_GROUP_CREATE | driverUser::PERMISSION_NODE_ALL_CREATE)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerCreateNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanCreate($i, true, false);
            if ($i & (driverUser::PERMISSION_NODE_OWNER_CREATE | driverUser::PERMISSION_NODE_ALL_CREATE)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerGroupCreateNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanCreate($i, true, true);
            if ($i & (driverUser::PERMISSION_NODE_OWNER_CREATE | driverUser::PERMISSION_NODE_GROUP_CREATE | driverUser::PERMISSION_NODE_ALL_CREATE)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    // Node read
    public function testCanAllReadNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanRead($i, false, false);
            if ($i & driverUser::PERMISSION_NODE_ALL_READ) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanGroupReadNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanRead($i, false, true);
            if ($i & (driverUser::PERMISSION_NODE_GROUP_READ |  driverUser::PERMISSION_NODE_ALL_READ)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerReadNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanRead($i, true, false);
            if ($i & (driverUser::PERMISSION_NODE_OWNER_READ | driverUser::PERMISSION_NODE_ALL_READ)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerGroupReadNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanRead($i, true, true);
            if ($i & (driverUser::PERMISSION_NODE_OWNER_READ | driverUser::PERMISSION_NODE_GROUP_READ | driverUser::PERMISSION_NODE_ALL_READ)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    // Node update
    public function testCanAllUpdateNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanUpdate($i, false, false);
            if ($i & driverUser::PERMISSION_NODE_ALL_UPDATE) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanGroupUpdateNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanUpdate($i, false, true);
            if ($i & (driverUser::PERMISSION_NODE_GROUP_UPDATE | driverUser::PERMISSION_NODE_ALL_UPDATE)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerUpdateNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanUpdate($i, true, false);
            if ($i & (driverUser::PERMISSION_NODE_OWNER_UPDATE | driverUser::PERMISSION_NODE_ALL_UPDATE)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerGroupUpdateNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanUpdate($i, true, true);
            if ($i & (driverUser::PERMISSION_NODE_OWNER_UPDATE | driverUser::PERMISSION_NODE_GROUP_UPDATE | driverUser::PERMISSION_NODE_ALL_UPDATE)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    // Node delete
    public function testCanAllDelNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanDelete($i, false, false);
            if ($i & driverUser::PERMISSION_NODE_ALL_DEL) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanGroupDelNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanDelete($i, false, true);
            if ($i & (driverUser::PERMISSION_NODE_GROUP_DEL | driverUser::PERMISSION_NODE_ALL_DEL)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerDelNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanDelete($i, true, false);
            if ($i & (driverUser::PERMISSION_NODE_OWNER_DEL | driverUser::PERMISSION_NODE_ALL_DEL)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
    
    public function testCanOwnerGroupDelNode_Combined() {
        for($i = 0; $i < 2048; ++$i) {
            $can = driverUser::secNodeCanDelete($i, true, true);
            if ($i & (driverUser::PERMISSION_NODE_OWNER_DEL | driverUser::PERMISSION_NODE_GROUP_DEL | driverUser::PERMISSION_NODE_ALL_DEL)) {
                $this->assertTrue($can);
            } else {
                $this->assertNotTrue($can);
            }
        }
    }
//    public function testCanPerformance() {
//        $stime = microtime(TRUE);
//        for($i = 0; $i < 1000000; ++$i) {
//            driverUser::secTestExecute(511, false, false);
//        }
//        $etime = microtime(TRUE);
//        $c = ($etime - $stime);
//        $this->assertLessThan(7, $c);
//    }
}
