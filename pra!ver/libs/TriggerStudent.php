<?php


	include("libraries.php");

	class TriggerStudent {

		const NEUANMELDUNG_LOGINTOLERANZ = 48;	// 2 Stunden Zeit von der Neuanmeldung bis zum Login.
												// �berschreitung dieser Toleranz l�scht den Student.
		private function __constuct() {}
		
		/*
		 * Markiert den Student als neu angemeldet.
		 * Wenn er sich sich innerhalb von 2 Stunden nicht einloggt, wird er automatisch gel�scht.
		 */
		public static function OnNeuanmeldung(Connection $conn, ErrorQueue $err, $matrnr) {
			$matrnr = intval($matrnr);
			$deleteAfter = strtotime("+".TriggerStudent::NEUANMELDUNG_LOGINTOLERANZ." hours"); // In 2 Stunden
			$q = new DBQuery("INSERT INTO neuanmeldung(MatrNr, DeleteAfter) VALUES($matrnr, $deleteAfter)");
			if (false == $conn->executeQuery($q)) {
				ErrorQueue::SystemErrorConnection("TriggerStudent.php", "OnNeuanmeldung", $conn);
				return false;
			}
			
			return true;
		}
		
		/*
		 * Jedes Mal, wenn sich der Student anmeldet, wird diese Funktion ausgef�hrt.
		 * Hier wird der Student aus der Neuanmeldungen-Tabelle gel�scht.
		 */
		public static function OnLogin(Connection $conn, ErrorQueue $err, $matrnr) {
			$matrnr = intval($matrnr);
			$q = new DBQuery("DELETE FROM neuanmeldung WHERE MatrNr=$matrnr");
			if (false == $conn->executeQuery($q)) {
				ErrorQueue::SystemErrorConnection("TriggerStudent.php", "OnLogin", $conn);
				return false;
			}
			return true;
		}
		
		/*
		 * Sucht unter den Neuanmeldungen die Studenten heraus, die sich l�nger als 2 Stunden nicht angemeldet haben
		 * und entfernt sie aus der Tabelle student.
		 */
		public static function OnCheckNeuanmeldungen(Connection $conn, ErrorQueue $err) {
			
			$returnvalue = true;
			$jetzt = strtotime("now");
			$q = new DBQuery("SELECT MatrNr FROM neuanmeldung WHERE DeleteAfter < $jetzt");

			if ($result = $conn->executeQuery($q)) {
				while ($r = $result->getNextRow()) {
					
					$delq = new DBQuery("DELETE FROM student WHERE MatrNr=".$r[0]);
					$delq2 = new DBQuery("DELETE FROM neuanmeldung WHERE MatrNr=$matrnr");
					if (false == $conn->executeQuery($delq)) {
						ErrorQueue::SystemErrorConnection("TriggerStudent.php", "OnCheckNeuanmeldungen", $conn);
						$returnvalue = false;
					}
				}
			} else {
				ErrorQueue::SystemErrorConnection("TriggerStudent.php", "OnCheckNeuanmeldungen", $conn);
				$returnvalue = false;
			}
			
			return $returnvalue;
		}
		
		
	}

?>