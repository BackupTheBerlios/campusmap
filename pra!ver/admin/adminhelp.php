<?php
	include("../libs/libraries.php");
	/// HILFE FÜR ADMIN
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
	
	<h4>Über Fächer</h4>
	<ul>
		<li>
		<b>Allgemeines</b><br>
			Man hat Studienpläne und Fächer. Die Studienpläne haben nur eine Gruppierungsfunktion. Das heisst, dass
			ein Fach in mehreren Studienplänen untergebracht werden kann. Man kann Fächer auf zwei unterschiedlichen
			Weisen anlegen. Einmal über einen Studienplan direkt und einmal separat.
			<br><b>Fächer über den Studienplan anlegen</b><br>
			Ersmal müssen Sie einen Studienplan haben. Den können Sie unter dem Menüpunkt "Studienpläne" anlegen.
			Danach klicken Sie auf "Editieren". Scrollen Sie herunter zur Eingabemaske "Fach einfügen". Dort können
			Sie die Felder ausfüllen und auf "Einfügen" drücken. Beachten Sie das das Fach, das Sie dadurch erstellen
			direkt dem ausgewählten Studienplan zugeordnet wird.
			<br><b>Fächer separat anlegen</b><br>
			Wenn Sie ein Fach beim Erzeugen nicht direkt einem Studienplan zuordnen möhten, können Sie ünter dem Menupunkt
			"Fächer" mit der Eingabemaske "Fach einfügen" ein neues Fach erzeugen. Dieses Fach ist aber noch keinem
			Studienplan zugeordnet. Die Zuordnung zu einem oder mehreren Studienpläne müssen Sie später selbst
			mit der Eingabemaske "Fach übernehmen" machen, die Sie beim Editieren eines Studienplans finden.
		</li>
	
		<li>
		<b>Fächer ändern</b><br>
			Das Ändern eines Fachs ist grundsätzlich gefährlich. Wenn das Fach in mehreren Studienplänen vorhanden ist
			wird die Änderung des Fachs für alle Studienpläne wirksam.
			<br>Under dem Menupunk "Fächer" finden Sie die Eingabemaske "Fachkorrektur". Dort wählen Sie bitte das
			Fach aus, das Sie korrigieren möchten und drücken Sie auf "Auswählen". Damit kommen Sie zum nächsten Schritt,
			wo die Facheigenschaften angezeigt werden und korrigiert werden können. Unter der Eingabemaske ist eine Liste
			mit den Studienplänen zu sehen, zu denen dieses Fach schon zugeordnet ist. Wenn das keine Liste erscheint,
			dann können Sie das Fach ohne weitere Gedanken über mögliche Auswirkungen ändern. Wenn da aber eine Liste
			erscheint, dann müssen sie bedenken, dass eine mögliche Korrektur eine Auswirkung auf diese Studienpläne
			haben wird.
		</li>
	
		<li>
		<b>Gemeinsame Leistungen</b><br>
		Die eindeutige Bezeichnung der Fächer ist die Kennzahl.Kennziffer-Kombination. Die Kennzahl ist das eigentliche
		Fach und die Kennziffer ist die Nummer der Teilleistung. Jedes Fach, gegeben durch Kennzahl.Kennziffer,
		hat einen Abschluss (oder auch Status genannt), der entweder Fachprüfung, Benoteter und Unbenoteter Test, oder
		eine Gemeinsame Leistung sein kann. Eine Gemeinsame Leistung ist so definiert, dass die Note davon nur als
		Teilnote für das komplette Fach gilt, und erst die Note von einer Prüfung oder Test als Endnote betrachtet wird.
		Deshalb ist es nicht unbedingt erforderlich, dass der Dozent explizite Noten für diese Teilleistung vergibt, denn
		mit der Abgabe der Endnote, die von einer Prüfung oder Test kommt, wird die Teilleistung freigegeben
		[!!!DAS WURDE GEÄNDERT - FREIGABE ERFOLGT NICHT MEHR].
		<br>
		<b>Deswegen:</b>
		<br>
		Wenn man ein Fach mit einer Kennzahl aber mehreren Teilleistungen (Kennziffer) anlegen möchte,
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
			<br>Um das zu tun, gehen Sie unter "Fächer", aus der "Fachkorrektur"-Maske, wählen Sie das Fach aus
			und klicken Sie auf "Auswählen". Geben Sie in das Feld "Gleichzeitige Angebote" die Anzahl der
			gleichzeitigen Angeboten ein, und drücken Sie auf "Speichern". Die minimale Anzahl ist 1.
			<br />
			Wenn Sie z.B. 2 eintragen, wird dies dazu führen, dass ein Fach von zwei Dozenten angeboten werden kann.
			Dem Studen wird die Wahl überlassen, welches der beiden Angebote er nehmen will. Wenn sich der Student für
			eines der beiden Angebote entscheidet, bekommt er die Endnote vom entsprechendem Dozent.
			<br />
			Bei Teilleistungen, die hier auch "Gemeinsame Leistungen" genannt werden sieht ein Bisschen anders aus.
			Dort muss der Administrator zwei (oder mehrere) unterschiedliche Vorlesungen vorsehen, die auch
			von unterschiedlichen Dozenten angeboten werden können, aber dort vergeben die beiden Dozenten
			nur Teilnoten. Da ein Fach mit Teilleistungen aus Minimum zwei Leisungen besteht, die Eine mit
			Abschluss "Gemeinsame Leistung" und die Andere mit Abschluss "FP, uT oder bT", wird nur der Dozent,
			der die Leistung mit dem Abschluss "FP, uT oder bT" anbietet, eine Endnote für das gesamte Fach
			erteilen.
		</li>
	</ul>
	
	<h4>Über Studienpläne</h4>
	<ul>
		<li><b>Studienplan löschen</b><br>
		Wenn ein Studienplan nicht mehr aktuell ist, kann er gelöscht werden. Das ist aber nicht empfehlenswert,
		denn alle Fächer, die diesem Studienplan zugeordnet waren, werden von dem Studienplan entfernt, aber nicht
		gelöscht. Es ist zu empfehlen, dass ein Studienplan nicht gelöscht, sondern korrigiert und angepasst wird.
		</li>
		<li><b>Wahlpflichtfächer</b><br>
		Die Wahlpflichtfächer, die beim Erzeugen eines Studienplans angegeben werden, sind keine verbindliche Anzahl,
		wieviele Wahlpflichtfächer dem Studienplan zugeordnet werden.
		Diese Zahl zeigt nur den Studenten, wieviele Fächer sie absolvieren Sollten.
		<br>
		Ein Wahlpflichtfächer ist dem Studienplan im Semester 0 zugeordnet.
		</li>
	</ul>
	
	<h4>Über Dozenten</h4>
	<ul>
		<li><b>Dozenten</b><br>
		Dozenten richtet man unter dem Menupunkt "Dozenten" ein. Dort kann man vorhandenen Dozenten den Zugang
		abziehen oder neu erteilen.
		</li>
	</ul>
	
	<h4>Über die Einstellungen</h4>
	<ul>
		<li><b>Wichtig</b><br>
		Jedes Semester (z.B. in der Mitte jedes Semesters) soll der Administrator die Einstellungen neu eingeben.
		Winter-/Sommersemesterende, Winter-/Sommersemesterbeginn und Anmeldefrist für Fachprüfungen. Die Anmeldefrist
		muss innerhalb der Semesters liegen, denn nach Ablauf der Frist werden die An-/Abmeldungen für Fächer mit
		Abschluss Fachprüfung gesperrt. 4 Tage vor dem Ablauf der Anmeldefrist werden alle Studenten darüber
		benachrichtigt. Diese Nachricht kann auch eine zusätzliche Aufforderung einthalten, die Sie unter dem
		Menupunkt "Einstellungen" eingeben können.
		</li>
	</ul>
	
</div>
<br />
<br />
<br />
</body>
</html>
