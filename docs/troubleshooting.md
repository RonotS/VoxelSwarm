# Troubleshooting

## Provisioning Issues

### Instance stuck in "provisioning" status

If the PHP process was interrupted during provisioning (server restart, timeout), the instance may be stuck. In the operator dashboard, instances stuck for over 5 minutes show a "Retry" button.

**Manual fix:**
1. Check `storage/logs/provision-YYYY-MM-DD.log` for the last step completed
2. If the subdomain was created but health check failed, check DNS and SSL
3. Delete the failed instance from the dashboard and re-provision

### Health check fails after subdomain creation

The health checker makes an HTTP GET to `https://{slug}.{base_domain}/_studio/install.php` — it needs:

- ✅ DNS: wildcard record `*.yourdomain.com` pointing to your server
- ✅ SSL: wildcard certificate covering `*.yourdomain.com`
- ✅ Web server: subdomain routing correctly configured
- ✅ PHP: running and accessible

**Debug steps:**
1. Check if the subdomain resolves: `dig {slug}.yourdomain.com`
2. Check SSL: `curl -I https://{slug}.yourdomain.com`
3. Check the adapter log: `storage/logs/adapter-YYYY-MM-DD.log`

### Template copy fails

Usually a permissions issue. Ensure the PHP process has write access to `storage/instances/`.

```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

## Adapter Issues

### "Test Connection" fails

Each adapter has specific requirements. Check [docs/adapters/](adapters/README.md) for your adapter's configuration guide.

Common issues:
- **Nginx:** PHP process needs write access to conf directory and permission to reload Nginx
- **Forge:** API token must have site management permissions
- **cPanel:** WHM API token required (not cPanel token) with subdomain management privileges
- **Plesk:** API key with subscription management permissions

### Subdomain not accessible after creation

1. Check `storage/logs/adapter-YYYY-MM-DD.log` for the API response
2. Verify DNS propagation: `dig {slug}.yourdomain.com`
3. Some control panels need a few seconds for configuration to take effect — the health checker retries 3 times with 3-second intervals

## Email Issues

### Emails not sending

1. Check `storage/logs/mail-YYYY-MM-DD.log`
2. Verify SMTP settings in `/operator/settings`
3. Use "Send Test" to test the connection
4. If using Gmail: enable "App Passwords" (2FA required) or use an App Password

### Using the log driver for testing

Set email driver to `log` in settings. Emails are written to `storage/logs/mail-YYYY-MM-DD.log` instead of being sent. Useful for local development.

## Database Issues

### Database locked errors

VoxelSwarm uses SQLite with WAL mode. If you see "database is locked" errors:

1. Ensure only one server process accesses the database at a time
2. Check that `storage/swarm.db-wal` and `storage/swarm.db-shm` files exist (WAL mode)
3. SQLite handles VoxelSwarm's concurrency profile (rate-limited signups, single provisioning) easily

### Resetting the database

⚠️ This deletes all instance records (not the instance files):

```bash
rm storage/swarm.db
```

After this, opening VoxelSwarm in your browser will redirect to the web install wizard. Alternatively, re-run the CLI: `php scripts/install.php`

## Installation Issues

### Stuck on "Redirecting to /install"

The install guard redirects all requests to `/install` when VoxelSwarm is not installed. If the wizard page doesn't load:

1. Check that your web server routes all requests to `index.php`
2. Verify PHP is running: `php -v`
3. Check that `storage/` exists and is writable: `chmod -R 775 storage/`

### System check shows failures

The wizard checks PHP extensions before allowing installation. Install missing extensions through your OS package manager:

```bash
# Ubuntu/Debian
sudo apt install php8.2-sqlite3 php8.2-mbstring php8.2-curl php8.2-zip

# CentOS/RHEL
sudo dnf install php-pdo php-mbstring php-curl php-zip
```

Restart PHP-FPM or Apache after installing extensions.

### Installation fails at "Setting up VoxelSwarm"

Check the browser console for error details. Common causes:
- `storage/` not writable — fix with `chmod -R 775 storage/`
- SQLite extension not loaded — install `php-pdo` and `php-sqlite3`
- PHP memory limit too low — set `memory_limit = 128M` in `php.ini`

## Getting Help

- **Check logs first:** `storage/logs/` contains detailed information about every operation
- **Open an issue:** [github.com/NowSquare/VoxelSwarm/issues](https://github.com/NowSquare/VoxelSwarm/issues) — include your hosting environment, PHP version, adapter, and relevant log entries
- **VoxelSite:** [voxelsite.com](https://voxelsite.com) — for VoxelSite-specific issues (not VoxelSwarm adapter issues)
