<?php
/*
 * Created on 15.12.2005
 *
 * File containing the view structure. 
 * 
 * At definition-time the GET-Array is handed-over.
 * By reading the keys, the constructor dicides what to do, and calls the filing-functions of the connect-class.
 * 
 * The template calls two other times the view class. To write comments to the beginning and to write a result table.
 * 
 */
 require_once("conn.php");
 session_start();
 class View{
 	var $conn;
 	var $searchString="";
 	var $BEGINNING=0, $SEARCH=1,$TOURS=2,$ADVANCED_SEARCH=3, $JTEMP=4;  // the different functionalities to reference by number later on
 	var $functionNum;
 	var $tours = array();
 	var $result = array();
 	var $numResults=0;
 	var $numSearchFields=0;
 	var $tourIndex;
 	
 	function View($searchEntry){
 		$this->searchString="";
 		$this->conn = new Connection($this);
 		// invoke the function for the GET-variable given
 		if(array_key_exists('quicksearch', $searchEntry)){
 			$this->functionNum=$this->SEARCH;
 			$this->searchString = $searchEntry['quicksearch'];
 			// search and put out
			$this->numResults = $this->conn->fillResultArray($this->searchString);
			if($this->numResults==1)$this->searchString="result";
 		}else if(array_key_exists('tours', $searchEntry)){
 			$this->functionNum=$this->TOURS;
 			$this->searchString = $searchEntry['tours'];
			if($this->searchString=="overview")$this->numResults = $this->conn->getTourList();
			else $this->numResults = $this->conn->getTourByName($this->searchString);
 		}else if(array_key_exists('advSearch', $searchEntry)){
 			$this->functionNum=$this->ADVANCED_SEARCH;
 			$this->searchString = $searchEntry['advSearch'];
 			if(array_key_exists('increaseFields', $_GET)){
 				$numRequest = $searchEntry['increaseFields'];
 				$this->searchString="overview";
 				if($numRequest<5)$this->numSearchFields=$numRequest;
 				else $this->numSearchFields=$numRequest-1;
 			}
 			else if(array_key_exists('decreaseFields', $searchEntry)){
 				$this->numSearchFields=$searchEntry['decreaseFields'];
 				$this->searchString="overview";
 			}
 			//startButton pressed -> change to result output.
 				if($this->searchString=="overview_searchresult"){
 				$this->numResults = $this->conn->getTourByRooms($searchEntry);
 			}
 			
 			// running Tour
 			else if($this->searchString=="result"){ 
 				if(!array_key_exists('tourIndex', $searchEntry)){
 					$this->tourIndex=0;
  				}else $this->tourIndex=$searchEntry['tourIndex'];
 				if($this->tourIndex<count($_SESSION['rooms'])){
					// quicksearch function
	 				$this->numResults = 1;
					$this->result[0]=$_SESSION['rooms'][$this->tourIndex];
 				}
 				else $this->functionNum=$this->BEGINNING;
 				
  			}
 		}else if(array_key_exists('javatemplate', $searchEntry)){
 			$this->functionNum=$this->JTEMP;
 		}else $this->functionNum=$this->BEGINNING;
 	}
 
 	function printCommentsForJava(){
		if($this->searchString=="result" && $this->functionNum!=$this->BEGINNING && isset($this->result[0])){
			// Is this a tour?
			$javaFunctionString = "";
			if($this->functionNum==$this->ADVANCED_SEARCH || $this->functionNum==$this->TOURS
				&& $this->tourIndex+1<count($_SESSION['rooms']))$javaFunctionString = "Tour";

			printf("<p> Coords %s %s %s </p>", $this->result[0]->number, $this->result[0]->parameters, $javaFunctionString);
		}else print "<p> noCoords  </p>";
 	}
 	
 
 	function printContentTableRows(){
		switch($this->functionNum){
			case $this->SEARCH:
			case $this->TOURS:
				$this->printResultRows();
				break;
			// is a template
			case $this->ADVANCED_SEARCH:
				if($this->searchString=="overview"){
					$this->printSearchTemplate();
				}else $this->printResultRows();		
				break;
			case $this->JTEMP:
				$this->printBuildingTemplate();		
				break;
			default: //".$this->printNaviTableRows()."
	 			print "
				  <tr>
				    <td border=\"0\">
					<table cellspacing=\"0\" border='0'>
				        <tr>
				          <td><a href=\"index.php?tours=overview&".SID."\"><img src=\"_pics/fuehrung.gif\" border='0'></a></td>
				          <td rowspan='3' style=\"background:url('_pics/littlerect.gif') repeat-y;\" width=\"5\"></td>
		    			  <td valign=\"top\"><a href=\"index.php?tours=overview&".SID."\">Ich m&ouml;chte mich f&uuml;hren lassen...</a> </td>
				          <td>&nbsp;</td>
				        </tr>
				        <tr>
				          <td><a href=\"index.php?advSearch=overview&".SID."\"><img src=\"_pics/ort.gif\" border='0'></a></td>
				          <td valign=\"top\"><a href=\"index.php?advSearch=overview&".SID."\">Ich m&ouml;chte nach dort/ zu dem...</a> </td>
				          <td>&nbsp;</td>
				        </tr>
				        <tr>
				          <td valign='top'><img src=\"_pics/direkt.gif\"></a></td>
				          <td valign=\"top\"><FORM name=\"form1\" method=\"GET\"> Raum und Personensuche:<br>
				            <input type=\"text\" name=\"quicksearch\" size=\"15\" maxlength=\"15\"><br>
				            <input type=\"submit\" name=\"Submit\" value=\"suchen\">
							<input type=\"hidden\" name='PHPSESSID' value='".session_id()."'>					
				          </form></td>
				          <td>&nbsp;</td>
				        </tr>
				      </table></td>
				  </tr>
				"; 			
	 		}
	 	}
	 	
	 function printSearchTemplate(){
					print "
					  <tr>
					    <td valign='top' border='0' rowspan='3'>";
					$this->printNaviTableRows();
					print "</td>
						<td style=\"background:url('_pics/littlerect.gif') repeat-y;\" width=\"5\"></td>
						    <td valign='top'>
							<table border='0' cellspacing='5'>
								<tr>
						          <td width='20'>&nbsp;</td>
						          <td><b>Advanced Search</b><br>Ich möchte...</td>
						          <td>&nbsp;</td>
						        </tr>
						        <tr>
						          <td>&nbsp;</td>
						          <td>
						    		<form name='valueHolder' method='GET'>
						    		<input type='hidden' name='advSearch' value='overview_searchresult'>
						          	von:<br>
						            <input type='text' name='advSearchValue0' size='20' maxlength='15' value='".(isset($_GET['advSearchValue0'])?$_GET['advSearchValue0']:"")."'>";
					if($this->numSearchFields>0)print "<br>&uuml;ber:";  	
					$adFields=0;
					for(;$adFields<$this->numSearchFields;$adFields++){
						$arrayKey = "advSearchValue".($adFields+1);
						print "
						          <br>".($adFields+1)."
						          <input type='text' name='advSearchValue".($adFields+1)."' size='20' maxlength='15' value='".(isset($_GET[$arrayKey])?$_GET[$arrayKey]:"")."'>
								" ;
					}
					$arrayKey = "advSearchValue".($adFields+1);
					print " 
					            <br>nach:<br>
					            <input type='text' name='advSearchValue".($adFields+1)."' size='20' maxlength='15' value='".(isset($_GET[$arrayKey])?$_GET[$arrayKey]:"")."'><br>
					            <input type='submit' name='suchbutton' value='suchen'>
								</form>
							</td><td> ";
					if($this->numSearchFields<4)print "
						            optional:<form name='increaseForm'>".
						              "<input type='hidden' name='advSearch' value='overview_searchresult'>".
							          "<input type='hidden' name='increaseFields' value='".($this->numSearchFields+1)."'>".
							          "<input type='submit' name='someButton' value='eine Station mehr'>".
						          "</form>";
					for($adFields=0;$adFields<$this->numSearchFields;$adFields++)
						print "<br>";
					if($adFields>0)print "
								<form name='decreaseForm'>
						            <input type='hidden' name='advSearch' value='overview_searchresult'>
							        <input type='hidden' name='decreaseFields' value='".($this->numSearchFields-1)."'>
							        <input type='submit' name='someButton' value='eine Station weniger'>
							       </form>
							";
					print "
						          </td>
						        </tr>
						      </table></td>
						  </tr>
						"; 	
	 }
	 	
	function printNaviTableRows(){
		print "
			<!-- navi table //--> 
					<table width='40' border='0' cellpadding='0' cellspacing='3'>
				      <tr><td width='70'><a href='index.php?".SID."'><img src=\"_pics/home.png\" border='0'></a></td><tr>
				      <tr><td width='70'><a href='index.php?tours=overview&".SID."'><img src=\"_pics/fuehrung.gif\" border='0'></a></td></tr>
				      <tr><td width='70'><a href='index.php?advSearch=overview&".SID."'><img src=\"_pics/ort.gif\" border='0''></a></td></tr>
				      <tr><td width='70' style='background:url(_pics/menuCellBorder.gif) no-repeat;' align='center' valign='top'>
				      <img src='_pics/spacer.gif' height='5'><form name='form1' method='GET'>
				            <input type='text' name='quicksearch' value='".(isset($_GET['quicksearch'])?$_GET['quicksearch']:"Raum-Etage.Geb&auml;ude")."' size='5' maxlength='15'><br>
				            <input type='submit' value='OK'>
							<input type=\"hidden\" name='PHPSESSID' value='".session_id()."'>					
				      </form></td></tr>
				    </table>
				    <!-- end navi table //-->
		";
	}
	
	function printBuildingTemplate(){
 		print " 
		  <tr>
		    <td valign='top'>";
		$this->printNaviTableRows();
		print "</td>
				<td style=\"background:url('_pics/littlerect.gif') repeat-y;\" width=\"5\"></td>
				<td valign='top' width='100%'>";
		$this->printWeiterButton();
		print "		<p align='center' class='ueberschrift'>
 					".$_GET['headline']."</b><hr width='100%' color='#8E6B72'></p>
			 		<p>".$_GET['content']."</p>
				</td>
			</tr>";
	}
	
	function printWeiterButton(){
		print "		<form name='tourForm' method='GET'>
						<input type='submit' value='Detailmodus beenden.'>
						<input type=\"hidden\" name='PHPSESSID' value='".session_id()."'>					
					</form>";
	}
			
	function printResultRows(){
	 	print "
		  <tr>
		    <td valign='top' rowspan='2'>";
		$this->printNaviTableRows();
		print "
			</td>
			<td style=\"background:url('_pics/littlerect.gif') repeat-y;\" width=\"5\"></td>";
			if($this->searchString=="result"){
				print "<td colspan=\"2\">
				<table><tr><td>";
				if($this->functionNum==$this->ADVANCED_SEARCH || $this->functionNum==$this->TOURS){
					if($this->tourIndex+1<count($_SESSION['rooms']))
						print 
							"<form name='continueForm' method='GET'>
								<input type='submit' value='zum n&auml;chsten Raum'>
								<input type='hidden' name='tourIndex' value='".($this->tourIndex+1)."'>
								<input type='hidden' name='advSearch' value='result'>
								<input type=\"hidden\" name='PHPSESSID' value='".session_id()."'>					
							</form></td><td>";
					print "
							<form name='finishForm' method='GET'>
								<input type='submit' value='Tour beenden'>
								<input type='hidden' name='tourIndex' value='".(count($_SESSION['rooms']))."'>
								<input type=\"hidden\" name='PHPSESSID' value='".session_id()."'>					
							</form>
						</td></tr>";
				 }else {
				 	$this->printWeiterButton();
				}
			 	print	"</td></tr><tr>";
			}
		print "<td valign='top' align='left'>";
		$this->printResult();
		print "</td>
		  </tr>
		<!-- end resultRow //-->";
	}
 
	
 	function printResult(){
		if(  $this->numResults == 1)$this->printSingleResult();
		else if($this->numResults > 1) $this->printListResult();
		else print "Es wurde f&uuml;r ihre Eingabe kein Ergebnis gefunden.<br> " .
				"Bitte überpr&uuml;fen sie trotzdem noch einmal ihre Eingabe auf Richtigkeit.<br>" .
				"R&auml;ume sollten im Format &quot;Geb&auml;ude-Etage.Raum&quot; angegeben werden (z.B. 1-1.01).<br>" .
				"Geben sie bei Personen oder Raumnamen nur ein Wort oder ein Teil des Namens ein.";
 	}
 	
 	function printSingleResult(){
 		$this->result[0]->printAll();
 	}
 	
 	function printListResult(){
 		print "<table border='0' width='490'>";
 		for($i=0;$i<count($this->result);$i++){
 			// Post-variable creation
 			$postVariable = ($this->functionNum!=$this->TOURS?"quicksearch=".$this->result[$i]->number:"tours=".$this->result[$i]->parameters);
	 		print "<tr><td><a href='index.php?".$postVariable."&".SID."'>";
 			$this->result[$i]->printListVersion($i);
 			print "<img border='0' src='../res/arrow.gif'></a></td></tr>";
  		}
 		print "</table>";
 	}
 }
?>
