<?php

declare(strict_types=1);

namespace Swarm\Services;

use Swarm\Logger;

/**
 * HealthChecker — Verify a provisioned instance is reachable.
 *
 * Makes HTTP GET to the instance's Studio install page.
 * 3 attempts at 3-second intervals. 200 or 302 = success.
 */
class HealthChecker
{
    /**
     * Verify an instance is reachable at its subdomain.
     *
     * @throws \RuntimeException if all attempts fail
     */
    public static function verify(string $subdomain): void
    {
        $url      = "https://{$subdomain}/_studio/install.php";
        $attempts = 3;
        $delay    = 3; // seconds between retries

        for ($i = 1; $i <= $attempts; $i++) {
            $result = self::httpGet($url);

            if ($result !== false) {
                Logger::info('provision', 'Health check passed', [
                    'subdomain' => $subdomain,
                    'attempt'   => $i,
                    'status'    => $result,
                ]);
                return;
            }

            Logger::warning('provision', 'Health check attempt failed', [
                'subdomain' => $subdomain,
                'attempt'   => $i,
            ]);

            if ($i < $attempts) {
                sleep($delay);
            }
        }

        throw new \RuntimeException(
            "Health check failed after {$attempts} attempts: {$subdomain}"
        );
    }

    /**
     * Make a GET request. Returns the HTTP status code on success (200, 302), false on failure.
     */
    private static function httpGet(string $url): int|false
    {
        $context = stream_context_create([
            'http' => [
                'method'          => 'GET',
                'timeout'         => 10,
                'follow_location' => 0, // Don't follow — 302 is also success
                'ignore_errors'   => true,
            ],
            'ssl' => [
                'verify_peer'      => false, // Self-signed wildcard certs during setup
                'verify_peer_name' => false,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false && empty($http_response_header)) {
            return false;
        }

        // Parse status code from response headers
        if (!empty($http_response_header)) {
            $statusLine = $http_response_header[0] ?? '';
            if (preg_match('/HTTP\/\S+\s+(\d+)/', $statusLine, $matches)) {
                $code = (int) $matches[1];
                if ($code === 200 || $code === 302) {
                    return $code;
                }
            }
        }

        return false;
    }
}
