<?php
	/* manage_dozenten.php 
	* Hier werden Dozenten vom Administrator verwaltet
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
		if (isset($_POST['aktion'])) {
			$aktion = intval($_POST['aktion']);
		}
		
		// Fügt einen Dozenten ein
		if ($aktion == 1) {
			if (!isset($_POST['name']) || $_POST['name']=="") {
				$err->addError("Geben Sie einen Namen ein!");
			}
			if (!isset($_POST['email']) || $_POST['email']=="") {
				$err->addError("Geben Sie die E-Mail ein!");
			}
			
			if (!isset($_POST['tel'])) {
				$err->addError("Die Telefonnummer wurde nicht gesetzt.");
			}
			
			if (!isset($_POST['username']) || $_POST['username']=="") {
				$err->addError("Geben Sie einen Benutzernamen ein!");
			}
			
			if (!isset($_POST['password']) || $_POST['password']=="") {
				$err->addError("Geben Sie ein Passwort ein!");
			}
			
			if (!$err->moreErrors()) {
				if (Dozent::neuerAnmelden($conn, $err, $_POST['name'], $_POST['email'], $_POST['tel'], $_POST['username'], $_POST['password'])) {
					$meldung = "Die Anmeldung war erfolgreich.";
					$ok = 30;
				} else {
					$ok = 31;
				}
			} else {
				$ok = 31;
			}
		} // if aktion
		
		// Löscht einen Dozenten
		if ($aktion == 2) {
			if (isset($_POST['dozentid']) && $_POST['dozentid'] != "") {
				$dozentid = intval($_POST['dozentid']);
				if (Dozent::abmeldenDozent($conn, $err, $dozentid)) {
					$ok = 20;
					$meldung = "Die angebotenen F&auml;cher des Dozents wurden freigegeben und seine Zugriffsdaten gel&ouml;scht.";
				} else $ok = 21;
			} else {
				$err->addError("Kein Dozent wurde ausgew&auml;hlt.");
				$ok = 21;
			}
		}
		
		// Löscht einen Dozent
		if ($aktion == 3) {
			if (!isset($_POST['dozentid']) || $_POST['dozentid'] == "") {
				$err->addError("Kein Dozent wurde ausgew&auml;hlt.");
			}
			$dozentid = intval($_POST['dozentid']);
			
			if (!isset($_POST['username']) || $_POST['username'] == "") {
				$err->addError("Geben Sie bitte einen Benutzernamen ein!");
			}

			if (!isset($_POST['password']) || $_POST['password'] == "") {
				$err->addError("Geben Sie bitte ein Passwort ein!");
			}

			if (!$err->moreErrors()) {
				if (Dozent::zugriffErteilen($conn, $err, $dozentid, $_POST['username'], $_POST['password'])) {
					$ok = 40;
					$meldung = "Die neuen Benutzername und Passwort wurden &uuml;bernommen.";
				} else $ok = 41;
			} else {
				$ok = 41;
			} // if
		}
		
	} else {
		$err->addError($conn->getLastError());
	}
	
	
	if (($dozenumeraton = Dozent::enumDozenten($conn)) == false) {
		unset($dozenumeraton);
	} else {
		$err->addError($conn->getLastError());
	}
?>

<html>
<head>
<title>Studienplan editieren</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/admin.css" type="text/css">

<script language="javascript">
	function conf() {
		var ok = confirm("Hiermit werden die Zugriffsdaten und alle von diesem Dozent angebotenen Fächer gelöscht. Der Dozent, sowie alle Noten, die er abgegeben hat, bleiben weiterhin in der Datenbank vorhanden. Sie können diesem Dozent später wieder Zugriffsrechte ertelen. Wollen Sie mit dem Löschen fortfahren?");
		return ok;
	}
	
	function showusername() {
		var arr = new Array();
		<?php
			if ($dozenumeraton) {
				$i = 0;
				while ($r = $dozenumeraton->getNextRow()) {
					echo "arr[$i] = \"".$r[4]."\";";
					$i++;
				}
				$dozenumeraton->rewindSet();
			}
		?>
		
		document.forms[1].elements(2).value = arr[document.fzugrif.dozentid.selectedIndex];
	}
</script>

</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	$menu = Menu::DOZENTEN;
	include("menu.php");
?>

<div>
	<h3>Dozenten Verwalten</h3>
	
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

		<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			NEUER DOZENT
		</div>
		<div class="inhaltHalb">
			<form name="f1" method="post" action="manage_dozenten.php">
			<input type="hidden" name="aktion" value="1">
			<table border="0" cellspacing="0">
				<tr>
					<td>Name: </td>
					<td><input type="text" name="name" value="" class="formfield"></td>
				</tr>
				<tr>
					<td>E-Mail: </td>
					<td><input type="text" name="email" value="" class="formfield"></td>
				</tr>
				<tr>
					<td>Tel.: </td>
					<td><input type="text" name="tel" value="" class="formfield"></td>
				</tr>
				<tr>
					<td>Benutzername: </td>
					<td><input type="text" name="username" value="" class="formfield"></td>
				</tr>
				<tr>
					<td>Passwort: </td>
					<td><input type="password" name="password" value="" class="formfield"></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="right"><input type="image" border="0" src="../images/buttons/einrichten.gif"></td>
				</tr>
			</table>
			</form>
		</div>
<br><br><br>
		<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==40) echo 'ico_ok_dark.gif'; else if ($ok==41) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			NEUE ZUGRIFFSDATEN
		</div>
		<div class="inhaltHalb">
	
			<div class="borderedDiv">
				<form name="fzugrif" method="post" action="manage_dozenten.php">
				<input type="hidden" name="aktion" value="3">
				<table border="0" cellspacing="0">
					<tr>
						<td colspan="2">
						<?php
							if ($dozenumeraton) {
								echo '<select name="dozentid" size="4" class="largeformfield" onClick="return showusername(this)">';
								while ($r = $dozenumeraton->getNextRow()) {
									$tt = $r[4];
									if ($tt == "") $tt = 'KEIN ZUGRIFF!';
									echo '<option value="'.$r[0].'">'.$r[1].' ['.$tt.']</option>';
								}
								echo '</select>';
							}
						?>
						</td>
					</tr>
					<tr>
						<td>Benutzername: </td>
						<td align="right"><input type="text" name="username" value="" class="formfield"></td>
					</tr>
					<tr>
						<td>Passwort: </td>
						<td align="right"><input type="password" name="password" value="" class="formfield"></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="right"><input type="image" border="0" src="../images/buttons/speichern.gif"></td>
					</tr>
				</table>
				</form>
			</div>
		</div>
	
	
<br><br><br>


	

	<div class="titelGanz">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			VORHANDENE BRANCHEN
		
	</div>

	<div class="inhaltGanz">
		<form name="f2" method="post" action="manage_dozenten.php">
		<input type="hidden" name="aktion" value="2">
		<table border="0" cellspacing="0" class="test1">
			<tr>
				<td>
				<?php
					if ($res = Dozent::enumDozenten($conn)) {
						echo '<select name="dozentid" size="13" class="supextendedformfield">';
						while ($r = $res->getNextRow()) {
							$tt = $r[4];
							if ($tt == "") $tt = 'DOZENT HAT KEINEN ZUGRIFF!';
							echo '<option value="'.$r[0].'">'.$r[1].', '.$r[2].', '.$r[3].' - ['.$tt.']</option>';
						}
						echo '</select>';
					} else {
						echo "Ein Fehler ist aufgetreten.".$conn->getLastError();
					}
				?>
				</td>
			</tr>
			<tr>
				<td align="right"><input type="image" border="0" src="../images/buttons/loeschen.gif" onClick="return conf()"></td>
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