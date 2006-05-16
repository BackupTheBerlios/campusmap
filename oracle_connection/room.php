<?php
/*
 * Created on 07.01.2006
 *
 * File holding room-informations
 */
 class Room{
 	var $parameters;
 	var $name;
 	var $number;
 	var $person;
 	var $personPic;
 	var $aboutString;
 	var $picFolder="_pics/";
 	
 	function Room(
 	 	$parameters,
	 	$name,
	 	$number,
	 	$person,
	 	$personPic,
	 	$aboutString
	 	){
 	 	$this->parameters = $parameters;
	 	$this->name = $name;
	 	$this->number = $number;
	 	$this->person = $person;
	 	$this->personPic = $personPic;
	 	$this->aboutString = $aboutString;
 	}
 	
 	function printAll(){
 		print 
 		"<table border='0' width='90%'>
 		<tr><td><p align='center' class='ueberschrift'>$this->name
 		<hr width='100%' color='#8E6B72'></p>
 		<p><font size='2' color='#8E6B72'>Raumnummer: <b>$this->number</b>
 		<br>zuständige Person: <b>$this->person</b></font></p>
		<p><b>Beschreibung:</b> $this->aboutString</p>
		</td></tr></table>";
 	}
 	
 	function printListVersion($num){
		print "
 		<font color='#00000'><b>$this->name</b>
 		<font size='2'>Raumnummer: $this->number<br />zuständige Person: $this->person</font></font>";
 	}
 }
?>
