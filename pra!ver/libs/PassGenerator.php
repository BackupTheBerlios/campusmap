<?php

	include("libraries.php");

	class PassGenerator {
		
		private $alpha; // Das Array mit dem Alphabet
		private $last_error;
		
		public function __construct() {
			$n = 0;
			$i = 'A';
			$alpha = array();
			for ($i = 65, $n = 0; $i <= 90; $n++, $i++) { // Von A - Z
				$alpha[$n] = chr($i);
			}
			
			for ($i = 97; $i <= 122; $n++, $i++) { // Von a - z
				$alpha[$n] = chr($i);
			}
			
			for ($i = '0'; $i <= '9'; $n++, $i++) { // Von 0 - 9
				$alpha[$n] = $i;
			}
			
			$alpha[$n] = '$';
			$n++;
			$alpha[$n] = '!';
			$n++;
			$alpha[$n] = '+';
			$n++;
			$alpha[$n] = '-';
			$n++;
			$alpha[$n] = '#';
			$n++;
			$alpha[$n] = '*';
			$n++;
			$alpha[$n] = '=';
			
			$this->alpha = $alpha;
			$this->last_error = "";
		} // construct
		
		// Setzt ein neues Alphabet
		public function setAlphabet($arr) {
			$this->alpha = $arr;
		}
		
		// Liefert das vorhandene Alphabet
		public function getAlphabet() {
			return $this->alpha;
		}
		
		public function getLastError() {
			return $this->last_error;
		}
		
		public function echoAlpha() {
			$i = 0;
			for ($i = 0; $i < count($this->alpha); $i++) {
				echo $this->alpha[$i];
			}
		}
		
		// Schreibt ein neues Passwort für den Studenten und liefert seine E-Mail adresse und Namen
		// als array zurück oder false;
		private function writepass(Connection $conn, $matrnr, $pass) {
			$pass = md5($pass);
			$matrnr = intval($matrnr);
			if ($conn->isConnected()) {
				// Email des Studenten auslesen
				$q = new DBQuery("SELECT Email FROM student WHERE MatrNr=$matrnr");
				if ($res = $conn->executeQuery($q)) {
					if ($r = $res->getNextRow()) {
						$email = $r[0];
					} else {
						$this->last_error = "Die Matrikelnummer existiert nicht.";
						return false;
					}
				} else {
					$this->last_error = "Die Matrikelnummer konnte nicht &uuml;berpr&uuml;ft werden. ".$conn->getLastError();
					return false;
				}
				
				// Das neue Passwort wird geschrieben
				$q = new DBQuery("UPDATE student SET Pass='$pass' WHERE MatrNr=$matrnr");
				if ($res = $conn->executeQuery($q)) {
					if ($res->affectedRows() > 0) {
						return $email;
					} else {
						$this->last_error = "Die Matrikelnummer existiert nicht.";
						return false;
					}
				} else {
					$this->last_error = "Die Passwort&auml;nderung konnte nicht durchgef&uuml;hrt werden. ".$conn->getLastError();
					return false;
				}
			} else {
				$this->last_error = "Keine Verbindung zur Datenbank.";
				return false;
			}
		}
		
		// Schreibt ein neues Passwort mit Zeichen aus dem Alphabet für den angegebenen Studenten ein,
		// und verschickt es als E-Mail
		// $len = Länge des Passwortes. Bei Len < 1 wird eine zufällige Länge zwischen 6 und 12
		// Liefert true oder false
		public function createPassForStudiAndMail(Connection $conn, $matrnr, $len) {
			$len = intval($len);
			if ($len <= 0) {
				$len = rand(6, 12);
			}
			$count = count($this->alpha);
			
			$pass = "";
			while ($len >= 0) {
				$pass .= $this->alpha[rand(0, $count-1)];
				$len = $len - 1;
			}
			
			$email = "";
			if ($email = $this->writepass($conn, $matrnr, $pass)) {
				Mailer::mailit($email, "Neues Passwort", "Dein Passwort lautet \"$pass\"\r\n\Du kannst Dich jetzt unter \r\n ".Config::PRAVER_ROOT_URL." \r\n mit Deiner Matrikelnummer und diesem Passwort anmelden.");
				return true;
			} else {
				return false;
			}
		} // function createPass
		
		// Erzeugt ein neues Passwort und liefert er zurück
		public function createNewPass($len) {
			$len = intval($len);
			if ($len <= 0) {
				$len = rand(6, 12);
			}
			$count = count($this->alpha);
			
			$pass = "";
			while ($len >= 0) {
				$pass .= $this->alpha[rand(0, $count-1)];
				$len = $len - 1;
			}
			
			return $pass;
		} // createNewPass
		
		// Überprüft, ob das angegebene Passwort minimum 6 Zeichen lang ist und
		// mindestens eine Zahl oder Sonderzeichen enthält
		// Wenn das Passwort OK ist, dann wird true zurückgeliefert
		public static function machesPasswordCriteria($pass) {
			$len = strlen($pass);
			if ($len < 6) {
				return false;
			}
			
			if (ctype_alpha($pass)) {
				return false;
			}
			
			return true;
		}
		
	} // CLASS

// TEST
/*
	$gen = new PassGenerator();
	$gen->echoAlpha();
	echo "<br>Passwords:<br>";
	echo $gen->createPass(0)."<br />";
	echo $gen->createPass(4)."<br />";
	echo $gen->createPass(40)."<br />";
*/
?>
