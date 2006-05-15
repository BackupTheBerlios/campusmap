<?php


	include_once("include_dozent.php");
	
	$THIS_SITE = "bericht_ausgabe.php";
	
	$_SESSION['backto'] = $THIS_SITE;
	
	

		
		$berichtid = -1;
		if (isset($_GET['berichtid'])) {
			$berichtid = intval($_GET['berichtid']);
		}
		$bericht = new Bericht($conn);
		$bericht->initAusDatenbank($berichtid);
		$student = Student::readStudent($conn, $err, $bericht->getMatrNr());
		
		
		if (($freigabeenum = Bericht::enumFreigaben($conn, $err))) {
		} else {
			$err->addError($conn->getLastError());
		}
		
		$freigabe=0;
		if (isset($_GET['freigabeauswahl'])) {
			$freigabe = intval($_GET['freigabeauswahl']);
		}
		
		
		if($freigabe!=0){
			$bericht->setFreigabe($freigabe); 
			$bericht->updateDatenbank(); 
			
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
	              echo Bericht::zeigeBerichtInternExtern($conn,$err,$berichtid, $student->getMatrNr());
	              
	              if ($student->getStudiengang()->getSachbearbeiterID() == $dozent->getID()){
	              
	             	$html_bericht .= '<table border="0" cellspacing="0" cellpadding="0" >';
	             	$html_bericht .= '<tr><td width="200" class="dick" valign="top" >Freigabeform:</td><td valign="bottom"><form method="get" action="bericht_ausgabe.php">'; 
					$html_bericht .= '<input type="hidden" name="berichtid" value="'.$berichtid.'"><select name="freigabeauswahl">';
		    			if ($freigabeenum) {
			    			while ($r = $freigabeenum->getNextRow()) {
									$sel = ""; if ($r[1]==$bericht->getFreigabeBereich())$sel = ' selected="selected" ';
									$html_bericht .= '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
							}
						}
								
					$html_bericht .= '</select>&nbsp;&nbsp;&nbsp;<input type="image" border="0" src="../images/buttons/aendern.gif" ></form>';
					$html_bericht .= '</td></tr></table>';
	             	echo $html_bericht;
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
