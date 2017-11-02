<?php
// Configuration -->
$recipients = ['mail@example.org'];
$subject    = 'CSP Violation on %s';

// Blacklist of files to not report to you.
$blacklist = [
	// There is a chrome bug with the inbuild translation: https://stackoverflow.com/questions/41052219/content-security-policy-translate-googleapis-com
	'img-src' => ['https://www.gstatic.com/images/branding/product/2x/translate_24dp.png'],
	'style-src' => ['https://translate.googleapis.com/translate_static/css/translateelement.css'],
	// Looks like there is a Skype extension that cause this: https://github.com/nico3333fr/CSP-useful/blob/master/csp-wtf/explained.md#skype-assets
	'font-src' => [
		'https://sxt.cdn.skype.com/assets/fonts/SkypeAssets-Light.ttf',
		'https://sxt.cdn.skype.com/assets/fonts/SkypeAssets-Regular.ttf',
		'https://sxt.cdn.skype.com/assets/fonts/SkypeAssets-Light.woff',
		'https://sxt.cdn.skype.com/assets/fonts/SkypeAssets-Regular.woff',
	],
	// Looks like there are some broken extensions that got blocked
	'extensions' => [
		'safari-extension',
	],
];
// <-- Configuration

// Actual Code -->
$inputData = file_get_contents('php://input');
$jsonData  = json_decode($inputData, true);

// Detect violated-directive 
$explode           = explode(' ', $jsonData['csp-report']['violated-directive']);
$violatedDirective = $explode[0] ? $explode[0] : 'none';
$blockedUri        = $jsonData['csp-report']['blocked-uri'];

// Check that the current report is not on the blacklist for sending mails else send mail
if (!in_array($blockedUri, $blacklist[$violatedDirective]) && !in_array(substr($blockedUri, 0, 16), $blacklist['extensions']))
{
	$mailData = json_encode(
		$jsonData,
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	);

	// Add UserAgent String
	$mailData .= "\n\n" . 'UserAgent: ' . $_SERVER['HTTP_USER_AGENT'];
	
	$website = ($jsonData['csp-report']['document-uri'] ? $jsonData['csp-report']['document-uri'] : 'Unknown Website');

	// Loop over all recipients
	foreach ($recipients as $recipient)
	{
		// Mail the report to the recipient.
		mail($recipient, sprintf($subject, $website), $mailData);
	}
}