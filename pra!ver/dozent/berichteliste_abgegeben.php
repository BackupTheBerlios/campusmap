<?php


	include_once("include_dozent.php");
	
	$THIS_SITE = "bericht_kontr.php";
	
	$_SESSION['backto'] = $THIS_SITE;
	
	if ($dozent->isLoggedIn()) {
		
		$dozentenid = $dozent->getID();
		
		$sachbearbeiterStgang = Studiengang::getStudiengaengeVomSachbearbeiter($conn, $dozentenid);
		$r = $sachbearbeiterStgang->getNextRow();
		$sachbearbeiterBericht = Bericht::enumBerichteZurKontrolle($conn, $err, $dozentenid, 0);
		
		if (!$r[0]) {
			
			include("kein_sachbearbeiter.php");
			exit(0);
		}
		
		
		
	} // if LOGGED IN
	

				



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
<h3>Kontrolle des Berichtes zum Berufspraktischen Semester</h3>
	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>
</div>
<div class="hintergrundstreifen">
		<div class="titelGanzStreifen">
		</div>
		<div class="titelGanz">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			PRAKTIKA VERWALTUNG
		</div>

	<div class="inhaltGanz">
		
		<table border="0" cellspacing="0" class="parallelTable">
	    <tr>
	      
	      <td valign="top">
	      	
	      			
					Dieses System dient der digitalen Abgabe des Berichtes zum Berufspraktischen Semester.<br>Es folgt eine Liste von Berichten, die Sie es noch zu kontrollieren gilt.<br><br>
	      					
	      			
	      			<?php
					if ($sachbearbeiterBericht) {
					
					if ($sachbearbeiterBericht->rowsCount()==0){
						
						echo "es liegen keine neuen Berichte vor";
						
					}else{
					?>
		  			<table border="0" cellspacing="0">
	    			
						
						<tr>
							<td width="150" class="fett">
							Name
							</td>
							
							<td width="150" class="fett">
							Matrikelnumer
							</td>
							
							<td width="150" class="fett">
							Unternehmen
							</td>
							
							<td width="150" class="fett">
							Abgabeversuch
							</td>
							
						</tr>
							<?
							
							  while ($r = $sachbearbeiterBericht->getNextRow()) {
								
								
								
									$nameValue= $r[3]." ".$r[2];
									$matrikelnummerValue = $r[6];
									$unternehmenValue = $r[8];
									$abgabeversuchValue = $r[1];
								?><tr><td><a class="db" href="bericht_kontr.php?aktion2=1&berichtid=<? echo ($r[0]); ?>">
								<?
									
									echo ($nameValue);
									
									
								?></a><br></td>
								
								<td><a class="db" href="bericht_kontr.php?aktion2=1&berichtid=<? echo ($r[0]); ?>">
								<?
									
									echo ($matrikelnummerValue);
									
									
								?></a><br></td>
								
								<td><a class="db" href="bericht_kontr.php?aktion2=1&berichtid=<? echo ($r[0]); ?>">
								<?
									
									echo ($unternehmenValue);
									
									
								?></a><br></td>
								
								<td><a class="db" href="bericht_kontr.php?aktion2=1&berichtid=<? echo ($r[0]); ?>">
								<?
									
									echo ($abgabeversuchValue);
									
									
								?></a><br></td></tr>
								
								<?
								
							  } // while
										  echo '</table>';
										}
										} // if
									?>
								
							
							<br><br><br><br><a  href="bericht_kontrollierte.php">abgegebene Berichte suchen</a><br><br>
		
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
