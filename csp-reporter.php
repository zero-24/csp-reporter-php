<?php
// Configuration -->
$recipients = [
	'csp@example.org',
];

$subject = 'CSP Violation on %s';

// Blacklist of domains to not report to you.
$blacklist = [
	'img-src' => [
	],
	'style-src' => [
	],
	'script-src' => [
	],
	'connect-src' => [
	],
	'font-src' => [
	],
	'default-src' => [
	],
	'frame-src' => [
	],
	'extensions' => [
		'safari-extension',
		'chrome-extension',
		'moz-extension://',
	],
	'document-uri' => [
		'about',
		'about:blank',
	],
	'top-level-domain' => [
	],
];
// <-- Configuration

// Actual Code -->
$inputData = file_get_contents('php://input');
$jsonData  = json_decode($inputData, true);

if (!is_array($jsonData))
{
	exit;
}

// Try to get the ReportSource value
$reportSource = 'The ReportSource could not be detected';

if (isset($_GET['source']))
{
	$reportSource = strip_tags($_GET['source']);
}

// Detect violated-directive 
$explode           = explode(' ', $jsonData['csp-report']['violated-directive']);
$violatedDirective = $explode[0] ? $explode[0] : 'none';
$blockedUri        = $jsonData['csp-report']['blocked-uri'];
$blockedUri        = str_replace('https://www.', '', $blockedUri);
$blockedUri        = str_replace('http://www.', '', $blockedUri);
$blockedUri        = str_replace('https://', '', $blockedUri);
$blockedUri        = str_replace('http://', '', $blockedUri);
$blockeddomain     = explode('/', $blockedUri);
$ip                = explode(':', $blockeddomain[0]);

// Block broken document-uri's
if (in_array($jsonData['csp-report']['document-uri'], $blacklist['document-uri']))
{
	exit;
}

// Some Browser Plugin missuse our csp by settings
if (substr($jsonData['csp-report']['violated-directive'], 0, 17) === "script-src 'none'"
	|| substr($jsonData['csp-report']['violated-directive'], 0, 17) === "object-src 'none'"
	|| substr($jsonData['csp-report']['violated-directive'], 0, 14) === "img-src 'none'"
	|| substr($jsonData['csp-report']['original-policy'], 0, 17) === "script-src 'none'"
	|| substr($jsonData['csp-report']['original-policy'], 0, 17) === "object-src 'none'"
	|| substr($jsonData['csp-report']['original-policy'], 0, 14) === "img-src 'none'")
{
	exit;
}

// Return in case we have a IP as this is invalid anyway
if (filter_var($ip[0], FILTER_VALIDATE_IP) !== false)
{
	exit;
}

// Catch the just another subdomain case here.
foreach ($blacklist[$violatedDirective] as $blacklistedUri)
{
	if (strpos($blockeddomain[0], $blacklistedUri))
	{
		exit;
	}
}

// Block by top-level-domain
foreach ($blacklist['top-level-domain'] as $blacklistedTopLevelDomain)
{
	if (substr($blockeddomain[0], -strlen($blacklistedTopLevelDomain)) === $blacklistedTopLevelDomain)
	{
		exit;
	}
}

// Block reports form services that don't handle the 'self' source correct and block your domain itself.
if ($blockeddomain[0] === $reportSource)
{
	exit;
}

// Check that the current report is not on the blacklist for sending mails else send mail
if (!in_array($blockeddomain[0], $blacklist[$violatedDirective]) && !in_array(substr($jsonData['csp-report']['blocked-uri'], 0, 16), $blacklist['extensions']))
{
	$mailData = json_encode(
		$jsonData,
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	);

	// Add UserAgent, Blocked Domain and Blocked Uri value String
	$mailData .= "\n\n" . 'UserAgent: ' . $_SERVER['HTTP_USER_AGENT'];
	$mailData .= "\n\n" . 'Violated Directive: ' . $violatedDirective;
	$mailData .= "\n\n" . 'Blocked Domain: ' . $blockeddomain[0];
	$mailData .= "\n\n" . 'Blocked Uri: ' . $jsonData['csp-report']['blocked-uri'];
	$mailData .= "\n\n" . 'ReportSource: ' . $reportSource;

	// Loop over all recipients
	foreach ($recipients as $recipient)
	{
		// Mail the report to the recipient.
		mail($recipient, sprintf($subject, $reportSource), $mailData);
	}
}