<?php
/********************************************************************************************************
 *					Datei: Unternehmen.php
 *					Author: David M. Hübner & Christian Burghoff
 *					E-Mail: info@milbridge.de
 *					Letzte Änderung: 25.01.2006
 */


	include("libraries.php");
		
	class Unternehmen {

		private $unternehmenID;
		private $unternehmensgroesseID;	// 1:1-5, 2:5-20, 3:20-100, 4:100-1000, 5:>1000
		private $name;								// name des unternehmens
		private $adrStrasse;						// strasse des unternehmens inkl hausnummer
		private $adrPLZ;							// plz des unternehmens
		private $adrOrt;							// ort des unternehmens
		private $staatID;							// staat des unternehmens
		private $url;								// internet adresse des unternehmens
		private $brancheID;							// branche des unternehmens
		private $status;							// status des unternehmens, ob der student es schon fertig eingepflegt hat

		private $last_error;
		private $conn;
		private $inited;
		
		public function __construct(Connection $conn) {
			$this->last_error = "";
			$this->conn = $conn;
			$this->status = 1;
			$this->staatID = Config::HEIMATLAND_ID;
			$this->inited = false;
		} //constructor
		
		// Initialisiert das Objekt mit den Daten aus der Datenbank
		// liefert true falls erfolgreich, sonst false
		public function initAusDatenbank($unternehmenID) {
			$conn = $this->conn;
			
			if (!$conn->isConnected()) {
				$this->last_error = "Keine verbindung zur Datenbank";
				return false;
			}
			
			$unternehmenID = intval($unternehmenID);
			$query = new DBQuery("SELECT UnternehmenID, UnternehmensgroesseID, Name, adrStrasse, adrPLZ, adrOrt, adrStaatID, URL, BrancheID, status FROM unternehmen WHERE UnternehmenID=$unternehmenID");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 1 && $row = $result->getNextRow()) {
					$this->unternehmenID= intval($row[0]);
					$this->unternehmensgroesseID = intval($row[1]);
					$this->name = $row[2];
					$this->adrStrasse = $row[3];
					$this->adrPLZ	= $row[4];
					$this->adrOrt	= $row[5];
					$this->staatID = $row[6];
					$this->url = $row[7];
					$this->brancheID = $row[8];
					$this->status = $row[9];
					$this->inited = true;
					return true;
					
					
				} else {
					$this->last_error = "Fehlerhafte UnternehmensID für das Unternehmen.";
				}
			} else {
				$this->last_error = "Konnte Unternehmen nicht lesen".$conn->getLastError();
			}
			
			return false;
		}
		
		//fügt einen neues unternehmen ein, bzw. aktualisiert dieses, falls es schon vorhanden ist.
		public function updateDatenbank() {
			if (!$this->conn->isConnected()) {
				$this->last_error = "Keine Verbindung zur Datenbank.";
				return false;
			}
			$q = new DBQuery("SELECT Name FROM unternehmen WHERE UnternehmenID='$this->unternehmenID'");
			if ($result = $this->conn->executeQuery($q)) {
				if ($result->rowsCount() > 0) {
					$q = new DBQuery("UPDATE unternehmen SET UnternehmenID='$this->unternehmenID', UnternehmensgroesseID='$this->unternehmensgroesseID', Name='$this->name', adrStrasse ='$this->adrStrasse', adrPLZ='$this->adrPLZ', adrOrt='$this->adrOrt', adrStaatID='$this->staatID', URL='$this->url', BrancheID='$this->brancheID', status='$this->status' WHERE UnternehmenID='$this->unternehmenID'");
					if (!$this->conn->executeQuery($q)) {
						$this->last_error = $conn->getLastError();
						return false;
					}
				}
				else {
					$q = new DBQuery("INSERT INTO unternehmen(UnternehmenID, UnternehmensgroesseID, Name, adrStrasse, adrPLZ, adrOrt, adrStaatID, URL, BrancheID, status) VALUES('$this->unternehmenID', '$this->unternehmensgroesseID', '$this->name', '$this->adrStrasse', '$this->adrPLZ', '$this->adrOrt', '$this->staatID', '$this->url', '$this->brancheID', '$this->status')");
					$result = $this->conn->executeQuery($q);
					if (!$result) {
						$this->last_error = $this->conn->getLastError();
						return false;
					}
					else {
						 $this->unternehmenID = $result->getInsertId();
					}
				}
			}
			else {
				$q = new DBQuery("INSERT INTO unternehmen(UnternehmenID, UnternehmensgroesseID, Name, adrStrasse, adrPLZ, adrOrt, adrStaatID, URL, BrancheID, status) VALUES('$this->unternehmenID', '$this->unternehmensgroesseID', '$this->name', '$this->adrStrasse', '$this->adrPLZ', '$this->adrOrt', '$this->staatID', '$this->url', '$this->brancheID', '$this->status')");
				$result = $this->conn->executeQuery($q);
				if (!$result) {
					$this->last_error = $conn->getLastError();
					return false;
				}
				else {
					 $this->unternehmenID = $result->getInsertId();
				}
			}
			return true;
		}

		//löscht ein unternehmen (nur möglich wenn vom studenten erzeugt und nicht abgegeben)
		public function loescheAusDatenbank() {
			if (!$this->conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$unternehmenID = $this->getUnternehmenID();
			
			$q = new DBQuery("DELETE FROM unternehmen WHERE UnternehmenID=$unternehmenID");
			return ($result = $this->conn->executeQuery($q));
		}

		/* Sucht nach einem Unternehmen anhand eines Namens */
		/* Siehe die SQL - Anfrage*/
		public static function sucheUnternehmen(Connection $conn, ErrorQueue $err, $suchString) {
			$suchString		= str_replace("'", "", $suchString);
			$suchString2	= str_replace("ö", "oe", $suchString);
			$suchString2	= str_replace("ä", "ae", $suchString2);
			$suchString2	= str_replace("ü", "ue", $suchString2);
			$suchString3	= str_replace("oe", "ö", $suchString);
			$suchString3	= str_replace("ae", "ä", $suchString3);
			$suchString3	= str_replace("ue", "ü", $suchString3);
			$q = new DBQuery("SELECT u.UnternehmenID, u.Name, u.adrOrt, s.Name FROM unternehmen u LEFT JOIN staat s ON (u.adrStaatID=s.StaatID) WHERE (u.Name LIKE '%".$suchString."%' OR u.Name LIKE '%".$suchString2."%' OR u.Name LIKE '%".$suchString3."%') AND u.status = 0 ORDER BY u.Name");
			
			if ($conn->isConnected()) {
				if ($result = $conn->executeQuery($q))
					return $result;
				else {
					$err->addError("Datenbankfehler: ".$conn->getLastError());
				}
			} else {
				return false;
			}
		}

		/* Liefert alle Unternehmen aus der Datenbank als Array zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumUnternehmen(Connection $conn, ErrorQueue $err) {
			$q = new DBQuery("SELECT UnternehmenID, Name FROM unternehmen ORDER BY Name");
			
			if ($conn->isConnected()) {
				if ($result = $conn->executeQuery($q))
					return $result;
				else {
					$err->addError("Datenbankfehler: ".$conn->getLastError());
				}
			} else {
				return false;
			}
		}
		
		/* Liefert alle Branchen aus der Datenbank als Array zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumBranchen(Connection $conn, ErrorQueue $err) {
			$q = new DBQuery("SELECT BrancheID, Name FROM branche WHERE Status='0' ORDER BY Name");
			
			if ($conn->isConnected()) {
				if ($result = $conn->executeQuery($q)) {
					return $result;
				}
				else {
					$err->addError("Datenbankfehler: ".$conn->getLastError());
				}
			} else {
				return false;
			}
		}
		
		/* Liefert alle Groessen aus der Datenbank als Array zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumGroessen(Connection $conn, ErrorQueue $err) {
			$q = new DBQuery("SELECT UnternehmensgroesseID, Mitarbeiterzahl, MindestName FROM unternehmensgroesse");
			
			if ($conn->isConnected()) {
				if ($result = $conn->executeQuery($q))
					return $result;
				else {
					$err->addError("Datenbankfehler: ".$conn->getLastError());
				}
			} else {
				return false;
			}
		}
		
		/* Liefert alle Groessen aus der Datenbank als wirkliches zweidimensionalesArray zurück */
		public static function arrayGroessen(Connection $conn, ErrorQueue $err) {
			$result = Unternehmen::enumGroessen($conn, $err);
			$i = 0;
			while ($r = $result->getNextRow()) {
				$groessenArray[$i][0] = $r[0]; $groessenArray[$i][1] = $r[1]; $groessenArray[$i][2] = $r[2];
				$i++;
			}
			return $groessenArray;
		}
		
		public function createUnternehmenTable() {
			$unternehmensgroessen = Unternehmen::arrayGroessen($this->conn, new Errorqueue());
			$html_bericht = '
			<table border="0" cellspacing="0" cellpadding="0" width="420">
  	      <tr>
  	        <td rowspan="8">
  	          <img src="_pic/spacer.gif" width="28" height="1" border="0"><br>
  	        </td>

  	      	<td class="dick" width="150">
  	          Arbeitsbereich:
  	        </td>
  	        <td>
  	          '.$this->getBranchenName().'
  	        </td>
  	      </tr>
  	      <tr>
  	      	<td class="dick">
  	          Firma:
  	        </td>
  	        <td>
  	          '.$this->getName().'
  	        </td>
  	      </tr>
  	      <tr>
  	      	<td class="dick">
  	          URL:
  	        </td>
  	        <td>
  	          '.$this->getUrl().'
  	        </td>
  	      </tr>
  	      <tr>
  	      	<td class="dick">
  	          Adresse:
  	        </td>
  	        <td>
  	          '.$this->getAdrStrasse().',&nbsp;'.$this->getAdrPLZ().'&nbsp;'.$this->getAdrOrt().'
  	        </td>
  	      </tr>
  	      <tr>

  	      	<td class="dick">
  	          Mitarbeiterzahl:

  	        </td>
  	        <td>
  	          '.$this->getUnternehmensgroesseAlsString().' 
  	        </td>
  	      </tr>
  	    </table>';
  	  return $html_bericht;
     }  
         
      
		public function getLastError() {
			return $this->last_error;
		}
		
		public function getInited() {
			return $this->inited;
		}
		
		public function setInited($zustand) {
			$this->inited = $zustand;
		}
		
		public function getStatus(){
			return $this->status;
		}
		
		public function setStatus($zustand){
			$this->status = $zustand;
		}
		
		public function getUnternehmenID() {
			return $this->unternehmenID;
		}
		
		public function setUnternehmenID($p) {
			$this->unternehmenID = $p;
		}
	
		public function getUnternehmensgroesseAlsString() {
			$groessen = Unternehmen::arrayGroessen($this->conn, new ErrorQueue);
			return $groessen[$this->unternehmensgroesseID-1][1];
		}
		
		public function getUnternehmenmidestgroesseAlsString() {
			$groessen = Unternehmen::arrayGroessen($this->conn, new ErrorQueue);
			return $groessen[$this->unternehmensgroesseID-1][2];
		}
		
					
		public function getUnternehmensgroesseID() {
			return $this->unternehmensgroesseID;
		}
		
		public function setUnternehmensgroesseID($p) {
			$this->unternehmensgroesseID = $p;
		}
				
		public function getName() {
			return $this->name;
		}
		
		public function setName($p) {
			$this->name = $p;
		}
		
		public function getAdrStrasse() {
			return $this->adrStrasse;
		}
		
		public function setAdrStrasse($p) {
			$this->adrStrasse = $p;
		}
		
		public function getAdrPLZ() {
			return $this->adrPLZ;
		}
		
		public function setAdrPLZ($p) {
			$this->adrPLZ = $p;
		}
		
		public function getAdrOrt() {
			return $this->adrOrt;
		}
		
		public function setAdrOrt($p) {
			$this->adrOrt = $p;
		}
		
		public function getStaatID() {
			return $this->staatID;
		}
		
		public function setStaatID($p) {
			$this->staatID = $p;
		}
		
		public function getUrl() {
			return $this->url;
		}
		
		public function setUrl($p) {
			$this->url = $p;
		}
		
		public function getBrancheID() {
			return $this->brancheID;
		}
		
		public function setBrancheID($p) {
			$this->brancheID = $p;
		}
		
		public function getBranchenName() {
			$conn = $this->conn;
			if (!$conn->isConnected()) {
				$this->last_error = "Keine verbindung zur Datenbank";
				return false;
			}
			$this->brancheID = intval($this->brancheID);
			$query = new DBQuery("SELECT Name FROM branche WHERE BrancheID=$this->brancheID");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 1 && $row = $result->getNextRow()) {
					return $row[0];
				} else {
					$this->last_error = "Fehlerhafte BrancheID für die Branche.";
				}
			} else {
				$this->last_error = "Konnte Branche nicht lesen".$conn->getLastError();
			}
			return false;
		}
		
		public function getStaatName() {
			$conn = $this->conn;
			if (!$conn->isConnected()) {
				$this->last_error = "Keine verbindung zur Datenbank";
				return false;
			}
			$this->staatID = intval($this->staatID);
			$query = new DBQuery("SELECT Name FROM staat WHERE StaatID=$this->staatID");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 1 && $row = $result->getNextRow()) {
					return $row[0];
				} else {
					$this->last_error = "Fehlerhafte adrStaatID für den Staat.";
				}
			} else {
				$this->last_error = "Konnte Staat nicht lesen".$conn->getLastError();
			}
			return false;
		}
		
	} // CLASS
	
?>
