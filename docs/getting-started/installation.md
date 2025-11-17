# Installation

- [Prerequisites](#prerequisites)
- [Installing Structurizr MCP Server](#installing-structurizr-mcp-server)
- [Structurizr CLI (Optional)](#structurizr-cli-optional)
- [Verifying Installation](#verifying-installation)
- [Next Steps](#next-steps)

---

## Prerequisites

Before installing Structurizr MCP Server, ensure your system meets the following requirements:

- **PHP 8.1 or higher** - The server requires modern PHP features
- **Composer** - PHP dependency manager
- **Claude Desktop or Claude Code** - MCP-compatible client

> **Note:** This server uses the Model Context Protocol (MCP) and requires an MCP-compatible client to function. Claude Desktop and Claude Code are the recommended clients.

### Checking Your PHP Version

You can verify your PHP version by running:

```bash
php -v
```

You should see output similar to:

```
PHP 8.1.27 (cli) (built: Dec 20 2023 20:78:57) ( NTS )
```

If you need to install or upgrade PHP, visit [php.net](https://www.php.net/downloads) for instructions.

---

## Installing Structurizr MCP Server

### Via Git Clone

The recommended installation method is cloning the repository:

```bash
git clone https://github.com/Cubical6/structurizr-mcp.git
cd structurizr-mcp
composer install
```

> **Tip:** Use `composer install --no-dev` in production to skip development dependencies.

### Directory Permissions

Ensure the following directories are writable:

```bash
chmod -R 755 cache/ sessions/ workspaces/
```

These directories store:
- `cache/` - Discovery cache for faster startup
- `sessions/` - MCP session data
- `workspaces/` - Your Structurizr workspace files

---

## Structurizr CLI (Optional)

The Structurizr CLI is **optional** but enables advanced features like PlantUML and Mermaid export.

### Download Structurizr CLI

1. **Download** from [structurizr/cli releases](https://github.com/structurizr/cli/releases)

2. **Extract** to the `bin/` directory:

```bash
mkdir -p bin
# For macOS/Linux
unzip structurizr-cli-*.zip -d bin/
chmod +x bin/structurizr.sh

# For Windows
unzip structurizr-cli-*.zip -d bin/
```

3. **Configure** the CLI path in your environment (see [Configuration](configuration.md))

> **Note:** Without the CLI, you can still create workspaces and export to DSL format. The CLI is only needed for PlantUML, Mermaid, and advanced validation features.

---

## Verifying Installation

Test your installation by starting the server:

```bash
php server.php
```

You should see log output indicating the server has started:

```
[INFO] Structurizr MCP Server starting...
[INFO] Cache initialized
[INFO] Workspace manager initialized
[INFO] Discovered 23 tools, 7 resources, 7 prompts
[INFO] Server ready
```

Press `Ctrl+C` to stop the server.

> **Tip:** If you encounter errors, see the [Troubleshooting Guide](../troubleshooting/common-issues.md).

---

## Next Steps

Congratulations! You've successfully installed Structurizr MCP Server.

Next, you should:

- **Configure the server** → [Configuration Guide](configuration.md)
- **Set up Claude Desktop** → [Claude Desktop Setup](claude-desktop.md)
- **Create your first workspace** → [Quick Start Guide](quick-start.md)

---

<p align="right">
  <strong>Next:</strong> <a href="configuration.md">Configuration →</a>
</p>
