<?php


	include_once("include_dozent.php");
	
	$THIS_SITE = "bericht_ablehnen.php";
	
	$_SESSION['backto'] = $THIS_SITE;
	
	$berichtid =0;
	if (isset($_POST['berichtID'])) {
		$berichtid =($_POST['berichtID']);
	}

		
	$bericht = new Bericht($conn);
	$bericht->initAusDatenbank($berichtid);
	$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
	
	$aktion ="";
	if (isset($_POST['abschicken_x'])) {
		$aktion ="abschicken";
	}				
	
	if ($aktion == "abschicken"){
		
		if (isset($_POST['grund'])) {
			$text =($_POST['grund']);
		}
		
		$subj = "Bericht zum BpS wurde abgelehnt!";
		$email = $student->getEmail();
		$bericht->setBearbeitungszustand(1);
		$bericht->updateDatenbank();
		$text .= "\n\nBitte korrigiere Deinen Bericht gegebenfalls unter \r\n ".Config::PRAVER_ROOT_URL." \r\n";
		Mailer::mailit($email, $subj, $text);	
		
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
	$menue = Menu::EINSTELLEN;
	include("menu.php");
?>

<div>
<h3>Kontrolle des Berichtes zum Berufspraktischen Semester</h3>


	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>
</div>

<div class="hintergrundstreifen">
	


	<?php if($aktion == ""){?>
	<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
					"E-MAIL AN STUDIERENDEN"
	
	</div>

	<div class="inhaltHalb">
		
		<table border="0" cellspacing="0" class="parallelTable">
		    <tr>
		      
		      <td valign="top">
			  	<form name="form1" method="post" action="bericht_ablehnen.php">
			  		<input type="hidden" name="berichtID" value="<?echo $berichtid; ?>">
			      	Hiermit schreiben sie eine Mail an <?echo $student->getVorname()." ".$student->getName(); ?>, um mitzuteilen, dass sein Bericht in dieser Form nicht entgegen genommen werden kann!
			      	<br><br>
				    <table border="0" cellspacing="0">
						<tr>
				      		<td>
				      			Diese Mail wird elektronisch erstellt, somit bedarf es nur der Angabe des Grundes für das Ablehnen: <br> 
							</td>
					
					    </tr>
						<tr>
				      		<td>					    
					    
							    <p>
		    						<textarea name="grund" cols="25" rows="5"></textarea><br><br>
		  						</p>
	  						
	  						</td>
						
					    </tr>
					    <tr>
				      		<td>					    
					    
					    		<input type="image" name="abschicken" border="0" src="../images/buttons/verschicken.gif" value="abschicken">
					    	</td>
						
					    </tr>
					    
					 </table>	
					 		
				</form>			
		      </td>
		
		    </tr>
	  	</table>
	</div>
	
	<?php }else if($aktion == "abschicken"){?>
	<div class="titelGanzStreifen">
	</div>
	<div class="titelGanz">
			<img border="0" src="../images/<?php echo 'ico_haken.gif'; ?>">
					VIELEN DANK!
	
	</div>

	<div class="inhaltGanz">
		
		<table border="0" cellspacing="0" class="parallelTable">
		    <tr>
		      
		      <td valign="top">
			  	Sie haben soeben die Berichtabgabe abgelehnt und eine E-Mail an den Studenten mit dem Grund für das Ablehnen gesendet. Nun liegt der nächste Arbeitsschritt wieder beim Studenten.
		      </td>
		
		    </tr>
	  	</table>
	</div>
	<?php } ?>
</div>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
