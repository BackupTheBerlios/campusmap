<?php
	
	include("libraries.php");
	
	class Mailer {
	
		public static function mailit($email, $subj, $text) {
			$subj = "[PRA|VER] ".$subj;
			
			$text = "Das ist eine automatische Nachricht vom Praktika-Verwaltungs-System.\r\n\r\n\r\n".$text;
			
			/* To send HTML mail, you can set the Content-type header to text/html. */
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
			$headers .= "From: praver@{$_SERVER['SERVER_NAME']}\r\n";
			$headers .= "Reply-To: PRA|VER <praver@millbridge.de>\r\n";
			$headers .= "Return-Path: PRA|VER <praver@millbridge.de>\r\n";
			$now = time();
			$headers .= "Message-ID: <".$now."@".$_SERVER['SERVER_NAME'].">\r\n";
			$headers .= "X-Mailer: PHP v".phpversion()."\r\n";
			
			/* and now mail it */

			//echo "Mail to: ".$email."<br>";
			//echo "Subject: ".$subj."<br>";
			//echo $text."<br ><br >";
			//echo "Mail wurde verschickt!<br>".$email." ".$subj." ".$text." ".$headers."<br>";
			
			mail($email, $subj, $text, $headers);
			//mail("david@millbridge.de", "mail wurde endlich mal verschickt", $text, $headers);
			
		} // mailit
		
		public static function mailAsHTML($email, $subj, $text) {
			$subj = "[PRA|VER] ".$subj;
			
			$text = "Das ist eine automatische Nachricht vom Praktika-Verwaltungs-System.<br /><br />\r\n\r\n\r\n".$text;
			
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From: praver@{$_SERVER['SERVER_NAME']}\r\n";
			
			/* and now mail it */
			mail($email, $subj, $text, $headers);
		}


		function checkMail($email) {
			if( ereg("^[a-zA-Z0-9-]+([._a-zA-Z0-9.-]+)*@[a-zA-Z0-9.-]+\.([a-zA-Z]{2,4})$",$email))
				return TRUE;
			else return FALSE;
		} 
		
		public static function sendGroupMail(UtilBuffer $anStudenten, Dozent $vonDozent, $sMessage, $subj,  $bSendCopyToDozent) {

			$subj = "[PRA|VER] ".$subj;
			
			//$text = "Das ist eine automatische Nachricht vom Studi-Manager-System.\r\n\r\n\r\n".$text;
			$text = $sMessage;
			
			/* To send HTML mail, you can set the Content-type header to text/html. */
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
			$headers .= "From: ".$vonDozent->getName()." <".$vonDozent->getEmail().">\r\n";
			
			// Verschicken der Mails
			for ($i = 0; $i < $anStudenten->getCount(); $i++) {
				$studi = $anStudenten->get($i);
				mail($studi->getEmail(), $subj, $text, $headers);
			}
			
			if ($bSendCopyToDozent) {
				$text = "Sie haben die folgende Nachricht über das Fächerverwaltungssystem verschickt:\r\n\r\n".$text;
				mail($vonDozent->getEmail(), $subj, $text, $headers);
			}

		}

		
		
	}
	
?>
