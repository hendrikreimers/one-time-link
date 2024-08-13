<?php
declare(strict_types=1);

// Defaults (Bootstrapping)
const FRONTEND_CONTEXT = true;
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Used classes
use Helper\ErrorMessageHelper;
use Helper\SecurityHelper;
use Helper\UrlHelper;
use Mail\Sendmail;
use Service\FileService;
use Service\ShortUrlService;
use Template\SimpleTemplateEngine;
use Validation\FormValidation;

// Send Nonce Header
$nonce = SecurityHelper::sendAndGetNonce();

// Initiate classes
$template = new SimpleTemplateEngine(); // Template Engine
$errorMsgHelper = new ErrorMessageHelper($template, $nonce); // Error handling
$formValidation = new FormValidation($errorMsgHelper);

// Extract the short URL from current URL
$shortUrl = ShortUrlService::extractShortFromRequestUri();
$fileName = ShortUrlService::getShortUrlFileName($shortUrl); // Prepare the file name

// First of all, drop old files if in .env set
ShortUrlService::dropRetiredShortUrls();

if (FileService::fileExists($fileName)) {
    // Load the data
    $shortUrlData = ShortUrlService::getShortUrlData($shortUrl);
    $targetUrl = $shortUrlData['targetUrl'];
    $notify = (bool)$shortUrlData['notify'];
    $identifier = $shortUrlData['identifier'];

    // Check targetURL
    $formValidation->urlCorrectOrExit($targetUrl);

    // urlCorrectOrExit must be first, because if someone calls an encrypted ShortURL without the key
    // it could happen, that it will get dropped before being decrypted
    //
    // Delete file so that the URL can only be called up once
    ShortUrlService::removeShortUrl($shortUrl);
    
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
    $errorMsgHelper->sendNotFound();
}
