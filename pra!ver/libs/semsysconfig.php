<?php

	/*
	 * In dieser Klasse sind die Datenbankeinstellungen einzutragen.
	 */

	class Config {
		
		const DB_SERVER   = 'localhost';      // Pfad zum MySQL-Server
		const DB_NAME     = 'studentmanager'; // Name der Datenbank
		const DB_USERNAME = 'manager';        // Benutzername
		const DB_PASSWORD = 'heXvBaF';        // Passwort
		
		const MAX_BERICHT_DATEIGROESSE = 20000000;
		const BERICHT_DATEIVERZEICHNIS = "../../../praver_upload/";
		
		const PRAVER_ROOT_URL = "http://osmigib.fh-luebeck.de/praver/";
		
		const HEIMATLAND_ID = 7;

		const DEBUGGING = 0; //
		
		// Systemfehler werden an die adresse gemailt.
		const SEND_DEBUG_INFORMATION_TO_EMAIL = '';//hristozk@stud.fh-luebeck.de';
		
	} // class

	/* OBSOLETE *************************************************************************/
	/* Diese Variablen sind veraltet und man darf sie im Quelltext nicht mehr verwenden */
	/************************************************************************************/
	$DB_SERVER   = Config::DB_SERVER;
	$DB_NAME     = Config::DB_NAME;
	$DB_USERNAME = Config::DB_USERNAME;
	$DB_PASSWORD = Config::DB_PASSWORD;

?>
