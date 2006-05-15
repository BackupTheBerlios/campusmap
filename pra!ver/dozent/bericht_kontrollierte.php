<?php


	include_once("include_dozent.php");
	
	$THIS_SITE = "bericht_kontrollierte.php";
	
	$_SESSION['backto'] = $THIS_SITE;
	
	$aktion = "";
	if (isset($_POST['aktion'])) {
		$aktion =($_POST['aktion']);
	}
	if (isset($_POST['suchen_x'])) {
		$aktion = "suchen";
	}

	if ($aktion =="suchen"){
		
		$suchtext="";
		if (isset($_POST['suchtext'])) {
			$suchtext =($_POST['suchtext']);
		}
		
		$dozentenid = $dozent->getID();
		
		if ($suchtext == "")
			$aktion="";
		else
			$suchergebnis = Bericht::sucheBericht($conn, $err, $suchtext, $dozentenid);

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
	$menue = Menu::EINPFLEGEN;
	include("menu.php");
?>

<div>
<h3>Kontrolle des Berichtes zum Berufspraktischen Semester</h3>


	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>
<div class="hintergrundstreifen">

 <? if($aktion!="suchen"){?>	
	<div class="titelGanzStreifen">
	</div>	
	<div class="titelGanz">
		<img src="../images/ico_inhalt.gif" border="0" />
		BERICHT SUCHEN
	</div>

	<div class="inhaltGanz">
		
			<table border="0" cellspacing="0" class="parallelTable">
			    <tr>
			      
			      <td valign="top">
			     
			      		<form name="form1" method="post" action="bericht_kontrollierte.php">
						  	
						     
							    <table border="0" cellspacing="0">
									<tr>
							      		<td>
							      			Bitte geben Sie eine Matrikelnummer oder den Vornamen oder den Nachnamen des Studenten zur Suche ein!<br> 
										</td>
								
								    </tr>
									<tr>
							      		<td>					    
								    
										    <p>
					    					<input name="suchtext" type="text" size="30" maxlength="30"><br><br><input type="image" name="suchen" border="0" src="../images/buttons/suchen.gif" value="suchen">
					  						</p>
				  						
				  						</td>
									
								    </tr>
								    
								    
								 </table>	
								 		
							</form>	
							
					</td>
					</tr>		
				</table>
		</div>	
<? }else if($aktion=="suchen") {?>
							<div class="titelGanzStreifen">
							</div>	
							<div class="titelGanz">
								<img src="../images/ico_inhalt.gif" border="0" />
								BEARBEITETE BERICHTE
							</div>
						
							<div class="inhaltGanz">
		
								<table border="0" cellspacing="0" class="parallelTable">


											<?php
												if ($suchergebnis) {
												
												if ($suchergebnis->rowsCount()==0){
													
													echo "kein Suchergebnis";
													
												}else{
													
													?>

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
									
													</tr>
													<?
													
												  while ($r = $suchergebnis->getNextRow()) {
													
													
													
														$nameValue= $r[3]." ".$r[2];
														$matrikelnummerValue = $r[6];
														$unternehmenValue = $r[8];
														$abgabeversuchValue = $r[1];
													?><tr><td><a class="db" href="bericht_ausgabe.php?berichtid=<? echo ($r[0]); ?>">
													<?
														
														echo ($nameValue);
														
														
													?></a><br></td>
													
													<td><a class="db" href="bericht_ausgabe.php?berichtid=<? echo ($r[0]); ?>">
													<?
														
														echo ($matrikelnummerValue);
														
														
													?></a><br></td>
													
													<td><a class="db" href="bericht_ausgabe.php?berichtid=<? echo ($r[0]); ?>">
													<?
														
														echo ($unternehmenValue);
														
														
													?></a><br></td>
													
													<td><a class="db" href="bericht_ausgabe.php?berichtid=<? echo ($r[0]); ?>">
													<?
														
														echo ($abgabeversuchValue);
														
														
													?></a><br></td></tr>
													
													<?
													
												  } // while
												}
												} // if
											?>
										
									
									
								
								
								<tr>
									<td colspan="4">
									<br><br><br><a  href="bericht_kontrollierte.php">weitere abgegebene Berichte suchen</a>
									</td>
									
								</tr>
							</table>
				
					</div>

			
<? }?>
</div>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
