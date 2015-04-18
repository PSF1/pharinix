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

class commandNodesAccessTest extends PHPUnit_Framework_TestCase {
    
    public static function setUpBeforeClass() {
//        error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
        include_once 'tests/drivers/etc/commandTools.php';
        // Add user
        driverUser::sessionStart();
        driverUser::sudo();
        $adduser = driverCommand::run("addUser", array(
            "mail" => "testlogin2@localhost",
            "pass" => "testlogin2",
            "name" => "testlogin2",
            "title" => "testlogin2",
        ));
        $adduser = driverCommand::run("addUser", array(
            "mail" => "testlogin@localhost",
            "pass" => "testlogin",
            "name" => "testlogin",
            "title" => "testlogin",
        ));
        $user = driverCommand::run("getNodes", array(
            "nodetype" => "user",
            "where" => "`mail` = 'testlogin@localhost'",
        ));
        $usrKeys = array_keys($user);
        // Add sudoers
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
        // Add node type
        driverCommand::run("batch", array(
            "starter" => array(),
            "commands" => array(
                array("addNodeType" => "name=test"),
                array("#clean" => ""),
                array("addNodeField" => "node_type=test&name=order&type=integer"),
                array("#clean" => ""),
                array("addNode" => "nodetype=test&title=NODE%201&order=4"),
                array("#clean" => ""),
                array("addNode" => "nodetype=test&title=NODE%202&order=3"),
                array("#clean" => ""),
                array("addNode" => "nodetype=test&title=NODE%203&order=2"),
                array("#clean" => ""),
                array("addNode" => "nodetype=test&title=NODE%204&order=1"),
            ),
        ));
        driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "nid" => 1,
            "owner" => "testlogin@localhost",
        ));
        driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "nid" => 2,
            "group" => "testlogin2",
        ));
        driverCommand::run("chmodNode", array(
            "nodetype" => "test",
            "nid" => 3,
            "flags" => PERMISSION_NODE_ALL_ALL,
        ));
        // End sudo
        driverUser::sudo(false);
    }
    
    protected function setUp() {
        driverUser::sessionStart();
    }
    
    protected function tearDown() {
        driverUser::logOut();
    }
    
    public static function tearDownAfterClass() {
        driverUser::sessionStart();
        driverUser::sudo();
        driverCommand::run("delUser", array(
            "mail" => "testlogin@localhost",
        ));
        driverCommand::run("delNodeType", array("name" => "test"));
        driverUser::logOut();
    }
    
    /*
     * addNodeField
     * delNodeField
     */
    public function testRootAddField() {
        driverUser::sudo();
        // Add field
        $resp = driverCommand::run("addNodeField",array(
            "node_type" => "test",
            "name" => "newNode",
            "type" => "integer",
        ));
        // Verify
        $this->assertTrue($resp["ok"]);
        $contain = false;
        $typeDef = driverCommand::run("getNodeTypeDef", array(
                "nodetype" => "test",
            ));
        foreach($typeDef["fields"] as $fId => $fieldDef) {
            if ($fieldDef["name"] == "newnode") {
                $contain = true;
                break;
            }
        }
        $this->assertTrue($contain);
        // Del field
        driverCommand::run("delNodeField",array(
            "nodetype" => "test",
            "name" => "newNode",
        ));
        // Verify
        $typeDef = driverCommand::run("getNodeTypeDef", array(
                "nodetype" => "test",
            ));
        foreach($typeDef["fields"] as $fId => $fieldDef) {
            $this->assertTrue($fieldDef["name"] != "newnode");
        }
    }
    
    public function testUserAddField_without_permission() {
        // Change permissions of addNodeField command to allow the user to use it.
        driverUser::sudo();
        $resp = driverCommand::run("chmod", array(
            "cmd" => "addNodeField",
            "flags" => 0777,
        ));
        driverUser::sudo(false);
        driverUser::logIn("testlogin@localhost", md5("testlogin"));
        // Add field
        $resp = driverCommand::run("addNodeField",array(
            "node_type" => "test",
            "name" => "newNode",
            "type" => "integer",
        ));
        // Verify
        $this->assertFalse($resp["ok"]);
        $contain = false;
        $typeDef = driverCommand::run("getNodeTypeDef", array(
                "nodetype" => "test",
            ));
        foreach($typeDef["fields"] as $fId => $fieldDef) {
            if ($fieldDef["name"] == "newnode") {
                $contain = true;
                break;
            }
        }
        $this->assertFalse($contain);
        // Defaults
        driverUser::sudo();
        driverCommand::run("chmod", array(
            "cmd" => "addNodeField",
            "flags" => driverCommand::getAccessFlags(),
        ));
        driverUser::sudo(false);
    }
    
    /*
     * AddNode
     * delNode
     */
    // Root can add node
    public function testRootAddNode() {
        driverUser::sudo();
        driverCommand::run("addNode", array(
            "nodetype" => "test",
            "title" => "NODE 50",
            "order" => 50
           ));
        // Verify
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Undo
        driverCommand::run("delNode", array(
            "nodetype" => "test",
            "nid" => $q->fields["id"],
        ));
        // Verify
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        
        driverUser::sudo(false);
    }
    
    // Owner can add node
    public function testOwnerAddNode() {
        // Set user how node type's owner
        driverUser::sudo();
        $resp = driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "owner" => "testlogin@localhost",
        ));
        driverUser::sudo(false);
        // User login
        driverUser::logIn("testlogin@localhost", md5("testlogin"));
        // User add a node
        $resp = driverCommand::run("addNode", array(
            "nodetype" => "test",
            "title" => "NODE 50",
            "order" => 50
           ));
        // Verify that the node is added
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Remove node
        driverCommand::run("delNode", array(
            "nodetype" => "test",
            "nid" => $q->fields["id"],
        ));
        // Verify that it's removed
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        // Log out
        driverUser::logOut();
        // Set root how owner of node type
        driverUser::sudo();
        driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "owner" => "0",
        ));
        driverUser::sudo(false);
    }
    
    // Owner group can add node
    public function testOwnerGroupAddNode() {
        // Set user how node type's owner
        driverUser::sudo();
        $resp = driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "group" => "testlogin",
        ));
        $resp = driverCommand::run("chmodNode", array(
            "nodetype" => "test",
            "flags" => PERMISSION_NODE_GROUP_ALL,
        ));
        driverUser::sudo(false);
        // User login
        driverUser::logIn("testlogin@localhost", md5("testlogin"));
        // User add a node
        $resp = driverCommand::run("addNode", array(
            "nodetype" => "test",
            "title" => "NODE 50",
            "order" => 50
           ));
        // Verify that the node is added
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Remove node
        driverCommand::run("delNode", array(
            "nodetype" => "test",
            "nid" => $q->fields["id"],
        ));
        // Verify that it's removed
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        // Log out
        driverUser::logOut();
        // Set root how owner of node type
        driverUser::sudo();
        driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "group" => "0",
        ));
        driverUser::sudo(false);
    }
    
    // Others can add node
    public function testGuestCanAddNode() {
        // Set user how node type's owner
        driverUser::sudo();
        $resp = driverCommand::run("chmodNode", array(
            "nodetype" => "test",
            "flags" => PERMISSION_NODE_ALL_ALL,
        ));
        driverUser::sudo(false);
        // User logout
        driverUser::logout();
        // User add a node
        $resp = driverCommand::run("addNode", array(
            "nodetype" => "test",
            "title" => "NODE 50",
            "order" => 50
           ));
        // Verify that the node is added
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Remove node
        driverCommand::run("delNode", array(
            "nodetype" => "test",
            "nid" => $q->fields["id"],
        ));
        // Verify that it's removed
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        // Set root how owner of node type
        driverUser::sudo();
        $resp = driverCommand::run("chmodNode", array(
            "nodetype" => "test",
            "flags" => PERMISSION_NODE_DEFAULT,
        ));
        driverUser::sudo(false);
    }
    
    // Others can't add node
    public function testGuestCantAddNode() {
        // User logout
        driverUser::logOut();
        // User add a node
        $resp = driverCommand::run("addNode", array(
            "nodetype" => "test",
            "title" => "NODE 50",
            "order" => 50
           ));
        $this->assertFalse($resp["ok"]);
        // Verify that it is not added
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
    }
    
    /*
     * updateNode
     */
    // Root can add node
    public function testRootUpdateNode() {
        driverUser::sudo();
        driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "nid" => 1,
            "title" => "NODE 50",
           ));
        // Verify
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Undo
        driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "nid" => 1,
            "title" => "NODE 1",
           ));
        // Verify
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        
        driverUser::sudo(false);
    }
    
    // Owner can add node
    public function testOwnerUpdateNode() {
        // Set user how node type's owner
        driverUser::sudo();
        $resp = driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "owner" => "testlogin@localhost",
        ));
        driverUser::sudo(false);
        // User login
        driverUser::logIn("testlogin@localhost", md5("testlogin"));
        // User add a node
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "title" => "NODE 50",
            "nid" => 1
           ));
        // Verify that the node is added
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Remove node
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "title" => "NODE 1",
            "nid" => 1
           ));
        // Verify that it's removed
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        // Log out
        driverUser::logOut();
        // Set root how owner of node type
        driverUser::sudo();
        driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "owner" => "0",
        ));
        driverUser::sudo(false);
    }
    
    // Owner group can add node
    public function testOwnerGroupUpdateNode() {
        // Set user how node type's owner
        driverUser::sudo();
        $resp = driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "group" => "testlogin",
        ));
        $resp = driverCommand::run("chmodNode", array(
            "nodetype" => "test",
            "flags" => PERMISSION_NODE_GROUP_ALL,
        ));
        driverUser::sudo(false);
        // User login
        driverUser::logIn("testlogin@localhost", md5("testlogin"));
        // User add a node
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "title" => "NODE 50",
            "nid" => 1
           ));
        // Verify that the node is added
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Remove node
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "title" => "NODE 1",
            "nid" => 1
           ));
        // Verify that it's removed
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        // Log out
        driverUser::logOut();
        // Set root how owner of node type
        driverUser::sudo();
        driverCommand::run("chownNode", array(
            "nodetype" => "test",
            "group" => "0",
        ));
        driverUser::sudo(false);
    }
    
    // Others can add node
    public function testGuestCanUpdateNode() {
        // Set user how node type's owner
        driverUser::sudo();
        $resp = driverCommand::run("chmodNode", array(
            "nodetype" => "test",
            "flags" => PERMISSION_NODE_ALL_ALL,
        ));
        driverUser::sudo(false);
        // User logout
        driverUser::logout();
        // User add a node
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "title" => "NODE 50",
            "nid" => 1
           ));
        // Verify that the node is added
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Remove node
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "title" => "NODE 1",
            "nid" => 1
           ));
        // Verify that it's removed
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        // Set root how owner of node type
        driverUser::sudo();
        $resp = driverCommand::run("chmodNode", array(
            "nodetype" => "test",
            "flags" => PERMISSION_NODE_DEFAULT,
        ));
        driverUser::sudo(false);
    }
    
    // Others can't add node
    public function testGuestCantUpdateNode() {
        // User logout
        driverUser::logOut();
        // User add a node
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "title" => "NODE 50",
            "nid" => 1
           ));
        $this->assertFalse($resp["ok"]);
        // Verify that it is not added
        $sql = "select * from `node_test` where `title` = 'NODE 50'";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
    }
    
    /*
     * getNodes
     * getNode
     */
    // Root can read all
    public function testRootCanReadAllNodes() {
        driverUser::sudo();
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
        ));
        // All
        $this->assertEquals(4, count($resp));
        driverUser::sudo(false);
    }
    
    // Owner can read only her nodes
    public function testOwnerCanReadNodes() {
        // User login
        driverUser::logIn("testlogin@localhost", md5("testlogin"));
        // Read
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
        ));
        // 1 by owned + 1 by all
        $this->assertEquals(2, count($resp));
        // User logout
        driverUser::logOut();
    }
    
    // Owner group can read only her nodes
    public function testGroupCanReadNodes() {
        // User login
        driverUser::logIn("testlogin2@localhost", md5("testlogin2"));
        // Read
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
        ));
        // 1 by group + 1 by all
        $this->assertEquals(2, count($resp));
        // User logout
        driverUser::logOut();
    }
    
    // Others can read only her nodes
    public function testGuestCanReadNodes() {
        // User logout
        driverUser::logOut();
        // Read
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
        ));
        // 1 by all
        $this->assertEquals(1, count($resp));
    }
}