# Configuration

VoxelSwarm stores all configuration in the SQLite database (`storage/swarm.db`) via the `settings` table. The install wizard populates these during setup. You can change them anytime from the operator dashboard at `/operator/settings`.

## Settings Reference

### General

| Setting | Description | Default |
|---------|-------------|---------|
| `base_domain` | Your Swarm domain (e.g. `voxelsite.io`) | Set during install |
| `instances_path` | Where instances are stored | `storage/instances` |
| `template_path` | Path to prepared VoxelSite template | `template/voxelsite` |
| `max_instances` | Maximum number of instances allowed | `100` |
| `signups_enabled` | Whether the public signup form is active | `true` |
| `gallery_enabled` | Whether the public gallery is visible | `true` |
| `operator_email` | Email for provisioning failure notifications | Set during install |

### Control Panel

| Setting | Description |
|---------|-------------|
| `control_panel_adapter` | Active adapter: `local`, `nginx`, `forge`, `cpanel`, `plesk` |
| `adapter_config` | JSON object with adapter-specific credentials (encrypted) |

See [adapters/README.md](adapters/README.md) for adapter-specific configuration.

### Email

| Setting | Description |
|---------|-------------|
| `mail_driver` | `smtp`, `log`, or `null` |
| `mail_config` | JSON with SMTP host, port, username, password (encrypted), encryption |

When `mail_driver` is `log`, emails are written to `storage/logs/mail-YYYY-MM-DD.log` instead of being sent. Useful for testing.

### Security

| Setting | Description |
|---------|-------------|
| `app_key` | 32-byte hex key for AES-256-CBC encryption. Generated during install. **Do not change this** after adapter credentials are stored — they'll become unreadable. |
| `operator_password_hash` | bcrypt hash of the operator password |

## Environment File

VoxelSwarm uses a minimal `.env` file for bootstrap-level configuration only:

```env
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

All other configuration lives in the database. This is intentional — it means settings can be changed from the dashboard without SSH access.

## Changing Settings

### Via Dashboard

Visit `/operator/settings`. Changes take effect immediately.

### Via CLI

```bash
php -r "
require 'src/bootstrap.php';
Swarm\Models\Setting::set('signups_enabled', 'false');
echo 'Done.';
"
```

## Sensitive Data

Adapter credentials (API tokens, passwords) and SMTP passwords are encrypted at rest using AES-256-CBC with the `app_key`. They are decrypted only in memory during use and never logged.

The Settings view never displays decrypted values — it shows masked inputs with a "Change" button to update.
