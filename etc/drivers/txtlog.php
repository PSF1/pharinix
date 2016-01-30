<?php

/*
 * Pharinix Copyright (C) 2016 Pedro Pelaez <aaaaa976@gmail.com>
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

class driverLogTXT {
    protected $handle;
    protected $path;
    protected $disposed = false;
    protected $uid;
    
    public function __construct($path) {
        $this->setPath($path);
        $this->uid = uniqid();
    }
    
    public function __destruct() {
        if ($this->disposed) return;
        $this->close();
    }
    
    /**
     * Validate the log file path. If haven't a folder path it will write the log file into var/logs/ folder. <br/>
     * This method is compatible with fatal error handler of driverHooks, getting absolute path from current script if not is in gived path.
     * @param string $path It can, or not, include a folder path.
     */
    public function setPath($path) {
        $fInfo = driverTools::pathInfo($path);
        if ($fInfo['path'] === FALSE) {
            // Don't have a path defined
            $curdir = __FILE__;
            $curdir = str_replace('\\', '/', $curdir);
            $curdir = str_replace('etc/drivers/txtlog.php', 'var/logs/', $curdir);
            if (!is_dir($curdir)) {
                mkdir($curdir, 0777, true);
            }
            $path = $curdir.$path;
        }
        $this->path = $path;
    }
    
    public function log($msg) {
        if ($this->disposed) return;
        $this->handle = fopen($this->path, "ab+");
        if ($msg != '') {
            fwrite($this->handle, "[".date("Y-m-d H:i:s")." - {$this->uid}] - ".$msg."\n");
        } else {
            fwrite($this->handle, "\n");
        }
        fclose($this->handle);
    }
    
    public function close() {
        $this->disposed = true;
    }
    
    public static function logNow($path, $msg) {
        $l = new driverLogTXT($path);
        $l->log($msg);
        $l->close();
    }
}
