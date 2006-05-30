<?php
	include("../libs/libraries.php");
		
	$err = new ErrorQueue();
	$conn = new Connection();

	if ($conn->connect($DB_SERVER, $DB_NAME, $DB_USERNAME, $DB_PASSWORD)) {
		

		$gruppe = -1;
		if (isset($_GET['gruppe'])) {
			$gruppe = intval($_GET['gruppe']);
		}
	
?>


<html>
<head>
<title>Information</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<?
              switch ($gruppe) {
              	case 1:
              		echo '<link rel="stylesheet" href="../styles/studieninteressierte.css" type="text/css">';
              		break;
              	case 2:
              		echo '<link rel="stylesheet" href="../styles/presse_besucher.css" type="text/css">';
              		break;
              	case 3:
              		echo '<link rel="stylesheet" href="../styles/wirtschaft.css" type="text/css">';
              		break;
              	default:
              		echo '<link rel="stylesheet" href="../styles/start.css" type="text/css">';
              		break;
							}
              	
?>

</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<?php
	// Menue Bereich
	$menue = Menu::DATENBANK;
	include("menu.php");
?>

<div class="hintergrundstreifen">
	

	
<?php
	
	if ($err->moreErrors()) {
		echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
	}

?>

		<div class="titelGanzStreifen">
	</div>
	<div class="titelGanz">
		<img src="../images/ico_inhalt.gif" border="0" />
		INFORMATION ZUM BERUFSPRAKTISCHEN SEMESTER
	</div>

	<div class="inhaltGanz">
					<span class="dick">Aufgabe und Inhalt</span><br>
      		Das berufspraktische Studiensemester soll die Studierenden in das Berufsfeld ihres Studiengangs einf&uuml;hren. Die Praktikantin bzw. der Praktikant sollen entsprechende T&auml;tigkeiten und ihre fachlichen Anforderungen kennen lernen. Sie sollen einen &Uuml;berblick in die für ihre k&uuml;nftige T&auml;tigkeit wichtigen technischen Gegebenheiten gewinnen und betriebliche Zusammenh&auml;nge, wie z. B. Arbeitsablauf, Ger&auml;teeinsatz, Organisation, Zusammenarbeit mit anderen Abteilungen usw. kennen lernen.<br>
      		
      		<?
              switch ($gruppe) {
              	case 1:
              		echo "<br>Viele Studieninteressierte haben bei Ihrer Studienwahl oft Schwierigkeiten, sich ein Bild von ihrem späteren Arbeitsplatz zu machen. Mit diesem Tool möchten wir dieses Bild anhand von Beispielen unserer Studierenden bereichern. Die Datenbank zeigt auf, in welchen Berufszweigen die Studierenden der einzelnen Studiengänge nach dem Studium landen.<br>";
              		break;
              	case 2:
              		echo "<br>Die hier vorzufindene Datenbank enthält eine umfangreiche Liste von Unternehmen, bei denen die Studierenden der FH-Lübeck ihr Berufspraktisches Semester absolvieren und oft auch nach dem Studium ihr Arbeitsleben beginnen.<br>";
              		break;
              	case 3:
              		echo "<br>Die hier vorzufindene Datenbank enthält eine umfangreiche Liste von Unternehmen, bei denen die Studierenden der FH-Lübeck ihr Berufspraktisches Semester absolvieren und oft auch nach dem Studium ihr Arbeitsleben beginnen.<br>Können Sie sich vorstellen, Praktikanten oder Absolventen aus den Studiengängen der FH-Lübeck zu beschäftigen? Wir freuen uns auf den Kontakt mit Ihnen. Vielleicht (und wahrscheinlich) finden Sie so Ihren zukünftigen Mitarbeiter. Vielleicht entsteht so der Kontakt zu unseren Abolventen.<br>Probieren Sie es aus!<br>";
              		break;
              	default:
              		break;
							}	
          ?>
          <br>
					Alle weiteren Informationen erhalten Sie unter <a href="http://www.fh-luebeck.de">http://www.fh-luebeck.de</a><br>

	</div><br><br><br>
	
		<div class="titelGanz">
		<img src="../images/ico_inhalt.gif" border="0">
		INFORMATION ZU PRA|VER
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

</div>
<br><br><br>
	
<?
} else {
      		$err->addError($conn->getLastError());
}
?>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>
</html>
