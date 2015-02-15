<?php

/*
 * Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
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
if (!defined("CMS_VERSION")) {
    header("HTTP/1.0 404 Not Found");
    die("");
}

class driverPages {
    
    /**
     * Find the page definition in data base or return FALSE
     * @param string $name
     * @return boolean Recordset with the page with the name
     */
    public static function getPage($name) {
        $sql = "SELECT * FROM pages where `name` = '$name'";
        $q = dbConn::get()->Execute($sql);
        if ($q->EOF) {
            return false;
        } else {
            return $q;
        }
    }
    
    /**
     * Get associated commands whit the block order by priority, cero first. 
     * @param int $pageId
     * @param string $colId
     * @return boolean Recordset list with commands
     */
    public static function getCommands($pageId, $colId) {
        $sql = "SELECT * FROM `page-blocks` where `idpage` = $pageId && `idcol` = '$colId'";
        $sql .= " order by `priority` asc";
        $q = dbConn::get()->Execute($sql);
        if ($q->EOF) {
            return false;
        } else {
            return $q;
        }
    }
}