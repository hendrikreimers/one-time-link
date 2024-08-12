<?php
declare(strict_types=1);

/**
 * Command Line script to create a random SecretKey for Encryption of the ShortURL Data files.
 *
 * Usage:
 *
 * ```
 * # php ./cli-initialize-encryption.php
 * ```
 */

// Defaults (Bootstrapping)
const CLI_CONTEXT = true;
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use Service\FileService;

if ( defined('ENCRYPTION_SECRET') || defined('ENCRYPTION_RAND_PASS_MAXBYTES') ) {
    echo "Looks like Encryption is already configured. Exiting now.\n";
    exit;
}

echo "Generating encryption Secret..." . PHP_EOL;
$secretKey = bin2hex(random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES));

if ( FileService::fileExists('.env', false) ) {
    echo "Putting into .env file." . PHP_EOL;
} else {
    echo "Creating .env file and pushing Secret into it." . PHP_EOL;
}

// Prepare .env data string
$data = PHP_EOL . 'ENCRYPTION_SECRET=' . $secretKey . PHP_EOL;
$data .= 'ENCRYPTION_RAND_PASS_MAXBYTES=4' . PHP_EOL;

// Append it to the file
FileService::writeFile('.env', $data, false, true);

// Done
echo PHP_EOL;
echo "All done." . PHP_EOL;
