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

class hookTest extends PHPUnit_Framework_TestCase {
    public static $data;
    public static $permanent = 'tests/drivers/etc/hookHandlers.inc';
    
    public static function setUpBeforeClass() {
//        error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
        //        include_once 'commandTools.php';
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
        include_once 'tests/drivers/etc/commandTools.php';
        driverHook::setPermanentFile(self::$permanent);
        new driverHook();
    }
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        driverHook::reset();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public static function tearDownAfterClass() {
        @unlink(self::$permanent);
    }

    public function dummyHook($params) {
        self::$data = $params;
    }
    
    public function dummyBeforeManHook($params) {
        $params['parameters']['cmd'] = 'man';
    }
    
    public function dummyAfterManHook($params) {
        $params['response']['dummy'] = true;
    }
    
    public function dummyBeforeNothingHookFail($params) {
        throw new Exception('Test message');
    }
    
    public function dummyAfterNothingHookFail($params) {
        trigger_error("Dummy fatal error", E_ERROR);
    }

    public function testBeforeHook() {
        // We can register a hook handler
        self::$data = null;
        driverCommand::RegisterHook('beforenothingHook', 'test', 'hookTest::dummyHook');
        driverCommand::run('nothing', array('a' => 1, 'b' => 2));
        
        $this->assertEquals(1, self::$data['parameters']['a']);
        $this->assertEquals(2, self::$data['parameters']['b']);
        
        // We can unregister it too
        self::$data = null;
        driverCommand::UnregisterHook('beforenothingHook', 'test');
        driverCommand::run('nothing', array('a' => 1, 'b' => 2));
        $this->assertNull(self::$data);
    }
    
    public function testAfterHook() {
        driverUser::sudo();
        // We can register a hook handler
        self::$data = null;
        driverCommand::RegisterHook('afterhooksGetRegisteredHook', 'test', 'hookTest::dummyHook');
        $resp = driverCommand::run('hooksGetRegistered');
        
        $this->assertEquals(1, count(self::$data['response']));
        
        // We can unregister it too
        self::$data = null;
        driverCommand::UnregisterHook('afterhooksGetRegisteredHook', 'test');
        driverCommand::run('hooksGetRegistered');
        $this->assertNull(self::$data);
        
        driverUser::sudo(false);
    }
    
    public function testAlterManCommandHook() {
        driverCommand::RegisterHook('beforemanHook', 'test', 'hookTest::dummyBeforeManHook');
        driverCommand::RegisterHook('aftermanHook', 'test', 'hookTest::dummyAfterManHook');
        $resp = driverCommand::run('man', array('cmd' => 'nothing'));
        
        $this->assertTrue(isset($resp['help']['man']));
        $this->assertTrue($resp['dummy']);
        
        driverCommand::UnregisterHook('beforemanHook', 'test');
        driverCommand::UnregisterHook('aftermanHook', 'test');
    }
    
    public function testPermanentHook() {
        driverHook::saveHandler('beforemanHook', 'tests/drivers/commandTest/driverHookTest.php', 'hookTest::dummyBeforeManHook');
        driverHook::saveHandler('aftermanHook', 'tests/drivers/commandTest/driverHookTest.php', 'hookTest::dummyAfterManHook');
        
        $resp = driverCommand::run('man', array('cmd' => 'nothing'));
        
        $this->assertTrue(isset($resp['help']['man']));
        $this->assertTrue($resp['dummy']);
        
        driverHook::removeHandler('beforemanHook', 'tests/drivers/commandTest/driverHookTest.php', 'hookTest::dummyBeforeManHook');
        driverHook::removeHandler('aftermanHook', 'tests/drivers/commandTest/driverHookTest.php', 'hookTest::dummyAfterManHook');
    }
    
    public function testAutoPermanentHook() {
        new driverHook('tests/drivers/etc/hookHandlersTest.inc');
        
        $resp = driverCommand::run('man', array('cmd' => 'nothing'));
        
        $this->assertTrue(isset($resp['help']['man']));
        $this->assertTrue($resp['dummy']);
    }
    
    public function testAutoPermanentHookFailMethod() {
        new driverHook('tests/drivers/etc/hookHandlersTest.inc');
        
        $this->assertTrue(driverHook::HasHookHandler('beforenothingHook'));
        $resp = driverCommand::run('nothing');
        $this->assertNotTrue(driverHook::HasHookHandler('beforenothingHook'));
    }
    
//    public function testAutoPermanentHookFatalErrorMethod() {
//        new driverHook('tests/drivers/etc/hookHandlersTest.inc');
//        
//        $this->assertTrue(driverHook::HasHookHandler('afternothingHook'));
//        $resp = driverCommand::run('nothing');
//        $this->assertNotTrue(driverHook::HasHookHandler('afternothingHook'));
//    }
}
