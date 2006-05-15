<?php

/*
 A class to encapsulate a mysql resultset and relative functions.
*/

	include ("libraries.php");

	class ResultSet {
		
		private $resultset;
		private $fetched;
		private $all_rows;
		private $fetch_offset; // Die Nummer der Zeile, die als nächstes von der Datenbank gelesen wird.
		private $get_offset;   // Die Nummer der Zeile, die der Benutzer von diesem Resultset als nächstes lesen soll.

		private $insID;		   // Falls das ein Insert war wird hier die InsertID gespeichert, die mit auto_increment erhöht wurde.
		private $rowscount;    // Anzahl der Zeilen von SELECT (getRowsCount muss aufgerufen werden)
		private $affectedcount;// Anzahl der Zeilen von INSERT/UPDATE/DELETE
		
		private $link;		   // Link zu der Mysql-Verbindungsressource
		
		// Creates a ResultSet object from a mysql resource created from mysql_query()
		function __construct($res, $link) {
			$this->resultset = $res;
			$this->link = $link;
			$this->fetched = false;
			$this->fetch_offset = 0;
			$this->get_offset = 0;
			
			$this->rowscount = -111;
			$this->affectedcount = -111;
			$this->insID = -1;
		}
		
		public function rowsCount() {
			if ($this->rowscount == -111) {
				$this->rowscount = mysql_num_rows($this->resultset);
			}
			return $this->rowscount;
			
		}
		
		public function affectedRows() {
			if ($this->affectedcount == -111) {
				$this->affectedcount = mysql_affected_rows($this->link);
			}
			return  $this->affectedcount;
		}
		
		// Returns the whole resultset as an array[][]
		public function getAllRows() {
			if (!$this->fetched) {
				//$this->all_rows = array();
				while ($r = mysql_fetch_row($this->resultset)) {
					$this->all_rows[$this->fetch_offset] = $r;
					$this->fetch_offset++;
				}
				$this->fetched = true;
				return $this->all_rows;
			} else {
				return $this->all_rows;
			}
		}
		
		// Lifert die Zeile mit dem angegebenen Index oder FALSE, wenn der index nicht existiert
		public function getRow($nr) {
			if (nr < $this->rowsCount()) {
				$this->getAllRows();
				return $this->all_rows[$nr];
			} else {
				return FALSE;
			}
		}
		
		// Liefert das Array mit der nächsten Zeile oder FALSE
		public function getNextRow() {
			// get_offset wird nur in dieser Funktion erhöht und kann nicht grösser als fetch_offset werden
			if ($this->get_offset == $this->fetch_offset) {
				if ($r = mysql_fetch_row($this->resultset)) {
					$this->all_rows[$this->fetch_offset] = $r;
					$this->get_offset++;
					$this->fetch_offset++;
					return $r;
				} else {
					return FALSE;
				}
			} else {
				if ($this->get_offset >= $this->rowsCount()) {
					return FALSE;
				} else {
					$this->get_offset++;
					return $this->getRow($this->get_offset-1);
				}
			}
		}
		
		/* Man kann weiter mit getNextRow arbeiten */
		public function rewindSet() {
			$this->get_offset = 0;
		}
		
		public function getInsertId() {
			if ($this->insID == -1) {
				$this->insID = mysql_insert_id($this->link);
			}
			
			return $this->insID;
		}
		
		function __destruct() {
			//mysql_free_result($this->resultset);
		}
	}

?>