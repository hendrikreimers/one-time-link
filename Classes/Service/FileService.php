<?php
declare(strict_types=1);

namespace Service;

/**
 * File Handling Service
 *
 */
class FileService {

    /**
     * Returns the directory of the calling script
     *
     * @param bool $addEndingSlash
     * @return string
     */
    public static function getCallerPath(bool $addEndingSlash = true): string {
        $caller = $_SERVER['SCRIPT_FILENAME'];
        $path = realpath(dirname($caller));

        return ( $addEndingSlash ) ? $path . DIRECTORY_SEPARATOR : $path;
    }

    /**
     * Returns the path to the data directory
     *
     * @param bool $addEndingSlash
     * @return string
     */
    public static function getDataPath(bool $addEndingSlash = true): string {
        $path = implode(DIRECTORY_SEPARATOR, [
            self::getCallerPath(false),
            'data'
        ]);

        return ( $addEndingSlash ) ? $path . DIRECTORY_SEPARATOR : $path;
    }

    /**
     * Returns file content
     *
     * @param string $file
     * @param bool $onlyDataDir
     * @return string|false
     */
    public static function getContents(string $file, bool $onlyDataDir = true): string|false {
        $file = self::appendPath($file, $onlyDataDir);

        if ( !self::fileExists($file, $onlyDataDir) )
            return false;

        return file_get_contents($file);
    }

    /**
     * Write a file
     *
     * @param string $file
     * @param string $contents
     * @param bool $onlyDataDir
     * @param bool $append
     * @return bool
     */
    public static function writeFile(string $file, string $contents, bool $onlyDataDir = true, bool $append = false): bool {
        $file = self::appendPath($file, $onlyDataDir);

        if ( self::fileExists($file, $onlyDataDir) && !$append )
            return false;

        if ( $append ) {
            return (bool)file_put_contents($file, $contents, FILE_APPEND);
        } else return (bool)file_put_contents($file, $contents);
    }

    /**
     * Deletes a file
     *
     * @param string $file
     * @param bool $onlyDataDir
     * @return bool
     */
    public static function deleteFile(string $file, bool $onlyDataDir = true): bool {
        $file = self::appendPath($file, $onlyDataDir);

        if ( !self::fileExists($file, $onlyDataDir) )
            return false;

        return unlink($file);
    }

    /**
     * Checks if the file exists
     *
     * @param string $fileName
     * @param bool $onlyDataDir
     * @return bool
     */
    public static function fileExists(string $fileName, bool $onlyDataDir = true): bool {
        return file_exists(self::appendPath($fileName, $onlyDataDir));
    }

    /**
     * Forces append the main path to file string
     *
     * @param string $filePath
     * @param bool $onlyDataDir
     * @return string
     */
    private static function appendPath(string $filePath, bool $onlyDataDir = true): string {
        // Remove breakout to root
        if (str_contains($filePath, '...'))
            str_replace('...' . DIRECTORY_SEPARATOR, '', $filePath);

        // Remove breakout to parent directory
        if (str_contains($filePath, '..'))
            str_replace('..' . DIRECTORY_SEPARATOR, '', $filePath);

        // Don't add it if the path is already correct to the base path
        if ( !$onlyDataDir && str_starts_with($filePath, self::getCallerPath()) ) {
            return $filePath;
        } elseif ( $onlyDataDir && str_starts_with($filePath, self::getDataPath()) ) {
            return $filePath;
        }

        // Append the path
        return ( $onlyDataDir ) ? self::getDataPath() . $filePath : self::getCallerPath() . $filePath;
    }

    /**
     * Deletes all .url files whose creation or modification date is older than a specified number of days.
     *
     * @param string $directory The directory in which to search for .url files.
     * @param string $wildcard File selector wildcard for example "*.url"
     * @param int $days The number of days used as the threshold for deleting files.
     * @param bool $onlyDataDir
     * @return array
     */
    public static function getOldFiles(string $directory, string $wildcard, int $days = 5, bool $onlyDataDir = true): array {
        // Take care of correct path
        $fileSelector = self::appendPath($directory . DIRECTORY_SEPARATOR . $wildcard, $onlyDataDir);

        // Get all .url files in the specified directory
        $files = glob($fileSelector);

        // Prepare result array
        $resultList = [];

        if ( sizeof($files) <= 0 )
            return $resultList;

        foreach ($files as $file) {
            // Get the creation and modification times of the file
            $fileCreationTime = filectime($file);
            $fileModificationTime = filemtime($file);
            $currentTime = time();

            // Use the most recent of the two dates (creation or modification)
            $lastModificationTime = max($fileCreationTime, $fileModificationTime);

            // If the last modification time is older than the specified number of days, delete the file
            if ($currentTime - $lastModificationTime >= $days * 86400) {
                $resultList[] = $file;
            }
        }

        return $resultList;
    }
}