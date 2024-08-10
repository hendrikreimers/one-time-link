<?php
declare(strict_types=1);

namespace Helper;

use Random\RandomException;

/**
 * Security Helper
 *
 */
class SecurityHelper {

    /**
     * Send NONCE Header and returns the nonce for usage as tag attribute
     *
     * @return string
     * @throws RandomException
     */
    public static function sendAndGetNonce(): string {
        $nonce = self::getNonce();

        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; object-src 'none'; frame-ancestors 'none';", true);

        return $nonce;
    }

    /**
     * Prevents calling the script by a crawler or link preview service
     * Because calling will remove the one time link before the user called it.
     *
     * @return void
     */
    public static function preventCrawlers(): void {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $crawlerList = implode('|', [
            'bot', 'crawl', 'slurp', 'spider', 'mediapartners', 'Googlebot', 'WhatsApp', 'Discordbot', 'TelegramBot',
            'Bingbot', 'Yahoo', 'DuckDuckBot', 'Baiduspider', 'YandexBot', 'Sogou', 'Exabot', 'facebot', 'ia_archiver',
            'Twitterbot', 'Applebot'
        ]);

        if (preg_match('/(' . $crawlerList . ')/i', $userAgent)) {
            header('HTTP/1.0 403 Forbidden');
            exit('Forbidden');
        }
    }

    /**
     * Generate a nonce
     *
     * @return string
     * @throws \Random\RandomException
     */
    public static function getNonce(): string {
        return base64_encode(random_bytes(16));
    }

    /**
     * Send security headers
     *
     * @return void
     */
    public static function sendDefaultHeaders(): void {
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
     * Forces HTTPS
     *
     * @return void
     */
    public static function forceHttps() {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit;
        }
    }

}