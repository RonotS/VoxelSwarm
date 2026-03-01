<?php

declare(strict_types=1);

namespace Swarm\Controllers;

use Swarm\Database;
use Swarm\Helpers\Response;
use Swarm\Helpers\Validator;
use Swarm\Middleware\Csrf;
use Swarm\Models\Instance;
use Swarm\Models\Setting;
use Swarm\Services\Provisioner;
use Swarm\Services\SubdomainGenerator;
use Swarm\Adapters\AdapterFactory;

/**
 * InstanceController — Instance CRUD for the operator dashboard.
 */
class InstanceController
{
    /**
     * GET /operator/instances — Filterable instance list.
     */
    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'type'   => $_GET['type']   ?? null,
            'search' => $_GET['search'] ?? null,
        ];

        $instances = Instance::list($filters);

        Response::view('operator/instances', [
            'instances'  => $instances,
            'filters'    => $filters,
            'csrfField'  => Csrf::field(),
        ], 'operator');
    }

    /**
     * POST /operator/instances — Create a new demo instance.
     */
    public function store(): void
    {
        Csrf::validate();

        $errors = Validator::validate($_POST, [
            'name' => 'required|string|min:2|max:80',
        ]);

        if (!empty($errors)) {
            Response::json(['error' => reset($errors)], 422);
        }

        $name  = trim($_POST['name']);
        $slug  = SubdomainGenerator::generate($name);

        $instanceId = Instance::create([
            'slug'   => $slug,
            'name'   => $name,
            'email'  => Setting::get('operator_email', 'operator@localhost'),
            'status' => 'queued',
            'type'   => 'demo',
        ]);

        // Provision in background
        if (function_exists('fastcgi_finish_request')) {
            Response::json(['id' => $instanceId, 'slug' => $slug]);
            fastcgi_finish_request();
        } else {
            ignore_user_abort(true);
            Response::json(['id' => $instanceId, 'slug' => $slug]);
        }

        Provisioner::run($instanceId);
    }

    /**
     * GET /operator/instances/{id} — Instance detail.
     */
    public function show(string $id): void
    {
        $instance = Instance::find((int) $id);
        if (!$instance) {
            Response::redirect('/operator/instances');
        }

        $logs = Database::query(
            'SELECT * FROM provision_logs WHERE instance_id = ? ORDER BY created_at ASC',
            [(int) $id]
        )->fetchAll();

        $baseDomain = Setting::get('base_domain', 'localhost');

        Response::view('operator/instance-detail', [
            'instance'   => $instance,
            'logs'       => $logs,
            'baseDomain' => $baseDomain,
            'csrfField'  => Csrf::field(),
        ], 'operator');
    }

    /**
     * PATCH /operator/instances/{id} — Update instance (notes, type).
     */
    public function update(string $id): void
    {
        Csrf::validate();

        $instance = Instance::find((int) $id);
        if (!$instance) {
            Response::json(['error' => 'Instance not found'], 404);
        }

        $updates = [];
        if (isset($_POST['notes'])) {
            $updates['notes'] = $_POST['notes'];
        }

        if (!empty($updates)) {
            Instance::update((int) $id, $updates);
        }

        Response::json(['ok' => true]);
    }

    /**
     * DELETE /operator/instances/{id} — Delete an instance permanently.
     */
    public function destroy(string $id): void
    {
        Csrf::validate();

        $instance = Instance::find((int) $id);
        if (!$instance) {
            Response::json(['error' => 'Instance not found'], 404);
        }

        // Remove subdomain routing
        try {
            $adapter = AdapterFactory::create();
            $adapter->removeSubdomain($instance['slug']);
        } catch (\Throwable $e) {
            \Swarm\Logger::error('adapter', 'Failed to remove subdomain on delete', [
                'slug'  => $instance['slug'],
                'error' => $e->getMessage(),
            ]);
        }

        // Remove instance directory from disk
        $instancePath = $instance['document_root']
            ?? (Setting::get('instances_path', SWARM_STORAGE . '/instances') . '/' . $instance['slug']);
        if (is_dir($instancePath)) {
            Provisioner::deleteDirectory($instancePath);
            \Swarm\Logger::info('instance', 'Deleted instance directory', ['slug' => $instance['slug']]);
        }

        // Delete the database row
        Instance::hardDelete((int) $id);

        Response::json(['ok' => true]);
    }

    /**
     * POST /operator/instances/{id}/pause — Pause an instance.
     */
    public function pause(string $id): void
    {
        Csrf::validate();

        $instance = Instance::find((int) $id);
        if (!$instance || $instance['status'] !== 'active') {
            Response::json(['error' => 'Cannot pause this instance'], 422);
        }

        try {
            $adapter = AdapterFactory::create();
            $adapter->pauseSubdomain($instance['slug']);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Failed to pause: ' . $e->getMessage()], 500);
        }

        Instance::update((int) $id, ['status' => 'paused']);

        Response::json(['ok' => true, 'status' => 'paused']);
    }

    /**
     * POST /operator/instances/{id}/resume — Resume a paused instance.
     */
    public function resume(string $id): void
    {
        Csrf::validate();

        $instance = Instance::find((int) $id);
        if (!$instance || $instance['status'] !== 'paused') {
            Response::json(['error' => 'Cannot resume this instance'], 422);
        }

        try {
            $adapter = AdapterFactory::create();
            $adapter->resumeSubdomain($instance['slug']);
        } catch (\Throwable $e) {
            Response::json(['error' => 'Failed to resume: ' . $e->getMessage()], 500);
        }

        Instance::update((int) $id, ['status' => 'active']);

        Response::json(['ok' => true, 'status' => 'active']);
    }

    /**
     * POST /operator/instances/{id}/gallery — Mark as gallery demo.
     */
    public function markGallery(string $id): void
    {
        Csrf::validate();

        $instance = Instance::find((int) $id);
        if (!$instance || $instance['status'] !== 'active') {
            Response::json(['error' => 'Instance must be active to mark as gallery'], 422);
        }

        Instance::update((int) $id, ['type' => 'gallery']);

        Response::json(['ok' => true, 'type' => 'gallery']);
    }
}
