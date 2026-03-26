<?php

declare(strict_types=1);

namespace Swarm\Adapters;

use Swarm\Logger;
use Swarm\Models\Setting;

/**
 * RailwayAdapter — Railway container adapter for VoxelSwarm.
 *
 * Railway handles wildcard DNS and SSL at the platform level.
 * All *.yourdomain.com traffic is routed to the container.
 * Inside the container, Nginx routes based on the Host header
 * to the matching instance directory on the persistent volume.
 *
 * This adapter is filesystem-only — no external API calls needed.
 * Subdomain routing is automatic: if the instance directory exists
 * at /data/instances/{slug}, Nginx serves from it.
 *
 * Config:
 *   instances_root — Absolute path where instances are deployed.
 *                    Defaults to /data/instances (Railway volume).
 */
class RailwayAdapter implements ControlPanelAdapter
{
    private string $baseDomain;
    private string $instancesRoot;

    public function __construct(array $config = [])
    {
        $this->baseDomain    = Setting::get('base_domain', 'localhost');
        $this->instancesRoot = $config['instances_root'] ?? '/data/instances';

        // Persist so the Provisioner uses the Railway volume path
        if (!empty($this->instancesRoot)) {
            Setting::set('instances_path', $this->instancesRoot);
        }
    }

    /**
     * Create routing for a new subdomain.
     *
     * On Railway, no action is needed here — Nginx inside the container
     * automatically routes based on Host header → directory existence.
     * The Provisioner already copies the template to the instances dir.
     */
    public function createSubdomain(string $slug, string $documentRoot): void
    {
        Logger::info('adapter', 'RailwayAdapter: instance directory ready — wildcard routing active', [
            'slug'          => $slug,
            'subdomain'     => "{$slug}.{$this->baseDomain}",
            'document_root' => $documentRoot,
        ]);
    }

    /**
     * Remove subdomain routing.
     *
     * Instance directory removal is handled by the Provisioner's cleanup.
     * Once the directory is gone, Nginx automatically stops routing to it.
     */
    public function removeSubdomain(string $slug): void
    {
        Logger::info('adapter', 'RailwayAdapter: subdomain removed (directory-based routing)', [
            'slug' => $slug,
        ]);
    }

    /**
     * Pause an instance by creating a .paused marker file.
     * Nginx checks for this file and returns a 503 maintenance page.
     */
    public function pauseSubdomain(string $slug): void
    {
        $markerPath = $this->instancesRoot . '/' . $slug . '/.paused';
        file_put_contents($markerPath, json_encode([
            'paused_at' => date('c'),
            'slug'      => $slug,
        ]));

        Logger::info('adapter', 'RailwayAdapter: instance paused', ['slug' => $slug]);
    }

    /**
     * Resume a paused instance by removing the .paused marker file.
     */
    public function resumeSubdomain(string $slug): void
    {
        $markerPath = $this->instancesRoot . '/' . $slug . '/.paused';

        if (file_exists($markerPath)) {
            unlink($markerPath);
        }

        Logger::info('adapter', 'RailwayAdapter: instance resumed', ['slug' => $slug]);
    }

    /**
     * Verify the adapter can operate correctly.
     * Checks that the Railway volume is mounted and writable.
     */
    public function verify(): array
    {
        $path = $this->instancesRoot;

        // Check if volume is mounted
        if (!is_dir(dirname($path))) {
            return [
                'ok'      => false,
                'message' => "Railway volume not mounted. Expected mount at: " . dirname($path)
                           . ". Create a volume in Railway and set mount path to /data.",
            ];
        }

        // Ensure instances directory exists
        if (!is_dir($path)) {
            if (!@mkdir($path, 0755, true)) {
                return [
                    'ok'      => false,
                    'message' => "Cannot create instances directory: {$path}. Check Railway volume permissions.",
                ];
            }
        }

        // Check writable
        if (!is_writable($path)) {
            return [
                'ok'      => false,
                'message' => "Instances directory not writable: {$path}. "
                           . "If using a non-root user, set RAILWAY_RUN_UID=0 in Railway variables.",
            ];
        }

        // Check database path is also writable
        $dbDir = dirname(SWARM_DB_PATH);
        if (!is_writable($dbDir)) {
            return [
                'ok'      => false,
                'message' => "Storage directory not writable: {$dbDir}. Check volume symlinks.",
            ];
        }

        return [
            'ok'      => true,
            'message' => "Railway adapter verified. Volume mounted and writable. "
                       . "Instances deploy to: {$path}. "
                       . "Wildcard subdomain routing handled by container Nginx.",
        ];
    }
}
