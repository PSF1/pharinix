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

class commandNodesQueryTest extends PHPUnit_Framework_TestCase {
    
    protected function setUp() {
//        include_once 'commandTools.php';
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'etc/pharinix.config.php';
        include_once("usr/adodb/cmsapi.php");
        include_once("etc/drivers/tools.php");
        include_once("etc/drivers/command.php");
        
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
    }
    
    protected function tearDown() {
        driverCommand::run("delNodeType", array("name" => "test"));
    }
    
    public function cleanDatabase($id, $node = "testtype") {
        driverCommand::run("delNodeType", array("name" => $node));
    } 
    
    public function testGetNode_test_1() {
        $resp = driverCommand::run("getNode", array("nodetype" => "test", "node" => 1));
        $this->assertArrayHasKey(1, $resp);
        $this->assertEquals("4", $resp[1]["order"]);
        $this->assertNotEquals("", $resp[1]["modified"]);
        $this->assertNotEquals("", $resp[1]["created"]);
        $this->assertEquals("NODE 1", $resp[1]["title"]);
    }
    
    public function testGetNode_test_4() {
        $resp = driverCommand::run("getNode", array("nodetype" => "test", "node" => 4));
        $this->assertArrayHasKey(4, $resp);
        $this->assertEquals("1", $resp[4]["order"]);
        $this->assertNotEquals("", $resp[4]["modified"]);
        $this->assertNotEquals("", $resp[4]["created"]);
        $this->assertEquals("NODE 4", $resp[4]["title"]);
    }
    
    public function testGetNodes_Default() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            ));
        $this->assertTrue(count($resp) == 4);
    }
    
    public function testGetNodes_Fields_With_ID() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "fields" => "id, order, title",
            ));
        $this->assertTrue(count($resp) == 4);
        foreach($resp as $id => $node) {
            $this->assertEquals("NODE $id", $node["title"]);
            $this->assertEquals(5 - $id, $node["order"]);
        }
    }
    
    public function testGetNodes_Fields_Without_ID() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "fields" => "`order`, title",
            ));
        $this->assertTrue(count($resp) == 4);
        foreach($resp as $id => $node) {
            $this->assertEquals("NODE $id", $node["title"]);
            $this->assertEquals(5 - $id, $node["order"]);
        }
    }
    
    public function testGetNodes_Where() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "where" => "`order` = 4",
            ));
        $this->assertTrue(count($resp) == 1);
        $this->assertEquals("NODE 1", $resp[1]["title"]);
        $this->assertEquals(4, $resp[1]["order"]);
    }
    
    public function testGetNodes_Where_2() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "where" => "`order` = 4 || `title` like '%2'",
            ));
        $this->assertTrue(count($resp) == 2);
    }
    
    public function testGetNodes_Order() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "order" => "`order` DESC",
            ));
        $antID = 0;
        $antorder = 10;
        foreach($resp as $id => $node) {
            $this->assertTrue($antID < $id);
            $this->assertTrue($antorder > $node["order"]);
            $antID = $id;
            $antorder = $node["order"];
        }
    }
    
    public function testGetNodes_Group() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "group" => "`modified`",
            ));
        $this->assertTrue(count($resp) == 1);
    }
    
    public function testGetNodes_Count_Group() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "count" => true,
            "group" => "`modified`",
            ));
        $this->assertTrue($resp[0]["ammount"] == 4);
    }
    
    public function testGetNodes_Count_Group2() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "count" => true,
            "where" => "mod(`order`,2) = 0",
            ));
        $this->assertTrue($resp[0]["ammount"] == 2);
    }
    
    public function testGetNodes_Offset() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "offset" => 3,
            ));
        $this->assertTrue(count($resp) == 1);
    }
    
    public function testGetNodes_Lenght() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "lenght" => 1,
            ));
        $this->assertTrue(count($resp) == 1);
    }
    
    public function testGetNodes_Offset_Lenght() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "offset" => 1,
            "lenght" => 3,
            ));
        $this->assertTrue(count($resp) == 3);
    }
}