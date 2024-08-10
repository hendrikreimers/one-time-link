<?php
declare(strict_types=1);

namespace Helper;

class DotEnv {

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
     * NOTIFICATION_MESSAGE="SHORT: '###SHORT_URL###'\nTARGET: '###TARGET_URL###'"
     * ```
     *
     * @return bool
     */
    public static function loadDotEnv(): bool {
        // Path to .env file
        $envFile = General::getCallerPath() . DIRECTORY_SEPARATOR . '.env';

        // Do something if .env exists
        if ( file_exists($envFile)) {
            $envContent = file_get_contents($envFile);

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