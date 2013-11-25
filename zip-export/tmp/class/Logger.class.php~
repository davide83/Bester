<?php
/* Logger.class.php - BetSter project (22.05.06)
 * Copyright (C) 2006  Harald Kröll
 * 
 * This program is free software; you can redistribute it and/or modify it 
 * under the terms of the GNU General Public License as published by the Free 
 * Software Foundation; either version 2 of the License, or (at your option) 
 * any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for 
 * more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, write to the Free Software Foundation, Inc., 
 * 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
 */


require_once("configuration.php");
require_once("class/DbMapper.class.php");

$db_mapper = new DbMapper;

class Logger {

	function Logger() {
	}

	function writeLog($user, $event){
		$db_mapper = new DbMapper;

		if ($user == ""){
			$user = "not registered";
		}

		$host= $_SERVER['REMOTE_HOST'];
		$addr = $_SERVER['REMOTE_ADDR'];
		$agent = $_SERVER['HTTP_USER_AGENT'];

		$db_mapper->insertLog($host, $addr, $user, $event,
				$agent);
	}

	function deleteLog($nr){
		$db_mapper->deleteLog($nr);

	}
}
?>
