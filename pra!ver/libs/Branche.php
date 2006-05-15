<?php
/********************************************************************************************************
 *            Datei: Branche.php
 *           Author: David Hübner
 * 			 E-Mail: David@millbridge.de
 *  Letzte Änderung: 22.01.2006
 */
 
	include("libraries.php");

	class Branche {
	
		// Die Status-Konstanten
		const STATUS_AKTIV = 0;
		const STATUS_INAKTIV = 1;
	
		/* Liefert alle Branchen aus der Datenbank als Array Name, Status usw. zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumBranchen(Connection $conn) {
			$q = new DBQuery("SELECT BrancheID, Name, Status FROM branche ORDER BY Name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}
		
		/* Liefert alle AKTIVEN Branchen aus der Datenbank als Array Name, Status usw. zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumAktiveBranchen(Connection $conn) {
			$q = new DBQuery("SELECT BrancheID, Name, Status FROM branche WHERE Status='$Branche::STATUS_AKTIV' ORDER BY Name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}

		// Fügt eine neue Branche zur Datenbank ein. Liefert das eingefügte Branche-Objekt oder false zurück.
		public static function add(Connection $conn, $name, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$status = Branche::STATUS_AKTIV;
			$string  = "INSERT INTO branche(Name, Status) ";
			$string .= "VALUES('".$name."', '".$status."')";
			$q  = new DBQuery($string);
			
			if ($result = $conn->executeQuery($q)) {
				return $result;
			} else {
				return false;
			}
		}
		
		// Löscht eine Branche bzw. setzt sie auf inaktiv. Liefert das eingefügte Branche-Objekt oder false zurück.
		public static function delete(Connection $conn, $BrancheID, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$query = new DBQuery("SELECT BrancheID FROM unternehmen WHERE BrancheID=$BrancheID");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 0) {
					$query = new DBQuery("DELETE FROM branche WHERE BrancheID=$BrancheID");
					return $conn->executeQuery($query);
				}	else {
					$query = new DBQuery("UPDATE branche SET Status='1' WHERE BrancheID=$BrancheID");
					$err->addError("Branche wird in einem oder mehreren Unternehmen referenziert. Sie wird daher nur deaktiviert.");
					$conn->executeQuery($query);
				}
				return false;
			} else {
				$query = new DBQuery("UPDATE branche SET Status='1' WHERE BrancheID=$BrancheID");
				$err->addError("Branche wird in einem oder mehr Unternehmen referenziert. Er wird daher nur deaktiviert.");
				$conn->executeQuery($query);
			}
			return false;
		}
	
		public static function setAktiv(Connection $conn, $BrancheID, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$neuerStatus = Branche::STATUS_AKTIV;
			$q = new DBQuery("UPDATE branche SET Status='$neuerStatus' WHERE BrancheID='$BrancheID'");
					if (!$conn->executeQuery($q)) {
						$err->addError($conn->getLastError());
						return false;
					}
		}
		
		/* Übersetzt den Abschluss als String */
		public static function translateStatusAsString($status) {
			switch ($status) {
				case Branche::STATUS_AKTIV : return "Status aktiv";
				case Branche::STATUS_INAKTIV : return "Status inaktiv";
				default: return "Undefiniert ".$status;
			}
		}
		

	} // END OF CLASS

?>