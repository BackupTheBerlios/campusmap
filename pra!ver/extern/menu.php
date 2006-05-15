
<?php if (isset($menue)) { ?>
<br>
<div>
	<table width="750" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td valign="bottom" class="logo"><img src="../images/logos/logo.gif" width="151" height="36" border="0" /></td>
		<td align="right" width="100%" valign="bottom">
			<table border="0" cellspacing="0" class="menutable" style="margin-bottom:3px;">
			  <tr>
			  	<?php
			    	echo '<td><a class="menulink" href="information.php?gruppe='.$gruppe.'">Information</a></td>';
			    	echo '<td><a class="menulink" href="datenbank.php?gruppe='.$gruppe.'">Datenbank</a></td>';
			    ?>
			    <td>&nbsp;&nbsp;</td>
		    	<td><a class="menulink" href="../">Home</a></td>
				</tr>
		 
			</table>
		</td>
	</tr>
	</table>
<br><br>
</div>
<?php } ?>
