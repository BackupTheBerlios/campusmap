
<?php

	include("libraries.php");

	class ErrorQueue {
		private $errors;
		private $arr_index; // The index to add (count of the elements)
		private $show_next; // The index to show
		
		private $maxskip;
		
		function __construct() {
			$this->errors = array();
			$this->maxskip = 10;
			$this->show_next = 0;
			$this->arr_index = 0;
		}
		
		public function moreErrors() {
			return ($this->arr_index > $this->show_next);
		}
		
		public function getCount() {
			if ($this->show_next > $this->arr_index) return 0;
			else return $this->arr_index - $this->show_next;
		}
		
		public function showNextError() {
			if ($this->moreErrors())
				return $this->errors[$this->show_next];
			else
				return FALSE;
		}
		
		public function showNextErrorAndRemove() {
			if ($this->moreErrors()) {
				$err = $this->errors[$this->show_next];
				$this->show_next++;
				if ($this->show_next > $this->maxskip) {
					$this->cascade(); // gezeigte Fehler löschen
				}
				return $err;
			} else {
				return FALSE;
			}
			
		}
		
		// löscht die Fehlermeldungen, die durch showNextErrorAndRemove angezeigt wurden
		private function cascade() {
			$i = 0;
			$j = 0;
			for ($i = $this->show_next; $i < $this->arr_index; $i++) {
				$this->errors[$j] = $this->errors[$i];
			}
			$this->arr_index = $i;
			$this->show_next = 0;
		}
		
		public function addError($err_text) {
			$this->errors[$this->arr_index] = $err_text;
			$this->arr_index++;
		}
		
		
		public function createErrorsListAndRemove() {
			
			if (!$this->moreErrors()) return "";
			
			$lst = "<ul class=\"errorsList\">";
			
			while ($s = $this->showNextErrorAndRemove()) {
				$lst .= "<li>".$s."</li>";
			}
			
			$lst .= "</ul>";
			return $lst;
		}
		
		public function returnAsStringAndRemove() {
			if (!$this->moreErrors()) return "";
			
			$lst = "";
			
			while ($s = $this->showNextErrorAndRemove()) {
				$lst .= $s."<br />";
			}
			
			return $lst;
		}
		
	}
	
	

?>