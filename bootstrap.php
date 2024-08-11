<?php
declare(strict_types=1);

use Helper\DotEnvHelper;
use Helper\SecurityHelper;

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Include custom transformation function if they exists
if ( file_exists('_custom.inc.php') ) require_once('_custom.inc.php');

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

// Force HTTPS
//SecurityHelper::forceHttps();

// Send security header
SecurityHelper::sendDefaultHeaders();

// Disallow crawler and link previews
SecurityHelper::preventCrawlers();