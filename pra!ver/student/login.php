
<?php

	include("../libs/libraries.php");

	if (isset($_SESSION['mdpass'])) {
		unset($_SESSION['mdpass']);
		unset($_SESSION['matrnr']);
		session_destroy();
	}
	
	$err = new ErrorQueue();
	$conn = new Connection();
	
	$aktion = 0;
	$meldung = "";
	if ($conn->connect($DB_SERVER, $DB_NAME, $DB_USERNAME, $DB_PASSWORD)) {
		TriggerStudent::OnCheckNeuanmeldungen($conn, $err);
		
		if (isset($_POST['aktion'])) {
			$aktion = intval($_POST['aktion']);
			
			switch($aktion) {
				case 1 : { // Wenn das eine komplett neue Anmeldung ist
					if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['matrnr'])) {
						if ($_POST['name'] == "") {
							$err->addError("Gib bitte Deinen Namen ein.");
						}
						
						if ($_POST['vorname'] == "") {
							$err->addError("Gib bitte Deinen Vornamen ein.");
						}
						
						if ($_POST['matrnr'] == "") {
							$err->addError("Gib bitte Deine Matrikelnummer ein.");
						}
						
						if ($_POST['email'] == "") {
							$err->addError("Gib bitte eine g&uuml;ltige E-Mail Adresse ein.");
						}
						
						if ($_POST['fhemail'] == "") {
							$err->addError("Gib bitte eine g&uuml;ltige E-Mail Adresse der Fachhochschule Lübeck ein.");
						}
						
						if (!$err->moreErrors())
							if (Student::neuerStudentAnmelden($_POST['matrnr'], $_POST['name'], $_POST['vorname'], $_POST['email'], $_POST['fhemail'], $err, $conn)) {
								TriggerStudent::OnNeuanmeldung($conn, $err, $_POST['matrnr']);
								$meldung = "Die Login-Informationen wurden Dir zugeschickt.";
							}
					} else {
						$err->addError("Falsche Parameter.");
					}
					break;
				} // case 1
				
				case 2 : { // Wenn das Passwort vergessen wurde
					if (isset($_POST['matrnr']) && intval($_POST['matrnr']) != 0) {
						$generator = new PassGenerator();
						if ($generator->createPassForStudiAndMail($conn, $_POST['matrnr'], 0)) {
							$meldung = "Die Login-Informationen wurden Dir zugeschickt.";
						} else {
							$err->addError($generator->getLastError());
						}
					} else {
						$err->addError("Gib bitte eine g&uuml;ltige Matrikelnummer ein.");
					}
					break;
				} // case 2
			} // switch
		} else {
			$err->addError($conn->getLastError());
		} // wenn Aktion da ist
	} // if connecting
	
?>

<html>
<head>
<title>Student anmelden</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/login.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/studi.css" type="text/css">
</head>

<body leftmargin="0" topmargin="0">

<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<br>
<div>
	<table width="750" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td>&nbsp;&nbsp;&nbsp;</td>
	<td valign="bottom" class="logo"><img src="../images/logos/logo.gif" width="151" height="36" border="0" /></td>
	<td align="right" width="100%" valign="bottom">
	<table border="0" cellspacing="0" class="menutable" style="margin-bottom:3px;">
	
	   <tr>
	    
	    <td><a class="menulink" href="../">Home</a></td>
	   </tr>

	  </tr>
	</table>
	</td>
	</tr>
	</table>
<br><br>
</div>



	<div id="errorDiv">
	<?php
		if (isset($_SESSION['txt'])) {
			echo "<h5>".$_SESSION['txt']."&nbsp;</h5>";
			unset($_SESSION['txt']);
		}
		
		if ($err->moreErrors()) {
			echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
		}
		
		if ($meldung != "") {
			echo '<p class="meldung">'.$meldung."</p>";
		}
	?>
	</div>
	
<div class="hintergrundstreifen">
	
		<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			STUDENTENANMELDUNG
		</div>
		<div class="inhaltHalb">
	
		
			<form name="form1" method="post" action="datenbank.php">
			    <table border="0" cellspacing="0">
			      <tr> 
			        <td width="100">Matrikelnummer:</td>
			        <td align="right"><input type="text" name="matrnr" class="wideformfield" value=""></td>
			      </tr>
			      <tr> 
			        <td>Passwort:</td>
			        <td align="right"><input type="password" maxlength="255" name="pass" class="wideformfield" value=""></td>
			      </tr>
			      <tr> 
			        <td>&nbsp;</td>
			        <td align="right"><input type="image" border="0" src="../images/buttons/anmelden.gif"></td>
			      </tr>
				</table>
			</form>
		</div>
		<br><br><br>
		<div class="titelGanzStreifen">
		</div>
		<div class="titelGanz">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			NEU ANMELDEN
		</div>
		
		<div class="inhaltGanz">
			Nach der Anmeldung kannst Du in der Berichtdatenbank stöbern oder Deinen eigenen Praktikumsbericht abgeben.<br>
			Die Anmeldung ist nur für Studenten der Fachhochschule Lübeck (FHL) vorgesehen. Um dich zu verifizieren, musst Du daher Deine E-Mail Adresse der FHL angeben.<br>  
			Die normale E-Mail Adresse (die sich auch mit der FHL-Adresse decken kann), wird zum Mail-Verkehr mit dem Tool genutzt. Hast Du Deine Daten richtig eingetragen, bekommst Du ein Passwort an die FHL-E-Mail Adresse zugeschickt.<br>
			Mit diesem Passwort musst Du Dich innerhalb der nächsten 48h einloggen, sonst wird Deine Anmeldung automatisch gel&ouml;scht.<br><br>
		
			<form name="form0" method="post" action="login.php">
			    <table cellspacing="0" border="0">

			      <tr>
			        <td width="150">Matrikelnummer:</td>
			        <td><input type="text" name="matrnr" class="formfield">
			        	<input type="hidden" name="aktion" value="1">
			        </td>
			      </tr>
			      <tr> 
			        <td>Name:</td>
			        <td ><input type="text" maxlength="255" name="name" class="formfield"></td>
			      </tr>
			      <tr> 
			        <td>Vorname:</td>
			        <td ><input type="text" maxlength="255" name="vorname" class="formfield"></td>
			      </tr>
			      <tr> 
			        <td>E-Mail:</td>
			        <td ><input type="text" name="email" maxlength="100" class="formfield"></td>
			      </tr>
			      <tr> 
			        <td>FHL-E-Mail:</td>
			        <td ><input type="text" name="fhemail" maxlength="15" class="formfield">@stud.fh-luebeck.de</td>
			      </tr>
			      <tr>
			      	<td>&nbsp;</td>
			        <td><input type="image" border="0" src="../images/buttons/neuanmelden.gif"></td>
			      </tr>
			    </table>
			</form>
		</div>	
		<br><br><br>
		<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			PASSWORT VERGESSEN?
		</div>
		<div class="inhaltHalb">
			Gib hier Deine Matrikelnummer ein und Du bekommst innerhalb von wenigen Minuten ein neues Passwort auf Deine E-Mail Adresse zugesandt.<br><br>
			<form name="form2" method="post" action="login.php">
			    <table border="0" cellspacing="0">
			      <tr> 
			        <td>Matrikelnummer:</td>
			        <td align="right"><input type="text" name="matrnr" class="formfield">
			        	<input type="hidden" name="aktion" value="2"></td>
			      </tr>
			      <tr>
			      	<td>&nbsp;</td>
			        <td align="right"><input type="image" border="0" src="../images/buttons/verschicken.gif"></td>
			      </tr>
				</table>
			</form>
		</div>


	
</div>

<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>

</html>
