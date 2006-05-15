<?php

/********************************************************************************************************
 *            Datei: Util.php
 *           Author: Konstantin G. Hristozov
 *  Letzte �nderung: 14.01.2005
 *
 * Kurzbeschreibung: Diese Datei enth�lt Hilfsklassen. Die Klasse Util besteht aus Funktionen, die kleine
 *					 routine Operationen durchf�hren, und die anderen Klassen (Hashtable, UtilBuffer, UtilGroup)
 * 					 sind der einfachen Datenkapselung gewidmet.
 *					
 ********************************************************************************************************/

	include("libraries.php");
	
	class Util {
		
		// Sucht in einem sortieretem Array nach einem Element.
		// Liefert den Index des ersten gefundenen Elements oder -1 zur�ck.
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
		// Der Index wird zur�ckgeliefert, wenn das Element gefunden wurde, sonst -1
		public static function getFirstIndexOf($element, $array) {
			$count = count($array);
			if ($element == $array[0]) return 0;
			if ($element == $array[$count-1]) return $count-1;
			return Util::find($element, $array, 0, $count-1);
		}
		
		
		// Die Funktion bringt die eingegebene Kennzahl in vern�nftige Form.
		// Die Kennzahl darf keine Punkte enthalten. Es wird nur der Teil bis zum Punkt genommen.
		// Die Kennzahl kann alphanumerisch sein. Die Ziffern sollen aber deutlich mit einem Leerzeichen
		// von den Buchstaben getrennt werden. Beispiel: Aus "IGi294" kommt "IGi 294" heraus.
		// Also wenn die Kennzahl nicht nur aus Ziffern besteht, wird der Pr�fix mit Leerzeichen getrennt.
		// Die Funktion wird genutzt, um alle Kennzahlen gleich darzustellen und eine nat�rliche Suche
		// der Kennzahlen einfacher zu l�sen.
		public static function filterKennzahlForInsert($kennzahl) {
			$a = explode(".", $kennzahl);
			$kennzahl = $a[0];
			$len = strlen($kennzahl);
			
			// Wenn die Kennzahl nur aus Ziffern besteht
			if (ctype_digit($kennzahl)) return $kennzahl;
			
			// Wenn die Kennzahl einen Pr�fix hat
			for ($i = $len - 1; $i >= 0; $i--) {
				$z = substr($kennzahl, $i, 1); // Das aktuelle Zeichen in z kopieren
				if (!ctype_digit($z)) { // Wenn das kein Ziffer ist, ist das unser Pr�fixanfang
					// Leerzeichen ist zu ignorieren
					if (strcmp($z, " ") == 0 || $i == $len - 1) return $kennzahl;
					
					// Vor anderen Zeichen ist ein Leerzeichen einzuf�gen
					$s1 = substr($kennzahl, 0, $i+1);
					$s2 = substr($kennzahl, $i+1);
					$kennzahl = $s1." ".$s2;
					return $kennzahl;
				}
			}
			
			return $kennzahl;
		}
		
		// Hilfefunktion zum zuschneiden von Strings auf die gew�nschte L�nge
		// Die funktion liefert nur die ersten "maxlen"-Zeichen des gegebenen Strings oder
		// den ganzen String zur�ck, wenn die L�nge des Strings k�rzer ist als "maxlen".
		public static function getTruncated($string, $maxlen) {
			$len = strlen($string);
			if ($maxlen >= $len) {
				return $string;
			} else {
				return substr($string, 0, $maxlen);
			}
		}
		
		
		// Liefert true, wenn das Datum im richtigen Format eingegeben wurde und existiert.
		// Als Format zum Pr�fen wird ein Datum in TT.MM.JJJJ nach dem Gregorianischen Kalender benutzt
		// mit der Ausnahme, dass das Jahr 2800 als Nichtschaltjahr berechnet wird, um die 
		// Ungenauigkeit des Kalenders zu kompensieren.
		public static function checkDatum($datum) {
			$d = explode(".", $datum);
			if (count($d) != 3) return false;
			$d[0] = intval($d[0]);
			$d[1] = intval($d[1]);
			$d[2] = intval($d[2]);
			
			// �berpr�fen, ob das Jahr richtig ist
			// Das Jahr muss zwischen 1980 und 4000 liegen
			if ($d[2] < 1980 || $d[2] > 4000) return false;
			
			// Monat �berpr�fen
			if ($d[1] < 1 || $d[1] > 12) return false;
			
			// Datum �berpr�fen
			if ($d[0] < 1) return false;
			
			// Anzahl der Tage im Monat �berpr�fen
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
					// Datum genau nach dem Gregorianischen Kalender �berpr�fen
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
		
		// Liefert das heutige Datum als TT.MM.JJJJ String zur�ck
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
		// Zur�ckgeliefert wird der Wert der Variable als PHP double
		// Wenn die Zahl sich nicht in double umwandeln l�sst, wird 0 zur�ckgeliefert
		// In den $err-Parameter (ein ErrorQueue-Objekt) wird die Fehlermeldung hereingeschrieben, falls die Zahl
		// sch�tzungsweise umgewandelt wurde. Wenn $err=null ist, wird es ignoriert.
		public static function toDouble($var, $err) {
			$separators = ".,";
			$arr = preg_split("/[".$separators."]+/", $var);
			
			if (count($arr) > 1) {
				$vor = trim($arr[0]);
				$nach = trim($arr[1]);
				
				$str = $vor.".".$nach; // Zahl als Zeichenkette bilden
				$zahl = Util::convertToNumeric($vor).".".Util::convertToNumeric($nach); // Zahl umwandeln
				if (strcmp($str, $zahl) != 0) { // Wenn die nicht �bereinstimmen, dann Fehlemeldung ausgeben
					if ($err) $err->addError("Die Zahl $var wurde nicht als g&uuml;ltige Zahl erkannt. Sie wurde in ".$zahl." umgewandelt.");
				}
				return doubleval($zahl);
			} else {
				$zahl = doubleval($var);
				if (0 == $zahl && strcmp($var, $zahl) != 0) { // Wenn die nicht �bereinstimmen, dann Fehlemeldung ausgeben
					$zahl = doubleval(Util::convertToNumeric($var));
					if ($err) $err->addError("Die Zahl $var wurde nicht als g&uuml;ltige Zahl erkannt. Sie wurde in ".$zahl." umgewandelt.");
				}
				return $zahl;
			}
		}
		
		
		/* Diese Methode bekommt ein Array mit Schl�sseln (Kennzahlen). Sie sucht, nach
		 * Schl�sseln mit einem gegebenen Pr�fix. Diese Methode wird bei der Notenabgabe benutzt.
		 * Dort verwendet man ein Pr�fix+Matrikelnummer, um die unterschliedlichen Daten, die
		 * zu einer Matrikelnummer zugeordnet sind, zu �bertragen.
		 *
		 * Liefert die Sch�ssel zur�ck, die mit dem angegebenen Pr�fix anfangen.
		 * Der Pr�fix wird dabei abgeschnitten.
		 * Zur�ckgeliefert wird ein UtilBuffer nur mit den Schl�sseln mit abgeschnittenem Pr�fix
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
		 * Begrenzt eine Zeichenkette auf eine maximale Gr��e
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
		
		// F�gt die Daten aus dem Array zum Objekt ein
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
		
		// F�gt ein Element ein und achtet auf Wiederholungen dabei
		// Liefert true, wenn das Element eingef�gt wurde
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

	/* Diese Klasse ist f�r bestimmte Gruppierungen zust�ndig */
	/*
	* Sie unterst�tzt im Moment nur Fach-Gruppen. Man kann sie aber jederzeit erweitern.
	* Eine Gruppe ist so etwas wie ein Hashtable, aber f�r Schl�ssel werden Objekte benutzt.
	* Jedes Objekt k�nnte auf einer unterschiedlichen Art und Weise ein Schl�ssel sein. Zum Beispiel,
	* wenn man das UtilGroup f�r F�cher verwendet, sind Fach-Objekte die Sch�ssel f�r die Hashtabelle,
	* da aber zwei Fach-Objekte mit gleicher Kennzahl.Kennziffer-Kombination als identisch gelten,
	* werden diese beiden Schl�ssel von der UtilGroup beabsichtigt und alles wird zu dem
	* urspr�nglichen Fach-Objekt mit denselben Kennzahl.Kennziffer zugeordnet.
	* Zu einem Schl�ssel geh�rt ein UtilBuffer mit allen Objekten, die man zum Schl�ssel zugeordnet hat.
	*/
	class UtilGroup {
		
		const GROUP_FACH = 1; // Gruppierung von
		
		private $type;
		private $ressourceArr; // Array der Schl�sselobjekte
		private $ressourceVal; // Array der UtilBuffers mit den Werteobjekten, die zu jedem Schl�sselobjekt geh�ren
		
		private $count; // Anzahl der Gruppenschl�ssel
		
		private function __construct($type) {
			$this->type = $type;
			$this->ressourceArr = array();
			$this->ressourceVal = array();
			$this->count = 0;
		}
		
		/************** Funktionen der Gruppe vom Typ FACH_TERMIN **************/
		
		// Erzeugt ein Gruppierungsobjekt f�r F�cher, die mehrere Termine haben.
		// Hiermit wird ein Fach mit Kennzahl.Kennziffer als Gruppenschl�ssel f�r die Termine gespeichert
		// Das ist wichtig, wenn man alle Noten eines Fachs zu unterschiedlichen Terminen hat und eine
		// Zuordnung braucht. Benutz wird die Funktion von Dozent::getAlteFaecher()
		public static function createGroupByFach() {
			return new UtilGroup(UtilGroup::GROUP_FACH);
		}
		
		// Liefert den Index des Fachs mit Kennzahl und Kennziffer aus dem array oder -1, wenn nicht gefunden
		private function getArrayIndex($kennzahl, $kennziffer) {
			if ($this->type != UtilGroup::GROUP_FACH) return -1; // Typ �berpr�fen
			
			for ($i = 0; $i < $this->count; $i++) {
				if ($this->ressourceArr[$i]->getKennzahl() == $kennzahl && $this->ressourceArr[$i]->getKennziffer() == $kennziffer) {
					return $i;
				}
			}
			
			return -1;
		}
		
		public function addFach(Fach $f, $res) {
			
			if ($this->type != UtilGroup::GROUP_FACH) return false; // Typ �berpr�fen
			
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
		
		// Liefert die Anzahl der Schl�ssel zur�ck
		public function getCount() {
			return $this->count;
		}
		
		// Liefert den Schl�ssel der Gruppe auf Index $i
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