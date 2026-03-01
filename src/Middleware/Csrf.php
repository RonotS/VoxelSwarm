<?php

declare(strict_types=1);

namespace Swarm\Middleware;

/**
 * Csrf — CSRF token generation and validation.
 *
 * Generates a token per session, validates on state-changing requests.
 * Forms include the token as a hidden field named _token.
 */
class Csrf
{
    /**
     * Generate or retrieve the CSRF token for the current session.
     */
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Render a hidden input field with the CSRF token.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::token()) . '">';
    }

    /**
     * Validate the CSRF token from the request.
     * Call this before processing any state-changing request.
     */
    public static function validate(): void
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!hash_equals(self::token(), $token)) {
            http_response_code(419);
            echo json_encode(['error' => 'CSRF token mismatch. Please refresh and try again.']);
            exit;
        }
    }
}
