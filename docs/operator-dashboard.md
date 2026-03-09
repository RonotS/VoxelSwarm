# Operator Dashboard

The operator dashboard is your control center for managing VoxelSwarm. Access it at `https://yourdomain.com/operator`.

For the full route inventory, visibility rules, and public-page map, see [page-map.md](page-map.md).

## Authentication

Single operator password, set during installation. Session lasts 30 days. Rate limited to 5 login attempts per IP per 15 minutes.

To change the password, visit `/operator/account`.

## Dashboard Overview

The main dashboard has two modes:

**Onboarding** (first visit): A guided 3-step setup — Prepare a template, Configure deployment, Create your first instance.

**Active** (instances exist):
- **Summary cards:** Total instances, Active, Paused, Storage used
- **Recent activity:** Last 10 provisioning events
- **Quick action:** "New Instance" button

Sidebar navigation groups: Dashboard + Instances, Templates + Deployment (CONFIG), Account + System (SYSTEM). Mobile-responsive with slide-in sidebar and backdrop overlay.

## Instance Management

### Instance List (`/operator/instances`)

Filterable table showing all instances:
- Identifier, name, email, status badge, type badge, created date
- **Filters:** Status (all/active/paused/provisioning/failed), Search by name or email
- **Per-row actions:** Click row to view detail page

### Instance Detail (`/operator/instances/{id}`)

- **Header:** Instance name, status badge with colored dot, slug in monospace
- **Details card:** Structured list of icon-led rows (identifier, URL, email, type, created, provisioned)
- **Actions:** Pause when active, Resume when paused, Delete with confirmation
- **Provision Log:** Card-wrapped deployment history table
- **Notes:** Private operator notes with toast notification on save

Adapter caveat: the UI exposes Pause/Resume broadly, but adapter support varies. Nginx rewrites the conf into a maintenance response, the local adapter only logs the action, and Forge/cPanel/Plesk currently warn rather than fully toggling availability.

### Instance Lifecycle

| Status | Meaning |
|--------|---------|
| `queued` | Instance created, provisioning not started |
| `provisioning` | Currently being set up |
| `active` | Live and accessible |
| `paused` | Temporarily disabled (shows holding page) |
| `failed` | Provisioning failed (see logs) |

### Creating Instances

From the dashboard or instances page: "New Instance" → enter an identifier (subdomain or folder name depending on configured adapter), optional name and email → Swarm provisions immediately.

The modal adapts to the configured adapter:
- **Domain adapters** (Nginx, Forge, cPanel, Plesk): Shows a subdomain input with `.basedomain.com` suffix and live preview
- **Filesystem adapter**: Shows the instances root path prefix with a folder name input

Implementation note: the controller has a gallery-marking endpoint, but the current operator UI does not expose a "Mark as Gallery Demo" action.

## Template Management (`/operator/templates`)

Manage VoxelSite versions from the dashboard. Two sections:

### Prepared Versions

Shows all extracted VoxelSite versions:
- **Version number**, directory name, size on disk
- **Active badge** — the version used for new instances
- **Actions:** Activate (switch new instances to this version), Delete

The **active** version cannot be deleted. Existing instances are not affected by version changes — they contain their own VoxelSite installation.

### Available ZIPs

Shows VoxelSite ZIP files in `template/voxelsite/`:
- **Filename**, file size, modification date
- **Process** — extracts the ZIP, reads the `VERSION` file inside, prepares the template, sets up image library, activates the version
- **Delete** — removes the ZIP file

ZIPs can have any filename (e.g., `codecanyon-yi8z1J7A-...zip`). VoxelSwarm reads the `VERSION` file from inside the ZIP to determine the version.

Upload ZIPs to the server via FTP/SSH, then process them from this page.

## Deployment (`/operator/deployment`)

| Section | What you configure |
|---------|-------------------|
| **Adapter** | Control panel adapter + adapter-specific config. "Test Connection" tests the currently visible form values, not just the saved ones. |
| **Public Site** | Landing page toggle + signups toggle |
| **Notifications** | Email driver (SMTP / Log / Disabled) + SMTP config + "Send Test Email" |

## Account (`/operator/account`)

| Section | What you configure |
|---------|-------------------|
| **Email Address** | Operator email. Receives system notifications. |
| **Password** | Current + new password. |

## System (`/operator/system`)

| Section | What you see |
|---------|-------------------|
| **System Status** | VoxelSwarm version, PHP version, SQLite version, database size |
| **Update** | Git availability, `Pull Latest`, and raw pull output |
| **Server Logs** | Download and delete `.log` files from `storage/logs/` |
| **Danger Zone** | Refresh Installation (purge instances but keep account/settings), Reset Installation (full wipe) |

## Logs

Logs are stored in `storage/logs/` with daily rotation:

| Log file | Contents |
|----------|----------|
| `provision-YYYY-MM-DD.log` | Every provisioning step |
| `adapter-YYYY-MM-DD.log` | Control panel API calls |
| `swarm-YYYY-MM-DD.log` | Settings changes, operator actions |
| `mail-YYYY-MM-DD.log` | Email sends and failures |

The System page can download or delete `.log` files. Retention is still manual or external to the app.
