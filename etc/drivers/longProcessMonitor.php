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
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

/**
 * Encapsulate process monitors to see the evolution of a long process. First we need create a monitor, next asign her ID to the process and we start it. (The process must be compatible with this tech).
 * 
 * Only one PHP thread must update the monitor state, but any number of threads can read it.
 */
class driverLPMonitor {
    protected static $path = 'var/lp/';

    public static function getPath($usrID = null) {
        if ($usrID == null) {
            $usrFolder = driverUser::getID();
        } else {
            $usrFolder = $usrID;
        }
        $resp = self::$path.'/'.$usrFolder.'/';
        if (!is_dir($resp)) {
            mkdir($resp, 0777, true);
        }
        return $resp;
    }
    
    /**
     * Start a new long process monitor
     * @param integer $stepsTotal Total number of steps.
     * @param string $label Global label
     * @return \stdClass Started monitor object.
     */
    public static function start($stepsTotal = 0, $label = null) {
        if ($label == null) {
            $label = __('Long process');
        }
        $resp = new stdClass();
        $resp->label = $label;
        $resp->id = str_replace('.', '', uniqid('', true));
        $resp->startTime = time();
        $resp->step = 0;
        $resp->stepLabel = __('Starting');
        $resp->stepsTotal = $stepsTotal; // Cero to show indeterminated progress.
        $resp->percent = 0;
        $resp->error = false;
        
        file_put_contents(self::getPath().$resp->id.'.lpm', json_encode($resp));
        return $resp;
    }
    
    /**
     * Update a process monitor
     * @param string $id Monitor ID
     * @param integer $step New step
     * @param string $stepLabel Label about the new step
     * @param integer $stepsTotal New total steps. Optional, if not defined don't change.
     * @return \stdClass Monitor object.
     */
    public static function update($id, $step, $stepLabel, $stepsTotal = -1) {
        if (!is_file(self::getPath().$id.'.lpm')) {
            $resp = new stdClass();
            $resp->label = __('Unknowed process monitor.');
            $resp->id = '';
            $resp->startTime = time();
            $resp->step = 0;
            $resp->stepLabel = __('Unknowed process monitor.');
            $resp->stepsTotal = 0;
            $resp->percent = 0;
            $resp->error = true;
        } else {
            $json = file_get_contents(self::getPath().$id.'.lpm');
            $resp = json_decode($json);
            $resp->step = $step;
            $resp->stepLabel = $stepLabel;
            $resp->error = false;
            if ($stepsTotal >= 0) $resp->stepTotal = $stepsTotal;
            if ($resp->stepsTotal > 0) {
                $resp->percent = round(($resp->step * 100) / $resp->stepsTotal, 2);
            }
            file_put_contents(self::getPath().$resp->id.'.lpm', json_encode($resp));
        }
        return $resp;
    }
    
    /**
     * Get the actual state of a monitor
     * @param string $id Monitor ID
     * @return \stdClass Started monitor object.
     */
    public static function read($id, $usrID = null) {
        if (!is_file(self::getPath($usrID).$id.'.lpm')) {
            $resp = new stdClass();
            $resp->label = __('Unknowed process monitor.');
            $resp->id = '';
            $resp->startTime = time();
            $resp->step = 0;
            $resp->stepLabel = __('Unknowed process monitor.');
            $resp->stepsTotal = 0;
            $resp->percent = 0;
            $resp->error = true;
        } else {
            $json = file_get_contents(self::getPath($usrID).$id.'.lpm');
            $resp = json_decode($json);
        }
        return $resp;
    }
    
    public static function setError($id, $val) {
        if (!is_file(self::getPath().$id.'.lpm')) {
            $resp = new stdClass();
            $resp->label = __('Unknowed process monitor.');
            $resp->id = '';
            $resp->startTime = time();
            $resp->step = 0;
            $resp->stepLabel = __('Unknowed process monitor.');
            $resp->stepsTotal = 0;
            $resp->percent = 0;
            $resp->error = true;
        } else {
            $json = file_get_contents(self::getPath().$id.'.lpm');
            $resp = json_decode($json);
            $resp->error = $val;
            file_put_contents(self::getPath().$resp->id.'.lpm', json_encode($resp));
        }
        return $resp;
    }


    /**
     * Close a monitor
     * @param string $id Monitor ID
     * @return boolean TRUE if exist the monitor.
     */
    public static function close($id) {
        if (!is_file(self::getPath().$id.'.lpm')) {
            return false;
        } else {
            @unlink(self::getPath().$id.'.lpm');
            return true;
        }
    }
    
    /**
     * Get a list of active monitors.
     * @param boolean $withContent If TRUE return state of each monitor.
     * @return array List of monitor objects.
     */
    public static function getActives($withContent = false) {
        $files = driverTools::lsDir(self::getPath(), '*.lpm');
        $resp = array();
        foreach($files['files'] as $file) {
            $item = new stdClass();
            $fInfo = driverTools::pathInfo($file);
            $item->id = $fInfo['name'];
            if ($withContent) {
                $resp[] = self::read($item->id);
            } else {
                $resp[] = $item;
            }
        }
        return $resp;
    }
}