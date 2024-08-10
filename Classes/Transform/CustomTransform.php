<?php
declare(strict_types=1);

namespace Transform;

/**
 * Custom Transform
 *
 * Functions to call custom transformation functions.
 *
 */
class CustomTransform {

    /**
     * Calls custom transformation for shortURL
     *
     * @param string $shortUrl
     * @return string
     */
    public static function customTransformShortUrl(string $shortUrl): string {
        if ( function_exists('transformShortUrl') ) {
            $shortUrl = transformShortUrl($shortUrl);
        }

        return $shortUrl;
    }

    /**
     * Calls custom transformation for targetURL
     *
     * @param string $targetUrl
     * @return string
     */
    public static function customTransformTargetUrl(string $targetUrl): string {
        if ( function_exists('transformTargetUrl') ) {
            $targetUrl = transformTargetUrl($targetUrl);
        }

        return $targetUrl;
    }
}