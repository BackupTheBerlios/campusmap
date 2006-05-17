<?php


	include_once("include_dozent.php");
	
	$THIS_SITE = "bericht_kontr.php";
	
	$_SESSION['backto'] = $THIS_SITE;
	
	/* if ($dozent->isLoggedIn()) {
		
		$dozentenid = $dozent->getID();
		
		$sachbearbeiterStgang = Studiengang::getStudiengaengeVomSachbearbeiter($conn, $dozentenid);
		$r = $sachbearbeiterStgang->getNextRow();
		$sachbearbeiterBericht = Bericht::enumBerichteZurKontrolle($conn, $err, $dozentenid, 0);
		

		
		if (!$r[0]) {
			
			include("kein_sachbearbeiter.php");
			exit(0);
		}
		
		
		
	} */

	
	$ANZAHL_SCHRITTE = 6;

	
		
	$letzterschritt = 0;
	if (isset($_POST['letzterschritt'])) {
		$letzterschritt = intval(($_POST['letzterschritt']));
	}
	
	$aktion = "";
	
	if (isset($_POST['ablehnen_x'])) {
		$aktion ="ablehnen";
		
	}
	
	if (isset($_POST['zurueck_x'])) {
		$aktion ="zurueck";
	}
	
	if (isset($_POST['weiter_x'])) {
		$aktion ="weiter";
	}
	
	if (isset($_POST['fertig_x'])) {
		$aktion ="fertig";
	}

		
	$aktion2 ="";
	if (isset($_GET['aktion2'])) {
		$aktion2 = intval($_GET['aktion2']);
	}
	


	//die Berichtid abfangen ob sie mit get oder post kommt
	$berichtid =0;
	if ($aktion2 == 1){	
		$schritt=1;
			
		if (isset($_GET['berichtid'])) {
			$berichtid = intval($_GET['berichtid']);
		}
	}else{
		
		if (isset($_POST['berichtID'])) {
			$berichtid =($_POST['berichtID']);
		}
	}
	
	
	$bericht = new Bericht($conn);
	$bericht->initAusDatenbank($berichtid);
	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
	
	$freigabe = "";
	if (isset($_POST['freigabe'])) {
		$freigabe =($_POST['freigabe']);
	}else if($bericht->getFreigabeStudent()== Bericht::FREIGABE_KEINE)
		$freigabe = "auto";
	else
		$freigabe = "keineAuswahl";
		
		
	
	$schritt = $letzterschritt;
	
	if ($aktion == "ablehnen") {
		
		include("bericht_ablehnen.php");
		exit(0);
	}
	
	else if ($aktion == "fertig") {

		
		if ($freigabe == "keineAuswahl"){
			$schritt = 5;
		}else{
			$schritt = 6;
			
			$bericht->setBearbeitungszustand(Bericht::FERTIG);
			$freigabe_text = "";
			
			switch ($freigabe) {
      			
      				case "beides": {
      					
      						$bericht->setFreigabe("3");
      						$freigabe_text = "Er ist nun in der Datenbank für Dich und andere abrufbar.";
      					
      					break;
      				}
      				
      				case "oeffentliche": {
      					
      						$bericht->setFreigabe("2");
      						$freigabe_text = "Der Sachbearbeiter hat jedoch entschieden, nur den öffentlichen Teil freizugegeben. Dieser ist nun in der Datenbank für Dich und andere abrufbar.";
      					
      					break;
      				}
      				
      				case "nicht": {
      					
      						$bericht->setFreigabe("1");
      						$freigabe_text = "Der Sachbearbeiter hat jedoch entschieden, diesen nicht für die Datenbank freizugegeben.";
      					
      					break;
      				}
      				
      				case "auto": {
      					
      						$bericht->setFreigabe("1");
      						$freigabe_text = "Aufgrund Deines Wunsches, wurde dieser jedoch nicht für die Datenbank freigegeben.";
      					
      					break;
      				}
      				
      		}//switch
      		if ($bericht->getUnternehmen()->getStatus() == 1) {
	      		$bericht->getUnternehmen()->setStatus(0);
	      		$bericht->getUnternehmen()->setInited(true);
	      		$bericht->getUnternehmen()->updateDatenbank();
      		}
      		
      		$email_adresse = $student->getEmail();
	      	Mailer::mailit($email_adresse, "Bericht wurde anerkannt", $dozent->getName()." hat Deinen Bericht zum Berufspraktischen Semester überprüft und anerkannt.\n".$freigabe_text."\n\nDu kannst Dich jederzeit wieder einloggen um Deinen und andere Bericht zu durchstöbern.\r\n ".Config::PRAVER_ROOT_URL." \r\n");
      		
      		
      		$bericht->updateDatenbank();
  		
		}//else
  		
	}
	else if ($aktion == "zurueck")
		$schritt--;
	else if ($aktion == "weiter" )
		$schritt++;

	

	
	
	
	if ($schritt < 1)
		$schritt = 1;
	else if ($schritt > $ANZAHL_SCHRITTE)
		$schritt = $ANZAHL_SCHRITTE;
			
	
	
	$schrittErneut = false;
	if ($letzterschritt == $schritt)
			$schrittErneut = true;

	//bestimmen, ob man auf weiter / zurueck drücken kann.
	$zurueck_btn_enabled = false;
	$weiter_btn_enabled = false;
			

	

?>


<html>
<head>
<title>Dozent</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/prof.css" type="text/css">
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


<div class="hintergrundstreifen">	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>
</div>



	
			<div class="titelGanzStreifen">
			</div>
				<?php
					$ueberschriften = array( //jeweils die überschrift und die zustandsanzeige
						
						array("BITTE &nbsp;ÜBERPRÜFEN &nbsp;SIE &nbsp;DIE &nbsp;DATEN &nbsp;DES &nbsp;STUDIERENDEN", "STUDIERENDENDATEN"),
						array("ÜBERPRÜFUNG &nbsp;DES &nbsp;BERICHTS", "BERICHT ÜBERPRÜFT"),
						array("ÜBERPRÜFUNG &nbsp;DES &nbsp;EXTERNEN &nbsp;BEREICHES", "EXTERNE INFORMATIONEN ÜBERPRÜFT"),
						array("ÜBERPRÜFUNG &nbsp;DES &nbsp;INTERNEN &nbsp;BEREICHES", "INTERNE INFORMATIONEN ÜBERPRÜFT"),
						array("ANNAHME/FREIGABE &nbsp;DES &nbsp;BERICHTS"),
						array("VIELEN DANK!")
						);
					if($schritt < $ANZAHL_SCHRITTE){
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
						echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SCHRITT '.($schritt).' VON 5';
						?></div><?
					}else{
						?><div class="titelGanz">
								<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
						<?
						echo $ueberschriften[$schritt-1][0];
						?></div><?
					}

				?>
			
	

	<div class="inhaltGanz">
		
		<table border="0" cellspacing="0" class="parallelTable">
	    <tr>
	      
	      <td valign="top">
	      	
	      		<?php  
	      		
	      		  		
	      			echo '<form name="form1" method="post" action="bericht_kontr.php">';
	      			echo '<input type="hidden" name="letzterschritt" value="'.$schritt.'">';
	      			echo '<input type="hidden" name="berichtID" value="'.$berichtid.'">';
	      			
	      			
	      				
	      			switch ($schritt) {
	      			
	      				case 1: {
	      					
	      					$aktion2 = 0;
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = false;
	      					
	      					echo Bericht::zeigeBerichtStudentendaten($conn,$err,$bericht);
	      					
	      				
	      					
	      					if (!$student->getStudiengang()->getMitarbeiterID()) {
	      						echo '<br><span style="color:#800000">Bitte fahren sie nur fort, wenn der Student auch sein Praktikumszeugnis abgegeben hat!</span><br><br><br>';
	      					}
	      					
	
	 				      	if ($zurueck_btn_enabled)
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck.gif" value="zurueck">';
	      					else
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck_aus.gif" value="zurueck" disabled="disabled">';
				      		
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

				      		echo '<input type="image" name="ablehnen" border="0" src="../images/buttons/ablehnen.gif" value="ablehnen">';
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				      		
				      		if ($weiter_btn_enabled)
				      			echo '<input type="image" name="weiter" border="0" src="../images/buttons/weiter.gif" value="weiter">';
				      		
				      				      	    
							break;
	      				}
	      				
	      				case 2: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
	      					
	      					
	      					echo Bericht::zeigeBerichtFileKontakt($conn,$err,$bericht);
	      					
	      					
	 				      	if ($zurueck_btn_enabled)
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck.gif" value="zurueck">';
	      					else
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck_aus.gif" value="zurueck" disabled="disabled">';
				      		
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				      		echo '<input type="image" name="ablehnen" border="0" src="../images/buttons/ablehnen.gif" value="ablehnen">';
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				      		
				      		if ($weiter_btn_enabled)
				      			echo '<input type="image" name="weiter" border="0" src="../images/buttons/weiter.gif" value="weiter">';
				      		
				      		
				      		     					
							break;
	      				}
	      				case 3: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
	      					
	      					
	      					echo Bericht::zeigeBerichtProfExtern($conn,$err,$bericht);
	      					
	      					if (!$student->getStudiengang()->getMitarbeiterID()) {
	      						echo '<br><span style="color:#800000">Bitte fahren sie nur fort wenn:<ul><li>die Unternehmenadresse auch mit denen des Praktikumszeugnisses übereinstimmen</li><li>die Praktikums-Mindestdauer eingehalten wurde</li></ul></span><br><br><br>';
	      					}
	      					
	 				      	if ($zurueck_btn_enabled)
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck.gif" value="zurueck">';
	      					else
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck_aus.gif" value="zurueck" disabled="disabled">';
				      		
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				      		echo '<input type="image" name="ablehnen" border="0" src="../images/buttons/ablehnen.gif" value="ablehnen">';
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				      		
				      		if ($weiter_btn_enabled)
				      			echo '<input type="image" name="weiter" border="0" src="../images/buttons/weiter.gif" value="weiter">';
				      		
				      		
				      		
				      		break;
	      				}
	      				
	      				case 4: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
	      					
	      					
	      					echo Bericht::zeigeBerichtProfIntern($conn,$err,$bericht);
	      					
	      					
	 				      	if ($zurueck_btn_enabled)
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck.gif" value="zurueck">';
	      					else
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck_aus.gif" value="zurueck" disabled="disabled">';
				      		
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				      		echo '<input type="image" name="ablehnen" border="0" src="../images/buttons/ablehnen.gif" value="ablehnen">';
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				      		
				      		if ($weiter_btn_enabled)
				      			echo '<input type="image" name="weiter" border="0" src="../images/buttons/weiter.gif" value="weiter">';
				      		
				      		
				      		
				      		break;
	      				}
	      				
	      				case 5: {
	      					$weiter_btn_enabled = true;
	      					$zurueck_btn_enabled = true;
	      					
	      					
	      					echo Bericht::zeigeBerichtFreigabeAuswahl($conn,$err,$bericht);
	      					
	      					
	      					if ($freigabe == "keineAuswahl" && $aktion == "fertig"){
	      						echo '<span style="color:#FF0000">Wählen sie Bitte eine Freigabeform für den Bericht aus!</span> ';
	      					}
	      					
	 				      	if ($zurueck_btn_enabled)
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck.gif" value="zurueck">';
	      					else
	      						echo '<br><br><input type="image" name="zurueck" border="0" src="../images/buttons/zurueck_aus.gif" value="zurueck" disabled="disabled">';
				      		
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				      		echo '<input type="image" name="ablehnen" border="0" src="../images/buttons/ablehnen.gif" value="ablehnen">';
				      		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							echo '<input type="image" name="fertig" border="0" src="../images/buttons/fertig.gif" value="fertig">';
				      		
				      		
				      		
				      		
				      		break;
	      				}
	      				
	      				case 6: {
	      					
	      					
	      					
	      					echo "Sie haben soeben einen Bericht erfolgreich überprüft.";
	      					echo '<br><br>';
	      					
	      					if($freigabe == "auto" || $freigabe == "nicht")
	      						$freigabe = "kein";
	      					else if($freigabe =="beides")
	      						$freigabe = "der interne und öffentliche";
							else if($freigabe =="oeffentliche")
								$freigabe = "der öffentliche";
	      					
	 				      	echo("Vom Bericht wurde <b>".$freigabe."</b> Teil freigegeben");
				      		
				      		echo '<br><br><a  href="berichteliste_abgegeben.php">Weitere Berichte kontrollieren</a>';
				      		
				      		
				      		break;
	      				}
	      					
	      			}//switch
	      			echo "</form>";
			    ?>
		     
	      
	
	
	      </td>
	
	    </tr>
	  </table>
	</div>

</div>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
