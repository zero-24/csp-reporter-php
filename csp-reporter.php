<?php
// Configuration -->
$recipients = ['mail@example.org'];
$subject    = 'CSP Violation on %s';
// <-- Configuration

// Actual Code -->
$inputData = file_get_contents('php://input');
$jsonData  = json_decode($inputData, true);

if ($jsonData)
{
	$mailData = json_encode(
		$jsonData,
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	);

	// Loop over all recipients
	foreach ($recipients as $recipient)
	{
		// Mail the report to the recipient.
		mail($recipient, sprintf($subject, $jsonData['csp-report']['referrer']), $mailData);
	}
}