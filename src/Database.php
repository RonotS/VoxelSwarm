<?php

declare(strict_types=1);

namespace Swarm;

/**
 * Database — PDO wrapper with SQLite WAL mode and migration runner.
 *
 * All access goes through Database::connection(). The connection is
 * created lazily on first call and reused for the request lifecycle.
 */
class Database
{
    private static ?\PDO $pdo = null;
    private static string $path = '';

    /**
     * Set the database path. Called from bootstrap.php.
     * Does NOT open the connection — that happens on first query.
     */
    public static function init(string $path): void
    {
        self::$path = $path;
    }

    /**
     * Get the PDO connection, creating it if needed.
     */
    public static function connection(): \PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new \PDO('sqlite:' . self::$path, null, null, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE  => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES    => false,
            ]);

            // WAL mode for concurrent reads during provisioning
            self::$pdo->exec('PRAGMA journal_mode=WAL');
            self::$pdo->exec('PRAGMA foreign_keys=ON');
            self::$pdo->exec('PRAGMA busy_timeout=5000');
        }

        return self::$pdo;
    }

    /**
     * Run all pending migrations from the migrations/ directory.
     * Tracks executed migrations in a _migrations table.
     */
    public static function migrate(string $migrationsDir): array
    {
        $db = self::connection();

        // Create migrations tracking table
        $db->exec("
            CREATE TABLE IF NOT EXISTS _migrations (
                filename TEXT PRIMARY KEY,
                executed_at TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");

        // Get already-run migrations
        $executed = [];
        $stmt = $db->query('SELECT filename FROM _migrations ORDER BY filename');
        while ($row = $stmt->fetch()) {
            $executed[] = $row['filename'];
        }

        // Find and run pending migrations
        $files = glob($migrationsDir . '/*.sql');
        sort($files);

        $ran = [];
        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $executed, true)) {
                continue;
            }

            $sql = file_get_contents($file);
            if ($sql === false) {
                throw new \RuntimeException("Cannot read migration: {$filename}");
            }

            $db->exec($sql);
            $db->prepare('INSERT INTO _migrations (filename) VALUES (?)')
               ->execute([$filename]);

            $ran[] = $filename;
        }

        return $ran;
    }

    /**
     * Convenience: run a prepared statement and return the statement.
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Convenience: insert a row and return the last insert ID.
     */
    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::connection()->lastInsertId();
    }
}
