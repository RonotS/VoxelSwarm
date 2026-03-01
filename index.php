<?php

declare(strict_types=1);

/**
 * VoxelSwarm — Front Controller
 *
 * All requests route through here via .htaccess (Apache) or
 * try_files (Nginx). Loads bootstrap, registers routes, dispatches.
 */

require_once __DIR__ . '/src/bootstrap.php';

use Swarm\Router;
use Swarm\Controllers\LandingController;
use Swarm\Controllers\SignupController;
use Swarm\Controllers\StatusController;
use Swarm\Controllers\GalleryController;
use Swarm\Controllers\AuthController;
use Swarm\Controllers\DashboardController;
use Swarm\Controllers\InstanceController;
use Swarm\Controllers\InstallController;
use Swarm\Controllers\SettingsController;
use Swarm\Controllers\TemplateController;

$router = new Router();

// ── Install guard: redirect all requests to /install if not installed ──
$requestUri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$isInstallRoute = str_starts_with($requestUri, '/install');
$isAssetRoute = preg_match('/\.(css|js|png|jpg|svg|ico|woff2?)$/', $requestUri);

if (!isInstalled() && !$isInstallRoute && !$isAssetRoute) {
    header('Location: /install');
    exit;
}

// ── Install wizard (only accessible when not installed) ──
$router->get('/install',               [InstallController::class,  'index']);
$router->post('/install/check',        [InstallController::class,  'check']);
$router->post('/install/test-adapter', [InstallController::class,  'testAdapter']);
$router->post('/install/complete',     [InstallController::class,  'complete']);

// ── Public ──
$router->get('/',                        [LandingController::class,   'index']);
$router->get('/signup',                  [SignupController::class,    'index']);
$router->post('/signup',                 [SignupController::class,    'store'],  ['throttle:signup']);
$router->get('/status/{id}',             [StatusController::class,    'show']);
$router->get('/api/status/{id}',         [StatusController::class,    'json']);
$router->get('/gallery',                 [GalleryController::class,   'index']);

// ── Operator auth ──
$router->get('/operator/login',          [AuthController::class,      'show']);
$router->post('/operator/login',         [AuthController::class,      'store'],  ['throttle:login']);
$router->post('/operator/logout',        [AuthController::class,      'destroy']);

// ── Operator dashboard (session-protected) ──
$router->group(['prefix' => '/operator', 'middleware' => ['auth']], function (Router $r) {
    $r->get('/',                              [DashboardController::class,  'index']);
    $r->get('/instances',                     [InstanceController::class,   'index']);
    $r->post('/instances',                    [InstanceController::class,   'store']);
    $r->get('/instances/{id}',                [InstanceController::class,   'show']);
    $r->patch('/instances/{id}',              [InstanceController::class,   'update']);
    $r->delete('/instances/{id}',             [InstanceController::class,   'destroy']);
    $r->post('/instances/{id}/pause',         [InstanceController::class,   'pause']);
    $r->post('/instances/{id}/resume',        [InstanceController::class,   'resume']);
    $r->post('/instances/{id}/gallery',       [InstanceController::class,   'markGallery']);
    $r->get('/templates',                     [TemplateController::class,   'index']);
    $r->post('/templates/process',            [TemplateController::class,   'process']);
    $r->post('/templates/activate',           [TemplateController::class,   'activate']);
    $r->post('/templates/delete-zip',         [TemplateController::class,   'deleteZip']);
    $r->post('/templates/delete-version',     [TemplateController::class,   'deleteVersion']);
    $r->get('/settings',                      [SettingsController::class,   'index']);
    $r->put('/settings',                      [SettingsController::class,   'update']);
    $r->post('/settings/adapter/test',        [SettingsController::class,   'testAdapter']);
    $r->post('/settings/mail/test',           [SettingsController::class,   'testMail']);
});

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
