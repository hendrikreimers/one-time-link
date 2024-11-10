<?php
declare(strict_types=1);

// Defaults (Bootstrapping)
const FRONTEND_CONTEXT = true;
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Used classes
use Domain\Model\ShortUrl;
use Helper\ErrorMessageHelper;
use Helper\SecurityHelper;
use Helper\UrlHelper;
use Mail\Sendmail;
use Service\ShortUrlService;
use Template\SimpleTemplateEngine;
use Transform\CustomTransform;
use Validation\FormValidation;

// Send Nonce Header
$nonce = SecurityHelper::sendAndGetNonce();

// Initiate classes
$template = new SimpleTemplateEngine(); // Template Engine
$errorMsgHelper = new ErrorMessageHelper($template, $nonce); // Error handling
$formValidation = new FormValidation($errorMsgHelper);

// First of all, drop old files if in .env set
ShortUrlService::dropRetiredShortUrls();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * Shows created ShortURL
     */

    // Validate input
    $targetUrl = (string)$formValidation->getFilteredValue('url', FILTER_VALIDATE_URL);
    $notify = (bool)$formValidation->getFilteredValue('notify', FILTER_VALIDATE_BOOLEAN);
    $identifier = (string)$formValidation->getFilteredValue('identifier', FILTER_SANITIZE_ENCODED);
    $maxNumViews = (int)$formValidation->getFilteredValue('maxNumViews', FILTER_VALIDATE_INT) ?: 1;

    // Limit maxNumViews
    $maxNumViews = max(min($maxNumViews, 10), 1); // 1..10

    // Second sanitize of identifier string (used for notifications)
    $identifier = $formValidation->additionalSanitize($identifier);

    // Check targetURL
    $formValidation->urlCorrectOrExit($targetUrl);
    
    // Generate short URL
    list($shortUrl, $shortUrlEncryptionPass) = ShortUrlService::generateShortUrl();
    $shortUrlObj = new ShortUrl($shortUrl, $targetUrl, $notify, $identifier, $maxNumViews);

    // Custom transformations
    $shortUrl = CustomTransform::customTransformShortUrl($shortUrl);
    $targetUrl = CustomTransform::customTransformTargetUrl($targetUrl);

    // Update to modified URLs
    $shortUrlObj->setShortUrl($shortUrl);
    $shortUrlObj->setTargetUrl($targetUrl);
    
    // Save the shortURL with the targetURL
    ShortUrlService::saveUrl($shortUrlObj, $shortUrlEncryptionPass);

    // Send creation notification mail (if configured in .env)
    Sendmail::sendCreateNotification(
        $shortUrlObj->getShortUrl(),
        $shortUrlObj->getTargetUrl(),
        $shortUrlObj->getIdentifier()
    );

    // Load template and set variables
    $template->loadTemplate('create-result');
    $template->assignMultiple([
        'BASE_PATH' => UrlHelper::getBaseUri(),
        'BASE_URL' => UrlHelper::getServerUrl(),
        'SHORT_URL' => ShortUrlService::concatenateShortUrlAndPassword($shortUrl, $shortUrlEncryptionPass),
        'NONCE' => $nonce
    ]);
} else {
    /**
     * Shows creation form
     */

    // Load template, assign variables and render it
    $template->loadTemplate('create');
    $template->assignMultiple([
        'BASE_PATH' => UrlHelper::getBaseUri(),
        'NONCE' => $nonce,
        'ENABLE_NOTIFY' => ( Sendmail::isNotifyConfigured() ) ? 'enabled' : 'disabled'
    ]);
}

// Render template and send output
echo $template->render();