<?php
/*
 *     Smarty plugin
 * -------------------------------------------------------------
 * File:     	function.cmd.php
 * Type:     	function
 * Name:     	cmd
 * Description: Execute a Pharinix command
 *
 * -------------------------------------------------------------
 * @license GNU Public License (GPL)
 *
 * -------------------------------------------------------------
 * Parameter:
 * - command   	= Command to execute (required)
 * - interface  = Output interface (optional, default is 'echoHtml')
 * - args       = Extra parameters
 * -------------------------------------------------------------
 * Example usage:
 *
 * {cmd command="manHTML" cmd="manHTML"}
 */

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

function smarty_function_cmd($params, &$smarty) {

	if(!isset($params['command'])) {
		$smarty->trigger_error("smarty function cmd : command required.");
		return;
	}
        $command = $params['command'];
        unset($params['command']);
        
        if (!isset($params['interface'])) {
            $params['interface'] = "echoHtml";
        }
        ob_start();
        driverCommand::run($command, $params);
        $output = ob_get_clean();
        
        return $output;
}