<?php
	include("../libs/libraries.php");

	$err = new ErrorQueue();
	$conn = new Connection();

	if ($conn->connect($DB_SERVER, $DB_NAME, $DB_USERNAME, $DB_PASSWORD)) {
		
		$gruppe = -1;
		if (isset($_GET['gruppe'])) {
			$gruppe = intval($_GET['gruppe']);
		}
		
		$sortierenach = 0;
		$sortierrichtung = 0;
		$region = 0;
		$groesse = 0;
		$branche = 0;
		$fachbereich = 0;
		$studiengang = 0;
		$keyword = "";
		$seite = 0;
		$aufzu = "zu";
		
		
		if (isset($_GET['aufzu'])) {
			$aufzu = ($_GET['aufzu']);
		}
		if (isset($_GET['auf_x'])) {
			$aufzu = "auf";
		}
		if (isset($_GET['zu_x'])) {
			$aufzu = "zu";
		}
		if (isset($_GET['aktion'])) {
			$aufzu = ($_GET['aktion']);
		}

		
		if (isset($_GET['sortierenach'])) {
			$sortierenach = intval($_GET['sortierenach']);
		}
		if (isset($_GET['sortierrichtung'])) {
			$sortierrichtung = intval($_GET['sortierrichtung']);
		}
		if (isset($_GET['seite'])) {
			$seite = intval($_GET['seite']);
		}
		if (isset($_GET['region'])) {
			$region = intval($_GET['region']);
		}
		if (isset($_GET['groesse'])) {
			$groesse = intval($_GET['groesse']);
		}
		if (isset($_GET['branche'])) {
			$branche = intval($_GET['branche']);
		}
		if (isset($_GET['fachbereich'])) {
			$fachbereich = intval($_GET['fachbereich']);
		}
		if (isset($_GET['studiengang'])) {
			$studiengang = intval($_GET['studiengang']);
		}
		if (isset($_GET['keyword'])) {
			$keyword = ($_GET['keyword']);
		}

		if (($kontinentenumeration = Staat::enumKontinente($conn, $err))) {
		} else {
			$err->addError($conn->getLastError());
		}
		if (($unternehmensgroessenenum = Unternehmen::enumGroessen($conn, $err))) {
		} else {
			$err->addError($conn->getLastError());
		}
		if (($branchenenum = Unternehmen::enumBranchen($conn, $err))) {
		} else {
			$err->addError($conn->getLastError());
		};
		if (($fachbereichenum = Fachbereich::enumAktiveFachbereiche($conn, $err))) {
		} else {
			$err->addError($conn->getLastError());
		};
		if (($studiengangenum = Studiengang::enumAktiveStudiengaenge($conn, $err))) {
		} else {
			$err->addError($conn->getLastError());
		};
	
?>


<html>
<head>
<title>Bericht-Datenbank</title>
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
		SORTIERTE LISTE ABGELEISTETER PRAKTIKA
	</div>

	<div class="inhaltGanz">
	<? if($aufzu =="zu"){?>
		<form method="get" action="datenbank.php">
		<input type="hidden" name="gruppe" value="<?php echo $gruppe; ?>">
			<table  width="650" border="0" cellspacing="0" cellpadding="0" >
					<tr>
						<td width="80">
							<input type="image" name="auf" border="0" src="../images/buttons/auf.gif" value="auf">
							
						</td>
					</tr>
			</table>
		</form>
	<? }else if($aufzu =="auf"){?>	
		<form method="get" action="datenbank.php">
		<input type="hidden" name="gruppe" value="<?php echo $gruppe; ?>">
			<table  width="650" border="0" cellspacing="0" cellpadding="0" >
					<tr>
						<td width="80">
							<input type="image" name="zu" border="0" src="../images/buttons/zu.gif" value="zu">
						</td>
					</tr>
			</table>
		</form>
<form method="get" action="datenbank.php">
			<input type="hidden" name="sortierenach" value="<?php echo $sortierenach; ?>">
			<input type="hidden" name="sortierrichtung" value="<?php echo $sortierrichtung; ?>">
			<input type="hidden" name="gruppe" value="<?php echo $gruppe; ?>">
			<input type="hidden" name="aktion" value="auf">
			
			<table  width="650" border="0" cellspacing="0" cellpadding="0" >
				<tr>
						<td >
							Studiengang
						</td>

			
						<td>
							<select name="studiengang">
								<option value="0" <?php if($studiengang==0)echo"selected" ?>>alle</option>
								<option value="0" >-------------------</option>
								<?php
					    			if ($studiengangenum) {
						    			while ($r = $studiengangenum->getNextRow()) {
												$sel = ""; if ($studiengang==$r[0])$sel = ' selected="selected" ';
												echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
										}
									}
								?>
							</select>
						</td>
					
						<td align="right">
						<input type="image" border="0" src="../images/buttons/filtern.gif">
						</td>
					
					</tr>

					<tr>
						<td width="80">
							Fachbereich
						</td>
						<td>
							<select name="fachbereich">
								<option value="0" <?php if($fachbereich==0)echo"selected" ?>>alle</option>
								<option value="0" >-------------------</option>
								<?php
					    			if ($fachbereichenum) {
						    			while ($r = $fachbereichenum->getNextRow()) {
												$sel = ""; if ($fachbereich==$r[0])$sel = ' selected="selected" ';
												echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
										}
									}
								?>
							</select>
						</td>
						</tr>
					<tr>
						<td width="80" >
								L&auml;nder
						</td>
							
						<td >	
							
							<select name="region">
								<option value="0"	<?php if($region== 0)echo"selected" ?>>alle</option>
								<option value="10"	<?php if($region==10)echo"selected" ?>>Inland</option>
								<option value="20"	<?php if($region==20)echo"selected" ?>>Ausland</option>
								<option value="0" >-------------------</option>
								<?php
					    			if ($kontinentenumeration) {
						    			while ($r = $kontinentenumeration->getNextRow()) {
												$sel = ""; if ($region==$r[0])$sel = ' selected="selected" ';
												echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
										}
									}
								?>
							</select>
						</td>
					
					

				
					</tr>
					<tr>
						<td >
			
								Branche
						</td>

						<td>
							<select name="branche">
								<option value="0" <?php if($branche==0)echo"selected" ?>>alle</option>
								<option value="0" >-------------------</option>
								<?php
					    			if ($branchenenum) {
						    			while ($r = $branchenenum->getNextRow()) {
												$sel = ""; if ($branche==$r[0])$sel = ' selected="selected" ';
												echo '<option value="'.$r[0].'"'.$sel.'>'.$r[1].'</option>';
										}
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td  >
								Firmengr&ouml;&szlig;e
						</td>
						<td  >
						
							<select name="groesse">
								<?php
					    			if ($unternehmensgroessenenum) {
						    			while ($r = $unternehmensgroessenenum->getNextRow()) {
												$sel = ""; if ($groesse==$r[0])$sel = ' selected="selected" ';
												echo '<option value="'.$r[0].'"'.$sel.'>'.$r[2].'</option>';
										}
									}
								?>
							</select>
						</td>
						
					

					</tr>

					<tr>
						<td  >
								Ein Keyword
						</td>
						<td  >
						
							<input type="text" size="20" maxlength="30" name="keyword" value="<?php echo $keyword; ?>" class="formfield">
						</td>
						
					

					</tr>
				
				
					
				
			</table>
			
		</form>
		<?}?>

		
		<table border="0" cellspacing="0" class="parallelTable">
		    <tr>
		      
		      <td valign="top">
		      	
		      		<? //hier die funktion zur ausgabe aufrufen
		      		  $aktuellerLink = 'datenbank.php?sortierenach='.$sortierenach.'&sortierrichtung='.$sortierrichtung.'&region='.$region.'&groesse='.$groesse.'&branche='.$branche.'&fachbereich='.$fachbereich.'&studiengang='.$studiengang.'&keyword='.$keyword;
		              if ($htmltable = Bericht::createBerichtTable($conn,$err,$sortierenach, $sortierrichtung, $gruppe, false, $region, $groesse, $branche, $fachbereich, $studiengang, $seite, $aktuellerLink, $aufzu, $keyword)) {
		              	echo $htmltable;
		              }
		              else echo "<h5>".$err->createErrorsListAndRemove()."</h5>";
		          ?>
		      		
		      		
				    
			     
		      
		
		
		      </td>
		
		    </tr>
	  	</table>	    
	</div>

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
