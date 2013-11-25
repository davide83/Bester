<?php
/* Session.class.php - BetSter project (22.05.06)
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


class Session {

	var $sessionstate = false;
	var $username;

	function Session(){	
		session_start();
		if (isset($_SESSION['username'])){
			$this->username = $_SESSION['username'];
			$this->sessionstate = true;
		}
	}

	function getState(){
		return $this->sessionstate;
	}

	function getUsername(){
		return $this->username;
	}


	function setState(){
	}

	function lockF5(){
		$_SESSION['F5'] = 1;
	}

	function unlockF5(){
		$_SESSION['F5'] = 0;
	}

	function getF5(){
		if ($_SESSION['F5'] == 1)
			return "locked";
		else 
			return "unlocked";
	}

	function setUsername(){
	}

	// Set session variables if user logged in
	function login($username, $password){
		
		$db_mapper = new DbMapper;
		$logger = new Logger;
		if($db_mapper->checkIfUserInDB($username, $password)){
			$_SESSION['username'] = $username;
			$_SESSION['F5'] = 0;
			$this->username = $username;
			$this->sessionstate = true;
			$logger->writeLog($this->username, _LOGGED_IN);
		}
	}

	function logout(){
		$logger = new Logger;
		$logger->writeLog($this->username, _LOGGED_OUT);
		session_destroy();
		$this->sessionstate = false;
	}
}
?>
