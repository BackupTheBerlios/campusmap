<?php
/********************************************************************************************************
 *					Datei: Bericht.php
 *					Author: David M. Hübner & Christian Burghoff
 *					E-Mail: David@milbridge.de
 *					Letzte Änderung: 25.01.2006
 */


	include("libraries.php");
		
	class Student {
		
		private $loggedIn;	// Ob der Student eingeloggt ist
		private $hatStudiengang;	// Ob der Student einen Studiengang hat
		
		private $name;				// Name
		private $vorname;			// Vorname
		private $matrnr;			// Matrikelnummer
		private $email;				// E-Mail
		private $text;				// Text
		private $semester;		// Semesterzahl
		private $studiengang; //Studiengang
		private $last_error;
		
		private $conn;
		
		public function __construct(Connection $conn) {
			$this->loggedIn = false;
			$this->last_error = "";
			$this->conn = $conn;
		} //constructor
		
		// Initialisiert das Objekt mit den Daten aus der Datenbank
		// liefert true, wenn der Student eingeloggt ist, sonst false
		public function init($matrnr, $pass) {
			$this->loggedIn = false;
			$conn = $this->conn;
			
			if (!$conn->isConnected()) {
				$this->last_error = "Keine verbindung zur Datenbank";
				return false;
			}
			
			$pass   = addslashes($pass);
			$matrnr = intval($matrnr);
			$query = new DBQuery("SELECT MatrNr, Name, EMail, Text, Semesterzahl, PStudienplanID, Vorname, StudiengangID FROM student WHERE Pass=\"$pass\" and MatrNr=$matrnr");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 1 && $row = $result->getNextRow()) {
					$this->loggedIn = true;
					$this->name     = $row[1];
					$this->matrnr   = $row[0];
					$this->semester = $row[4];
					$this->email    = $row[2];
					$this->text     = urldecode($row[3]);
					$this->vorname  = $row[6];
					$this->studiengang = new Studiengang($conn);
					if ($this->studiengang->initAusDatenbank($row[7])) {
						$this->hatStudiengang = true;
					} else {
						$this->last_error = $this->studiengang->getLastError();
						$this->hatStudiengang = false;
					}
					
				} else {
					$this->last_error = "Fehlerhafte Matrikelnummer und Passwort.";
				}
			} else {
				$this->last_error = "Fehler beim Einloggen. ".$conn->getLastError();
			}
			
			return $this->loggedIn;
		}
		
		// Liefert den angegebenen Student aus der Datenbank oder false, wenn er nicht existiert
		public static function readStudent(Connection $conn, ErrorQueue $err, $matrnr) {
			$matrnr = intval($matrnr);
			$s = "SELECT s.MatrNr, s.Name, s.EMail, s.Text, s.Semesterzahl, s.PStudienplanID, s.Vorname, s.StudiengangID FROM student s WHERE s.MatrNr = ".$matrnr;
			$q = new DBQuery($s);
			if ($result = $conn->executeQuery($q)) {
				if ($row = $result->getNextRow()) {
					$studi = new Student($conn);
					$studi->loggedIn = true;
					$studi->matrnr   = $row[0];
					$studi->name     = $row[1];
					$studi->email    = $row[2];
					$studi->text     = urldecode($row[3]);
					$studi->semester = $row[4];
					$studi->vorname  = $row[6];
					$studi->studiengang = new Studiengang($conn);
					if ($studi->studiengang->initAusDatenbank($row[7])) {
						$studi->hatStudiengang = true;
					} else {
						$studi->last_error = $studi->studiengang->getLastError();
						$studi->hatStudiengang = false;
					}
					
										
					return $studi;
				} else {
					$err->addError("Der Student mit Matrikelnummer $matrnr existiert nicht.");
				}
			} else {
				$err->addError("Ein Datenbankfehler ist aufgetreten. ".$conn->getLastError());
			}
			
			return false;
		}
		
		public function hatStudiengang() {
			return $this->hatStudiengang;
		}
		
		public function getLastError() {
			return $this->last_error;
		}
		
		public function isLoggedIn() {
			return $this->loggedIn;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function setName($n) {
			$this->name = $n;
		}

		public function getVorname() {
			return $this->vorname;
		}
		
		public function setVorname($vn) {
			$this->vorname = $vn;
		}

		public function getNameKomplett() {
			if (strcmp($this->name, "") != 0 && strcmp($this->vorname, "") != 0) {
				$s = $this->name.", ".$this->vorname;
			} else {
				$s = $this->vorname." ".$this->name;
			}
			
			$s = Util::truncateStringLength($s, 40);
			return $s;
		}

		public function getMatrNr() {
			return $this->matrnr;
		}

		public function setMatrNr($m) {
			$this->matrnr = $m;
		}		

		public function getEmail() {
			return $this->email;
		}
		
		public function setEmail($e) {
			$this->email = $e;
		}
		
		public function getSemester() {
			return $this->semester;
		}
		
		public function setSemester($s) {
			$this->semester = $s;
		}
		
		public function getText() {
			return $this->text;
		}
		
		public function setText($t) {
			$this->text = $t;
		}

		public function getStudiengang() {
			if ($this->hatStudiengang()) {
				return $this->studiengang;
			} else {
				return "";
			}
		}
		
		// Meldet einen Neuen Student an und liefert true zurück; Bei Fehler - false
		public static function neuerStudentAnmelden($matrnr, $name, $vorname, $email, $fhemail, ErrorQueue $err, Connection $conn) {
			$gen = new PassGenerator();
			$pass = $gen->createNewPass(0);
			$mdpass = md5($pass);
			
			// Matrikelnummer überprüfen
			$matrnr1 = $matrnr;
			$matrnrlen = strlen($matrnr1);
			$matrnr = intval($matrnr);
			if ($matrnrlen != strlen($matrnr) || $matrnr <= 0) {
				$err->addError("Die Matrikelnummer ist ungültig.");
				return false;
			}
			
			$name = addslashes($name);
			$email = addslashes($email);
			$vorname = addslashes($vorname);
			
			$fhemail.="@stud.fh-luebeck.de";
			$fhemail = addslashes($fhemail);
			
			if (strcmp($name, "") == 0 || strcmp($vorname, "") == 0 || strcmp($email, "") == 0 || strcmp($fhemail, "") == 0) {
				$err->addError("Bitte f&uuml;ll alle Felder aus.");
				return false;
			}
			
			if (!Mailer::checkMail($email)) {
				$err->addError("Die E-Mail-Adresse hat das falsche Format.");
				return false;
			}
			
			if (!Mailer::checkMail($fhemail)) {
				$err->addError("Die FHL-E-Mail-Adresse hat das falsche Format.");
				return false;
			}
			
			if ($conn->isConnected()) {
				$q = new DBQuery("INSERT INTO student(MatrNr, Name, EMail, Pass, Vorname) VALUES($matrnr, '$name', '$email', '$mdpass', '$vorname')");
				if ($conn->executeQuery($q)) {
					Mailer::mailit($fhemail, "Deine Anmeldung", "Hallo $name,\n danke für Deine Anmeldung. Deine Login-Daten lauten:\n Passwort: \"$pass\"\nMatrikelnummer: $matrnr\n\n".Studiengang::getSemsysInfo($conn, $err));
					return true;
				} else {
					$err->addError("Die Anmeldung konnte nicht durchgef&uuml;hrt werden. Diese Matrikelnummer ist schon registriert.");
					return false;
				} // Query executed
			} else {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			} // Connected
		} // neuanmeldung

	} // CLASS
	
?>