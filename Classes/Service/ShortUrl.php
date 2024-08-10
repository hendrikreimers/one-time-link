<?php
declare(strict_types=1);

namespace Service;

use Helper\General;
use Random\RandomException;

/**
 * ShortURL Service
 *
 */
class ShortUrl {

    /**
     * Generate a short url
     *
     * @return string
     * @throws RandomException
     */
    public static function generateShortUrl(): string {
        return bin2hex(random_bytes(4)); // Generates a random URL 8 characters long
    }

    /**
     * Extract the shortURL name from the request URI
     *
     * @return string
     */
    public static function extractShortUrl(): string {
        return basename(preg_replace('/^.*\/([a-z0-9]{1,})$/i', '\\1', $_SERVER['REQUEST_URI']));
    }

    /**
     * Saves the URL to file based on the short url name
     *
     * @param string $shortUrl
     * @param string $targetUrl
     * @return void
     */
    public static function saveUrl(string $shortUrl, string $targetUrl, bool $notify = false, string $identifier = ''): void {
        $fileName = General::getCallerPath() . '/data/' . trim($shortUrl) . '.url';

        $data = json_encode([
            'targetUrl' => $targetUrl,
            'notify' => $notify,
            'identifier' => $identifier
        ]);

        file_put_contents($fileName, trim($data));
    }

    /**
     * Loads data for a ShortURL saved in file.
     * If it's the old format creates a data array.
     *
     * @param string $fileName
     * @return array
     */
    public static function getShortUrlData(string $fileName): array {
        $data = file_get_contents($fileName);

        if ( json_validate($data) ) {
            $result = json_decode($data, true);
        } else $result = [
            'targetUrl' => $data,
            'notify' => false,
            'identifier' => ''
        ];

        return $result;
    }

}