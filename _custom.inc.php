<?php
declare(strict_types=1);

/**
 * Functions for custom/individual URL transformations.
 * Whatever your reason is, to do that.
 * 
 */
 
 /*
 // Custom ShortURL transformation
 function transformShortUrl($shortUrl): string {
	 // Your transformation logic for the shortURL
 }
 */
 
 // Custom TargetURL transformation
function transformTargetUrl($targetUrl): string {
    // Path to .env file
    $envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';

    // Do something if .env exists
    if ( file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        $env = [];

        // Parse .env file
        //
        // Example content:
        //
        //   TRANSFORM_TARGET_EXPR="\/\/your-domain.com\/"
        //   TRANSFORM_TARGET_SEARCH="/a-folder/"
        //   TRANSFORM_TARGET_REPLACE="/a-folder/subfolder/"
        //
        if ( preg_match_all('/([A-Z_]+)="(.*)"/mi', $envContent, $matches) ) {
            foreach ( $matches[1] as $index => $key ) {
                $env[trim($key)] = $matches[2][$index];
            }
        }

        // Modify URL by given search/replace defined in .env
        if ( preg_match('=' . $env['TRANSFORM_TARGET_EXPR'] . '=i', $targetUrl) ) {
            return str_replace($env['TRANSFORM_TARGET_SEARCH'], $env['TRANSFORM_TARGET_REPLACE'], $targetUrl);
        }
    }

    // Nothing changed return
    return $targetUrl;
 }
