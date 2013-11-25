<?php
/* Mailer.class.php - BetSter project (22.05.06)
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
require("class/phpmailer.class.php");
//require_once("class/DbMapper.class.php");



class Mailer extends PHPMailer {

	//var $mail = new PHPMailer();
	
	function Mailer(){
	}
	
	function send_email($subject,$message,$receivers){


	$this->From     = CONTACT_MAIL;
	$this->FromName = SENDER;
	$this->Host     = MAIL_HOST;
	$this->Mailer   = "smtp";

	$this->IsSMTP();
	$this->SMTPAuth = true;
	$this->Username = SMTP_USERNAME;
	$this->Password = SMTP_PASSWORD;

	$this->Body    = $message.SIGNATURE;
	$this->Subject = $subject;
	$this->AltBody = $text_body;
	$this->AddAddress(CONTACT_MAIL, "");

	$receivers_array = split(',',$receivers);
	foreach($receivers_array as $receiver){
		$this->AddBCC(trim($receiver));
	}

	if(!$this->Send()) {
		return false;
	}
	else return true;

	// Clear all addresses and attachments for next loop
	$this->ClearAddresses();
	$this->ClearAttachments();
}
	
}
?>
