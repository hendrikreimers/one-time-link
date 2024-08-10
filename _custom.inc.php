<?php
declare(strict_types=1);

/**
 * Functions for custom/individual URL transformations.
 * Whatever your reason is, to do that.
 * 
 */

// Custom ShortURL transformation
function transformShortUrl($shortUrl): string {
    // Do something if .env data
    if (
        defined('TRANSFORM_SHORT_EXPR') &&
        defined('TRANSFORM_SHORT_SEARCH') &&
        defined('TRANSFORM_SHORT_REPLACE')
    ) {
        // Modify URL by given search/replace defined in .env
        if ( preg_match(TRANSFORM_SHORT_EXPR, $shortUrl) ) {
            return str_replace(TRANSFORM_SHORT_SEARCH, TRANSFORM_SHORT_REPLACE, $shortUrl);
        }
    }

    // Nothing changed return
    return $shortUrl;
}

 
 // Custom TargetURL transformation
function transformTargetUrl($targetUrl): string {
    // Do something if .env data
    if (
        defined('TRANSFORM_TARGET_EXPR') &&
        defined('TRANSFORM_TARGET_SEARCH') &&
        defined('TRANSFORM_TARGET_REPLACE')
    ) {
        // Modify URL by given search/replace defined in .env
        if ( preg_match(TRANSFORM_TARGET_EXPR, $targetUrl) ) {
            return str_replace(TRANSFORM_TARGET_SEARCH, TRANSFORM_TARGET_REPLACE, $targetUrl);
        }
    }

    // Nothing changed return
    return $targetUrl;
 }
