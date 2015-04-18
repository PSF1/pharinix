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
        include_once 'tests/drivers/etc/bootstrap.php';
        driverUser::sessionStart();
        driverUser::sudo();
        
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
        
        driverCommand::run("batch", array(
            "starter" => array(),
            "commands" => array(
                array("addNodeType" => "name=test2"),
                array("#clean" => ""),
                array("addNodeField" => "node_type=test2&name=order&type=integer"),
                array("#clean" => ""),
                array("addNodeField" => "node_type=test2&name=relation&type=test&multi=true"),
                array("#clean" => ""),
                array("addNode" => "nodetype=test2&title=NODE%201&order=4&relation=1"),
                array("#clean" => ""),
                array("addNode" => "nodetype=test2&title=NODE%202&order=3&relation=1,2"),
                array("#clean" => ""),
                array("addNode" => "nodetype=test2&title=NODE%203&order=2&relation=1,2,3"),
                array("#clean" => ""),
                array("addNode" => "nodetype=test2&title=NODE%204&order=1&relation=1,2,3,4"),
            ),
        ));
        
        driverCommand::run("batch", array(
            "starter" => array(),
            "commands" => array(
                array("addNodeType" => "name=testkey"),
                array("#clean" => ""),
                array("addNodeField" => "node_type=testkey&iskey=true&name=order&type=integer"),
                array("#clean" => ""),
                array("addNode" => "nodetype=testkey&title=NODE%201&order=4"),
                array("#clean" => ""),
                array("addNode" => "nodetype=testkey&title=NODE%202&order=3"),
                array("#clean" => ""),
                array("addNode" => "nodetype=testkey&title=NODE%203&order=2"),
                array("#clean" => ""),
                array("addNode" => "nodetype=testkey&title=NODE%204&order=1"),
            ),
        ));
    }
    
    protected function tearDown() {
        driverCommand::run("delNodeType", array("name" => "testkey"));
        driverCommand::run("delNodeType", array("name" => "test2"));
        driverCommand::run("delNodeType", array("name" => "test"));
    }
    
    public function cleanDatabase($id, $node = "testtype") {
        driverCommand::run("delNodeType", array("name" => $node));
    } 
    
    public function testDelNodeWithMulti_test2_1() {
        // Delete node with multi
        $resp = driverCommand::run("delNode", array("nodetype" => "test2", "nid" => 1));
        // Deleted?
        $resp = driverCommand::run("getNode", array("nodetype" => "test2", "node" => 1));
        $this->assertArrayNotHasKey(1, $resp);
        // Erased on relation table?
        $sql = "select * from `node_relation_test2_relation_test` where `type1` = 1";
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
    }
    
    public function testGetNodeMulti_test2_1() {
        $resp = driverCommand::run("getNode", array("nodetype" => "test2", "node" => 1));
        $this->assertArrayHasKey(1, $resp);
        $this->assertEquals("4", $resp[1]["order"]);
        $this->assertNotEquals("", $resp[1]["modified"]);
        $this->assertNotEquals("", $resp[1]["created"]);
        $this->assertEquals("NODE 1", $resp[1]["title"]);
        $this->assertArrayHasKey("relation", $resp[1]);
        $this->assertEquals(1, count($resp[1]["relation"]));
        $this->assertEquals("1", $resp[1]["relation"][0]);
    }
    
    public function testGetNodeMulti_test2_2() {
        $resp = driverCommand::run("getNode", array("nodetype" => "test2", "node" => 2));
        $this->assertArrayHasKey(2, $resp);
        $this->assertEquals("3", $resp[2]["order"]);
        $this->assertNotEquals("", $resp[2]["modified"]);
        $this->assertNotEquals("", $resp[2]["created"]);
        $this->assertEquals("NODE 2", $resp[2]["title"]);
        $this->assertArrayHasKey("relation", $resp[2]);
        $this->assertEquals(2 ,count($resp[2]["relation"]));
        $this->assertEquals("1", $resp[2]["relation"][0]);
        $this->assertEquals("2", $resp[2]["relation"][1]);
    }
    
    public function testGetNodeMulti_test2_3() {
        $resp = driverCommand::run("getNode", array("nodetype" => "test2", "node" => 3));
        $this->assertArrayHasKey(3, $resp);
        $this->assertEquals("2", $resp[3]["order"]);
        $this->assertNotEquals("", $resp[3]["modified"]);
        $this->assertNotEquals("", $resp[3]["created"]);
        $this->assertEquals("NODE 3", $resp[3]["title"]);
        $this->assertArrayHasKey("relation", $resp[3]);
        $this->assertEquals(3, count($resp[3]["relation"]));
        $this->assertEquals("1", $resp[3]["relation"][0]);
        $this->assertEquals("2", $resp[3]["relation"][1]);
        $this->assertEquals("3", $resp[3]["relation"][2]);
    }
    
    public function testGetNodeMulti_test2_4() {
        $resp = driverCommand::run("getNode", array("nodetype" => "test2", "node" => 4));
        $this->assertArrayHasKey(4, $resp);
        $this->assertEquals("1", $resp[4]["order"]);
        $this->assertNotEquals("", $resp[4]["modified"]);
        $this->assertNotEquals("", $resp[4]["created"]);
        $this->assertEquals("NODE 4", $resp[4]["title"]);
        $this->assertArrayHasKey("relation", $resp[4]);
        $this->assertEquals(4, count($resp[4]["relation"]));
        $this->assertEquals("1", $resp[4]["relation"][0]);
        $this->assertEquals("2", $resp[4]["relation"][1]);
        $this->assertEquals("3", $resp[4]["relation"][2]);
        $this->assertEquals("4", $resp[4]["relation"][3]);
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
        $this->assertEquals(4 ,count($resp));
    }
    
    public function testGetNodes_Fields_With_ID() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "fields" => "id, order, title",
            ));
        $this->assertEquals(4, count($resp));
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
        $this->assertEquals(4 ,count($resp));
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
        $this->assertEquals(1, count($resp));
        $this->assertEquals("NODE 1", $resp[1]["title"]);
        $this->assertEquals(4, $resp[1]["order"]);
    }
    
    public function testGetNodes_Where_2() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "where" => "`order` = 4 || `title` like '%2'",
            ));
        $this->assertEquals(2, count($resp));
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
        $this->assertEquals(1, count($resp));
    }
    
    public function testGetNodes_Count_Group() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "count" => true,
            "group" => "`modified`",
            ));
        $this->assertEquals(4, $resp[0]["ammount"]);
    }
    
    public function testGetNodes_Count_Group2() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "count" => true,
            "where" => "mod(`order`,2) = 0",
            ));
        $this->assertEquals(2, $resp[0]["ammount"]);
    }
    
    public function testGetNodes_Offset() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "offset" => 3,
            ));
        $this->assertEquals(1, count($resp));
    }
    
    public function testGetNodes_Lenght() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "lenght" => 1,
            ));
        $this->assertEquals(1, count($resp));
    }
    
    public function testGetNodes_Offset_Lenght() {
        $resp = driverCommand::run("getNodes", array(
            "nodetype" => "test",
            "offset" => 1,
            "lenght" => 3,
            ));
        $this->assertEquals(3, count($resp));
    }
    
    // Updates
    public function testUpdateNodes_BadType() {
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "notype",
            "nid" => 1
            ));
        $this->assertFalse($resp["ok"]);
    }
    
    public function testUpdateNodes_Defaultype() {
        $resp = driverCommand::run("updateNode", array(
            "nid" => 1
            ));
        $this->assertFalse($resp["ok"]);
    }
    
    public function testUpdateNodes_BadID() {
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "nid" => 10000
            ));
        $this->assertFalse($resp["ok"]);
    }
    
    public function testUpdateNodes_DefaultID() {
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            ));
        $this->assertFalse($resp["ok"]);
    }
    
    // updateNode allow not update required fields.
//    public function testUpdateNodes_Required_fail() {
//        $resp = driverCommand::run("updateNode", array(
//            "nodetype" => "test",
//            "nid" => 1,
//            ));
//        $this->assertFalse($resp["ok"]);
//    }
    
    public function testUpdateNodes_ok() {
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "test",
            "nid" => 1,
            "title" => "test",
            ));
        $this->assertTrue($resp["ok"]);
        $resp = driverCommand::run("getNode", array(
            "nodetype" => "test",
            "node" => 1,
        ));
        $this->assertEquals("test", $resp[1]["title"]);
    }
    
    // updateNode allow not update key fields.
//    public function testUpdateNodes_key_fail() {
//        $resp = driverCommand::run("updateNode", array(
//            "nodetype" => "testkey",
//            "nid" => 1,
//            "title" => "test",
//            ));
//        $this->assertFalse($resp["ok"]);
//    }
    
    public function testUpdateNodes_duplicate_key_fail() {
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "testkey",
            "nid" => 1,
            "title" => "test",
            "order" => 1,
            ));
        $this->assertFalse($resp["ok"]);
    }
    
    public function testUpdateNodes_key_ok() {
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "testkey",
            "nid" => 1,
            "title" => "test",
            "order" => 5,
            ));
        $this->assertTrue($resp["ok"]);
        $resp = driverCommand::run("getNode", array(
            "nodetype" => "testkey",
            "node" => 1,
        ));
        $this->assertEquals("test", $resp[1]["title"]);
        $this->assertEquals(5, $resp[1]["order"]);
    }
    
    public function testUpdateNodes_DuplicateKey_fail() {
        $resp = driverCommand::run("updateNode", array(
            "nodetype" => "testkey",
            "nid" => 1,
            "title" => "test",
            "order" => 2,
            ));
        $this->assertFalse($resp["ok"]);
    }
}