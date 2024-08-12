<?php
declare(strict_types=1);

// Defaults (Bootstrapping)
const FRONTEND_CONTEXT = true;
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Used classes
use Helper\SecurityHelper;
use Helper\UrlHelper;
use Mail\Sendmail;
use Service\ShortUrlService;
use Template\SimpleTemplateEngine;
use Transform\CustomTransform;
use Validation\FormValidation;

// Initialize Template Engine
$template = new SimpleTemplateEngine();

// Send nonce headers
$nonce = SecurityHelper::sendAndGetNonce();

// First of all, drop old files if in .env set
ShortUrlService::dropRetiredShortUrls();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $targetUrl = FormValidation::getFilteredValue('url', FILTER_VALIDATE_URL);
    $notify = FormValidation::getFilteredValue('notify', FILTER_VALIDATE_BOOLEAN);
    $identifier = FormValidation::getFilteredValue('identifier', FILTER_SANITIZE_ENCODED);

    // Second sanitize of identifier string (used for notifications)
    $identifier = FormValidation::additionalSanitize($identifier);

    // Check targetURL
    FormValidation::urlCorrectOrExit($targetUrl);
    
    // Generate short URL
    list($shortUrl, $shortUrlEncryptionPass) = ShortUrlService::generateShortUrl();
    
    // Custom transformations
    $shortUrl = CustomTransform::customTransformShortUrl($shortUrl);
    $targetUrl = CustomTransform::customTransformTargetUrl($targetUrl);
    
    // Save the shortURL with the targetURL
    ShortUrlService::saveUrl($shortUrl, $targetUrl, $shortUrlEncryptionPass, (bool)$notify, $identifier);

    // Load template and set variables
    $template->loadTemplate('create-result');
    $template->assignMultiple([
        'BASE_PATH' => UrlHelper::getBaseUri(),
        'BASE_URL' => UrlHelper::getServerUrl(),
        'SHORT_URL' => ShortUrlService::concatenateShortUrlAndPassword($shortUrl, $shortUrlEncryptionPass),
        'NONCE' => $nonce
    ]);
} else {
    // Shows created ShortURL
    //     Load template, assign variables and render it
    $template->loadTemplate('create');
    $template->assignMultiple([
        'BASE_PATH' => UrlHelper::getBaseUri(),
        'NONCE' => $nonce,
        'ENABLE_NOTIFY' => ( Sendmail::isNotifyConfigured() ) ? 'enabled' : 'disabled'
    ]);
}

// Render template and send output
echo $template->render();