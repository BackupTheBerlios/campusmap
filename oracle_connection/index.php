<?php
 require_once("view.php");

 $sicht = new View($_GET);
 $sicht->printCommentsForJava();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Unbenanntes Dokument</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>

<body bgColor="#f0f0ff">
<div style="position:absolute; left:0px;">
<table width="530" border="0" cellspacing="0" cellpadding="5">
  <?php $sicht->printContentTableRows(($_GET!=null)?false:true); ?>
</table>
</div>
</body> 
</html>