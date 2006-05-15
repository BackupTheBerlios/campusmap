<?php
	session_start();
	
	include("../libs/libraries.php");

	$err = new ErrorQueue();
	
	$txt = "";
	$meldung = "";
	$ok = 0;
	$conn = new Connection();
	if ($conn->connect($DB_SERVER, $DB_NAME, $DB_USERNAME, $DB_PASSWORD)) {
		
		$aktion = 0;
		$StudiengangID = 0;
		$neuerName = "kein Name";
		
		if (isset($_GET['aktion'])) {
			$aktion = intval($_GET['aktion']);
		}
		if (isset($_GET['StudiengangID'])) {
			$StudiengangID = intval($_GET['StudiengangID']);
		}
		if (isset($_GET['fachbereichid'])) {
			$fachbereichid = intval($_GET['fachbereichid']);
		}
		if (isset($_GET['neuerName'])) {
			$neuerName = $_GET['neuerName'];
		}

		// Wenn ein Studiengang zu editieren ist
		if($aktion == 3){
		    if ((Studiengang::update($conn, $StudiengangID, $neuerName, $fachbereichid, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
	  
	  // Wenn ein Studiengang zu aktivieren ist
		if($aktion == 4){
		    if ((Studiengang::setAktiv($conn, $StudiengangID, true, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
	  // Wenn ein Studiengang zu deaktivieren ist
		if($aktion == 5){
		    if ((Studiengang::setAktiv($conn, $StudiengangID, false, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
		

		// Wenn ein neuer Studiengang einzufügen ist
		if (isset($_POST['insert']) && $_POST['insert'] == 1) {

			$name = $_POST['bezeichnung'];
			
			if ($name == "") $err->addError("Es wurde kein Studiengangsname eingegeben.");

			if (!$err->moreErrors()) {
				if ($f = Studiengang::insertStudiengang($conn, $name, $fachbereichid, $err)) {
					$meldung = "Der Studiengang wurde erfolgreich angelegt.";
					$ok = 40;
				} else {
					$ok = 41;
					$err->addError("Der Studiengang existiert schon.");
				}
			} else {
				$ok = 41;
			}
		}

		if (($fachbereichenumeration = Fachbereich::enumAktiveFachbereiche($conn, $err))) {}
		else $err->addError($conn->getLastError());
		
		//Auflistung der Studiengänge
		$vorhandene_studiengaenge = "";
		$select_query = new DBQuery("SELECT studiengangID, name, PraktikaMitarbeiterUsername, PraktikaSachbearbeiterID, Status, FachbereichID FROM studiengang ORDER BY name");
		if (!($result = $conn->executeQuery($select_query))) {
			$err->addError("Die Studiengänge konnten von der Datenbank nicht gelesen werden. ".$conn->getLastError());
			if ($ok == 0 || $ok == 10) $ok = 21;
		} else {
			//Tabelle bilden
    if ($result->rowsCount() > 0) {
				$vorhandene_studiengaenge .= '<table border="0" cellspacing="0" cellpadding="0" class="studiengangTable">';
		      	$vorhandene_studiengaenge .= '<tr><td width="40%" class="tableTitle">Studiengang</td>';
		      	$vorhandene_studiengaenge .= '<td width="5%" class="tableTitle"></td>';
		      	$vorhandene_studiengaenge .= '<td width="20%" class="tableTitle">Fachbereich</td>';
		      	$vorhandene_studiengaenge .= '<td width="5%" class="tableTitle"></td>';
		      	$vorhandene_studiengaenge .= '<td width="30%" class="tableTitle">Aktion</td></tr>';
				while ($r = $result->getNextRow()) {
					$vorhandene_studiengaenge .= '<tr>';
					$grau = "";
					if ($r[4]==1) $grau = ' class="ausgegraut"';
			    if ($aktion==2 && $r[0]==$StudiengangID) {
			    	$vorhandene_studiengaenge .= '<td valign="top" colspan="2"><form name="formName" method="get" action="manage_studiengaenge.php"><input type="hidden" name="aktion" value="3"><input type="hidden" name="StudiengangID" value="'.$StudiengangID.'">';
			    	$vorhandene_studiengaenge .= '<input type="text" name="neuerName" class="formfield" value="'.$r[1].'">&nbsp;&nbsp;&nbsp;';
			    	$vorhandene_studiengaenge .= '<select name="fachbereichid" size="1" class="formfield" onClick="">';
	    			if ($fachbereichenumeration) {
		    			while ($r2 = $fachbereichenumeration->getNextRow()) {
								$sel = "";
								echo $fachbereichid;
								if ($fachbereichid==$r2[0])$sel = ' selected="selected" ';
								$vorhandene_studiengaenge .= '<option value="'.$r2[0].'"'.$sel.'>'.$r2[1].'</option>';
							}
						}
			    	$vorhandene_studiengaenge .= '</select><br><input type="image" border="0" src="../images/buttons/aendern.gif"><a href="manage_studiengaenge.php"><img border="0" src="../images/buttons/verwerfen.gif"></a></form><br></td>';
			    }
			    else {
			    	$vorhandene_studiengaenge .= '<td valign="top"'.$grau.'>'.$r[1].'<br><br></td>';
			    	$vorhandene_studiengaenge .= '<td></td>';
			    	$vorhandene_studiengaenge .= '<td valign="top"'.$grau.'>'.Fachbereich::getName($conn, $r[5], $err).'<br><br></td>';
			    }
			    $vorhandene_studiengaenge .= '<td></td>';
			    $vorhandene_studiengaenge .= '<td valign="top"><a href="manage_studiengaenge.php?StudiengangID='.$r[0].'&fachbereichid='.$r[5].'&aktion=2">Editieren</a>';
          if($r[4]!=0)	$vorhandene_studiengaenge .= ' | <a href="manage_studiengaenge.php?StudiengangID='.$r[0].'&aktion=4" onClick="return confAkt()"><span style="color:#00FF00">Aktivieren</span></a>';
          else					$vorhandene_studiengaenge .= ' | <a href="manage_studiengaenge.php?StudiengangID='.$r[0].'&aktion=5" onClick="return confDeakt()"><span style="color:#FF0000">Deaktivieren</span></a>';
          $vorhandene_studiengaenge .= '<td valign="top"><br><br></td>';
					$vorhandene_studiengaenge .= '</tr>';
				}
				$vorhandene_studiengaenge .= "</table>";
			} else {
				$vorhandene_studiengaenge = "Keine";
			}
			
		}

	} else {
		$err->addError($conn->getLastError());
	}

?>


<html>
<head>
<title>Studieng&auml;nge editieren</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/admin.css" type="text/css">

<script language="javascript">
	function confDeakt() {
		var ok = confirm("Hiermit deaktivieren Sie die gesamte Praktikaverwaltung für dieses Studiengang. Wollen Sie mit dem Deaktivieren fortfahren?");
		return ok;
	}
	function confAkt() {
		var ok = confirm("Hiermit aktivieren Sie die gesamte Praktikaverwaltung für dieses Studiengang. Wollen Sie mit dem Aktivieren fortfahren?");
		return ok;
	}
</script>

</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	$studiengang_edit = 1; // Damit das "Neues Fach" Button erscheint
	$menu = Menu::STUDIENGAENGE;
	include("menu.php");
?>

<div id="delfachHead">
<h3>Studieng&auml;nge</h3>

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
	if ($txt != "") {
		echo $txt;
	}
?>


<div class="hintergrundstreifen">

		
			<div class="titelGanz"><?php if ($hideinsertWindow == 1) echo 'hidden'; ?>
						
				<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
				STUDIENGÄNGE EINFÜGEN
			</div>

	<?php if ($hideinsertWindow != 1) { ?>
	<div class="inhaltGanz">
		<br>Achtung! Bitte keine unnötigen Eingaben.Studiengänge können nicht gelöscht,sondern nur deaktiviert und editiert werden.
		<form name="formE" method="post" action="manage_studiengaenge.php">
			<input type="hidden" name="insert" value="1">
			<table border="0" cellpadding="2" cellspacing="0">
			  <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
			  </tr>
			  <tr>
			    <td>Bezeichnung:</td>
			    <td><input type="text" name="bezeichnung" class="formfield" value="<?php if(isset($_POST['bezeichnung'])) echo $_POST['bezeichnung']; ?>"></td>
			  </tr>
			  <tr>
			    <td>Fachbereich:</td>
			    <td>
			    	<select name="fachbereichid" size="1" class="formfield" onClick="">
			    		<?php
			    			if ($fachbereichenumeration) {
				    			while ($r = $fachbereichenumeration->getNextRow()) {
										$sel = "";
										if (1==$r[0])$sel = ' selected="selected" ';
										echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
									}
								}
			    		?>
			    	</select>
			    </td>
			  </tr>
			  <tr>
			  	<td>&nbsp;</td>
			    <td align="right"><input type="image" border="0" src="../images/buttons/anlegen.gif"></td>
			  </tr>
			</table>
		</form>
	</div>
	<?php } ?>
</div>

<br><br><br>

<div class="hintergrundstreifen">
	

	<div class="titelGanz">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			VORHANDENE STUDIENGÄNGE
		
	</div>

	<div class="inhaltGanz">
		<div class="normAbstand">
	
    	<?php echo $vorhandene_studiengaenge; ?>
  		</div>
    </div>
</div>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
