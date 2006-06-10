<?php
	include("../libs/libraries.php");
		
	$err = new ErrorQueue();
	$conn = new Connection();

	if ($conn->connect($DB_SERVER, $DB_NAME, $DB_USERNAME, $DB_PASSWORD)) {
		

		$gruppe = -1;
		if (isset($_GET['gruppe'])) {
			$gruppe = intval($_GET['gruppe']);
		}
		
		$berichtID = -1;
		if (isset($_POST['postBerichtID'])) {
			$berichtID = intval($_POST['postBerichtID']);
		} else if (isset($_GET['BerichtID'])) {
			$berichtID = intval($_GET['BerichtID']);
		} else
			$err->addError("Fehler: Es ist kein E-Mail-Ziel definiert");
			
		$aktion = "formular";
		if (isset($_POST['aktion']) && $_POST['aktion']=="senden") {
			if (isset($_POST['nachricht']) && $_POST['nachricht']!="" && isset($_POST['absender']) && $_POST['absender']!="" && Mailer::checkMail($_POST['absender']) && $berichtID > -1) {
				$bericht = new Bericht($conn);
				$bericht->initAusDatenbank($berichtID);
				if ($bericht->getInited()) {
					if ($student = Student::readStudent($conn, $err, $bericht->getMatrNr())) {				
						$email_adresse = $student->getEmail();
						$betreff = "";
						if (isset($_POST['betreff']))
							$betreff = $_POST['betreff'];
						Mailer::mailit($email_adresse, $betreff, $_POST['nachricht']);
						$aktion = "senden";
					} else echo $err->addError("Eingaben nicht ausreichend!");
				} else echo $err->addError("Eingaben nicht ausreichend!");
			} else echo $err->addError("Eingaben nicht ausreichend!");
		}
	
?>


<html>
<head>
<title>Studenten kontaktieren</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<?
              switch ($gruppe) {
              	case 1:
              		echo '<link rel="stylesheet" href="../styles/studieninteressierte.css" type="text/css">';
              		break;
              	case 2:
              		echo '<link rel="stylesheet" href="../styles/presse_besucher.css" type="text/css">';
              		break;
              	case 3:
              		echo '<link rel="stylesheet" href="../styles/wirtschaft.css" type="text/css">';
              		break;
              	default:
              		echo '<link rel="stylesheet" href="../styles/start.css" type="text/css">';
              		break;
							}
              	
?>
</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	// Menue Bereich
	$menue = Menu::DATENBANK;
	include("menu.php");
?>

<div>

		<h3>Studenten kontaktieren</h3>
	

</div>

<div class="hintergrundstreifen">
	

	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>
<?php
	
	if ($aktion=="formular") {
?>
	<div class="titelGanzStreifen">
	</div>
	<div class="titelGanz">
		<img src="../images/ico_inhalt.gif" border="0" />
		BERICHTDETAIL
	</div>

	<div class="inhaltGanz">

	
		<form name="form1" method="post" action="kontaktformularBericht.php">
			<input type="hidden" name="postBerichtID" value="<?echo $berichtID; ?>">
			<input type="hidden" name="aktion" value="senden">
			<span class="dick">
				Absender:&nbsp;<input name="absender" type="text" size="30" maxlength="50"><br><br>
			</span><br>
			<span class="dick">
				Betreff:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="betreff" type="text" size="30" maxlength="50"><br><br>
			</span><br>
			<span class="dick">
				Nachricht:<br><br><textarea name="nachricht" cols="70" rows="7"></textarea><br><br>
			</span><br>
			<input type="image" border="0" src="../images/buttons/verschicken.gif" >
		</form>

	</div>
	
<?php
	} else if ($aktion=="senden") {
?>
	<br><br><br>Nachricht gesendet.
<?php
	}
?>
</div>
<br><br><br>
<?php
} else {
      		$err->addError($conn->getLastError());
}
?>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
