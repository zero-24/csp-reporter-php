<?php
// Configuration -->
$recipients = ['mail@example.org'];
$subject    = 'CSP Violation on %s';

// Blacklist of files to not report to you.
$blacklist = [
	// There is a chrome bug with the inbuild translation: https://stackoverflow.com/questions/41052219/content-security-policy-translate-googleapis-com
	'https://www.gstatic.com/images/branding/product/2x/translate_24dp.png',
	'https://translate.googleapis.com/translate_static/css/translateelement.css',
];
// <-- Configuration

// Actual Code -->
$inputData = file_get_contents('php://input');
$jsonData  = json_decode($inputData, true);

if ($jsonData && !in_array($jsonData['csp-report']['blocked-uri'], $blacklist))
{
	$mailData = json_encode(
		$jsonData,
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	);

	$website = ($jsonData['csp-report']['document-uri'] ? $jsonData['csp-report']['document-uri'] : 'Unknown Website');

	// Loop over all recipients
	foreach ($recipients as $recipient)
	{
		// Mail the report to the recipient.
		mail($recipient, sprintf($subject, $website), $mailData);
	}
}