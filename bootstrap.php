<?php
declare(strict_types=1);

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// CONTEXT CHECKS
if (
    (!defined("FRONTEND_CONTEXT") && !defined("CLI_CONTEXT")) || // Not context defined
    (defined("FRONTEND_CONTEXT") && php_sapi_name() === 'cli') || // Backend Context but in CLI mode
    (defined("CLI_CONTEXT") && php_sapi_name() !== 'cli') // CLI Context but not on command line
) {
    die("WRONG_CONTEXT\n");
}

use Helper\DotEnvHelper;
use Helper\SecurityHelper;

// Include custom transformation function if they exists
if ( defined("FRONTEND_CONTEXT") && file_exists('_custom.inc.php') ) {
    require_once('_custom.inc.php');
}

// Custom Classes Autoloader registration
spl_autoload_register(function ($class) {
    $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';

    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Loads .env file constants
DotEnvHelper::loadDotEnv();

// Only in Frontend (Browser) send headers etc.
if ( defined("FRONTEND_CONTEXT") ) {
    // Force HTTPS
    SecurityHelper::forceHttps();

    // Send security header
    SecurityHelper::sendDefaultHeaders();

    // Disallow crawler and link previews
    SecurityHelper::preventCrawlers();
}