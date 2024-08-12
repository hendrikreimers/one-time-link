<?php
declare(strict_types=1);

// Defaults (Bootstrapping)
const FRONTEND_CONTEXT = true;
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Used classes
use Helper\SecurityHelper;
use Helper\UrlHelper;
use Mail\Sendmail;
use Service\FileService;
use Service\ShortUrlService;
use Template\SimpleTemplateEngine;
use Validation\FormValidation;

// Extract the short URL from current URL
$shortUrl = ShortUrlService::extractShortFromRequestUri();
$fileName = ShortUrlService::getShortUrlFileName($shortUrl); // Prepare the file name

// Initialize Template Engine
$template = new SimpleTemplateEngine();

// Send Nonce Header
$nonce = SecurityHelper::sendAndGetNonce();

// First of all, drop old files if in .env set
ShortUrlService::dropRetiredShortUrls();

if (FileService::fileExists($fileName)) {
    // Load the data
    $shortUrlData = ShortUrlService::getShortUrlData($shortUrl);
    $targetUrl = $shortUrlData['targetUrl'];
    $notify = (bool)$shortUrlData['notify'];
    $identifier = $shortUrlData['identifier'];

    // Delete file so that the URL can only be called up once
    ShortUrlService::removeShortUrl($shortUrl);

    // Check targetURL
    FormValidation::urlCorrectOrExit($targetUrl);
    
    // Prepare url as data attribute
    $dataAttrUrl = base64_encode(htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8'));

    // Send notification mail
    if ( $notify && Sendmail::isNotifyConfigured() ) {
        Sendmail::sendNotification($shortUrl, $targetUrl, $identifier);
    }

    // Load template and set variables
    $template->loadTemplate('redirect');
    $template->assignMultiple([
        'BASE_PATH' => UrlHelper::getBaseUri(),
        'DATA_ATTR_URL' => $dataAttrUrl,
        'NONCE' => $nonce
    ]);

    // Send rendered template to browser
    echo $template->render();
} else {
    // Error handling
    //     Show not found
    header("HTTP/1.0 404 Not Found");

    $template->loadTemplate('404');
    $template->assignMultiple([
        'BASE_PATH' => UrlHelper::getBaseUri(),
        'NONCE' => $nonce
    ]);

    // Send rendered template to browser
    echo $template->render();
}
