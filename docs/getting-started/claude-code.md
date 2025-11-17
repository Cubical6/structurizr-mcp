# Claude Code Setup

- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
- [Verifying the Connection](#verifying-the-connection)
- [Using Structurizr in Claude Code](#using-structurizr-in-claude-code)
- [Differences from Claude Desktop](#differences-from-claude-desktop)
- [Troubleshooting](#troubleshooting)

---

## Introduction

Claude Code is an AI-powered coding assistant integrated into your development environment. By adding the Structurizr MCP Server, you can create and manage C4 architecture diagrams directly while working on your codebase.

> **Note:** This guide assumes you've already [installed](installation.md) and [configured](configuration.md) the Structurizr MCP Server.

---

## Installation

### Prerequisites

- **Claude Code extension** installed in your IDE
- **PHP 8.1+** available in your system PATH
- **Structurizr MCP Server** cloned and dependencies installed

### Verify PHP Availability

Ensure PHP is accessible from the command line:

```bash
php -v
```

---

## Configuration

Claude Code uses a similar configuration approach to Claude Desktop, but the settings are managed through your IDE or a local configuration file.

### Option 1: Via IDE Settings

1. **Open Claude Code Settings**
   - VS Code: `Cmd/Ctrl + Shift + P` → "Claude Code: Open Settings"

2. **Add MCP Server Configuration**

Navigate to the MCP Servers section and add:

```json
{
  "name": "structurizr",
  "command": "php",
  "args": ["/absolute/path/to/structurizr-mcp/server.php"]
}
```

### Option 2: Via Configuration File

Create or edit `.claude/mcp_config.json` in your project root:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/absolute/path/to/structurizr-mcp/server.php"],
      "env": {
        "LOG_LEVEL": "INFO",
        "WORKSPACE_STORAGE_PATH": "${workspaceFolder}/architecture"
      }
    }
  }
}
```

> **Tip:** Use `${workspaceFolder}` to reference your IDE workspace directory dynamically.

---

## Verifying the Connection

After configuration:

1. **Reload IDE Window**
   - VS Code: `Cmd/Ctrl + Shift + P` → "Developer: Reload Window"

2. **Check MCP Status**
   - Look for Structurizr in the Claude Code MCP servers list
   - Status should show "Connected" or "Ready"

3. **Test with Claude**

Ask Claude Code:

```
What Structurizr tools are available?
```

You should see a list of all 23 tools.

---

## Using Structurizr in Claude Code

### Workflow Integration

Structurizr MCP works seamlessly within your development workflow:

#### 1. Document Existing Architecture

```
Claude, analyze this codebase and create a C4 system context
diagram showing the main components and their relationships.
```

#### 2. Plan New Features

```
I'm adding a new authentication service. Create a container
diagram showing how it will integrate with the existing system.
```

#### 3. Keep Docs Updated

```
Update the architecture workspace to include the new payment
processing microservice we just added.
```

### Workspace Location

By default, workspaces are stored in:

```
./workspaces/
```

To store them with your project:

```json
"env": {
  "WORKSPACE_STORAGE_PATH": "${workspaceFolder}/docs/architecture"
}
```

> **Tip:** Commit workspace JSON files to version control to track architecture evolution.

---

## Differences from Claude Desktop

| Feature | Claude Desktop | Claude Code |
|---------|---------------|-------------|
| **Context** | Standalone conversations | Integrated with codebase |
| **Workspace Path** | User's home directory | Project directory (configurable) |
| **Use Case** | General architecture design | Code-connected architecture |
| **File Access** | Manual export/import | Can reference project files |
| **Version Control** | Manual tracking | Natural git integration |

### Recommended Workflow

**Claude Desktop:**
- Initial architecture design
- Stakeholder presentations
- High-level planning

**Claude Code:**
- Implementation-level diagrams
- Keeping docs in sync with code
- Developer-focused documentation

---

## Advanced Configuration

### Project-Specific Workspaces

Store architecture in your repository:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/absolute/path/to/structurizr-mcp/server.php"],
      "env": {
        "WORKSPACE_STORAGE_PATH": "${workspaceFolder}/docs/architecture",
        "LOG_LEVEL": "WARNING"
      }
    }
  }
}
```

### Multi-Project Setup

Use workspace folder variable for portability:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/Users/yourname/tools/structurizr-mcp/server.php"],
      "env": {
        "WORKSPACE_STORAGE_PATH": "${workspaceFolder}/.architecture"
      }
    }
  }
}
```

Now each project gets its own `.architecture/` directory for workspaces.

### With Structurizr CLI

Enable PlantUML and Mermaid export:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/path/to/structurizr-mcp/server.php"],
      "env": {
        "WORKSPACE_STORAGE_PATH": "${workspaceFolder}/docs/architecture",
        "STRUCTURIZR_CLI_PATH": "/usr/local/bin/structurizr"
      }
    }
  }
}
```

---

## Troubleshooting

### MCP Server Not Showing in Claude Code

1. **Check configuration syntax**
   - Validate JSON at [jsonlint.com](https://jsonlint.com)
   - Ensure proper quotes and commas

2. **Verify PHP path**
   ```bash
   which php  # macOS/Linux
   where php  # Windows
   ```

3. **Check server.php path**
   - Must be absolute path
   - Use `pwd` (macOS/Linux) or `cd` (Windows) to get full path

4. **Reload IDE**
   - Fully reload window after config changes

### Workspaces Not Persisting

**Check workspace directory:**

```bash
ls -la ./workspaces/
# or
ls -la ${workspaceFolder}/docs/architecture/
```

**Ensure write permissions:**

```bash
chmod -R 755 ./workspaces/
```

### Tools Show But Don't Work

**Enable debug logging:**

```json
"env": {
  "LOG_LEVEL": "DEBUG"
}
```

**Check IDE terminal/console** for error messages.

---

## Best Practices

### 1. Version Control Integration

Create `.gitignore` entry:

```gitignore
# Keep workspace JSON files
!docs/architecture/*.json

# Ignore cache
cache/
sessions/
```

### 2. Team Collaboration

Commit workspace files to git:

```bash
git add docs/architecture/my-system.json
git commit -m "docs: Add container diagram for authentication service"
```

### 3. Documentation Workflow

1. **Create workspace** when planning new features
2. **Update diagrams** as you implement
3. **Export to DSL** for documentation
4. **Commit both JSON and DSL** to repository

### 4. Directory Structure

```
my-project/
├── docs/
│   ├── architecture/
│   │   ├── system-context.json
│   │   ├── containers.json
│   │   └── components.json
│   └── diagrams/
│       ├── system-context.dsl
│       ├── system-context.puml
│       └── README.md
├── src/
└── tests/
```

---

## Next Steps

Now that Claude Code is configured:

- **Create your first diagram** → [Quick Start Guide](quick-start.md)
- **Learn the tools** → [Tools Reference](../tools/overview.md)
- **See examples** → [Examples & Tutorials](../examples/basic-c4.md)
- **Advanced usage** → [Architecture Concepts](../architecture/mcp-overview.md)

---

<p align="right">
  <strong>Next:</strong> <a href="quick-start.md">Quick Start Guide →</a>
</p>
