<?php
/********************************************************************************************************
 *					Datei: Bericht.php
 *					Author: David M. Hübner & Christian Burghoff
 *					E-Mail: info@milbridge.de
 *					Letzte Änderung: 25.01.2006
 */


	include("libraries.php");
		
	class Bericht {

		const BEIM_STUDENT		= 1;
		const BEIM_MITARBEITER	= 2;
		const BEIM_PROFESSOR	= 3;
		const FERTIG			= 4;
		
		const FREIGABE_KEINE	= 1;
		const FREIGABE_EXTERN	= 2;
		const FREIGABE_INT_EXT	= 3;
		
		const BERICHTE_PRO_SEITE = 30;

		private $berichtid;
		private $matrnr;				// Matrikelnummer (Referenz des Studenten)
		private $freigabe;				// 0,1 oder 2 (nicht freigegeben, intern, intern & extern)
		private $freigabeStudent;		// 0 oder 1 (nicht freigegebender oder freigegeben)
		private $bearbeitungszustand;	// 1,2,3 oder 4 (beim Student, beim Mitarbeiter, beim Professor, fertig)
		private $unternehmen;			// Unternehmensobjekt
		private $zeitraum_anfang;		// Startdatum des Praktikums als Unix TimeStamp
		private $zeitraum_ende;			// Enddatum des Praktikums als Unix TimeStamp
		private $keywords;				// Zur Beschreibung der Tätigkeiten
		private $email_betreuer;		// Email-Adresse des Betreuers des Praktikums
		private $email_bewerbungen;		// Email-Adresse zur Bewerbung für andere Studenten
		private $abstrakt;				// Text zur inhaltlichen Beschreibung des Praktikums
		private $fazit;					// Text als abschließendes Kommentar (Bewertung)
		private $dateiname;				// Link zur Datei des ganzen Berichtes
		private $abgabeversuch;			// Abgabeversuch n

		private $last_error;
		private $conn;
		private $inited;
		
		public function __construct(Connection $conn) {
			$this->last_error = "";
			$this->conn = $conn;
			$this->inited = false;
			$this->unternehmen = new Unternehmen($conn);
		} //constructor
		
		// Initialisiert das Objekt mit den Daten aus der Datenbank
		// liefert true falls erfolgreich, sonst false
		public function initAusDatenbank($berichtid) {
			$conn = $this->conn;
			
			if (!$conn->isConnected()) {
				$this->last_error = "Keine verbindung zur Datenbank";
				return false;
			}
			
			$berichtid = intval($berichtid);
			$query = new DBQuery("SELECT berichtID, Matrikelnummer, FreigabeID, BearbeitungszustandID, UnternehmenID, ZeitraumAnfang, ZeitraumEnde, Keywords , EmailBetreuer, EmailBewerbungen, Abstrakt, Fazit, BerichtDateiname, Abgabeversuch, FreigabeStudent FROM bericht WHERE BerichtID=$berichtid");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 1 && $row = $result->getNextRow()) {
					$this->berichtid= intval($row[0]);
					$this->matrnr   = intval($row[1]);
					$this->freigabe = intval($row[2]);
					$this->bearbeitungszustand = intval($row[3]);
					if (!($this->unternehmen->initAusDatenbank($row[4])))
						$this->last_error = $this->unternehmen->getLastError();					
					$this->zeitraum_anfang	= array(substr($row[5], 8, 2), substr($row[5], 5, 2), substr($row[5], 0, 4));
					$this->zeitraum_ende	= array(substr($row[6], 8, 2), substr($row[6], 5, 2), substr($row[6], 0, 4));
					$this->keywords = $row[7];
					$this->email_betreuer = $row[8];
					$this->email_bewerbungen = $row[9];
					$this->abstrakt = $row[10];
					$this->fazit = $row[11];
					$this->dateiname = $row[12];
					$this->abgabeversuch = intval($row[13]);
					$this->freigabeStudent = intval($row[14]);
					$this->inited = true;
					return true;
				} else {
					$this->last_error = "Fehlerhafte Matrikelnummer für den Bericht.";
				}
			} else {
				$this->last_error = "Konnte Bericht nicht lesen".$conn->getLastError();
			}
			
			return false;
		}
		
		public function initAusDatenbankPerMatrNr($matrNr) {
			$conn = $this->conn;
			
			if (!$conn->isConnected()) {
				$this->last_error = "Keine verbindung zur Datenbank";
				return false;
			}
			
			$matrnr = intval($matrNr);
			$query = new DBQuery("SELECT berichtID, Matrikelnummer, FreigabeID, BearbeitungszustandID, UnternehmenID, ZeitraumAnfang, ZeitraumEnde, Keywords , EmailBetreuer, EmailBewerbungen, Abstrakt, Fazit, BerichtDateiname, Abgabeversuch, FreigabeStudent FROM bericht WHERE Matrikelnummer=$matrNr");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 1 && $row = $result->getNextRow()) {
					$this->berichtid= intval($row[0]);
					$this->matrnr   = intval($row[1]);
					$this->freigabe = intval($row[2]);
					$this->bearbeitungszustand = intval($row[3]);
					if (!($this->unternehmen->initAusDatenbank($row[4])))
						$this->last_error = $this->unternehmen->getLastError();
					$this->zeitraum_anfang	= array(substr($row[5], 8, 2), substr($row[5], 5, 2), substr($row[5], 0, 4));
					$this->zeitraum_ende	= array(substr($row[6], 8, 2), substr($row[6], 5, 2), substr($row[6], 0, 4));
					$this->keywords = $row[7];
					$this->email_betreuer = $row[8];
					$this->email_bewerbungen = $row[9];
					$this->abstrakt = $row[10];
					$this->fazit = $row[11];
					$this->dateiname = $row[12];
					$this->abgabeversuch = intval($row[13]);
					$this->freigabeStudent = intval($row[14]);
					$this->inited = true;
					return true;
				} else {
					$this->last_error = "Fehlerhafte Matrikelnummer für den Bericht.";
				}
			} else {
				$this->last_error = "Konnte Bericht nicht lesen".$conn->getLastError();
			}
			
			return false;
		}
		
		//fügt einen neuen bericht ein, bzw. aktualisiert diesen, falls er schon vorhanden ist.
		public function updateDatenbank() {
			if (!$this->conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			if ($this->unternehmen->getInited())
				$this->unternehmen->updateDatenbank();
			$unternehmenID = $this->unternehmen->getUnternehmenID();
			$zeit_anf = $this->zeitraum_anfang[2]."-".$this->zeitraum_anfang[1]."-".$this->zeitraum_anfang[0];
			$zeit_end = $this->zeitraum_ende[2]."-".$this->zeitraum_ende[1]."-".$this->zeitraum_ende[0];

			$q = new DBQuery("SELECT Matrikelnummer FROM bericht WHERE Matrikelnummer='$this->matrnr'");
			if ($result = $this->conn->executeQuery($q)) {
				if ($result->rowsCount() > 0) {
					$q = new DBQuery("UPDATE bericht SET Matrikelnummer='$this->matrnr', FreigabeID='$this->freigabe', BearbeitungszustandID='$this->bearbeitungszustand', UnternehmenID='$unternehmenID', ZeitraumAnfang='$zeit_anf', ZeitraumEnde='$zeit_end', Keywords='$this->keywords', EmailBetreuer='$this->email_betreuer', EmailBewerbungen='$this->email_bewerbungen', Abstrakt='$this->abstrakt', Fazit='$this->fazit', BerichtDateiname='$this->dateiname', Abgabeversuch='$this->abgabeversuch', FreigabeStudent='$this->freigabeStudent' WHERE Matrikelnummer='$this->matrnr'");
					if (!$this->conn->executeQuery($q)) {
						$err->addError($this->conn->getLastError());
						return false;
					}
				}
				else {
					$q = new DBQuery("INSERT INTO bericht(Matrikelnummer, FreigabeID, BearbeitungszustandID, UnternehmenID, ZeitraumAnfang, ZeitraumEnde, Keywords, EmailBetreuer, EmailBewerbungen, Abstrakt, Fazit, BerichtDateiname, Abgabeversuch, FreigabeStudent) VALUES('$this->matrnr', '$this->freigabe', '$this->bearbeitungszustand', '$unternehmenID', '$zeit_anf', '$zeit_end', '$this->keywords', '$this->email_betreuer', '$this->email_bewerbungen', '$this->abstrakt', '$this->fazit', '$this->dateiname', '$this->abgabeversuch', '$this->freigabeStudent')");
					if (!$this->conn->executeQuery($q)) {
						$err->addError($this->conn->getLastError());
						return false;
					}
				}
			}
			else {
				$q = new DBQuery("INSERT INTO bericht(Matrikelnummer, FreigabeID, BearbeitungszustandID, UnternehmenID, ZeitraumAnfang, ZeitraumEnde, Keywords, EmailBetreuer, EmailBewerbungen, Abstrakt, Fazit, BerichtDateiname, Abgabeversuch. FreigabeStudent) VALUES('$this->matrnr', '$this->freigabe', '$this->bearbeitungszustand', '$unternehmenID', '$zeit_anf', '$zeit_end', '$this->keywords', '$this->email_betreuer', '$this->email_bewerbungen', '$this->abstrakt', '$this->fazit', '$this->dateiname', '$this->abgabeversuch', '$this->freigabeStudent')");
				if (!$this->conn->executeQuery($q)) {
					$err->addError($this->conn->getLastError());
					return false;
				}
			}
			return true;
		}
		
		//löscht einen bericht
		public function loescheAusDatenbank() {
			if (!$this->conn->isConnected()) {
				$err->addError("Keine Verbindung zur Datenbank.");
				return false;
			}
			if ($this->unternehmen->getInited() && $this->unternehmen->getStatus() == 1)
				$this->unternehmen->loescheAusDatenbank();
			
			$q = new DBQuery("DELETE FROM bericht WHERE Matrikelnummer=$this->matrnr");
			return ($result = $this->conn->executeQuery($q));
		}


		/* Sucht nach einem Bericht anhand eines Namens */
		/* Siehe die SQL - Anfrage*/
		public static function sucheBericht(Connection $conn, ErrorQueue $err, $suchString, $sachbearbeiterID) {
			$nurZahlen = true;
			if (!(preg_match("/^\d{6}$/", $suchString))) {
				$nurZahlen = false;
				$suchString		= str_replace("'", "", $suchString);
				$suchString2	= str_replace("ö", "oe", $suchString);
				$suchString2	= str_replace("ä", "ae", $suchString2);
				$suchString2	= str_replace("ü", "ue", $suchString2);
				$suchString3	= str_replace("oe", "ö", $suchString);
				$suchString3	= str_replace("ae", "ä", $suchString3);
				$suchString3	= str_replace("ue", "ü", $suchString3);
			}
			$querystring  = "SELECT b.berichtID, b.Abgabeversuch, st.Name, st.Vorname, sg.PraktikaSachbearbeiterID, b.BearbeitungszustandID, b.Matrikelnummer, b.UnternehmenID, u.Name FROM bericht b ";
			$querystring .=	"LEFT JOIN student st ON (st.MatrNr=b.Matrikelnummer) ";
			$querystring .=	"LEFT JOIN unternehmen u ON (u.UnternehmenID=b.UnternehmenID) ";
			$querystring .= "LEFT JOIN studiengang sg ON (sg.StudiengangID=st.StudiengangID) ";					
			if ($nurZahlen == false) {
				$querystring .=	" WHERE (st.Name LIKE '%".$suchString."%' OR st.Name LIKE '%".$suchString2."%' OR st.Name LIKE '%".$suchString3."%' ";
				$querystring .=	"OR st.Vorname LIKE '%".$suchString."%' OR st.Vorname LIKE '%".$suchString2."%' OR st.Vorname LIKE '%".$suchString3."%') ";
			} else {
				$querystring .=	" WHERE b.Matrikelnummer=".$suchString." ";
			}
			$bestimmterSachbearbeiter = "";
			if ($sachbearbeiterID!=0) $bestimmterSachbearbeiter = "AND sg.PraktikaSachbearbeiterID = ".$sachbearbeiterID." ";
			$querystring .=	"AND b.BearbeitungszustandID = 4 ".$bestimmterSachbearbeiter."ORDER BY st.Name";
					
			$q = new DBQuery($querystring);
			
			if ($conn->isConnected()) {
				if ($result = $conn->executeQuery($q))
					return $result;
				else {
					$err->addError("Datenbankfehler: ".$conn->getLastError());
				}
			} else {
				return false;
			}
		}
		/* Liefert alle Bericht unter bestimmten Kriterien aus der Datenbank als Array zurück */
		/* Siehe die SQL - Anfrage*/
		public static function enumBerichte(Connection $conn, ErrorQueue $err, $sortierenach, $sortierrichtung, $region, $groesse, $branche, $fachbereich, $studiengang, $zeigeAuchNichtFreigegebene, $keyword) {
			$richtung_string = " ASC";
			if ($sortierrichtung==1)
				$richtung_string = " DESC";
			$sortiere_string = "u.Name".$richtung_string.", br.Name";
			switch ($sortierenach) {
				case 1: $sortiere_string = "br.Name".$richtung_string.", u.Name"; break;
				case 2: $sortiere_string = "u.adrOrt".$richtung_string.", u.Name"; break;
				case 3: $sortiere_string = "sg.Name".$richtung_string.", u.Name"; break;
			}
			$suchString		= str_replace("'", "", $keyword);
			$suchString2	= str_replace("ö", "oe", $suchString);
			$suchString2	= str_replace("ä", "ae", $suchString2);
			$suchString2	= str_replace("ü", "ue", $suchString2);
			$suchString3	= str_replace("oe", "ö", $suchString);
			$suchString3	= str_replace("ae", "ä", $suchString3);
			$suchString3	= str_replace("ue", "ü", $suchString3);
			
			$filter_string = "";
			if (! ($region==0 && $groesse==0 && $branche==0 && $fachbereich==0 && $studiengang==0 && $zeigeAuchNichtFreigegebene)) {
				$filter_string = "WHERE";
				$erste_bedingung = true;
				if ($region != 0) { 
					if (!$erste_bedingung) $filter_string .= " AND"; else $erste_bedingung = false;
					if ($region == 10)
						$filter_string .= " u.adrStaatID = ".Config::HEIMATLAND_ID;
					else if ($region == 20)
						$filter_string .= " u.adrStaatID <> ".Config::HEIMATLAND_ID;
					else
						$filter_string .= " sta.KontinentID = ".$region;
				}
				if ($groesse != 0) {
					if (!$erste_bedingung) $filter_string .= " AND"; else $erste_bedingung = false;
					$filter_string .= " u.UnternehmensgroesseID >= ".$groesse;
				}
				if ($branche != 0) {
					if (!$erste_bedingung) $filter_string .= " AND"; else $erste_bedingung = false;
					$filter_string .= " u.BrancheID = ".$branche;
				}
				if ($fachbereich != 0) {
					if (!$erste_bedingung) $filter_string .= " AND"; else $erste_bedingung = false;
					$filter_string .= " sg.FachbereichID = ".$fachbereich;
				}
				if ($studiengang != 0) {
					if (!$erste_bedingung) $filter_string .= " AND"; else $erste_bedingung = false;
					$filter_string .= " st.StudiengangID = ".$studiengang;
				}
				if (!$zeigeAuchNichtFreigegebene) {
					if (!$erste_bedingung) $filter_string .= " AND"; else $erste_bedingung = false;
					$filter_string .= " b.FreigabeID <> ".Bericht::FREIGABE_KEINE;
				}
				if ($keyword != "") {
					if (!$erste_bedingung) $filter_string .= " AND"; else $erste_bedingung = false;
					$filter_string .=	" (b.Keywords LIKE '%".$suchString."%' OR b.Keywords LIKE '%".$suchString2."%' OR b.Keywords LIKE '%".$suchString3."%')";
				}
			}

			
			$querystring  = "SELECT b.berichtID, u.Name, br.Name, u.adrOrt, sg.name, b.FreigabeID, b.Keywords, b.BearbeitungszustandID FROM bericht b ";
			$querystring .= "LEFT JOIN unternehmen u ON (u.UnternehmenID=b.UnternehmenID) ";
			$querystring .= "LEFT JOIN branche br ON (u.BrancheID=br.BrancheID) ";
			$querystring .= "LEFT JOIN student st ON (st.MatrNr=b.Matrikelnummer) ";
			$querystring .= "LEFT JOIN studiengang sg ON (sg.StudiengangID=st.StudiengangID) ";
			$querystring .= "LEFT JOIN staat sta ON (u.adrStaatID=sta.StaatID) ";
			$querystring .= $filter_string;
			$querystring .= " ORDER BY ".$sortiere_string;
			$q = new DBQuery($querystring);
			
			if ($conn->isConnected()) {
				if ($result = $conn->executeQuery($q))
					return $result;
				else {
					$err->addError("Datenbankfehler: ".$conn->getLastError());
				}
			} else {
				return false;
			}
		}
		
		public static function enumBerichteZurKontrolle(Connection $conn, ErrorQueue $err, $sachbearbeiterID, $mitarbeiterUsername){	
			
			$querystring  = "SELECT b.berichtID, b.Abgabeversuch, st.Name, st.Vorname, sg.PraktikaSachbearbeiterID, b.BearbeitungszustandID, b.Matrikelnummer, b.UnternehmenID, u.Name   FROM bericht b ";
			$querystring .= "LEFT JOIN unternehmen u ON (u.UnternehmenID=b.UnternehmenID) ";
			$querystring .= "LEFT JOIN student st ON (st.MatrNr=b.Matrikelnummer) ";
			$querystring .= "LEFT JOIN studiengang sg ON (sg.StudiengangID=st.StudiengangID) ";
			$bestimmterSachbearbeiter = "";
			if ($sachbearbeiterID) $bestimmterSachbearbeiter = "AND sg.PraktikaSachbearbeiterID = ".$sachbearbeiterID." ";
			$bestimmterMitarbeiter = "";
			if ($mitarbeiterUsername) $bestimmterMitarbeiter = "AND sg.PraktikaMitarbeiterUsername = '".$mitarbeiterUsername."' ";
			$imZustand = "";
			if ($sachbearbeiterID && $mitarbeiterUsername) $imZustand = "AND (b.BearbeitungszustandID = 3 OR b.BearbeitungszustandID = 2) ";
			else if ($sachbearbeiterID) $imZustand = "AND b.BearbeitungszustandID = 3 ";
			 if ($mitarbeiterUsername) $imZustand = "AND b.BearbeitungszustandID = 2 ";
			$querystring .= "WHERE sg.PraktikaSachbearbeiterID <> 0 ".$imZustand.$bestimmterSachbearbeiter.$bestimmterMitarbeiter;
			$querystring .= " ORDER BY  b.Abgabeversuch";
			$q = new DBQuery($querystring);
			
			if ($conn->isConnected()) {
				if ($result = $conn->executeQuery($q))
					return $result;
				else {
					$err->addError("Datenbankfehler: ".$conn->getLastError());
				}
			} else {
				return false;
			}
		}
		
		public static function enumFreigaben(Connection $conn, ErrorQueue $err) {
			$q = new DBQuery("SELECT FreigabeID, FreigabeBereich FROM freigabe ORDER BY FreigabeID");
			
			if ($conn->isConnected()) {
				if ($result = $conn->executeQuery($q))
					return $result;
				else {
					$err->addError("Datenbankfehler: ".$conn->getLastError());
				}
			} else {
				return false;
			}
		}
		
		public static function createBerichtTable(Connection $conn, ErrorQueue $err, $sortierenach, $sortierrichtung, $gruppe, $zeigeAuchNichtFreigegebene, $region, $groesse, $branche, $fachbereich, $studiengang, $seite, $aktuellerLink, $aufzu, $keyword) {

      		// Vorhandene Studiengänge suchen.
      		$vorhandene_berichte = "";
      		if (!($result = Bericht::enumBerichte($conn, $err, $sortierenach, $sortierrichtung, $region, $groesse, $branche, $fachbereich, $studiengang, $zeigeAuchNichtFreigegebene, $keyword))) {
      			$err->addError("Die Studiengänge konnten von der Datenbank nicht gelesen werden. ".$conn->getLastError());
      			//if ($ok == 0 || $ok == 10) $ok = 21;
      		} else {
      			//Tabelle bilden
      			$gruppenid = "";
      			if ($gruppe != -1) $gruppenid = "&gruppe=".$gruppe;
      			$berichtanzahl = $result->rowsCount();
      			$seitenanzahl = intval($berichtanzahl/Bericht::BERICHTE_PRO_SEITE)+1;
				if ($result->rowsCount() > 0) {
      			    $vorhandene_berichte .= '<table border="0" cellspacing="0" cellpadding="0" class="studiengangTable">';
      			    if ($berichtanzahl>Bericht::BERICHTE_PRO_SEITE) {
      			    	$vorhandene_berichte .= '<tr><td colspan ="7">Seite '.($seite+1).' von '.$seitenanzahl;
      		      		if ($seite > 0) {
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite=0">|&lt;</a>';
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite='.($seite-1).'">&lt;&lt;</a>';
      		      		}
      		      		for ($seiteNo = 0; $seiteNo < $seitenanzahl && $seiteNo < 40; $seiteNo++)
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite='.($seiteNo).'">'.($seiteNo+1).'</a>';
      		      		if ($seite+1 < $seitenanzahl) {
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite='.($seite+1).'">&gt;&gt;</a>';
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite='.($seitenanzahl-1).'">&gt;|</a>';
      		      		}
      		      		$vorhandene_berichte .= '</td></tr>';
      			    }
					$restLink = '&region='.$region.'&groesse='.$groesse.'&branche='.$branche.'&fachbereich='.$fachbereich.'&studiengang='.$studiengang.'&keyword='.$keyword;
      		      	if ($sortierenach==0)
      		      	  $vorhandene_berichte .= '<tr><td width="20%" valign="bottom"  ><a class="dick" href="datenbank.php?sortierenach=0&sortierrichtung='.!($sortierrichtung).$gruppenid.'&aufzu='.$aufzu.$restLink.'">Unternehmen&nbsp;&nbsp;<img src="../images/ico_arr_'.!($sortierrichtung).'.gif" border="0" width="20" height="20"></a></td>';
      		      	else
      		      	  $vorhandene_berichte .= '<tr><td width="20%" valign="bottom" ><a class="dick" href="datenbank.php?sortierenach=0'.$gruppenid.'&aufzu='.$aufzu.$restLink.'">Unternehmen</a></td>';
      		      	$vorhandene_berichte .= '<td width="2%" valign="bottom"  ></td>';
      		      	if ($sortierenach==1)
      		      	  $vorhandene_berichte .= '<td width="20%"  valign="bottom" ><a class="dick" href="datenbank.php?sortierenach=1&sortierrichtung='.!($sortierrichtung).$gruppenid.'&aufzu='.$aufzu.$restLink.'">Branche&nbsp;&nbsp;<img src="../images/ico_arr_'.!($sortierrichtung).'.gif" border="0" width="20" height="20"></a></td>';
      		      	else
       		      	  $vorhandene_berichte .= '<td width="20%" valign="bottom"  ><a class="dick" href="datenbank.php?sortierenach=1'.$gruppenid.'&aufzu='.$aufzu.$restLink.'">Branche</a></td>';
       		      	$vorhandene_berichte .= '<td width="2%" valign="bottom"  ></td>';
					if ($sortierenach==2)
      		      	  $vorhandene_berichte .= '<td width="20%" valign="bottom"  ><a class="dick" href="datenbank.php?sortierenach=2&sortierrichtung='.!($sortierrichtung).$gruppenid.'&aufzu='.$aufzu.$restLink.'">Ort&nbsp;&nbsp;<img src="../images/ico_arr_'.!($sortierrichtung).'.gif" border="0" width="20" height="20"></a></td>';
      		      	else
      		      	  $vorhandene_berichte .= '<td width="20%" valign="bottom"  ><a class="dick" href="datenbank.php?sortierenach=2'.$gruppenid.'&aufzu='.$aufzu.$restLink.'">Ort</a></td>';
      		      	$vorhandene_berichte .= '<td width="2%" valign="bottom"  ></td>';
      		      	if ($sortierenach==3)
      		      	  $vorhandene_berichte .= '<td width="36%" valign="bottom"  ><a class="dick" href="datenbank.php?sortierenach=3&sortierrichtung='.!($sortierrichtung).$gruppenid.'&aufzu='.$aufzu.$restLink.'">Studiengang&nbsp;&nbsp;<img src="../images/ico_arr_'.!($sortierrichtung).'.gif" border="0" width="20" height="20"></a></td></tr>';
      		      	else
      		      	  $vorhandene_berichte .= '<td width="36%" valign="bottom"  ><a class="dick" href="datenbank.php?sortierenach=3'.$gruppenid.'&aufzu='.$aufzu.$restLink.'">Studiengang</a></td></tr>';
      		      	$vorhandene_berichte .= '<tr><td colspan ="7">&nbsp;</td></tr>';
      		      	if ($berichtanzahl<=Bericht::BERICHTE_PRO_SEITE) {
	      				while ($r = $result->getNextRow()) {
		    				  	$vorhandene_berichte .= Bericht::fuelleZeileMitBericht($r, $gruppenid);
	      				}
      		      	}
      		      	else {
      		      		for ($berichtNo = $seite * Bericht::BERICHTE_PRO_SEITE; $berichtNo< (($seite+1) * Bericht::BERICHTE_PRO_SEITE); $berichtNo++) {
      		      			if ($r = $result->getRow($berichtNo)) {
      		      				$vorhandene_berichte .= Bericht::fuelleZeileMitBericht($r, $gruppenid);
      		      			}
      		      			else {
      		      				break;
      		      			}
      		      		}
      		      		$vorhandene_berichte .= '<tr><td colspan ="7" >Seite '.($seite+1).' von '.$seitenanzahl;
      		      		if ($seite > 0) {
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite=0">|&lt;</a>';
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite='.($seite-1).'">&lt;&lt;</a>';
      		      		}
      		      		for ($seiteNo = 0; $seiteNo < $seitenanzahl && $seiteNo < 40; $seiteNo++)
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite='.($seiteNo).'">'.($seiteNo+1).'</a>';
      		      		if ($seite+1 < $seitenanzahl) {
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite='.($seite+1).'">&gt;&gt;</a>';
      		      			$vorhandene_berichte .= '&nbsp;&nbsp;<a href="'.$aktuellerLink.'&seite='.($seitenanzahl-1).'">&gt;|</a>';
      		      		}
      		      		$vorhandene_berichte .= '</td></tr>';
      		      	}
      				$vorhandene_berichte .= "</table>";
      			} else {
      				$vorhandene_berichte = "Die gewählten Filterkriterien liefern keine Ergebnisse.";
      			}
      			return $vorhandene_berichte;
      		}
      	}
      	
		private static function fuelleZeileMitBericht($r, $gruppenid) {
			if ($r[7] == Bericht::FERTIG)
			{
				$link = '<a class="db" href="bericht_ausgabe.php?berichtid='.$r[0].$gruppenid.'">';
				$zeile .= '<tr>';
	
				$zeile .= '<td valign="top">'.$link.$r[1].'</a><br><br></td>';
				$zeile .= '<td valign="top"> &nbsp;<br><br></td>';
	
				$zeile .= '<td valign="top">'.$link.$r[2].'</a><br><br></td>';
				$zeile .= '<td valign="top">&nbsp;<br><br></td>';
				$zeile .= '<td valign="top">'.$link.$r[3].'</a><br><br></td>';
				$zeile .= '<td valign="top">&nbsp;<br><br></td>';
				$zeile .= '<td valign="top">'.$link.$r[4].'</a><br><br></td>';
				$zeile .= '</tr>';
				return $zeile;
			}
			return "";
		}
      
      public static function zeigeBerichtInternExtern(Connection $conn, ErrorQueue $err, $berichtid, $matrikelNr) {
      	$bericht = new Bericht($conn);
      	$bericht->initAusDatenbank($berichtid);
      	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
	      
	    if ($bericht->freigabe == Bericht::FREIGABE_INT_EXT || $student->getMatrNr() == $matrikelNr) {
	      	$html_bericht  = "";
	      	$html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" class="studiengangTable">';
	      	$html_bericht .= '<tr><td width="200" class="dick">Studierender:</td><td>'.$student->getNameKomplett().'</td></tr>';
	      	$html_bericht .= '<tr><td class="dick">Semester:</td><td>'.$student->getSemester().'</td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Studiengang:</td><td>'.$student->getStudiengang()->getName().'</td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Email Adresse:</td><td><a href="mailto:'.$student->getEmail().'">'.$student->getEmail().'</a></td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Zeitraum:</td><td>'.$bericht->getZeitraumAnfangAlsString().' - '.$bericht->getZeitraumEndeAlsString().'</td></tr>';
	       	$html_bericht .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Unternehmen:</td><td>'.$bericht->getUnternehmen()->getName().'</td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Branche:</td><td>'.$bericht->getUnternehmen()->getBranchenName().'</td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Straße:</td><td>'.$bericht->getUnternehmen()->getAdrStrasse().'</td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Ort:</td><td>'.$bericht->getUnternehmen()->getAdrPLZ().' '.$bericht->getUnternehmen()->getAdrOrt().'</td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Staat:</td><td>'.$bericht->getUnternehmen()->getStaatName().'</td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Email für Bewerbungsanfragen:</td><td>'.$bericht->getEmailBewerbungen().'</td></tr>';
	       	$html_bericht .= '<tr><td class="dick">Keywords:</td><td>'.$bericht->getKeywords().'</td></tr>';
	        $html_bericht .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
	      	$html_bericht .= '<tr><td valign="top"  class="dick">Abstrakt:</td><td>'.$bericht->getAbstrakt().'</td></tr>';
	       	$html_bericht .= '<tr><td valign="top"  class="dick">Fazit:</td><td>'.$bericht->getFazit().'</td></tr>';
	       	$html_bericht .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr></table>';
	    } else return Bericht::zeigeBerichtExtern($conn, $err, $berichtid, 0);
       	return $html_bericht;
     }

      public static function zeigeBerichtExtern(Connection $conn, ErrorQueue $err, $berichtid, $gruppe) {
      	$bericht = new Bericht($conn);
      	$bericht->initAusDatenbank($berichtid);
      	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
      	$gruppenzusatz = ""; if ($gruppe>0) $gruppenzusatz = "&gruppe=".$gruppe;
      	if ($bericht->freigabe == Bericht::FREIGABE_EXTERN || $bericht->freigabe == Bericht::FREIGABE_INT_EXT) {
		  	$html_bericht  = "";
		  	$html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" class="studiengangTable">';
		  	$html_bericht .= '<tr><td class="dick">Studierender:</td><td>'.$student->getNameKomplett().'</td></tr>';
		  	$html_bericht .= '<tr><td class="dick">Kontakt:</td><td><a href="mailto:'.$student->getEmail().'">'.$student->getEmail().'</a></td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Studiengang:</td><td>'.$student->getStudiengang()->getName().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Zeitraum:</td><td>'.$bericht->getZeitraumAnfangAlsString().' - '.$bericht->getZeitraumEndeAlsString().'</td></tr>';
		   	$html_bericht .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Unternehmen:</td><td>'.$bericht->getUnternehmen()->getName().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Branche:</td><td>'.$bericht->getUnternehmen()->getBranchenName().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Strasse:</td><td>'.$bericht->getUnternehmen()->getAdrStrasse().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Ort:</td><td>'.$bericht->getUnternehmen()->getAdrPLZ().' '.$bericht->getUnternehmen()->getAdrOrt().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Staat:</td><td>'.$bericht->getUnternehmen()->getStaatName().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Keywords:</td><td>'.$bericht->getKeywords().'</td></tr>';
		    $html_bericht .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr></table>';
      	} else
      		$html_bericht  = "Dieser Bericht ist nicht freigegeben. Er darf nur von authentifizierten Personen betrachtet werden";
       	
       	return $html_bericht;
     }
     
     public static function zeigeBerichtOeffentlich(Connection $conn, ErrorQueue $err, $berichtid, $gruppe) {
      	$bericht = new Bericht($conn);
      	$bericht->initAusDatenbank($berichtid);
      	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
      	$gruppenzusatz = ""; if ($gruppe>0) $gruppenzusatz = "&gruppe=".$gruppe;
      	if ($bericht->freigabe == Bericht::FREIGABE_EXTERN || $bericht->freigabe == Bericht::FREIGABE_INT_EXT) {
		  	$html_bericht  = "";
		  	$html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" class="studiengangTable">';
		  	$html_bericht .= '<tr><td class="dick">Studierender:</td><td>'.$student->getNameKomplett().'</td></tr>';
		  	$html_bericht .= '<tr><td class="dick">Kontakt:</td><td><a href="kontaktformularBericht.php?BerichtID='.$bericht->getBerichtID().$gruppenzusatz.'">Studierenden kontaktieren</a></td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Studiengang:</td><td>'.$student->getStudiengang()->getName().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Zeitraum:</td><td>'.$bericht->getZeitraumAnfangAlsString().' - '.$bericht->getZeitraumEndeAlsString().'</td></tr>';
		   	$html_bericht .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Unternehmen:</td><td>'.$bericht->getUnternehmen()->getName().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Branche:</td><td>'.$bericht->getUnternehmen()->getBranchenName().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Strasse:</td><td>'.$bericht->getUnternehmen()->getAdrStrasse().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Ort:</td><td>'.$bericht->getUnternehmen()->getAdrPLZ().' '.$bericht->getUnternehmen()->getAdrOrt().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Staat:</td><td>'.$bericht->getUnternehmen()->getStaatName().'</td></tr>';
		   	$html_bericht .= '<tr><td class="dick">Keywords:</td><td>'.$bericht->getKeywords().'</td></tr>';
		    $html_bericht .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr></table>';
      	} else
      		$html_bericht  = "Dieser Bericht ist nicht freigegeben. Er darf nur von authentifizierten Personen betrachtet werden";
       	
       	return $html_bericht;
     }
   //--------------------Funktionen die für den Prof zur Abgabe sind-------------------------------------
   
     public static function zeigeBerichtStudentendaten(Connection $conn, ErrorQueue $err, $bericht) {

      	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
        $html_bericht  = "";
		$html_bericht .= '	<table border="0" cellspacing="0" cellpadding="0" width="420">';
  	    $html_bericht .= '<tr><td width="150" class="dick">Name:</td><td>'.$student->getName().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Vorname:</td><td>'.$student->getVorname().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Studiengang:</td><td>'.$student->getStudiengang()->getName().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Matrikelnummer:</td><td>'.$student->getMatrNr().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Semester:</td><td>'.$student->getSemester().' </td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Abgabeversuch:</td><td>'.$bericht->getAbgabeversuch().' </td></tr>';
		$html_bericht .= '</table>';
  	  return $html_bericht;
     }
   
     public static function zeigeBerichtUnternehmensdaten(Connection $conn, ErrorQueue $err, $bericht) {

		$unternehmen = new Unternehmen($conn);

      	$unternehmen->initAusDatenbank($bericht->getUnternehmen()->getUnternehmenID());
      	
  		$html_bericht  = "";
		$html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" width="420">';
  	    $html_bericht .= '<tr><td width="150" class="dick">Arbeitsbereich:</td><td>'.$unternehmen->getBranchenName().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Name:</td><td>'.$unternehmen->getName().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">URL:</td><td> '.$unternehmen->getUrl().'</td></tr>';
		$html_bericht .= '<tr><td class="dick">Adresse:</td><td>'.$unternehmen->getAdrStrasse().' '.$unternehmen->getAdrPLZ().' '.$unternehmen->getAdrOrt().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Zeitraum:</td><td>'.$bericht->getZeitraumAnfangAlsString().' bis '.$bericht->getZeitraumEndeAlsString().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Wochenanzahl:</td><td>'.$bericht->getZeitraumInWochen().'</td></tr>';
  	    
 		$html_bericht .= '</table>';
		
		return $html_bericht;
     }
          
     public static function zeigeBerichtFileKontakt(Connection $conn, ErrorQueue $err, $bericht) {

      	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
        $html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" width="420">';
  	    $html_bericht .= '<tr><td width="150" class="dick">Kontaktadresse:</td><td>Bei Fragen bezüglich des Praktikumsunternehmes können Sie sich an <a href="mailto:'.$bericht->getEmailBetreuer().'">'.$bericht->getEmailBetreuer().' </a> wenden.</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Berichtdatei:</td><td><a  href="'.$bericht->kreiereLinkZurDatei().'" target="_blank">'.$bericht->getDateiname().' </a></td></tr>';
		$html_bericht .= '<tr><td colspan="2"><br><br>Bitte beachten Sie, dass Sie nur für 60 Minuten eingeloggt bleiben. Gehen Sie also nun zur Korrektur des Praktikumsberichts über, so müssen sie sich ggf. neu einloggen. Der Bericht ist natürlich jederzeit wieder aufrufbar.<br><br></td></tr>';
  	    $html_bericht .= '</table>';
  	  return $html_bericht;
     }
     
     
	public static function zeigeBerichtProfExtern(Connection $conn, ErrorQueue $err, $bericht) {

      	$unternehmen = new Unternehmen($conn);

      	$unternehmen->initAusDatenbank($bericht->getUnternehmen()->getUnternehmenID());

      	
      	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
      	
      	$html_bericht  = "";
		$html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" width="420">';
  	    $html_bericht .= '<tr><td width="150" class="dick">Arbeitsbereich:</td><td>'.$unternehmen->getBranchenName().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Unternehmen:</td><td>'.$unternehmen->getName().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">URL:</td><td> '.$unternehmen->getUrl().'</td></tr>';

		$html_bericht .= '<tr><td class="dick">Adresse:</td><td>'.$unternehmen->getAdrStrasse().' '.$unternehmen->getAdrPLZ().' '.$unternehmen->getAdrOrt().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Kontakt-Email:</td><td>'.$bericht->getEmailBewerbungen().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Mitarbeiterzahl:</td><td>'.$unternehmen->getUnternehmensgroesseAlsString().'</td></tr>';

  	    $html_bericht .= '<tr><td class="dick">Zeitraum:</td><td>'.$bericht->getZeitraumAnfangAlsString().' bis '.$bericht->getZeitraumEndeAlsString().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Wochenanzahl:</td><td>'.$bericht->getZeitraumInWochen().'</td></tr>';
  	    $html_bericht .= '<tr><td class="dick">Keywords:</td><td>'.$bericht->getKeywords().'</td></tr>';
		$html_bericht .= '</table>';
		return $html_bericht;
     }  
     
     public static function zeigeBerichtProfIntern(Connection $conn, ErrorQueue $err, $bericht) {

      	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
      	
      	$html_bericht  = "";
		$html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" width="420">';
		$html_bericht .= '<tr><td class="dick">Abstrakt zum Praktikumsbericht:</td></tr>'; 	  
  	    $html_bericht .= '<tr><td>'.$bericht->getAbstrakt().'<br><br></td></tr>';		
		$html_bericht .= '<tr><td class="dick">Fazit zum Praktikum:</td></tr>'; 	  
  	    $html_bericht .= '<tr><td>'.$bericht->getFazit().'<br><br></td></tr>';
		$html_bericht .= '</table>';
  	  	return $html_bericht;
     }
      
      
     public static function zeigeBerichtFreigabeAuswahl(Connection $conn, ErrorQueue $err, $bericht) {

      	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
      	
      	
      	
      	
      	$html_bericht  = "";
      	$html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" width="420">';
	  	
      	if($bericht->getFreigabeStudent() == Bericht::FREIGABE_KEINE){
      		
      		$html_bericht .=  '<span style="color:#FF0000">Achtung! Der Student möchte  aus persönlichen Gründen nicht, dass Informationen zu seinem Bericht im Internet veröffentlicht werden! Deswegen wird die Freigabe automatisch gesetzt.</span> ';
      	
      	}else {

			$html_bericht .= '<tr><td><p>Geben Sie bitte die Freigabeform des Berichts an</p>'; 	  
	  	    $html_bericht .= '<p><input type="radio" name="freigabe" value="beides"> den internen und den öffentlichen Teil freigeben<br>';		
			$html_bericht .= '<input type="radio" name="freigabe" value="oeffentliche"> nur den öffentlichen Teil freigeben<br>'; 	  
	  	    $html_bericht .= '<input type="radio" name="freigabe" value="nicht"> nicht freigeben</p>';
			
      	}

			$html_bericht .= '</td></tr></table>';
			
  	  	return $html_bericht;
     } 
        		
		public function getLastError() {
			return $this->last_error;
		}

		/*public function getNameKomplett() {
			if (strcmp($this->name, "") != 0 && strcmp($this->vorname, "") != 0) {
				$s = $this->name.", ".$this->vorname;
			} else {
				$s = $this->vorname." ".$this->name;
			}
			
			$s = Util::truncateStringLength($s, 40);
			return $s;
		}*/

		public function getBerichtID() {
			return $this->berichtid;
		}
		
		public function getMatrNr() {
			return $this->matrnr;
		}
		
		public function getKeywords() {
			return $this->keywords;
		}
		
		public function setKeywords($words) {
			$this->keywords = $words;
		}
				
		public function getAbstrakt() {
			return $this->abstrakt;
		}
		
		public function setAbstrakt($neuerAbstrakt) {
			$this->abstrakt = $neuerAbstrakt;
		}
		
		public function getFazit() {
			return $this->fazit;
		}

		public function setFazit($neuesFazit) {
			$this->fazit = $neuesFazit;
		}
			
		public function getEmailBetreuer() {
			return $this->email_betreuer;
		}
		
		public function getDateiname() {
			return $this->dateiname;
		}
		
		public function setEmailBetreuer($neue_email) {
			$this->email_betreuer = $neue_email;
		}
		
		public function getEmailBewerbungen() {
			return $this->email_bewerbungen;
		}
		
		public function setEmailBewerbungen($neue_email) {
			$this->email_bewerbungen = $neue_email;
		}
		
		public function getZeitraumAnfangAlsString() {
			return $this->zeitraum_anfang[0].".".$this->zeitraum_anfang[1].".".$this->zeitraum_anfang[2];
		}
		
		public function getZeitraumAnfangAlsArray() {
			return $this->zeitraum_anfang;
		}
		
		public function setZeitraumAnfang($tag, $monat, $jahr) {
			$this->zeitraum_anfang = array($tag, $monat, $jahr);
		}
				
		public function getZeitraumEndeAlsString() {
			return $this->zeitraum_ende[0].".".$this->zeitraum_ende[1].".".$this->zeitraum_ende[2];
		}
		
		public function getZeitraumEndeAlsArray() {
			return $this->zeitraum_ende;
		}
		
		public function setZeitraumEnde($tag, $monat, $jahr) {
			$this->zeitraum_ende = array($tag, $monat, $jahr);
		}
		
		public function getZeitraumInWochen() {
			$ende = strtotime($this->zeitraum_ende[2].'-'.$this->zeitraum_ende[1].'-'.$this->zeitraum_ende[0]);
			$start = strtotime($this->zeitraum_anfang[2].'-'.$this->zeitraum_anfang[1].'-'.$this->zeitraum_anfang[0]);
			$weekSec = 60*60*24*7;
			$diff = $ende-$start;
			
			return round($diff/$weekSec);
		}
		
		public function getUnternehmen() {
			return $this->unternehmen;
		}
		
		public function setUnternehmen(Unternehmen $unt) {
			$this->unternehmen = $unt;
		}

		public function setMatrNr($m) {
			$this->matrnr = $m;
		}
		
		public function getInited() {
			return $this->inited;
		}
		
		public function getBearbeitungszustand(){
			return $this->bearbeitungszustand;
		}
		
		public function setBearbeitungszustand($zustand){
			$this->bearbeitungszustand = $zustand;
		}
		
		public function getFreigabe(){
			return $this->freigabe;
		}
		
		public function setFreigabe($zustand){
			$this->freigabe = $zustand;
		}
			
		public function getFreigabeStudent(){
			return $this->freigabeStudent;
		}
		
		public function setFreigabeStudent($zustand){
			$this->freigabeStudent = $zustand;
		}
		
		public function getAbgabeversuch(){
			return $this->abgabeversuch;
		}
		
		public function setAbgabeversuch($nummer){
			$this->abgabeversuch = $nummer;
		}
		
		public function getFreigabeBereich() {
			$conn = $this->conn;
			if (!$conn->isConnected()) {
				$this->last_error = "Keine verbindung zur Datenbank";
				return false;
			}
			$this->brancheID = intval($this->brancheID);
			$query = new DBQuery("SELECT FreigabeBereich FROM freigabe WHERE FreigabeID=$this->freigabe");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 1 && $row = $result->getNextRow()) {
					return $row[0];
				} else {
					$this->last_error = "Fehlerhafte FreigabeID für die Freigabe.";
				}
			} else {
				$this->last_error = "Konnte Freigabe nicht lesen".$conn->getLastError();
			}
			return false;
		}
		
		public function pruefeAufVollstaendigkeit() {
			$unternehmen_volstaendig = true;
			if ($this->unternehmen) {
				if (!(	$this->unternehmen->getName() && $this->unternehmen->getName() != ""
					&&	$this->unternehmen->getAdrOrt() && $this->unternehmen->getAdrOrt() != ""
				)) $unternehmen_volstaendig = false;
			}
			if (	$unternehmen_volstaendig
				&&	$this->getMatrNr() && $this->getMatrNr()!= 0
				&&	$this->getZeitraumAnfangAlsString() && $this->getZeitraumAnfangAlsString() != "00.00.0000"
				&&	$this->getZeitraumEndeAlsString() && $this->getZeitraumEndeAlsString() != "00.00.0000"
				&&	$this->getUnternehmen()
				&&	$this->getKeywords() && $this->getKeywords() != ""
				&&	$this->getFazit() && $this->getFazit() != ""
				&&	$this->getAbstrakt() && $this->getAbstrakt() != ""
				&&	$this->getDateiname() && $this->getDateiname() != ""
				) {
				return true;
			}
			return false;
		}
		
		public function ladeDateiHoch($identifier) {
			//datei löschen falls vorhanden
			if (!is_dir(Config::BERICHT_DATEIVERZEICHNIS . $this->matrnr))
				mkdir(Config::BERICHT_DATEIVERZEICHNIS . $this->matrnr, 0700);
			if (isset($this->dateiname) && $this->dateiname!="")
				unlink(Config::BERICHT_DATEIVERZEICHNIS . $this->matrnr . '/' . $this->dateiname);
			if (move_uploaded_file($_FILES[$identifier]['tmp_name'], Config::BERICHT_DATEIVERZEICHNIS . $this->matrnr . '/' . $_FILES[$identifier]['name'])) {
				$this->dateiname = $_FILES[$identifier]['name'];
			} else {
			    $this->last_error = "Upload Fehler! Info:\n" . $_FILES;
			}
		}
		
		public function kreiereLinkZurDatei() {
			$string = "bericht_durchleiten.php?berichtid=" . $this->getBerichtID();
			return $string;
		}
		
		public function leiteDateiDurch() {
			$fehlertext = "";
			$filename = Config::BERICHT_DATEIVERZEICHNIS . $this->matrnr . '/' . $this->dateiname;
			if ( (!$this->dateiname) || $this->dateiname=="" || (!is_dir(Config::BERICHT_DATEIVERZEICHNIS . $this->matrnr)) || (!file_exists($filename))) {
				$fehlertext = "Es wurde keine Bericht-Datei gespeichert.<br>Es könnte ein Fehler beim Hochladen des Berichtes entstanden sein.";
			} else {
				$len = filesize($filename);
				if (substr($filename, -3, 3)=="pdf")
					header("Content-type: application/pdf");
				else
					header("Content-type: application/msword");
					
				header("Content-Length: $len");
				header("Content-Disposition: inline; filename=".$this->dateiname);
				
				readfile($filename);
				exit(0);
			}
			return $fehlertext;
		}

	} // CLASS
	
?>
