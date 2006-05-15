<?php
	
	include("../libs/libraries.php");


	$ok = 0;
	$meldung = "";
	$err = new ErrorQueue();
	$conn = new Connection();
	

	if ($conn->connect($DB_SERVER, $DB_NAME, $DB_USERNAME, $DB_PASSWORD)) {
		if (isset($_POST['update'])) {
			$update = intval($_POST['update']);
		} else {
			$update = 0;
		}
		
		$einstellungen = new Einstellungen($conn);
		
		//$err->addError($einstellungen->getLastError());

		if ($update == 1) {
		
			if ($einstellungen->writeEinstellungen( $_POST['adminmail'])) {
				$einstellungen->loadEinstellungen();
				$meldung = "Die Einstellungen wurden erfolgreich &uuml;bernommen.";
			} else {
				//echo $einstellungen->getLastError();
				$err->addError($einstellungen->getLastError());
			}
		}

		$adminmail = $einstellungen->getAdminsEmail();

		
	} else {
		$err->addError($conn->getLastError());
	}
	
?>


<html>
<head>
<title>Einstellungen</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/admin.css" type="text/css">
</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	$menu = Menu::EINSTELLEN;
	include("menu.php");
?>

<div id="settingsHead">
	<h3>Einstellungen</h3>
	
	<?php
		if ($err->moreErrors()) {
			echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
		}
		
		if ($meldung != "") {
			echo '<p class="meldung">'.$meldung.'</p>';
		}
	?>
	
</div>

<div class="hintergrundstreifen">

		
			<div class="titelHalb"><?php if ($hideinsertWindow == 1) echo 'hidden'; ?>
						
				<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
				AKTUELLE EINSTELLUNGEN
			</div>
	
		<div class="inhaltHalb">
			<form name="changesettings" method="post" action="einstellen.php">
				<table border="0" cellpadding="2" cellspacing="0">
				  
				  <tr> 
				    <td>Administrator E-Mail:</td>
				    <input type="hidden" name="update" value="1">
				    <td>
						<input type="text" name="adminmail" value="<?php echo $adminmail?>" class="formfield">
				    </td>
				  </tr>
	
				  <tr>
				    <td><a href="einstellen.php"><img border="0" src="../images/buttons/verwerfen.gif"></a></td>
				    <td align="right"><input type="image" border="0" src="../images/buttons/speichern.gif"></td>
				  </tr>
				  
				</table>
			</form>
		</div>
</div>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
