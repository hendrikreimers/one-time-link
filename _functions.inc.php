<?php
declare(strict_types=1);

/**
 * Forces HTTPS
 *
 * @return void
 */
function forceHttps() {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}

/**
 * Generate a short url
 *
 * @return string
 * @throws \Random\RandomException
 */
function generateShortUrl(): string {
    return bin2hex(random_bytes(4)); // Generates a random URL 8 characters long
}

/**
 * Extract the shortURL name from the request URI
 *
 * @return string
 */
function extractShortUrl(): string {
    return basename(preg_replace('/^.*\/([a-z0-9]{1,})$/i', '\\1', $_SERVER['REQUEST_URI']));
}

/**
 * Saves the URL to file based on the short url name
 *
 * @param string $shortUrl
 * @param string $targetUrl
 * @return void
 */
function saveUrl(string $shortUrl, string $targetUrl): void {
    $fileName = __DIR__ . '/data/' . $shortUrl . '.url';
    file_put_contents($fileName, trim($targetUrl));
}

/**
 * Determines the ServerURL
 *
 * @return string
 */
function getServerUrl(): string {
    // Determine the protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

    // Determine the host name, use SERVER_NAME as fallback
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];

    // Determine the URI path and remove the script name
    $uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';

    // Prepare the full url
    $directoryUrl = "$protocol://$host$uri";

    // Remove the last slash
    if (substr($directoryUrl, -1) === '/') $directoryUrl = substr($directoryUrl, 0, -1);

    // Deliver result
    return $directoryUrl;
}

/**
 * Generate a nonce
 *
 * @return string
 * @throws \Random\RandomException
 */
function getNonce(): string {
    return base64_encode(random_bytes(16));
}

/**
 * Send security headers
 *
 * @return void
 */
function sendDefaultHeaders(): void {
    // No-Cache Header
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
    header('Cache-Control: post-check=0, pre-check=0', true);
    header('Pragma: no-cache', true);

    // Security-relevant headers
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload, true'); // HSTS
    header('X-Content-Type-Options: nosniff', true); // Prevents MIME sniffing
    header('X-Frame-Options: DENY', true); // Prevents clickjacking
    header('X-XSS-Protection: 1; mode=block', true); // Activates XSS protection
    header('Referrer-Policy: no-referrer', true); // Controls referrer header
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; frame-ancestors 'none';", true); // CSP
    
    // No crawler
    header('X-Robots-Tag: noindex, nofollow', true);
}

/**
 * Send NONCE Header and returns the nonce for usage as tag attribute
 *
 * @return string
 */
function sendAndGetNonce(): string {
    $nonce = getNonce();
    
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; object-src 'none'; frame-ancestors 'none';", true);
    
    return $nonce;
}

/**
 * Prevents calling the script by a crawler or link preview service
 * Because calling will remove the one time link before the user called it.
 *
 * @return void
 */
function preventCrawlers(): void {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    $crawlerList = implode('|', [
        'bot',
        'crawl',
        'slurp',
        'spider',
        'mediapartners',
        'Googlebot',
        'WhatsApp',
        'Discordbot',
        'TelegramBot',
        'Bingbot',
        'Yahoo',
        'DuckDuckBot',
        'Baiduspider',
        'YandexBot',
        'Sogou',
        'Exabot',
        'facebot',
        'ia_archiver',
        'Twitterbot',
        'Applebot'
    ]);
    
    if (preg_match('/(' . $crawlerList . ')/i', $userAgent)) {
        header('HTTP/1.0 403 Forbidden');
        exit('Forbidden');
    }
}

/**
 * Loads enviroment file (.env)
 *
 * @return bool
 */
function loadDotEnv(): bool {
    // Path to .env file
    $envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';

    // Do something if .env exists
    if ( file_exists($envFile)) {
        $envContent = file_get_contents($envFile);

        // Parse .env file
        //
        // Example content:
        //
        //   TRANSFORM_TARGET_EXPR="\/\/your-domain.com\/"
        //   TRANSFORM_TARGET_SEARCH="/a-folder/"
        //   TRANSFORM_TARGET_REPLACE="/a-folder/subfolder/"
        //
        //   TRANSFORM_SHORT_EXPR="\/a-folder\/"
        //   TRANSFORM_SHORT_SEARCH=/a-folder/
        //   TRANSFORM_SHORT_REPLACE=/a-folder/subfolder/
        //
        if (preg_match_all('/([A-Z_]+)="?(.*[^"\r\n])"?/m', $envContent, $matches)) {
            foreach ($matches[1] as $index => $key) {
                define(strtoupper($key), $matches[2][$index]);
            }
        }

        return true;
    }

    return false;
}