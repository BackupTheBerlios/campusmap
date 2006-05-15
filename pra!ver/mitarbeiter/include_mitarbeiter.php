<?php
	session_start();
	header ("Cache-control: private");
	Header ("HTTP/1.0 401 Unauthorized");  
	Header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");			// Datum der Vergangenheit
	Header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");	// immer geändert
	Header ('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
	Header ("Pragma: no-cache");					// HTTP/1.0

	include_once("../libs/libraries.php");

	function invokeLogin($txt) {
		$_SESSION['txt'] = $txt;
		include("login.php");
		exit();
	}

	if (isset($_POST['pass']) && isset($_POST['user'])) {
		$user = addslashes($_POST['user']);
		$_SESSION['mdpass'] = md5($_POST['pass']);
		$_SESSION['user'] = $user;
		$_POST['pass'] = "";
		$pass = "";
	}

	$err = new ErrorQueue();
	$conn = new Connection();
	$mitarbeiter = new Mitarbeiter($conn);
	
	if (!isset($_SESSION['mdpass']) || !isset($_SESSION['user'])) {
		invokeLogin("Geben Sie Ihre Benutzernamen und Passwort ein!");
	} else {
		if ($conn->connect(Config::DB_SERVER, Config::DB_NAME, Config::DB_USERNAME, Config::DB_PASSWORD)) {
			if (!$mitarbeiter->login($_SESSION['user'], $_SESSION['mdpass'])) {
				invokeLogin($mitarbeiter->getLastError());
			} // if can't init, do login, else it's OK
		} else {
			$err->addError($conn->getLastError());
		} // if-else connection
	} // if-else variables

?>