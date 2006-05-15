<?php

/********************************************************************************************************
 *            Datei: Util.php
 *           Author: Konstantin G. Hristozov
 *  Letzte Änderung: 14.01.2005
 *
 * Kurzbeschreibung: Diese Datei enthält Hilfsklassen. Die Klasse Util besteht aus Funktionen, die kleine
 *					 routine Operationen durchführen, und die anderen Klassen (Hashtable, UtilBuffer, UtilGroup)
 * 					 sind der einfachen Datenkapselung gewidmet.
 *					
 ********************************************************************************************************/

	include("libraries.php");
	
	class Util {
		
		// Sucht in einem sortieretem Array nach einem Element.
		// Liefert den Index des ersten gefundenen Elements oder -1 zurück.
		private static function find($element, $arr, $left, $right) {
			
			if ($right <= $left + 1) return -1;
			
			$index = intval($left + ($right - $left) / 2);
			
			if ($element == $arr[$index]) {
				return $index;
			}
			
			if ($arr[$index] > $element) {
				$right = $index;
				return Util::find($element, $arr, $left, $right);
			}
			
			if ($arr[$index] < $element) {
				$left = $index;
				return Util::find($element, $arr, $left, $right);
			}
			
			return -1;
		}
		
		
		// Sucht in einem array nach dem ersten Vorkommen eines Elementes
		// Normale Vergleiche verden benutzt
		// Der Index wird zurückgeliefert, wenn das Element gefunden wurde, sonst -1
		public static function getFirstIndexOf($element, $array) {
			$count = count($array);
			if ($element == $array[0]) return 0;
			if ($element == $array[$count-1]) return $count-1;
			return Util::find($element, $array, 0, $count-1);
		}
		
		
		// Die Funktion bringt die eingegebene Kennzahl in vernünftige Form.
		// Die Kennzahl darf keine Punkte enthalten. Es wird nur der Teil bis zum Punkt genommen.
		// Die Kennzahl kann alphanumerisch sein. Die Ziffern sollen aber deutlich mit einem Leerzeichen
		// von den Buchstaben getrennt werden. Beispiel: Aus "IGi294" kommt "IGi 294" heraus.
		// Also wenn die Kennzahl nicht nur aus Ziffern besteht, wird der Präfix mit Leerzeichen getrennt.
		// Die Funktion wird genutzt, um alle Kennzahlen gleich darzustellen und eine natürliche Suche
		// der Kennzahlen einfacher zu lösen.
		public static function filterKennzahlForInsert($kennzahl) {
			$a = explode(".", $kennzahl);
			$kennzahl = $a[0];
			$len = strlen($kennzahl);
			
			// Wenn die Kennzahl nur aus Ziffern besteht
			if (ctype_digit($kennzahl)) return $kennzahl;
			
			// Wenn die Kennzahl einen Präfix hat
			for ($i = $len - 1; $i >= 0; $i--) {
				$z = substr($kennzahl, $i, 1); // Das aktuelle Zeichen in z kopieren
				if (!ctype_digit($z)) { // Wenn das kein Ziffer ist, ist das unser Präfixanfang
					// Leerzeichen ist zu ignorieren
					if (strcmp($z, " ") == 0 || $i == $len - 1) return $kennzahl;
					
					// Vor anderen Zeichen ist ein Leerzeichen einzufügen
					$s1 = substr($kennzahl, 0, $i+1);
					$s2 = substr($kennzahl, $i+1);
					$kennzahl = $s1." ".$s2;
					return $kennzahl;
				}
			}
			
			return $kennzahl;
		}
		
		// Hilfefunktion zum zuschneiden von Strings auf die gewünschte Länge
		// Die funktion liefert nur die ersten "maxlen"-Zeichen des gegebenen Strings oder
		// den ganzen String zurück, wenn die Länge des Strings kürzer ist als "maxlen".
		public static function getTruncated($string, $maxlen) {
			$len = strlen($string);
			if ($maxlen >= $len) {
				return $string;
			} else {
				return substr($string, 0, $maxlen);
			}
		}
		
		
		// Liefert true, wenn das Datum im richtigen Format eingegeben wurde und existiert.
		// Als Format zum Prüfen wird ein Datum in TT.MM.JJJJ nach dem Gregorianischen Kalender benutzt
		// mit der Ausnahme, dass das Jahr 2800 als Nichtschaltjahr berechnet wird, um die 
		// Ungenauigkeit des Kalenders zu kompensieren.
		public static function checkDatum($datum) {
			$d = explode(".", $datum);
			if (count($d) != 3) return false;
			$d[0] = intval($d[0]);
			$d[1] = intval($d[1]);
			$d[2] = intval($d[2]);
			
			// Überprüfen, ob das Jahr richtig ist
			// Das Jahr muss zwischen 1980 und 4000 liegen
			if ($d[2] < 1980 || $d[2] > 4000) return false;
			
			// Monat überprüfen
			if ($d[1] < 1 || $d[1] > 12) return false;
			
			// Datum überprüfen
			if ($d[0] < 1) return false;
			
			// Anzahl der Tage im Monat überprüfen
			switch($d[1]) {
				case 1 : if ($d[0] > 31) return false; break;
				case 3 : if ($d[0] > 31) return false; break;
				case 4 : if ($d[0] > 30) return false; break;
				case 5 : if ($d[0] > 31) return false; break;
				case 6 : if ($d[0] > 30) return false; break;
				case 7 : if ($d[0] > 31) return false; break;
				case 8 : if ($d[0] > 31) return false; break;
				case 9 : if ($d[0] > 30) return false; break;
				case 10 : if ($d[0] > 31) return false; break;
				case 11 : if ($d[0] > 30) return false; break;
				case 12 : if ($d[0] > 31) return false; break;
				case 2 :  // Anzahl der Tage im Februa berechnen
					// Datum genau nach dem Gregorianischen Kalender überprüfen
					$schaltjahrConst1 = ($d[2] % 4 == 0 ? true : false);
					$schaltjahrConst2 = ($d[2] % 100 == 0 ? false : true);
					$schaltjahrConst3 = ($d[2] % 400 == 0 ? true : false);
					$schaltjahrConst4 = ($d[2] == 2800 ? false : true);
					
					// Wenn das ein Schaltjahr ist, dann darf Februar 29 Tage haben
					if (($schaltjahrConst3 && $schaltjahrConst4) || ($schaltjahrConst1 && $schaltjahrConst2)) {
						if ($d[0] > 29) return false;
					} else { // Sonst kann Februa nur 28 Tage haben
						if ($d[0] > 28) return false;
					}
					break;
				
			} // switch
			
			return true;
		}
		
		// Vergleicht zwei Arrays mit Daten
		// array[0] = Tag
		// array[1] = Monat
		// array[2] = Jahr
		// Bei Gleichheit 0, wenn 1 vor 2 liegt negativ, wenn 1 nach 2 liegt positiv 
		private static function compareDaten($datum1, $datum2) {
			if ($datum1[2] < $datum2[2]) { 
				 return -1;
			} else if ($datum1[2] > $datum2[2]) {
				return 1;
			} else {
				if ($datum1[1] < $datum2[1]) {
					return -1;
				} else if ($datum1[1] > $datum2[1]) {
					return 1;
				} else {
					if ($datum1[0] < $datum2[0]) {
						return -1;
					} else if ($datum1[0] > $datum2[0]) {
						return 1;
					} else {
						return 0;
					} // if-else TAG
				} // if-else MONAT
			} // if-else JAHR
			
		} // compareDaten
		
		// Vergleicht zwei String Daten im Format TT.MM.JJJJ.
		// Bei Gleichheit 0, wenn 1 vor 2 liegt negativ (-1), wenn 1 nach 2 liegt positiv (+1)
		// Bei Fehler: -1000
		public static function compareStringDaten($date1, $date2) {
			$arr1 = explode(".", $date1);
			$arr2 = explode(".", $date2);
			if (false == Util::checkDatum($date1)) return -1000;
			if (false == Util::checkDatum($date2)) return -1000;
			return Util::compareDaten($arr1, $arr2);
		}
		
		// Liefert das heutige Datum als TT.MM.JJJJ String zurück
		public static function getHeutigesDatum() {
			$date = getdate();
			return $date['mday'].".".$date['mon'].".".$date['year'];
		}

		// Wandelt das String in numerische Zeichenkette um
		private function convertToNumeric($val) {
			$ret = "";
			$len = strlen($val);
			if($len) {
				for ($i = 0; $i < $len; $i++) {
					$z = $val[$i];
					if (ctype_digit($z)) {
						$ret .= $z;
					} else {
						if ("" != $ret) {
							if (strcmp($z, " ") != 0) {
								break;
							}
						} // if
					} // if-else
				} // for
			} else {
				$ret =  "0";
			} // if-else
			
			if ($ret == "") return "0";
			
			return $ret;
		} // function
		
		// Wandelt die gegebene Variable in einen Double-Wert um.
		// Die Funktion unterscheidet nicht zwischen 1.5 und 1,5
		// Zurückgeliefert wird der Wert der Variable als PHP double
		// Wenn die Zahl sich nicht in double umwandeln lässt, wird 0 zurückgeliefert
		// In den $err-Parameter (ein ErrorQueue-Objekt) wird die Fehlermeldung hereingeschrieben, falls die Zahl
		// schätzungsweise umgewandelt wurde. Wenn $err=null ist, wird es ignoriert.
		public static function toDouble($var, $err) {
			$separators = ".,";
			$arr = preg_split("/[".$separators."]+/", $var);
			
			if (count($arr) > 1) {
				$vor = trim($arr[0]);
				$nach = trim($arr[1]);
				
				$str = $vor.".".$nach; // Zahl als Zeichenkette bilden
				$zahl = Util::convertToNumeric($vor).".".Util::convertToNumeric($nach); // Zahl umwandeln
				if (strcmp($str, $zahl) != 0) { // Wenn die nicht übereinstimmen, dann Fehlemeldung ausgeben
					if ($err) $err->addError("Die Zahl $var wurde nicht als g&uuml;ltige Zahl erkannt. Sie wurde in ".$zahl." umgewandelt.");
				}
				return doubleval($zahl);
			} else {
				$zahl = doubleval($var);
				if (0 == $zahl && strcmp($var, $zahl) != 0) { // Wenn die nicht übereinstimmen, dann Fehlemeldung ausgeben
					$zahl = doubleval(Util::convertToNumeric($var));
					if ($err) $err->addError("Die Zahl $var wurde nicht als g&uuml;ltige Zahl erkannt. Sie wurde in ".$zahl." umgewandelt.");
				}
				return $zahl;
			}
		}
		
		
		/* Diese Methode bekommt ein Array mit Schlüsseln (Kennzahlen). Sie sucht, nach
		 * Schlüsseln mit einem gegebenen Präfix. Diese Methode wird bei der Notenabgabe benutzt.
		 * Dort verwendet man ein Präfix+Matrikelnummer, um die unterschliedlichen Daten, die
		 * zu einer Matrikelnummer zugeordnet sind, zu übertragen.
		 *
		 * Liefert die Schüssel zurück, die mit dem angegebenen Präfix anfangen.
		 * Der Präfix wird dabei abgeschnitten.
		 * Zurückgeliefert wird ein UtilBuffer nur mit den Schlüsseln mit abgeschnittenem Präfix
		 * Die Methode arbeitet case sensitive
		 */
		public static function extractKeysFromPrefixedArray($prefix, $array) {
			$keys = array_keys($array);
			$count = count($keys);

			//echo $count." keys<br />";

			$buffer = new UtilBuffer();
			for ($i = 0; $i < $count; $i++) {
				$s1 = substr($keys[$i], 0, strlen($prefix));
				if (strcmp($s1, $prefix) == 0) {
					$s2 = substr($keys[$i], strlen($prefix));
					$buffer->add($s2);
				}
			}
			return $buffer;
		}
		
		
		/*
		 * Begrenzt eine Zeichenkette auf eine maximale Größe
		 */
		public static function truncateStringLength($string, $maxlen) {
			if ($maxlen < 6) {
				return $string;
			} else {
				$len = strlen($string);
				if ($len <= $maxlen) {
					return $string;
				} else {
					return substr($string, $maxlen - 3)."...";
				}
			}
		}
		
		
	} // CLASS
	

/***************************************************************************************************/	
/************************************* CLASS UTILBUFFER ********************************************/	
/***************************************************************************************************/	
	
	class UtilBuffer {
		
		private $insert_index;
		private $count;
		private $buffer;
		
		public function __construct() {
			$this->insert_index = 0;
			$this->count = 0;
			$this->buffer = array();
		}
		
		public function getBuffer() {
			return $this->buffer;
		}
		
		public function setBuffer($array) {
			$this->buffer = $array;
			$this->count = count($array);
			$this->insert_index = $this->count;
		}
		
		// Liefert das Element auf dem Index
		public function get($index) {
			return $this->buffer[$index];
		}
		
		public function getCount() {
			return $this->count;
		}
		
		// Fügt die Daten aus dem Array zum Objekt ein
		public function addArray($arr) {
			$c = count($arr);
			
			for ($i = 0; $i < $c; $i++) {
				$this->buffer[$this->insert_index] = $arr[$i];
				$this->insert_index++;
				$this->count++;
			} // for
		}
		
		public function add($el) {
			$this->buffer[$this->insert_index] = $el;
			$this->insert_index++;
			$this->count++;
		}
		
		// Fügt ein Element ein und achtet auf Wiederholungen dabei
		// Liefert true, wenn das Element eingefügt wurde
		// und false, wenn das Element schon im Buffer vorhanden ist
		public function addUnique($el) {
			if (in_array($el, $this->buffer)) return false;
			else $this->add($el);
		}
		
	} // CLASS UtilBuffer


	class Hashtable {
		private $hashtable;
		
		public function __construct() {
			$this->hashtable = array();
		}
		
		public function setValue($key, $value) {
			$this->hashtable[$key] = $value;
		}
		
		public function getValue($key) {
			//if (isset($this->hashtable[$key])) {
				return $this->hashtable[$key];
			//} else {
			//	return '';
			//}
		}
		
		public function hasKey($key) {
			return array_key_exists($key, $this->hashtable);
		}
		
		public function getCount() {
			return count($this->hashtable);
		}
		
		public function getAsArray() {
			/*
			$arr = array();
			$keys = array_keys($this->hashtable);
			$len = count($keys);
			
			for ($i = 0; $i < $len; $i++) {
				$arr[$i] = $this->hashtable[$keys[$i]];
			} // for
		
			return $arr;
			*/
			return array_values($this->hashtable);
		}
		
	} // CLASS Hashtable
	
/***************************************************************************************************/
/******************************************* UTIL GROUP ********************************************/
/***************************************************************************************************/

	/* Diese Klasse ist für bestimmte Gruppierungen zuständig */
	/*
	* Sie unterstützt im Moment nur Fach-Gruppen. Man kann sie aber jederzeit erweitern.
	* Eine Gruppe ist so etwas wie ein Hashtable, aber für Schlüssel werden Objekte benutzt.
	* Jedes Objekt könnte auf einer unterschiedlichen Art und Weise ein Schlüssel sein. Zum Beispiel,
	* wenn man das UtilGroup für Fächer verwendet, sind Fach-Objekte die Schüssel für die Hashtabelle,
	* da aber zwei Fach-Objekte mit gleicher Kennzahl.Kennziffer-Kombination als identisch gelten,
	* werden diese beiden Schlüssel von der UtilGroup beabsichtigt und alles wird zu dem
	* ursprünglichen Fach-Objekt mit denselben Kennzahl.Kennziffer zugeordnet.
	* Zu einem Schlüssel gehört ein UtilBuffer mit allen Objekten, die man zum Schlüssel zugeordnet hat.
	*/
	class UtilGroup {
		
		const GROUP_FACH = 1; // Gruppierung von
		
		private $type;
		private $ressourceArr; // Array der Schlüsselobjekte
		private $ressourceVal; // Array der UtilBuffers mit den Werteobjekten, die zu jedem Schlüsselobjekt gehören
		
		private $count; // Anzahl der Gruppenschlüssel
		
		private function __construct($type) {
			$this->type = $type;
			$this->ressourceArr = array();
			$this->ressourceVal = array();
			$this->count = 0;
		}
		
		/************** Funktionen der Gruppe vom Typ FACH_TERMIN **************/
		
		// Erzeugt ein Gruppierungsobjekt für Fächer, die mehrere Termine haben.
		// Hiermit wird ein Fach mit Kennzahl.Kennziffer als Gruppenschlüssel für die Termine gespeichert
		// Das ist wichtig, wenn man alle Noten eines Fachs zu unterschiedlichen Terminen hat und eine
		// Zuordnung braucht. Benutz wird die Funktion von Dozent::getAlteFaecher()
		public static function createGroupByFach() {
			return new UtilGroup(UtilGroup::GROUP_FACH);
		}
		
		// Liefert den Index des Fachs mit Kennzahl und Kennziffer aus dem array oder -1, wenn nicht gefunden
		private function getArrayIndex($kennzahl, $kennziffer) {
			if ($this->type != UtilGroup::GROUP_FACH) return -1; // Typ überprüfen
			
			for ($i = 0; $i < $this->count; $i++) {
				if ($this->ressourceArr[$i]->getKennzahl() == $kennzahl && $this->ressourceArr[$i]->getKennziffer() == $kennziffer) {
					return $i;
				}
			}
			
			return -1;
		}
		
		public function addFach(Fach $f, $res) {
			
			if ($this->type != UtilGroup::GROUP_FACH) return false; // Typ überprüfen
			
			$i = $this->getArrayIndex($f->getKennzahl(), $f->getKennziffer());
			if ($i == -1) {
				$count = $this->count;
				$this->ressourceArr[$count] = $f;
				$this->ressourceVal[$count] = new UtilBuffer();
				$this->ressourceVal[$count]->add($res);
				$this->count++;
			} else {
				$this->ressourceVal[$i]->addUnique($res);
			}
			
			return true;
		}
		
		// Liefert die Anzahl der Schlüssel zurück
		public function getCount() {
			return $this->count;
		}
		
		// Liefert den Schlüssel der Gruppe auf Index $i
		public function getGroupKey($i) {
			return $this->ressourceArr[$i];
		}
		
		// Liefert den UtilBuffer mit den Werten der Gruppe auf index $i
		public function getGroupValues($i) {
			return $this->ressourceVal[$i];
		}
		
	} // CLASS
	
/***************************************************************************************************/

?>