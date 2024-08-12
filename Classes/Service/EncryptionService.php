<?php
declare(strict_types=1);

namespace Service;

use Exception;
use Random\RandomException;
use SodiumException;

/**
 * Simple string encryption Service Class
 *
 */
class EncryptionService {

    /**
     * Encrypts a string with a user-provided password and a fixed server-side secret.
     *
     * @param string $plaintext The string to encrypt.
     * @param string $password The user-provided password.
     * @param string $serverSecret The fixed server-side secret.
     * @return string The encrypted string.
     * @throws RandomException|SodiumException
     */
    public static function encryptString(string $plaintext, string $password, string $serverSecret): string {
        // Derive encryption key using Argon2 (you can use HMAC or PBKDF2 instead)
        $key = sodium_crypto_pwhash(
            32, // Length of the derived key
            $password,
            hex2bin($serverSecret),
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
        );

        // Generate a random initialization vector (IV)
        $iv = random_bytes(16);

        // Encrypt the plaintext using AES-256-CBC
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        // Create HMAC for integrity check
        $hmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);

        // Return base64 encoded IV, HMAC, and ciphertext (password is not included)
        return base64_encode($iv . $hmac . $ciphertext);
    }

    /**
     * Decrypts a string that was encrypted with a user-provided password and a fixed server-side secret.
     *
     * @param string $encryptedString The encrypted string.
     * @param string $password The user-provided password.
     * @param string $serverSecret The fixed server-side secret.
     * @return string The decrypted string or false on failure.
     * @throws SodiumException
     * @throws Exception
     */
    public static function decryptString(string $encryptedString, string $password, string $serverSecret): string {
        // Derive decryption key using Argon2 (same as during encryption)
        $key = sodium_crypto_pwhash(
            32, // Length of the derived key
            $password,
            hex2bin($serverSecret),
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
        );

        // Decode the base64 encoded data
        $data = base64_decode($encryptedString);

        // Extract the IV, HMAC, and ciphertext
        $iv = substr($data, 0, 16);
        $hmac = substr($data, 16, 32);
        $ciphertext = substr($data, 48);

        // Verify HMAC
        $calculatedHmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);
        if (!hash_equals($hmac, $calculatedHmac)) {
            throw new Exception('HMAC validation failed');
        }

        // Decrypt the ciphertext using AES-256-CBC
        return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }
}