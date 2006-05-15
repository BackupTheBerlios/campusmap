<?php
	session_start();
	header("Cache-control: private");
	Header ("HTTP/1.0 401 Unauthorized");  
	Header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");			// Datum der Vergangenheit
	Header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");	// immer geändert
	Header ('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
	Header ("Pragma: no-cache");					// HTTP/1.0

	include("../libs/libraries.php");

	function invokeLogin($txt) {
		$_SESSION['txt'] = $txt;
		include("login.php");
		exit();
	}

	if (isset($_POST['pass']) && isset($_POST['username'])) {
		$username = $_POST['username'];
		$_SESSION['mdpass'] = md5($_POST['pass']);
		$_SESSION['username'] = $username;
		$pass = "";
	}

	$err = new ErrorQueue();
	$conn = new Connection();
	$dozent = new Dozent($conn);
	$meldung = "";
	
	if (!isset($_SESSION['mdpass']) || !isset($_SESSION['username'])) {
		invokeLogin("Geben Sie Ihren Benutzernamen und Ihr Passwort ein!");
	} else {
		if ($conn->connect(Config::DB_SERVER, Config::DB_NAME, Config::DB_USERNAME, Config::DB_PASSWORD)) {
			if (!$dozent->init($_SESSION['username'], $_SESSION['mdpass'])) {
				invokeLogin($dozent->getLastError());
			} else {
				$err->addError($conn->getLastError());
			}
		} else {
			$err->addError($conn->getLastError());
		}
		
	}
?>