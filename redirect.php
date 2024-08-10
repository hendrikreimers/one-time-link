<?php
declare(strict_types=1);

require_once('_functions.inc.php');

// Force HTTPS
forceHttps();

// Send security header
sendDefaultHeaders();

// Disallow crawler and link previews
preventCrawlers();

// Extract the short URL from current URL
$shortUrl = extractShortUrl();
$fileName = __DIR__ . '/data/' . $shortUrl . '.url'; // Prepare the file name

if (file_exists($fileName)) {
    $targetUrl = file_get_contents($fileName);
    unlink($fileName); // Delete file so that the URL can only be called up once
	
    // Check whether the URL begins with http:// or https://
    if (!preg_match('/^https?:\/\/.+/', $targetUrl)) {
        echo "URL must start with http:// or https://";
        exit;
    }

    // Optional further validation: Parsing the URL and checking certain host names or TLDs
    $parsedUrl = parse_url($targetUrl);
    if (!isset($parsedUrl['host']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
        echo "Invalid URL";
        exit;
    }
	
	// Prepare url as data attribute
	$dataAttrUrl = base64_encode(htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8'));
	
	// Send Nonce Header
	$nonce = sendAndGetNonce();
} else {
	// Show not found
    header("HTTP/1.0 404 Not Found");
    echo implode("\n", [
		'<!DOCTYPE html>',
		'<html id="redirect" lang="en-EN">',
		'<head>',
		'  <meta http-equiv="cache-control" content="max-age=0" />',
		'  <meta http-equiv="cache-control" content="no-cache" />',
		'  <meta http-equiv="expires" content="0" />',
		'  <meta http-equiv="expires" content="Sat, 01 Jan 2000 1:00:00 GMT" />',
		'  <meta http-equiv="pragma" content="no-cache" />',
		'  <link rel="stylesheet" type="text/css" href="style.css">',
		'</head>',
		'<body>',
		'  <h1>404 Not found</h1>',
		'</body>',
		'</html>'
	]);
    exit;
}
?>
<!DOCTYPE html>
<html id="redirect">
<head>
  <meta http-equiv="cache-control" content="max-age=0" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta http-equiv="expires" content="0" />
  <meta http-equiv="expires" content="Sat, 01 Jan 2000 1:00:00 GMT" />
  <meta http-equiv="pragma" content="no-cache" />
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  Redirecting...
  <script src="script.js" id="script" data-check="<?= $dataAttrUrl; ?>" nonce="<?= $nonce ?>"></script>
  <noscript>
	<p>PLEASE ENABLE JAVASCRIPT</p>
  </noscript>
</body>
</html>