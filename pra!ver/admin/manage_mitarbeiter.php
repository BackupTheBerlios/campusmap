<?php
	/* manage_mitarbeiter.php 
	* Hier werden Dozenten vom Administrator verwaltet
	*/
	
	include("../libs/libraries.php");
	
	$err = new ErrorQueue();
	$conn = new Connection();
	$meldung = ""; // Diese Meldung wird angezeigt
	
	$THIS_SITE = "manage_mitarbeiter.php";
	
	// Unterschiedliche Bereiche dürfen eine zweistellige Nummer haben, damit kennzeichnen sie 
	// bestimmte aktionen als gelungen oder nicht.
	// Beispiel. Der Bereich zum Einfügen der Mitarbeiter hat ok=30, wenn eine aktion erfolgreich
	// durchgeführt wurde. $ok = 31, wenn die Aktion fehlschlägt.
	$ok = 0;
	
	if ($conn->connect($DB_SERVER, $DB_NAME, $DB_USERNAME, $DB_PASSWORD)) {
	
		$aktion = 0;
		if (isset($_POST['aktion'])) {
			$aktion = intval($_POST['aktion']);
		}
		
		// Fügt einen Mitarbeiter ein
		if ($aktion == 1) {
			if (!isset($_POST['name']) || $_POST['name']=="") {
				$err->addError("Geben Sie einen Namen ein!");
			}
			if (!isset($_POST['email']) || $_POST['email']=="") {
				$err->addError("Geben Sie die E-Mail ein!");
			}
			
			if (!isset($_POST['username']) || $_POST['username']=="") {
				$err->addError("Geben Sie einen Benutzernamen ein!");
			}
			
			if (!isset($_POST['password']) || $_POST['password']=="") {
				$err->addError("Geben Sie ein Passwort ein!");
			}
			
			
			if (isset($_POST['komplett']) && (intval($_POST['komplett']) == Zugangsstufe::KOMPLETT)) {
				$zugangsstufe = Zugangsstufe::KOMPLETT;
			} else {
				$zugangsstufe = 0;
				$bereiche = $_POST['bereiche'];
				$count = count($bereiche);
				for ($i = 0; $i < $count; $i++) {
					$zugangsstufe = intval($zugangsstufe | intval($bereiche[$i]));
				}
			}

			if (!$err->moreErrors()) {
				if ($mitarbeiter = Mitarbeiter::createMitarbeiter($conn, $err, $_POST['username'], $_POST['password'], $_POST['name'], $_POST['email'], $zugangsstufe)) {
					$meldung = $mitarbeiter->getName()." wurde erfolgreich angelegt.";
					$ok = 30;
				} else {
					$ok = 31;
				}
			} else {
				$ok = 31;
			}
		} // if aktion neu anlegen
		
		// Löscht einen Mitarbeiter
		if ($aktion == 2) {
			if (isset($_POST['delusername']) && $_POST['delusername'] != "") {
				$username = $_POST['delusername'];
				if (Mitarbeiter::deleteMitarbeiter($conn, $err, $username)) {
					$meldung = "Der Mitarbeiter wurde gelöscht";
					$ok = 40;
				} else $ok = 41;
			} else {
				$err->addError("Es wurde kein Mitarbeiter ausgew&auml;hlt.");
				$ok = 41;
			}
		}
		
	} else {
		$err->addError($conn->getLastError());
	}


/*	
	if (($dozenumeraton = Dozent::enumDozenten($conn)) == false) {
		unset($dozenumeraton);
	} else {
		$err->addError($conn->getLastError());
	}
*/
	$allemitarbeiter = new UtilBuffer();
	Mitarbeiter::getAlleMitarbeiter($conn, $err, $allemitarbeiter);

?>

<html>
<head>
<title>Mitarbeiter verwalten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/admin.css" type="text/css">

<script language="javascript">
	function conf() {
		var ok = confirm("Sind Sie sicher, dass sie den Mitarbeiter löschen wollen?");
		return ok;
	}
	
	function showdata() {
		var arr = new Array();
		<?php
/*			if ($dozenumeraton) {
				$i = 0;
				while ($r = $dozenumeraton->getNextRow()) {
					echo "arr[$i] = \"".$r[4]."\";";
					$i++;
				}
				$dozenumeraton->rewindSet();
			}
*/			
		?>
		
		document.forms[1].elements(2).value = arr[document.fzugrif.dozentid.selectedIndex];
	}
</script>

</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	$menu = Menu::MITARBEITER;
	include("menu.php");
?>

<div>
	<h3>Mitarbeiter Verwalten</h3>
	
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
	<div class="floatleft">
		<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			NEUER MITARBEITER
		</div>
		<div class="inhaltHalb">
			<form name="f1" method="post" action="<?php echo $THIS_SITE; ?>">
			<input type="hidden" name="aktion" value="1">
			<table border="0" cellspacing="0">
				<tr>
					<td>Name: </td>
					<td><input type="text" name="name" value="<?php echo $_POST['name']; ?>" class="formfield"></td>
				</tr>
				<tr>
					<td>E-Mail: </td>
					<td><input type="text" name="email" value="<?php echo $_POST['email']; ?>" class="formfield"></td>
				</tr>
				<tr>
					<td>Benutzername: </td>
					<td><input type="text" name="username" value="<?php echo $_POST['username']; ?>" class="formfield"></td>
				</tr>
				<tr>
					<td>Passwort: </td>
					<td><input type="password" name="password" value="" class="formfield"></td>
				</tr>
				<tr>
					<td valign="top">Zugriff zu:<br /><br />
									<i>(Mehrfachauswahl <br />
									mit Strg-Taste<br />
									f&uuml;r Windows und<br />
									Apfel-Taste bei Mac)</i>
					</td>
					<td>
						<input type="checkbox" name="komplett" value="<?php echo Zugangsstufe::KOMPLETT; ?>"> Komplettzugriff
						<br />
						<select name="bereiche[]" class="formfield" size="6" multiple>
						<?php
							$bereiche = Zugangsstufe::getAlleBereiche();
							for ($i = 0; $i < $bereiche->getCount(); $i++) {
								echo '<option value="'.$bereiche->get($i).'">'.Zugangsstufe::getBereichName($bereiche->get($i)).'</option>';
							}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="right"><input type="image" border="0" src="../images/buttons/einrichten.gif"></td>
				</tr>
			</table>
			</form>
		</div>
	</div>

	<div class="floatleft">
		<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==40) echo 'ico_ok_dark.gif'; else if ($ok==41) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			MITARBEITER L&Ouml;SCHEN
		</div>
		<div class="inhaltHalb">
			<form name="delmitarbeiter" method="post" action="<?php echo $THIS_SITE; ?>">
			<input type="hidden" name="aktion" value="2">
			<table border="0" cellspacing="0">
				<tr>
					<td align="right">
						<select type="text" name="delusername" size="8" class="largeformfield">
						<?php
							for ($i = 0; $i < $allemitarbeiter->getCount(); $i++) {
								$mit = $allemitarbeiter->get($i);
								echo '<option value="'.$mit->getUsername().'">'.$mit->getName().'['.$mit->getUsername().']</option>';
							}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right"><input type="image" border="0" src="../images/buttons/loeschen.gif"></td>
				</tr>
			</table>
			</form>
		</div>
	</div>
</div>

</td></tr>

<tr><td>
<br><br><br>
</td></tr>

<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>