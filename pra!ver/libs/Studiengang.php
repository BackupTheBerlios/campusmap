<?php
/********************************************************************************************************
 *            Datei: Studiengang.php
 *           Author: David Hübner
 * 			 E-Mail: David@millbridge.de
 *  Letzte Änderung: 22.01.2006
 */
 
	include("libraries.php");

	class Studiengang {
	
		// Die Status-Konstanten
		const STATUS_AKTIV = 0;
		const STATUS_INAKTIV = 1;
	
		private $id;
		private $name;				// Bezeichnung des Studiengangs
		private $status;			// AKTIV / INAKTIV
		
		private $sachbearbeiter;
		private $ueberpruefer;
		
		private $fachbereichid;

		private $inited;			// Ob der Studiengang mit den richtigen Werten initialisiert wurde
		private $conn;   // Eine Verbindung zur Datenbank
		
		private $last_error;
		
		// Erzeugt einen leeren Studiengang
		public function __construct(Connection $conn) {
			
			$this->inited = false;
			$this->last_error = "";
			$this->conn = $conn;
			$this->id = -1;
		} // construct
		
		// Initialisiert den Studiengang mit den Werden aus der Datenbank
		public function initAusDatenbank($studiengangID) {
			
			$conn = $this->conn;
			
			if (!$conn->isConnected()) {
				$this->last_error = "Das Fach konnte nicht initialisiert werden. Keine Verbindung zur Datenbank.";
				return false;
			}
						
			$q = new DBQuery("SELECT studiengangID, name, Status, PraktikaSachbearbeiterID, PraktikaMitarbeiterUsername, FachbereichID FROM studiengang WHERE studiengangID=$studiengangID");
			if ($result = $conn->executeQuery($q)) {
				if ($result->rowsCount() == 1) {
					
					$arr = $result->getNextRow();
					
					$this->id = $arr[0];
					$this->name = $arr[1];
					$this->status = $arr[2];
					$this->sachbearbeiter = $arr[3];
					$this->ueberpruefer = $arr[4];
					$this->fachbereichid = $arr[5];
					$err = new ErrorQueue();
					$this->last_error = $err->returnAsStringAndRemove();
					$this->inited = true;
					
					return true;
				} else {
					$this->last_error = "Das angegebene Fach '$fach' existiert nicht mehr.";
					return false;
				}
			} else {
				$this->last_error = $conn->getLastError();
				return false;
			}
		}
		
		// Initialisiert den Studiengang mit den angegebenen Werden.
		// Offline initialisierung (zum Zwischenspeichern oder Schreiben in die DB geignet)
		public function initAusWerten($name, $status, $sachbearbeiter, $ueberpruefer, $fachbereichid) {
			$this->name = addslashes($name);
			$this->status = intval($status);
			$this->sachbearbeiter = intval($sachbearbeiter);
			$this->ueberpruefer = intval($ueberpruefer);
			$this->fachbereichid = intval($fachbereichid);
			$this->inited = true;
		}

		/* Liefert alle Studiengänge aus der Datenbank als Array Name, Status usw. zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumStudiengaenge(Connection $conn) {
			
			$q = new DBQuery("SELECT studiengangID, name FROM studiengang ORDER BY name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}
		
		/* Liefert alle AKTIVEN Studiengänge aus der Datenbank als Array Name, Status usw. zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumAktiveStudiengaenge(Connection $conn) {
			
			$q = new DBQuery("SELECT studiengangID, name FROM studiengang WHERE Status='$Studiengang::STATUS_AKTIV' ORDER BY name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}

		public static function getStudiengaengeVomSachbearbeiter(Connection $conn, $dozentid) {
			
			$q = new DBQuery('SELECT Name, studiengangID FROM studiengang WHERE PraktikaSachbearbeiterID ="'.$dozentid.'"');

			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}
		
		public static function getStudiengaengeVomMitarbeiter(Connection $conn, $username) {
			
			$q = new DBQuery('SELECT Name, studiengangID FROM studiengang WHERE PraktikaMitarbeiterUsername ="'.$username.'"');

			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}

		// Fügt einen neuen Studiengang zur Datenbank ein. Liefert das eingefügte Studiengang-Objekt oder false zurück.
		public static function insertStudiengang(Connection $conn, $name, $fachbereichid, ErrorQueue $err) {
			$s = new Studiengang($conn);
			$status = STATUS_AKTIV;
			$sachbearbeiter = 0;
			$ueberpruefer = 0;
			$s->initAusWerten($name, $status, $sachbearbeiter, $ueberpruefer, intval($fachbereichid));
			if ($s->writeStudiengang()) {
				return $s;
			} else {
				$err->addError($s->getLastError());
			}
		}
		
		// Schreibt den Studiengang in die Datenbank.
		// Liefert das ResultSet der Datenbankanfrage oder false
		protected function writeStudiengang() {
			if (!$this->conn->isConnected()) {
				$this->last_error = "Keine Verbindung zur Datenbank.";
				return false;
			}
			// Überprüfen bereits ein Studiengang mit so einem Namen existiert
			$q = new DBQuery("SELECT name FROM studiengang WHERE name='".$this->name."'");
			if ($result = $this->conn->executeQuery($q)) {
				if ($result->rowsCount() > 0) {
					$this->last_error = "Ein Studiengang mit dem Namen exisitiert bereits.";
					return false;
				}
			}
			$string  = "INSERT INTO studiengang(Name, Status, PraktikaMitarbeiterUsername, PraktikaSachbearbeiterID, FachbereichID) ";
			$string .= "VALUES('".$this->name."', '".$this->status."', '".$this->sachbearbeiter."', '".$this->ueberpruefer."', '".$this->fachbereichid."')";
			$q  = new DBQuery($string);
			
			if ($result = $this->conn->executeQuery($q)) {
				return $result;
			} else {
				$this->last_error = $this->conn->getLastError();
				return false;
			}
		}
		
		// Echoes all parameters
		private function echoStudiengang() {
			echo "ID = ".$this->id."<br />";
			echo "Name = ".$this->name."<br />";
			echo "Status = ".$this->status."<br />";
			echo "PraktikumsSachbearbeiter = ".$this->sachbearbeiter."<br />";
			echo "PraktikumsÜberprüfer = ".$this->ueberpruefer."<br />";
			echo "FachbereichID = ".$this->fachbereichid."<br />";
		}
		
		public static function setSachbearbeiter(Connection $conn, $studiengangID, $dozentID, $mitarbeiterUsername, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$q = new DBQuery("UPDATE studiengang SET PraktikaSachbearbeiterID='$dozentID', PraktikaMitarbeiterUsername='$mitarbeiterUsername'  WHERE studiengangID='$studiengangID'");
					if (!$conn->executeQuery($q)) {
						$err->addError($conn->getLastError());
						return false;
					}
		}
		
		public static function update(Connection $conn, $studiengangID, $neuerName, $neuefachbereichid, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			// Überprüfen bereits ein Studiengang mit so einem Namen existiert
			$q = new DBQuery("SELECT name FROM studiengang WHERE name='".$neuerName."' AND StudiengangID!='".$studiengangID."'");
			if ($result = $conn->executeQuery($q)) {
				if ($result->rowsCount() > 0) {
					$err->addError("Ein Studiengang mit dem Namen exisitiert bereits.");
					return false;
				}
			}
			$q = new DBQuery("UPDATE studiengang SET name='".$neuerName."', FachbereichID='".$neuefachbereichid."' WHERE studiengangID='".$studiengangID."'");
					if (!$conn->executeQuery($q)) {
						$err->addError($conn->getLastError());
						return false;
					}
		}
	
		public static function setAktiv(Connection $conn, $studiengangID, $aktiv, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$neuerStatus = Studiengang::STATUS_AKTIV;
			if ($aktiv == false) $neuerStatus = Studiengang::STATUS_INAKTIV;
			$q = new DBQuery("UPDATE studiengang SET status='$neuerStatus' WHERE studiengangID='$studiengangID'");
					if (!$conn->executeQuery($q)) {
						$err->addError($conn->getLastError());
						return false;
					}
		}
		
		public function getConnection() {
			return $this->conn;
		}
		
		public function getLastError() {
			return $this->last_error;
		}
		
		public function isInited() {
			return $this->inited;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function getStatus() {
			return $this->status;
		}
		
		public function getID() {
			return $this->id;
		}
		
		public function getFachbereichID() {
			return $this->fachbereichid;
		}
		
		public function getSachbearbeiterID() {
			return $this->sachbearbeiter;
		}
		
		public function getSachbearbeiter() {
			$query = new DBQuery('SELECT Name, EMail, Telefon FROM dozent WHERE PDozentID="'.$this->getSachbearbeiterID().'"');
			if ($result = $this->conn->executeQuery($query))
				if ($result->rowsCount() == 1 && $row = $result->getNextRow())
					return $row;
			$this->last_error = $this->conn->getLastError();
			return false;
		}
		

		
		public function getMitarbeiterID() {
			return $this->ueberpruefer;
		}
		
		public function getMitarbeiter() {
			$q = new DBQuery('SELECT Name, Email FROM mitarbeiter WHERE Username="'.$this->getMitarbeiterID().'"');
			if ($result = $this->conn->executeQuery($q))
				if ($result->rowsCount() == 1 && $row = $result->getNextRow())
					return $row;
			$this->last_error = $this->conn->getLastError();
			return false;
		}
		
		/* Übersetzt den Abschluss als String */
		public static function translateStatusAsString($status) {
			switch ($status) {
				case Studiengang::STATUS_AKTIV : return "Status aktiv";
				case Studiengang::STATUS_INAKTIV : return "Status inaktiv";
				default: return "Undefiniert ".$status;
			}
		}
		
		public static function getSemsysInfo(Connection $conn, ErrorQueue $err) {
			$select_query = new DBQuery("SELECT PStudienplanID, Studiengang FROM studienplan ORDER BY Studiengang");
			if (!($studienplaene = $conn->executeQuery($select_query))) {
				$err->addError("Die vorhandenen studienpl&auml;ne konnten von der Datenbank nicht gelesen werden. ".$conn->getLastError());
			}
			if ($studienplaene) {
			  $studiengaenge_string = "Folgende Studiengänge nehmen bereits an Studiman teil:\n";
			  while ($r = $studienplaene->getNextRow()) {
				$studiengaenge_string .= $r[1]."\n";
			  } // while
			  $studiengaenge_string .= "\n\n";
			} // if
				
			$string  = "Kennst Du schon Studiman?\n" 
					."Studiman ist ein Tool zur Organisation Deines Semesters an der Fachhochschule Lübeck.\n"
					."Deine Benutzerdaten für PRA|VER gelten ebenso für Studiman.\n"
					."Schau doch mal rein unter http://osmigib.fh-luebeck.de/SemSys/ .\n\n"
					.$studiengaenge_string;
					
			return $string;
		}
		
	} // END OF CLASS

?>