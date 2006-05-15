<?php
	
	include("libraries.php");

	class Connection {
		
		const QUPDATE = 1;
		const QINSERT = 2;
		const QSELECT = 3;
		
		const SPERREN = 0; // Wenn 1, dann ist der Datenbankzugriff gesperrt
		
		protected $connection_link = NULL;
		protected $connected = false;
		private   $last_error = "";
		private   $last_query;
		
		function __construct() {
			$this->connected = false;
			$this->last_query = new DBQuery("_INVALID_QUERY_");
		} //constructor
		
		public function connect($host, $database, $user, $password) {
			if (Connection::SPERREN == 1) {
				$this->last_error = "Zugriff temporär gesperrt. Bitte versuchen Sie es wieder in 30 Minuten.";
				return false;
			}
			
			$this->connection_link = mysql_connect($host, $user, $password);
			$this->connected = false;
			if (!$this->connection_link) {
    			$this->last_error = "Keine Verbindung zur Datenbank. ".mysql_error();
			} else {
				if (mysql_select_db($database)) {
					$this->connected = true;
				} else {
					$this->last_error = "Kein Zugriff auf '$database'. ".mysql_error();
				}
			}
			
			return $this->connected;
		} // connect
		
		// Erstellt eine Verbindung zur Datenbank mit den Standardwerten
		public function connectDefault() {
			return $this->connect(Config::DB_SERVER, Config::DB_NAME, Config::DB_USERNAME, Config::DB_PASSWORD);
		}
		
		public function executeQuery(DBQuery $query) {
			$this->last_query = $query;
			
			if (!$this->connected) {
				$this->last_error = "Keine Verbindung zur Datenbank.";
				return NULL;
			}
			
			$result = mysql_query($query->getString());
			
			if (!$result) {
				$this->last_error = mysql_error();
				return NULL;
			}
			
			return new ResultSet($result, $this->connection_link);
		}
		
		
		public function getLastError() {
			return $this->last_error;
		}
		
		public function getLastQuery() {
			return $this->last_query;
		}
		
		public function isConnected() {
			return $this->connected;
		}
		
		function __destruct() {
			if ($this->connected) {
				mysql_close($this->connection_link);
			}
		} //destructor
		
	}

?>
