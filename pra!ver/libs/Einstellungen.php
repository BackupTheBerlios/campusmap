<?php
/********************************************************************************************************
 *            Datei: Einstellungen.php
 *           Author: Christian Burghoff
 *  Letzte Änderung: 24.01.2005
 *
 * Kurzbeschreibung: Diese Klasse repräsentiert die aktuellen Einstellungen des Systems.
 *					 Wenn ein Objekt dieser Klasse erzeugt wird, dann werden die Einstellungen:
 * 					 Administrator E-Mail und die Anmeldeaufforderung aus der Datenbank
 *					 geladen. Neue Einstellungen können auch wieder über ein Objekt dieser Klasse
 *					 in die Datenbank gespeichert werden.
 *					
 ********************************************************************************************************/

	include("libraries.php");
	
	class Einstellungen {
		
		
		
				
		private $conn;			// Die Verbindung zur Datenbank
		private $last_error;    // Die zuletzt generiert Fehlermeldung als String
		
		
		
		private $loaded;		// Flag, ob die Einstellungen aus der Datenbank ausgelesen wurden.
								// (Das passiert entweder bei der Erzeugung des Objektes oder zu einem späteren Zitpunkt)
								
		private $adminmail;		// Die E-Mail Adresse des Administrators

	
		
		public function __construct(Connection $conn) {

		
			$this->adminmail = "";
			
			$this->last_error = "";
			
			$this->conn = $conn;
			$this->loaded = false;
			$this->loadEinstellungen();
			

		}
		
		// Lädt die aktuellsten Einstellungen aus der Datenbank
		// Liefert true oder false bei Fehler zurück
		public function loadEinstellungen() {
			
			if (null == $this->conn) return false;
			

			$q1 = new DBQuery("SELECT inhalt FROM einstellungen WHERE einstellung='adminmail'");

			
			$errval = 0;

			
			if ($res = $this->conn->executeQuery($q1)) {
				if ($r = $res->getNextRow()) {
					$this->adminmail = $r[0];
				}
			} else {
				$this->last_error .= $this->conn->getLastError()."<br>";
				$errval++;
			}
			
			$this->loaded = true;
			
			if ($errval > 0) return false;
			else return true;
		}//loadEinstellungen
		



		// Liefert die E-Mail Adresse des Administrators zurück
		public function getAdminsEmail() {
			if (!$this->loaded) $this->loadEinstellungen();
			return $this->adminmail;
		}

		public function getLastError() {
			return $this->last_error;
		}


		
		// Schreibt die Einstellungen in die Datenbank ein und 
		public function writeEinstellungen($adminmail) {
      
			$adminmail = addslashes($adminmail);

						// Abspeichern
						$q1 = new DBQuery("UPDATE einstellungen SET inhalt='".$adminmail."' WHERE einstellung='adminmail'");
						
						$errval = 0;

						if (!$this->conn->executeQuery($q1)) {
							$this->last_error .= $this->conn->getLastError()."<br />";
							$errval++;
						}


						if ($errval > 0) return false;
						else return true;
					

		} // writeEinstellungen

		
	} // CLASS
	

?>
