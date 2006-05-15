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
		$BrancheID = 0;
		$neuerName = "kein Name";
		
		if (isset($_GET['aktion'])) {
			$aktion = intval($_GET['aktion']);
		}
		if (isset($_GET['BrancheID'])) {
			$BrancheID = intval($_GET['BrancheID']);
		}
		if (isset($_GET['neuerName'])) {
			$neuerName = $_GET['neuerName'];
		}

	  // Wenn eine Branche zu aktivieren ist
		if($aktion == 4){
		    if ((Branche::setAktiv($conn, $BrancheID, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
	  // Wenn eine Branche zu löschen ist
		if($aktion == 5){
		    if ((Branche::delete($conn, $BrancheID, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
		

		// Wenn ein neuer Branche einzufügen ist
		if (isset($_POST['insert']) && $_POST['insert'] == 1) {

			$name = $_POST['bezeichnung'];
			
			if ($name == "") $err->addError("Es wurde kein Branchenname eingegeben.");

			if (!$err->moreErrors()) {
				if ($f = Branche::add($conn, $name, $err)) {
					$meldung = "Die Branche wurde erfolgreich angelegt.";
					$ok = 40;
				}
			} else {
				$ok = 41;
			}
		}

		
		//Auflistung der Branchen
		$vorhandene_Branchen = "";
		if (!($result = Branche::enumBranchen($conn))) {
			$err->addError("Die Branchen konnten von der Datenbank nicht gelesen werden. ".$conn->getLastError());
			if ($ok == 0 || $ok == 10) $ok = 21;
		} else {
			//Tabelle bilden
    if ($result->rowsCount() > 0) {
				$vorhandene_Branchen .= '<table border="0" cellspacing="0" cellpadding="0" class="StudiengangTable">';
		      	$vorhandene_Branchen .= '<tr><td width="50%" class="tableTitle">Branche</td>';
		      	$vorhandene_Branchen .= '<td width="50%" class="tableTitle">Aktion</td></tr>';
				while ($r = $result->getNextRow()) {
					$vorhandene_Branchen .= '<tr>';
					if($r[2]!=0) {
			    	$vorhandene_Branchen .= '<td valign="top" class="ausgegraut">'.$r[1].'<br><br></td>';
				  } else {
			    	$vorhandene_Branchen .= '<td valign="top">'.$r[1].'<br><br></td>';
				  }
          if($r[2]!=0)	$vorhandene_Branchen .= '<td valign="top"><a href="manage_branchen.php?BrancheID='.$r[0].'&aktion=4" onClick="return confAkt()"><span style="color:#00FF00">Aktivieren</span></a></td>';
          else					$vorhandene_Branchen .= '<td valign="top"><a href="manage_branchen.php?BrancheID='.$r[0].'&aktion=5" onClick="return confLoesch()"><span style="color:#FF0000">Löschen</span></a></td>';
          $vorhandene_Branchen .= '<td valign="top"><br><br></td>';
					$vorhandene_Branchen .= '</tr>';
				}
				$vorhandene_Branchen .= "</table>";
			} else {
				$vorhandene_Branchen = "Keine";
			}
			
		}
		
	} else {
		$err->addError($conn->getLastError());
	}

?>


<html>
<head>
<title>Branchen editieren</title>
<META HTTP-EQUIV="imagetoolbar" CONTENT="no">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/admin.css" type="text/css">

<script language="javascript">
	function confLoesch() {
		var ok = confirm("Hiermit löschen Sie die Branche. Wollen Sie mit dem L&ouml;schen fortfahren?");
		return ok;
	}
	function confAkt() {
		var ok = confirm("Hiermit aktivieren die Branche. Wollen Sie mit dem Aktivieren fortfahren?");
		return ok;
	}
</script>

</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	$Branche_edit = 1; // Damit das "Neues Fach" Button erscheint
	$menu = Menu::BRANCHEN;
	include("menu.php");
?>

<div id="delfachHead">
<h3>Branchen</h3>

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
				BRANCHE EINFÜGEN
			</div>

	<?php if ($hideinsertWindow != 1) { ?>
	<div class="inhaltHalb">
		<form name="formE" method="post" action="manage_branchen.php">
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
			VORHANDENE BRANCHEN
		
	</div>

	<div class="inhaltGanz">
		<div class="normAbstand">
	
    	<?php echo $vorhandene_Branchen; ?>
  		</div>
   </div> 
</div>
<br><br><br>
</td></tr>

<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
