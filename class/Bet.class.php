<?php
/* Bet.class.php - BetSter project (22.05.06)
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


class Bet {
	var $id;
	var $title;
	var $subtitle;
	var $category_id;
	var $category_name;
	var $image;
	var $start_time;
	var $end_time;
	var $created;
	var $autor_id;
	var $possibilities_names = array();
	var $possibilities_ids = array();
	var $possibilities_quotes = array();

	function Bet($id, $title, $subtitle, $category_id, $category_name, $image, $start_time, $end_time,
			$created, $autor_id, 
			$possibilities_names, $possibilities_ids, $possibilities_quotes){
		$this->id = $id;
		$this->title = $title;
		$this->subtitle = $subtitle;
		$this->category_id = $category_id;
		$this->category_name = $category_name;
		$this->image = $image;
		$this->start_time = $start_time;
		$this->end_time = $end_time;
		$this->created = $created;
		$this->autor_id = $autor_id;
		$this->possibilities_names = $possibilities_names;
		$this->possibilities_ids = $possibilities_ids;
		$this->possibilities_quotes = $possibilities_quotes;
	}

	// execute the bet ...
	function execute($pos_id, $credits, $user){
		$db_mapper = new DbMapper;
		$logger = new Logger;
		$possibility_name = $db_mapper->getPossibilityNameFromId($pos_id);

		$db_mapper->setTransaction($user->getUserId(), $pos_id, $credits);
		$logger->writeLog($user->getUsername(), _BET_COMPLETED_WITH." "
				._POSSIBILITY.": ".$possibility_name." , "._CREDITS.": ".$credits);
		$db_mapper->decBalance($user->getUserId(), $credits);
		$logger->writeLog($user->getUsername(), _BALANCE_DECREASED." "
				._POSSIBILITY.": ".$possibility_name." , "._CREDITS.": ".$credits);
	}

	function freeze($pos_id){
		$db_mapper = new DbMapper;
		$db_mapper->setWonPossibility($pos_id);
	}

	function getBetId(){
		return $this->id;
	}

	function getCategoryId(){
		return $this->category_id;
	}

	function getCategoryName(){
		return $this->category_name;
	}

	function getBetImage(){
		return $this->image;
	}

	function getBetTitle(){
		return $this->title;
	}

	function getSubtitle(){
		return $this->subtitle;
	}

	function getBetStartTime(){
		return $this->start_time;
	}

	function getBetEndTime(){
		return $this->end_time;
	}

	function getBetCreationTime(){
		return $this->created;
	}

	function getBetAutorId(){
		return $this->autor_id;
	}


	function getPossibilitiesNames(){
		return $this->possibilities_names;
	}

	function getPossibilitiesIds(){
		return $this->possibilities_ids;
	}

	function getPossibilitiesQuotes(){
		return $this->possibilities_quotes;
	}

	// set methods

	function setBetId($id){
		$this->id = $id;
	}


	function setCategoryId($cat_id){
		$this->category_id = $cat_id;
	}

	function setCategoryName($cat_name){
		$this->category_name = $cat_name;
	}

	function setBetImage(){
		$this->image = 1;
	}

	function setBetTitle($title){
		$this->title = $title;
	}

	function setSubtitle($subtitle){
		$this->subtitle = $subtitle;
	}

	function setBetStartTime($start_time){
		$this->start_time = $start_time;
	}

	function setBetEndTime($end_time){
		$this->end_time = $end_time;
	}

	function setBetAutorId($id){
		$this->autor_id = $id;
	}

	function setPossibilitiesNames($pos_array){
		$this->possibilities_names = $pos_array;
	}
	function setPossibilitiesIds($id_array){
		$this->possibilities_ids = $id_array;
	}

	function setPossibilitiesQuotes($quotes_array){
		$this->possibilities_quotes = $quotes_array;
	}
}
?>
