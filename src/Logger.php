<?php

declare(strict_types=1);

namespace Swarm;

/**
 * Logger — Simple file-based logging with daily rotation.
 *
 * Each channel writes to its own file: storage/logs/{channel}-YYYY-MM-DD.log
 * 30-day retention handled externally (cron or manual cleanup).
 */
class Logger
{
    public static function info(string $channel, string $message, array $context = []): void
    {
        self::write($channel, 'INFO', $message, $context);
    }

    public static function warning(string $channel, string $message, array $context = []): void
    {
        self::write($channel, 'WARNING', $message, $context);
    }

    public static function error(string $channel, string $message, array $context = []): void
    {
        self::write($channel, 'ERROR', $message, $context);
    }

    private static function write(string $channel, string $level, string $message, array $context): void
    {
        $logDir = SWARM_STORAGE . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $file = $logDir . '/' . $channel . '-' . date('Y-m-d') . '.log';
        $line = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : ''
        );

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
