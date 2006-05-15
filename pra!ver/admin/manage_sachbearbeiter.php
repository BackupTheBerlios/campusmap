<?php
	/* manage_sachbearbeiter.php 
	* Hier werden Sachbearbeiter vom Administrator verwaltet
	*  Author: Christian Burghoff
	*  letzte Änderung: 22.01.2006
	*/
	
	include("../libs/libraries.php");
	
	$err = new ErrorQueue();
	$conn = new Connection();
	$meldung = ""; // Diese Meldung wird angezeigt
	
	// Unterschiedliche Bereiche dürfen eine zweistellige Nummer haben, damit kennzeichnen sie 
	// bestimmte aktionen als gelungen oder nicht.
	// Beispiel. Der Bereich zum Einfügen der Dozenten hat ok=30, wenn eine aktion erfolgreich
	// durchgeführt wurde oder 31, wenn die Aktion fehlschlägt.
	$ok = 0;
	
	if ($conn->connect($DB_SERVER, $DB_NAME, $DB_USERNAME, $DB_PASSWORD)) {
	
		$aktion = 0;
		if (isset($_GET['aktion'])) {
			$aktion = intval($_GET['aktion']);
		}
		$StudiengangID = 0;
		if (isset($_GET['StudiengangID'])) {
			$StudiengangID = intval($_GET['StudiengangID']);
		}
		$DozentID = 0;
		if (isset($_GET['dozentid'])) {
			$DozentID = intval($_GET['dozentid']);
		}
		$MitarbeiterUsername = "";
		if (isset($_GET['mitarbeiterusername'])) {
			$MitarbeiterUsername = $_GET['mitarbeiterusername'];
		}
		
	//	echo $DozentID;

		if($aktion == 2){
		  
		    if ((Studiengang::setSachbearbeiter($conn, $StudiengangID, $DozentID, $MitarbeiterUsername, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
	  
	  if($aktion == 4){
		  
		    $DozentID=0;
		    $MitarbeiterUsername=0;
		  
		    if ((Studiengang::setSachbearbeiter($conn, $StudiengangID, $DozentID, $MitarbeiterUsername, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
		
		
		// Vorhandene Studiengänge suchen.
		$vorhandene_studiengaenge = "";
		$select_query = new DBQuery("SELECT s.studiengangID, s.name, s.PraktikaMitarbeiterUsername, s.PraktikaSachbearbeiterID, d.Name, s.Status, m.Username, m.Name FROM studiengang s LEFT JOIN dozent d ON (d.PDozentID=s.PraktikaSachbearbeiterID) LEFT JOIN mitarbeiter m ON (m.Username=s.PraktikaMitarbeiterUsername) ORDER BY s.name");
		if (!($result = $conn->executeQuery($select_query))) {
			$err->addError("Die Studiengänge konnten von der Datenbank nicht gelesen werden. ".$conn->getLastError());
			if ($ok == 0 || $ok == 10) $ok = 21;
		} else {
			//Tabelle bilden
    if ($result->rowsCount() > 0) {
				$vorhandene_studiengaenge .= '<table border="0" cellspacing="0" cellpadding="0" class="studiengangTable">';
		      	$vorhandene_studiengaenge .= '<tr><td colspan="7"><br> </td></tr>';
		      	$vorhandene_studiengaenge .= '<tr><td width="27%" class="tableTitle">Studiengang</td>';
		      	$vorhandene_studiengaenge .= '<td width="5%" class="tableTitle"></td>';
		      	$vorhandene_studiengaenge .= '<td width="20%" class="tableTitle">Sachbearbeiter</td>';
		      	$vorhandene_studiengaenge .= '<td width="5%" class="tableTitle"></td>';
		      	$vorhandene_studiengaenge .= '<td width="20%" class="tableTitle">Mitarbeiter</td>';
		      	$vorhandene_studiengaenge .= '<td width="5%" class="tableTitle"></td>';
		      	$vorhandene_studiengaenge .= '<td width="18%" class="tableTitle">Aktion</td></tr>';
		      	$vorhandene_studiengaenge .= '<tr><td colspan="7"><br> </td></tr>';
				while ($r = $result->getNextRow()) {
				if($r[5]==0){
					$vorhandene_studiengaenge .= '<tr>';
				    $vorhandene_studiengaenge .= '<td valign="top" >'.$r[1].'<br><br></td>';
				    $vorhandene_studiengaenge .= '<td></td>';
				    $vorhandene_studiengaenge .= '<td valign="top" >'.$r[4].'<br><br></td>';
				    $vorhandene_studiengaenge .= '<td></td>';
				    $vorhandene_studiengaenge .= '<td valign="top" >'.$r[7].'<br><br></td>';
				    $vorhandene_studiengaenge .= '<td ></td>';
				    $vorhandene_studiengaenge .= '<td valign="top" ><a href="manage_sachbearbeiter.php?StudiengangID='.$r[0].'&dozentid='.$r[3].'&mitarbeiterusername='.$r[2].'&aktion=1">Editieren</a>';
            if($r[3]!=0)$vorhandene_studiengaenge .= ' | <a href="manage_sachbearbeiter.php?StudiengangID='.$r[0].'&aktion=4" onClick="return conf()">L&ouml;schen</a>';
            //$vorhandene_studiengaenge .= '<td valign="top"><br><br></td>';
					$vorhandene_studiengaenge .= '</tr>';
					}
				}
				$vorhandene_studiengaenge .= "</table>";
			} else {
				$vorhandene_studiengaenge = "Keine";
			}
			
		}
   

	} else {
		$err->addError($conn->getLastError());
	}
	
	
	if (($dozenumeraton = Dozent::enumDozenten($conn)) == false) {
		unset($dozenumeraton);
	} else {
		$err->addError($conn->getLastError());
	}
	
	$allemitarbeiter = new UtilBuffer();
	Mitarbeiter::getAlleMitarbeiter($conn, $err, $allemitarbeiter);
	
	if (($studiengaegenum = Studiengang::enumStudiengaenge($conn)) == false) {
		unset($studiengaegenum);
	} else {
		$err->addError($conn->getLastError());
	}
	


?>

<html>
<head>
<title>Praktikumssachbearbeiter verwalten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/admin.css" type="text/css">

<script language="javascript">
	function conf() {
		var ok = confirm("Hiermit wird der Zugriff für die Sachbearbeiter und Mitarbeiter gelöscht. Ist kein Sachbearbeiter eingetragen, so können auch keine Berichte abgegeben oder kontrolliert werden. Bevor Sie einen Mitarbeiter löschen und keinen Neuen auswählen, sollten Sie sicher gehen, dass dieser keine Berichte in Bearbeitung hat. Diese werden erst wieder bearbeitbar, sobald ein neuer Mitarbeiter ausgewählt wird. Wollen Sie mit dem Löschen fortfahren?");
		return ok;
	}

</script>

</head>

<body leftmargin="0" topmargin="0">

<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top">
<?php include("../libs/kopf.php"); ?>

<?php
	$menu = Menu::SACHBEARBEITER;
	include("menu.php");
?>

<div>
	<h3>Praktikumssachbearbeiter verwalten</h3>
	
	<?php
		if ($err->moreErrors()) {
			echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
		}
		
		if ($meldung != "") {
			echo '<p class="meldung">'.$meldung.'</p>';
		}
	?>
</div>

<?php

  if($aktion==1){
  
?>

<div class="hintergrundstreifen">
	<div class="floatleft">
		<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			STUDIENGANG ZUORDNEN
		</div>
		<div class="inhaltHalb">

			<form name="fzugrif" method="get" action="manage_sachbearbeiter.php">
			<input type="hidden" name="aktion" value="2">
			<input type="hidden" name="StudiengangID" value="<?php echo $StudiengangID; ?>">
			<table border="0" cellspacing="0">
				
	      	<tr>
	  			<td colspan="2">
	  				<?php
	  					if ($studiengaegenum) {
	  						
	  						while ($r = $studiengaegenum->getNextRow()) {
	  						  
	               				 if($r[0]==$StudiengangID){
	  							   
	                   				echo "Bitte ordnen sie dem Studiengang <br><br>[ $r[1] ]<br><br> einen Sachbearbeiter<br>und einen Überprüfer zu!<br><br>";
	  							}
	  					   }
	  						
	  					}
	  				?>
	  			</td>
	  		</tr>
	      
	      	<tr>
					<td colspan="2">
					<?php
						if ($dozenumeraton) {
							echo 'Sachbearbeiter:<br><select name="dozentid" size="8" class="largeformfield" onClick="">'; //return showusername(this)
							while ($r = $dozenumeraton->getNextRow()) {
								$tt = $r[4];
								if ($tt == "") $tt = 'KEIN ZUGRIFF!';
								$sel = "";
								if ($dozentid==$r[0])$sel = ' selected="selected" ';
								echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].' ['.$tt.']</option>';
							}
							echo '</select>';
						}
					?>
					</td>
				</tr>
				
				<tr>
					<td colspan="2">
					<?php
						if ($allemitarbeiter) {
							echo '<br>Überprüfer:<br><select name="mitarbeiterusername" size="8" class="largeformfield" onClick="">'; //return showusername(this)
							for ($i = 0; $i < $allemitarbeiter->getCount(); $i++) {
								$mit = $allemitarbeiter->get($i);
								$sel = "";
								if ($MitarbeiterUsername==$mit->getUsername()) {
									$sel = ' selected="selected" ';
								}
								if (!($mit->getUsername()=="keiner" && $mit->getName()==""))
									echo '<option value="'.$mit->getUsername().'"'.$sel.'>'.$mit->getName().'['.$mit->getUsername().']</option>';
							}
							echo '</select>';
						}
					?>
					</td>
				</tr>
	
				<tr>
					<td>&nbsp;</td>
					<td align="right"><input type="image" border="0" src="../images/buttons/speichern.gif"></td>
				</tr>
			</table>
			</form>
		</div>
	</div>
</div>

</td>

</tr>
</table>
<?php

  }


if($aktion==0 || $aktion==2 || $aktion==4){
  
?>

<div class="hintergrundstreifen">
	<div class="titelGanz">
			
		
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
		STUDIENGÄNGE MIT ZUSTÄNDIGEN SACHBEARBEITER
					
	</div>
	
	<div class="inhaltGanz">
		<div class="normAbstand">
		Ist einem Studiengang kein Sachbearbeiter zugewiesen, so können für diesen auch keine Bericht abgegeben werden. Der Studiengang nimmt dann also quasi nicht an diesem Tool teil.
	    	<?php echo $vorhandene_studiengaenge; ?>
	    </div>
   </div> 
</div>

<?php

  }
  
?>

<br />
<br />
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
