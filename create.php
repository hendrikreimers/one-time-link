<?php
declare(strict_types=1);

require_once('_functions.inc.php');
if ( file_exists('_custom.inc.php') ) require_once('_custom.inc.php');

// Force HTTPS
forceHttps();

// Send security header
sendDefaultHeaders();

// Disallow crawler and link previews
preventCrawlers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Validate input
    $targetUrl = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);

	// Show error
    if ($targetUrl === false) {
        echo "Wrong URL";
        exit;
    }

    // Check whether the URL begins with http:// or https://
    if (!preg_match('/^https?:\/\/.+/', $targetUrl)) {
        echo "URL must start with http:// or https://";
        exit;
    }

    // Further validation by URL parsing and checking specific host names or TLDs
    $parsedUrl = parse_url($targetUrl);
    if (!isset($parsedUrl['host']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
        echo "Wrong URL";
        exit;
    }
	
    // Generate short URL
    $shortUrl = generateShortUrl();
	
	// Custom transformations
	if ( function_exists('transformShortUrl') ) {
		$shortUrl = transformShortUrl($shortUrl);
	}
	if ( function_exists('transformTargetUrl') ) {
		$targetUrl = transformTargetUrl($targetUrl);
	}
	
	// Save the shortURL with the targetURL
    saveUrl($shortUrl, $targetUrl);

    // Get Base-URL
    $baseUrl = getServerUrl();
	
	// Send nonce headers
	$nonce = sendAndGetNonce();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="cache-control" content="max-age=0" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta http-equiv="expires" content="0" />
  <meta http-equiv="expires" content="Sat, 01 Jan 2000 1:00:00 GMT" />
  <meta http-equiv="pragma" content="no-cache" />
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<?php if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ?>
  <form method="post">
    <input type="url" name="url" required>
    <button type="submit">Erstellen</button>
  </form>
<?php } else { ?>
  <div>
    Your one time shortURL: <input type="text" readonly value="<?= $baseUrl . '/' . $shortUrl ?>" id="shorturl" />
  </div>
  <script src="script.js" id="script" nonce="<?= $nonce; ?>"></script>
<?php } ?>
</body>
</html>