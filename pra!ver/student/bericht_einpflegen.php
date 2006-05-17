<?php


	include_once("include_student.php");
	
	$THIS_SITE = "bericht_einpflegen.php";
	
	$_SESSION['backto'] = $THIS_SITE;
	
	if ($student->isLoggedIn()) {
		
		// Wenn der Student noch keinen Studiengang hat, muss er einen auswählen
		if (!$student->hatStudiengang()) {
			$_SESSION['txt'] = "Sie m&uuml;ssen einen Studiengang ausw&auml;hlen";
			include("einstellen.php");
			exit(0);
		}
	} // if LOGGED IN
	
	$ANZAHL_SCHRITTE = 7;
		
		$letzterschritt = 0;
		$sonderseite = 0;
		if (isset($_POST['letzterschritt'])) {
			$letzterschritt = intval(($_POST['letzterschritt']));
		}
		
		
		if (isset($_POST['abbrechen+speichern_x'])) {
		$aktion ="abbrechen+speichern";
		
		}

		if (isset($_POST['abbrechen+verwerfen_x'])) {
		$aktion ="abbrechen+verwerfen";
		
		}
	
		if (isset($_POST['zurueck_x'])) {
			$aktion ="zurueck";
		}
		
		if (isset($_POST['weiter_x'])) {
			$aktion ="weiter";
		}
		

		
		if (isset($_POST['abschicken_x'])) {
			$aktion ="abschicken";
		}
		
		
		
		
		
		$schritt = $letzterschritt;
		if ($aktion == "zurueck")
			$schritt--;
		else if ($aktion == "weiter")
			$schritt++;
		else if ($aktion == "abschicken")
			$sonderseite = 1;
		else if ($aktion == "abbrechen+speichern")
			$sonderseite = 2;
		else if ($aktion == "abbrechen+verwerfen")
			$sonderseite = 3;
			
		if ($schritt < 1)
			$schritt = 1;
		else if ($schritt > $ANZAHL_SCHRITTE)
			$schritt = $ANZAHL_SCHRITTE;
				
		$datenbestaetigung = false;
		if (isset($_POST['datenbestaetigungchecked'])) {
			$tempdatenbestaetigung = ($_POST['datenbestaetigungchecked']);
		}
		if ($tempdatenbestaetigung == 'on')
			$datenbestaetigung = true;
			
		//erst überprüfen, ob der studiengang des studenten auch teil nimmt
		$nimmt_teil = true;
		if ($student->getStudiengang()->getSachbearbeiterID() == 0) {
			$schritt = 1;
			$nimmt_teil = false;
		}
		if ($schritt>2 && $datenbestaetigung == false)
			$schritt = 2;
		
		//bestimmen, ob man auf weiter / zurueck drücken kann.
		$zurueck_btn_enabled = false;
		$weiter_btn_enabled = false;
		$speichern_btn_enabled = false;
		$verwerfen_btn_enabled = false;
				
		$bericht = new Bericht($conn);
		$bericht->initAusDatenbankPerMatrNr($student->getMatrNr());
		//echo $bericht->getMatrNr()."<br><br>";

		//lese und schreibe daten aus formularen
		
		if ($letzterschritt == 3) {
			$unterteilungschritt3 = "";
			
			if (isset($_POST['unterteilungschritt3_aendern_x'])) {
				$unterteilungschritt3 ="aendern";
			}
			
			if (isset($_POST['unterteilungschritt3_auswaehlen_x'])) {
				$unterteilungschritt3 ="auswaehlen";
			}
			
			if (isset($_POST['unterteilungschritt3'])) {
				$unterteilungschritt3 =($_POST['unterteilungschritt3']);
			}
			
			if (isset($_POST['unterteilungschritt3_erstellen_x'])) {
				$unterteilungschritt3 ="erstellen";
			}
			
			if (isset($_POST['unterteilungschritt3_suchen_x'])) {
				$unterteilungschritt3 ="suchen";
			}			
			
			
			$unternehmenssuche = "";
			if (isset($_POST['unternehmenssuche'])) {
				$unternehmenssuche =($_POST['unternehmenssuche']);
			}
			
			if ($unterteilungschritt3=="suchen") {
				if ($unternehmenssuche == "")
					$unterteilungschritt3 = "";
				else
					$suchergebnis = Unternehmen::sucheUnternehmen($conn, $err, $unternehmenssuche);
			}
			if ($unterteilungschritt3=="erstellen") {
				$bericht->setUnternehmen(new Unternehmen($conn));
				$unterteilungschritt3="bearbeiten";
			}
			if ($unterteilungschritt3=="auswaehlen") {
				$unternehmenswahl = 0;
				if (isset($_POST['unternehmenswahl'])) {
					$unternehmenswahl =($_POST['unternehmenswahl']);
				}
				$tempunternehmen = new Unternehmen($conn);
				$tempunternehmen->initAusDatenbank($unternehmenswahl);
				if ($tempunternehmen != null) {
					$bericht->setUnternehmen($tempunternehmen);
					$unterteilungschritt3="anzeigen";
				} else
					$unterteilungschritt3="suchen";
			}
			if ($unterteilungschritt3=="aendern") {
				$unterteilungschritt3 = "";
				$bericht->setUnternehmen(new Unternehmen($conn));
			}
			if ($unterteilungschritt3=="verwerten") {
				if (isset($_POST['unt_name'])) {
					$bericht->getUnternehmen()->setName($_POST['unt_name']);
				}
				if (isset($_POST['unt_url'])) {
					$bericht->getUnternehmen()->setUrl($_POST['unt_url']);
				}
				if (isset($_POST['unt_strasse'])) {
					$bericht->getUnternehmen()->setAdrStrasse($_POST['unt_strasse']);
				}
				if (isset($_POST['unt_plz'])) {
					$bericht->getUnternehmen()->setAdrPLZ($_POST['unt_plz']);
				}
				if (isset($_POST['unt_ort'])) {
					$bericht->getUnternehmen()->setAdrOrt($_POST['unt_ort']);
				}
				if (isset($_POST['unt_land'])) {
					$bericht->getUnternehmen()->setStaatID(intval($_POST['unt_land']));
				}
				if (isset($_POST['unt_branche'])) {
					$bericht->getUnternehmen()->setBrancheID(intval($_POST['unt_branche']));
				}
				if (isset($_POST['unt_groesse'])) {
					$bericht->getUnternehmen()->setUnternehmensgroesseID(intval($_POST['unt_groesse']));
				}
			}
		}
		else if ($letzterschritt == 4) {
			$anf_dat_tag = "0";
			if (isset($_POST['anf_dat_tag'])) {
				$anf_dat_tag = ($_POST['anf_dat_tag']);
			}
			$anf_dat_mon = "0";
			if (isset($_POST['anf_dat_mon'])) {
				$anf_dat_mon = ($_POST['anf_dat_mon']);
			}
			$anf_dat_jah = "0";
			if (isset($_POST['anf_dat_jah'])) {
				$anf_dat_jah = ($_POST['anf_dat_jah']);
			}
			$end_dat_tag = "0";
			if (isset($_POST['end_dat_tag'])) {
				$end_dat_tag = ($_POST['end_dat_tag']);
			}
			$end_dat_mon = "0";
			if (isset($_POST['end_dat_mon'])) {
				$end_dat_mon = ($_POST['end_dat_mon']);
			}
			$end_dat_jah = "0";
			if (isset($_POST['end_dat_jah'])) {
				$end_dat_jah = ($_POST['end_dat_jah']);
			}
			$bericht->setZeitraumAnfang($anf_dat_tag, $anf_dat_mon, $anf_dat_jah);
			$bericht->setZeitraumEnde($end_dat_tag, $end_dat_mon, $end_dat_jah);
			
			$input_keywords = "";
			if (isset($_POST['input_keywords'])) {
				$input_keywords = ($_POST['input_keywords']);
			}
			$bericht->setKeywords($input_keywords);
		}
			
		else if ($letzterschritt == 5) {
			$input_email_bewerbungen = "";
			if (isset($_POST['input_email_bewerbungen'])) {
				$input_email_bewerbungen = ($_POST['input_email_bewerbungen']);
				$bericht->setEmailBewerbungen($input_email_bewerbungen);
			}
			$input_abstrakt = "";
			if (isset($_POST['input_abstrakt'])) {
				$input_abstrakt = ($_POST['input_abstrakt']);
				$bericht->setAbstrakt($input_abstrakt);
			}
			$input_fazit = "";
			if (isset($_POST['input_fazit'])) {
				$input_fazit = ($_POST['input_fazit']);
				$bericht->setFazit($input_fazit);
			}
		}
		else if ($letzterschritt == 6) {
			$dateifehler = "";
			$input_abstrakt = "";
			if ($_FILES['input_bericht_upload']['error']==UPLOAD_ERR_OK && !(intval($_FILES['input_bericht_upload']['size'])>Config::MAX_BERICHT_DATEIGROESSE)) {
				$bericht->ladeDateiHoch('input_bericht_upload');
			} else if ($_FILES['input_bericht_upload']['error']==UPLOAD_ERR_INI_SIZE
					|| $_FILES['input_bericht_upload']['error']==UPLOAD_ERR_PARTIAL
					|| intval($_FILES['input_bericht_upload']['size'])>Config::MAX_BERICHT_DATEIGROESSE) {
				$schritt = 6;
				$dateifehler = "<span style='color:#ff0000'>Hochgeladene Datei ist zu groß oder wurde nur teilweise hochgeladen</span>";
			}
			$input_email_betreuer = "";
			if (isset($_POST['input_email_betreuer'])) {
				$input_email_betreuer = ($_POST['input_email_betreuer']);
				$bericht->setEmailBetreuer($input_email_betreuer);
			}
			$keineveroeffentlichung = Bericht::FREIGABE_INT_EXT;
			if (isset($_POST['keineveroeffentlichungchecked'])) {
				$keineveroeffentlichungtemp = ($_POST['keineveroeffentlichungchecked']);
				if ($keineveroeffentlichungtemp == 'on')
					$keineveroeffentlichung = Bericht::FREIGABE_KEINE;
				$bericht->setFreigabeStudent($keineveroeffentlichung);
			}
		}
		else if ($letzterschritt == 7 && $sonderseite == 1) {
			if (isset($_POST['angabenrichtig']) && ($_POST['angabenrichtig']) == 'on') {
				//ok
			}
			else {
				$schritt = 7;
				$sonderseite = 0;
			}
		}
		
		$schrittErneut = false;
		if ($letzterschritt == $schritt)
				$schrittErneut = true;

		
?>


<html>
<head>
<title>Student</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/studi.css" type="text/css">
</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>

<?php
	// Menue Bereich
	$menue = Menu::EINPFLEGEN;
	include("menu.php");
?>

<div>
<h3>Kontrolle des Berichtes zum Berufspraktischen Semester</h3>

<div id="studentplanDiv">
	

	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>

			<div class="titelGanzStreifen">
			</div>
				<?php
					if ($sonderseite == 0) {
						$ueberschriften = array( //jeweils die überschrift und die zustandsanzeige
							array("PRAKTIKA-VERWALTUNGSSYSTEM"),
							array("BITTE &nbsp;ÜBERPRÜFE &nbsp;DEINE &nbsp;DATEN", "PERSÖNLICHE DATEN ÜBERPRÜFT"),
							array("ANGABEN &nbsp;ZUM &nbsp;UNTERNEHMEN &nbsp;UND &nbsp;ZEITRAUM (öffentlich)", "DATEN ZUM UNTERNEHMEN EINGEGEBEN"),
							array("ANGABEN &nbsp;ZUM &nbsp;ZEITRAUM &nbsp;UND &nbsp;KEYWORDS (öffentlich)", "ZEITRAUM UND KEYWORDS EINGEGEBEN"),
							array("ABSTRAKT &nbsp;UND &nbsp;FAZIT &nbsp;EINGEBEN (hochschulintern)", "ABSTRAKT UND FAZIT EINGEGEBEN"),
							array("HOCHLADEN &nbsp;DES &nbsp;BERICHTS (nur für den Sachbearbeiter)"),
							array("DATEN &nbsp;BESTÄTIGEN &nbsp;UND &nbsp;ABSCHICKEN &nbsp;ZUR &nbsp;ABGABE")
							);
						for ($i=0; $i < $schritt; $i++)
							if ($ueberschriften[$i-1][1]){
							
							?><div class="titelGanzHistory">
									<img border="0" src="../images/ico_haken.gif">
							<?echo '<span style="color:#777777">'.$ueberschriften[$i-1][1].'</span><br>';
								
							?></div><?
							}
						?><div class="titelGanz">
								<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
						<?
						echo $ueberschriften[$schritt-1][0];
						if($schritt < $ANZAHL_SCHRITTE)
						echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SCHRITT '.($schritt).' VON 6';
						?></div><?
					} else {
						switch ($sonderseite) {
							case 1:{
								?><div class="titelGanz">
								<img border="0" src="../images/<? echo 'ico_haken.gif'; ?>">
								<?
								echo "BERICHT ABGEGEBEN";
								?></div><?
							}break;
							case 2:{
								?><div class="titelGanz">
								<img border="0" src="../images/<? echo 'ico_haken.gif'; ?>">
								<?
								echo "EINGABEN GESPEICHERT";
								?></div><?
							}break;
							case 3:{
								?><div class="titelGanz">
								<img border="0" src="../images/<? echo 'ico_haken.gif'; ?>">
								<?
								echo "EINGABEN GELÖSCHT";
								?></div><?
							}break;
						}
					}
						

				?>
<div class="inhaltGanz">
		
	<table border="0" cellspacing="0" class="parallelTable">
    <tr>
      
      <td valign="top">
      	
      		<?php      			
      			if ($sonderseite == 0) {
	      			echo '<form name="form1" method="post" action="bericht_einpflegen.php" enctype="multipart/form-data">';
	      			echo '<input type="hidden" name="letzterschritt" value="'.$schritt.'">';
	      			if ($datenbestaetigung && $schritt != 2)
	      				echo '<input type="hidden" name="datenbestaetigungchecked" value="on">';
	      			switch ($schritt) {
	      				case 1: {
	      					
	      					if ($bericht->getInited() && $nimmt_teil)
	      						switch ($bericht->getBearbeitungszustand()) {
	      							case 2:
	      								$mitarb = $student->getStudiengang()->getMitarbeiter();
	      								echo "Dein Bericht wird derzeit von $mitarb[0] bearbeitet. Bei Fragen oder Anmerkungen zu Deinem Bericht, kontaktiere bitte <a href='mailto:$mitarb[1]'>$mitarb[1]</a>.<br><br>";
	      								echo '<a href="bericht_ausgabe.php?berichtid='.$bericht->getBerichtID().'">Hier</a> kannst Du Deinen Bericht zur Kontrolle abrufen.<br><br>';
	      								break;
	      							case 3:
	       								$profess = $student->getStudiengang()->getSachbearbeiter();
	      								echo "Dein Bericht wird derzeit von $profess[0] bearbeitet. Bei Fragen oder Anmerkungen zu Deinem Bericht, kontaktiere bitte <a href='mailto:$profess[1]'>$profess[1]</a> bzw. $profess[2].<br><br>";
	      								echo '<a href="bericht_ausgabe.php?berichtid='.$bericht->getBerichtID().'">Hier</a> kannst Du Deinen Bericht zur Kontrolle abrufen.<br><br>';
	      								break;
	      							case 4:
	      								echo 'Dein Bericht wurde bereits überprüft und angenommen. <a href="bericht_ausgabe.php?berichtid='.$bericht->getBerichtID().'">Hier</a> kannst Du Deinen Bericht zur Kontrolle abrufen.<br><br>';
	      								break;
	      							case 1:
	      								if ($bericht->getAbgabeversuch > 1) {
		      								echo "Dein Bericht konnte nicht angenommen werden. Es wurden nachfolgende Gründe angegeben:<br><br>";
		      								echo "Hier muss ein neues Feld mit der Ablehnbegründung angezeigt werden.<br><br>";
		      								echo "Bitte überarbeite Deinen Bericht anhand der angegebenen Gründe.<br><br>";
		      								$weiter_btn_enabled = true;
	      								}
	      								else {
	      									echo "Du hast Deinen Bericht bereits einmal angefangen und hast nun die Möglichkeit, ihn weiterzuschreiben.<br><br>";
		      								$weiter_btn_enabled = true;
	      								}
	      								break;
	      							default:
	      								echo "Fehler! Der Bericht befindet sich in einem ungültigen Status.<br><br>";
	      								echo "Bitte überprüfe ihn erneut und schicke ihn ab.<br><br>";
	      								$bericht->setBearbeitungszustand(Bericht::BEIM_STUDENT);
	      								$bericht->updateDatenbank();
	      								$weiter_btn_enabled = true;
	      						}
	     					else if ($nimmt_teil){
	     						echo "Wenn Du die folgenden Vorraussetzungen erfüllt hast, kannst Du mit dem Einpflegen Deines Berichtes beginnen.<br><br>";
	     						$weiter_btn_enabled = true;
	     					}
	      					echo '<br><br><span class="dick">Allgemeine Informationen:</span>';
	      					echo '<br><br>Dieses System dient der digitalen Abgabe des Berichtes zum Berufspraktischen Semester.<br><br>';
	      					if ($nimmt_teil)
	      						echo	 "Um Deinen Bericht abzugeben, müssen gewisse Vorraussetzungen bestehen:<br>"
	      									."<ul><li>Du musst Dein Praktikum vor dessen Beginn im Sekretariat angemeldet haben</li>"
	      									."<li>Dein Praktikum muss abgeschossen sein und die vorgeschriebene Mindestwochenanzahl muss eingehalten sein.</li>"
	      									."<li>Du solltest bereits ein gültiges Zeugnis des Praktikumunternehmens im Sekretariat abgegeben haben. "
	      									."Ist dieses nicht erfolgt, so solltest Du es vor der endültigen Abgabe in diesem Tool nachholen.</li>"
	      									."</ul><br><br>";
	      					else
	      						echo	 "Dein Studiengang nimmt derzeit nicht an diesem System teil.<br>"
	      									."Um Dein berufspraktisches Semester anerkennen zu lassen, musst Du den Bericht im Sekretariat abgeben.<br><br>"
	      									."Die Entscheidung, ob ein Studiengang teilnimmt, trägt alleine der Praktikumssachbearbeiter Deines Studiengangs."
	      									."<br><br><br>";
	      									
	      					
							break;
	      				}
	      				
	      				case 2: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
	      					$checkdatenbestaetigung = "";
	      					if ($datenbestaetigung)
	      						$checkdatenbestaetigung = "checked";
	      					if ($schrittErneut)
	      						$spanrot = ' style="color:#ff0000"';
	      					echo '
	      					<table border="0" cellspacing="0" cellpadding="0" width="420">
			      	      <tr>
			      	        <td rowspan="8">
			      	          <img src="_pic/spacer.gif" width="28" height="1" border="0"><br>
			      	        </td>
	
			      	      	<td class="dick" width="150">
			      	          Name:
			      	        </td>
			      	        <td>
			      	          '.$student->getName().'
			      	        </td>
			      	      </tr>
			      	      <tr>
			      	      	<td class="dick">
			      	          Vorname:
			      	        </td>
			      	        <td>
			      	          '.$student->getVorname().'
			      	        </td>
			      	      </tr>
			      	      <tr>
			      	      	<td class="dick">
			      	          Studiengang:
			      	        </td>
			      	        <td>
			      	          '.$student->getStudiengang()->getName().'
			      	        </td>
			      	      </tr>
			      	      <tr>
			      	      	<td class="dick">
			      	          Matrikelnummer:
			      	        </td>
			      	        <td>
			      	          '.$student->getMatrNr().'
			      	        </td>
			      	      </tr>
			      	      <tr>
			      	      	<td class="dick">
			      	          Semester:
			      	        </td>
			      	        <td>
			      	           '.$student->getSemester().'
			      	        </td>
			      	      </tr>
			      	      <tr>
			      	      	<td colspan="2">
			      	      	<br><br>
			      	          <input type="checkbox" id="iddatenbestaetigungchecked" name="datenbestaetigungchecked" '.$checkdatenbestaetigung.'><label for="iddatenbestaetigungchecked"><span'.$spanrot.'>Ich bestätige hiermit, dass meine Daten richtig und vollständig sind</span></label><br><br>
			      	        </td>
			      	      </tr>
			      	    </table>';
			      	    
							break;
	      				}
	      				
	      				case 3: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
							$speichern_btn_enabled = true;
							$verwerfen_btn_enabled = true;
							if (($unternehmensgroessenenum = Unternehmen::enumGroessen($conn, $err))) {
							} else {
								$err->addError($conn->getLastError());
							}
							if (($branchenenum = Unternehmen::enumBranchen($conn, $err))) {
							} else {
								$err->addError($conn->getLastError());
							};
							if (($laenderenum = Staat::enumStaaten($conn, $err))) {
							} else {
								$err->addError($conn->getLastError());
							}
	      					if ($bericht->getMatrNr()==0) {
	      						$bericht->setMatrNr($student->getMatrNr());
	      						$bericht->setFreigabe(Bericht::FREIGABE_KEINE);
	      						$bericht->setBearbeitungszustand(Bericht::BEIM_STUDENT);
	      					}
      						if ($unterteilungschritt3 == "" && $bericht->getUnternehmen()->getInited() == true) {
								if ($bericht->getUnternehmen()->getStatus() == 0)
									$unterteilungschritt3 = "anzeigen";
								else if ($bericht->getUnternehmen()->getStatus() == 1)
									$unterteilungschritt3 = "bearbeiten";
							}
	      					echo 'Die Daten, die Du hier eingibst gehören zum öffentlichen Teil Deiner Abgabe. Das heisst, das diese Daten von allen Besuchern dieser Webseite gelesen werden können und daher keine sensiblen Informationen enthalten sollten, die Dir oder Deinem Praktikumsunternehmen schaden könnten.<br><br><br>';
	      					switch ($unterteilungschritt3) {
	      						case "": { // sub schritt 1
	      							echo	'Viele Unternehmen werden von mehreren Praktikanten besucht.<br>Um die Pflege dieses Systems zu vereinfachen, bitten wir Dich, zuerst zu schauen,<br>ob sich Dein Unternehmen bereits in der Datenbank befindet.<br><br>
	      									Gib bitte den Namem oder einen Teil des<br>Namens Deines Praktikumunternehmens ein:<br><br>
											<input type="text" name="unternehmenssuche" size="20" maxlength="40" value="'.$unternehmenssuche.'">
	      									<input type="image" name="unterteilungschritt3_suchen" border="0" src="../images/buttons/suchen.gif" value="suchen"><br><br><br><br>
	      									';
	      							break;
	      						}
	      						case "suchen": { // sub schritt 2
	      							echo	'<table border="0" cellspacing="0" cellpadding="0"><tr><td valign="top">' .
	      									'Ist Dein Unternehmen nicht dabei, kannst Du<br>nun entweder selbst ein Unternehmen eingeben<br>oder erneut eine Suche starten.<br><br>' .
											'Gib bitte den Namem oder einen Teil des<br>Namens Deines Praktikumunternehmens ein:<br>' .
											'<input type="text" name="unternehmenssuche" size="20" maxlength="40" value="'.$unternehmenssuche.'">' .
											'<input type="image" name="unterteilungschritt3_suchen" border="0" src="../images/buttons/suchen.gif" value="suchen"><br><br><br><br>' .
											'Erstelle bitte nur ein neues Unternehmen, wenn Du Dir<br>sicher bist, dass dieses noch nicht in der Datenbank besteht.:<br>' .
											'<input type="image" name="unterteilungschritt3_erstellen" border="0" src="../images/buttons/erstellen.gif" value="erstellen"><br><br><br></td>' .
	      									'<td valign="top">Deine Suche ergab <b>'.$suchergebnis->rowsCount().' Ergebnisse</b>:<br><br>';
				      	            if ($suchergebnis->rowsCount() > 0) {
						    			while ($r = $suchergebnis->getNextRow()) {
						    				echo '<input type="radio" name="unternehmenswahl" id="unternehmen'.$r[0].'" value="'.$r[0].'" checked><label for="unternehmen'.$r[0].'">'.$r[1].', '.$r[2].'</label><br>';
										}
										echo '<br><input type="image" name="unterteilungschritt3_auswaehlen" border="0" src="../images/buttons/auswaehlen.gif" value="auswaehlen"><br>';
				      	            }
				      	            echo '</td></tr></table><br><br><br>';
				      	            
				      	            break;
	      						}
	      						case "bearbeiten": {
	      							$bericht->getUnternehmen()->setInited(true);
									?><br><br>
											<table border="0" cellpadding="0" cellspacing="0">
							      	            <tr>
							      	              <td valign="top" width="150" >
							      	                Name:
							      	              </td>
							      	              <td>
							      	                <input name="unt_name" size="20" maxlength="40" value="<?php echo $bericht->getUnternehmen()->getName();?>" type="text"><br><br>
							      	              </td>
							      	            </tr>
							      	            <tr>
							      	              <td valign="top" >
							      	                URL:
							      	              </td>
							      	              <td>
							      	                <input name="unt_url" size="20" maxlength="40" value="<?php echo $bericht->getUnternehmen()->getUrl();?>" type="text"><br><br>
							      	              </td>
							      	            </tr>
							      	            <tr>
							      	              <td valign="top" >
							      	                Straße / Hausnummer:
							      	              </td>
							      	              <td>
							      	                <input name="unt_strasse" size="20" maxlength="40" value="<?php echo $bericht->getUnternehmen()->getAdrStrasse();?>" type="text">&nbsp;&nbsp;<br><br>
							      	              </td>
							      	            </tr>
							      	            <tr>
							      	              <td valign="top" >
							      	                PLZ:
							      	              </td>
							      	              <td>
							      	                <input name="unt_plz" size="20" maxlength="40" value="<?php echo $bericht->getUnternehmen()->getAdrPlz();?>" type="text"><br><br>
							      	              </td>
							      	            </tr>
							      	            <tr>
							      	              <td valign="top" >
							      	                Ort:
							      	              </td>
							      	              <td>
							      	                <input name="unt_ort" size="20" maxlength="40" value="<?php echo $bericht->getUnternehmen()->getAdrOrt();?>" type="text"><br><br>
							      	              </td>
							      	            </tr>
							      	            <tr>
							      	              <td valign="top" >
							      	                Staat:
							      	              </td>
							      	              <td>
							      	              		<select name="unt_land" >
															<?php
												    			if ($laenderenum) {
													    			while ($r = $laenderenum->getNextRow()) {
																			$sel = ""; if ($bericht->getUnternehmen()->getStaatID()==$r[0]) $sel = ' selected="selected" ';
																			echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
																	}
																}
															?>
														</select>
														<br><br>
							      	              </td>
							      	            </tr>
							      	            <tr>
							      	    			<td valign="top" >
													Branche:
													</td>
							      	       			 <td>
											      	    <select name="unt_branche">
															<?php
												    			if ($branchenenum) {
													    			while ($r = $branchenenum->getNextRow()) {
																			$sel = ""; if ($bericht->getUnternehmen()->getBrancheID()==$r[0]) $sel = ' selected="selected" ';
																			echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
																	}
																}
															?>
														</select>
														<br><br>
													</td>
												</tr>
												<tr>

							      	    			<td valign="top">
													Mitarbeiterzahl:

													</td>
							      	       			 <td>
											      	    <select name="unt_groesse">
														<?php
											    			if ($unternehmensgroessenenum) {
												    			while ($r = $unternehmensgroessenenum->getNextRow()) {
																		$sel = ""; if ($bericht->getUnternehmen()->getUnternehmensgroesseID()==$r[0])$sel = ' selected="selected" ';
																		echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
																}
															}
														?>
														</select>
														<br><br>
													</td>
												</tr>
											  </table>
											  <input type="hidden" name="unterteilungschritt3" value="verwerten">
											  <br><br><br><br>
											<?php											
									break;
	      						}
	      						case "anzeigen": {
	      							echo 'Du hast folgendes Unternehmen ausgewählt:<br><br>';
	      							echo $bericht->getUnternehmen()->createUnternehmenTable();
	      							$profess = $student->getStudiengang()->getSachbearbeiter();
	      							echo "<br><br>Falls Daten in dem ausgewählten Unternehmen fehlerhaft<br>oder nicht mehr aktuell sind, schreibe bitte ein Mail<br>mit den Einzelheiten an Deinen Praktikasachbearbeiter<br>unter <a href='mailto:$profess[1]'>$profess[1]</a> bzw. $profess[2].<br><br>";
	      							echo 'Möchtest Du ein anderes Unternehmen auswählen, klicke bitte hier: <input type="image" name="unterteilungschritt3_aendern" border="0" src="../images/buttons/aendern.gif" value="aendern"><br><br><br>';
	      							break;
	      						}
	      					}
	      					
							break;
	      				}
	      				
	      				case 4: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
							$speichern_btn_enabled = true;
							$verwerfen_btn_enabled = true;
							$anfangs_zeit = $bericht->getZeitraumAnfangAlsArray();
							$ende_zeit = $bericht->getZeitraumEndeAlsArray();
							echo $anfangs_zeit["mday"];
	      					echo '
			      	          <table border="0" cellspacing="0" cellpadding="0">
			      	            <tr>
			      	              <td colspan="3">
			      	         		 Die Daten, die Du hier eingibst gehören zum öffentlichen Teil Deiner Abgabe. Das heisst, das diese Daten von allen Besuchern dieser Webseite gelesen werden können und daher keine sensiblen Informationen enthalten sollten, die Dir oder Deinem Praktikumsunternehmen schaden könnten.<br><br><br><br>
			      	        	  </td>
			      	            </tr>
			      	            <tr>
			      	              <td valign="top" >
			      	                Anfangsdatum des Praktikums:
			      	              </td>
			      	              <td valign="top">
			      	                (Tag | Monat | Jahr)
			      	              </td>
			      	              <td align="right">
			      	                	  <input type="text" name="anf_dat_tag" size="2" maxlength="2" value="'.$anfangs_zeit[0].'">
			      	                &nbsp;<input type="text" name="anf_dat_mon" size="2" maxlength="2" value="'.$anfangs_zeit[1].'">
			      	                &nbsp;<input type="text" name="anf_dat_jah" size="4" maxlength="4" value="'.$anfangs_zeit[2].'">
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td valign="top">
			      	                Enddatum des Praktikums:
			      	              </td>
			      	              <td valign="top">
			      	                (Tag | Monat | Jahr)
			      	              </td>
			      	              <td align="right">
			      	                	  <input type="text" name="end_dat_tag" size="2" maxlength="2" value="'.$ende_zeit[0].'">
			      	                &nbsp;<input type="text" name="end_dat_mon" size="2" maxlength="2" value="'.$ende_zeit[1].'">
			      	                &nbsp;<input type="text" name="end_dat_jah" size="4" maxlength="4" value="'.$ende_zeit[2].'"><br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td colspan="3" valign="top">
			      	                Keywords (2-3 Stichworte die Deinen Arbeitsbereich kennzeichnen)
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td colspan="3">
			      	                <input type="text" name="input_keywords" size="90" maxlength="255" value="'.$bericht->getKeywords().'"><br><br>
			      	              </td>
			      	            </tr>
			      	          </table>
			      	    	';
						
							break;
	      				}
	      				
	      				case 5: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
							$speichern_btn_enabled = true;
							$verwerfen_btn_enabled = true;
	      					echo '
			      	          <table border="0" cellspacing="0" cellpadding="0">
			      	            <tr>
			      	              <td>
			      	         		 Die folgenden Eingaben sind nur für angemeldete Studierende, Mitarbeiter und Professoren sichtbar. Um den Studierenden einen Eindruck über Dein Praktikumsunternehmen zu geben, möchten wir Dich bitten, Deinen wahren Eindruck zu vermitteln.<br><br><br><br>
			      	        	  </td>
			      	            </tr>
			      	            <tr>
			      	              <td valign="top" >
			      	                (ggf.) Kontakt-Email-Adresse des Unternehmens für Praktikumsanfragen anderer Studierende<br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td>
			      	                <input type="text" name="input_email_bewerbungen" size="40" maxlength="40" value="'.$bericht->getEmailBewerbungen().'"><br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td colspan="2" valign="top">
			      	                Abstrakt zum Praktikumsbericht (Kurze Beschreibung Deiner Tätigkeiten während des Praktikums)
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td colspan="2">
			      	                <textarea name="input_abstrakt" cols="64" rows="10">'.$bericht->getAbstrakt().'</textarea><br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td colspan="2">
			      	                Fazit zum Praktikum (Kurze Wertung des Unternehmens und Deiner Tätigkeiten)
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td colspan="2">
			      	                <textarea name="input_fazit" cols="64" rows="10">'.$bericht->getFazit().'</textarea><br><br>
			      	              </td>
			      	            </tr>
			      	          </table>
			      	    	';
						
							break;
	      				}
	      				
	      				case 6: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
							$speichern_btn_enabled = true;
							$verwerfen_btn_enabled = true;
	      					$checknichtfreigegeben = "";
	      					if ($bericht->getFreigabeStudent() > Bericht::FREIGABE_KEINE)
	      						$checknichtfreigegeben = "checked";
	      					$dateiVorhanden = "";
	      					if ($bericht->getDateiname() && $bericht->getDateiname()!="" && file_exists(Config::BERICHT_DATEIVERZEICHNIS . $bericht->getMatrNr() . '/' . $bericht->getDateiname()))
	      						$dateiVorhanden = "Du hast bereits einen Bericht namens <span style='color:#ff0000'>".$bericht->getDateiname()."</span> hochgeladen.<br>Du kannst diesen nun durch einen anderen Bericht ersetzen.<br>";
	      					echo'
			      	          <table border="0" cellspacing="0" cellpadding="0">
			      	            <tr>
			      	              <td colspan="2">
			      	              Der Bericht, den Du hier hochladen kannst, gehört zur offiziellen Abgabe Deines Praktikumsberichtes. Er kann nur vom zuständigen Praktikasachbearbeiter betrachtet werden.<br>
			      	         			Der Bericht sollte im Word-, RTF-, HTML- oder PDF-Format und nicht größer als 20 Megabyte sein. Bitte beachte, dass das Hochladen abhängig von Deiner Internetanbindung einige Minuten dauern kann.<br><br><br>
									'.$dateifehler.'<br>
			      	        	  </td>
			      	            </tr>
			      	            <tr>
			      	              <td colspan="2">
									'.$dateifehler.'<br>
			      	        	  	'.$dateiVorhanden.'
			      	        	  </td>
			      	            </tr>
			      	           <tr>
			      	              <td valign="top">
			      	                Bericht hochladen:
			      	              </td>
			      	              <td>
			      	                <input name="input_bericht_upload" type="file" size="20" accept="text/*, application/pdf, application/msword, application/rtf"><br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td valign="top">
			      	                (ggf.) Kontakt-Emailadresse des Unternehmens<br>für Rückfragen des Dozenten
			      	              </td>
			      	              <td>
			      	                <input type="text" name="input_email_betreuer" size="20" maxlength="40" value="'.$bericht->getEmailBetreuer().'"><br><br><br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td colspan="2">
			      	          	    <input type="checkbox" name="keineveroeffentlichungchecked" id="idkeineveroeffentlichung" '.$checknichtfreigegeben.'><label for="idkeineveroeffentlichung">Auf Grund meines persönlichen Wunsches bzw. des, des Unternehmens, möchte ich nicht,<br>dass meine angegeben Daten, weder öffentlich noch hochschulintern, veröffentlicht werden sollen.</label><br><br>
			      	        	  </td>
			      	        	</tr>
			      	          </table>
					      	';
							break;
	      				}
	      				
	      				case 7: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
							$speichern_btn_enabled = true;
							$verwerfen_btn_enabled = true;
							
							
							if ($schrittErneut)
	      						$spanrot = ' style="color:#ff0000"';
	      						
      					
	      					$html_bericht  = "";
					      	$html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" >';
					      	$html_bericht .= '<tr><td>Studierender:</td><td>'.$student->getNameKomplett().'</td></tr>';
					      	$html_bericht .= '<tr><td>Semester:</td><td>'.$student->getSemester().'</td></tr>';
					       	$html_bericht .= '<tr><td>Studiengang:</td><td>'.$student->getStudiengang()->getName().'</td></tr>';
					       	$html_bericht .= '<tr><td>Email Adresse:</td><td><a href="mailto:'.$student->getEmail().'">'.$student->getEmail().'</a></td></tr>';
					       	$angabe_rot =' style="color:#ff0000"'; if ($bericht->getZeitraumAnfangAlsString() && $bericht->getZeitraumAnfangAlsString() != "00.00.0000" && $bericht->getZeitraumEndeAlsString() && $bericht->getZeitraumEndeAlsString() != "00.00.0000") $angabe_rot = '';
					       	$html_bericht .= '<tr><td'.$angabe_rot.'>Zeitraum:</td><td>'.$bericht->getZeitraumAnfangAlsString().' - '.$bericht->getZeitraumEndeAlsString().'</td></tr>';
					       	$html_bericht .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
					       	$angabe_rot =' style="color:#ff0000"'; if ($bericht->getUnternehmen() && $bericht->getUnternehmen()->getName() && $bericht->getUnternehmen()->getName() != "") $angabe_rot = '';
					       	$html_bericht .= '<tr><td'.$angabe_rot.'>Unternehmen:</td><td>'.$bericht->getUnternehmen()->getName().'</td></tr>';
					       	$html_bericht .= '<tr><td>Branche:</td><td>'.$bericht->getUnternehmen()->getBranchenName().'</td></tr>';
					       	$html_bericht .= '<tr><td>Straße:</td><td>'.$bericht->getUnternehmen()->getAdrStrasse().'</td></tr>';
					       	$angabe_rot =' style="color:#ff0000"'; if ($bericht->getUnternehmen() &&	$bericht->getUnternehmen()->getAdrOrt() && $bericht->getUnternehmen()->getAdrOrt() != "") $angabe_rot = '';
					       	$html_bericht .= '<tr><td'.$angabe_rot.'>Ort:</td><td>'.$bericht->getUnternehmen()->getAdrPLZ().' '.$bericht->getUnternehmen()->getAdrOrt().'</td></tr>';
					       	$html_bericht .= '<tr><td>Staat:</td><td>'.$bericht->getUnternehmen()->getStaatName().'</td></tr>';
					       	$html_bericht .= '<tr><td>Email für Bewerbungsanfragen:</td><td>'.$bericht->getEmailBewerbungen().'</td></tr>';
					       	$html_bericht .= '<tr><td>Email für Fragen des Dozenten:</td><td>'.$bericht->getEmailBetreuer().'</td></tr>';
					       	$angabe_rot =' style="color:#ff0000"'; if ($bericht->getKeywords() && $bericht->getKeywords() != "") $angabe_rot = '';
					       	$html_bericht .= '<tr><td'.$angabe_rot.'>Keywords:</td><td>'.$bericht->getKeywords().'</td></tr>';
					       	$angabe_rot =' style="color:#ff0000"'; if ($bericht->getDateiname() && $bericht->getDateiname() != "") $angabe_rot = '';
					       	$html_bericht .= '<tr><td'.$angabe_rot.'>Bericht-Datei:</td><td>'.$bericht->getDateiname().'</td></tr>';
					        $html_bericht .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
					        $angabe_rot =' style="color:#ff0000"'; if ($bericht->getAbstrakt() && $bericht->getAbstrakt() != "") $angabe_rot = '';
					      	$html_bericht .= '<tr><td'.$angabe_rot.' valign="top">Abstrakt:</td><td>'.$bericht->getAbstrakt().'</td></tr>';
					      	$angabe_rot =' style="color:#ff0000"'; if ($bericht->getFazit() && $bericht->getFazit() != "") $angabe_rot = '';
					       	$html_bericht .= '<tr><td'.$angabe_rot.' valign="top">Fazit:</td><td>'.$bericht->getFazit().'</td></tr>';
					       	
					       	
					       		      					if ($bericht->pruefeAufVollstaendigkeit()) {
					       		$checkbox_disabled = "";
					       		$fehler_text = "";
					       	}
					       	else {
					       		$checkbox_disabled = 'disabled="disabled"';
					       		$fehler_text = '<span style="color: #ff0000">Um mit der Abgabe fortzufahren müssen mindestens folgende Angaben gemacht sein:<br>' .
					       						'Unternehmen mit Name und Ort, Zeitraum (Anfang + Ende), Keywords, Fazit und Abstrakt.<br>' .
					       						'Zudem muss der Bericht erfolgreich hochgeladen sein.</span>';
					       	}
					       		
			      	    $html_bericht .= '<tr><td colspan="2"><br><br><br>'.$fehler_text.'<br><br><input type="checkbox" '.$checkbox_disabled.' name="angabenrichtig" id="ideingabenbestaetigung"><label for="ideingabenbestaetigung"><span '.$spanrot.'>Ich bestätige hiermit alle Angaben vertrauenswürdig und richtig gemacht zu haben.</span></label></td></tr></table><br><br>';
					       	
					       	
					       	echo $html_bericht;
	      					
	      					break;
	      				}
	      					
	      			}
	      			//form buttons
		      		if ($zurueck_btn_enabled)
	      				echo '<input type="image" name="zurueck" border="0" src="../images/buttons/zurueck.gif" value="zurueck">';
	      			else
	      				echo '<input type="image" name="zurueck" border="0" src="../images/buttons/zurueck_aus.gif" value="zurueck" disabled="disabled">';
	      			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	      			if ($speichern_btn_enabled)
	      				echo '<input type="image" name="abbrechen+speichern" border="0" src="../images/buttons/abbrechen_speichern.gif" value="abbrechen+speichern">';
	      			else
	      				echo '<input type="image" name="abbrechen+speichern" border="0" src="../images/buttons/abbrechen_speichern_aus.gif" value="abbrechen+speichern" disabled="disabled">';
	      			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	      			if ($verwerfen_btn_enabled)
	      				echo '<input type="image" name="abbrechen+verwerfen" border="0" src="../images/buttons/abbrechen_verwerfen.gif" value="abbrechen+verwerfen">';
	      			else
	      				echo '<input type="image" name="abbrechen+verwerfen" border="0" src="../images/buttons/abbrechen_verwerfen_aus.gif" value="abbrechen+verwerfen" disabled="disabled">';
	      			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	      			if ($weiter_btn_enabled)
	      				if ($schritt ==7)
	      					echo '<input type="image" name="abschicken" border="0" src="../images/buttons/verschicken.gif" value="abschicken">';
	      				else
	      					echo '<input type="image" name="weiter" border="0" src="../images/buttons/weiter.gif" value="weiter">';
	      			else
	      				echo '<input type="image" name="weiter" border="0" src="../images/buttons/weiter_aus.gif" value="weiter" disabled="disabled">';
	      			echo "</form>";
				}
				
				else { //sonderseiten
	      			switch ($sonderseite) {
	      				case 1: {
	      					echo 'Vielen Dank, Dein Bericht wurde nun zur Überprüfung weitergeleitet.<br>Sobald er beurteilt wurde, erhälst Du eine Ergebnis-Email.';
      						$bericht->setAbgabeversuch($bericht->getAbgabeversuch()+1);
      						
	      					
	      					if (!$student->getStudiengang()->getMitarbeiterID()) {
	      						$bericht->setBearbeitungszustand(Bericht::BEIM_PROFESSOR);
	      						$dozent_daten = $student->getStudiengang()->getSachbearbeiter();
	      						Mailer::mailit($dozent_daten[1], "Neuer Bericht zum BpS", $student->getNameKomplett()." hat einen Bericht zum Berufspraktischen Semester abgegeben.\nBitte überprüfen Sie diesen unter \r\n ".Config::PRAVER_ROOT_URL." \r\n");
	      					}
	      					else {
	      						$bericht->setBearbeitungszustand(Bericht::BEIM_MITARBEITER);
	      						$mitarbeiter_daten = $student->getStudiengang()->getMitarbeiter();
	      						Mailer::mailit($mitarbeiter_daten[1], "Neuer Bericht zum BpS", $student->getNameKomplett()." hat einen Bericht zum Berufspraktischen Semester abgegeben.\nBitte überprüfen Sie diesen unter \r\n ".Config::PRAVER_ROOT_URL." \r\n");

	      					}
	      					break;
	      				}
	      				case 2: {
	      					echo 'Deine bisherigen Eingaben wurden gespeichert.<br>Du kannst Deinen Bericht jederzeit unter <i>Bericht einpflegen</i> weiterschreiben.';
	      					
	      					break;
	      				}
	      				case 3: {
	      					echo 'Deine bisherigen Eingaben wurden gelöscht.<br>Unter <i>Bericht einpflegen</i> kannst Du nun jederzeit einen neuen Bericht anfangen.';
	      					$bericht->loescheAusDatenbank();
	      					break;
	      				}
	      			}
				}
				
		    ?>
	     
      


      </td>

    </tr>
  </table>
</div>

</div>
<br><br><br>
<?
		//erstelle den Bericht (und das Unternehmen)
		if ($schritt > 2 && $sonderseite != 3)
			$bericht->updateDatenbank();
?>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
