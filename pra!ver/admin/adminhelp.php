<?php
	include("../libs/libraries.php");
	/// HILFE F�R ADMIN
?>


<html>
<head>
<title>Administrator - Hilfe</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">

</head>


<body leftmargin="0" topmargin="0">


<?php
	$menu = Menu::HELP;
	include("menu.php");
?>

<div>
	<h3>Hilfe</h3>
	
	<h4>�ber F�cher</h4>
	<ul>
		<li>
		<b>Allgemeines</b><br>
			Man hat Studienpl�ne und F�cher. Die Studienpl�ne haben nur eine Gruppierungsfunktion. Das heisst, dass
			ein Fach in mehreren Studienpl�nen untergebracht werden kann. Man kann F�cher auf zwei unterschiedlichen
			Weisen anlegen. Einmal �ber einen Studienplan direkt und einmal separat.
			<br><b>F�cher �ber den Studienplan anlegen</b><br>
			Ersmal m�ssen Sie einen Studienplan haben. Den k�nnen Sie unter dem Men�punkt "Studienpl�ne" anlegen.
			Danach klicken Sie auf "Editieren". Scrollen Sie herunter zur Eingabemaske "Fach einf�gen". Dort k�nnen
			Sie die Felder ausf�llen und auf "Einf�gen" dr�cken. Beachten Sie das das Fach, das Sie dadurch erstellen
			direkt dem ausgew�hlten Studienplan zugeordnet wird.
			<br><b>F�cher separat anlegen</b><br>
			Wenn Sie ein Fach beim Erzeugen nicht direkt einem Studienplan zuordnen m�hten, k�nnen Sie �nter dem Menupunkt
			"F�cher" mit der Eingabemaske "Fach einf�gen" ein neues Fach erzeugen. Dieses Fach ist aber noch keinem
			Studienplan zugeordnet. Die Zuordnung zu einem oder mehreren Studienpl�ne m�ssen Sie sp�ter selbst
			mit der Eingabemaske "Fach �bernehmen" machen, die Sie beim Editieren eines Studienplans finden.
		</li>
	
		<li>
		<b>F�cher �ndern</b><br>
			Das �ndern eines Fachs ist grunds�tzlich gef�hrlich. Wenn das Fach in mehreren Studienpl�nen vorhanden ist
			wird die �nderung des Fachs f�r alle Studienpl�ne wirksam.
			<br>Under dem Menupunk "F�cher" finden Sie die Eingabemaske "Fachkorrektur". Dort w�hlen Sie bitte das
			Fach aus, das Sie korrigieren m�chten und dr�cken Sie auf "Ausw�hlen". Damit kommen Sie zum n�chsten Schritt,
			wo die Facheigenschaften angezeigt werden und korrigiert werden k�nnen. Unter der Eingabemaske ist eine Liste
			mit den Studienpl�nen zu sehen, zu denen dieses Fach schon zugeordnet ist. Wenn das keine Liste erscheint,
			dann k�nnen Sie das Fach ohne weitere Gedanken �ber m�gliche Auswirkungen �ndern. Wenn da aber eine Liste
			erscheint, dann m�ssen sie bedenken, dass eine m�gliche Korrektur eine Auswirkung auf diese Studienpl�ne
			haben wird.
		</li>
	
		<li>
		<b>Gemeinsame Leistungen</b><br>
		Die eindeutige Bezeichnung der F�cher ist die Kennzahl.Kennziffer-Kombination. Die Kennzahl ist das eigentliche
		Fach und die Kennziffer ist die Nummer der Teilleistung. Jedes Fach, gegeben durch Kennzahl.Kennziffer,
		hat einen Abschluss (oder auch Status genannt), der entweder Fachpr�fung, Benoteter und Unbenoteter Test, oder
		eine Gemeinsame Leistung sein kann. Eine Gemeinsame Leistung ist so definiert, dass die Note davon nur als
		Teilnote f�r das komplette Fach gilt, und erst die Note von einer Pr�fung oder Test als Endnote betrachtet wird.
		Deshalb ist es nicht unbedingt erforderlich, dass der Dozent explizite Noten f�r diese Teilleistung vergibt, denn
		mit der Abgabe der Endnote, die von einer Pr�fung oder Test kommt, wird die Teilleistung freigegeben
		[!!!DAS WURDE GE�NDERT - FREIGABE ERFOLGT NICHT MEHR].
		<br>
		<b>Deswegen:</b>
		<br>
		Wenn man ein Fach mit einer Kennzahl aber mehreren Teilleistungen (Kennziffer) anlegen m�chte,
		muss man die einzelnen Teilleistungen mit dem Abschluss "Gemeinsames Fach" oder "Kein" anlegen und
		nur eine der Teilleistungen mit einem anderen Abschluss (FP, uT, bT), deren Note eine Endnote ist.
		Der Abschluss "Kein" bedeutet, das das Angebot am Ende des Semesters automatisch beendet wird.
		Sowohl bei "Gemeinsame Leistung", als auch bei "Kein" muss man keine Note explizit abgeben, um
		das Angebot damit zu beenden.
		</li>
		
		<li><b>Gleichzeitige Angebote vs. Teilleistungen</b><br>
			Wenn Sie ein Fach mit Kennzahl.Kennziffer anlegen, dann kann dieses Fach nur von einem Dozent zur
			gleichen Zeit angeboten werden. Wenn aber eine Vorlesung von zwei unterschiedlichen Dozenten
			gleichzeitig angeboten wird, ohne dass jede der beiden Vorlesungen als Teilleistung eines Fachs gilt,
			kann das mehrfache Anbieten vom Administrator erlaubt werden.
			<br>Um das zu tun, gehen Sie unter "F�cher", aus der "Fachkorrektur"-Maske, w�hlen Sie das Fach aus
			und klicken Sie auf "Ausw�hlen". Geben Sie in das Feld "Gleichzeitige Angebote" die Anzahl der
			gleichzeitigen Angeboten ein, und dr�cken Sie auf "Speichern". Die minimale Anzahl ist 1.
			<br />
			Wenn Sie z.B. 2 eintragen, wird dies dazu f�hren, dass ein Fach von zwei Dozenten angeboten werden kann.
			Dem Studen wird die Wahl �berlassen, welches der beiden Angebote er nehmen will. Wenn sich der Student f�r
			eines der beiden Angebote entscheidet, bekommt er die Endnote vom entsprechendem Dozent.
			<br />
			Bei Teilleistungen, die hier auch "Gemeinsame Leistungen" genannt werden sieht ein Bisschen anders aus.
			Dort muss der Administrator zwei (oder mehrere) unterschiedliche Vorlesungen vorsehen, die auch
			von unterschiedlichen Dozenten angeboten werden k�nnen, aber dort vergeben die beiden Dozenten
			nur Teilnoten. Da ein Fach mit Teilleistungen aus Minimum zwei Leisungen besteht, die Eine mit
			Abschluss "Gemeinsame Leistung" und die Andere mit Abschluss "FP, uT oder bT", wird nur der Dozent,
			der die Leistung mit dem Abschluss "FP, uT oder bT" anbietet, eine Endnote f�r das gesamte Fach
			erteilen.
		</li>
	</ul>
	
	<h4>�ber Studienpl�ne</h4>
	<ul>
		<li><b>Studienplan l�schen</b><br>
		Wenn ein Studienplan nicht mehr aktuell ist, kann er gel�scht werden. Das ist aber nicht empfehlenswert,
		denn alle F�cher, die diesem Studienplan zugeordnet waren, werden von dem Studienplan entfernt, aber nicht
		gel�scht. Es ist zu empfehlen, dass ein Studienplan nicht gel�scht, sondern korrigiert und angepasst wird.
		</li>
		<li><b>Wahlpflichtf�cher</b><br>
		Die Wahlpflichtf�cher, die beim Erzeugen eines Studienplans angegeben werden, sind keine verbindliche Anzahl,
		wieviele Wahlpflichtf�cher dem Studienplan zugeordnet werden.
		Diese Zahl zeigt nur den Studenten, wieviele F�cher sie absolvieren Sollten.
		<br>
		Ein Wahlpflichtf�cher ist dem Studienplan im Semester 0 zugeordnet.
		</li>
	</ul>
	
	<h4>�ber Dozenten</h4>
	<ul>
		<li><b>Dozenten</b><br>
		Dozenten richtet man unter dem Menupunkt "Dozenten" ein. Dort kann man vorhandenen Dozenten den Zugang
		abziehen oder neu erteilen.
		</li>
	</ul>
	
	<h4>�ber die Einstellungen</h4>
	<ul>
		<li><b>Wichtig</b><br>
		Jedes Semester (z.B. in der Mitte jedes Semesters) soll der Administrator die Einstellungen neu eingeben.
		Winter-/Sommersemesterende, Winter-/Sommersemesterbeginn und Anmeldefrist f�r Fachpr�fungen. Die Anmeldefrist
		muss innerhalb der Semesters liegen, denn nach Ablauf der Frist werden die An-/Abmeldungen f�r F�cher mit
		Abschluss Fachpr�fung gesperrt. 4 Tage vor dem Ablauf der Anmeldefrist werden alle Studenten dar�ber
		benachrichtigt. Diese Nachricht kann auch eine zus�tzliche Aufforderung einthalten, die Sie unter dem
		Menupunkt "Einstellungen" eingeben k�nnen.
		</li>
	</ul>
	
</div>
<br />
<br />
<br />
</body>
</html>
