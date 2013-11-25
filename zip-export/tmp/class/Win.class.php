<?php
/*
 * Created on 03.09.2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class Win {

var $id;
var $bet_title;
var $possibility_name;
var $quote;
var $credits;


function Win($id, $bet_title, $possibility_name, $quote, $credits){
	$this->id = $id;
	$this->bet_title = $bet_title;
	$this->possibility_name = $possibility_name;
	$this->quote = $quote;
	$this->credits = $credits;
}

function getWinId(){
	return $this->id;
}

function getBetTitle(){
	return $this->bet_title;
}

function getPossibilityName(){
	return $this->possibility_name;
}

function getQuote(){
	return $this->quote;
}

function getCredits(){
	return $this->credits;
}

}
?>
