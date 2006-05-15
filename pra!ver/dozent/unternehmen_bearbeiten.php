<?php


	include_once("include_dozent.php");
	
	$THIS_SITE = "unternehmen_bearbeiten.php";
	
	$_SESSION['backto'] = $THIS_SITE;
	
	$aktion = "";
	if (isset($_GET['aktion'])) {
		$aktion =($_GET['aktion']);
	}
	if (isset($_GET['suchtext'])) {
		$aktion = "suchen";
	}
	if (isset($_GET['unt_branche'])) {
		$aktion = "speichern";
	}
	
	if ($aktion =="suchen"){
		
		$suchtext="";
		if (isset($_GET['suchtext'])) {
			$suchtext =($_GET['suchtext']);
		}
		
		if ($suchtext == "")
			$aktion="";
		else
			$suchergebnis = Unternehmen::sucheUnternehmen($conn, $err, $suchtext);
	}
	if ($aktion =="bearbeiten"){
		$unternehmenid = 0;
		if (isset($_GET['unternehmenid'])) {
			$unternehmenid =($_GET['unternehmenid']);
		}
			
		if ($unternehmenid > 0) {
			$unternehmen = new Unternehmen($conn);
			$unternehmen->initAusDatenbank($unternehmenid);
			if (!($unternehmen->getInited())) {
				$err->addError("ID des Unternehmen falsch oder nicht angegeben.");
				$aktion = "";
			}
		}
		else {
			$err->addError("ID des Unternehmen falsch oder nicht angegeben.");
			$aktion = "";
		}
	}
	if ($aktion =="speichern") {
		$unternehmenid = 0;
		if (isset($_GET['unternehmenid'])) {
			$unternehmenid =($_GET['unternehmenid']);
		}
			
		if ($unternehmenid > 0) {
			$unternehmen = new Unternehmen($conn);
			$unternehmen->initAusDatenbank($unternehmenid);
			if (!($unternehmen->getInited())) {
				$err->addError("ID des Unternehmen falsch oder nicht angegeben.");
				$aktion = "";
			} else {
				if (isset($_GET['unt_name'])) {
					$unternehmen->setName($_GET['unt_name']);
				}
				if (isset($_GET['unt_url'])) {
					$unternehmen->setUrl($_GET['unt_url']);
				}
				if (isset($_GET['unt_strasse'])) {
					$unternehmen->setAdrStrasse($_GET['unt_strasse']);
				}
				if (isset($_GET['unt_plz'])) {
					$unternehmen->setAdrPLZ($_GET['unt_plz']);
				}
				if (isset($_GET['unt_ort'])) {
					$unternehmen->setAdrOrt($_GET['unt_ort']);
				}
				if (isset($_GET['unt_land'])) {
					$unternehmen->setStaatID(intval($_GET['unt_land']));
				}
				if (isset($_GET['unt_branche'])) {
					$unternehmen->setBrancheID(intval($_GET['unt_branche']));
				}
				if (isset($_GET['unt_groesse'])) {
					$unternehmen->setUnternehmensgroesseID(intval($_GET['unt_groesse']));
				}
				if ($unternehmen->updateDatenbank())
					$erfolgt = "Daten wurden übernommen.";
				else
					$erfolgt = "Die Daten konnten nicht gespeichert werden.<br><br>";
				$aktion = "";
			}
		}
		else {
			$err->addError("ID des Unternehmen falsch oder nicht angegeben.<br><br>");
			$aktion = "";
		}
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
<? if($aktion!="suchen" && $aktion!="bearbeiten"){ ?>
		<div class="hintergrundstreifen">
		
			
			<div class="titelGanzStreifen">
			</div>	
			<div class="titelGanz">
				<img src="../images/ico_inhalt.gif" border="0" />
				UNTERNEHMEN SUCHEN
			</div>
		
			<div class="inhaltGanz">
		
				<table border="0" cellspacing="0" class="parallelTable">
			    <tr>
			      
			      <td valign="top">
			      
			      		<form name="form1" method="get" action="unternehmen_bearbeiten.php">
						  	
						     <?php echo $erfolgt; ?>
							    <table border="0" cellspacing="0">
									<tr>
							      		<td>
							      			Bitte geben Sie den Namen des Unternehmens oder einen Teil des Namens zur Suche ein!<br> 
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

		</div>							
		<? }else if($aktion=="suchen") {?>
			<div class="hintergrundstreifen">

	
							<div class="titelGanzStreifen">
							</div>	
							<div class="titelGanz">
								<img src="../images/ico_inhalt.gif" border="0" />
								SUCHERGEBNIS
							</div>
						
							<div class="inhaltGanz">
								
											<table border="0" cellspacing="0" class="parallelTable">
										    <tr>
										      
										      <td valign="top">
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
															Ort
															</td>
															
															<td width="150" class="dick">
															Staat
															</td>
															
														</tr>
														<?
														
													  while ($r = $suchergebnis->getNextRow()) {
														
														
														
															$nameValue= $r[1];
															$ortValue = $r[2];
															$staatValue = $r[3];
														?><tr><td><a class="db" href="unternehmen_bearbeiten.php?aktion=bearbeiten&unternehmenid=<? echo ($r[0]); ?>">
														<?
															
															echo ($nameValue);
															
															
														?></a><br></td>
														
														<td><a class="db" href="unternehmen_bearbeiten.php?aktion=bearbeiten&unternehmenid=<? echo ($r[0]); ?>">
														<?
															
															echo ($ortValue);
															
															
														?></a><br></td>
														
														<td><a class="db" href="unternehmen_bearbeiten.php?aktion=bearbeiten&unternehmenid=<? echo ($r[0]); ?>">
														<?
															
															echo ($staatValue);
															
															
														?></a><br></td></tr>
														
														<?
														
													  } // while
													}
													} // if
												?>
											
										
										
									
									
										<tr>
											<td colspan="4">
											<br><br><br><br><a  href="unternehmen_bearbeiten.php">weitere Unternehmen suchen</a>
											</td>
										
										</tr>
									</table>
								</td>
					
					    	</tr>
					  	</table>
					</div>
		
				</div>
			<? } else if($aktion=="bearbeiten") {
				if (($unternehmensgroessenenum = Unternehmen::enumGroessen($conn, $err))) {
				} else {
					$err->addError($conn->getLastError());
				}
				if (($branchenenum = Unternehmen::enumBranchen($conn, $err))) {
				} else {
					$err->addError($conn->getLastError());
				};
				if (($laenderenum = Staat::enumStaaten($conn, $err))) {
				} else {
					$err->addError($conn->getLastError());
				}
				?>
				<div class="hintergrundstreifen">
			
				
					<div class="titelGanzStreifen">
					</div>	
					<div class="titelGanz">
						<img src="../images/ico_inhalt.gif" border="0" />
						UNTERNEHMEN BEARBEITEN
					</div>
				
					<div class="inhaltGanz">
			
						<table border="0" cellspacing="0" class="parallelTable">
					    <tr>
					      
					      <td valign="top">
							<br><br>
							<form name="form1" method="GET" action="unternehmen_bearbeiten.php">
							<input type="hidden" name="unternehmenid" value="<?php echo $unternehmenid; ?>">
							<table border="0" cellpadding="0" cellspacing="0">
			      	            <tr>
			      	              <td valign="top" width="150">
			      	                Name
			      	              </td>
			      	              <td>
			      	                <input name="unt_name" size="20" maxlength="40" value="<?php echo $unternehmen->getName();?>" type="text"><br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td valign="top">
			      	                URL
			      	              </td>
			      	              <td>
			      	                <input name="unt_url" size="20" maxlength="40" value="<?php echo $unternehmen->getUrl();?>" type="text"><br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td valign="top">
			      	                Straße / Hausnummer
			      	              </td>
			      	              <td>
			      	                <input name="unt_strasse" size="20" maxlength="40" value="<?php echo $unternehmen->getAdrStrasse();?>" type="text">&nbsp;&nbsp;<br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td valign="top">
			      	                PLZ
			      	              </td>
			      	              <td>
			      	                <input name="unt_plz" size="20" maxlength="40" value="<?php echo $unternehmen->getAdrPlz();?>" type="text"><br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td valign="top">
			      	                Ort
			      	              </td>
			      	              <td>
			      	                <input name="unt_ort" size="20" maxlength="40" value="<?php echo $unternehmen->getAdrOrt();?>" type="text"><br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	              <td valign="top">
			      	                Land&nbsp;&nbsp;
			      	              </td>
			      	              <td>
			      	              		<select name="unt_land" >
											<?php
								    			if ($laenderenum) {
									    			while ($r = $laenderenum->getNextRow()) {
															$sel = ""; if ($unternehmen->getStaatID()==$r[0]) $sel = ' selected="selected" ';
															echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
													}
												}
											?>
										</select>
										<br><br>
			      	              </td>
			      	            </tr>
			      	            <tr>
			      	    			<td valign="top">
									Branche
									</td>
			      	       			 <td>
							      	    <select name="unt_branche">
											<?php
								    			if ($branchenenum) {
									    			while ($r = $branchenenum->getNextRow()) {
															$sel = ""; if ($unternehmen->getBrancheID()==$r[0]) $sel = ' selected="selected" ';
															echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
													}
												}
											?>
										</select>
										<br><br>
									</td>
								</tr>
								<tr>
			      	    			<td valign="top">
									Mitarbeiterzahl
									</td>
			      	       			 <td>
							      	    <select name="unt_groesse">
										<?php
							    			if ($unternehmensgroessenenum) {
								    			while ($r = $unternehmensgroessenenum->getNextRow()) {
														$sel = ""; if ($unternehmen->getUnternehmensgroesseID()==$r[0]) $sel = ' selected="selected" ';
														echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
												}
											}
										?>
										</select>
										<br><br>
									</td>
								</tr>
							  </table>
							  <br><br><input type="image" name="speichern" border="0" src="../images/buttons/speichern.gif" value="speichern">
							  </form>
							  <br><br><br><br>
							  
							  
						</td>
			
			    	</tr>
			  	</table>
			</div>

		</div>

<? }?>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
