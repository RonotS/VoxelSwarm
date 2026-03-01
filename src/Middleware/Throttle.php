<?php

declare(strict_types=1);

namespace Swarm\Middleware;

/**
 * Throttle — Rate limiting for signup and login.
 *
 * Uses a simple file-based approach: storage/logs/throttle-{type}.json
 * Tracks IP → [timestamps] and prunes expired entries.
 */
class Throttle
{
    private const LIMITS = [
        'signup' => ['max' => 3,  'window' => 3600],    // 3 per hour
        'login'  => ['max' => 5,  'window' => 900],     // 5 per 15 minutes
    ];

    public function handle(string $type = 'signup'): void
    {
        $limit  = self::LIMITS[$type] ?? self::LIMITS['signup'];
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $file   = SWARM_STORAGE . "/logs/throttle-{$type}.json";

        $data = [];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: [];
        }

        $now     = time();
        $cutoff  = $now - $limit['window'];

        // Prune expired entries for this IP
        $attempts = array_filter(
            $data[$ip] ?? [],
            fn(int $ts) => $ts > $cutoff
        );

        if (count($attempts) >= $limit['max']) {
            http_response_code(429);
            echo json_encode([
                'error'   => 'Too many attempts. Please try again later.',
                'retry_after' => $limit['window'],
            ]);
            exit;
        }

        // Record this attempt
        $attempts[] = $now;
        $data[$ip]  = array_values($attempts);

        // Prune other IPs with no recent attempts
        foreach ($data as $dataIp => $timestamps) {
            $data[$dataIp] = array_filter($timestamps, fn(int $ts) => $ts > $cutoff);
            if (empty($data[$dataIp])) {
                unset($data[$dataIp]);
            }
        }

        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
