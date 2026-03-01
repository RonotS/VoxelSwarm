<?php

declare(strict_types=1);

namespace Swarm\Helpers;

use Swarm\Models\Setting;

/**
 * Crypt — AES-256-CBC encryption for sensitive settings.
 *
 * The encryption key is the app_key from the settings table,
 * generated during installation (32-byte random hex).
 */
class Crypt
{
    private const CIPHER = 'aes-256-cbc';

    /**
     * Encrypt a plaintext string.
     */
    public static function encrypt(string $value): string
    {
        $key = self::getKey();
        $iv  = random_bytes(openssl_cipher_iv_length(self::CIPHER));

        $encrypted = openssl_encrypt($value, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt an encrypted string.
     */
    public static function decrypt(string $payload): string
    {
        $key  = self::getKey();
        $data = base64_decode($payload, true);

        if ($data === false) {
            throw new \RuntimeException('Invalid encrypted payload');
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv       = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $decrypted = openssl_decrypt($encrypted, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * Generate a random key suitable for use as app_key.
     */
    public static function generateKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    private static function getKey(): string
    {
        // Read directly from DB to avoid infinite recursion through Setting::getJson
        $stmt = \Swarm\Database::query("SELECT value FROM settings WHERE key = 'app_key'");
        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row || empty($row['value'])) {
            throw new \RuntimeException('No app_key configured. Run: php scripts/install.php');
        }

        // Derive a 32-byte key from the hexadecimal app_key
        return hash('sha256', $row['value'], true);
    }
}
