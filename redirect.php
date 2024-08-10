<?php
declare(strict_types=1);

// Defaults (Bootstrapping)
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Used classes
use Helper\SecurityHelper;
use Helper\Url;
use Mail\Sendmail;
use Service\ShortUrl;
use Template\SimpleTemplateEngine;
use Validation\FormValidation;

// Extract the short URL from current URL
$shortUrl = ShortUrl::extractShortUrl();
$fileName = __DIR__ . '/data/' . $shortUrl . '.url'; // Prepare the file name

// Initialize Template Engine
$template = new SimpleTemplateEngine();

if (file_exists($fileName)) {
    // Load the data
    $shortUrlData = ShortUrl::getShortUrlData($fileName);
    $targetUrl = $shortUrlData['targetUrl'];
    $notify = (bool)$shortUrlData['notify'];
    $identifier = $shortUrlData['identifier'];

    // Delete file so that the URL can only be called up once
    unlink($fileName);

    // Check targetURL
    FormValidation::urlCorrectOrExit($targetUrl);
    
    // Prepare url as data attribute
    $dataAttrUrl = base64_encode(htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8'));
    
    // Send Nonce Header
    $nonce = SecurityHelper::sendAndGetNonce();

    // Send notification
    if ( $notify ) {
        Sendmail::sendNotification($shortUrl, $targetUrl, $identifier);
    }

    $template->loadTemplate('redirect');
    $template->assignMultiple([
        'BASE_PATH' => Url::getBaseUri(),
        'DATA_ATTR_URL' => $dataAttrUrl,
        'NONCE' => $nonce
    ]);
    echo $template->render();
} else {
    // Error handling
    //     Show not found
    header("HTTP/1.0 404 Not Found");

    // Send rendered template to browser
    echo $template->getTemplate('404');
}
