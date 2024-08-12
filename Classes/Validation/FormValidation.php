<?php
declare(strict_types=1);

namespace Validation;

class FormValidation {

    /**
     * Returns a filtered input value
     *
     * @param string $varName
     * @param int $filter
     * @return mixed
     */
    public static function getFilteredValue(string $varName, int $filter): mixed {
        return filter_input(INPUT_POST, $varName, $filter);
    }

    /**
     * Additional string sanitizing
     *
     * @param string|null $value
     * @param string $default
     * @return string
     */
    public static function additionalSanitize(?string $value, string $default = ''): string {
        if ( $value ) {
            $value = htmlspecialchars(strip_tags($value));
        } else $value = $default;

        return $value;
    }

    /**
     * Checks if given URL is set and starts with http(s)
     *
     * @param string $url
     * @return void
     */
    public static function urlCorrectOrExit(string $url): void {
        // Show error
        if ($url === false) {
            echo "Wrong URL";
            exit;
        }

        // Check whether the URL begins with http:// or https://
        if (!preg_match('/^https?:\/\/.+/', $url)) {
            echo "URL must start with http:// or https://";
            exit;
        }

        // Further validation by URL parsing and checking specific host names or TLDs
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            echo "Wrong URL";
            exit;
        }
    }

}