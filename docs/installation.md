# Installation

## Requirements

- **PHP 8.1+** with extensions:
  - `pdo_sqlite` — database
  - `mbstring` — string handling
  - `openssl` — encryption
  - `fileinfo` — MIME detection
  - `zip` — template extraction
  - `curl` — health checks and API calls
- **Web server:** Nginx (recommended) or Apache with `mod_rewrite`
- **Wildcard DNS:** `*.yourdomain.com` → your server IP
- **Wildcard SSL:** for your domain (method depends on your adapter)
- **VoxelSite license:** purchased from [CodeCanyon](https://voxelsite.com/buy)

No MySQL. No Node.js. No Redis. No Composer on the server.

## Step 1: Clone VoxelSwarm

```bash
git clone https://github.com/NowSquare/VoxelSwarm.git
cd VoxelSwarm
```

If you downloaded the ZIP instead:

```bash
unzip voxelswarm.zip -d VoxelSwarm
cd VoxelSwarm
```

All Composer dependencies are included in the `vendor/` directory. **No `composer install` needed.** The repo works out of the box.

## Step 2: Point Your Domain

Point your domain to the VoxelSwarm directory before running the installer.

### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/VoxelSwarm;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Apache

The included `.htaccess` handles URL rewriting. Ensure `mod_rewrite` is enabled.

### Laravel Herd (local development)

Link the directory in Herd and visit `https://voxelsite-swarm.test`.

## Step 3: Run the Setup Wizard

Open `https://yourdomain.com` in your browser. VoxelSwarm detects that it's not yet installed and redirects you to the **web-based setup wizard**.

The wizard walks you through:
1. **System check** — PHP version, extensions, directory permissions
2. **Configuration** — base domain, operator email and password, control panel adapter, email settings
3. **Installation** — runs migrations, generates encryption key, creates your operator account, and auto-logs you in

After installation, you're dropped directly into the operator dashboard.

### Alternative: CLI Install

If you prefer the command line:

```bash
php scripts/install.php
```

## Step 4: Prepare a VoxelSite Template

Upload a VoxelSite ZIP to `template/voxelsite/` via FTP/SSH, then:

- **Via dashboard:** Visit `/operator/templates`, click **Process** on the ZIP
- **Via CLI:** `php scripts/prepare-template.php /path/to/voxelsite.zip`

**You must prepare a template before any instances can be provisioned.**

See [Template Preparation](template-preparation.md) for details.

## Step 5: Provision Your First Instance

1. Visit `/operator` and log in
2. Click "New Demo Instance" on the dashboard
3. Enter a test name
4. Watch the provisioning progress
5. Visit the resulting instance URL

## Next Steps

- [Configure your adapter](adapters/README.md)
- [Learn about template preparation](template-preparation.md)
- [Explore the operator dashboard](operator-dashboard.md)

---

## For Developers Only

If you want to modify VoxelSwarm's CSS or JavaScript (the compiled `build/` assets), you'll need Node.js and npm:

```bash
npm install
npm run dev    # Vite watch mode for Tailwind + Alpine.js
```

This is **not required** for running VoxelSwarm. The `build/` directory already contains pre-compiled CSS and JS. Only run this if you're contributing to VoxelSwarm's UI or developing adapter settings views.
