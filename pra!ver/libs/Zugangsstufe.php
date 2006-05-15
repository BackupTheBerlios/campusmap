<?php

	/*
	 * Die Klasse kapselt Funktionen, die für den Zugang von Mitarbeitern zu bestimmten Bereichen wichtig sind.
	 */

	include("libraries.php");

	class Zugangsstufe {
		
		// Diese Konstanten sind die Bereiche
		// Eine Bitmaske aus diesen Konstanten bildet die Zugangsstufe
		const BEREICH_ANMELDELISTEN = 0x01; // Zugang zu den Anmeldelisten
		const BEREICH_NOTENLISTEN   = 0x02; // Zugang zu den Notenlisten
		
		const KOMPLETT              = 0xFF; // Zugang zu allen Bereichen
		
		private $bereiche;
		private $bereichsnamen;
		private $startseiten;
		private $menuebilder;
		
		private function __construct() {
			$this->bereiche = new UtilBuffer();
			$this->bereichsnamen = new UtilBuffer();
			$this->startseiten = new UtilBuffer();
			$this->menuebilder = new UtilBuffer();
			
			$this->bereiche->add     (Zugangsstufe::BEREICH_ANMELDELISTEN);
			$this->bereichsnamen->add('Pr&uuml;fungsanmeldelisten');
			$this->startseiten->add  ('anmeldelisten.php');
			$this->menuebilder->add  ('menubut_anmeldelisten');
			
			$this->bereiche->add     (Zugangsstufe::BEREICH_NOTENLISTEN);
			$this->bereichsnamen->add('Notenlisten');
			$this->startseiten->add  ('notenlisten.php');
			$this->menuebilder->add  ('menubut_notenlisten');
		}
		
		
		/*
		 * Liefert alle Zugangsstufen als UtilBuffer zurück
		 */
		public static function getAlleBereiche() {
			$zugang = new Zugangsstufe();
			return $zugang->bereiche;
		}
		
		/*
		 * Liefert ein UtilBuffer mit den Bereichen zurück, die die angegebene Zugangsstufe betreten darf.
		 */
		public static function getBereicheVonZugangsstufe($stufe) {
			
			$bereiche = Zugangsstufe::getAlleBereiche();
			$buffer = new UtilBuffer();
			for ($i = 0; $i < $bereiche->getCount(); $i++) {
				if (Zugangsstufe::hatZugang($stufe, $bereiche->get($i))) {
					$buffer->add($bereiche->get($i));
				}
			}
			
			return $buffer;
		}
		
		/*
		 * Diese Methode überprüft, ob die angegebene zugangsstufe den Zugang zu dem angegebenen Bereich zulässt.
		 * Rückgabewert ist true oder false.
		 *
		 * Jeder Mitarbeiter hat seine Zugangsstufe vom Administrator zugewiesen
		 */
		public static function hatZugang($zugangstufe, $bereich) {
			return (($zugangstufe & $bereich) == $bereich);
		}
		
		public static function getBereichName($bereich) {
			$zugang = new Zugangsstufe();
			$index = $zugang->getBereichIndex($bereich);
			if ($index != -1) {
				return $zugang->bereichsnamen->get($index);
			} else {
				return "";
			}
		}
		
		public static function getBereichMenueBild($bereich) {
			$zugang = new Zugangsstufe();
			$index = $zugang->getBereichIndex($bereich);
			if ($index != -1) {
				return $zugang->menuebilder->get($index);
			} else {
				return "";
			}
		}
		
		public static function getBereichStartseite($bereich) {
			$zugang = new Zugangsstufe();
			$index = $zugang->getBereichIndex($bereich);

			if ($index != -1) {
				return $zugang->startseiten->get($index);
			} else {
				return "";
			}
		}
		
		
		private function getBereichIndex($bereich) {
			for ($i = 0; $i < $this->bereiche->getCount(); $i++) {
				if ($this->bereiche->get($i) == $bereich) return $i;
			}
			
			return -1;
		}
		
	} // CLASS

?>