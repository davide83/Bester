<?php
/* functions.inc.php - BetSter project (22.05.06)
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



function getTemplatePart($comment, $template){
	$begin_comment = "<!-- #".$comment." -->";				
	$end_comment = "<!-- ".$comment."# -->";		

	$begin_template = strstr($template, $begin_comment);		
	$begin_template = substr($begin_template, strlen($begin_comment));
	$end_template = strstr($begin_template, $end_comment);
	$templatePart = str_replace($end_template,"",$begin_template);

	return $templatePart;
}


function replace($comment, $value, $template){
	$begin_comment = "<!-- #".$comment." -->";				
	$end_comment = "<!-- ".$comment."# -->";

	$begin_template = strstr($template, $begin_comment);			
	$end_template = strstr($begin_template, $end_comment);
	$templatePart = str_replace($end_template,"",$begin_template);
	$templatePart .= $end_comment;

	return str_replace($templatePart,$value,$template);				
}


function file_get_content($datei){
	$file = file($datei);
	$content = "";
	if ($file){
		foreach($file as $value){
			$content .= $value;	
		}
	}
	return $content;
}

function checkEmail($email){
	if(ereg ("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$",$email))
		return true;
	else 
		return false;
}


function replaceNewLines($text){
	$newtext = "";
	for ($i = 0; $i < strlen($text); $i++){
		if ($text[$i] == "\n")
			$newtext .= "<br />";
		else
			$newtext .= $text[$i];		

	}
	return $newtext;
}

// can you round it? yes we can!
function roundBorder($images_dir, $image_file, $corner_radius){
	$angle = 0; // The default angle is set to 0º
	$topleft = true; // Top-left rounded corner is shown by default
	$bottomleft = true; // Bottom-left rounded corner is shown by default
	$bottomrigbt = true; // Bottom-right rounded corner is shown by default
	$topright = true; // Top-right rounded corner is shown by default

	$corner_source = imagecreatefrompng('images/rounded_corner.png');

	$corner_width = imagesx($corner_source);  
	$corner_height = imagesy($corner_source);  
	$corner_resized = ImageCreateTrueColor($corner_radius, $corner_radius);
	ImageCopyResampled($corner_resized, $corner_source, 0, 0, 0, 0, $corner_radius, $corner_radius, $corner_width, $corner_height);

	$corner_width = imagesx($corner_resized);  
	$corner_height = imagesy($corner_resized);  
	$image = imagecreatetruecolor($corner_width, $corner_height);  
	$image = imagecreatefromjpeg($images_dir . $image_file); // replace filename with $_GET['src'] 
	$size = getimagesize($images_dir . $image_file); // replace filename with $_GET['src'] 
	$white = ImageColorAllocate($image,255,255,255);
	$black = ImageColorAllocate($image,0,0,0);

	// Top-left corner
	if ($topleft == true) {
		$dest_x = 0;  
		$dest_y = 0;  
		imagecolortransparent($corner_resized, $black); 
		imagecopymerge($image, $corner_resized, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);
	} 

	// Bottom-left corner
	if ($bottomleft == true) {
		$dest_x = 0;  
		$dest_y = $size[1] - $corner_height; 
		$rotated = imagerotate($corner_resized, 90, 0);
		imagecolortransparent($rotated, $black); 
		imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);  
	}

	// Bottom-right corner
	if ($bottomrigbt == true) {
		$dest_x = $size[0] - $corner_width;  
		$dest_y = $size[1] - $corner_height;  
		$rotated = imagerotate($corner_resized, 180, 0);
		imagecolortransparent($rotated, $black); 
		imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);  
	}

	// Top-right corner
	if ($topright == true) {
		$dest_x = $size[0] - $corner_width;  
		$dest_y = 0;  
		$rotated = imagerotate($corner_resized, 270, 0);
		imagecolortransparent($rotated, $black); 
		imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);  
	}

	// Rotate image
	$image = imagerotate($image, $angle, $white);

	imagejpeg($image, $images_dir.$image_file);
	imagedestroy($image);  
	imagedestroy($corner_source);
}


// a Width of 100px for each Image and a round corner
function resizeAndSaveImage($dir, $filename, $new_filename) {
	$size_array = getimagesize("$dir/$filename");
	$width = $size_array[0];
	$height = $size_array[1];

	$new_width = 100;
	$new_height = 100*($height/$width);

	$image_old_width = imagecreatefromjpeg("$dir/$filename");    // source
	$image_new_width = imagecreatetruecolor($new_width,$new_height);
	imagecopyresampled($image_new_width,$image_old_width,0,0,0,0,$new_width,$new_height,$width,$height);
	imagejpeg($image_new_width,"$dir/$new_filename");   
	roundBorder($dir, $new_filename, 10);
	imagedestroy($image_old_width);  
	unlink("$dir/$filename");    
}




function error_catcher(){
	header("Location:error.php");
	exit();
}

?>
