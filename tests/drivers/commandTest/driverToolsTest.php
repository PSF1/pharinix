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

class driverToolsTest extends PHPUnit_Framework_TestCase {
    
    public static function setUpBeforeClass() {
//        error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
        while (!is_file("etc/pharinix.config.php")) {
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
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public static function tearDownAfterClass() {
        
    }
    
    public function test_Str_Start() {
        $this->assertTrue(driverTools::str_start("", "abc"), 'str_start("", "abc")');
        $this->assertTrue(driverTools::str_start("a", "abc"), 'str_start("a", "abc")');
        $this->assertFalse(driverTools::str_start("a", "ABC"), 'str_start("a", "ABC")');
        $this->assertFalse(driverTools::str_start("a", "hi"), 'str_start("a", "hi")');
        $this->assertTrue(driverTools::str_start("ab", "ab"), 'str_start("ab", "ab")');
        $this->assertFalse(driverTools::str_start("ab", "a"), 'str_start("ab", "a")');
        $this->assertTrue(driverTools::str_start("ab", "abc"), 'str_start("ab", "abc")');
        $this->assertFalse(driverTools::str_start("abc", "ab"), 'str_start("abc", "ab")');
    }
    
    public function test_Str_End() {
        $this->assertTrue(driverTools::str_end("", "abc"), 'str_end("", "abc")');
        $this->assertTrue(driverTools::str_end("c", "abc"), 'str_end("c", "abc")');
        $this->assertFalse(driverTools::str_end("c", "ABC"), 'str_end("c", "ABC")');
        $this->assertFalse(driverTools::str_end("a", "hi"), 'str_end("a", "hi")');
        $this->assertTrue(driverTools::str_end("ab", "ab"), 'str_end("ab", "ab")');
        $this->assertFalse(driverTools::str_end("ab", "a"), 'str_end("ab", "a")');
        $this->assertTrue(driverTools::str_end("bc", "abc"), 'str_end("bc", "abc")');
        $this->assertFalse(driverTools::str_end("abc", "ab"), 'str_end("abc", "ab")');
    }
    
    public function test_pathInfo_OnlyExtension() {
        $fInfo = driverTools::pathInfo(".htaccess");
        $this->assertTrue($fInfo["exists"]);
        $this->assertTrue($fInfo["writable"]);
//        $this->assertEquals("0666", $fInfo["chmod"]);
        $this->assertEquals("htaccess", $fInfo["ext"]);
        $this->assertEquals(getcwd(), $fInfo["path"]);
        $this->assertEquals("", $fInfo["name"]);
        $this->assertEquals(".htaccess", $fInfo["filename"]);
    }
    
    public function test_pathInfo_OnlyName() {
        $fInfo = driverTools::pathInfo("LICENSE");
        $this->assertTrue($fInfo["exists"]);
        $this->assertTrue($fInfo["writable"]);
//        $this->assertEquals("0666", $fInfo["chmod"]);
        $this->assertEquals("", $fInfo["ext"]);
        $this->assertEquals(getcwd(), $fInfo["path"]);
        $this->assertEquals("LICENSE", $fInfo["name"]);
        $this->assertEquals("LICENSE", $fInfo["filename"]);
    }
    
    public function test_pathInfo_NameAndExtension() {
        $fInfo = driverTools::pathInfo("index.php");
        $this->assertTrue($fInfo["exists"]);
        $this->assertTrue($fInfo["writable"]);
//        $this->assertEquals("0666", $fInfo["chmod"]);
        $this->assertEquals("php", $fInfo["ext"]);
        $this->assertEquals(getcwd(), $fInfo["path"]);
        $this->assertEquals("index", $fInfo["name"]);
        $this->assertEquals("index.php", $fInfo["filename"]);
    }
    
    public function test_pathInfo_NameAnd2Extension() {
        $fInfo = driverTools::pathInfo("index.bad.php");
        $this->assertFalse($fInfo["exists"]);
        $this->assertFalse($fInfo["writable"]);
//        $this->assertFalse($fInfo["chmod"]);
        $this->assertEquals("php", $fInfo["ext"]);
        $this->assertEquals("", $fInfo["path"]);
        $this->assertEquals("index.bad", $fInfo["name"]);
        $this->assertEquals("index.bad.php", $fInfo["filename"]);
    }
    
    public function test_pathInfo_AbsolutePath() {
        $fInfo = driverTools::pathInfo(getcwd()."/index.php");
        $this->assertTrue($fInfo["exists"]);
        $this->assertTrue($fInfo["writable"]);
//        $this->assertEquals("0666", $fInfo["chmod"]);
        $this->assertEquals("php", $fInfo["ext"]);
        $this->assertEquals(getcwd(), $fInfo["path"]);
        $this->assertEquals("index", $fInfo["name"]);
        $this->assertEquals("index.php", $fInfo["filename"]);
    }
    
    public function test_pathInfo_RelativePath() {
        $fInfo = driverTools::pathInfo("bin/nothing.php");
        $this->assertTrue($fInfo["exists"]);
        $this->assertTrue($fInfo["writable"]);
//        $this->assertEquals("0666", $fInfo["chmod"]);
        $this->assertEquals("php", $fInfo["ext"]);
        $this->assertEquals(getcwd().DIRECTORY_SEPARATOR."bin", $fInfo["path"]);
        $this->assertEquals("nothing", $fInfo["name"]);
        $this->assertEquals("nothing.php", $fInfo["filename"]);
    }
    
    public function test_pathInfo_EmptyFile() {
        $fInfo = driverTools::pathInfo("");
        $this->assertFalse($fInfo["exists"]);
        $this->assertFalse($fInfo["writable"]);
        $this->assertFalse($fInfo["chmod"]);
        $this->assertEquals("", $fInfo["ext"]);
        $this->assertEquals("", $fInfo["path"]);
        $this->assertEquals("", $fInfo["name"]);
        $this->assertEquals("", $fInfo["filename"]);
        $this->assertFalse($fInfo["filename"]);
    }
    
    public function test_pathInfo_BadString() {
        $fInfo = driverTools::pathInfo("/\/.path///file/.gif");
        $this->assertFalse($fInfo["exists"]);
        $this->assertFalse($fInfo["writable"]);
        $this->assertFalse($fInfo["chmod"]);
        $this->assertEquals("gif", $fInfo["ext"]);
        $this->assertEquals("///.path///file/", $fInfo["path"]);
        $this->assertEquals("", $fInfo["name"]);
        $this->assertEquals(".gif", $fInfo["filename"]);
    }
    
    public function test_pathInfo_OnlyPath() {
        $fInfo = driverTools::pathInfo(getcwd().DIRECTORY_SEPARATOR."bin");
        $this->assertTrue($fInfo["exists"]);
        $this->assertFalse($fInfo["isfile"]);
        $this->assertTrue($fInfo["isdir"]);
        $this->assertTrue($fInfo["writable"]);
//        $this->assertEquals("0777", $fInfo["chmod"]);
        $this->assertEquals("", $fInfo["ext"]);
        $this->assertEquals(getcwd(), $fInfo["path"]);
        $this->assertEquals("", $fInfo["name"]);
        $this->assertEquals("bin", $fInfo["filename"]);
    }
    
    public function test_pathInfo_EmptyStart() {
        $fInfo = driverTools::pathInfo("bin");
        $this->assertTrue($fInfo["exists"]);
        $this->assertFalse($fInfo["isfile"]);
        $this->assertTrue($fInfo["isdir"]);
        $this->assertTrue($fInfo["writable"]);
//        $this->assertEquals("0777", $fInfo["chmod"]);
        $this->assertEquals("", $fInfo["ext"]);
        $this->assertEquals(getcwd(), $fInfo["path"]);
        $this->assertEquals("", $fInfo["name"]);
        $this->assertEquals("bin", $fInfo["filename"]);
    }
    
    public function test_pathInfo_folder_RelativeVSAbsolute() {
        $fInfoRel = driverTools::pathInfo("bin/nothing.php");
        $fInfoAbs = driverTools::pathInfo(getcwd().DIRECTORY_SEPARATOR."bin/nothing.php");
        $this->assertTrue($fInfoRel["exists"]);
        $this->assertTrue($fInfoAbs["exists"]);
        $this->assertEquals($fInfoRel["path"], $fInfoAbs["path"]);
    }
}
