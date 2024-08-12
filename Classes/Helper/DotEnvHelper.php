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
        // Expression to identify key/value in each line
        $lineExpr = '/^([A-Z_]+)="?(.*[^"\r\n])"?$/m';

        // Do something if .env exists
        if ( FileService::fileExists(self::$envFileName, false)) {
            // Get file content and split to each line
            $envContentLines = explode(PHP_EOL,
                FileService::getContents(self::$envFileName, false)
            );

            // Nothing in there, so leave
            if ( sizeof($envContentLines) <= 0) {
                return false;
            }

            // Parse .env file
            foreach ( $envContentLines as $envContentLine ) {
                if (preg_match($lineExpr, trim($envContentLine), $matches)) {
                    // Check if result size is three (total match, key, value)
                    if ( sizeof($matches) === 3 ) {
                        // Unpack
                        list($null, $key, $value) = $matches;

                        // Define constant if not already done
                        if ( !defined($key) ) {
                            define($key, $value);
                        }
                    }
                }
            }

            // All fine
            return true;
        }

        // Something went wrong
        return false;
    }

}