<?php
declare(strict_types=1);

namespace Validation;

use Exception;
use Helper\ErrorMessageHelper;

class FormValidation {

    /**
     * Constructor with dependency injection
     *
     * @param ErrorMessageHelper $errorMessageHelper
     */
    public function __construct(
        private ErrorMessageHelper &$errorMessageHelper
    ) {}

    /**
     * Returns a filtered input value
     *
     * @param string $varName
     * @param int $filter
     * @return mixed
     */
    public function getFilteredValue(string $varName, int $filter): mixed {
        return filter_input(INPUT_POST, $varName, $filter);
    }

    /**
     * Additional string sanitizing
     *
     * @param string|null $value
     * @param string $default
     * @return string
     */
    public function additionalSanitize(?string $value, string $default = ''): string {
        if ( $value ) {
            $value = htmlspecialchars(strip_tags($value));
        } else $value = $default;

        return $value;
    }

    /**
     * Checks if given URL is set and starts with http(s)
     *
     * @param string|false $url
     * @return void
     * @throws Exception
     */
    public function urlCorrectOrExit(string | false $url): void {
        // Show error
        if ($url === false) {
            $this->errorMessageHelper->sendCustomBadRequest('Wrong URI');
        }

        // Check if the URL is an unencrypted string
        if ( str_starts_with($url, 'encrypted:') ) {
            $this->errorMessageHelper->sendNotFound();
        }

        // Check whether the URL begins with http:// or https://
        if (!preg_match('/^https?:\/\/.+/', $url)) {
            $this->errorMessageHelper->sendCustomBadRequest('URL must start with http:// or https://');
        }

        // Further validation by URL parsing and checking specific host names or TLDs
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            $this->errorMessageHelper->sendCustomBadRequest('Wrong URI Scheme');
        }
    }
}