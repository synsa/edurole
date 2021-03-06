<div class="homecontainer">
	<div class="hl">
		<div class="homeboxl">
			<h2><strong>ONLINE STUDENT REGISTRATION</strong></h2>

			<p>Through the easy online registration form you can now complete your request for admission online. Click on
				the link below to view the studies for which intake is currently open.</p>

			<p><a href="<? echo $this->core->conf['conf']['path']; ?>/intake"><strong> 1. View current intake possibilities for applying students</strong></a></br>
			<a href="<? echo $this->core->conf['conf']['path']; ?>/intake/register"><strong> 2. Registration of existing students</strong></a></p>
		</div>
	</div>

	<form name="login" action="login" method="POST">
		<div class="hr">
			<div class="homeboxr">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td colspan="2" valign="top" style="padding-left:0px;"><h2><strong>LOGIN</strong></h2>

							<p> In case you forgot your account password, click <a href="<?php echo $this->core->conf['conf']['path'] . "/password/recover"; ?>">here</a>.</p></td>
					</tr>
					<tr>
						<td width="124" valign="middle">Username</td>
						<td width="363" valign="top"><input type="text" name="username" class="login" id="username"/>
						</td>
					</tr>
					<tr>
						<td valign="middle">Password</td>
						<td valign="top"><input type="password" name="password" class="login" id="password"/></td>
					</tr>
					<tr>
						<td valign="top">&nbsp;</td>
						<td valign="top"><p>
								<input type="submit" class="login" name="submit" id="submit" value="Login"/>
							</p>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</form>
</div>
</div>
