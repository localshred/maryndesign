<?php
	$PageTitle = "MySchmoopie.com | Error";
	include_once('publicHeader.php');
	$error = ($_SERVER['REDIRECT_STATUS'] ? $_SERVER['REDIRECT_STATUS'] : $_GET['error']);
	$arrErrors = array(
		'400' => 'Error 400: Bad Request',
		'401' => 'Error 401: Authorization Required',
		'402' => 'Error 402: Payment Required (not used yet)',
		'403' => 'Error 403: Forbidden',
		'404' => 'Error 404: Document Not Found',
		'405' => 'Error 405: Method Not Allowed',
		'406' => 'Error 406: Not Acceptable (encoding)',
		'407' => 'Error 407: Proxy Authentication Required',
		'408' => 'Error 408: Request Timed Out',
		'409' => 'Error 409: Conflicting Request',
		'410' => 'Error 410: Resource Gone',
		'411' => 'Error 411: Content Length Required',
		'412' => 'Error 412: Precondition Failed',
		'413' => 'Error 413: Request Entity Too Long',
		'414' => 'Error 414: Request URI Too Long',
		'415' => 'Error 415: Unsupported Media Type',
		'500' => 'Error 500: Internal Server Error',
		'501' => 'Error 501: Not Implemented',
		'502' => 'Error 502: Bad Gateway',
		'503' => 'Error 503: Service Unavailable',
		'504' => 'Error 504: Gateway Timeout',
		'505' => 'Error 505: HTTP Version Not Supported',
		'other' => 'An unknown error has occured. The Page you have requested cannot be found.'
	);
?>

		<div>
			<p class="Message">
				We're Sorry, but there was a problem loading the page you requested<br />
				<p class="errorMessage"><?php if ($error != 'other' || $error != '') print $arrErrors[$error]; else print $arrErrors['other']; ?></p>
				You may try refreshing the browser, and if that does not work, please <a href="/contact.php?type=webmaster" title="Contact us through our online form">Contact Us</a> if you believe this error has been a mistake.
			</p>
			<p>[ <a href="/index.php" title="Go to our Home Page">Home</a> ]</p>
		</div>

<?php include_once('publicFooter.php'); ?>