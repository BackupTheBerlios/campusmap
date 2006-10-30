<?php


	include_once("include_student.php");
	
	$THIS_SITE = "datenbank.php";
	
	$_SESSION['backto'] = $THIS_SITE;
	
	if ($student->isLoggedIn()) {
		
		// Wenn der Student noch keinen Studiengang hat, muss er einen auswählen
		if (!$student->hatStudiengang()) {
			$_SESSION['txt'] = "Sie m&uuml;ssen einen Studiengang ausw&auml;hlen";
			include("einstellen.php");
			exit(0);
		}		
	} // if LOGGED IN
	

		$berichtid = -1;
		if (isset($_GET['berichtid'])) {
			$berichtid = intval($_GET['berichtid']);
		}
	
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


		<table border="0" cellspacing="0"  class="parallelTable" >
		    <tr>
		      
		      <td valign="top">
		      	
		      		<? //hier die funktion zur ausgabe aufrufen
		              echo Bericht::zeigeBerichtInternExtern($conn,$err,$berichtid, $student->getMatrNr());
		              
		              $bericht = new Bericht($conn);
					  $bericht->initAusDatenbank($berichtid);
					  
					  if ($bericht && $bericht->getInited() && $bericht->getMatrNr() == $student->getMatrNr())
		              echo Bericht::zeigeBerichtFile($conn,$err,$bericht);
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
