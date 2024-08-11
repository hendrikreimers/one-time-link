<?php
declare(strict_types=1);

namespace Helper;

use Service\FileService;

class DotEnvHelper {

    protected static string $envFileName = '.env';

    /**
     * Loads enviroment file (.env)
     *
     * Example content of .env file:
     *
     * ```
     * TRANSFORM_TARGET_EXPR="\/\/your-domain.com\/"
     * TRANSFORM_TARGET_SEARCH="/a-folder/"
     * TRANSFORM_TARGET_REPLACE="/a-folder/subfolder/"
     *
     * TRANSFORM_SHORT_EXPR="\/a-folder\/"
     * TRANSFORM_SHORT_SEARCH=/a-folder/
     * TRANSFORM_SHORT_REPLACE=/a-folder/subfolder/
     *
     * NOTIFICATION_EMAIL=you@somewhere.com
     * NOTIFICATION_SUBJECT="One Time Link - Event"
     * ```
     *
     * @return bool
     */
    public static function loadDotEnv(): bool {
        // Do something if .env exists
        if ( FileService::fileExists(self::$envFileName, false)) {
            $envContent = FileService::getContents(self::$envFileName, false);

            // Parse .env file
            if (preg_match_all('/([A-Z_]+)="?(.*[^"\r\n])"?/m', $envContent, $matches)) {
                foreach ($matches[1] as $index => $key) {
                    define(strtoupper($key), $matches[2][$index]);
                }
            }

            return true;
        }

        return false;
    }

}