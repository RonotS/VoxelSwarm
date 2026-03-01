<?php

declare(strict_types=1);

namespace Swarm\Controllers;

use Swarm\Helpers\Response;
use Swarm\Models\Instance;
use Swarm\Models\Setting;

/**
 * LandingController — The SaaS homepage.
 *
 * Renders the landing page with live stats from the database.
 * If signups are disabled, the CTA text changes to "Coming soon."
 */
class LandingController
{
    /**
     * GET / — Show the landing page.
     */
    public function index(): void
    {
        $signupsEnabled = Setting::get('signups_enabled', 'true') === 'true';
        $counts = Instance::countByStatus();

        // No layout — the landing page is a self-contained document
        Response::view('landing', [
            'signupsEnabled' => $signupsEnabled,
            'totalInstances' => $counts['total'],
            'activeInstances' => $counts['active'],
        ]);
    }
}
