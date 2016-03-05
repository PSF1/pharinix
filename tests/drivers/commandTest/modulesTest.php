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

class modulesTest extends PHPUnit_Framework_TestCase {
    public static $driver;
    
    public static function setUpBeforeClass() {
//        error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
        //        include_once 'commandTools.php';
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

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public static function tearDownAfterClass() {
        
    }
    
    public function test_mod_requires_fail_by_pharinix_version() {
        driverUser::sudo(true);
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_requires_fail_by_pharinix_version.zip',
        ));
        $this->assertFalse($resp['ok']);
        
        driverUser::sudo(false);
    }
    
    public function test_mod_requires_unknowed_module() {
        driverUser::sudo(true);
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_requires_unknowed_module.zip',
        ));
        $this->assertFalse($resp['ok']);
        
        driverUser::sudo(false);
    }
    
    public function testInstallAndUninstall() {
        // Install
        driverUser::sudo(true);
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_neutral.zip',
        ));
        $this->assertTrue($resp['ok']);
        $this->assertTrue(is_dir('usr/neutral_mod/'));
        $this->assertTrue(is_file('usr/neutral_mod/meta.json'));
        $mods = driverCommand::run('getNodes', array(
            'nodetype' => 'modules',
            'where' => "`title` = 'neutral_mod'",
        ));
        $this->assertTrue(count($mods) == 1);
        // Uninstall
        $resp = driverCommand::run('modUninstall', array(
            'name' => 'neutral_mod',
        ));
        $this->assertTrue($resp['ok']);
        $this->assertFalse(is_dir('usr/neutral_mod/'));
        $this->assertFalse(is_file('usr/neutral_mod/meta.json'));
        $mods = driverCommand::run('getNodes', array(
            'nodetype' => 'modules',
            'where' => "`title` = 'neutral_mod'",
        ));
        $this->assertTrue(count($mods) == 0);
        
        driverUser::sudo(false);
    }
    
    public function testInstallWithRequirements_fails() {
        driverUser::sudo(true);
        
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_require_neutral_1.0.zip',
        ));
        $this->assertFalse($resp['ok']);
        
        driverUser::sudo(false);
    }
    
    public function testInstallWithRequirements_ok() {
        driverUser::sudo(true);
        
        driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_neutral.zip',
        ));
        
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_require_neutral_1.0.zip',
        ));
        $this->assertTrue($resp['ok']);
        
        driverCommand::run('modUninstall', array('name' => 'require_neutral_mod'));
        driverCommand::run('modUninstall', array('name' => 'neutral_mod'));
        
        driverUser::sudo(false);
    }
    
    public function testConfigInstall_Uninstall() {
        driverUser::sudo(true);
        
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_conf_example.zip',
        ));
        $this->assertNotNull(driverConfig::getCFG()->getSection('[conf_example_mod]'));
        $this->assertEquals("a value", driverConfig::getCFG()->getSection('[conf_example_mod]')->get('config_key1'));
        $this->assertEquals("other value", driverConfig::getCFG()->getSection('[conf_example_mod]')->get('config_key2'));
        
        driverCommand::run('modUninstall', array('name' => 'conf_example_mod'));
        $this->assertNull(driverConfig::getCFG()->getSection('[conf_example_mod]'));
        
        driverUser::sudo(false);
    }
    
    public function testBootingInstall_Uninstall() {
        driverUser::sudo(true);
        
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_booting.zip',
        ));
        // Try
        $mods = driverCommand::run('getNodes', array(
                'nodetype' => 'modules',
                'where' => "`title` = 'booting_mod'"
            ));
        $ids = array_keys($mods);
        $mod = $mods[$ids[0]];
        $jsonMeta = $mod['meta'];
        $meta = json_decode($jsonMeta);
        $ids = 0;
        foreach($meta->booting as $bootObj) {
                foreach($bootObj as $key => $value) {
                    switch ($key) {
                        case 'id':
                            ++$ids;
                            break;
                    }
                }
            }
        $this->assertEquals(2, $ids);
        
        driverCommand::run('modUninstall', array('name' => 'booting_mod'));
        
        driverUser::sudo(false);
    }
    
    public function testCommand_path() {
        driverUser::sudo(true);
        
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_command_path.zip',
        ));
        // Try
        $resp = driverCommand::run('cmd_template');
        $this->assertEquals('cmd_template', $resp);
        // Uninstall
        driverCommand::run('modUninstall', array('name' => 'command_path_mod'));
        $resp = driverCommand::run('cmd_template');
        $this->assertFalse($resp['ok']);
        
        driverUser::sudo(false);
    }
    
    public function testNodes() {
        driverUser::sudo(true);
        
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_nodes_example.zip',
        ));
        // Try
        $resp = driverCommand::run('getNodeTypeList');
        $findExaNode = false;
        $findExaNode2 = false;
        foreach($resp as $type) {
            switch ($type) {
                case 'exanode':
                    $findExaNode = true;
                    break;
                case 'exanode2':
                    $findExaNode2 = TRUE;
                    break;
            }
        }
        $this->assertTrue($findExaNode);
        $this->assertTrue($findExaNode2);
        $nodetype = driverCommand::run('getNodeTypeDef', array(
            'nodetype' => 'exanode'
        ));
        $haveTitle = false;
        $haveField1 = false;
        $haveField2 = false;
        foreach($nodetype['fields'] as $field) {
            switch($field['name']) {
                case 'title': $haveTitle = true; break;
                case "field1": $haveField1 = true; break;
                case "field2": $haveField2 = true; break;
            }
        }
        $this->assertFalse($haveTitle);
        $this->assertTrue($haveField1);
        $this->assertTrue($haveField2);
        $this->assertEquals('field1', $nodetype['label_field']);
        
        $nodetype = driverCommand::run('getNodeTypeDef', array(
            'nodetype' => 'exanode2'
        ));
        $haveTitle = false;
        $haveField1 = false;
        $haveField2 = false;
        foreach($nodetype['fields'] as $field) {
            switch($field['name']) {
                case 'title': $haveTitle = true; break;
                case "field1": $haveField1 = true; break;
                case "field2": $haveField2 = true; break;
            }
        }
        $this->assertTrue($haveTitle);
        $this->assertTrue($haveField1);
        $this->assertFalse($haveField2);
        $this->assertEquals('title', $nodetype['label_field']);
        
        // Uninstall
        driverCommand::run('modUninstall', array('name' => 'nodes_example_mod'));
        $resp = driverCommand::run('getNodeTypeList');
        $findExaNode = false;
        $findExaNode2 = false;
        foreach($resp as $type) {
            switch ($type) {
                case 'exanode':
                    $findExaNode = true;
                    break;
                case 'exanode2':
                    $findExaNode2 = TRUE;
                    break;
            }
        }
        $this->assertFalse($findExaNode);
        $this->assertFalse($findExaNode2);
        
        driverUser::sudo(false);
    }
    
    public function testSQL() {
        driverUser::sudo(true);
        
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_sql_example.zip',
        ));
        // Try
        $sql = "show tables like 'test_table'";
        $q = dbConn::Execute($sql);
        $this->assertFalse($q->EOF);
        // Uninstall
        driverCommand::run('modUninstall', array('name' => 'sql_example_mod'));
        $q = dbConn::Execute($sql);
        $this->assertTrue($q->EOF);
        
        driverUser::sudo(false);
    }
    
    public function testCommands() {
        driverUser::sudo(true);
        
        $resp = driverCommand::run('modInstall', array(
            'zip' => 'tests/drivers/mod_template/mod_commands_example.zip',
        ));
        // Try
        $this->assertNotNull(driverConfig::getCFG()->getSection('[test_config]'));
        $this->assertTrue(driverConfig::getCFG()->getSection('[test_config]')->getAsBoolean('test'));
        // Uninstall
        driverCommand::run('modUninstall', array('name' => 'commands_example_mod'));
        $this->assertNull(driverConfig::getCFG()->getSection('[test_config]'));
        
        driverUser::sudo(false);
    }
    
    public function testVersionIsGreaterOrEqual() {
        $this->assertTrue(driverTools::versionIsGreaterOrEqual('1', '2'));
        $this->assertTrue(driverTools::versionIsGreaterOrEqual('1.1', '1.2'));
        $this->assertTrue(driverTools::versionIsGreaterOrEqual('1.1.1', '1.1.2'));

        $this->assertFalse(driverTools::versionIsGreaterOrEqual('2', '1'));
        $this->assertFalse(driverTools::versionIsGreaterOrEqual('1.2', '1.1'));
        $this->assertFalse(driverTools::versionIsGreaterOrEqual('1.1.2', '1.1.1'));

        $this->assertFalse(driverTools::versionIsGreaterOrEqual('1.001.002', '1.1.1'));
        $this->assertTrue(driverTools::versionIsGreaterOrEqual('1.1.1', '1.001.002'));
        
        $this->assertTrue(driverTools::versionIsGreaterOrEqual('1.07.12', '1.08.05'));
        $this->assertTrue(driverTools::versionIsGreaterOrEqual('1.07.x', '1.07.05'));
        $this->assertTrue(driverTools::versionIsGreaterOrEqual('1.x.x', '1.08.05'));
        $this->assertTrue(driverTools::versionIsGreaterOrEqual('1.x.x', '2.08.05'));
    }
}
