<?php


	session_start();

	include("../libs/libraries.php");

	function invokeLogin($txt) {
		$_SESSION['txt'] = $txt;
		include("login.php");
		exit();
	}

	if (isset($_POST['pass']) && isset($_POST['username'])) {
		$username = $_POST['username'];
		$_SESSION['mdpass'] = md5($_POST['pass']);
		$_SESSION['username'] = $username;
		$pass = "";
	}

	$err = new ErrorQueue();
	$conn = new Connection();
	$dozent = new Dozent($conn);
	$meldung = "";
	
	if (!isset($_SESSION['mdpass']) || !isset($_SESSION['username'])) {
		invokeLogin("Geben Sie Ihren Benutzernamen und Ihr Passwort ein!");
	} else {
		if ($conn->connect(Config::DB_SERVER, Config::DB_NAME, Config::DB_USERNAME, Config::DB_PASSWORD)) {
			if (!$dozent->init($_SESSION['username'], $_SESSION['mdpass'])) {
				invokeLogin($dozent->getLastError());
			} else {
				$err->addError($conn->getLastError());
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
		$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
		if ($student->getStudiengang()->getSachbearbeiterID() == $dozent->getID())
			$fehlertext = $bericht->leiteDateiDurch();
		else
			$fehlertext = "Sie sind nicht als Sachbearbeiter für den Studiengang des Berichtes eingetragen und haben daher leider keinen Zugriff auf die Berichtdatei.";
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
	$menue = Menu::KONTROLLIEREN;
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
