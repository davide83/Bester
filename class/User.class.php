<?php
/* User.class.php - BetSter project (22.05.06)
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

require_once("class/DbMapper.class.php");
require_once("configuration.php");
 
class User {

var $id;
var $username;
var $password;
var $email;
var $firstname;
var $lastname;
var $balance;
var $status;


function User($id, $username, $email, $firstname, $lastname, $balance, $status){
	$this->id = $id;
	$this->username = $username;
	$this->email = $email;
	$this->firstname = $firstname;
	$this->lastname = $lastname;
	$this->balance = $balance;
	$this->status = $status;
}

function getUserId(){
	return $this->id;
}

function getUsername(){
	return $this->username;
}

function getEmail(){
	return $this->email;
}

function getFirstname(){
	return $this->firstname;
}

function getLastname(){
	return $this->lastname;
}

function getBalance(){
	// create a new object for the actuality :-(
	$my_db_mapper = new DbMapper;
	$this->balance = $my_db_mapper->getUserBalance($this->id); 
	return $this->balance;
}

function setBalance($credits){
	$db_mapper = new DbMapper;
	$this->balance = $credits;
	$db_mapper->setBalance($this->id, $this->balance);
}

function setEmail($email){
	$db_mapper = new DbMapper;
	$this->email = $email;
	$db_mapper->setEmail($this->username, $this->email);
}

function getStatus(){
	return $this->status;
}

function activate(){
	$db_mapper = new DbMapper;
	$this->status = "active";
	$db_mapper->setStatus($this->id, $this->status);
}

function deactivate(){
    $db_mapper = new DbMapper;
	$this->status = "inactive";
	$db_mapper->setStatus($this->id, $this->status);
}

function lock(){
	$db_mapper = new DbMapper;
	$this->status = "locked";
	$db_mapper->setStatus($this->id, $this->status);
}

function betmaster(){
		$db_mapper = new DbMapper;
	$this->status = "betmaster";
	$db_mapper->setStatus($this->id, $this->status);
}
}
?>
