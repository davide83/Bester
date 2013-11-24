<?php
/* Site.class.php - BetSter project (22.05.06)
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

class Site {

	var $id;
	var $title;
	var $text;
	var $author;
	var $date;


	function Site($id, $sitetitle, $text, $author, $date){
		$this->id = $id;
		$this->title = $sitetitle;
		$this->text = $text;
		$this->author = $author;
		$this->date = $date;
	}


	function getId(){
		return $this->id;
	}

	function getTitle(){
		//return "lkhl";
		return $this->title;
	}

	function getHtmlText(){
		return nl2br($this->text);
	}

	function getText(){
		return $this->text;
	}

	function getAuthor(){
		return $this->author;
	}

	function getDate(){
		return $this->date;
	}

	function setTitle($sitetitle){
		$this->title = $sitetitle;
	}

	function setText($text){
		$this->text = $text;

	}

	function setAuthor($author){
		$this->author = $author;
	}

}
?>
