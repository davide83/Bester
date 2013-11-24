<?php
/* Category.class.php - BetSter project (22.05.06)
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

class Category {

	var $id;
	var $position;
	var $name;
	var $description;
	var $image;


	function Category($id, $position, $name, $description, $image){
		$this->id = $id;
		$this->position = $position;
		$this->name = $name;
		$this->description = $description;
		$this->image = $image;
	}

	function getNrBets(){
		$db_mapper = new DbMapper;
		$bets = $db_mapper->getActualCategoryBets($this->id);
		return count($bets);
	}

	function getCategoryId(){
		return $this->id;
	}
	
	function getCategoryPosition(){
		return $this->position;
	}

	function getCategoryName(){
		return $this->name;
	}

	function getCategoryDescription(){
		return $this->description;
	}

	function getCategoryImage(){
		return $this->image;
	}



	// set functions
	function setCategoryId($id){
		$this->id = $id;
	}

	function setCategoryPosition($position){
		$this->position = $position;
	}

	function setCategoryName($name){
		$this->name = $name;
	}

	function setCategoryDescription($desc){
		$this->description = $desc;
	}

	function setCategoryImage($image){
		$this->image = $image;
	}

}
?>
