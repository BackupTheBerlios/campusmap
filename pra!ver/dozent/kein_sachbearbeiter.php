<html>
<head>
<title>Dozent</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/studi.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	// Menue Bereich
	$menue = Menu::EINSTELLEN;
	include("menu.php");
?>

<div>
<?php

	echo "<h3> ".$dozent->getName()." (".$dozent->getUsername().")</h3>";

	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}
	
	if ($meldung != "") {
		echo '<p class="meldung">'.$meldung.'</p>';
	}
	
	// Txt wird normallerweise von anderen Seiten gesetzt
	// z.B. bei studi.php; Die Einstellungsseite wird aufgerufen, wenn kein Plan ausgewählt ist.
	if (isset($_SESSION['txt'])) {
		echo '<p class="meldung">'.$_SESSION['txt'].'</p>';
		unset($_SESSION['txt']);
	}
		
?>
</div>
<div class="hintergrundstreifen">
	

	<div class="titelHalb">
		<img src="../images/ico_inhalt.gif" border="0" />
		ACHTUNG!
	</div>

	<div class="inhaltHalb">
		
      		<?
              		echo ("Sie sind nicht als Sachbearbeiter eingeteilt!");
              ?>
		
	</div>

</div>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>