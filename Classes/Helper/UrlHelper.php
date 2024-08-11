<?php
declare(strict_types=1);

namespace Helper;

class UrlHelper {

    /**
     * Determines the ServerURL
     *
     * @return string
     */
    public static function getServerUrl(): string {
        // Determine the protocol
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

        // Determine the host name, use SERVER_NAME as fallback
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];

        // Determine the URI path and remove the script name
        $uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';

        // Prepare the full url
        $directoryUrl = "$protocol://$host$uri";

        // Remove the last slash
        if (str_ends_with($directoryUrl, '/')) {
            $directoryUrl = substr($directoryUrl, 0, -1);
        }

        // Deliver result
        return $directoryUrl;
    }

    /**
     * Returns the base URI (without hostname and protocol)
     *
     * @return string
     */
    public static function getBaseUri(): string {
        $uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';

        // Remove the last slash
        if (str_ends_with($uri, '/')) {
            $uri = substr($uri, 0, -1);
        }

        // Deliver result
        return $uri;
    }

}