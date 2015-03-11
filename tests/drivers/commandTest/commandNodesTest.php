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

class commandNodesTest extends PHPUnit_Framework_TestCase {
    
    protected function setUp() {
//        include_once 'commandTools.php';
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'etc/pharinix.config.php';
        include_once("usr/adodb/cmsapi.php");
        include_once("etc/drivers/tools.php");
        include_once("etc/drivers/command.php");
    }
    
    protected function tearDown() {
        
    }
    
    public function cleanDatabase($id, $node = "testtype") {
        driverCommand::run("delNodeType", array("name" => $node));
//        $node = "node_".$node;
//        // Clean database
//        $sql = "delete FROM `node_type_field` where `node_type` = $id";
//        dbConn::get()->Execute($sql);
//        $sql = "delete FROM `node_type` where `id` = $id";
//        dbConn::get()->Execute($sql);
//        $sql = "DROP TABLE IF EXISTS `$node`";
//        dbConn::get()->Execute($sql);
    } 
    
    public function testCommandAddType() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        // It must add type info in table
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $id = $q->fields["id"];
        // It must create a table
        $sql = "show tables like 'node_testtype'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        // It must add 4 system fields
        $sql = "SELECT count(*) FROM `node_type_field` where `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(4, $q->fields[0]);
        // The table must have 4 fields plus ID field
        $sql = "show columns from `node_testtype`";
        $q = dbConn::get()->Execute($sql);
        $fields = array(
            "id" => false,
            "modifier" => false,
            "modified" => false,
            "creator" => false,
            "created" => false,
        );
        while (!$q->EOF) {
            // Is a expected field?
            $this->assertArrayHasKey($q->fields["Field"], $fields);
            $fields[$q->fields["Field"]] = true;
            $q->MoveNext();
        }
        // Contains the expected fields?
        foreach ($fields as $value) {
            $this->assertEquals(true, $value);
        }
        $this->cleanDatabase($id);
    }
    
    public function testCommandAdd2Type() {
        $nid = driverCommand::run("addNodeType", array("name" => "testtype"));
        $nid = $nid["nid"];
        $nid1 = driverCommand::run("addNodeType", array("name" => "testtype2"));
        $nid1 = $nid1["nid"];
        // It must add type info in table
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype2'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        // It must create a table
        $sql = "show tables like 'node_testtype'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $sql = "show tables like 'node_testtype2'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        // It must add 4 system fields
        $sql = "SELECT count(*) FROM `node_type_field` where `node_type` = ".$nid;
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(4, $q->fields[0]);
        $sql = "SELECT count(*) FROM `node_type_field` where `node_type` = ".$nid1;
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(4, $q->fields[0]);
        // The table must have 4 fields plus ID field
        $sql = "show columns from `node_testtype`";
        $q = dbConn::get()->Execute($sql);
        $fields = array(
            "id" => false,
            "modifier" => false,
            "modified" => false,
            "creator" => false,
            "created" => false,
        );
        while (!$q->EOF) {
            // Is a expected field?
            $this->assertArrayHasKey($q->fields["Field"], $fields);
            $fields[$q->fields["Field"]] = true;
            $q->MoveNext();
        }
        // Contains the expected fields?
        foreach ($fields as $value) {
            $this->assertEquals(true, $value);
        }
        // The table must have 4 fields plus ID field
        $sql = "show columns from `node_testtype2`";
        $q = dbConn::get()->Execute($sql);
        $fields = array(
            "id" => false,
            "modifier" => false,
            "modified" => false,
            "creator" => false,
            "created" => false,
        );
        while (!$q->EOF) {
            // Is a expected field?
            $this->assertArrayHasKey($q->fields["Field"], $fields);
            $fields[$q->fields["Field"]] = true;
            $q->MoveNext();
        }
        // Contains the expected fields?
        foreach ($fields as $value) {
            $this->assertEquals(true, $value);
        }
        $this->cleanDatabase($nid);
        $this->cleanDatabase($nid1, "testtype2");
    }
    
    public function testCommandAddTypeProtectedName() {
        $resp = driverCommand::run("addNodeType", array("name" => "type"));
        $this->assertNotEquals("", $resp["msg"]);
        $resp = driverCommand::run("addNodeType", array("name" => "type_field"));
        $this->assertNotEquals("", $resp["msg"]);
    }
    
    public function testCommandAddFieldLongtext() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "longtext",
            "type" => "longtext",
            "len" => 0,
            "required" => false,
            "readonly" => false,
            "node_type" => "testtype",
            "default" => "default",
            "label" => "label",
            "help" => "help",
        );
        driverCommand::run("addNodeField", $nField);
        // New field?
        $sql = "SELECT count(*) FROM `node_type_field` where `name` = 'longtext' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(1, $q->fields[0]);
        // The table must have a new field
        $sql = "show columns from `node_testtype` like 'longtext'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $this->assertEquals("longtext", $q->fields["Type"]);
        $this->assertEquals("YES", $q->fields["Null"]);
        $this->assertEquals("", $q->fields["Key"]);
        $this->assertEquals("", $q->fields["Default"]);
        $this->assertEquals("", $q->fields["Extra"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldLongtextDefaults() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "longtext",
            "type" => "longtext",
            "node_type" => "testtype",
        );
        driverCommand::run("addNodeField", $nField);
        
        $sql = "SELECT * FROM `node_type_field` where `name` = 'longtext' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals("longtext", $q->fields["name"]);
        $this->assertEquals("longtext", $q->fields["type"]);
        $this->assertEquals($id, $q->fields["node_type"]);
        // Defaults?
        $this->assertEquals(0, $q->fields["len"]);
        $this->assertEquals("0", $q->fields["required"]);
        $this->assertEquals("0", $q->fields["readonly"]);
        $this->assertEquals("0", $q->fields["locked"]);
        $this->assertEquals("", $q->fields["default"]);
        $this->assertEquals("Field", $q->fields["label"]);
        $this->assertEquals("", $q->fields["help"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldBool() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "bool",
            "type" => "bool",
            "len" => 0,
            "required" => false,
            "readonly" => false,
            "node_type" => "testtype",
            "default" => "0",
            "label" => "label",
            "help" => "help",
        );
        driverCommand::run("addNodeField", $nField);
        // New field?
        $sql = "SELECT count(*) FROM `node_type_field` where `name` = 'bool' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(1, $q->fields[0]);
        // The table must have a new field
        $sql = "show columns from `node_testtype` like 'bool'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $this->assertEquals("varchar(1)", $q->fields["Type"]);
        $this->assertEquals("YES", $q->fields["Null"]);
        $this->assertEquals("", $q->fields["Key"]);
        $this->assertEquals("0", $q->fields["Default"]);
        $this->assertEquals("", $q->fields["Extra"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldBoolDefaults() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "bool",
            "type" => "bool",
            "node_type" => "testtype",
        );
        driverCommand::run("addNodeField", $nField);
        
        $sql = "SELECT * FROM `node_type_field` where `name` = 'bool' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals("bool", $q->fields["name"]);
        $this->assertEquals("bool", $q->fields["type"]);
        $this->assertEquals($id, $q->fields["node_type"]);
        // Defaults?
        $this->assertEquals(0, $q->fields["len"]);
        $this->assertEquals("0", $q->fields["required"]);
        $this->assertEquals("0", $q->fields["readonly"]);
        $this->assertEquals("0", $q->fields["locked"]);
        $this->assertEquals("0", $q->fields["default"]);
        $this->assertEquals("Field", $q->fields["label"]);
        $this->assertEquals("", $q->fields["help"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldDatetime() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "datetime",
            "type" => "datetime",
            "len" => 0,
            "required" => false,
            "readonly" => false,
            "node_type" => "testtype",
            "default" => "2015-02-24 22:47:00",
            "label" => "label",
            "help" => "help",
        );
        driverCommand::run("addNodeField", $nField);
        // New field?
        $sql = "SELECT count(*) FROM `node_type_field` where `name` = 'datetime' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(1, $q->fields[0]);
        // The table must have a new field
        $sql = "show columns from `node_testtype` like 'datetime'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $this->assertEquals("datetime", $q->fields["Type"]);
        $this->assertEquals("YES", $q->fields["Null"]);
        $this->assertEquals("", $q->fields["Key"]);
        $this->assertEquals("2015-02-24 22:47:00", $q->fields["Default"]);
        $this->assertEquals("", $q->fields["Extra"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldDatetimeDefaults() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "datetime",
            "type" => "datetime",
            "node_type" => "testtype",
        );
        driverCommand::run("addNodeField", $nField);
        
        $sql = "SELECT * FROM `node_type_field` where `name` = 'datetime' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals("datetime", $q->fields["name"]);
        $this->assertEquals("datetime", $q->fields["type"]);
        $this->assertEquals($id, $q->fields["node_type"]);
        // Defaults?
        $this->assertEquals(0, $q->fields["len"]);
        $this->assertEquals("0", $q->fields["required"]);
        $this->assertEquals("0", $q->fields["readonly"]);
        $this->assertEquals("0", $q->fields["locked"]);
        $this->assertEquals("", $q->fields["default"]);
        $this->assertEquals("Field", $q->fields["label"]);
        $this->assertEquals("", $q->fields["help"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldDouble() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "double",
            "type" => "double",
            "len" => 0,
            "required" => false,
            "readonly" => false,
            "node_type" => "testtype",
            "default" => "1",
            "label" => "label",
            "help" => "help",
        );
        driverCommand::run("addNodeField", $nField);
        // New field?
        $sql = "SELECT count(*) FROM `node_type_field` where `name` = 'double' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(1, $q->fields[0]);
        // The table must have a new field
        $sql = "show columns from `node_testtype` like 'double'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $this->assertEquals("decimal(20,6)", $q->fields["Type"]);
        $this->assertEquals("YES", $q->fields["Null"]);
        $this->assertEquals("", $q->fields["Key"]);
        $this->assertEquals("1", $q->fields["Default"]);
        $this->assertEquals("", $q->fields["Extra"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldDoubleDefaults() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "double",
            "type" => "double",
            "node_type" => "testtype",
        );
        driverCommand::run("addNodeField", $nField);
        
        $sql = "SELECT * FROM `node_type_field` where `name` = 'double' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals("double", $q->fields["name"]);
        $this->assertEquals("double", $q->fields["type"]);
        $this->assertEquals($id, $q->fields["node_type"]);
        // Defaults?
        $this->assertEquals(0, $q->fields["len"]);
        $this->assertEquals("0", $q->fields["required"]);
        $this->assertEquals("0", $q->fields["readonly"]);
        $this->assertEquals("0", $q->fields["locked"]);
        $this->assertEquals("", $q->fields["default"]);
        $this->assertEquals("Field", $q->fields["label"]);
        $this->assertEquals("", $q->fields["help"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldInteger() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "integer",
            "type" => "integer",
            "len" => 0,
            "required" => false,
            "readonly" => false,
            "node_type" => "testtype",
            "default" => "1",
            "label" => "label",
            "help" => "help",
        );
        driverCommand::run("addNodeField", $nField);
        // New field?
        $sql = "SELECT count(*) FROM `node_type_field` where `name` = 'integer' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(1, $q->fields[0]);
        // The table must have a new field
        $sql = "show columns from `node_testtype` like 'integer'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $this->assertEquals("int(11)", $q->fields["Type"]);
        $this->assertEquals("YES", $q->fields["Null"]);
        $this->assertEquals("", $q->fields["Key"]);
        $this->assertEquals("1", $q->fields["Default"]);
        $this->assertEquals("", $q->fields["Extra"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldIntegerDefaults() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "integer",
            "type" => "integer",
            "node_type" => "testtype",
        );
        driverCommand::run("addNodeField", $nField);
        
        $sql = "SELECT * FROM `node_type_field` where `name` = 'integer' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals("integer", $q->fields["name"]);
        $this->assertEquals("integer", $q->fields["type"]);
        $this->assertEquals($id, $q->fields["node_type"]);
        // Defaults?
        $this->assertEquals(0, $q->fields["len"]);
        $this->assertEquals("0", $q->fields["required"]);
        $this->assertEquals("0", $q->fields["readonly"]);
        $this->assertEquals("0", $q->fields["locked"]);
        $this->assertEquals("", $q->fields["default"]);
        $this->assertEquals("Field", $q->fields["label"]);
        $this->assertEquals("", $q->fields["help"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldString() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "string",
            "type" => "string",
            "len" => 10,
            "required" => false,
            "readonly" => false,
            "node_type" => "testtype",
            "default" => "A",
            "label" => "label",
            "help" => "help",
        );
        driverCommand::run("addNodeField", $nField);
        // New field?
        $sql = "SELECT count(*) FROM `node_type_field` where `name` = 'string' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(1, $q->fields[0]);
        // The table must have a new field
        $sql = "show columns from `node_testtype` like 'string'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $this->assertEquals("varchar(10)", $q->fields["Type"]);
        $this->assertEquals("YES", $q->fields["Null"]);
        $this->assertEquals("", $q->fields["Key"]);
        $this->assertEquals("A", $q->fields["Default"]);
        $this->assertEquals("", $q->fields["Extra"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldStringDefaults() {
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "string",
            "type" => "string",
            "node_type" => "testtype",
        );
        driverCommand::run("addNodeField", $nField);
        
        $sql = "SELECT * FROM `node_type_field` where `name` = 'string' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals("string", $q->fields["name"]);
        $this->assertEquals("string", $q->fields["type"]);
        $this->assertEquals($id, $q->fields["node_type"]);
        // Defaults?
        $this->assertEquals(250, $q->fields["len"]);
        $this->assertEquals("0", $q->fields["required"]);
        $this->assertEquals("0", $q->fields["readonly"]);
        $this->assertEquals("0", $q->fields["locked"]);
        $this->assertEquals("", $q->fields["default"]);
        $this->assertEquals("Field", $q->fields["label"]);
        $this->assertEquals("", $q->fields["help"]);
        $this->cleanDatabase($id);
    }
    
    public function testCommandAddFieldNodeType() {
        $nid = driverCommand::run("addNodeType", array("name" => "subtype"));
        $nid = $nid["nid"];
        driverCommand::run("addNodeType", array("name" => "subtype"));
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "subtype",
            "type" => "subtype",
            "len" => 0,
            "required" => false,
            "readonly" => false,
            "node_type" => "testtype",
            "default" => "1",
            "label" => "label",
            "help" => "help",
        );
        driverCommand::run("addNodeField", $nField);
        // New field?
        $sql = "SELECT count(*) FROM `node_type_field` where `name` = 'subtype' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(1, $q->fields[0]);
        // The table must have a new field
        $sql = "show columns from `node_testtype` like 'subtype'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        $this->assertEquals("int(10) unsigned", $q->fields["Type"]);
        $this->assertEquals("YES", $q->fields["Null"]);
        $this->assertEquals("", $q->fields["Key"]);
        $this->assertEquals("0", $q->fields["Default"]);
        $this->assertEquals("", $q->fields["Extra"]);
        $this->cleanDatabase($id);
        $this->cleanDatabase($nid, "subtype");
    }
    
    public function testCommandAddFieldNodeTypeDefaults() {
        $nid = driverCommand::run("addNodeType", array("name" => "subtype1"));
        $nid = $nid["nid"];
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "subtype1",
            "type" => "subtype1",
            "node_type" => "testtype",
        );
        driverCommand::run("addNodeField", $nField);
        
        $sql = "SELECT * FROM `node_type_field` where `name` = 'subtype1' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals("subtype1", $q->fields["name"]);
        $this->assertEquals("subtype1", $q->fields["type"]);
        $this->assertEquals($id, $q->fields["node_type"]);
        // Defaults?
        $this->assertEquals(0, $q->fields["len"]);
        $this->assertEquals("0", $q->fields["required"]);
        $this->assertEquals("0", $q->fields["readonly"]);
        $this->assertEquals("0", $q->fields["locked"]);
        $this->assertEquals("0", $q->fields["default"]);
        $this->assertEquals("Field", $q->fields["label"]);
        $this->assertEquals("", $q->fields["help"]);
        $this->cleanDatabase($id);
        $this->cleanDatabase($nid, "subtype1");
    }
    
    public function testCommandAddFieldNodeTypeMulti() {
        $nid = driverCommand::run("addNodeType", array("name" => "subtype1"));
        $nid = $nid["nid"];
        driverCommand::run("addNodeType", array("name" => "testtype"));
        $sql = "SELECT * FROM `node_type` where `name` = 'testtype'";
        $q = dbConn::get()->Execute($sql);
        $id = $q->fields["id"];
        $nField = array(
            "name" => "subtype1",
            "type" => "subtype1",
            "multi" => true,
            "node_type" => "testtype",
        );
        driverCommand::run("addNodeField", $nField);
        
        $sql = "SELECT * FROM `node_type_field` where `name` = 'subtype1' && `node_type` = $id";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals("subtype1", $q->fields["name"]);
        $this->assertEquals("subtype1", $q->fields["type"]);
        $this->assertEquals($id, $q->fields["node_type"]);
        // Relation table created?
        $sql = "show tables like 'node_relation_testtype%'";
        $q = dbConn::get()->Execute($sql);
        $this->assertEquals(false, $q->EOF);
        // Clean data base
        $this->cleanDatabase($id);
        $this->cleanDatabase($nid, "subtype1");
    }
}