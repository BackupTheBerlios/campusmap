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
		$FachbereichID = 0;
		$neuerName = "kein Name";
		
		if (isset($_GET['aktion'])) {
			$aktion = intval($_GET['aktion']);
		}
		if (isset($_GET['FachbereichID'])) {
			$FachbereichID = intval($_GET['FachbereichID']);
		}
		if (isset($_GET['neuerName'])) {
			$neuerName = $_GET['neuerName'];
		}

		// Wenn ein Studiengang zu editieren ist
		if($aktion == 3){
		    if ((Fachbereich::setName($conn, $FachbereichID, $neuerName, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
	  // Wenn ein Fachbereich zu aktivieren ist
		if($aktion == 4){
		    if ((Fachbereich::setAktiv($conn, $FachbereichID, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
	  // Wenn ein Fachbereich zu löschen ist
		if($aktion == 5){
		    if ((Fachbereich::delete($conn, $FachbereichID, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
		

		// Wenn ein neuer Fachbereich einzufügen ist
		if (isset($_POST['insert']) && $_POST['insert'] == 1) {

			$name = $_POST['bezeichnung'];
			
			if ($name == "") $err->addError("Es wurde kein Fachbereichename eingegeben.");

			if (!$err->moreErrors()) {
				if ($f = Fachbereich::add($conn, $name, $err)) {
					$meldung = "Die Fachbereich wurde erfolgreich angelegt.";
					$ok = 40;
				}
			} else {
				$ok = 41;
			}
		}

		
		//Auflistung der Fachbereiche
		$vorhandene_Fachbereiche = "";
		if (!($result = Fachbereich::enumFachbereiche($conn))) {
			$err->addError("Die Fachbereiche konnten von der Datenbank nicht gelesen werden. ".$conn->getLastError());
			if ($ok == 0 || $ok == 10) $ok = 21;
		} else {
			//Tabelle bilden
    if ($result->rowsCount() > 0) {
				$vorhandene_Fachbereiche .= '<table border="0" cellspacing="0" cellpadding="0" class="StudiengangTable">';
      	$vorhandene_Fachbereiche .= '<tr><td width="50%" class="tableTitle">Fachbereich</td>';
      	$vorhandene_Fachbereiche .= '<td width="50%" class="tableTitle">Aktion</td>';
      	$vorhandene_Fachbereiche .= '<td valign="top">&nbsp;<br><br></td></tr>';
				while ($r = $result->getNextRow()) {
					$vorhandene_Fachbereiche .= '<tr>';
					if ($aktion==2 && $r[0]==$FachbereichID) {
			    	$vorhandene_Fachbereiche .= '<td valign="top"><form name="formName" method="get" action="manage_fachbereiche.php"><input type="hidden" name="aktion" value="3"><input type="hidden" name="FachbereichID" value="'.$FachbereichID.'">';
			    	$vorhandene_Fachbereiche .= '<input type="text" name="neuerName" class="formfield" value="'.$r[1].'"><br><input type="image" border="0" src="../images/buttons/aendern.gif"><a href="manage_fachbereiche.php"><img border="0" src="../images/buttons/verwerfen.gif"></a></form><br><br></td>';
			    }
			    else {
						if($r[2]!=0) $vorhandene_Fachbereiche .= '<td valign="top" class="ausgegraut">'.$r[1].'<br><br></td>';
					  else $vorhandene_Fachbereiche .= '<td valign="top">'.$r[1].'<br><br></td>';
					}
				  $vorhandene_Fachbereiche .= '<td valign="top"><a href="manage_fachbereiche.php?FachbereichID='.$r[0].'&aktion=2">Editieren</a>';
          if($r[2]!=0)	$vorhandene_Fachbereiche .= '| <a href="manage_fachbereiche.php?FachbereichID='.$r[0].'&aktion=4" onClick="return confAkt()"><span style="color:#00FF00">Aktivieren</span></a></td>';
          else					$vorhandene_Fachbereiche .= '| <a href="manage_fachbereiche.php?FachbereichID='.$r[0].'&aktion=5" onClick="return confLoesch()"><span style="color:#FF0000">Löschen</span></a></td>';
          $vorhandene_Fachbereiche .= '<td valign="top">&nbsp;<br><br></td>';
					$vorhandene_Fachbereiche .= '</tr>';
				}
				$vorhandene_Fachbereiche .= "</table>";
			} else {
				$vorhandene_Fachbereiche = "Keine";
			}
			
		}
		
	} else {
		$err->addError($conn->getLastError());
	}

?>


<html>
<head>
<title>Fachbereiche editieren</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/admin.css" type="text/css">

<script language="javascript">
	function confLoesch() {
		var ok = confirm("Hiermit löschen Sie die Fachbereich. Wollen Sie mit dem L&ouml;schen fortfahren?");
		return ok;
	}
	function confAkt() {
		var ok = confirm("Hiermit aktivieren die Fachbereich. Wollen Sie mit dem Aktivieren fortfahren?");
		return ok;
	}
</script>

</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	$Fachbereich_edit = 1; // Damit das "Neues Fach" Button erscheint
	$menu = Menu::FACHBEREICHE;
	include("menu.php");
?>

<div id="delfachHead">
<h3>Fachbereiche</h3>

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

		
			<div class="titelHalb"><?php if ($hideinsertWindow == 1) echo 'hidden'; ?>
						
				<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
				FACHBEREICH EINFÜGEN
			</div>

	<?php if ($hideinsertWindow != 1) { ?>
			
			<div class="inhaltHalb">
				<form name="formE" method="post" action="manage_fachbereiche.php">
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
			VORHANDENE FACHBEREICHE
		
	</div>

	<div class="inhaltGanz">
		<div class="normAbstand">
	
    	<?php echo $vorhandene_Fachbereiche; ?>
  		</div>
    </div>
</div>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
