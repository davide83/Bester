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


class Transaction {

var $id;
var $bet_id;
var $bet_title;
var $possibility_name;
var $possibility_id;
var $user_id;
var $credits;
var $time;

function Transaction($id, $bet_id, $bet_title, $possibility_name, $possibility_id, $user_id, $credits, $time){
	$this->id = $id;
	$this->bet_id = $bet_id;
	$this->bet_title = $bet_title;
	$this->possibility_name = $possibility_name;
	$this->possibility_id = $possibility_id;
	$this->user_id = $user_id;
	$this->credits = $credits;
	$this->time = $time;
}

function getTransactionId(){
	return $this->id;
}

function getBetId(){
	return $this->bet_id;
}

function getBetTitle(){
	return $this->bet_title;
}

function getPossibilityName(){
	return $this->possibility_name;
}

function getPossibilityId(){
	return $this->possibility_id;
}

function getUserId(){
	return $this->user_id;
}

function getCredits(){
	return $this->credits;
}

function getTime(){
	return $this->time;
}

}
?>
