<?php


	include_once("include_student.php");
	
	$ok = 0;
	$meldung = "";
	if ($student->isLoggedIn()) {
		$matrnr = $student->getMatrNr();
		$update = intval($_POST['update']);
		$updatepass = intval($_POST['updatepass']);
		$showit = 1;
		
		if ($update == 5) {
			$name = addslashes($_POST['name']);
			$vorname = addslashes($_POST['vorname']);
			$email = addslashes($_POST['email']);
			//$text = urlencode($_POST['text']);
			$semester = intval($_POST['semester']);
			$mystudiengangid = intval($_POST['mystudiengangid']);
			if ($semester < 1) $semester = 1;
			
			if ($name == "" || !isset($_POST['name'])) $err->addError("Sie haben Ihren Namen nicht eingegeben.");
			if ($vorname == "" || !isset($_POST['vorname'])) $err->addError("Sie haben Ihren Vornamen nicht eingegeben.");
			if ($email == "" || !isset($_POST['email'])) $err->addError("Sie haben Ihre E-Mail Adresse nicht eingegeben.");
			if ($mystudiengangid == "" || !isset($_POST['mystudiengangid'])) $err->addError("Sie haben keinen Studiengang ausgew&auml;hlt.");
			
			if (!$err->moreErrors()) {
				$q = new DBQuery("UPDATE student SET Name='$name', Vorname='$vorname', Email='$email', Text='$text', Semesterzahl=$semester, StudiengangID=$mystudiengangid WHERE MatrNr=$matrnr");
				if ($conn->executeQuery($q)) {
					$showit = 0; //Nicht die alten daten anzeigen, sondern die neu eingegebenen
					$ok = 1;
					$meldung = "Die &Auml;nderungen wurden gespeichert.";
				} else {
					$err->addError("Fehler. Die Daten konten nicht gespeichert werden.");
					$ok = 2;
				}
			} else {
				$ok = 2;
			}
			$text =  stripslashes(urldecode($text));
		}
		
		$studiengaenge = Studiengang::enumAktiveStudiengaenge($conn, $err);
		
		if ($showit == 1) {
			$name = $student->getName();
			$vorname = $student->getVorname();
			$email = $student->getEmail();
			$text =  stripslashes($student->getText());
			$mystudiengangid = -1;
			if ($student->hatStudiengang()) {
				$mystudiengangid = $student->getStudiengang()->getID();
			}
			$semester = $student->getSemester();
		}
		
		// Wenn ein neues Passwort sein muss
		if (8 == $updatepass) {
			$studi = new Student($conn);
			$inited = $studi->init($student->getMatrNr(), md5($_POST['oldpass']));
			if ($inited) {
				$newpass = $_POST['newpass'];
				if (strcmp($_POST['newpassagain'], $newpass) == 0) {
					if (PassGenerator::machesPasswordCriteria($newpass)) {
						$np = md5($newpass);
						$q = new DBQuery("UPDATE student Set Pass='$np' WHERE MatrNr=".$student->getMatrNr());
						if ($conn->executeQuery($q)) {
							invokeLogin("Ihr Passwort wurde gespeichert.<br/>Bitte melden Sie sich jetzt mit Ihrem neuen Passwort an.");
						} else {
							$err->addError("Ein Fehler ist aufgetreten. Das Passwort konnte nicht ge&auml;ndert werden.");
						}
					} else {
						$err->addError("Das Passwort muss mindestens 6 Zeichen lang sein und Sonderzeichen oder Zahlen enthalten.");
					}
				} else {
					$err->addError("Das neue Passwort stimmt mit dem wiederholten Passwort nicht &uuml;berein.");
				}
			} else {
				$err->addError("Sie haben ein falsches Passwort eingegeben.");
			}
		}
		
	} // if LOGGED IN

?>


<html>
<head>
<title>Student</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/studi.css" type="text/css">
</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	// Menue Bereich
	$menue = Menu::EINSTELLEN;
	include("menu.php");
?>

<div>
<h3>
<?php

	echo "<h3>Einstellungen f&uuml;r ".$student->getName()." (".$student->getMatrNr().")</h3>";

	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}
	
	if ($meldung != "") {
		echo '<p class="meldung">'.$meldung.'</p>';
	}
	
	// Txt wird normallerweise von anderen Seiten gesetzt
	// z.B. bei studi.php; Die Einstellungsseite wird aufgerufen, wenn kein Plan ausgewählt ist.
	if (isset($_SESSION['txt'])) {
		echo '<p class="meldung">'.$_SESSION['txt'].'</p>';
		unset($_SESSION['txt']);
	}
		
?>
</h3>
</div>

<div class="hintergrundstreifen">
		<div class="titelGanzStreifen">
		</div>
		<div class="titelGanz">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			AKTUELLE EINSTELLUNGEN
		</div>
		<div class="inhaltGanz">
			<form name="form1" method="post" action="einstellen.php">
			<input type="hidden" name="update" value="5">
				<table border="0" cellspacing="0">
					<tr>
						<td width="200px" class="dick">Matrikelnummer:</td>
						<td><?php echo $matrnr; ?></td>
					</tr>
					<tr>
						<td class="dick">Name:</td>
						<td><input type="text" class="formfield" name="name" value="<?php echo $name; ?>"></td>
					</tr>
					<tr>
						<td class="dick">Vorname:</td>
						<td><input type="text" class="formfield" name="vorname" value="<?php echo $vorname; ?>"></td>
					</tr>
					<tr>
						<td class="dick">E-Mail:</td>
						<td><input type="text" class="formfield" name="email" value="<?php echo $email; ?>"></td>
					</tr>
					<tr>
						<td class="dick">Semester:</td>
						<td><input type="text" class="formfield" name="semester" value="<?php echo $semester; ?>"></td>
					</tr>
					
					<tr>
						<td class="dick">Studiengang:</td>
						<td>
							<ul>
							<?php
								if ($studiengaenge) {
								  while ($r = $studiengaenge->getNextRow()) {
									$checked = "";
									if ($r[0] == $mystudiengangid) $checked = "checked";
		
									echo '<li>';
									echo '<input type="radio" name="mystudiengangid" value="'.$r[0].'" '.$checked.'>';
									echo $r[1]."</input>";
									echo '</li>';
								  } // while
								} // if
							?>
							</ul>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td align="right"><input type="image" border="0" src="../images/buttons/speichern.gif"></td>
					</tr>
				</table>
			</form>
		</div>
		<br><br><br>
		<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==40) echo 'ico_ok_dark.gif'; else if ($ok==41) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			PASSWORT ÄNDERN
		</div>
		<div class="inhaltHalb">
			Das Passwort muss minimum 6 Zeichen lang sein und mindestens eine Zahl oder ein Sonderzeichen enthalten.<br />
			Nach der Passwort&auml;nderung werden Sie aufgefordert sich mit dem neuen Passwort anzumelden.
			<br><br>
			<form name="formpass" method="post" action="einstellen.php">
			<input type="hidden" name="updatepass" value="8">
				<table border="0" cellspacing="0" class="">
					<tr>
						<td class="dick">Altes Passwort:</td>
						<td><input type="password" class="formfield" name="oldpass" value=""></td>
					</tr>
					<tr>
						<td class="dick">Neues Passwort:</td>
						<td><input type="password" class="formfield" name="newpass" value=""></td>
					</tr>
					<tr>
						<td class="dick">Neues Passwort wiederholen:</td>
						<td><input type="password" class="formfield" name="newpassagain" value=""></td>
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
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
