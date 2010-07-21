<?php
	$PageTitle = "Contact BJ &amp; Angelee";
	$WhichBlogMenu = $_GET['t'] == 'engl' ? "UVSC" : "";
	$CustomStyle = "";
	$CustomScript = "
	<script type=\"text/javascript\">
		function checkForm(form) {
			var email = form.elements['email'];
			var emailCheck = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+\$/;
			if (!email.value) {
				alert('Please specify an Email Address we can contact you at. (ex. \'user@domain.com\')');
				email.focus();
				return false;
			}
			if (!emailCheck.test(email.value) || email.value == 'user@domain.com') {
				alert('Please specify a valid Email Address. (ex. \'user@domain.com\')');
				email.select();
				return false;
			}
			return true;
		}
	</script>\n";
	include_once('publicHeader.php');
?>
		<div>
<?php
//if ($_POST['submit']) {
if (false) {

	$name = $_POST['name'] != '' ? $_POST['name'] : 'somebody';
	$email = $_POST['email'];
	$subject = $_POST['subject'] != '' ? $_POST['subject'] : "New Message from " . $name;

	$body = "Name:    " . $name . "\n".
			"Email:   " . $email . "\n".
			"Date:    " . date('n.j.Y') . "\n";
	$body .= "\nComments\n".
			"--------\n".
			$_POST['comments'];
	$to = $_POST['WhichBlogMenu'] == 'UVSC' ? 'bj.neilsen@gmail.com' : 'bj.neilsen@gmail.com,angelee.neilsen@gmail.com';
	$headers = 	"From: contact@maryndesign.com\r\n".
							"Reply-To: $email";

	if(mail($to,$subject,stripslashes($body),$headers)) {
		print "
			<p class=\"Message\">Message sent to " . ($_POST['WhichBlogMenu'] == 'UVSC' ? 'BJ' : "the Neilsen's") . ".</p>
			<p>[ <a href=\"/" . ($_POST['WhichBlogMenu'] == 'UVSC' ? 'blog/' : '') . "\" title=\"Go to the Home Page\">Go to the Home Page</a> ]</p>\n";
	}
	else {
		print "\t\t\t<p class=\"errorMessage\">Message could not be sent due to an Internal Error. Please try again later.</p>\n";
	}
}
else {
?>
			<div class="clearfix">
				<p>Why in the world would anyone want to contact <?= ($WhichBlogMenu == 'UVSC' ? 'me' : 'us'); ?>? Because <?= ($WhichBlogMenu == 'UVSC' ? 'I\'m' : 'we\'re'); ?> neat... that's why.</p>
				<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>" onsubmit="return checkForm(this);">
				<div style="display: none;"><input type="hidden" name="WhichBlogMenu" value="<?= $WhichBlogMenu; ?>" /></div>
				<div class="row">
					<span class="label">Name:</span>
					<span class="val"><input type="text" name="name" size="30" /></span>
				</div>
				<div class="row">
					<span class="label"><span style="color: red;">*</span> Email:</span>
					<span class="val"><input type="text" name="email" value="user@domain.com" onclick="if (this.value == 'user@domain.com') this.value = '';" onblur="if (this.value == '') this.value = 'user@domain.com';" size="30" /></span>
				</div>
				<div class="row">
					<span class="label">Subject:</span>
					<span class="val"><input type="text" name="subject" size="30" /></span>
				</div>
				<div class="row">
					<span class="label">So... what did you want to say?:</span>
					<span class="val"><textarea name="comments" rows="6" cols="23"></textarea></span>
				</div>
				<div class="row">
					<span class="label">&nbsp;</span>
					<span class="val smalltext" style="color: red;">* indicates required field</span>
				</div>
				<div class="row">
					<span class="label">&nbsp;</span>
					<span class="val"><input type="submit" name="submit" value="Cyberize It!" /> <input type="reset" value="Clear Form" /></span>
				</div>
				</form>
			</div>

<?php
}
?>
		</div>
<?php include_once('publicFooter.php'); ?>
