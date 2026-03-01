<?php

declare(strict_types=1);

namespace Swarm\Helpers;

/**
 * Response — Rendering, redirects, and JSON helpers.
 */
class Response
{
    /**
     * Render a PHP view with data.
     */
    public static function view(string $template, array $data = [], ?string $layout = null): void
    {
        // Extract data as local variables for the template
        extract($data);

        // Capture the view content
        ob_start();
        require SWARM_ROOT . '/views/' . $template . '.php';
        $content = ob_get_clean();

        if ($layout) {
            require SWARM_ROOT . '/views/layouts/' . $layout . '.php';
        } else {
            echo $content;
        }
    }

    /**
     * Send a JSON response.
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to a URL.
     */
    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect back to the previous page. Uses Referer header or falls back to '/'.
     */
    public static function back(array $flash = []): void
    {
        // Store flash data in session
        if (!empty($flash)) {
            $_SESSION['_flash'] = $flash;
        }

        $url = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirect($url);
    }

    /**
     * Get and clear flash data from session.
     */
    public static function flash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
