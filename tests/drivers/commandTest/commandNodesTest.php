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
        while (!is_file("config/config.php")) {
            chdir("../");
        }
        include_once 'config/config.php';
        include_once("libs/adodb/cmsapi.php");
        include_once("drivers/tools.php");
        include_once("drivers/command.php");
    }
    
    protected function tearDown() {
        
    }
    
    public function cleanDatabase($id) {
        // Clean database
        $sql = "delete FROM `node_type_field` where `node_type` = $id";
        dbConn::get()->Execute($sql);
        $sql = "delete FROM `node_type` where `id` = $id";
        dbConn::get()->Execute($sql);
        $sql = "DROP TABLE IF EXISTS `node_testtype`";
        dbConn::get()->Execute($sql);
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
}