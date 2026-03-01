<?php

declare(strict_types=1);

namespace Swarm\Adapters;

use Swarm\Models\Setting;

/**
 * AdapterFactory — Resolves the active control panel adapter from settings.
 */
class AdapterFactory
{
    public static function create(): ControlPanelAdapter
    {
        $adapter = Setting::get('control_panel_adapter', 'nginx');
        $config  = Setting::getJson('adapter_config', []);

        return match ($adapter) {
            'local'       => new LocalAdapter($config),
            'nginx'       => new NginxAdapter($config),
            'forge'       => new ForgeAdapter($config),
            'cpanel'      => new CpanelAdapter($config),
            'plesk'       => new PleskAdapter($config),
            'directadmin' => new DirectAdminAdapter($config),
            'cloudpanel'  => new CloudPanelAdapter($config),
            'hestiacp'    => new HestiaCPAdapter($config),
            'cyberpanel'  => new CyberPanelAdapter($config),
            default       => throw new \RuntimeException("Unknown adapter: {$adapter}"),
        };
    }
}
