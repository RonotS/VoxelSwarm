# Plesk Adapter

**Status:** 🧪 Testing — we need community testing across different Plesk editions and server setups.

The Plesk adapter uses the [Plesk REST API](https://docs.plesk.com/en-US/obsidian/api-rpc/) to create subdomains and manage hosting.

## How It Works

- **createSubdomain:** Creates a subdomain under the operator's Plesk subscription via REST API
- **removeSubdomain:** Removes the subdomain via REST API
- **pauseSubdomain:** Sets the subdomain to maintenance mode via Plesk API
- **resumeSubdomain:** Removes maintenance mode

SSL is handled via Plesk's built-in Let's Encrypt integration.

## Prerequisites

- Plesk Obsidian (latest recommended)
- Plesk API key with subscription management permissions
- Wildcard DNS: `*.yourdomain.com` → your server IP

## Configuration

| Field | Description | Example |
|-------|-------------|---------|
| `plesk_hostname` | Plesk server hostname | `server.yourdomain.com` |
| `plesk_port` | Plesk port (usually 8443) | `8443` |
| `plesk_api_key` | Plesk API key | `your-api-key-here` |

### Getting a Plesk API Key

1. Log in to Plesk
2. Go to Tools & Settings → API
3. Create a new API key
4. Copy the key

## Troubleshooting

- **Authentication errors:** Verify API key is valid and has correct permissions
- **SSL not provisioning:** Check Plesk → Let's Encrypt → ensure the extension is enabled
- **Subdomain limit reached:** Check your Plesk subscription limits

## Help Us Test

If you're running Plesk, please test VoxelSwarm and [report any issues](https://github.com/NowSquare/VoxelSwarm/issues) with:
- Your Plesk version and edition
- Server OS
- Any error messages from `storage/logs/adapter-YYYY-MM-DD.log`
