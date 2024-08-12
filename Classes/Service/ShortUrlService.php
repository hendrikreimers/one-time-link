<?php
declare(strict_types=1);

namespace Service;

use Exception;
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
        // Initialize basic variables
        $maxRetries = 10;
        $numRetries = 0;
        $shortUrlFound = false;

        // Define the max fileName length (1 byte = 2 chars, 4 bytes = 8 chars)
        $numBytes = ( defined('SHORTURL_FILENAME_MAXBYTES') ) ? SHORTURL_FILENAME_MAXBYTES : 4;

        // Return initial value
        $shortUrlName = '';

        // Try to find a unique filename
        while ( $numRetries < $maxRetries && $shortUrlFound === null ) {
            $numRetries++;

            // Generates a random URL 8 characters long
            $shortUrlName = bin2hex(random_bytes($numBytes));
            $fileName = $shortUrlName . self::$fileSuffix;

            // Check if file exists
            if ( !FileService::fileExists($fileName) ) {
                $shortUrlFound = true;
            }
        }

        // Max retries reached and no filename found error
        if ( $shortUrlFound === false ) {
            throw new Exception('Max retries ('. $maxRetries .'reached to generate a shortURL Filename');
        }

        // Everything is fine, return result (without file suffix)
        return $shortUrlName;
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