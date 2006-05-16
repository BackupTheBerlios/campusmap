<?php
/*
 * Class to manage the "Connection" to data. Either in database or file. 
 */
 
		
require_once("room.php");
require_once("tour.php");
 
class Connection
{
	//Einstellung der Oracle Datenkbank
	var $ORACLE_BENUTZERNAME	= "HR";
	var $ORACLE_PASSWORT		= "hr";
	
	var $conn;
	var $theTable;
	var $theView;
	var $result = array();

	var $roomNumberColumn;
	var $coordColumnX;
	var $coordColumnY;
	var $personNameColumn;
	var $personPic;
	var $roomNamesColumn;
	var $aboutStringColumn;
	var $objectType;

	// XML input
	var $getDetails = false;
	var $searchString;
	var $found;
	var	$file = "tours.xml";
	var	$depth = array();
	var $other=0, $descr=1, $roomNumber=2, $addText=3;
	var $actTag;
	var $actRoomNumber;
	var $actAddText;
	var $actTourName;
	var $actTourId;
	var $actTourDescr;
	var	$tourIndex;
	var	$roomIndex=0;
	
	/**
	 * Constructor
	 */
	function Connection(&$theView){
		$this->theView = &$theView;
		$this->coordColumnX="STRA_SX";                                 // names of the correspondent database-fields
		$this->coordColumnY="STRA_SY";
		$this->roomNumberColumn="OBJ_NUM";
		$this->personTitelColumn="STPE_TITEL";
		$this->personNameColumn="STPE_VORNAME";
		$this->personSurnameColumn="STPE_NAME";
		$this->roomNamesColumn="OBJ_BEZ";
		$this->roomAlternNamesColumn="OBJATTTEX_CVAL";
		$this->aboutStringColumn="STRA_RAUNUM1";
		$this->objectType="OBJTYP_ID";
		$this->personPic="";
		$this->connect();
	}
	
	/**************************************
	 * Database related
	 */
	
	/**
	 * Connection
	 */
	function connect(){
		$this->conn = OCILogon($this->ORACLE_BENUTZERNAME, $this->ORACLE_PASSWORT);
	}
	
	/**
	 *  get a searchresult from the String by searching in room-number fields.
	 * 	Result is saved in the view class.
	 */
	function getXYZByName($searchParam){	
		$numResults = 0;
		$sql = "SELECT DISTINCT ".
				$this->coordColumnX."," .
				$this->coordColumnY."," .
				$this->roomNamesColumn."," .
				$this->roomNumberColumn."," .
				$this->personTitelColumn."," .
				$this->personNameColumn."," .
				$this->roomAlternNamesColumn."," .
				$this->personSurnameColumn."," .
				$this->aboutStringColumn.
				" FROM ((SELECT * FROM fm_obj WHERE ".$this->objectType." = 8 OR ".$this->objectType." = 10) o " .
				"JOIN fm_stra a USING (OBJ_ID)) " .
				"LEFT OUTER JOIN fm_stpe p USING (OBJ_ID) " .
				"LEFT OUTER JOIN fm_objatttex t USING (OBJ_ID) " . 
				"WHERE (".
				"INSTR(LOWER(".$this->roomNamesColumn."), LOWER('".$searchParam."'))!=0 OR ".
				"INSTR(LOWER(".$this->personTitelColumn."), LOWER('".$searchParam."'))!=0 OR ".
				"INSTR(LOWER(".$this->personNameColumn."), LOWER('".$searchParam."'))!=0 OR ".
				"INSTR(LOWER(".$this->personSurnameColumn."), LOWER('".$searchParam."'))!=0 OR ".
				"INSTR(LOWER(".$this->roomAlternNamesColumn."), LOWER('".$searchParam."'))!=0 OR ".
				"INSTR(LOWER(".$this->aboutStringColumn."), LOWER('".$searchParam."'))!=0)";
				
		//print $sql;		
		$stmt = ociparse($this->conn, $sql);
		ociexecute($stmt);
		while ( OCIFetchInto($stmt, $row, OCI_ASSOC) ) {
			$numResults++;
			//print_r($row);
			array_push($this->theView->result, new Room(
											$row[$this->coordColumnX]." ".$row[$this->coordColumnY],
											$row[$this->roomNamesColumn],
											$row[$this->roomNumberColumn],
											(isset($row[$this->personTitelColumn])&&
											 isset($row[$this->personNameColumn])&&
											 isset($row[$this->personSurnameColumn])?$row[$this->personTitelColumn]." ".$row[$this->personNameColumn]." ".$row[$this->personSurnameColumn]:null),
											"beispiel.jpg",
											(isset($row[$this->aboutStringColumn])?$row[$this->aboutStringColumn]:null)
											));
		}
		//print count($this->theView->rooms);
		return $numResults;
	}

	/**
	 *  get a searchresult from the String by searching in all name fields.
	 * 	Result is saved in the view class.
	 * 
	 *  Concept: look in the table fm_obj for the number. if it is found look for further information
	 *  Concatenate ALL fields of fm_obj in where the number is found with all lines in the two tables 
	 *  where the ID is equal
	 * 
	 */
	function getXYZByRoomNumber($searchParam){	
		$numResults = 0;
		$sql = "SELECT DISTINCT ".
				$this->coordColumnX."," .
				$this->coordColumnY."," .
				$this->roomNamesColumn."," .
				$this->roomNumberColumn."," .
				$this->personTitelColumn."," .
				$this->personNameColumn."," .
				$this->personSurnameColumn."," .
				$this->aboutStringColumn.
				" FROM (" .
				"(SELECT DISTINCT * FROM fm_obj WHERE LOWER(".$this->roomNumberColumn.") = LOWER('".$searchParam."')) o " .
				"INNER JOIN fm_stra a USING (OBJ_ID)) " .
				"LEFT OUTER JOIN fm_stpe p USING (OBJ_ID) ";
		//print $sql;		
		$stmt = ociparse($this->conn, $sql);
		ociexecute($stmt);
		while ( OCIFetchInto($stmt, $row, OCI_ASSOC) ) {
			$numResults++;
			//print_r($row);
			array_push($this->theView->result, new Room(
											(isset($row[$this->coordColumnX])&&isset($row[$this->coordColumnY])?$row[$this->coordColumnX]." ".$row[$this->coordColumnY]:null),
											(isset($row[$this->roomNamesColumn])?$row[$this->roomNamesColumn]:null),
											(isset($row[$this->roomNumberColumn])?$row[$this->roomNumberColumn]:null),
											(isset($row[$this->personTitelColumn])&&
											 isset($row[$this->personNameColumn])&&
											 isset($row[$this->personSurnameColumn])?$row[$this->personTitelColumn]." ".$row[$this->personNameColumn]." ".$row[$this->personSurnameColumn]:null),
											"beispiel.jpg",
											(isset($row[$this->aboutStringColumn])?$row[$this->aboutStringColumn]:null)
											));
		}
		//print count($this->theView->rooms);
		return $numResults;
	}
	/**
	 * 
	 * Function to look for Roomnumberpattern and call the faster function in case.
	 */
	function fillResultArray($zeichenkette){
		$ergebnis = array();
		$raunummer_pattern = "/(\w{1,3})-(\d{1,2}|k|K).(\w{1,3})/";
		$numResults = preg_match($raunummer_pattern, $zeichenkette, $ergebnis);
		if($numResults > 0){
			//raumnummer eingegeben!
			$numResults = $this->getXYZByRoomNumber($ergebnis[0]);
		}else $numResults = $this->getXYZByName($zeichenkette);
		return $numResults;
	}
	
	/**
	 * 
	 * Function which performs an incremental fill of the result in a 
	 * tour object (!) with the search-possibilities above and saves the ONE tour onject in the result
	 * in the view.
	 */
	function getTourByRooms($GET_Variables){
		$keys = array_keys($GET_Variables);
		$myString = "advSearchValue";
		$numResults=0;
		$newTour = new Tour("Ihre Wunschrute", array(), "", "beispiel.jpg", "Die Datenbank wurde nach ihren Eingaben durchsucht.");
		for($resultIndex=0;$resultIndex<count($GET_Variables);$resultIndex++){
			if(strpos($keys[$resultIndex], $myString)!==FALSE){
				$numResults=$this->fillResultArray($GET_Variables[$keys[$resultIndex]]);
				if($numResults>1){
					$newTour->notDefinite=true;
					array_push($newTour->rooms, $this->theView->result);
				}else if($numResults==1) array_push($newTour->rooms, $this->theView->result[0]);
				else /*no result*/ array_push($newTour->rooms, new Room("", "Kein Raum gefunden", "", "", "", "Für das eingegebene Stuchwort wurde kein Raum gefunden. Die Eingabe wird ignoriert. Bitte verzeihen sie, sollte es sich um einen Softwarefehler handeln."));
				$this->theView->result = array();
			}
		}
		$this->theView->result=array();
		$this->theView->result[0] = $newTour;
		session_unset();
		$_SESSION['rooms']=$this->theView->result[0]->rooms;
		return 1;
	}


	/**************************************
	 * XML related
	 */

	
	function getTourList(){
		$xml_parser = xml_parser_create();
		$this->theView->result = array();
		$this->tourIndex=0;

		xml_parser_set_option($xml_parser,XML_OPTION_SKIP_WHITE,1);
		xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0);
		xml_set_object($xml_parser, $this);
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($xml_parser, "characterData");
		if (!($fp = fopen($this->file, "r"))) {
		   die("could not open XML input");
		}
		
		while ($data = fread($fp, 4096)) {
		   if (!xml_parse($xml_parser, $data, feof($fp))) {
		       die(sprintf("XML error: %s at line %d",
		                   xml_error_string(xml_get_error_code($xml_parser)),
		                   xml_get_current_line_number($xml_parser)));
		   }
		}
		xml_parser_free($xml_parser);
		return $this->tourIndex;
	}
	
	function getTourByName($searchString){
		$this->getDetails=true;
		$this->found = false;
		$this->searchString = $searchString; 
		$this->getTourList();
		$this->getDetails=false;
		
		
		session_unset();
		$_SESSION['rooms']=$this->result;
		return 1;
	}

	function startElement($parser, $name, $attrs)
	{
	   if(!$this->found){
		   if($name=="tour" && $attrs['name']!=""){
		   		$this->actTourName=$attrs['name'];
		   		$this->actTourId=$attrs['id'];
				$this->result = array();
		   }
		   else if($name=="description")$this->actTag=$this->descr;
		   else if($name=="room")$this->actTag=$this->roomNumber;
		   else if($name=="text")$this->actTag=$this->addText;
		   else $this->actTag=$this->other;
	   }	   
	}
	
	function endElement($parser, $name)
	{
	   if(!$this->found){
			if($name=="tour"){
				if(!$this->getDetails){
						array_push($this->theView->result, new Tour($this->actTourName, $this->result, $this->actTourId, "", $this->actTourDescr));
						$this->tourIndex++;
				}else if($this->getDetails && $this->searchString==$this->actTourId){
						$this->theView->result[0] = new Tour($this->actTourName, $this->result, $this->actTourId, "", $this->actTourDescr);
						$this->tourIndex++;
						$this->found=true;
					}
			}
			else if($name=="station" && $this->getDetails && $this->searchString==$this->actTourId){
				$numResults = $this->getXYZByRoomNumber($this->actRoomNumber); 
				$this->result= $this->theView->result;
				$this->roomIndex++;
			}
	   }
	}
	
	function characterData($parser, $data)
	{
	   if(!$this->found){
		$tidiedString = trim($data);
		if($tidiedString!=""){
			switch($this->actTag){
				case $this->descr:
				$this->actTourDescr=$data;
				break;
				case $this->roomNumber:
				$this->actRoomNumber=$data;
				break;
				case $this->addText:
				$this->actAddText=$data;
				break;
				default:
				break;
			}
		}
	   }
	}
}
?>