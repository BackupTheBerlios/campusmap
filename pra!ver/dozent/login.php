
<?php

	include("../libs/libraries.php");

	if (isset($_SESSION['mdpass'])) {
		unset($_SESSION['mdpass']);
		unset($_SESSION['username']);
		session_destroy();
	}
	
	$err = new ErrorQueue();
	$meldung = "";

	
?>

<html>
<head>
<title>Dozent anmelden</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../styles/main.css" type="text/css">
<link rel="stylesheet" href="../styles/login.css" type="text/css">
<link rel="stylesheet" href="../styles/menu.css" type="text/css">
<link rel="stylesheet" href="../styles/prof.css" type="text/css">

</head>

<body leftmargin="0" topmargin="0">
<table height="100%" border="0" cellspacing="0" cellpadding="0" class="hauptbox"><tr><td valign="top" height="100%">
<?php include("../libs/kopf.php"); ?>
<br>
<div>
	<table width="750" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td>&nbsp;&nbsp;&nbsp;</td>
	<td valign="bottom" class="logo"><img src="../images/logos/logo.gif" width="151" height="36" border="0" /></td>
	<td align="right" width="100%" valign="bottom">
	<table border="0" cellspacing="0" class="menutable" style="margin-bottom:3px;">
	
	   <tr>
	    
	    <td><a class="menulink" href="../">Home</a></td>
	   </tr>

	  </tr>
	</table>
	</td>
	</tr>
	</table>
<br><br>
</div>




<div id="errorDiv">
	<?php
		if (isset($_SESSION['txt'])) {
			echo "<h5>".$_SESSION['txt']."&nbsp;</h5>";
			unset($_SESSION['txt']);
		}
		
		if ($err->moreErrors()) {
			echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
		}
		
		if ($meldung != "") {
			echo '<p class="meldung">'.$meldung."</p>";
		}
	?>
</div>
	
<div class="hintergrundstreifen">
	<div class="floatleft">
		<div class="titelHalb">
			<img border="0" src="../images/<?php if($ok==30) echo 'ico_ok_dark.gif'; else if ($ok==31) echo 'ico_x_dark.gif'; else echo 'ico_inhalt.gif'; ?>">
			DOZENTENANMELDUNG
		</div>
		<div class="inhaltHalb">
	
		
			<form name="form1" method="post" action="berichteliste_abgegeben.php">
			    <table border="0" cellspacing="0">
			      <tr> 
			        <td>Benutzername:</td>
			        <td align="right"><input type="text" name="username" class="formfield" value=""></td>
			      </tr>
			      <tr> 
			        <td>Passwort:</td>
			        <td align="right"><input type="password" maxlength="255" name="pass" class="formfield" value=""></td>
			      </tr>
			      <tr> 
			        <td>&nbsp;</td>
			        <td align="right"><input type="image" border="0" src="../images/buttons/anmelden.gif"></td>
			      </tr>
				</table>
			</form>
		</div>
	</div>

</div>
<br><br><br>
</td></tr>
<?php include("../libs/fuss.php"); ?>
</table>
</body>

</html>
