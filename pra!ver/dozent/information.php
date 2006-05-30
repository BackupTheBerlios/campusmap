<?php


	include_once("include_dozent.php");
	
	$ok = 0;
	$meldung = "";
	if ($dozent->isLoggedIn()) {
		$username = $dozent->getUsername();
		$dozentenid = $dozent->getID();
		
		
		$studiengaenge = Studiengang::enumAktiveStudiengaenge($conn, $err);
		
		$sachbearbeiterStgang = Studiengang::getStudiengaengeVomSachbearbeiter($conn, $dozentenid);
		

		

		
	} // if LOGGED IN

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
	$menue = Menu::EINSTELLEN;
	include("menu.php");
?>

<div>
<?php

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
</div>

<div class="hintergrundstreifen">
		<div class="titelGanzStreifen">
		</div>
		<div class="titelGanz">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			<?php echo "EINSTELLUNGEN F&Uuml;R ".$dozent->getName()." (".$dozent->getUsername().")"; ?>
		</div>

		<div class="inhaltGanz">
			<form name="form1" method="post" action="einstellen.php">
			<input type="hidden" name="update" value="5">
				<table border="0" cellspacing="0">
					<tr>
						<td width="250px" class="dick">Benutzername:</td>
						<td><?php echo $username; ?></td>
					</tr>
					<tr>
						<td class="dick">Sachbearbeiter für folgende Studiengänge:</td>
						<td>
							
							<?php
								if ($sachbearbeiterStgang) {
								  while ($r = $sachbearbeiterStgang->getNextRow()) {
									
							
		
									echo ($r[0]);
									?><br>
									<?
									
								  } // while
								} // if
							?>
							
						</td>
					</tr>
					
					<tr>
						<td>&nbsp;</td>
						<td ></td>
					</tr>
				</table>
			</form>
		</div>
</div>
<br><br><br>
<div class="hintergrundstreifen">
	
	<div class="titelGanz">
		<img src="../images/ico_inhalt.gif" border="0" />
		INFORMATION ZU PRA|VER
	</div>
		<div class="titelGanzStreifen">
	</div>
	
	<div class="inhaltGanz">
		<div class="normAbstand">
      		PRA|VER entstand als Semesterprojekt im Studiengang Informationstechnologie und Gestaltung international<br>an der FH-L&uuml;beck.<br>
      		Treten Fragen während der Benutzung auf, bitten wir Sie, mit den Entwicklern dieses Tools in Verbindung zu treten:<br>
      		<br>
      	Entwickler: David Hübner und Christian Burghoff | <a href='mailto:praver@millbridge.de'>praver@millbridge.de</a>
		</div>
	</div>
	<br><br>
	
	<div class="titelGanz">
		<img src="../images/ico_inhalt.gif" border="0" />
		INFORMATION ZUM BERUFSPRAKTISCHEN SEMESTER
	</div>

	<div class="inhaltGanz">
		<div class="normAbstand">
					<span class="dick">Aufgabe und Inhalt</span><br>
      		Das berufspraktische Studiensemester soll die Studierenden in das Berufsfeld ihres Studiengangs einf&uuml;hren. Die Praktikantin bzw. der Praktikant sollen entsprechende T&auml;tigkeiten und ihre fachlichen Anforderungen kennen lernen. Sie sollen einen &Uuml;berblick in die für ihre k&uuml;nftige T&auml;tigkeit wichtigen technischen Gegebenheiten gewinnen und betriebliche Zusammenh&auml;nge, wie z. B. Arbeitsablauf, Ger&auml;teeinsatz, Organisation, Zusammenarbeit mit anderen Abteilungen usw. kennen lernen. Die Praktikantin bzw. der Praktikant soll voll in den Arbeitsablauf eingegliedert werden und keine Sonderstellung einnehmen.<br>
      		Der vorgesehene Ausbildungsplatz muss vorher beim Fachbereich angemeldet werden!!
      		<br><br>
      		<span class="dick">Dauer und Zeitpunkt</span><br>
      		Das berufspraktische Studiensemester dauert in Diplomstudiengängen durchschnittlich 24 Wochen, d. h. 120 Arbeitstage. Fehlzeiten (z. B. durch Urlaub, Krankheit oder gesetzliche Feiertage) dürfen nicht dazu führen, dass die 24 Wochen unterschritten werden. Das berufspraktische Studiensemester soll ohne Unterbrechung in einem Betrieb, möglichst an einem Arbeitsplatz, abgeleistet werden. Die genaue Zeitdauer ist im Sekretäriat zu erfragen.
      		<br><br>
      		<span class="dick">Tätigkeitsbericht</span><br>
Über das berufspraktische Studiensemester ist ein Bericht anzufertigen. Der Bericht soll exemplarisch zeigen, mit welchen Aufgaben sich die Praktikantin bzw. der Praktikant beschäftigt hat. Es muss aus dem Bericht ersichtlich sein, dass sie bzw. er sich mit ihren bzw. seinen Aufgaben theoretisch und praktisch auseinandergesetzt hat, welche Ziele das Unternehmen verfolgt (Produkte, Dienstleistungen usw.) und an welcher Stelle die Praktikantin bzw. der Praktikant im Unternehmen eingebunden war. Der Bericht dient auch der Vorbereitung des Vortrages im berufspraktischen Seminar.
<br><br>
<span class="dick">Nachweis und Anerkennung</span><br>
Das berufspraktische Studiensemester wird für das Studium nur dann anerkannt, wenn ein Bericht vorgelegt wird, in welchem die wesentlichen Tätigkeiten beschrieben werden. Zusätzlich bedarf es der Vorlage eines Tätigkeitsnachweises des Unternehmens. Es wird empfohlen, darüber hinaus ein qualifiziertes Arbeitszeugnis einzufordern. Der Umfang des Berichtes sollte 15 Seiten nicht unterschreiten.
<br><br>
<span class="dick">Seminarvortrag</span><br>
Der Seminarvortrag soll einen Einblick geben in die Firma, die Abteilung und die Arbeitsbedingungen.
Außerdem soll exemplarisch ein inhaltliches Thema („Highlight“) aus dem Praktikum vorgestellt werden.
<br><br>
<span class="dick">Ausbildungsförderung, Krankenversicherung, Studentenwerksbeitrag</span><br>
Ausbildungsförderung, Krankenversicherung und Studentenwerksbeitrag sind wie in den anderen Studiensemestern geregelt.
		</div>
	</div>


</div>
<br><br><br>



<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>