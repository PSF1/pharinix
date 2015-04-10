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
        driverCommand::run("addUser", array(
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
    
    public function testUserAddField() {
        //TODO: Change owner group of addNodeField command to allow the user to use it.
        
        // Add field
        $resp = driverCommand::run("addNodeField",array(
            "node_type" => "test",
            "name" => "newNode",
            "type" => "integer",
        ));
        // Verify
        $this->assertFalse($resp["ok"]);
    }
    
    /*
     * AddNode
     */
    
    /*
     * delNode
     */
    
    /*
     * getNode
     */
    
    /*
     * getNodes
     */
    
    /*
     * updateNode
     */
    
}