<?php

	session_start();

	include("../libs/libraries.php");

	function invokeLogin($txt) {
		$_SESSION['txt'] = $txt;
		include("login.php");
		exit();
	}

	if (isset($_POST['pass']) && isset($_POST['matrnr'])) {
		$matrnr = $_POST['matrnr'];
		$_SESSION['mdpass'] = md5($_POST['pass']);
		$_SESSION['matrnr'] = $matrnr;
		$pass = "";
	}

	$err = new ErrorQueue();
	$conn = new Connection();
	$student = new Student($conn);
	$meldung = "";
	
	if (!isset($_SESSION['mdpass']) || !isset($_SESSION['matrnr'])) {
		invokeLogin("Geben Sie Ihre Matrikelnummer und Ihr Passwort ein!");
	} else {
		if ($conn->connect(Config::DB_SERVER, Config::DB_NAME, Config::DB_USERNAME, Config::DB_PASSWORD)) {
			if (!$student->init($_SESSION['matrnr'], $_SESSION['mdpass'])) {
				invokeLogin($student->getLastError());
			} else {
				TriggerStudent::OnLogin($conn, $err, $student->getMatrNr());
			}
		} else {
			$err->addError($conn->getLastError());
		}
		
	}
	
	$THIS_SITE = "bericht_durchleiten.php";
	
	$_SESSION['backto'] = $THIS_SITE;

	$berichtid = -1;
	if (isset($_GET['berichtid'])) {
		$berichtid = intval($_GET['berichtid']);
	}
	$bericht = new Bericht($conn);
	$bericht->initAusDatenbank($berichtid);
	
	$fehlertext = "Der angegebene Bericht existiert nicht.";
	
	if ($bericht->getInited()) {
		if ($student->getMatrNr() == $bericht->getMatrNr())
			$fehlertext = $bericht->leiteDateiDurch();
		else
			$fehlertext = "Du kannst aus leider nur auf Deine eigene Berichtdatei zugreifen.";
	}
			
		
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
	$menue = Menu::DATENBANK;
	include("menu.php");
?>

<div>
<h3>Berichtdatei lesen</h3>


	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>
</div>

<div class="hintergrundstreifen">

	<div class="titelGanz">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
					ES TRAT EIN FEHLERBEIMLESEN DER DATEI AUF!
	
	</div>

	<div class="inhaltGanz">

		<table border="0" cellspacing="0" class="parallelTable">
	    <tr>
	      
	      <td valign="top">
	      	
	      		<span style="color:#ff0000"> <? echo $fehlertext ?> </span>
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
