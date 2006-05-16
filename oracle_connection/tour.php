<?php

class Tour{
	
 	var $name;
 	var $parameters;
 	var $rooms = array();
 	var $additionalTexts = array();
 	var $tourPic;
 	var $aboutString;
 	var $picFolder="_pics/";
 	var $isDefinite=true;
 	
 	function Tour(
	 	$name,
	 	$rooms,
	 	$parameters,
	 	$tourPic,
	 	$aboutString
	 	){
	 	$this->name = $name;
	 	$this->rooms = $rooms;
	 	$this->parameters = $parameters;
	 	$this->tourPic = $tourPic;
	 	$this->aboutString = $aboutString;
	 	
 	}
 	
 	function collectCoords(){
 		
 	}
 	
 	/***
 	 *  Prints explication for this tour. Lists various results if exist.
 	 */

 	function printAll(){
	 		print 
	 		"<form name='roomForm'>
		 				<font color='#00000'><p align='center'><b>$this->name</b></font></p>
		 				<p>$this->aboutString</p>
						<input type='submit' name='startbutton' value='Start Tour'>
		 				<p>";
			if(is_array($this->rooms)){
				print "<hr width='100%' color='#8E6B72'>";
				for($roomCount=0;$roomCount<count($this->rooms);$roomCount++){
					print "<b>Ergebnis f&uuml;r Station ".($roomCount+1)." von ".count($this->rooms).":</b>";

/****************************************************************************
 * various Searchresults in ONE of the room searches
 */
			 		if(is_array($this->rooms[$roomCount])){
						 		print "<table border='0' width='470'>";
						 		for($roomCount2=0;$roomCount2<count($this->rooms[$roomCount]);$roomCount2++){
									print "<tr><td><input type='radio' name='advSearchValue".$roomCount."' value='".$this->rooms[$roomCount][$roomCount2]->number."' ".($roomCount2==0?"checked":"")."></td><td>";
									$this->rooms[$roomCount][$roomCount2]->printListVersion($roomCount2);
			 						print "</td></tr>";
								}
								print "</table>";
								$this->isDefinite=false;
						
/****************************************************************************
 * only one Searchresult in ALL of the room searches
 */
					}else {
						print "<table border='0' width='470'>";
						$this->rooms[$roomCount]->printListVersion($roomCount); 
						print "<input type='hidden' name='advSearchValue".$roomCount."' value='".$this->rooms[$roomCount]->number."'>";
						print "</table>";
			 		}
			 		print "<hr width='100%' color='#8E6B72'>";
				}
			} else print "Error: no array".$this->rooms;
	 		if($this->isDefinite)print "<input type='hidden' name='advSearch' value='result'>";
	 		else print "<input type='hidden' name='advSearch' value='overview_searchresult'>";
			print		"</p>
			<input type=\"hidden\" name='PHPSESSID' value='".session_id()."'>					
			</form>";

 	}
 	

 	function printListVersion($num){
		print " 
 		<font color='#00000' font-weight='bold'><b>$this->name</b></font>
 		<font size='2'><br />".substr($this->aboutString,0,60)."...</font>";
 	}
}
?>