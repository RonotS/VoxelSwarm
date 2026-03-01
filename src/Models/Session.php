<?php

declare(strict_types=1);

namespace Swarm\Models;

use Swarm\Database;

/**
 * Session — Operator session management.
 *
 * Simple token-based auth: 64-char hex token stored in a cookie
 * and validated against the operator_sessions table.
 */
class Session
{
    private const COOKIE_NAME = 'swarm_session';
    private const COOKIE_DAYS = 30;

    /**
     * Create a new session and set the cookie.
     * Returns the session token.
     */
    public static function create(): string
    {
        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('c', time() + (self::COOKIE_DAYS * 86400));

        Database::query(
            "INSERT INTO operator_sessions (id, expires_at, created_at) VALUES (?, ?, datetime('now'))",
            [$token, $expiresAt]
        );

        $secure   = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie(self::COOKIE_NAME, $token, [
            'expires'  => time() + (self::COOKIE_DAYS * 86400),
            'path'     => '/',
            'secure'   => $secure,
            'httponly'  => true,
            'samesite'  => 'Lax',
        ]);

        return $token;
    }

    /**
     * Validate the current session. Returns true if authenticated.
     */
    public static function validate(): bool
    {
        $token = $_COOKIE[self::COOKIE_NAME] ?? null;

        if (!$token || strlen($token) !== 64) {
            return false;
        }

        $stmt = Database::query(
            'SELECT id, expires_at FROM operator_sessions WHERE id = ?',
            [$token]
        );
        $row = $stmt->fetch();

        if (!$row) {
            return false;
        }

        // Check expiry
        if (strtotime($row['expires_at']) < time()) {
            self::destroyToken($token);
            return false;
        }

        return true;
    }

    /**
     * Destroy the current session.
     */
    public static function destroy(): void
    {
        $token = $_COOKIE[self::COOKIE_NAME] ?? null;

        if ($token) {
            self::destroyToken($token);
        }

        setcookie(self::COOKIE_NAME, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly'  => true,
            'samesite'  => 'Lax',
        ]);
    }

    /**
     * Clean up expired sessions.
     */
    public static function cleanup(): void
    {
        Database::query("DELETE FROM operator_sessions WHERE expires_at < datetime('now')");
    }

    private static function destroyToken(string $token): void
    {
        Database::query('DELETE FROM operator_sessions WHERE id = ?', [$token]);
    }
}
