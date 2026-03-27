<?php

declare(strict_types=1);

namespace Swarm\Services;

use Swarm\Database;
use Swarm\Logger;
use Swarm\Models\Instance;
use Swarm\Models\Setting;
use Swarm\Adapters\AdapterFactory;

/**
 * Provisioner — Orchestrates the full provisioning flow.
 *
 * Runs synchronously after fastcgi_finish_request() / ignore_user_abort(true).
 * Each step updates the database so the polling endpoint stays current.
 */
class Provisioner
{
    /**
     * Run the full provisioning flow for an instance.
     */
    public static function run(int $instanceId): void
    {
        $instance = Instance::find($instanceId);
        if (!$instance) {
            Logger::error('provision', 'Instance not found', ['id' => $instanceId]);
            return;
        }

        Instance::update($instanceId, ['status' => 'provisioning']);

        try {
            // Step 1: Copy template
            self::runStep($instanceId, 'copy_template', function () use ($instance) {
                $templatePath = Setting::get('template_path', SWARM_ROOT . '/template/voxelsite/active');
                $instancePath = Setting::get('instances_path', SWARM_STORAGE . '/instances') . '/' . $instance['slug'];

                if (!is_dir($templatePath)) {
                    throw new \RuntimeException("Template not found: {$templatePath}");
                }

                self::recursiveCopy($templatePath, $instancePath);

                Instance::update((int) $instance['id'], ['document_root' => $instancePath]);
            });

            // Step 2: Write meta file
            self::runStep($instanceId, 'write_meta', function () use ($instance) {
                $instancePath = Setting::get('instances_path', SWARM_STORAGE . '/instances') . '/' . $instance['slug'];

                $meta = json_encode([
                    'slug'           => $instance['slug'],
                    'subdomain'      => $instance['subdomain'],
                    'email'          => $instance['email'],
                    'provisioned_at' => date('c'),
                ], JSON_PRETTY_PRINT);

                file_put_contents($instancePath . '/meta.json', $meta);
            });

            // Step 3: Create subdomain
            self::runStep($instanceId, 'create_subdomain', function () use ($instance) {
                $instancePath = Setting::get('instances_path', SWARM_STORAGE . '/instances') . '/' . $instance['slug'];
                $adapter = AdapterFactory::create();
                $adapter->createSubdomain($instance['slug'], $instancePath);
            });

            // Step 4: Health check (skip for local/railway adapter — routing is internal)
            self::runStep($instanceId, 'health_check', function () use ($instance) {
                $adapter = Setting::get('control_panel_adapter', 'nginx');
                if ($adapter === 'local' || $adapter === 'railway') {
                    Logger::info('provision', 'Health check skipped (' . $adapter . ' adapter)', [
                        'slug' => $instance['slug'],
                    ]);
                    return;
                }
                HealthChecker::verify($instance['subdomain']);
            });

            // Step 5: Activate
            self::runStep($instanceId, 'activate', function () use ($instanceId) {
                Instance::update($instanceId, [
                    'status'         => 'active',
                    'provisioned_at' => date('c'),
                ]);
            });

            // Step 6: Send welcome email (best-effort, skip if no mailer)
            try {
                self::runStep($instanceId, 'send_welcome', function () use ($instance) {
                    $mailDriver = Setting::get('mail_driver', 'log');
                    if ($mailDriver === 'log') {
                        Logger::info('mail', 'Welcome email logged (mail_driver=log)', [
                            'to'   => $instance['email'],
                            'slug' => $instance['slug'],
                        ]);
                        return;
                    }
                    Mailer::sendWelcome($instance);
                });
            } catch (\Throwable $e) {
                Logger::warning('mail', 'Welcome email failed (non-fatal)', [
                    'slug'  => $instance['slug'],
                    'error' => $e->getMessage(),
                ]);
            }

            Logger::info('provision', 'Provisioning complete', ['slug' => $instance['slug']]);

        } catch (\Throwable $e) {
            Instance::update($instanceId, ['status' => 'failed']);

            Logger::error('provision', 'Provisioning failed', [
                'slug'  => $instance['slug'],
                'step'  => $instance['step'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            // Clean up partial provisioning
            self::cleanup($instance);

            // Notify operator
            try {
                Mailer::sendProvisionFailed($instance, $e->getMessage());
            } catch (\Throwable) {
                // Can't send email about failure — just log it
                Logger::error('mail', 'Could not notify operator of provision failure');
            }
        }
    }

    /**
     * Execute a provisioning step with logging.
     */
    private static function runStep(int $instanceId, string $step, callable $callback): void
    {
        Instance::update($instanceId, ['step' => $step]);

        // Log step start
        $logId = Database::insert(
            "INSERT INTO provision_logs (instance_id, step, status, created_at) VALUES (?, ?, 'started', datetime('now'))",
            [$instanceId, $step]
        );

        $startTime = hrtime(true);

        try {
            $callback();

            $durationMs = (int) ((hrtime(true) - $startTime) / 1_000_000);

            Database::query(
                "UPDATE provision_logs SET status = 'completed', duration_ms = ? WHERE id = ?",
                [$durationMs, $logId]
            );

            Logger::info('provision', "Step completed: {$step}", [
                'instance_id' => $instanceId,
                'duration_ms' => $durationMs,
            ]);

        } catch (\Throwable $e) {
            $durationMs = (int) ((hrtime(true) - $startTime) / 1_000_000);

            Database::query(
                "UPDATE provision_logs SET status = 'failed', error = ?, duration_ms = ? WHERE id = ?",
                [$e->getMessage(), $durationMs, $logId]
            );

            throw $e;
        }
    }

    /**
     * Clean up after a failed provisioning attempt.
     */
    private static function cleanup(array $instance): void
    {
        $instancePath = Setting::get('instances_path', SWARM_STORAGE . '/instances') . '/' . $instance['slug'];

        if (is_dir($instancePath)) {
            self::recursiveDelete($instancePath);
            Logger::info('provision', 'Cleaned up instance directory', ['slug' => $instance['slug']]);
        }

        // Try to remove subdomain routing
        try {
            $adapter = AdapterFactory::create();
            $adapter->removeSubdomain($instance['slug']);
        } catch (\Throwable) {
            // Best effort
        }
    }

    /**
     * Recursive directory copy.
     */
    private static function recursiveCopy(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcPath  = $source . '/' . $file;
            $destPath = $dest . '/' . $file;

            if (is_dir($srcPath)) {
                self::recursiveCopy($srcPath, $destPath);
            } else {
                copy($srcPath, $destPath);
            }
        }
        closedir($dir);
    }

    /**
     * Recursive directory delete. Public for use by InstanceController cleanup.
     */
    public static function deleteDirectory(string $dir): void
    {
        self::recursiveDelete($dir);
    }

    /**
     * Recursive directory delete (internal).
     */
    private static function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                self::recursiveDelete($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
