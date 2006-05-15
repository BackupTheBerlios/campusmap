<?php
	
	/*
	 * Die Klasse enthält Sortierungsroutinen
	 * 
	 * 
	 * SORT_DIRECTION wird nur beim Sortieren von bestimmeten Typen unterstützt.
	 * 
	 * 
	 * 
	 */
	
	include("libraries.php");
	
	class SortingPool {
		
		const TYPE_RESULTSET_SORT = 1;		// Hier arbeiten wir mit einem ResultSet von Fächern. Sortierung nach Kennzahl und Kennziffer
		const TYPE_FAECHER_SORT = 2;		// Hier arbeiten wir mit einem Fächer-Array und sortieren nach Kennzahl und Kennziffer
		const TYPE_STRINGSARRAY_SORT = 3;	// Hier wird ein Array mit Strings sortieren
		const TYPE_DATEARRAY_SORT = 4;		// Hier wird ein Array mit gültigen Datum-Strings sortiert
		
		const SORT_DIRECTION_ASCENDING = 1; // Aufsteigende Sortierung
		const SORT_DIRECTION_DESCENDING = 2;// Absteigende Sortierung
		
		private $type;			// Typ der Ressource
		private $ressource;		// Die Ressource, die zu sortieren ist
		private $last_error;	// Der letzte fehler
		private $direction;

		private $indexOrder; // Array der Sortierungsinizes.
							 // Hier wird die eigentliche reihenfolge festgehalten, datmit Zuordnungen zu Indizes wieder hergestellt sein können
		
		// PRIVATER KONSTRUKTOR: Die Factory Methoden sind stattdessen zu benutzen
		private function __construct($type, $ressource) {
			$this->ressource = $ressource;
			$this->type = $type;
			$this->indexOrder = array();
			$this->direction = SortingPool::SORT_DIRECTION_ASCENDING;
		}
		
		public static function createFromResultSet(ResultSet $result) {
			return new SortingPool(SortingPool::TYPE_RESULTSET_SORT, $result);
		}

		public static function createFromFaecherArray($faecher) {
			return new SortingPool(SortingPool::TYPE_FAECHER_SORT, $faecher);
		}
		
		public static function createFromStringsArray($aStrings) {
			return new SortingPool(SortingPool::TYPE_STRINGSARRAY_SORT, $aStrings);
		}
		
		public static function createFromDatesArray($aDates, $direction) {
			$sp = new SortingPool(SortingPool::TYPE_DATEARRAY_SORT, $aDates);
			$sp->direction = $direction;
			return $sp;
		}
		
		
		/*
		 * Sortiert die Ressource passend und füllt die einzelnen Zeilen in "outBuffer" ein.
		 * Liefert normallerweise true zurück. Wenn der Typ nicht definiert ist, wird false zurückgeliefert.
		 */
		public function sortIt(UtilBuffer $outBuffer) {
			if ($outBuffer == null) return false;
			switch ($this->type) {
				case SortingPool::TYPE_RESULTSET_SORT :
					return $this->sortResultSet($outBuffer);
					break;
				case SortingPool::TYPE_FAECHER_SORT :
					return $this->sortFaecherArray($outBuffer);
					break;
				case SortingPool::TYPE_STRINGSARRAY_SORT :
					return $this->sortStrings($outBuffer);
					break;
				case SortingPool::TYPE_DATEARRAY_SORT :
					return $this->sortDates($outBuffer);
					break;
			}
			
			return false;
		}
		
		/*
		 * Liefert das Array mit den Sortierungsindizes zurück.
		 * Mit diesem Array kann man schnell ein anderes Array passend umordnen.
		 * Man verwendet die Funktion Util::orderArrayUsingIndexOrder, um ein array an einer vorhandenen Sortierung
		 * anzupassen.
		 * Beispiel für ein IndexOrder-Array:
		 * [0] = 2 -> Bedeutet: Auf index 0 muss das stehen, was in dem Werte-Array auf Index 2 steht.
		 * [1] = 1
		 * [2] = 0
		 * [3] = 4
		 * [4] = 3
		 */
		public function getIndexOrder() {
			return $this->indexOrder;
		}
		
		
		/*
		 * Erzeugt eine neues Array vom $aInput-Array geordnet nach dem gegebenen $aIndexOrder
		 * Sieh. noch getIndexOrder()
		 */
		public static function orderArrayUsingIndexOrder($aInput, $aIndexOrder) {
			
			$count = count($aIndexOrder);
			$retarr = array();
			for ($i = 0; $i < $count; $i++) {
				$retarr[$i] = $aInput[$aIndexOrder[$i]];
			} // for
			
			return $retarr;
		}
		
		
		/*
		 * Liefert den letzten Fehler als String zurück
		 */
		public function getLastError() {
			return $this->last_error;
		}
		
/**********************************************************************************************************/
/************************************** PRIVATE FUNKTIONEN ************************************************/
/**********************************************************************************************************/

		/*
		 * Sortiert die Ressource als Array von Datum-Strings im Format TT.MM.JJJJ
		 */
		 private function sortDates(UtilBuffer $out) {
		 	if ($out == null) return false;
		 	$count = count($this->ressource);
		 	
		 	for ($i = 0; $i < $count; $i++) {
		 		for ($j = $i; $j < $count; $j++) {
		 			$a = $this->ressource[$i];
		 			$b = $this->ressource[$j];
		 			
		 			$res = Util::compareStringDaten($a, $b);
		 			if ($res == -1000) {
		 				$this->last_error = "Die Sortierung nach Datum ist fehlgeschlagen. Ein ung&uuml;ltiges Datum wurde gefunden.";
		 				$out->setBuffer($this->ressource);
		 				return false;
		 			} else if ($res > 0 && $this->direction == SortingPool::SORT_DIRECTION_ASCENDING) {
		 				$this->ressource[$i] = $b;
		 				$this->ressource[$j] = $a;
		 			} else if ($res < 0 && $this->direction == SortingPool::SORT_DIRECTION_DESCENDING) {
		 				$this->ressource[$i] = $b;
		 				$this->ressource[$j] = $a;
		 			}
		 			
		 		} // for j
		 	} // for i
		 	
		 	$out->setBuffer($this->ressource);
		 	
		 	return true;
		 }


		/*
		 * Sortiert die Ressource als String-Array
		 */
		private function sortStrings(UtilBuffer $out) {
			if ($out == null) return false;
			$strkeys = array();
			$count = count($this->ressource);
			
			for ($i = 0; $i < $count; $i++) {
				$strkeys[$this->ressource[$i]] = $i;
			}
			
			sort($this->ressource, SORT_STRING);
			
			for ($i = 0; $i < $count; $i++) {
				$this->indexOrder[$i] = $strkeys[$this->ressource[$i]];
			}

			//$out->add($this->ressource[$i]);
			$out->setBuffer($this->ressource);
			
			return true;
		}
		

		// Sortiert das Resultset nach Kennzahl und Kennziffer
		// Die ersten beiden Felder des ResultSets werden jeweils als Kennzahl und Kennziffer interpretiert
		// Als Rückgabewert bekommt man eine 1 oder 0 bei Fehler
		// Im Utilbuffer befinden sich die Zeilen aus dem ResultSet in sortierter Form
		private function sortResultSet(UtilBuffer $ret) {
			if ($ret == null) return false;
			$arr = array();
			if ($this->type == SortingPool::TYPE_RESULTSET_SORT) {
				$count = 0;
				while ($r = $this->ressource->getNextRow()) {
					$arr[$count] = $r;
					//echo "r$count is: ".$arr[$count][0]."<br>";
					$this->indexOrder[$count] = $count;
					$count++;
				}
				
				// Suchen nach gleichen Präfixen
				$len = strlen($arr[0][0]);
				$suffix = strrchr($arr[0][0], " ");
				$prefixlen = $len - strlen($suffix) + 1;
				
				// If everyting is a prefix, ignore it
				if ($prefixlen >= $len) {
					$prefix = "";
					$prefixlen = 0;
				} else {
					$prefix = substr($arr[0][0], 0, $prefixlen);
				}
				
				$start = 0;
				for ($i = 1; $i < $count; $i++) {
					// Suchen nach gleichen Präfixen
					$nlen = strlen($arr[$i][0]);
					$nsuffix = strrchr($arr[$i][0], " ");
					$nprefixlen = $nlen - strlen($nsuffix) + 1;
					
					// If everyting is a prefix, ignore it
					if ($nprefixlen >= $nlen) {
						$nprefix = "";
						$nprefixlen = 0;
					} else {
						$nprefix = substr($arr[$i][0], 0, $nprefixlen);
					}
					
					// Wenn der neue präfix nicht mehr übereinstimmt
					// Wird vom Start bis zu diesem Präfix sortiert
					if (strcmp($nprefix, $prefix) != 0) {
						//echo "sorting form $start to ".($i-1)." with prefixlen $prefixlen<br>";
						$arr = $this->sortKennzahlen($arr, $prefixlen, $start, $i - 1);
						$prefix = $nprefix;
						$prefixlen = $nprefixlen;
						$start = $i;
					}
				} // for
				
				if ($start != $i) $arr = $this->sortKennzahlen($arr, $prefixlen, $start, $i - 1);
				
				$ret->addArray($arr);
				
				return true;
			} else {
				$this->last_error = "Sortierung fehlgeschlagen. Falscher Typ.";
				return false;
			}
		}
		
		
		// Zum Sortieren eines Arrays von Fach oder AngebotenesFach - Objekten
		private function sortFaecherArray(UtilBuffer $ret) {
			if ($ret == null) return false;
			$arr = array();
			if ($this->type == SortingPool::TYPE_FAECHER_SORT) {
				$count = 0;
				$arr = $this->ressource;
				$count = count($arr);
				if ($count == 0) return;
				
				for ($i = 0; $i < $count; $i++) $this->indexOrder[$i] = $i; //Zuordnung vor der Sortierung festhalten
				
				// Suchen nach gleichen Präfixen
				$f = $arr[0];
				$len = strlen($f->getKennzahl());
				$suffix = strrchr($f->getKennzahl(), " ");
				$prefixlen = $len - strlen($suffix) + 1;

				// If everyting is a prefix, ignore it
				if ($prefixlen >= $len) {
					$prefix = "";
					$prefixlen = 0;
				} else {
					$prefix = substr($f->getKennzahl(), 0, $prefixlen);
				}
				
				$start = 0;
				for ($i = 1; $i < $count; $i++) {
					$f = $arr[$i];
					// Suchen nach gleichen Präfixen
					$nlen = strlen($f->getKennzahl()); // Länge der nächsten Kennzahl ermitteln
					$nsuffix = strrchr($f->getKennzahl(), " "); // Nächster Suffix
					$nprefixlen = $nlen - strlen($nsuffix) + 1; // Länge des nächsten Präfixes ermitteln
					
					// If everyting is a prefix, ignore it
					if ($nprefixlen >= $nlen) {
						$nprefix = "";
						$nprefixlen = 0;
					} else {
						$nprefix = substr($f->getKennzahl(), 0, $nprefixlen);
					}
					
					// Wenn der neue präfix nicht mehr übereinstimmt
					// Wird vom Start bis zu diesem Präfix sortiert
					if (strcmp($nprefix, $prefix) != 0) {
						$arr = $this->sortKennzahlen2($arr, $prefixlen, $start, $i - 1);
						$prefix = $nprefix;
						$prefixlen = $nprefixlen;
						$start = $i;
					}
				} // for
				
				if ($start != $i) $arr = $this->sortKennzahlen2($arr, $prefixlen, $start, $i - 1);
				
				$ret->addArray($arr);
				
				return true;
			} else {
				$this->last_error = "Sortierung fehlgeschlagen. Falscher Typ.";
				return false;
			}
		}


		// Die Funktion ist für ResultSet-Type ausgelegt
		private function sortKennzahlen($arr, $prefixlen, $startindex, $endindex) {
			for ($j = $startindex; $j <= $endindex; $j++) {
				$mindex = $j;
				$minval = intval(substr($arr[$j][0], $prefixlen));
				for ($i = $j; $i <= $endindex; $i++) {
					$z = $arr[$i][0];
					$val = intval(substr($z, $prefixlen));
					//echo "comparing $val and $minval ";
					if ($val < $minval) {
						//echo "$val is less than $minval";
						$x = $arr[$mindex];
						$arr[$mindex] = $arr[$i];
						$arr[$i] = $x;
						$minval = $val;
						
						// Saving order
						$indx = $this->indexOrder[$i];
						$this->indexOrder[$i] = $this->indexOrder[$j];
						$this->indexOrder[$j] = $indx;
					}
					//echo "<br>";
				} // for
			} // for
			return $arr;
			
		} // function
		
		// Die Funktion ist für Array von Fächern ausgelegt
		private function sortKennzahlen2($arr, $prefixlen, $startindex, $endindex) {
			for ($j = $startindex; $j <= $endindex; $j++) {
				$mindex = $j;
				$minval = intval(substr($arr[$j]->getKennzahl(), $prefixlen));
				for ($i = $j; $i <= $endindex; $i++) {
					$z = $arr[$i]->getKennzahl();
					$val = intval(substr($z, $prefixlen));
					if ($val < $minval) {
						$x = $arr[$mindex];
						$arr[$mindex] = $arr[$i];
						$arr[$i] = $x;
						$minval = $val;
						
						// Saving order
						$indx = $this->indexOrder[$i];
						$this->indexOrder[$i] = $this->indexOrder[$mindex];
						$this->indexOrder[$mindex] = $indx;
					}
				} // for
			} // for
			return $arr;
			
		} // function
		
	} // CLASS
	
?>