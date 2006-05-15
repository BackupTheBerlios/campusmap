<?php
/********************************************************************************************************
 *            Datei: Fachbereich.php
 *           Author: David Hübner
 * 			 E-Mail: David@millbridge.de
 *  Letzte Änderung: 22.01.2006
 */
 
	include("libraries.php");

	class Fachbereich {
	
		// Die Status-Konstanten
		const STATUS_AKTIV = 0;
		const STATUS_INAKTIV = 1;
	
		/* Liefert alle Fachbereiche aus der Datenbank als Array Name, Status usw. zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumFachbereiche(Connection $conn) {
			$q = new DBQuery("SELECT ID, Name, Status FROM fachbereich ORDER BY Name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}
		
		/* Liefert alle AKTIVEN Fachbereiche aus der Datenbank als Array Name, Status usw. zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumAktiveFachbereiche(Connection $conn) {
			$q = new DBQuery("SELECT ID, Name, Status FROM fachbereich WHERE Status='$Fachbereich::STATUS_AKTIV' ORDER BY Name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}

		// Fügt einen neuen Fachbereich zur Datenbank ein. Liefert das eingefügte Fachbereich-Objekt oder false zurück.
		public static function add(Connection $conn, $name, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$q = new DBQuery("SELECT name FROM fachbereich WHERE name='".$name."'");
			if ($result = $conn->executeQuery($q)) {
				if ($result->rowsCount() > 0) {
					$err->addError("Ein Fachbereich mit dem Namen exisitiert bereits.");
					return false;
				}
			}
			$status = Fachbereich::STATUS_AKTIV;
			$string  = "INSERT INTO fachbereich(Name, Status) ";
			$string .= "VALUES('".$name."', '".$status."')";
			$q  = new DBQuery($string);
			
			if ($result = $conn->executeQuery($q)) {
				return $result;
			} else {
				return false;
			}
		}
		
		// Löscht einen Fachbereich bzw. setzt sie auf inaktiv. Liefert das eingefügte Fachbereich-Objekt oder false zurück.
		public static function delete(Connection $conn, $ID, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$query = new DBQuery("SELECT StudiengangID FROM studiengang WHERE FachbereichID=$ID");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 0) {
					$query = new DBQuery("DELETE FROM fachbereich WHERE ID=$ID");
					return $conn->executeQuery($query);
				}	else {
					$query = new DBQuery("UPDATE fachbereich SET Status='1' WHERE ID=$ID");
					$err->addError("Fachbereich wird in einem oder mehreren Unternehmen referenziert. Sie wird daher nur deaktiviert.");
					$conn->executeQuery($query);
				}
				return false;
			} else {
				$query = new DBQuery("UPDATE fachbereich SET Status='1' WHERE ID=$ID");
				$err->addError("Fachbereich wird in einem oder mehr Unternehmen referenziert. Er wird daher nur deaktiviert.");
				$conn->executeQuery($query);
			}
			return false;
		}
		
		public static function setName(Connection $conn, $ID, $neuerName, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			// Überprüfen bereits ein Studiengang mit so einem Namen existiert
			$q = new DBQuery("SELECT name FROM fachbereich WHERE name='".$neuerName."'");
			if ($result = $conn->executeQuery($q)) {
				if ($result->rowsCount() > 0) {
					$this->last_error = "Ein Fachbereich mit dem Namen exisitiert bereits.";
					return false;
				}
			}
			$q = new DBQuery("UPDATE fachbereich SET name='".$neuerName."' WHERE ID='".$ID."'");
					if (!$conn->executeQuery($q)) {
						$err->addError($conn->getLastError());
						return false;
					}
		}
		
		public static function getName(Connection $conn, $ID, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			// Überprüfen bereits ein Studiengang mit so einem Namen existiert
			$q = new DBQuery("SELECT name FROM fachbereich WHERE ID='".$ID."'");
			if ($result = $conn->executeQuery($q)) {
				if ($result->rowsCount() == 1 && $r = $result->getNextRow()) {
					return $r[0];
				} else {
					$err->addError("Die ID weist auf keinen Fachbereich.");
					return false;
				}
			}
		}
		
		public static function setAktiv(Connection $conn, $ID, ErrorQueue $err) {
			if (!$conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			$neuerStatus = Fachbereich::STATUS_AKTIV;
			$q = new DBQuery("UPDATE fachbereich SET Status='$neuerStatus' WHERE ID='$ID'");
					if (!$conn->executeQuery($q)) {
						$err->addError($conn->getLastError());
						return false;
					}
		}
		
		/* Übersetzt den Abschluss als String */
		public static function translateStatusAsString($status) {
			switch ($status) {
				case Fachbereich::STATUS_AKTIV : return "Status aktiv";
				case Fachbereich::STATUS_INAKTIV : return "Status inaktiv";
				default: return "Undefiniert ".$status;
			}
		}
		

	} // END OF CLASS

?>