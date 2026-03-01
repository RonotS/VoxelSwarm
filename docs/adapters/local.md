# Local Adapter (Herd / Valet)

**Status:** ✅ Working

The local adapter is for development only. It uses Laravel Herd or Valet's automatic `.test` domain routing — no configuration needed.

## How It Works

- **createSubdomain:** Creates a symlink from the Herd/Valet sites directory to the instance directory
- **removeSubdomain:** Removes the symlink
- **pauseSubdomain:** Removes the symlink (instance becomes inaccessible)
- **resumeSubdomain:** Re-creates the symlink

No API calls. No SSL configuration. Herd/Valet handle DNS and SSL for `*.test` domains automatically.

## Configuration

| Field | Value |
|-------|-------|
| Adapter | `local` |

No additional configuration required. The adapter auto-detects the Herd/Valet sites directory.

## Setup

1. Install [Laravel Herd](https://herd.laravel.com/) (macOS) or [Laravel Valet](https://laravel.com/docs/valet)
2. Set `base_domain` to your local domain (e.g., `voxelsite-swarm.test`)
3. Select the `local` adapter in Settings
4. Provision a test instance

Instances will be accessible at `{slug}.voxelsite-swarm.test`.

## Limitations

- Development only — not for production
- macOS only (Herd/Valet)
- Subdomain routing depends on Herd/Valet being configured
