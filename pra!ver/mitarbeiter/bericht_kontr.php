<?php


	include_once("include_mitarbeiter.php");
	
	$THIS_SITE = "bericht_kontr.php";
	
	$_SESSION['backto'] = $THIS_SITE;
	
	if ($mitarbeiter->isLoggedIn()) {
		$username = $mitarbeiter->getUsername();
		
		$mitarbeiterStudiengaenge = Studiengang::getStudiengaengeVomMitarbeiter($conn, $username);
		$r = $mitarbeiterStudiengaenge->getNextRow();
		$zuBearbeitendeBerichte = Bericht::enumBerichteZurKontrolle($conn, $err, 0, $username);
		
		if (!$r[0]) {
			
			include("kein_sachbearbeiter.php");
			exit(0);
		}
		
	} // if LOGGED IN

	
	$ANZAHL_SCHRITTE = 6;
	
	$aktion = "";
	if (isset($_POST['aktion'])) {
		$aktion =($_POST['aktion']);
	}
	
	if (isset($_POST['abschicken_x'])) {
		$aktion ="abschicken";
	}
	
	//die Berichtid abfangen ob sie mit get oder post kommt
	$berichtid =0;
	if (isset($_GET['getberichtid'])) {
		$berichtid = intval($_GET['getberichtid']);
	} else if (isset($_POST['postberichtid'])) {
		$berichtid =($_POST['postberichtid']);
	}
	
	$bericht = new Bericht($conn);
	$bericht->initAusDatenbank($berichtid);
	
	$fehlernachticht = "";
	
	if ($aktion != "") {
		if ($aktion == "abschicken") {
  			$zeugnisvorhanden = "";
			if (isset($_POST['zeugnisvorhanden'])) {
				$zeugnisvorhanden =($_POST['zeugnisvorhanden']);
			}
			$datenkorrekt = "";
			if (isset($_POST['datenkorrekt'])) {
				$datenkorrekt =($_POST['datenkorrekt']);
			}
			$zeiteingehalten = "";
			if (isset($_POST['zeiteingehalten'])) {
				$zeiteingehalten =($_POST['zeiteingehalten']);
			}
			
			if (($zeugnisvorhanden != "Ja" && $zeugnisvorhanden != "Nein")
			||	($datenkorrekt != "Ja" && $datenkorrekt != "Nein")
			||	($zeiteingehalten != "Ja" && $zeiteingehalten != "Nein")) {
				$fehlernachticht = "<span style='color:FF0000'>Bitte vollständig ausfüllen</span><br>";
				$aktion = "";
			} else if ($zeugnisvorhanden == "Ja" && $datenkorrekt == "Ja" && $zeiteingehalten == "Ja") {
				$aktion = "erfolgreich";
				$bericht->setBearbeitungszustand(3);
				$bericht->updateDatenbank();
			} else {
				$aktion = "fehlerhaft";
				//TODO: schreibe mail
				if (!($zeugnisvorhanden == "Nein" && $datenkorrekt == "Ja" && $zeiteingehalten == "Ja")) {
					$bericht->setBearbeitungszustand(1);
					$bericht->updateDatenbank();
				}
			}
			

  			
		}
	}
	
?>


<html>
<head>
<title>Mitarbeiter</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/mitarbeiter.css" type="text/css">
</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	// Menue Bereich
	$menue = Menu::KONTROLLIEREN;
	include("menu.php");
?>

<div>

		<h3>Kontrolle des Berichtes zum Berufspraktischen Semester</h3>


</div>

<div class="hintergrundstreifen">
	

	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>

		<div class="titelGanzStreifen">
		</div>
		<div class="titelGanz">
		<?php
					if ($aktion ==""){
			?>
			<img border="0" src="../images/<?php echo 'ico_inhalt.gif'; ?>">
			<?php	
						echo "BERICHT KONTROLLIEREN";
			
					}else if ($aktion == "erfolgreich"){
			?>
			<img border="0" src="../images/<?php echo 'ico_haken.gif'; ?>">
			<?php
						echo "VIELEN DANK!";

					}
			?>
			
		</div>

	<div class="inhaltGanz">
		
		<table border="0" cellspacing="0" class="parallelTable">
	    <tr>
	      
	      <td valign="top">
	      	
	      	<?php  
	      		
	      		if ($aktion == "") {
	      			echo '<form name="form1" method="post" action="bericht_kontr.php">';
	      			echo '<input type="hidden" name="postberichtid" value="'.$berichtid.'">';
	      			echo '<input type="hidden" name="aktion" value="abschicken">';
	      			echo '<span class="dick">STUDENT </span><br><br>';
	      			echo Bericht::zeigeBerichtStudentendaten($conn,$err,$bericht);
	      			echo '<br><br><span class="dick">UNTERNEHMEN </span><br><br>';
	      			echo Bericht::zeigeBerichtUnternehmensdaten($conn,$err,$bericht)."<br><br>";
	      			echo $fehlernachticht;
	      			?>
	      			<table border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td width="200" >
								Liegt ein Praktikumszeugnis vor?
							</td>
							<td>
	      						<input type="radio" name="zeugnisvorhanden" id ="id_zeugnis_ja" value="Ja"><label for="id_zeugnis_ja">Ja</label><br>
	      						<input type="radio" name="zeugnisvorhanden" id ="id_zeugnis_nein" value="Nein"><label for="id_zeugnis_nein">Nein</label><br><br>
	      					</td>
	      				</tr>
						<tr>
							<td>
								Stimmen die dargestellten Daten<br>mit dem Zeugnis überein?
							</td>
							<td>
	      						<input type="radio" name="datenkorrekt" id ="id_datenkorrekt_ja" value="Ja"><label for="id_datenkorrekt_ja">Ja</label><br>
	      						<input type="radio" name="datenkorrekt" id ="id_datenkorrekt_nein" value="Nein"><label for="id_datenkorrekt_nein">Nein</label><br><br>
	      					</td>
	      				</tr>
						<tr>
							<td>
								Ist die Mindestpraktikumszeit<br>eingehalten?
							</td>
							<td>
	      						<input type="radio" name="zeiteingehalten" id ="id_zeiteingehalten_ja" value="Ja"><label for="id_zeiteingehalten_ja">Ja</label><br>
	      						<input type="radio" name="zeiteingehalten" id ="id_zeiteingehalten_nein" value="Nein"><label for="id_zeiteingehalten_nein">Nein</label><br><br>
	      					</td>
	      				</tr>
	      			</table>
					<br><br><input type="image" name="abschicken" border="0" src="../images/buttons/verschicken.gif" value="abschicken">
					</form>
					<?php
	      		}
	      		else if ($aktion == "erfolgreich") {
	      			$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
	      			$dozent_daten = $student->getStudiengang()->getSachbearbeiter();
	      			Mailer::mailit($dozent_daten[1], "Neuer Bericht zum BpS", $student->getNameKomplett()." hat einen Bericht zum Berufspraktischen Semester abgegeben.\n"
	      									."Dieser wurde bereits erfolgreich von ".$mitarbeiter->getName()." mit dem Praktikumszeugnis abgeglichen.\n"
	      									."Bitte überprüfen Sie diesen unter \r\n ".Config::PRAVER_ROOT_URL." \r\n");
	      			
	      			echo "Der Bericht wurde erfolgreich überprüft und wurde nun zum zuständigen Sachbearbeiter weitergeleitet.";
	      		} else if ($aktion == "fehlerhaft") {
	      			$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
	      			$email = $student->getEmail();
	      			
	      			if ($zeugnisvorhanden == "Nein") $zeugnisproblem = "Das Zeugnis wurde nicht abgegeben\n";
	      			if ($datenkorrekt == "Nein") $datenproblem = "Die Daten im Bericht stimmen nicht mit denen im Zeugnis überein\n";
	      			if ($zeiteingehalten == "Nein") $zeitproblem = "Die Länge dess Praktikums ist nicht ausreichend.\n";
	      			
	      			Mailer::mailit($email, "Bericht zum BpS fehlerhaft", "Dein Bericht wurde von ".$mitarbeiter->getName()."überprüft und abgelehnt.\nGrund:\n"
	      									.$zeugnisproblem.$datenproblem.$zeitproblem
	      									."Bitte korrigiere Deinen Bericht gegebenfalls unter \r\n ".Config::PRAVER_ROOT_URL." \r\n");
	      									
	      			echo "Der Bericht wurde als fehlerhaft gewertet und der Student wurde benachrichtigt.<br>";
	      			if ($zeugnisvorhanden == "Nein" && $datenkorrekt == "Ja" && $zeiteingehalten == "Ja")
	      				echo "Da das Zeugnis noch nicht abgegeben wurde, verbleibt der Bericht in Ihrer Liste";
	      			else
	      				echo "Der Bericht wurde zurück zum Studenten geschickt";
	      		}
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
