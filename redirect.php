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

// First of all, drop old files if in .env set (only based on file creation and modification date at this point)
ShortUrlService::dropRetiredShortUrls();

if (FileService::fileExists($fileName)) {
    // Load the data
    $shortUrlData = ShortUrlService::getShortUrlData($shortUrl);

    // urlCorrectOrExit must be first, because if someone calls an encrypted ShortURL without the key
    // it could happen, that it will get dropped before being decrypted
    //
    // Check targetURL
    $formValidation->urlCorrectOrExit($shortUrlData->getTargetUrl());

    // Check if isValid or should be dropped (based on internal timestamp now)
    if ( defined('DELETE_UNUSED_SHORTURLS_AFTER_DAYS') ) {
        $unusedAfterInSeconds = DELETE_UNUSED_SHORTURLS_AFTER_DAYS * 24 * 60 * 60;
        $retirementTime = $shortUrlData->getTimestamp() + $unusedAfterInSeconds;

        // File expired due timestamp
        if ( $retirementTime < time() ) {
            ShortUrlService::removeShortUrl($shortUrl);
            $errorMsgHelper->sendNotFound();
        }
    }

    // Lower the left views counter
    $shortUrlData->setNumViewsLeft($shortUrlData->getNumViewsLeft()- 1);

    // This should never happen (but who knows)
    if ( $shortUrlData->getNumViewsLeft() < 0 ) {
        ShortUrlService::removeShortUrl($shortUrl);
        $errorMsgHelper->sendNotFound();
    }

    // Max views reached, drop the file
    // or lower the counter and rewrite it
    if ( $shortUrlData->getNumViewsLeft() === 0 ) {
        ShortUrlService::removeShortUrl($shortUrl);
    } else { // Set new counter and write new state
        // Extract password for recreation
        $password = ShortUrlService::getShortUrlPassword($shortUrl);

        // Rewrite file with new count
        FileService::deleteFile($fileName);
        ShortUrlService::saveUrl($shortUrlData, $password);
    }
    
    // Prepare url as data attribute for template
    $dataAttrUrl = base64_encode(htmlspecialchars($shortUrlData->getTargetUrl(), ENT_QUOTES, 'UTF-8'));

    // Send notification mail
    if ( $shortUrlData->isNotify() && Sendmail::isNotifyConfigured() ) {
        Sendmail::sendNotification(
            $shortUrlData->getShortUrl(),
            $shortUrlData->getTargetUrl(),
            $shortUrlData->getIdentifier()
        );
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
