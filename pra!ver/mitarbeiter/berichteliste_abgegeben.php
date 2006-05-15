<?php


	include_once("include_mitarbeiter.php");
	
	$THIS_SITE = "bericht_abgegeben.php";
	
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
	$menue = Menu::EINPFLEGEN;
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
			<img border="0" src="../images/<?php echo 'ico_inhalt.gif'; ?>">
			ZU KONTROLLIERENDE BERICHTE
		</div>

		<div class="inhaltGanz">
		
			<table border="0" cellspacing="0" class="parallelTable">
		    <tr>
		      
		      <td valign="top">
		      	
		      			
						Dieses System dient der digitalen Abgabe des Berichtes zum Berufspraktischen Semester.<br>Es folgt eine Liste von Berichten, die Sie es noch zu kontrollieren gilt.<br><br>
		      					
										<?php
											if ($zuBearbeitendeBerichte) {
											
											if ($zuBearbeitendeBerichte->rowsCount()==0){
												
												echo "<br>Es liegen keine neuen Berichte vor!";
												
											}else{
											  echo '<table border="0" cellspacing="0">
														
														<tr>
															<td width="150" class="dick">
																Name
															</td>
															<td width="150" class="dick">
																Matrikelnumer
															</td>
															<td width="150" class="dick">
																Unternehmen
															</td>
															<td width="150" class="dick">
																Abgabeversuch
															</td>
														</tr>';
											  while ($r = $zuBearbeitendeBerichte->getNextRow()) {
												
												
												
													$nameValue= $r[3]." ".$r[2];
													$matrikelnummerValue = $r[6];
													$unternehmenValue = $r[8];
													$abgabeversuchValue = $r[1];
												?><tr><td><a class="db" href="bericht_kontr.php?getberichtid=<? echo ($r[0]); ?>">
												<?
													
													echo ($nameValue);
													
													
												?></a><br></td>
												
												<td><a class="db" href="bericht_kontr.php?getberichtid=<? echo ($r[0]); ?>">
												<?
													
													echo ($matrikelnummerValue);
													
													
												?></a><br></td>
												
												<td><a class="db" href="bericht_kontr.php?getberichtid=<? echo ($r[0]); ?>">
												<?
													
													echo ($unternehmenValue);
													
													
												?></a><br></td>
												
												<td><a class="db" href="bericht_kontr.php?getberichtid=<? echo ($r[0]); ?>">
												<?
													
													echo ($abgabeversuchValue);
													
													
												?></a><br></td></tr>
												
												<?
												
											  } // while
											  echo '</table>';
											}
											} // if
										
									
								
								//<br><br><br><br><a  href="bericht_kontrollierte.php">abgegebene Berichte suchen</a><br><br>
		      ?>
		      </td>
		
		    </tr>
		  </table>
	</div>

</div>

</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
