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

class iniFilesTest extends PHPUnit_Framework_TestCase {

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        while (!is_file("etc/pharinix.config.php")) {
            chdir("../");
        }
        include_once 'tests/drivers/etc/bootstrap.php';
        //driverUser::sessionStart();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        //driverUser::logOut();
    }
    
    public function testLexEmpty() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/empty.ini');
        $token = $cfg->lex();
        $this->assertEquals('', $cfg->getError());
        $this->assertFalse($token);
    }
    
    public function testLexStartNewLine() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/start_new_line.ini');
        $ind = 0;
        $resps = array(
            "\n",
            "; this is an INI file",
            "\n",
            "[section]",
            "\n",
            "key",
            "=",
            "value",
            "\n",
            false
        );
        do {
            $token = $cfg->lex();
            $this->assertEquals($resps[$ind], $token);
            ++$ind;
        } while ($token !== false);
    }
    
    public function testLexMultiLineValue() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/multiline_value.ini');
        $ind = 0;
        $resps = array(
            "; this is an INI file",
            "\n",
            "[section]",
            "\n",
            "key",
            "=",
            "\"value value value value value value \nvalue value value value value \"",
            "\n",
            false
        );
        do {
            $token = $cfg->lex();
            $this->assertEquals($resps[$ind], $token);
            ++$ind;
        } while ($token !== false);
    }
    
    public function testLexMultiLineSingleQuoteValue() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/multiline_value_simple_quote.ini');
        $ind = 0;
        $resps = array(
            "; this is an INI file",
            "\n",
            "[section]",
            "\n",
            "key",
            "=",
            "'value value value value value value \nvalue value value value value '",
            "\n",
            false
        );
        do {
            $token = $cfg->lex();
            $this->assertEquals($resps[$ind], $token);
            ++$ind;
        } while ($token !== false);
    }
    
    public function testLexMultiLineWithSingleQuote() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/multiline_value_with_single_quote.ini');
        $ind = 0;
        $resps = array(
            "; this is an INI file",
            "\n",
            "[section]",
            "\n",
            "key",
            "=",
            "\"value 'value' value value value value \nvalue value value value value \"",
            "\n",
            false
        );
        do {
            $token = $cfg->lex();
            $this->assertEquals($resps[$ind], $token);
            ++$ind;
        } while ($token !== false);
    }
    
    public function testLexNoValue() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/no_value.ini');
        $ind = 0;
        $resps = array(
            "; this is an INI file",
            "\n",
            "[section]",
            "\n",
            "key",
            "\n",
            "key2",
            "=",
            "value",
            false
        );
        do {
            $token = $cfg->lex();
            $this->assertEquals($resps[$ind], $token);
            ++$ind;
        } while ($token !== false);
    }
    
    public function testLexCommentAtEnd() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/comment_at_end.ini');
        $ind = 0;
        $resps = array(
            "; this is an INI file",
            "\n",
            "[section]",
            "\n",
            "key",
            "=",
            "1",
            "; A comment",
            "\n",
            "key1",
            "=",
            "; Other comment",
            false
        );
        do {
            $token = $cfg->lex();
            $this->assertEquals($resps[$ind], $token);
            ++$ind;
        } while ($token !== false);
    }
    
    public function testLexCommentFakeInValue() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/comment_fake_in_value.ini');
        $ind = 0;
        $resps = array(
            "; this is an INI file",
            "\n",
            "[section]",
            "\n",
            "key",
            "=",
            "\"1 ; A comment\nkey = ; Other comment\"",
            false
        );
        do {
            $token = $cfg->lex();
            $this->assertEquals($resps[$ind], $token);
            ++$ind;
        } while ($token !== false);
    }
    
    public function testLexEmptyValue() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/empty_value.ini');
        $ind = 0;
        $resps = array(
            "key",
            "=",
            "''",
            false
        );
        do {
            $token = $cfg->lex();
            $this->assertEquals($resps[$ind], $token);
            ++$ind;
        } while ($token !== false);
    }
    
    public function testLexDuplicateKey() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/duplicated_key.ini');
        $ind = 0;
        $resps = array(
            "; this is an INI file",
            "\n",
            "[section]",
            "\n",
            "key",
            "=",
            "1",
            "\n",
            "key",
            "=",
            "2",
            false
        );
        do {
            $token = $cfg->lex();
            $this->assertEquals($resps[$ind], $token);
            ++$ind;
        } while ($token !== false);
    }
    
    public function testParse_comment_at_end() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/comment_at_end.ini');
        $cfg->parse();
        $this->assertEquals('1', $cfg->getSection('[section]')->get('key'));
        $this->assertEquals('', $cfg->getSection('[section]')->get('key1'));
    }
    
    public function testParse_comment_fake_in_value() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/comment_fake_in_value.ini');
        $cfg->parse();
        $this->assertEquals("1 ; A comment\nkey = ; Other comment", $cfg->getSection('[section]')->get('key'));
    }
    
    public function testParse_duplicated_key() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/duplicated_key.ini');
        $cfg->parse();
        $this->assertEquals("2", $cfg->getSection('[section]')->get('key'));
    }
    
    public function testParse_empty() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/empty.ini');
        $cfg->parse();
        $this->assertTrue(true);
    }
    
    public function testParse_empty_section() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/empty_section.ini');
        $cfg->parse();
        $this->assertNotNull($cfg->getSection('[section]'));
    }
    
    public function testParse_empty_value() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/empty_value.ini');
        $cfg->parse();
        $this->assertEquals('', $cfg->getSection(' ')->get('key'));
    }
    
    public function testParse_free_key() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/free_key.ini');
        $cfg->parse();
        $this->assertEquals('value', $cfg->getSection(' ')->get('key'));
    }
    
    public function testParse_multiline_value() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/multiline_value.ini');
        $cfg->parse();
        $this->assertEquals("value value value value value value \nvalue value value value value ", $cfg->getSection('[section]')->get('key'));
    }
    
    public function testParse_multiline_value_simple_quote() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/multiline_value_simple_quote.ini');
        $cfg->parse();
        $this->assertEquals("value value value value value value \nvalue value value value value ", $cfg->getSection('[section]')->get('key'));
    }
    
    public function testParse_multiline_value_with_single_quote() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/multiline_value_with_single_quote.ini');
        $cfg->parse();
        $this->assertEquals("value 'value' value value value value \nvalue value value value value ", $cfg->getSection('[section]')->get('key'));
    }
    
    public function testParse_no_key() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/no_key.ini');
        $cfg->parse();
        $this->assertEquals("", $cfg->getSection('[section]')->get('value'));
    }
    
    public function testParse_no_value() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/no_value.ini');
        $cfg->parse();
        $this->assertEquals("", $cfg->getSection('[section]')->get('key'));
        $this->assertEquals("value", $cfg->getSection('[section]')->get('key2'));
    }
    
    public function testParse_php_head() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/php_head.ini');
        $cfg->parse();
        $this->assertEquals("value", $cfg->getSection('[section]')->get('key'));
    }
    
    public function testParse_start_new_line() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/start_new_line.ini');
        $cfg->parse();
        $this->assertEquals("value", $cfg->getSection('[section]')->get('key'));
    }
    
    public function testParse_string_value() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/string_value.ini');
        $cfg->parse();
        $this->assertEquals("value", $cfg->getSection('[section]')->get('key'));
    }
    
    public function testParse_two_keys() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/two_keys.ini');
        $cfg->parse();
        $this->assertEquals("1", $cfg->getSection('[section]')->get('key'));
        $this->assertEquals("2", $cfg->getSection('[section]')->get('key1'));
        $this->assertEquals("3", $cfg->getSection('[section]')->get('key2'));
        $this->assertEquals("4", $cfg->getSection('[section]')->get('key3'));
    }
    
    public function testParse_two_sections() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/two_sections.ini');
        $cfg->parse();
        $this->assertEquals("value", $cfg->getSection('[section]')->get('key'));
        $this->assertEquals("value", $cfg->getSection('[section 2]')->get('key'));
    }
    
    public function testParse_especial_chars_on_value() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/especial_chars_on_value.ini');
        $cfg->parse();
        $this->assertEquals(";\"[]", $cfg->getSection('[section]')->get('key'));
    }
    
    public function testParse_two_sections_set_value() {
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/two_sections.ini');
        $cfg->parse();
        $this->assertEquals("value", $cfg->getSection('[section]')->get('key'));
        $this->assertEquals("value", $cfg->getSection('[section 2]')->get('key'));
        $cfg->getSection('[section 2]')->set('key', '"new value"');
        $this->assertEquals('new value', $cfg->getSection('[section 2]')->get('key'));
    }
    
    public function testParse_save() {
        $testfile = 'tests/drivers/cfg_ini/save_test.ini';
        if (is_file($testfile)) {
            unlink($testfile);
        }
        $cfg = new driverConfigIni('tests/drivers/cfg_ini/two_sections.ini');
        $cfg->parse();
        $cfg->getSection('[section 2]')->set('key', "'new value\nnew line'");
        $cfg->getSection('[section 2]')->set('key2', "2");
        $cfg->save($testfile);
        // Saved?
        $this->assertTrue(is_file($testfile));
        // Test values
        $cfg = new driverConfigIni($testfile);
        $cfg->parse();
        $this->assertEquals("value", $cfg->getSection('[section]')->get('key'));
        $this->assertEquals("new value\nnew line", $cfg->getSection('[section 2]')->get('key'));
        $this->assertEquals("2", $cfg->getSection('[section 2]')->get('key2'));
    }
}