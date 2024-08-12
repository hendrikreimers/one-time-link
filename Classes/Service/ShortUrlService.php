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
     * @return array
     * @throws RandomException
     */
    public static function generateShortUrl(): array
    {
        // Initialize basic variables
        $maxRetries = 10;
        $numRetries = 0;
        $shortUrlFound = false;

        // Define the max fileName length (1 byte = 2 chars, 4 bytes = 8 chars)
        $numBytes = ( defined('SHORTURL_FILENAME_MAXBYTES') ) ? SHORTURL_FILENAME_MAXBYTES : 4;

        // Return initial value
        $shortUrlName = '';

        // Try to find a unique filename
        while ( $numRetries < $maxRetries && $shortUrlFound === false ) {
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
            throw new Exception('Max retries ('. $maxRetries .') reached to generate a shortURL Filename');
        }

        // If encryption is enabled generate a random password
        $randPass = null;
        if ( defined('ENCRYPTION_SECRET') && strlen(ENCRYPTION_SECRET) > 0 ) {
            $randPass = bin2hex(random_bytes(defined('ENCRYPTION_RAND_PASS_MAXBYTES') ? intval(ENCRYPTION_RAND_PASS_MAXBYTES) : 4));
        }

        // Everything is fine, return result (without file suffix)
        return [$shortUrlName, $randPass];
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
     * @param string|null $password
     * @param bool $notify
     * @param string $identifier
     * @return void
     */
    public static function saveUrl(string $shortUrl, string $targetUrl, string|null $password = null, bool $notify = false, string $identifier = ''): void {
        $fileName = trim($shortUrl) . self::$fileSuffix;

        $data = json_encode([
            'targetUrl' => $targetUrl,
            'notify' => $notify,
            'identifier' => $identifier
        ]);

        // Encrypt data if encryption is enabled and configured
        if ( defined('ENCRYPTION_SECRET') && strlen(ENCRYPTION_SECRET) > 0 && $password !== null ) {
            $password = self::buildFullPassword($shortUrl, $password); // More comple password
            $data = 'encrypted:' . EncryptionService::encryptString($data, $password, ENCRYPTION_SECRET);
        }

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
        // Extract real shortURL and password if possible
        list($shortUrl, $password) = self::getShortUrlAndPassword($shortUrl);

        // Get file content
        $data = FileService::getContents($shortUrl . self::$fileSuffix);

        // Decrypt if possible
        if (
            defined('ENCRYPTION_SECRET') &&
            strlen(ENCRYPTION_SECRET) > 0 &&
            $password !== null && strlen($password) > 0 &&
            str_starts_with($data, 'encrypted:')
        ) {
            // Extract the encrypted data string
            $identifier = strlen('encrypted:');
            $encryptedData = substr($data, $identifier);

            // Decrypt
            $data = EncryptionService::decryptString($encryptedData, $password, ENCRYPTION_SECRET);
        }

        // Parse JSON if possible
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
        // Extract real shortURL and password if possible
        list($shortUrl, $password) = self::getShortUrlAndPassword($shortUrl);

        return trim($shortUrl) . self::$fileSuffix;
    }

    /**
     * Deletes the shortURL Data file
     *
     * @param string $shortUrl
     * @return void
     */
    public static function removeShortUrl(string $shortUrl): void {
        // Extract real shortURL and password if possible
        list($shortUrl, $password) = self::getShortUrlAndPassword($shortUrl);

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

    /**
     * Extract from the ShortURL string the real shortURL name and password if possible
     *
     * @param string $shortUrl
     * @return array
     */
    private static function getShortUrlAndPassword(string $shortUrl): array {
        // Get shortURL and password string length (num bytes multiplied to hex value, 2 chars per byte)
        $shortUrlLength = defined('SHORTURL_FILENAME_MAXBYTES') ? (intval(SHORTURL_FILENAME_MAXBYTES) * 2): (4 * 2);
        $passwordLength = defined('ENCRYPTION_RAND_PASS_MAXBYTES') ? (intval(ENCRYPTION_RAND_PASS_MAXBYTES) * 2) : (4 * 2);

        // If encrypted extract the shortUrl and password if possible
        if (
            defined('ENCRYPTION_SECRET') &&
            strlen(ENCRYPTION_SECRET) > 0 &&
            strlen($shortUrl) === $shortUrlLength + $passwordLength
        ) {
            // Extract password and real shortUrl fileName from shortUrl string
            $password = substr($shortUrl, $shortUrlLength);
            $shortUrl = substr($shortUrl, 0, $shortUrlLength);

            // Combine to full password
            $password = self::buildFullPassword($shortUrl, $password);
        } else {
            $password = null;
        }

        return [$shortUrl, $password];
    }

    /**
     * Combines to the full more complex password
     *
     * @param string $shortUrl
     * @param string $urlPassword
     * @return string
     */
    private static function buildFullPassword(string $shortUrl, string $urlPassword): string {
        return $shortUrl . ':' . $urlPassword;
    }

}