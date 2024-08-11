<?php
declare(strict_types=1);

namespace Service;

use Random\RandomException;

/**
 * ShortURL Service
 *
 */
class ShortUrlService {

    protected static string $fileSuffix = '.url';

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
        $fileName = trim($shortUrl) . self::$fileSuffix;

        $data = json_encode([
            'targetUrl' => $targetUrl,
            'notify' => $notify,
            'identifier' => $identifier
        ]);

        FileService::writeFile($fileName, trim($data));
    }

    /**
     * Loads data for a ShortURL saved in file.
     * If it's the old format creates a data array.
     *
     * @param string $shortUrl
     * @return array
     */
    public static function getShortUrlData(string $shortUrl): array {
        $data = FileService::getContents($shortUrl . self::$fileSuffix);

        if ( json_validate($data) ) {
            $result = json_decode($data, true);
        } else $result = [
            'targetUrl' => $data,
            'notify' => false,
            'identifier' => ''
        ];

        return $result;
    }

    /**
     * Returns the full path of the shortURL Data file
     *
     * @param string $shortUrl
     * @return string
     */
    public static function getShortUrlFileName(string $shortUrl): string {
        return trim($shortUrl) . self::$fileSuffix;
    }

    /**
     * Deletes the shortURL Data file
     *
     * @param string $shortUrl
     * @return void
     */
    public static function removeShortUrl(string $shortUrl): void {
        FileService::deleteFile(trim($shortUrl) . self::$fileSuffix);
    }

    /**
     * Removes unused ShortURLs after given number of days, set in .env file
     *
     * @return void
     */
    public static function dropRetiredShortUrls(): void {
        if ( !defined('DELETE_UNUSED_SHORTURLS_AFTER_DAYS') ) {
            return;
        }

        $oldShortUrls = FileService::getOldFiles(
            FileService::getDataPath(false),
            '*' . self::$fileSuffix,
            intVal(DELETE_UNUSED_SHORTURLS_AFTER_DAYS),
            true
        );

        if ( count($oldShortUrls) <= 0 ) return;

        foreach ( $oldShortUrls as $shortUrlFile ) {
            FileService::deleteFile($shortUrlFile);
        }
    }

}