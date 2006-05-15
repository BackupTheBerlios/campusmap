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
		$StaatID = 0;
		$neuerName = "kein Name";
		
		if (isset($_GET['aktion'])) {
			$aktion = intval($_GET['aktion']);
		}
		if (isset($_GET['StaatID'])) {
			$StaatID = intval($_GET['StaatID']);
		}
		if (isset($_GET['neuerName'])) {
			$neuerName = $_GET['neuerName'];
		}

	  // Wenn ein Staat zu aktivieren ist
		if($aktion == 4){
		    if ((Staat::setAktiv($conn, $StaatID, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
	  // Wenn ein Staat zu löschen ist
		if($aktion == 5){
		    if ((Staat::delete($conn, $StaatID, $err)) == false) {
		      $err->addError($conn->getLastError());
	      }
	  }
		

		// Wenn ein neuer Staat einzufügen ist
		if (isset($_POST['insert']) && $_POST['insert'] == 1) {

			$name = $_POST['bezeichnung'];
			$kontinentID = intval($_POST['kontinentid']);
			
			if ($name == "") $err->addError("Es wurde kein Staatsname eingegeben.");

			if (!$err->moreErrors()) {
				if ($f = Staat::add($conn, $name, $kontinentID, $err)) {
					$meldung = "Der Staat wurde erfolgreich angelegt.";
					$ok = 40;
				}
			} else {
				$ok = 41;
			}
		}

		
		//Auflistung der Staaten
		$vorhandene_staaten = "";
		if (!($result = Staat::enumStaaten($conn))) {
			$err->addError("Die Staaten konnten von der Datenbank nicht gelesen werden. ".$conn->getLastError());
			if ($ok == 0 || $ok == 10) $ok = 21;
		} else {
			//Tabelle bilden
    if ($result->rowsCount() > 0) {
				$vorhandene_staaten .= '<table border="0" cellspacing="0" cellpadding="0" class="StudiengangTable">';
		      	$vorhandene_staaten .= '<tr><td width="40%" class="tableTitle">Staat</td>';
		      	$vorhandene_staaten .= '<td width="20%" class="tableTitle">Kontinent</td>';
		      	$vorhandene_staaten .= '<td width="40%" class="tableTitle">Aktion</td></tr>';
				while ($r = $result->getNextRow()) {
					$vorhandene_staaten .= '<tr>';
					if($r[2]!=0) {
			    	$vorhandene_staaten .= '<td valign="top" class="ausgegraut">'.$r[1].'<br><br></td>';
				    $vorhandene_staaten .= '<td valign="top" class="ausgegraut">'.$r[4].'<br><br></td>';
				  } else {
			    	$vorhandene_staaten .= '<td valign="top">'.$r[1].'<br><br></td>';
				    $vorhandene_staaten .= '<td valign="top">'.$r[4].'<br><br></td>';
				  }
          if($r[2]!=0)	$vorhandene_staaten .= '<td valign="top"><a href="manage_staaten.php?StaatID='.$r[0].'&aktion=4" onClick="return confAkt()"><span style="color:#00FF00">Aktivieren</span></a></td>';
          else					$vorhandene_staaten .= '<td valign="top" ><a href="manage_staaten.php?StaatID='.$r[0].'&aktion=5" onClick="return confLoesch()"><span >Löschen</span></a></td>';
          $vorhandene_staaten .= '<td valign="top"><br><br></td>';
					$vorhandene_staaten .= '</tr>';
				}
				$vorhandene_staaten .= "</table>";
			} else {
				$vorhandene_staaten = "Keine";
			}
			
		}
		if (($kontinentenumeration = Staat::enumKontinente($conn, $err))) {
		} else {
			$err->addError($conn->getLastError());
		}
		
	} else {
		$err->addError($conn->getLastError());
	}

?>


<html>
<head>
<title>Staaten editieren</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/admin.css" type="text/css">

<script language="javascript">
	function confLoesch() {
		var ok = confirm("Hiermit löschen Sie den Staat. Wollen Sie mit dem L&ouml;schen fortfahren?");
		return ok;
	}
	function confAkt() {
		var ok = confirm("Hiermit aktivieren den Staat. Wollen Sie mit dem Aktivieren fortfahren?");
		return ok;
	}
</script>

</head>

<body leftmargin="0" topmargin="0">

<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top">
<?php include("../libs/kopf.php"); ?>

<?php
	$Staat_edit = 1; // Damit das "Neues Fach" Button erscheint
	$menu = Menu::STAATEN;
	include("menu.php");
?>

<div id="delfachHead">
<h3>Staaten verwalten</h3>

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
				STAAT ANLEGEN
			</div>

	<?php if ($hideinsertWindow != 1) { ?>
			
			<div class="inhaltHalb">
				<form name="formE" method="post" action="manage_staaten.php">
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
					    <td>Kontinent:</td>
					    <td>
					    	<select name="kontinentid" size="1" class="formfield" onClick="">
					    		<?php
					    			if ($kontinentenumeration) {
						    			while ($r = $kontinentenumeration->getNextRow()) {
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
			VORHANDENE STAATEN
		
	</div>

	<div class="inhaltGanz">
		<div class="normAbstand">
	
    		<?php echo $vorhandene_staaten; ?>
 		</div>
	</div>

</div>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
