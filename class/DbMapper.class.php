<?php
/* DbMapper.class.php - BetSter project (22.05.06)
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

require_once("Win.class.php");

class DbMapper {

	//var $win = new Win();
	
	function DbMapper(){
		$connect  = mysql_connect (HOST, USER, PASSWORD) or 
			die("Database error, contact your admin");
		if (!mysql_select_db (DBNAME, $connect))
			error_catcher();
	}

	// returns a Bet from a certain id
	function getBet($id){

		$query = "SELECT * FROM bets WHERE id = '$id'";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$rowarray = mysql_fetch_array($result);

		$query = "SELECT id,name FROM possibilities WHERE bets_id = '$id'";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$possibilities_ids_array = array();
		$possibilities_names_array = array();	
		$i = 0;

		for ($possibilities_counter = 0; $possibilities_counter < (mysql_num_rows($result)) ; $possibilities_counter++) {
			$possibilities_ids_array[$i] = mysql_result($result, $possibilities_counter, "id");
			$possibilities_names_array[$i] = mysql_result($result, $possibilities_counter, "name");
			$i++;
		}

		$return_bet = new Bet($id = $rowarray["id"],
				$title = $rowarray["title"],
				$subtitle = $rowarray["subtitle"],
				$categories_id = $rowarray["categories_id"],
				$categories_name = $rowarray["categories_id"],
				$image = $rowarray["image"],
				$start_time = $rowarray["start"],
				$end_time = $rowarray["end"], 
				$created =  $rowarray["created"],
				$autor_id =  $rowarray["autor_id"],
				$possibilities_names = $possibilities_names_array,
				$possibilities_ids = $possibilities_ids_array,
				$possibilities_quotes = 0); 
		return $return_bet;
	}

	function getBetIdFromPossibility($possibility_id) {
		$query = "SELECT DISTINCT possibilities.bets_id FROM possibilities WHERE possibilities.id = '$possibility_id'";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		return mysql_result($result,0);
	}


	// returns the bets that are active now
	function getActiveBets() {

		$query = "SELECT * FROM bets WHERE start < NOW() AND end > NOW() ORDER BY end ASC";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$return_bet_array = array();


		for ($bet_counter = 0; $bet_counter < (mysql_num_rows($result)) ; $bet_counter++) {

			$bet_id = mysql_result($result, $bet_counter, "id");

			// Read out Arrays with Possibilities names and ids	
			$query = "SELECT id,name FROM possibilities WHERE bets_id = '$bet_id'";
			$result2 = mysql_db_query (DBNAME,$query) or error_catcher();

			$possibilities_ids_array = array();
			$possibilities_names_array = array();
			$possibilities_quotes_array = array();

			for ($possibilities_counter = 0; $possibilities_counter < (mysql_num_rows($result2)) ; $possibilities_counter++) {
				$possibilities_ids_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "id");
				$possibilities_names_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "name");
			}

			// Read out and calculate the array with the quotes		
			$i = 0;
			foreach ($possibilities_ids_array as $possibility_id) {
				$query1 = "SELECT SUM(credits) FROM transactions WHERE possibilities_id = '$possibility_id'";
				$query2 = "SELECT SUM(credits)
					FROM transactions, possibilities
					WHERE possibilities.bets_id = '$bet_id'
					AND possibilities.id = transactions.possibilities_id";
				$result1 = mysql_db_query (DBNAME,$query1)  or error_catcher();
				$result2 = mysql_db_query (DBNAME,$query2)  or error_catcher();
				$credits_of_possibility = mysql_result($result1, 0);
				$credits_of_bet = mysql_result($result2, 0);
				if ($credits_of_possibility == 0){
					$dirty_quote = "-";
				}
				else {
					$quote = $credits_of_bet / $credits_of_possibility;
					$dirty_quote = round($quote, 2);
				}
				$possibilities_quotes_array[$i] = $dirty_quote;
				$i++;
			}

			// get the category_name from the category id
			$query = "SELECT name FROM categories, bets WHERE bets.categories_id = categories.id AND bets.id = '$bet_id'";
			$result1 = mysql_db_query (DBNAME,$query)  or error_catcher();
			$category_name = mysql_result($result1, 0);


			// Write the Bet objects in the return array
			$return_bet_array[$bet_counter] = new Bet($bet_id,
					mysql_result($result, $bet_counter, "title"),
					mysql_result($result, $bet_counter, "subtitle"),
					mysql_result($result, $bet_counter, "categories_id"),
					$category_name,
					mysql_result($result, $bet_counter, "image"),
					mysql_result($result, $bet_counter, "start"),
					mysql_result($result, $bet_counter, "end"),
					mysql_result($result, $bet_counter, "created"),
					mysql_result($result, $bet_counter, "autor_id"),
					$possibilities_names_array,
					$possibilities_ids_array,
					$possibilities_quotes_array);		             
		}
		return $return_bet_array;
	}

	// returns the bets from a certain categoy
	function getActualCategoryBets($cat_id){

		$query = "SELECT * FROM bets WHERE start < NOW() AND end > NOW() AND categories_id = '$cat_id' ORDER BY end ASC";
		$result = mysql_db_query (DBNAME,$query)
			 or error_catcher();
		$return_bet_array = array();


		for ($bet_counter = 0; $bet_counter < (mysql_num_rows($result)) ; $bet_counter++) {

			$bet_id = mysql_result($result, $bet_counter, "id");

			// Read out Arrays with Possibilities names and ids	
			$query = "SELECT id,name FROM possibilities WHERE bets_id = '$bet_id'";
			$result2 = mysql_db_query (DBNAME,$query)
				 or error_catcher();

			$possibilities_ids_array = array();
			$possibilities_names_array = array();
			$possibilities_quotes_array = array();

			for ($possibilities_counter = 0; $possibilities_counter < (mysql_num_rows($result2)) ; $possibilities_counter++) {
				$possibilities_ids_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "id");
				$possibilities_names_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "name");
			}

			// Read out and calculate the array with the quotes		
			$i = 0;
			foreach ($possibilities_ids_array as $possibility_id) {
				$query1 = "SELECT SUM(credits) FROM transactions WHERE possibilities_id = '$possibility_id'";
				$query2 = "SELECT SUM(credits)
					FROM transactions, possibilities
					WHERE possibilities.bets_id = '$bet_id'
					AND possibilities.id = transactions.possibilities_id";
				$result1 = mysql_db_query (DBNAME,$query1)  or error_catcher();
				$result2 = mysql_db_query (DBNAME,$query2)  or error_catcher();
				$credits_of_possibility = mysql_result($result1, 0);
				$credits_of_bet = mysql_result($result2, 0);
				if ($credits_of_possibility == 0){
					$dirty_quote = "-";
				}
				else{
					$quote = $credits_of_bet / $credits_of_possibility;
					$dirty_quote = round($quote, 2);
				}
				$possibilities_quotes_array[$i] = $dirty_quote;
				$i++;
			}

			// get the category_name from the category id
			$query = "SELECT name FROM categories, bets WHERE bets.categories_id = categories.id AND bets.id = '$bet_id'";
			$result1 = mysql_db_query (DBNAME,$query)  or error_catcher();
			$category_name = mysql_result($result1, 0);


			// Write the Bet objects in the return array
			$return_bet_array[$bet_counter] = new Bet($bet_id,
					mysql_result($result, $bet_counter, "title"),
					mysql_result($result, $bet_counter, "subtitle"),
					mysql_result($result, $bet_counter, "categories_id"),
					$category_name,
					mysql_result($result, $bet_counter, "image"),
					mysql_result($result, $bet_counter, "start"),
					mysql_result($result, $bet_counter, "end"),
					mysql_result($result, $bet_counter, "created"),
					mysql_result($result, $bet_counter, "autor_id"),
					$possibilities_names_array,
					$possibilities_ids_array,
					$possibilities_quotes_array);		             
		}
		return $return_bet_array;
	}


	// returns the bets from a certain categoy
	function getCategoryBets($cat_id){

		$query = "SELECT * FROM bets WHERE  categories_id = '$cat_id' ORDER BY end ASC";
		$result = mysql_db_query (DBNAME,$query)
			 or error_catcher();
		$return_bet_array = array();


		for ($bet_counter = 0; $bet_counter < (mysql_num_rows($result)) ; $bet_counter++) {

			$bet_id = mysql_result($result, $bet_counter, "id");

			// Read out Arrays with Possibilities names and ids	
			$query = "SELECT id,name FROM possibilities WHERE bets_id = '$bet_id'";
			$result2 = mysql_db_query (DBNAME,$query)
				 or error_catcher();

			$possibilities_ids_array = array();
			$possibilities_names_array = array();
			$possibilities_quotes_array = array();

			for ($possibilities_counter = 0; $possibilities_counter < (mysql_num_rows($result2)) ; $possibilities_counter++) {
				$possibilities_ids_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "id");
				$possibilities_names_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "name");
			}

			// Read out and calculate the array with the quotes		
			$i = 0;
			foreach ($possibilities_ids_array as $possibility_id) {
				$query1 = "SELECT SUM(credits) FROM transactions WHERE possibilities_id = '$possibility_id'";
				$query2 = "SELECT SUM(credits)
					FROM transactions, possibilities
					WHERE possibilities.bets_id = '$bet_id'
					AND possibilities.id = transactions.possibilities_id";
				$result1 = mysql_db_query (DBNAME,$query1)  or error_catcher();
				$result2 = mysql_db_query (DBNAME,$query2)  or error_catcher();
				$credits_of_possibility = mysql_result($result1, 0);
				$credits_of_bet = mysql_result($result2, 0);
				if ($credits_of_possibility == 0){
					$dirty_quote = "-";
				}
				else{
					$quote = $credits_of_bet / $credits_of_possibility;
					$dirty_quote = round($quote, 2);
				}
				$possibilities_quotes_array[$i] = $dirty_quote;
				$i++;
			}

			// get the category_name from the category id
			$query = "SELECT name FROM categories, bets WHERE bets.categories_id = categories.id AND bets.id = '$bet_id'";
			$result1 = mysql_db_query (DBNAME,$query)  or error_catcher();
			$category_name = mysql_result($result1, 0);


			// Write the Bet objects in the return array
			$return_bet_array[$bet_counter] = new Bet($bet_id,
					mysql_result($result, $bet_counter, "title"),
					mysql_result($result, $bet_counter, "subtitle"),
					mysql_result($result, $bet_counter, "categories_id"),
					$category_name,
					mysql_result($result, $bet_counter, "image"),
					mysql_result($result, $bet_counter, "start"),
					mysql_result($result, $bet_counter, "end"),
					mysql_result($result, $bet_counter, "created"),
					mysql_result($result, $bet_counter, "autor_id"),
					$possibilities_names_array,
					$possibilities_ids_array,
					$possibilities_quotes_array);		             
		}
		return $return_bet_array;
	}



	// returns an array with all bets
	function getAllBets(){

		$query = "SELECT * FROM bets ORDER BY end ASC";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();

		$return_bet_array = array();


		for ($bet_counter = 0; $bet_counter < (mysql_num_rows($result)) ; $bet_counter++) {

			$bet_id = mysql_result($result, $bet_counter, "id");

			// Read out Arrays with Possibilities names and ids	
			$query = "SELECT id,name FROM possibilities WHERE bets_id = '$bet_id'";
			$result2 = mysql_db_query (DBNAME,$query)
				 or error_catcher();

			$possibilities_ids_array = array();
			$possibilities_names_array = array();
			$possibilities_quotes_array = array();

			for ($possibilities_counter = 0; $possibilities_counter < (mysql_num_rows($result2)) ; $possibilities_counter++) {
				$possibilities_ids_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "id");
				$possibilities_names_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "name");
			}

			// Read out and calculate the array with the quotes		
			$i = 0;
			foreach ($possibilities_ids_array as $possibility_id) {
				$query1 = "SELECT SUM(credits) FROM transactions WHERE possibilities_id = '$possibility_id'";
				$query2 = "SELECT SUM( credits )
					FROM transactions, possibilities
					WHERE possibilities.bets_id = '$bet_id'
					AND possibilities.id = transactions.possibilities_id";
				$result1 = mysql_db_query (DBNAME,$query1) or die ("Ungültige Abfrage: ". mysql_error());
				$result2 = mysql_db_query (DBNAME,$query2) or die ("Ungültige Abfrage: ". mysql_error());
				$credits_of_possibility = mysql_result($result1, 0);
				$credits_of_bet = mysql_result($result2, 0);
				if ($credits_of_possibility == 0){
					$dirty_quote = "-";
				}
				else{
					$quote = $credits_of_bet / $credits_of_possibility;
					$dirty_quote = round($quote, 2);
				}
				$possibilities_quotes_array[$i] = $dirty_quote;
				$i++;
			}

			// get the category_name from the category id
			$query = "SELECT name FROM categories, bets WHERE bets.categories_id = categories.id AND bets.id = '$bet_id'";
			$result1 = mysql_db_query (DBNAME,$query)  or error_catcher();
			$category_name = mysql_result($result1, 0);


			// Write the Bet objects in the return array
			$return_bet_array[$bet_counter] = new Bet($bet_id,
					mysql_result($result, $bet_counter, "title"),
					mysql_result($result, $bet_counter, "subtitle"),
					mysql_result($result, $bet_counter, "categories_id"),
					$category_name,
					mysql_result($result, $bet_counter, "image"),
					mysql_result($result, $bet_counter, "start"),
					mysql_result($result, $bet_counter, "end"),
					mysql_result($result, $bet_counter, "created"),
					mysql_result($result, $bet_counter, "autor_id"),
					$possibilities_names_array,
					$possibilities_ids_array,
					$possibilities_quotes_array);		             
		}
		return $return_bet_array;
	}

	// returns an array with all bets before now
	function getActiveAndPastBets(){

		$query = "SELECT * FROM bets WHERE start < NOW() ORDER BY created DESC";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();

		$return_bet_array = array();


		for ($bet_counter = 0; $bet_counter < (mysql_num_rows($result)) ; $bet_counter++) {

			$bet_id = mysql_result($result, $bet_counter, "id");

			// Read out Arrays with Possibilities names and ids	
			$query = "SELECT id,name FROM possibilities WHERE bets_id = '$bet_id'";
			$result2 = mysql_db_query (DBNAME,$query) or error_catcher();

			$possibilities_ids_array = array();
			$possibilities_names_array = array();
			$possibilities_quotes_array = array();

			for ($possibilities_counter = 0; $possibilities_counter < (mysql_num_rows($result2)) ; $possibilities_counter++) {
				$possibilities_ids_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "id");
				$possibilities_names_array[$possibilities_counter] = mysql_result($result2, $possibilities_counter, "name");
			}

			// Read out and calculate the array with the quotes		
			$i = 0;
			foreach ($possibilities_ids_array as $possibility_id) {
				$query1 = "SELECT SUM(credits) FROM transactions WHERE possibilities_id = '$possibility_id'";
				$query2 = "SELECT SUM( credits )
					FROM transactions, possibilities
					WHERE possibilities.bets_id = '$bet_id'
					AND possibilities.id = transactions.possibilities_id";
				$result1 = mysql_db_query (DBNAME,$query1)  or error_catcher();
				$result2 = mysql_db_query (DBNAME,$query2)  or error_catcher();
				$credits_of_possibility = mysql_result($result1, 0);
				$credits_of_bet = mysql_result($result2, 0);
				if ($credits_of_possibility == 0){
					$dirty_quote = "-";
				}
				else{
					$quote = $credits_of_bet / $credits_of_possibility;
					$dirty_quote = round($quote, 2);
				}
				$possibilities_quotes_array[$i] = $dirty_quote;
				$i++;
			}

			// get the category_name from the category id
			$query = "SELECT name FROM categories, bets WHERE bets.categories_id = categories.id AND bets.id = '$bet_id'";
			$result1 = mysql_db_query (DBNAME,$query)  or error_catcher();
			$category_name = mysql_result($result1, 0);


			// Write the Bet objects in the return array
			$return_bet_array[$bet_counter] = new Bet($bet_id,
					mysql_result($result, $bet_counter, "title"),
					mysql_result($result, $bet_counter, "subtitle"),
					mysql_result($result, $bet_counter, "categories_id"),
					$category_name,
					mysql_result($result, $bet_counter, "image"),
					mysql_result($result, $bet_counter, "start"),
					mysql_result($result, $bet_counter, "end"),
					mysql_result($result, $bet_counter, "created"),
					mysql_result($result, $bet_counter, "autor_id"),
					$possibilities_names_array,
					$possibilities_ids_array,
					$possibilities_quotes_array);		             
		}
		return $return_bet_array;
	}    



	function checkIfUserInDB($username, $password){
		$query = "SELECT username FROM user WHERE username like '$username'
			AND password like '".md5($password)."' AND (status LIKE 'active' OR status LIKE 'betmaster' OR status LIKE 'administrator' OR status LIKE 'dummy' OR status LIKE 'locked')";
		$result = mysql_query ($query)  or error_catcher();
		if (mysql_num_rows ($result) >= 1) return true;
		else return false;
	}


	function checkIfEmailInDb($email){
		$query = "SELECT email FROM user WHERE email like '$email'";
		$result = mysql_query ($query)  or error_catcher();
		if (mysql_num_rows ($result) >= 1) return true;
		else return false;
	}

	function getUserCount(){
		$query = "SELECT count(id) FROM user";
		$result = mysql_query ($query)  or error_catcher();
		if (mysql_num_rows($result) >= 1){
			return mysql_result($result, 0);
		}
	}

	function getUserConfNum($username){
		$query = "SELECT conf_num FROM user WHERE username like '$username'";
		$result = mysql_query ($query)  or error_catcher();
		if (mysql_num_rows($result) >= 1){
			return mysql_result($result, 0);
		}
	}


	function getUsersOfPossibilities($pos) {
		$query = "SELECT DISTINCT user.id, user.username, user.email, user.firstname, user.lastname, user.balance, user.status
			FROM user, transactions WHERE transactions.possibilities_id = '$pos'
			AND user.id = transactions.user_id;";
		$result = mysql_query ($query)  or error_catcher();

		$user_array = array();
		for ($user_counter = 0; $user_counter < (mysql_num_rows($result)) ; $user_counter++) {
			$user_array[$user_counter] = new User(mysql_result($result, $user_counter, "id"),
					mysql_result($result, $user_counter, "username"),
					mysql_result($result, $user_counter, "email"),
					mysql_result($result, $user_counter, "firstname"),
					mysql_result($result, $user_counter, "lastname"),
					mysql_result($result, $user_counter, "balance"),
					mysql_result($result, $user_counter, "status"));
		}
		return $user_array;
	}



	function getUser($username){
		$query = "SELECT id,username,email,firstname,lastname,balance,status
			FROM user WHERE username = '".$username."'";
		$result = mysql_query ($query)  or error_catcher();
		$rowarray = mysql_fetch_array($result);

		$user = new User($rowarray['id'],
				$rowarray['username'],
				$rowarray['email'],
				$rowarray['firstname'],
				$rowarray['lastname'],
				$rowarray['balance'],
				$rowarray['status']);
		return $user;
	}



	function getSite($id){
		$query = "SELECT *  FROM sites WHERE id = '".$id."'";
		$result = mysql_query ($query) or error_catcher();
		$rowarray = mysql_fetch_array($result);

		$site = new Site($rowarray['id'],
				$rowarray['title'],
				$rowarray['text'],
				$rowarray['author'],
				$rowarray['date']);
		return $site;
	}


	function getAllSites(){
		$query = "SELECT * FROM sites ORDER BY title ASC";
		$result = mysql_query ($query)  or error_catcher();
		$sites = array();
		for ($site_counter = 0; $site_counter < (mysql_num_rows($result)) ; $site_counter++) {
			$sites[$site_counter] = new Site(mysql_result($result, $site_counter, "id"),
					mysql_result($result, $site_counter, "title"),
					mysql_result($result, $site_counter, "text"),
					mysql_result($result, $site_counter, "author"),
					mysql_result($result, $site_counter, "date"));
		}
		return $sites;
	}

	function deleteUser($id){
		$query = "DELETE FROM user WHERE id = '".$id."'";
		$result = mysql_query ($query) or error_catcher();
	}

	function deletePossibility($id){
		$query = "DELETE FROM possibilities WHERE id = '".$id."'";
		$result = mysql_query ($query)  or error_catcher();
	}

	function deleteBet($id){
		$query = "DELETE FROM bets WHERE id = '".$id."'";
		$result = mysql_query ($query)  or error_catcher();
	}

	function deleteCategory($id){
		$bets = $this->getCategoryBets($id);
		if (count($bets) == 0){
			$query = "DELETE FROM categories WHERE id = '".$id."'";
			$result = mysql_query ($query)  or error_catcher();
			return true;
		}
		else {
			return false;
		}
	}

	function deleteSite($id){
		$query = "DELETE FROM sites WHERE id = '".$id."'";
		$result = mysql_query ($query)  or error_catcher();
	}


	function getUserById($id){
		$query = "SELECT id,username,email,firstname,lastname,balance,status
			FROM user WHERE id = '".$id."'";
		$result = mysql_query ($query)  or error_catcher();
		$rowarray = mysql_fetch_array($result);

		$user = new User($rowarray['id'],
				$rowarray['username'],
				$rowarray['email'],
				$rowarray['firstname'],
				$rowarray['lastname'],
				$rowarray['balance'],
				$rowarray['status']);
		return $user;
	}

	function getLastUserId(){
		$query = "SELECT MAX(id) FROM user";
		$result = mysql_query ($query)  or error_catcher();
		if (mysql_num_rows($result) >= 1){
			return mysql_result($result, 0);
		}
	}


	function getFirstUserId(){
		$query = "SELECT MIN(id) FROM user";
		$result = mysql_query ($query)  or error_catcher();
		if (mysql_num_rows($result) >= 1){
			return mysql_result($result, 0);
		}
	}


	function getAllUsers(){
		$query = "SELECT * FROM user ORDER BY Id ASC";
		$result = mysql_query ($query) or error_catcher();
		$user_array = array();
		for ($user_counter = 0; $user_counter < (mysql_num_rows($result)) ; $user_counter++) {
			$user_array[$user_counter] = new User(mysql_result($result, $user_counter, "id"),
					mysql_result($result, $user_counter, "username"),
					mysql_result($result, $user_counter, "email"),
					mysql_result($result, $user_counter, "firstname"),
					mysql_result($result, $user_counter, "lastname"),
					mysql_result($result, $user_counter, "balance"),
					mysql_result($result, $user_counter, "status"));
		}
		return $user_array;
	}

	function getTopUsers($nr){
		$query = "SELECT DISTINCT user.* FROM user, transactions WHERE 
			(STATUS NOT LIKE 'inactive' AND STATUS NOT LIKE 'dummy' AND STATUS NOT LIKE 'locked')
			AND transactions.user_id = user.id
			ORDER BY balance DESC
			LIMIT ".$nr."";
		$result = mysql_query ($query) or error_catcher();
		$user_array = array();
		for ($user_counter = 0; $user_counter < (mysql_num_rows($result)) ; $user_counter++) {
			$user_array[$user_counter] = new User(mysql_result($result, $user_counter, "id"),
					mysql_result($result, $user_counter, "username"),
					mysql_result($result, $user_counter, "email"),
					mysql_result($result, $user_counter, "firstname"),
					mysql_result($result, $user_counter, "lastname"),
					mysql_result($result, $user_counter, "balance"),
					mysql_result($result, $user_counter, "status"));
		}
		return $user_array;
	}

	// query doesn't work, try this:
	/*
	SELECT DISTINCT user . * 
	FROM user, transactions, possibilities
	WHERE user.id = transactions.user_id
	AND transactions.possibilities_id = possibilities.id
	AND possibilities.id =  '23'
	AND possibilities.win =  'yes'
	LIMIT 0 , 30
	*/
	function getWinningUsers($possibility_id){
		$query = "SELECT DISTINCT user.*
			FROM user, transactions, possibilities
			WHERE user.id = transactions.user_id
			AND transactions.possibilities_id = possibilities.id
			AND possibilities.id = '".$possibility_id."'
			AND possibilities.win = 'yes'";
		$result = mysql_query ($query) or error_catcher();
		$user_array = array();
		for ($user_counter = 0; $user_counter < (mysql_num_rows($result)) ; $user_counter++) {
			$user_array[$user_counter] = new User(mysql_result($result, $user_counter, "id"),
					mysql_result($result, $user_counter, "username"),
					mysql_result($result, $user_counter, "email"),
					mysql_result($result, $user_counter, "firstname"),
					mysql_result($result, $user_counter, "lastname"),
					mysql_result($result, $user_counter, "balance"),
					mysql_result($result, $user_counter, "status"));
		}
		return $user_array;
	}


	function getLoosingUsers($bet_id){
		$query = "SELECT DISTINCT user.*
			FROM user, transactions, possibilities, userwins
			WHERE user.id = transactions.user_id
			AND transactions.possibilities_id = possibilities.id
			AND possibilities.bets_id = '$bet_id'";
		$result = mysql_query ($query) or error_catcher();
		$user_array = array();
		for ($user_counter = 0; $user_counter < (mysql_num_rows($result)) ; $user_counter++) {
			$user_array[$user_counter] = new User(mysql_result($result, $user_counter, "id"),
					mysql_result($result, $user_counter, "username"),
					mysql_result($result, $user_counter, "email"),
					mysql_result($result, $user_counter, "firstname"),
					mysql_result($result, $user_counter, "lastname"),
					mysql_result($result, $user_counter, "balance"),
					mysql_result($result, $user_counter, "status"));
		}
		return $user_array;
	}

	function insertUser($username, $email, $firstname, $lastname, $password, $status, $conf_num){
		$query = "INSERT INTO user (username, password, email, firstname, lastname, status, conf_num)
			VALUES('".$username."', '".md5($password)."', '".$email."', '".$firstname."', '".$lastname."', '".$status."', '".$conf_num."')";
		$result = mysql_query ($query)  or error_catcher();
		return $result;
	}


	function insertSite($site){
		$query = "INSERT INTO sites (title, text, author, date)
			VALUES('".$site->getTitle()."', '".$site->getText()."', '".$site->getAuthor()."', NOW())";
		$result = mysql_query ($query)  or error_catcher();
		return $result;
	}

	function updateSite($site){
		$query = "UPDATE sites SET title = '".$site->getTitle()."',
			text = '".$site->getText()."', 
			author = '".$site->getAuthor()."', 
			date = NOW()
				WHERE id = '".$site->getId()."'";
		$result = mysql_query ($query)  or error_catcher();
		return $result;
	}

	function updateUser($username, $firstname, $lastname, $password){
		if ($password == ""){
			$query = "UPDATE user SET firstname = '$firstname',
				lastname = '$lastname'
					WHERE username = '$username'";	

		}
		else {
			$query = "UPDATE user SET password = '".md5($password)."',
				firstname = '$firstname',
				lastname = '$lastname'
					WHERE username = '$username'";
		}
		$result = mysql_query ($query)  or error_catcher();
		return $result;
	}

	function updateUserPwd($password, $username){
		$query = "UPDATE user SET password = '".md5($password)."' WHERE username = '$username'";
		$result = mysql_query ($query)  or error_catcher();
		return $result;
	}

	function insertCategory($category){
		$query = "SELECT MAX(id) FROM categories";
		$result = mysql_db_query (DBNAME,$query)  or error_catcher();
		$max_id = mysql_result($result, 0);
		$category->setCategoryPosition($max_id++);

		$query = "INSERT INTO categories (position, name, description, image)
			VALUES ('".$category->getCategoryPosition()."',
					'".$category->getCategoryName()."',
					'".$category->getCategoryDescription()."', 
					'".$category->getCategoryImage()."')";
		$result = mysql_query ($query)  or error_catcher();
		return $result;
	}

	function updateCategory($category){
		$query = "UPDATE categories SET position = '".$category->getCategoryPosition()."',
										name = '".$category->getCategoryName()."', 
										description = '".$category->getCategoryDescription()."',
										image = '".$category->getCategoryImage()."'
									WHERE id = '".$category->getCategoryId()."'";
		$result = mysql_query ($query)  or error_catcher();
		return $result;
	}

	function insertBetTmp($title, $subtitle, $cat_id, $image, $start, $end, $pos_array){
		$query = "INSERT INTO bets_tmp (title, subtitle, categories_id, image, start, end)
			VALUES ('".$title."', '".$subtitle."', '".$cat_id."', '".$image."', '".$start."', '".$end."')";
		$result = mysql_query ($query)  or error_catcher();
		$bet_id = mysql_insert_id();
		// insert the possibilities
		$id = mysql_insert_id();
		if ($pos_array != ""){
			foreach($pos_array as $pos){
				$query = "INSERT INTO possibilities_tmp (bets_id, name)
					VALUES ('".$id."', '".$pos."')";
				$result = mysql_query ($query)  or error_catcher();
			}
		}
		return $bet_id;
	}

	function insertBet($bet){
		$query = "INSERT INTO bets (title, subtitle, categories_id, image, start, end, created, autor_id)
			VALUES ('".$bet->getBetTitle()."', 
					'".$bet->getSubtitle()."', 
					'".$bet->getCategoryId()."', 
					'".$bet->getBetImage()."', 
					'".$bet->getBetStartTime()."', 
					'".$bet->getBetEndTime()."',
					NOW(),
					'".$bet->getBetAutorId()."')";
		$result = mysql_query ($query)  or error_catcher();
		$bet_id = mysql_insert_id();
		// insert the possibilities
		$id = mysql_insert_id();
		foreach($bet->getPossibilitiesNames() as $pos){
			$query = "INSERT INTO possibilities (bets_id, name)
				VALUES ('".$id."', '".$pos."')";
			$result = mysql_query ($query)  or error_catcher();
		}
		return $id;
	}


	function insertLog($host, $addr, $username, $event,
			$agent){
		$query = "INSERT INTO log (rem_host, rem_addr, user, event, rem_agt, time)
			VALUES ('".$host."',
					'".$addr."', 
					'".$username."', 
					'".$event."', 
					'".$agent."', 
					NOW())";
		$result = mysql_query ($query)  or error_catcher();
		return $result;
	}


	function  getBettedCredits($user_id, $possibility_id){
		$query = "SELECT SUM(credits) FROM transactions WHERE 
			possibilities_id = '$possibility_id' AND
			user_id = '$user_id'";
		$result = mysql_db_query (DBNAME,$query)  or error_catcher();
		$credits = mysql_result($result, 0);
		return $credits;
	}

	/* testquerys

	   $query = "SELECT SUM(credits) FROM transactions WHERE 
	   possibilities_id = '$possibility_id' AND
	   user_id = '$user_id'";
	   UPDATE user SET balance = (SELECT SUM(credits) FROM transactions WHERE possibilities_id = bet_id)
	 */

	function  getWonCredits($user_id, $possibility_id){
		// get quote of the possibiliity
		$query1 = "SELECT SUM(credits) FROM transactions WHERE possibilities_id = '$possibility_id'";

		$query3 = "SELECT possibilities.bets_id FROM possibilities WHERE possibilities.id = '$possibility_id'";
		$result3 = mysql_db_query (DBNAME,$query3)  or error_catcher();
		$bet_id = mysql_result($result3, 0);

		$query2 = "SELECT SUM(credits)
			FROM transactions, possibilities
			WHERE possibilities.bets_id = '$bet_id'
			AND possibilities.id = transactions.possibilities_id";
		$result1 = mysql_db_query (DBNAME,$query1) or die ("Ungültige Abfrage: ". mysql_error());
		$result2 = mysql_db_query (DBNAME,$query2) or die ("Ungültige Abfrage: ". mysql_error());
		$credits_of_possibility = mysql_result($result1, 0);
		$credits_of_bet = mysql_result($result2, 0);	
		$quote = $credits_of_bet / $credits_of_possibility;
		$quote = round($quote, 2);

		// get the the credits for the possibility
		$query = "SELECT SUM(transactions.credits)
			FROM transactions
			WHERE transactions.possibilities_id = '$possibility_id'
			AND transactions.user_id = '$user_id'";
		$result = mysql_db_query (DBNAME,$query)  or error_catcher();
		$pos_sum = mysql_result($result, 0);

		$won_credits = $pos_sum * $quote;
		return $won_credits;
	}

	function getQuoteFromPosId($possibility_id, $credits){
		// get quote of the possibiliity
		$query1 = "SELECT SUM(credits) FROM transactions WHERE possibilities_id = '".$possibility_id."'";
		$result1 = mysql_db_query (DBNAME,$query1)  or error_catcher();
		// does not work in mysql prior 4.1
		/*	$query2 = "SELECT SUM(credits)
			FROM transactions, possibilities
			WHERE possibilities.bets_id = (SELECT possibilities.bets_id FROM possibilities
			WHERE possibilities.id = '".$possibility_id."')
			AND possibilities.id = transactions.possibilities_id";             */     
		$query3 = "SELECT possibilities.bets_id FROM possibilities WHERE possibilities.id = '$possibility_id'";
		$result3 = mysql_db_query (DBNAME,$query3)  or error_catcher();
		$bet_id = mysql_result($result3, 0);

		$query2_new = "SELECT SUM(credits)
			FROM transactions, possibilities
			WHERE possibilities.bets_id = '".$bet_id."'
			AND possibilities.id = transactions.possibilities_id";  

			$result2 = mysql_db_query (DBNAME,$query2_new)  or error_catcher();

		$credits_of_possibility = mysql_result($result1, 0) + $credits;
		$credits_of_bet = mysql_result($result2, 0) + $credits;    
		if ($credits_of_possibility != 0){
			$quote = $credits_of_bet / $credits_of_possibility;
		}
		$quote = round($quote, 2);
		return $quote;
	}



	function getWonPossibility($bet_id){
		$query = "SELECT id FROM possibilities WHERE win = 'yes' AND bets_id = '$bet_id'"; 
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		if (mysql_num_rows($result) >= 1){
			return mysql_result($result, 0);
		}
	}

	function getPossibilityNameFromId($pos_id){
		$query = "SELECT name FROM possibilities WHERE id = '$pos_id'"; 
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		if (mysql_num_rows($result) >= 1){
			return mysql_result($result, 0);
		}
	}


	function getPossibilitiesIdsOfBet($bet_id){
		$query = "SELECT id FROM possibilities WHERE bets_id = '$bet_id'"; 
		$result = mysql_db_query (DBNAME,$query) or error_catcher();

		for ($pos_counter = 0; 
				$pos_counter < (mysql_num_rows($result));
				$pos_counter++) {
			$pos_array[$pos_counter] = mysql_result($result, $pos_counter, "id");
		}
		return $pos_array;
	}

	/**
	 * sets a transaction when a user ecexutes a bet
	 * 
	 * 
	// *  has nothing to do with the transaction class!
	 */
	function setTransaction($user_id, $pos_id, $credits){

		$query = "INSERT INTO transactions (possibilities_id, user_id, credits, time)".
			"VALUES ('".$pos_id."', '".$user_id."', '".$credits."', NOW());";
		$result = mysql_db_query (DBNAME,$query)  or error_catcher();

		return $result;
	}

	function setWonPossibility($id){

		$query = "UPDATE possibilities SET win = 'yes' WHERE id = '$id'";
		$result = mysql_db_query(DBNAME, $query)  or error_catcher();

		return $result;
	}


	function getAllTransactions(){

		$query = "SELECT transactions.id, bets.id, bets.title, 
			possibilities.name, transactions.possibilities_id, 
			transactions.user_id, transactions.credits, 
			transactions.time
				FROM transactions, possibilities, bets
				WHERE transactions.possibilities_id = possibilities.id
				AND possibilities.bets_id = bets.id
				ORDER BY transactions.time DESC";
		$result = mysql_db_query (DBNAME,$query)  or error_catcher();


		$return_trans_array = array();


		for ($trans_counter = 0; 
				$trans_counter < (mysql_num_rows($result));
				$trans_counter++) {
			$return_trans_array[$trans_counter] = 
				new Transaction(mysql_result($result, $trans_counter, "transactions.id"),
						mysql_result($result, $trans_counter, "bets.id"),
						mysql_result($result, $trans_counter, "title"),
						mysql_result($result, $trans_counter, "name"),
						mysql_result($result, $trans_counter, "transactions.possibilities_id"),
						mysql_result($result, $trans_counter, "user_id"),
						mysql_result($result, $trans_counter, "credits"),
						mysql_result($result, $trans_counter, "time"));
		}
		return $return_trans_array;
	}


	function getTransactions($user_id){

		$query = "SELECT transactions.id, bets.id, bets.title, possibilities.name, transactions.possibilities_id, transactions.credits, transactions.time
			FROM transactions, possibilities, bets
			WHERE user_id = '$user_id'
			AND transactions.possibilities_id = possibilities.id
			AND possibilities.bets_id = bets.id
			ORDER BY transactions.time DESC";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();


		$return_trans_array = array();

		for ($trans_counter = 0; $trans_counter < (mysql_num_rows($result)) ; $trans_counter++) {
			$return_trans_array[$trans_counter] = new Transaction(mysql_result($result, $trans_counter, "transactions.id"),
					mysql_result($result, $trans_counter, "bets.id"),
					mysql_result($result, $trans_counter, "title"),
					mysql_result($result, $trans_counter, "name"),
					mysql_result($result, $trans_counter, "transactions.possibilities_id"),
					$user_id,
					mysql_result($result, $trans_counter, "credits"),
					mysql_result($result, $trans_counter, "time"));
		}
		return $return_trans_array;
	}


	function getWins($user_id){

		$query = "SELECT bets.title,  possibilities.name,  userwins.won_credits,  userwins.quote
			FROM userwins, bets,  possibilities
			WHERE userwins.user_id = '$user_id'
			AND userwins.possibilities_id = possibilities.id
			AND  possibilities.bets_id = bets.id";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$wins_array = array();

		for ($wins_counter = 0; $wins_counter < (mysql_num_rows($result)) ; $wins_counter++) {
			$wins_array[$wins_counter] = new Win("",
					mysql_result($result, $wins_counter, "title"),
					mysql_result($result, $wins_counter, "name"),
					mysql_result($result, $wins_counter, "quote"),
					mysql_result($result, $wins_counter, "won_credits"));
		}
		return $wins_array;
	}


	// used during ececute a bet
	function decBalance($user_id, $credits){
		$query = "UPDATE user SET balance = (balance - '$credits') WHERE id = '$user_id'";
		$result = mysql_db_query (DBNAME,$query)  or error_catcher();
		return $result;
	}

	// used when updating a balance manual
	function setBalance($user_id, $credits){
		$query = "UPDATE user SET balance = '$credits' WHERE id = '$user_id'";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		return $result;
	}

	function addBalance($user_id, $credits){
		$query = "UPDATE user SET balance = balance + '$credits' WHERE id = '$user_id'";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		return $result;	
	}

	function setStatus($user_id, $status){
		$query = "UPDATE user SET status = '$status' WHERE id = '$user_id'";
		$result = mysql_db_query (DBNAME,$query)  or error_catcher();
		return $result;
	}

	function setEmail($username, $email){
		$query = "UPDATE user SET email = '$email' WHERE username = '$username'";
		$result = mysql_db_query (DBNAME,$query)  or error_catcher();
		return $result;
	}

	function getUserBalance($user_id){
		$query = "SELECT balance  FROM user WHERE id = '$user_id'";
		$result = mysql_query ($query) or error_catcher();
		$rowarray = mysql_fetch_array($result);
		$balance = $rowarray['balance'];
		return $balance;
	}


	/**
	 * retuns true if the user has not betted on another
	 * possibility before
	 * 
	 */

	function checkBettedPossibility($user_id, $pos){

		// extract bet_id and possibility_id from $pos	
		$id_array = explode("#",$pos);
		$bet_id = $id_array[0];
		$possibility_id = $id_array[1];

		$query = "SELECT possibilities.id
			FROM possibilities, transactions
			WHERE possibilities.bets_id = '$bet_id'
			AND transactions.user_id = '$user_id'
			AND transactions.possibilities_id = possibilities.id";

		$result = mysql_db_query (DBNAME,$query)  or error_catcher();

		$rowarray = mysql_fetch_array($result);

		$allowed_possibility_id = $rowarray['id'];

		if (($allowed_possibility_id == $possibility_id) || empty($allowed_possibility_id))
			return true;
		else
			return false;
	}


	function getAllCategories(){
		$query = "SELECT * FROM categories ORDER BY position, id asc";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$rowarray = mysql_fetch_array($result);

		$cat_array = array();
		for ($cat_counter = 0; $cat_counter < (mysql_num_rows($result)) ; $cat_counter++) {
			$cat_array[$cat_counter] = new Category(mysql_result($result, $cat_counter, "id"),
					mysql_result($result, $cat_counter, "position"),
					mysql_result($result, $cat_counter, "name"),
					mysql_result($result, $cat_counter, "description"),
					mysql_result($result, $cat_counter, "image"));
		}
		return $cat_array;
	}

	function getCategory($id){
		$query = "SELECT * FROM categories WHERE id = $id";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$rowarray = mysql_fetch_array($result);

		$category = new Category($rowarray['id'],
				$rowarray['position'],
				$rowarray['name'],
				$rowarray['description'],
				$rowarray['image']);
		return $category;
	}


	function shiftCategoryUp($id){

		// get pos from category with $id
		$query = "SELECT position FROM categories WHERE id = $id";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$position = mysql_result($result,0);
		
		// get previous position:
		$query = "SELECT max(position) FROM categories WHERE position < $position";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$prev_position = mysql_result($result,0);

		// get max position:
		$query = "SELECT min(position) FROM categories";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$max_position = mysql_result($result,0);

		if ($position != $max_position){

		// set previous to actual
		$query = "UPDATE categories SET position = $position WHERE position = $prev_position";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
	
		// set actual to previous
		$query = "UPDATE categories SET position = $prev_position WHERE id = $id";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		}
	}
	
	function shiftCategoryDown($id){

		// get pos from category with $id
		$query = "SELECT position FROM categories WHERE id = $id";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$position = mysql_result($result,0);
		
		// get next position:
		$query = "SELECT min(position) FROM categories WHERE position > $position";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$next_position = mysql_result($result,0);

		// get min position:
		$query = "SELECT max(position) FROM categories";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		$min_position = mysql_result($result,0);

		if (($position != $min_position)){
		// set next to actual
		$query = "UPDATE categories SET position = $position WHERE position = $next_position";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
	
		// set actual to next
		$query = "UPDATE categories SET position = $next_position WHERE id = $id";
		$result = mysql_db_query (DBNAME,$query) or error_catcher();
		}
	}
}
?>
