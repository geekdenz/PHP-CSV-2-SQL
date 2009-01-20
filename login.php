<?php
/*
 * Created on 30/09/2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
<p>
<form name="login" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<table cellpadding="0" cellspacing="10" border="0">
		<tr>
			<td align="left" colspan="2">
				<input type="text" id="user" name="user" value=" Username " maxlength="32" size="20" 
				onfocus="
				  if (document.getElementById('user').value == ' Username ') document.getElementById('user').value = '';
				"
				onblur="
				  if (document.getElementById('user').value == '') document.getElementById('user').value = ' Username ';
				" />
			</td>
		</tr>
		<tr>
			<td align="left">
				<input type="password" id="password" name="password" value=" Password " maxlength="32" size="20"
				onfocus="
				  if (document.getElementById('password').value == ' Password ') document.getElementById('password').value = '';
				"
				onblur="
				  if (document.getElementById('password').value == '') document.getElementById('password').value = ' Password ';
				" />
			</td>
			<td>
				<input type="submit" name="login" value="Login" />
			</td>
		</tr>
	</table>
</form>
<br />
</p>
