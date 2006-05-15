<?php
/********************************************************************************************************
 *					Datei: Bericht.php
 *					Author: David M. Hübner & Christian Burghoff
 *					E-Mail: info@milbridge.de
 *					Letzte Änderung: 25.01.2006
 */
	
	include("libraries.php");
	
	class Dozent {
		
		private $loggedIn;	// Ob der Student eingeloggt ist
		
		private $id;
		private $name;
		private $email;
		private $tel;
		private $username;
		
		private $last_error;
		private $conn;
		
		function __construct(Connection $conn) {
			$this->loggedIn = false;
			$this->last_error = "";
			$this->conn = $conn;
		} //constructor
		
		// Initialisiert das Objekt mit den Daten aus der Datenbank
		// liefert true, wenn der Dozent eingeloggt ist, sonst false
		public function init($user, $pass) {
			$this->loggedIn = false;
			$conn = $this->conn;
			
			if (!$conn->isConnected()) {
				$this->last_error = "Keine verbindung zur Datenbank";
				return false;
			}
			
			$pass = addslashes($pass);
			$user = addslashes($user);
			$query = new DBQuery("SELECT d.PDozentID, d.Name, d.EMail, d.Telefon FROM dozentlogin l LEFT JOIN dozent d ON (l.PDozentID=d.PDozentID) WHERE Password=\"$pass\" and Username=\"$user\"");
			if ($result = $conn->executeQuery($query)) {
				if ($result->rowsCount() == 1 && $row = $result->getNextRow()) {
					$this->loggedIn = true;
					$this->username = $user;
					$this->id       = $row[0];
					$this->name     = $row[1];
					$this->email    = $row[2];
					$this->tel      = $row[3];
				} else {
					$this->last_error = "Fehlerhafte Benutzername und Passwort.";
				}
			} else {
				$this->last_error = "Fehler beim Einloggen. ".$conn->getLastError();
			}
			
			return $this->loggedIn;
		}
		
		public function hatStudiengang() {
			return $this->hatStudiengang;
		}
		
		public function getLastError() {
			return $this->last_error;
		}
		public function getUsername() {
			return $this->username;
		}
		
		public function isLoggedIn() {
			return $this->loggedIn;
		}
		
		public function getName() {
			return $this->name;
		}

		public function getEmail() {
			return $this->email;
		}
		
		public function getTel() {
			return $this->tel;
		}

		public function getID() {
			return $this->id;
		}
		
		// Liefert die Verbingung zur Datenbank zurück
		public function getConnection() {
			return $this->conn;
		}
		
		// Liefert das ResultSet mit den ID, Name, Email, Telefon, Benutzername der Dozenten
		public static function enumDozenten(Connection $conn) {
			$q = new DBQuery("SELECT d.PDozentID, d.Name, d.EMail, d.Telefon, log.Username FROM dozent d LEFT JOIN dozentlogin log ON (log.PDozentID=d.PDozentID) ORDER BY d.Name");
			if ($conn->isConnected()) {
				return $conn->executeQuery($q);
			} else {
				return false;
			}
		}
		
		// Erzeugt einen Neuen Dozent
		public static function neuerAnmelden(Connection $conn, ErrorQueue $err, $name, $email, $tel, $username, $password) {
			$mdpass = md5($password);
			$username = addslashes($username);
			$name = addslashes($name);
			$tel = addslashes($tel);
			$email = addslashes($email);
			
			
			// Überprüfen, ob ein Dozent mit dem Benutzernamen schon existiert
			$q = new DBQuery("SELECT Username from dozentlogin WHERE Username='$username'");
			if ($res = $conn->executeQuery($q)) {
				if ($res->rowsCount() > 0) {
					$err->addError("Dieser Benutzername existiert schon. W&auml;hlen Sie bitte einen anderen.");
					return false;
				} else {
					// Wenn der Benutzername nicht in der Datenbank vorhanden ist, dann darf der Dozent angemeldet werden
					$q = new DBQuery("INSERT INTO dozent(Name, EMail, Telefon) VALUES('$name', '$email', '$tel')");
					if ($res = $conn->executeQuery($q)) {
						$dozentid = $res->getInsertId();
						$q = new DBQuery("INSERT INTO dozentlogin(PDozentID, Username, Password) VALUES($dozentid, '$username', '$mdpass')");
						if ($res = $conn->executeQuery($q)) {
							// Wenn auch der Benutzer angelegt wurde
							// versenden wir eine Mail dem Dozenten
							Mailer::mailit($email, "Anmeldung zur Web-Faecherverwaltung", "Hallo $name,\n willkommen im Web-System zur Faecherverwaltung. Ihre Benutzerdaten sind: Benutzername: \"$username\"\nPasswort: \"$password\"");
							return true;
						} else {
							$err->addError("Der Benutzer konnte nicht angelegt werden. ".$conn->getLastError());
							// Hier wird versuch den angelegten Dozent zu entfernen.
							$conn->executeQuery(new DBQuery('DELETE FROM dozent WHERE PDozentID='.$dozentid));
							return false;
						}
					} else {
						$err->addError("Der Dozent konnte nicht angelegt werden. ".$conn->getLastError());
						return false;
					}
				}
			} else {
				$err->addError($conn->getLastError());
				return false;
			}
			
		}
		
		// Löscht die angebotenen Fächer und die Zugriffsrechte des Dozents
		public static function abmeldenDozent(Connection $conn, ErrorQueue $err, $dozid) {
			$dozid = intval($dozid);
			
			// Löschen der Anmeldungen zu den Fächern dieses Dozents
			$q = new DBQuery("SELECT PAnfachID FROM angebotenesfach WHERE PDozentID=$dozid");
			if ($res = $conn->executeQuery($q)) {
				while ($r = $res->getNextRow()) {
					Mailer::mailOnFachFreigabe($conn, $dozid, $r[0]); // Studenten benachrichtigen
					$q0 = new DBQuery("DELETE FROM angemeldet WHERE PAnFachID=".$r[0]);
					if (!$conn->executeQuery($q0)) {
						$err->addError($conn->getLastError());
					}
				}
			} else {
				$err->addError($conn->getLastError());
				return false;
			}
			
			// Löschen von angebotenen Fächern und Zugriffsrechten
			$q1 = new DBQuery("DELETE FROM angebotenesfach WHERE PDozentID=$dozid");
			$q2 = new DBQuery("DELETE FROM dozentlogin WHERE PDozentID=$dozid");
			if ($conn->executeQuery($q1)) {
				if ($conn->executeQuery($q2)) {
					return true;
				} else {
					$err->addError("Die Zugriffsrechte konnten nicht gel&ouml;scht werden. ".$conn->getLastError());
					return false;
				}
			} else {
				$err->addError("Die angebotenen F&auml;cher konnten nicht gel&ouml;scht werden. ".$conn->getLastError());
				return false;
			}
		}
		
		// Setzt neunen Benutzernamen und Passwort für den angegebenen Dozent ein
		public static function zugriffErteilen($conn, $err, $dozentid, $username, $password) {
			$mdpass = md5($password);
			$username = addslashes($username);
			$dozentid = intval($dozentid);
			
			$q = new DBQuery("SELECT log.PDozentID, d.Email FROM dozent d LEFT OUTER JOIN dozentlogin log ON (log.PDozentID=d.PDozentID) WHERE d.PDozentID=".$dozentid);
			if ($res = $conn->executeQuery($q)) {
				if ($r = $res->getNextRow()) {
					$email = $r[1];
					if ($r[0] != NULL) {
						// MAKE ONLY UPDATE
						$q1 = new DBQuery("UPDATE dozentlogin SET Username='$username', Password='$mdpass' WHERE PDozentID=".$dozentid);
						if ($conn->executeQuery($q1)) {
							Mailer::mailit($email, "Zugriff zur Web-Faecherverwaltung", "Hallo $name,\n Ihre Benutzerdaten sind: Benutzername: \"$username\"\nPasswort: \"$password\"");
							return true;
						} else {
							$err->addError($conn->getLastError());
							return false;
						}
					} else {
						// MAKE FULL INSERT
						$q1 = new DBQuery("INSERT INTO dozentlogin(PDozentID, Username, Password) VALUES($dozentid, '$username', '$mdpass')");
						if ($conn->executeQuery($q1)) {
							Mailer::mailit($email, "Zugriff zur Web-Faecherverwaltung", "Hallo $name,\n Ihre Benutzerdaten sind: Benutzername: \"$username\"\nPasswort: \"$password\"");
							return true;
						} else {
							$err->addError($conn->getLastError());
							return false;
						}
					}
				} else {
					$err->addError("Dieser Dozent existiert nicht. Sie k&ouml;nnen nur vorhandenen Dozenten Zugriffsrechte erteilen.");
					return false;
				}
			} else {
				$err->addError($conn->getLastError());
				return false;
			}
		}
		
		// Füllt in das UtilBuffer alle freien Fächer, die nicht angeboten sind und liefert True zurück,
		// oder false bei Fehler
		public function getAlleFreieFaecher(ErrorQueue $err, UtilBuffer $buffer, $filter) {
			if ($this->loggedIn) {
				return Fach::getAlleFreieFaecher($this->conn, $err, $buffer, $filter);
			} else {
				$err->addError("Sie sind nicht eingeloggt.");
				return false;
			}
		}
		
		// Liefert ein Array aus AngebotenesFach-Objekten, die der Dozent anbietet, zurück oder false bei Fehler
		public function getAlleMeineFaecher() {
			if ($this->isLoggedIn()) {
				if ($this->conn->isConnected()) {
					$s = "SELECT f.Kennzahl, f.Teilkennziffer, f.Name, f.WS_SS, f.Status, f.SWS, f.CP, f.Herkunft, f.AnzLeistungen, f.AnzahlDozenten, a.PAnFachID, a.Anfang, a.Text, a.AnteilLeistung, a.MaxTeilnehmer, a.Anmeldefrist, a.Pruefungstermin, a.NurPruefung FROM fach f RIGHT JOIN angebotenesfach a ON (a.Kennzahl=f.Kennzahl and a.Teilkennziffer=f.Teilkennziffer) WHERE a.PDozentID=".$this->id." ORDER BY f.Kennzahl";
					$q = new DBQuery($s);
					if ($res = $this->conn->executeQuery($q)) {
						$arr = array(); // Array für die Fächer
						$i = 0;
						$ht = new Hashtable();
						while ($r = $res->getNextRow()) {
							$ht->setValue('nurPruefung', $r[17]);
							$arr[$i] = AngebotenesFach::create2($this->conn, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7], $r[8], $r[9], $r[10], $r[11], $r[12], $r[13], $r[14], $r[15], $r[16], $ht);
							$i++;
						}
						return $arr;
					} else {
						$this->last_error = "Die F&auml;cher konnten nicht ausgelesen werden. ".$this->conn->getLastError();
						return false;
					}
				} else {
					$this->last_error = "Keine Verbindung zur Datenbank.";
					return false;
				}
			} else {
				$this->last_error = "Sie sind nicht eingeloggt.";
				return false;
			}
		}
		
		/* Liefert eine UtilGroup mit den Fächern als Schlüssel und den Terminen als Gruppenmitglieder
		*  Bei Fehler false.
		*/
		public function getAlteFaecher() {
			$group = UtilGroup::createGroupByFach();
			
			if ($this->isLoggedIn()) {
				if ($this->conn->isConnected()) {
					$s = "SELECT f.Kennzahl, f.Teilkennziffer, f.Name, f.WS_SS, f.Status, f.SWS, f.CP, f.Herkunft, f.AnzLeistungen, f.AnzahlDozenten, n.Datum FROM fach f LEFT JOIN hatleistung n ON (n.Kennzahl=f.Kennzahl and n.Teilkennziffer=f.Teilkennziffer) WHERE n.PDozentID=".$this->id." ORDER BY f.Kennzahl";
					$q = new DBQuery($s);
					if ($res = $this->conn->executeQuery($q)) {
						while ($r = $res->getNextRow()) {
							$f = new Fach($this->conn);
							$f->init2($r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7], $r[8]);
							$f->setAnzahlDozenten($r[9]);
							$group->addFach($f, $r[10]);
						}
						
						return $group;
					} else {
						$this->last_error = "Fehler beim Auslesen der alten F&auml;cher.".$this->conn->getLastError();
						return false;
					} // if-else
				} else {
					$this->last_error = "Keine Verbindung zur Datenbank.";
					return false;
				}
			} else {
				$this->last_error = "Sie sind nicht eingeloggt.";
				return false;
			}
		}
		
		// Liefert 1, wenn der Dozent das Fach mit der angegebenen ID anbietet sonst 0
		// Bei Fehler -1;
		public function istMeinFach($anfachid) {
			$anfachid = intval($anfachid);
			$q = new DBQuery("SELECT PAnFachID FROM angebotenesfach WHERE PAnFachID=$anfachid and PDozentID=".$this->getID());
			if ($result = $this->conn->executeQuery($q)) {
				if ($result->rowsCount() > 0) {
					return true;
				} else {
					return false;
				}
			} else {
				$thid->last_error = "Fehler beim &Uuml;berpr&uuml;fen des Angebots. ".$this->conn->getLastError();
				return -1;
			}
		}
		
	
		// Gibt die Noten ab und meldet die Studenten ab, wenn es sein muss
		// Die Fehler werden direkt in die ErrorQueue eingefügt
		// True wird zurückgeliefert. Bei Fehler false.
		public function notenAbgeben($anfachid, $notenstatus, $termin, ErrorQueue $err, UtilBuffer $notenwerte, UtilBuffer $matrnummer, UtilBuffer $fehlendeVorleistungen) {
			$anfachid = intval($anfachid);
			$notenstatus = intval($notenstatus);
			$freigebenAmEnde = false; // Ob das Fach nach der Notenabgabe freigegeben werden soll.
			
			if ($notenwerte->getCount() != $matrnummer->getCount() || $notenwerte->getCount() != $fehlendeVorleistungen->getCount()) {
				$err->addError("Systemfehler: Funktion 'notenAbgeben' wurde mit fehlerhaften Patametern aufgerufen.");
				return false;
			}
			
			if ($anfach = AngebotenesFach::readAngebotenesFach($this->conn, $err, $anfachid)) {
				$fach = $anfach->getFach();
				
				$hatGemeinsame = $fach->hatGemeinsameLeistungen();
				$istPruefung = $fach->istPruefung();
				
				if (-1 == $hatGemeinsame) {
					$err->addError($fach->getLastError());
					return false;
				}
				
				if (1 == $hatGemeinsame) {
					if ($istPruefung) {
						$notenstatus = ($notenstatus == Note::STATUS_TEILNOTE) ? Note::STATUS_TEILNOTE : Note::STATUS_ENDNOTE;
						// Wenn das eine Prüfung ist, die mit gemeinsamen Leistungen verbunden ist
						// dann wird ersmal überprüft, was überhaupt abgegeben wurde
						$abgabeStatus = $anfach->getNotenabgabeStatus();
						if(-1 == $abgabeStatus) {
							$err->addError($anfach->getLastError());
							return false;
						}
						
						if ($notenstatus == Note::STATUS_TEILNOTE && $abgabeStatus == Note::STATUS_TEILNOTE) {
							$err->addError("Sie haben die Teilnoten f&uuml;r dieses Fach schon vergeben.");
							return false;
						}
						
						if ($notenstatus == Note::STATUS_ENDNOTE && $abgabeStatus == Note::STATUS_ENDNOTE) {
							$err->addError("Sie haben die Endnoten f&uuml;r dieses Fach schon vergeben.");
							return false;
						}
						
						$endnote = $notenstatus;
						
						if ($abgabeStatus == 0) {
							$freigebenAmEnde = false;
						} else {
							$freigebenAmEnde = true;
						}
						
					} else {
						$endnote = Note::STATUS_TEILNOTE;
						$freigebenAmEnde = true;
					}
				} else {
					if ($istPruefung) {
						$endnote = Note::STATUS_ENDNOTE;
					} else {
						$endnote = Note::STATUS_TEILNOTE;
					}
					$freigebenAmEnde = true;
				}
			} else {
				$err->addError("Das Fach ist nicht mehr angeboten. Sie k&ouml;nnen nur Noten abgeben, wenn Sie das Fach anbieten.");
				return false;
			}

			if ($endnote == Note::STATUS_TEILNOTE) {
				$endnoteString = "TEILNOTE";
			} else {
				$endnoteString = "ENDNOTE";
			}

			$dozentmessage = "Hallo ".$this->getName().",\r\n";
			$dozentmessage .= "Sie haben gerade die folgenden Noten zum Fach ".$anfach->getKennzahl()." ".$anfach->getName()." abgegeben.\r\n\r\n";

			for ($i = 0; $i < $notenwerte->getCount(); $i++) {
				$notenwert  = $notenwerte->get($i);
				$matrnr     = $matrnummer->get($i);
				
				$kennzahl   = $anfach->getKennzahl();
				$kennziffer = $anfach->getKennziffer();
				$termin     = addslashes($termin);
				$fehlende   = $fehlendeVorleistungen->get($i);
				$interpretation = Note::getInterpretation($notenwert);
				$bestanden = Note::retrBr($interpretation);
				if (Note::NOTE_BESTANDEN == $interpretation || Note::NOTE_NICHTBESTANDEN == $interpretation) {
					$note = $notenwert;
				} else {
					$note = 0;
				}

				$n = new Note($note, $interpretation, $termin);
				$n->setFehlendeVorleistungen($fehlende);
				$n->setMatrNr($matrnr);
				$n->setDozentId($this->getID());
				$n->setKennzahl($kennzahl);
				$n->setKennziffer($kennziffer);
				if ($endnote == Note::STATUS_ENDNOTE) {
					$n->setEndnote(true);
				} else {
					$n->setEndnote(false);
				}
				
				$fristgerecht = Student::istFristgerechtAngemeldet($this->conn, $err, $matrnr, $anfach);
				$fristgerechtS = $fristgerecht ? 'JA' : 'NEIN';

				$s  = "INSERT INTO hatleistung(MatrNr, Kennzahl, Teilkennziffer, Bestanden, Note, Datum, TeilEndNote, PDozentID, FehlendeVorleistungen, FristgerechtAngemeldet) ";
				$s .= "VALUES($matrnr, '$kennzahl', $kennziffer, '$bestanden', $note, '$termin', '$endnoteString', ".$this->getID().", $fehlende, '$fristgerechtS')";
				$q = new DBQuery($s);
				if ($this->conn->executeQuery($q)) {
					Mailer::mailNote($n, $anfach, $this);
					$vf = $n->hatFehlendeVorleistungen() ? "(Vorleistungen fehlen)" : "";
					$dozentmessage .= $n->getMatrNr().": ".$n->getBenotung($anfach->getFach())." ".$vf."\r\n";
					
					if ($freigebenAmEnde) {
						$q = new DBQuery("DELETE FROM angemeldet WHERE MatrNr=$matrnr and PAnFachID=".$anfach->getID());
						if (false == $this->conn->executeQuery($q)) {
							$err->addError("Die Abmeldung von $matrnr konnte nicht durchgef&uuml;hrt werden. ".$this->conn->getLastError());
							return false;
						}
					}
				} else {
					$err->addError("Unerwarteter Fehler. Die Note von $matrnr konnte nicht abgegeben werden.");
					return false;
				}
			} // for 
			
			$dozentmessage .= "\r\nTermin der Pruefung: ".$termin."\r\n";
			$dozentmessage .= "Status: ".$endnoteString;
			Mailer::mailit($this->getEmail(), "Notenabgabe ".$anfach->getKennzahl()." ".$anfach->getName()." zum ".$termin, $dozentmessage);
			
			// Hier werden (wenn es soweit ist) die Studenten abgemeldet und das Fach freigegeben
			if ($freigebenAmEnde) {
				if (false == $this->fachBeenden($anfachid)) {
					$err->addError($this->getLastError());
				}
			} else {
				if (false == $anfach->setNotenabgabeStatus($notenstatus)) {
					$err->addError($anfach->getLastError());
				}
			}
			
			return true;
		} // notenAbgeben
		
		
		// "$status" kann 1 2 oder 3 sein
		// 1 = note hat schon vorleistungen (EIN UPDATE WIRD DURCHGEFÜHRT)
		// 0 = note hat die Vorleistungen noch nicht (HIER WIRD NICHTS GETAN)
		// 2 = die Note wird entfernt (DELETE FROM HATLEISTUNG)
		// Wenn status eine andere Zahl ist, wird nichts getan und true zurückgegeben
		public function noteFreischalten($noteid, $status, $termin) {
			
			$noteid = addslashes($noteid);
			$status = intval($status);
			$termin = addslashes($termin);
			if ($status == 1) { // Wenn die Vorleistungen erbracht wurden
				$q = new DBQuery("UPDATE hatleistung SET Datum='$termin', PDozentID=".$this->getID().", FehlendeVorleistungen=0 WHERE PLeistungID=".$noteid);
				if ($this->conn->executeQuery($q)) {
					return true;
				} else {
					$this->last_error = "Fehler beim &Auml;ndern einer Note mit fehlenden Vorleistungen.".$this->conn->getLastError();
					return false;
				}
			} else if ($status == 2) {
				$q = new DBQuery("DELETE FROM hatleistung WHERE PLeistungID=".$noteid);
				if ($this->conn->executeQuery($q)) {
					return true;
				} else {
					$this->last_error = "Fehler beim L&ouml;schen einer Note mit fehlenden Vorleistungen.".$this->conn->getLastError();
					return false;
				}
			}
			
			return true;
		}
		
		// Beendet das Fach, falls keine Studenten mehr angemeldet sind und liefert true zurück, sonst false
		// Hier werden die gemeinsamen Fächer auch abgeben
		public function fachBeenden($anfachID) {
			
			$anfachID = intval($anfachID);
			
			// Wenn keiner Mehr angemeldet ist wird das Angebot freigegeben
			if ($this->istMeinFach($anfachID)) {
				if ($this->gibDasFachFrei($anfachID)) {
					return true;
				} else {
					return false;
				}
			} else {
				$this->last_error = "Sie haben versucht ein Fach zu beenden, das Sie nicht anbieten.";
				return false;
			}
		} // function
		
		// Beendet vorzeitig das Angebot und gibt alle angemeldeten Studenten frei.
		public function fachFreigeben($anfachID) {
			$anfachID = intval($anfachID);
			if ($this->istMeinFach($anfachID)) {
				Mailer::mailOnFachFreigabe($this->getConnection(), $this->getID(), $anfachID);
				$this->gibDasFachFrei($anfachID);
				return true;
			} else {
				$this->last_error = "Sie k&ouml;nnen dieses Fach nicht freigeben, denn es wird von jemandem anderen angeboten.";
				return false;
			}
		}
		
		// Diese Methode erzwingt die Freigabe eines angebotenen Fachs, ohne den Anbietenen zu bezücksichtigen
		// Nötig für die Abgabe von "Gemeinsame" und "Keine" Fächer
		public function gibDasFachFrei($anfachID) {
			$anfachID = intval($anfachID);
			$q = new DBQuery("DELETE FROM angemeldet WHERE PAnFachID=".$anfachID);
			if ($this->conn->executeQuery($q)) {
				$q = new DBQuery("DELETE FROM angebotenesfach WHERE PAnFachID=".$anfachID);
				if ($this->conn->executeQuery($q)) {
					return true;
				} else {
					$this->last_error = "Fehler beim Freigeben eines Fachs.".$this->conn->getLastError();
					return false;
				}
			} else {
				$this->last_error = "Fehler beim Freigeben eines Fachs.".$this->conn->getLastError();
				return false;
			}
		}
		
		// Korrigiert die Note, falls sie von diesem Dozent abgegeben wurde und liefert true zurück
		// sonst false.
		public function noteKorrigieren($noteid, $note, $bestanden, $vorl) {
			$vorl = intval($vorl);
			$noteid = addslashes($noteid);
			$bestanden = Note::retrBr(intval($bestanden));
			$note = Util::toDouble($note, null);
			$s = "UPDATE hatleistung SET Bestanden='$bestanden', Note='$note', FehlendeVorleistungen='$vorl' WHERE PLeistungID=$noteid and PDozentID=".$this->getID();
			$q = new DBQuery($s);
			if ($res = $this->conn->executeQuery($q)) {
				if ($res->affectedRows() == 0) {
					$this->last_error = "Die Note ist nicht mehr g&uuml;ltig.";
				} else return true;
			} else {
				$this->last_error = "Fehler. Die Note konnte nicht ge&auml;ndert werden.".$this->conn->getLastError();
			}
			
			return true;
		}
		
		/*
		* Diese Methode ist veraltet. Man soll die Klasse Notenliste verwenden, um die Noten zu einem Termin zu bekommen.
		*/
		// Liefert ein UtilBuffer mit den Noten, die zu einem Termin von diesem Dozent abgegeben wurden
		// False bei Fehler
		public function getNotenZuTermin($termin, $kennzahl, $kennziffer) {
			$termin = addslashes($termin);
			$kennzahl = addslashes($kennzahl);
			$kennziffer = addslashes($kennziffer);
			$q = new DBQuery("SELECT PLeistungID, MatrNr, Kennzahl, Teilkennziffer, Bestanden, Note, Datum, TeilEndNote, PDozentID, FehlendeVorleistungen FROM hatleistung WHERE PDozentID=".$this->getID()." and Datum='$termin' and Kennzahl='$kennzahl' and Teilkennziffer=$kennziffer ORDER BY MatrNr");
			if ($res = $this->conn->executeQuery($q)) {
				$buf = new UtilBuffer();
				while ($r = $res->getNextRow()) {
					$n = new Note($r[5], Note::trBe($r[4]), $r[6]);
					$n->setID($r[0]);
					$n->setMatrNr($r[1]);
					$n->setKennzahl($r[2]);
					$n->setKennziffer($r[3]);
					if (strcmp($r[7], 'ENDNOTE') == 0) $e = true;
					else $e = false;
					$n->setEndnote($e);
					$n->setDozentId($r[8]);
					$n->setFehlendeVorleistungen($r[9]); // Anzahl der fehlenden Vorleistunen (0 oder 1)
					$buf->add($n);
				}
				
				return $buf;
			} else {
				$this->last_error = "Die Noten vom $termin konnten nicht ausgelesen werden.";
				return false;
			}
		}
		
		
	} // CLASS
	
?>