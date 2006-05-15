<?php

	include("libraries.php");

	class DBQuery {
		
		private $query;
		
		function __construct($q) {
			$this->query = $q;
		}
		
		public function getString() {
			return $this->query;
		}
		
		function __toString() {
			return $this->query;
		}
	}

?>