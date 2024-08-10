<?php
declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('_functions.inc.php');
if ( file_exists('_custom.inc.php') ) require_once('_custom.inc.php');

// Loads .env file constants
loadDotEnv();

// Force HTTPS
forceHttps();

// Send security header
sendDefaultHeaders();

// Disallow crawler and link previews
preventCrawlers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $targetUrl = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
    $notify = filter_input(INPUT_POST, 'notify', FILTER_VALIDATE_BOOLEAN);
    $identifier = filter_input(INPUT_POST, 'identifier', FILTER_SANITIZE_ENCODED);

    // Second sanitize of identifier string (used for notifications)
    if ( $identifier ) {
        $identifier = htmlspecialchars(strip_tags($identifier));
    } else $identifier = '';

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
    saveUrl($shortUrl, $targetUrl, (bool)$notify, $identifier);

    // Get Base-URL
    $baseUrl = getServerUrl();
}

// Send nonce headers
$nonce = sendAndGetNonce();

?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="cache-control" content="max-age=0" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta http-equiv="expires" content="0" />
  <meta http-equiv="expires" content="Sat, 01 Jan 2000 1:00:00 GMT" />
  <meta http-equiv="pragma" content="no-cache" />
  <link rel="stylesheet" type="text/css" href="style.css" nonce="<?= $nonce ?>">
</head>
<body>
<?php if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ?>
  <form method="post">
    <div class="field">
      <label for="url">Target URL:</label>
      <input type="url" name="url" id="url" required>
    </div>
    <div class="field">
      <label for="notify">Notify:</label>
      <input type="checkbox" name="notify" id="notify">
    </div>
    <div class="field">
      <label for="identifier">Notify Name:</label>
      <input type="text" name="identifier" id="identifier">
    </div>
    <div class="field">
      <button type="submit">Create</button>
    </div>
  </form>
<?php } else { ?>
  <div class="field">
    Your one time shortURL: <input type="text" readonly value="<?= $baseUrl . '/' . $shortUrl ?>" id="shorturl" />
  </div>
  <div class="field">
      <a href="create.php">Create another one</a>
  </div>
  <script src="script.js" id="script" nonce="<?= $nonce ?>"></script>
<?php } ?>
</body>
</html>