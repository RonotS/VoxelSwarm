<?php

declare(strict_types=1);

namespace Swarm\Adapters;

use Swarm\Logger;
use Swarm\Models\Setting;

/**
 * LocalAdapter — No-op adapter for local development (Herd, Valet, etc).
 *
 * Does not create real subdomains. Instead, logs what it would do
 * and marks the subdomain step as complete. Perfect for testing the
 * full provisioning flow on localhost without Nginx, Forge, etc.
 */
class LocalAdapter implements ControlPanelAdapter
{
    private string $baseDomain;

    public function __construct(array $config = [])
    {
        $this->baseDomain = Setting::get('base_domain', 'localhost');
    }

    public function createSubdomain(string $slug, string $documentRoot): void
    {
        Logger::info('adapter', 'LocalAdapter: subdomain created (no-op)', [
            'slug'         => $slug,
            'subdomain'    => "{$slug}.{$this->baseDomain}",
            'document_root' => $documentRoot,
        ]);
    }

    public function removeSubdomain(string $slug): void
    {
        Logger::info('adapter', 'LocalAdapter: subdomain removed (no-op)', ['slug' => $slug]);
    }

    public function pauseSubdomain(string $slug): void
    {
        Logger::info('adapter', 'LocalAdapter: subdomain paused (no-op)', ['slug' => $slug]);
    }

    public function resumeSubdomain(string $slug): void
    {
        Logger::info('adapter', 'LocalAdapter: subdomain resumed (no-op)', ['slug' => $slug]);
    }

    public function verify(): array
    {
        return [
            'ok'      => true,
            'message' => 'Local adapter — no subdomain management. Instances are provisioned but not publicly accessible via subdomain.',
        ];
    }
}
