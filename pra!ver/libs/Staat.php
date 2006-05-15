<?php
/********************************************************************************************************
 *            Datei: Staat.php
 *           Author: David Hübner
 * 			 E-Mail: David@millbridge.de
 *  Letzte Änderung: 22.01.2006
 */
 
	include("libraries.php");

	class Staat {
	
		// Die Status-Konstanten
		const STATUS_AKTIV = 0;
		const STATUS_INAKTIV = 1;
	
		/* Liefert alle Staaten aus der Datenbank als Array Name, Status usw. zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumStaaten(Connection $conn) {
			$q = new DBQuery("SELECT s.StaatID, s.Name, s.Status, s.KontinentID, k.Name FROM staat s LEFT JOIN kontinent k ON (s.KontinentID=k.KontinentID) ORDER BY s.Name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}
		
		/* Liefert alle AKTIVEN Staaten aus der Datenbank als Array Name, Status usw. zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumAktiveStaaten(Connection $conn) {
			$q = new DBQuery("SELECT s.StaatID, s.Name, s.Status, s.KontinentID, k.Name FROM staat s LEFT JOIN kontinent k ON (s.KontinentID=k.KontinentID) WHERE Status='$Staat::STATUS_AKTIV' ORDER BY s.Name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}
		
		/* Liefert alle Kontinente aus der Datenbank als Array zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumKontinente(Connection $conn, ErrorQueue $err) {
			$q = new DBQuery("SELECT KontinentID, Name FROM kontinent ORDER BY Name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}

		// Fügt einen neuen Staat zur Datenbank ein. Liefert das eingefügte Studiengang-Objekt oder false zurück.
		public static function add(Connection $conn, $name, $kontinentID, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$status = Staat::STATUS_AKTIV;
			$string  = "INSERT INTO staat(Name, Status, KontinentID) ";
			$string .= "VALUES('".$name."', '".$status."', '".$kontinentID."')";
			$q  = new DBQuery($string);
			
			if ($result = $conn->executeQuery($q)) {
				return $result;
			} else {
				return false;
			}
		}
		
		// Löscht einen Staat bzw. setzt ihn auf inaktiv. Liefert das eingefügte Studiengang-Objekt oder false zurück.
		public static function delete(Connection $conn, $staatID, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$query = new DBQuery("SELECT adrStaatID FROM unternehmen WHERE adrStaatID=$staatID");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 0) {
					$query = new DBQuery("DELETE FROM staat WHERE StaatID=$staatID");
					return $conn->executeQuery($query);
				}	else {
					$query = new DBQuery("UPDATE staat SET Status='1' WHERE StaatID=$staatID");
					$err->addError("Staat wird in einem oder mehreren Unternehmen referenziert. Er wird daher nur deaktiviert.");
					$conn->executeQuery($query);
				}
				return false;
			} else {
				$query = new DBQuery("UPDATE staat SET Status='1' WHERE StaatID=$staatID");
				$err->addError("Staat wird in einem oder mehr Unternehmen referenziert. Er wird daher nur deaktiviert.");
				$conn->executeQuery($query);
			}
			return false;
		}
	
		public static function setAktiv(Connection $conn, $staatID, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$neuerStatus = Staat::STATUS_AKTIV;
			$q = new DBQuery("UPDATE staat SET Status='$neuerStatus' WHERE StaatID='$staatID'");
					if (!$conn->executeQuery($q)) {
						$err->addError($conn->getLastError());
						return false;
					}
		}
		
		/* Übersetzt den Abschluss als String */
		public static function translateStatusAsString($status) {
			switch ($status) {
				case Staat::STATUS_AKTIV : return "Status aktiv";
				case Staat::STATUS_INAKTIV : return "Status inaktiv";
				default: return "Undefiniert ".$status;
			}
		}
		

	} // END OF CLASS

?>