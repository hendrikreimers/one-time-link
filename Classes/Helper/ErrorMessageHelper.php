<?php
declare(strict_types=1);

namespace Helper;

use Exception;
use Template\SimpleTemplateEngine;

/**
 * Error Message Helper
 *
 * Sends some kind of error messages to the user and
 * possibly stops the script from running ($exitAfterSent in methods)
 *
 * Usage:
 *
 * ```
 * $errorMsgHelper = new ErrorMessageHelper($template, $nonce);
 * $errorMsgHelper->sendNotFound();
 * ```
 */
class ErrorMessageHelper {

    /**
     * Constructor with dependency injection
     *
     * @param SimpleTemplateEngine $templateEngine
     * @param string $nonce
     */
    public function __construct(
        private SimpleTemplateEngine &$templateEngine,
        private string &$nonce
    ) {}

    /**
     * Sends a common 404 Not found error
     *
     * @param bool $exitAfterSent
     * @return void
     * @throws Exception
     */
    public function sendNotFound(bool $exitAfterSent = true): void {
        // Send headers
        header("HTTP/1.0 404 Not Found");

        // Load template
        $this->templateEngine->loadTemplate('404');

        // Send rendered template to browser
        $this->sendError($exitAfterSent);
    }

    /**
     * Sends a bad request with a custom message
     *
     * @param string $msg
     * @param bool $exitAfterSent
     * @return void
     * @throws Exception
     */
    public function sendCustomBadRequest(string $msg, bool $exitAfterSent = true): void {
        // Send headers
        header("HTTP/1.0 400 Bad Request");

        // Load template
        $this->templateEngine->loadTemplate('CustomError');

        // Assign error message to template
        $this->templateEngine->assignVar('ERROR_MSG', strip_tags($msg));

        // Send rendered template to browser
        $this->sendError($exitAfterSent);
    }

    /**
     * Renders the template and sends it to the browser.
     * After that it will exit the script if required.
     *
     * @param bool $exitAfterSent
     * @return void
     * @throws Exception
     */
    private function sendError(bool $exitAfterSent = true): void {
        // Prepare additional template variables
        $this->templateEngine->assignMultiple([
            'BASE_PATH' => UrlHelper::getBaseUri(),
            'NONCE' => $this->nonce
        ]);

        // Send rendered template to browser
        echo $this->templateEngine->render();

        // Exit if requested
        $this->doExit($exitAfterSent);
    }

    /**
     * Stops scripts if requested
     *
     * @param bool $exitAfterSent
     * @return void
     */
    private function doExit(bool $exitAfterSent = true): void {
        if ( $exitAfterSent ) {
            exit;
        }
    }
}