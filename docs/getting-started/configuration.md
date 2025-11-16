# Configuration

- [Introduction](#introduction)
- [Environment Configuration](#environment-configuration)
- [Configuration Options](#configuration-options)
- [Storage Configuration](#storage-configuration)
- [Logging Configuration](#logging-configuration)
- [Structurizr CLI Configuration](#structurizr-cli-configuration)
- [Advanced Configuration](#advanced-configuration)

---

## Introduction

Structurizr MCP Server is configured primarily through environment variables. This approach allows for flexible deployment across different environments without code changes.

---

## Environment Configuration

### Creating the Configuration File

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` to customize your configuration:

```bash
nano .env
# or
vim .env
```

> **Note:** The `.env` file is optional. All settings have sensible defaults and can be configured via environment variables directly.

### Environment Variables Overview

```env
# Server Information
SERVER_NAME=structurizr-mcp-server
SERVER_VERSION=1.0.0

# Storage Paths
WORKSPACE_STORAGE_PATH=./workspaces

# Logging
LOG_LEVEL=INFO
LOG_PATH=php://stderr

# Structurizr CLI (Optional)
STRUCTURIZR_CLI_PATH=./bin/structurizr-cli.sh

# Structurizr Cloud (Optional)
STRUCTURIZR_API_KEY=
STRUCTURIZR_API_SECRET=
STRUCTURIZR_API_URL=https://api.structurizr.com
STRUCTURIZR_WORKSPACE_ID=
```

---

## Configuration Options

### Server Information

#### `SERVER_NAME`

**Default:** `structurizr-mcp-server`

The name of your MCP server instance.

```env
SERVER_NAME=my-architecture-server
```

#### `SERVER_VERSION`

**Default:** `1.0.0`

Version identifier for your server instance.

```env
SERVER_VERSION=1.0.0
```

---

## Storage Configuration

### `WORKSPACE_STORAGE_PATH`

**Default:** `./workspaces`

Directory where workspace JSON files are stored.

```env
WORKSPACE_STORAGE_PATH=/var/lib/structurizr/workspaces
```

> **Important:** Ensure this directory is writable by the PHP process.

**Absolute vs Relative Paths:**

```env
# Relative path (from server.php location)
WORKSPACE_STORAGE_PATH=./workspaces

# Absolute path (recommended for production)
WORKSPACE_STORAGE_PATH=/var/lib/structurizr/workspaces
```

---

## Logging Configuration

### `LOG_LEVEL`

**Default:** `INFO`

Controls logging verbosity.

**Available levels:**
- `DEBUG` - All messages (very verbose)
- `INFO` - General information messages
- `WARNING` - Warning messages only
- `ERROR` - Error messages only

```env
# Development
LOG_LEVEL=DEBUG

# Production
LOG_LEVEL=INFO
```

### `LOG_PATH`

**Default:** `php://stderr`

Where logs are written.

**Options:**

```env
# Standard error (default, recommended for Docker/systemd)
LOG_PATH=php://stderr

# File path
LOG_PATH=/var/log/structurizr-mcp.log

# Standard output
LOG_PATH=php://stdout
```

> **Tip:** Using `php://stderr` allows log aggregation systems (like Docker logs) to capture output automatically.

---

## Structurizr CLI Configuration

### `STRUCTURIZR_CLI_PATH`

**Default:** `./bin/structurizr-cli.sh`

Path to the Structurizr CLI executable.

```env
# macOS/Linux
STRUCTURIZR_CLI_PATH=./bin/structurizr.sh

# Windows
STRUCTURIZR_CLI_PATH=./bin/structurizr.bat

# Absolute path
STRUCTURIZR_CLI_PATH=/usr/local/bin/structurizr
```

> **Note:** The CLI is optional. Without it, PlantUML and Mermaid export features will be unavailable.

---

## Advanced Configuration

### Structurizr Cloud Integration

For cloud sync features, configure your Structurizr Cloud credentials:

#### `STRUCTURIZR_API_KEY`

Your Structurizr Cloud API key.

```env
STRUCTURIZR_API_KEY=your-api-key-here
```

#### `STRUCTURIZR_API_SECRET`

Your Structurizr Cloud API secret.

```env
STRUCTURIZR_API_SECRET=your-api-secret-here
```

> **Warning:** Keep your API credentials secure. Never commit them to version control.

#### `STRUCTURIZR_API_URL`

**Default:** `https://api.structurizr.com`

Structurizr Cloud API endpoint.

```env
# Cloud (default)
STRUCTURIZR_API_URL=https://api.structurizr.com

# On-premises
STRUCTURIZR_API_URL=https://structurizr.example.com
```

#### `STRUCTURIZR_WORKSPACE_ID`

Default workspace ID for cloud operations.

```env
STRUCTURIZR_WORKSPACE_ID=12345
```

---

## Configuration Examples

### Development Environment

Verbose logging, relative paths:

```env
SERVER_NAME=structurizr-dev
LOG_LEVEL=DEBUG
LOG_PATH=php://stderr
WORKSPACE_STORAGE_PATH=./workspaces
STRUCTURIZR_CLI_PATH=./bin/structurizr.sh
```

### Production Environment

Minimal logging, absolute paths, cloud integration:

```env
SERVER_NAME=structurizr-production
SERVER_VERSION=1.0.0
LOG_LEVEL=WARNING
LOG_PATH=/var/log/structurizr-mcp.log
WORKSPACE_STORAGE_PATH=/var/lib/structurizr/workspaces
STRUCTURIZR_CLI_PATH=/usr/local/bin/structurizr
STRUCTURIZR_API_KEY=prod-api-key
STRUCTURIZR_API_SECRET=prod-api-secret
```

### Docker Environment

```env
SERVER_NAME=structurizr-docker
LOG_PATH=php://stderr
WORKSPACE_STORAGE_PATH=/data/workspaces
STRUCTURIZR_CLI_PATH=/usr/local/bin/structurizr
```

---

## Environment Variable Priority

Configuration is loaded in the following order (later sources override earlier ones):

1. **Default values** (hardcoded in `Configuration.php`)
2. **`.env` file** (if present)
3. **System environment variables** (highest priority)

This allows you to:
- Use `.env` for local development
- Override with environment variables in production/Docker

---

## Security Considerations

> **Warning:** Never commit sensitive credentials to version control.

**Best practices:**

1. **Add `.env` to `.gitignore`**

```gitignore
.env
.env.local
```

2. **Use environment variables in production**

Don't use `.env` files in production. Set environment variables directly:

```bash
# systemd service
Environment="STRUCTURIZR_API_KEY=your-key"
Environment="STRUCTURIZR_API_SECRET=your-secret"
```

3. **Restrict file permissions**

```bash
chmod 600 .env
```

---

## Next Steps

Now that you've configured the server, you're ready to:

- **Set up Claude Desktop** → [Claude Desktop Setup](claude-desktop.md)
- **Create your first workspace** → [Quick Start Guide](quick-start.md)
- **Learn about architecture** → [How MCP Works](../architecture/mcp-overview.md)

---

<p align="right">
  <strong>Next:</strong> <a href="quick-start.md">Quick Start →</a>
</p>
