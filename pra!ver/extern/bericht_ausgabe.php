<?php
	include("../libs/libraries.php");

	$err = new ErrorQueue();
	$conn = new Connection();

	if ($conn->connect($DB_SERVER, $DB_NAME, $DB_USERNAME, $DB_PASSWORD)) {
		
		$berichtid = -1;
		if (isset($_GET['berichtid'])) {
			$berichtid = intval($_GET['berichtid']);
		}
		$gruppe = -1;
		if (isset($_GET['gruppe'])) {
			$gruppe = intval($_GET['gruppe']);
		}
	
?>


<html>
<head>
<title>Bericht-Datenbank</title>
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

<div class="hintergrundstreifen">
	

	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>

	<div class="titelGanzStreifen">
	</div>
	<div class="titelGanz">
		<img src="../images/ico_inhalt.gif" border="0" />
		BERICHTDETAIL
	</div>

	<div class="inhaltGanz">

		<table border="0" cellspacing="0" class="parallelTable">
	    <tr>
	      
	      <td valign="top">
	      	
	      		<? //hier die funktion zur ausgabe aufrufen
	      		//TODO Mail Formular schreiben
	              echo Bericht::zeigeBerichtOeffentlich($conn,$err,$berichtid, $gruppe);
	          ?>
	      		
	      		
			    
		     
	      
	
	
	      </td>
	
	    </tr>
	  </table>
	</div>

</div>
<br><br><br>
<?
} else {
      		$err->addError($conn->getLastError());
}
?>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
