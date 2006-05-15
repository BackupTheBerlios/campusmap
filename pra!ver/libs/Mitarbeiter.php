<?php

/********************************************************************************************************
 *					Datei: Bericht.php
 *					Author: David M. Hübner & Christian Burghoff
 *					E-Mail: info@milbridge.de
 *					Letzte Änderung: 25.01.2006
 */

	include("libraries.php");

	class Mitarbeiter {
		
		private $benutzername;
		private $name;
		private $email;
		private $zugangsstufe;
		
		private $last_error;
		private $conn;
		private $inited;
		
		/*
		 * Erzeugt ein leeres Mitarbeiter-Objekt
		 */
		public function __construct(Connection $conn) {
			$this->benutzername = "";
			$this->name = "";
			$this->zugangstufe = 0;
			$this->last_error = "";
			$this->conn = $conn;
			$this->inited = false;
		}
		
		/*
		 * Erzeugt ein Mitarbeiter mit den gegebenen Parametern und speichert ihn in die Datenbank ab.
		 * Rückgabewert ist das Mitarbeiter-Objekt. Bei Fehler false.
		 * Paramerer: string $username  - Der Benutzername
		 *            string $password  - Das Passwort in Klartext
		 *            string $name      - Name des Mitarbeiters
		 *            string $email     - E-Mail-Adresse des Mitarbeiters
		 *            Connection $conn  - Die Datenbankverbindung
		 *            ErrorQueue $err   - Ein Buffer für runtime Fehlermeldungen
		 */
		public static function createMitarbeiter(Connection $conn, ErrorQueue $err, $username, $password, $name, $email, $zugangsstufe) {
			$username = addslashes($username);
			$mdpass = md5($password);
			$name = addslashes($name);
			$email = addslashes($email);
			$zugangsstufe = intval($zugangsstufe);
			if (!Mailer::checkMail($email)) {
				$err->addError("Die angegebene E-Mail-Adresse ist im falschen Format.");
				return false;
			}
			
			$q = new DBQuery("INSERT INTO mitarbeiter(Username, Password, Name, Email, Zugangsstufe) VALUES('$username', '$mdpass', '$name', '$email', $zugangsstufe)");
			if ($conn->executeQuery($q)) {
				$mit = new Mitarbeiter($conn);
				$mit->benutzername = $username;
				$mit->name = $name;
				$mit->email = $email;
				$mit->zugangsstufe = $zugangsstufe;
				
				$message = "Hallo $name,<br /> \r\n";
				$message .= "Sie haben jetzt Zugang zum Mitarbeiter-Bereich von Studiman.<br /> \r\n";
				$message .= "Sie k&ouml;nnen sich unter: ";
				$message .= '<a href="http://igi.fh-luebeck.de/SemSys/mitarbeiter/">http://igi.fh-luebeck.de/SemSys/mitarbeiter/</a>';
				$message .= "<br >\r\n mit dem Benutzernamen \"$username\" und dem Passwort \"$password\" anmelden.";
				
				Mailer::mailAsHTML($email, "Zugang zum Mitarbeiter-Bereich", $message);
				
				return $mit;
			} else {
				$err->addError("Die/Der Mitarbeiterin/Mitarbeiter \"$name\" konnte nicht erzeugt werden. &Uuml;berpr&uuml;fen Sie den Benutzernamen und versuchen Sie es noch einmal.");
			}
			
			return false;
		}
		
		/*
		 * Entfernt den Mitarbeiter mit dem gegebenen Benutzernamen aus der Datenbank.
		 * Rückgabewert ist true. Bei Fehler false.
		 * Paramerer: string $username  - Der Benutzername
		 *            Connection $conn  - Die Datenbankverbindung
		 *            ErrorQueue $err   - Ein Buffer für runtime Fehlermeldungen
		 */
		public static function deleteMitarbeiter(Connection $conn, ErrorQueue $err, $username) {
			
			$username = addslashes($username);
			$q = new DBQuery("DELETE FROM mitarbeiter WHERE Username='$username'");
			
			if ($conn->executeQuery($q)) {
				return true;
			} else {
				$err->addError("Die/Der Mitarbeiterin/Mitarbeiter konnte nicht gel&ouml;scht werden.");
			}
			
			return false;
		}
		
		/*
		 * Liest alle Mitarbeiter aus der Datenbank aus und füllt sie in den UtilBuffer
		 * Rückgabewert ist true. Bei Fehler false.
		 */
		public static function getAlleMitarbeiter(Connection $conn, ErrorQueue $err, UtilBuffer $buffer) {
			$q = new DBQuery("SELECT Username, Name, Email, Zugangsstufe FROM mitarbeiter ORDER by Name");
			
			if ($result = $conn->executeQuery($q)) {
				while ($r = $result->getNextRow()) {
					$mit = new Mitarbeiter($conn);
					$mit->benutzername = $r[0];
					$mit->name = $r[1];
					$mit->email = $r[2];
					$mit->zugangsstufe = $r[3];
					$buffer->add($mit);
				}
				
				return true;
			} else {
				$err->addError("Die Mitarbeiter konnten nicht ausgelesen werden. ".$conn->getLastError());
			}
			
			return false;
		}
		
		/*
		 * Initialisiert den gegebenen Mitarbeiter mit den Daten aus der Datenbank.
		 * Rückgabewert ist true, wenn der Mitarbeiter sich erfolgreich angemeldet hat, sonst false.
		 * Paramerer: string $username  - Der Benutzername
		 *            string $password  - Das Passwort als Message Digest (md5)
		 */
		public function login($username, $password) {
			
			$username = addslashes($username);
			$password = addslashes($password);
			
			$q = new DBQuery("SELECT Username, Name, Email, Zugangsstufe FROM mitarbeiter WHERE Username='$username' and Password='$password'");
			if ($result = $this->conn->executeQuery($q)) {
				if ($r = $result->getNextRow()) {
					$this->benutzername = $r[0];
					$this->name = $r[1];
					$this->email = $r[2];
					$this->zugangsstufe = $r[3];
				} else {
					$this->last_error = "Falsche Benutzername oder Passwort.";
					return false;
				}
			} else {
				$this->last_error = "Fehler bei der Anmeldung. ".$this->conn->getLastError();
				return false;
			}
			
			$this->inited = true;
			return true;
		}
		
		/*
		 * Hiermit weist man einem Mitarbeiter neue Zugriffsrechte, Benutzername und Passwort zu.
		 * Rückgabewert ist true. Bei Fehler wird last_error gesetzt und false zurückgegeben.
		 * Paramerer: string $new_username  - Der neue Benutzername
		 *            string $new_password  - Das neue Passwort in Klartext
		 *            int    $zugangssstufe - Die neue Zugangsstufe
		 */
		public function updateMitarbeiterLogin($new_username, $new_password, $zugangsstufe) {
			$err->addError("updateMitarbeiterLogin wurde noch nicht implementiert");
			return false;
		}
		
		/*
		 * Hiermit kann man die E-Mail-Adresse und den Namen des Mitarbeiters ändern
		 * Rückgabewert ist true. Bei Fehler wird last_error gesetzt und false zurückgegeben.
		 * Paramerer: string $name  - Der neue Name
		 *            string $email - Die neue E-Mail Adresse
		 */
		public function updateMitarbeiterData($name, $email) {
			$err->addError("updateMitarbeiterData wurde noch nicht implementiert");
			return false;
		}
		
		/*
		 * Überprüft, ob der Mitarbeiter Zugriffsrechte zum angegeben Bereich hat und liefert
		 * dementsprechend true oder false zurück
		 * Paramerer: int $berech - Der Bereich (Siehe Zugangsstufe.php)
		 */
		public function hatZugangZu($bereich) {
			return Zugangsstufe::hatZugang($this->zugangsstufe, $bereich);
		}
		
		public function isLoggedIn() {
			return $this->inited;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function getUsername() {
			return $this->benutzername;
		}
		
		public function getEmail() {
			return $this->email;
		}
		
		public function getZugangsstufe() {
			return $this->zugangsstufe;
		}
		
		
		public function getLastError() {
			return $this->last_error;
		}
		
	}


?>