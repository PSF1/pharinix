<?php
/* 
 * Pharinix Copyright (C) 2017 Pedro Pelaez <aaaaa976@gmail.com>
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

/**
 * @see pecl install pthreads
 */
class threadTest extends Thread {
    protected $testfile = '';
    protected $result = 0;


    public function __construct($testfile) {
        $this->testfile = $testfile;
    }
    
    /**
     * We open a configuration file, or create it, and change 5000 values, or add it.
     * 
     * @see http://php.net/manual/es/book.pthreads.php#118320
     */
    public function run() {
        // Allow colisions
        sleep(2);
        $cfg = new driverConfigIni($this->testfile);
        $cfg->parse();
        $cfg->addSection('[long section]');
        for ($index = 0; $index < 5000; $index++) {
            $cfg->getSection('[long section]')->set('key_' . $index, $index);
        }
        $cfg->save($this->testfile);
        // To verify collisions we save the file size
        $this->result = filesize($this->testfile);
    }
    
    public function getResult() {
        return $this->result;
    }
}