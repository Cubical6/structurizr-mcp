# Frequently Asked Questions (FAQ)

- [Getting Started](#getting-started)
- [Installation & Setup](#installation--setup)
- [Using the Server](#using-the-server)
- [Workspaces & Models](#workspaces--models)
- [Export & Integration](#export--integration)
- [Troubleshooting](#troubleshooting)
- [Advanced Topics](#advanced-topics)

---

## Getting Started

### Q1: What is Structurizr MCP Server?

**A:** Structurizr MCP Server is an implementation of the Model Context Protocol (MCP) that allows you to create and manage software architecture diagrams using the C4 model directly from Claude Desktop or Claude Code. It provides 23 tools for creating workspaces, adding architectural elements, creating views, and exporting diagrams in various formats.

---

### Q2: Do I need a Structurizr Cloud account?

**A:** No! The MCP server works entirely locally. You can create, manage, and export workspaces without any cloud account or internet connection. Structurizr Cloud integration is optional and planned for future releases.

---

### Q3: What's the difference between Structurizr and the C4 model?

**A:** The C4 model is a framework for visualizing software architecture with four levels (Context, Container, Component, Code). Structurizr is a toolset that implements the C4 model, allowing you to create diagrams as code. This MCP server makes Structurizr accessible through Claude.

---

### Q4: Can I use this with other AI assistants?

**A:** Currently, the server uses the Model Context Protocol (MCP), which is primarily supported by Claude Desktop and Claude Code. Other AI assistants would need to support the MCP protocol to use this server.

---

## Installation & Setup

### Q5: Why aren't tools showing up in Claude Desktop?

**A:** This is the most common issue. Check these steps:

1. **Verify server path is absolute** in `claude_desktop_config.json`:
   ```json
   "args": ["/absolute/path/to/structurizr-mcp/server.php"]
   ```

2. **Ensure environment variables are set**:
   ```json
   "env": {
     "WORKSPACE_STORAGE_PATH": "/absolute/path/to/workspaces"
   }
   ```

3. **Restart Claude Desktop completely** - Quit entirely, don't just close the window.

4. **Check logs** for errors:
   - macOS: `~/Library/Logs/Claude/mcp*.log`
   - Windows: `%APPDATA%\Claude\logs\mcp*.log`
   - Linux: `~/.config/Claude/logs/mcp*.log`

5. **Test server manually**:
   ```bash
   php /absolute/path/to/structurizr-mcp/server.php
   ```

See [Common Issues - Issue 7](common-issues.md#issue-7-tools-not-appearing-in-claude-desktop) for detailed solutions.

---

### Q6: Do I need to install the Structurizr CLI?

**A:** The Structurizr CLI is **optional but recommended**. Here's what works with and without it:

**Without CLI:**
- ✅ Create workspaces
- ✅ Add elements (people, systems, containers, components)
- ✅ Create relationships
- ✅ Create views
- ✅ Export to DSL format
- ✅ Add documentation
- ❌ Export to PlantUML
- ❌ Export to Mermaid
- ❌ Advanced validation

**With CLI:**
- ✅ All of the above
- ✅ Export to PlantUML
- ✅ Export to Mermaid
- ✅ Export to DOT
- ✅ Advanced workspace validation

---

### Q7: What PHP version do I need?

**A:** You need **PHP 8.1 or higher**. The server uses modern PHP features including:
- Attributes (for MCP tool definitions)
- Named arguments
- Readonly properties
- Union types
- Enums

Check your version:
```bash
php -v
```

If you have an older version, upgrade using:
- **macOS**: `brew install php@8.1`
- **Ubuntu/Debian**: `sudo apt-get install php8.1`
- **Windows**: Download from [php.net](https://www.php.net/downloads)

---

### Q8: How do I configure the server?

**A:** All configuration is done through environment variables in the Claude Desktop config file:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/absolute/path/to/structurizr-mcp/server.php"],
      "env": {
        "WORKSPACE_STORAGE_PATH": "/absolute/path/to/workspaces",
        "STRUCTURIZR_CLI_PATH": "/absolute/path/to/bin/structurizr.sh",
        "LOG_LEVEL": "INFO",
        "LOG_PATH": "php://stderr",
        "SERVER_NAME": "structurizr-mcp-server",
        "SERVER_VERSION": "1.0.0"
      }
    }
  }
}
```

Only `WORKSPACE_STORAGE_PATH` is required. See [Configuration Guide](../getting-started/configuration.md) for details.

---

## Using the Server

### Q9: How do I create my first workspace?

**A:** Simply ask Claude in natural language:

```
Create a new Structurizr workspace called "My E-Commerce Platform"
with description "Architecture for our online store"
```

Claude will use the `create_workspace` tool and return a workspace ID. Save this ID for future operations!

Alternatively, you can use the `create_example_workspace` prompt:

```
Use the create_example_workspace prompt to generate a sample e-commerce architecture
```

---

### Q10: What can I ask Claude to do with this server?

**A:** You can ask Claude to perform any architecture modeling task:

**Creating Models:**
- "Create a system context diagram for my web application"
- "Add a PostgreSQL database container to the system"
- "Create a relationship between the web app and database"

**Creating Views:**
- "Create a container view for the e-commerce system"
- "Generate a component view for the API container"
- "Apply left-to-right auto layout to the system context view"

**Exporting:**
- "Export the workspace to DSL format"
- "Convert the container view to PlantUML"
- "Generate a Mermaid diagram from the system context"

**Analysis:**
- "Analyze the dependencies in this workspace"
- "Review the security of my architecture"
- "Suggest improvements for my system design"

---

### Q11: How do I find my workspace ID?

**A:** Ask Claude:

```
List all workspaces
```

This uses the `list_workspaces` tool and shows all workspaces with their IDs, names, and descriptions.

Alternatively, check the workspace storage directory:

```bash
ls -la /path/to/workspaces/
```

Filenames are workspace IDs (e.g., `550e8400-e29b-41d4-a716-446655440000.json`).

---

### Q12: Can I edit an existing workspace?

**A:** Yes! All tools that add elements work on existing workspaces. Just provide the workspace ID:

```
Add a new microservice container called "Payment Service"
to workspace abc-123-def, in the E-Commerce system
```

To view workspace contents:

```
Get the details of workspace abc-123-def
```

Or use resources:

```
Show me the model from workspace abc-123-def
```

---

## Workspaces & Models

### Q13: How do I fix "workspace not found" errors?

**A:** This error occurs when you reference a workspace ID that doesn't exist. Solutions:

1. **List workspaces to find correct ID**:
   ```
   List all workspaces
   ```

2. **Check workspace files exist**:
   ```bash
   ls /path/to/workspaces/
   ```

3. **Create workspace if missing**:
   ```
   Create a new workspace called "My Project"
   ```

4. **Verify workspace ID format** - Should be a UUID like:
   ```
   550e8400-e29b-41d4-a716-446655440000
   ```

See [Common Issues - Issue 11](common-issues.md#issue-11-workspace-not-found-errors) for details.

---

### Q14: What's the difference between a system, container, and component?

**A:** These are the three main levels in the C4 model:

**Software System** (Highest level):
- A major software product or service
- Example: "E-Commerce Platform", "Payment Gateway"
- Shown in System Context diagrams

**Container** (Middle level):
- Separately deployable/executable unit
- Examples: "Web Application", "API Server", "PostgreSQL Database"
- Shown in Container diagrams
- Must belong to a System

**Component** (Lowest level):
- Logical grouping of related functionality
- Examples: "Order Controller", "Payment Processor", "User Repository"
- Shown in Component diagrams
- Must belong to a Container

**Relationship:**
```
System > Container > Component
E-Commerce > Web App > Order Controller
```

---

### Q15: Can I import existing Structurizr DSL files?

**A:** Yes! Use the `import_from_dsl` tool:

```
Import this DSL into a new workspace:

workspace "Example" {
    model {
        user = person "User"
        system = softwareSystem "System"
        user -> system "Uses"
    }
    views {
        systemContext system {
            include *
        }
    }
}
```

Or ask Claude to read a file:

```
Import the DSL from /path/to/workspace.dsl
```

---

### Q16: How do I create relationships between elements?

**A:** Relationships connect two elements with a description:

```
Create a relationship from element abc-123 to element def-456
with description "Sends payment data to" using technology "HTTPS/JSON"
```

**Important:** You need the exact element IDs. Get them from:
- The response when creating elements
- Using `get_workspace` to view all elements
- Using `find_element` to search by name

Example workflow:
```
1. "Add a system called 'Web App'" → returns systemId: abc-123
2. "Add a system called 'Database'" → returns systemId: def-456
3. "Create relationship from abc-123 to def-456 with description 'Reads from'"
```

---

### Q17: Can I delete workspaces or elements?

**A:** Currently, you can delete entire workspaces:

```
Delete workspace abc-123-def
```

**Individual element deletion is not yet supported.** To remove elements, you need to:

1. Export workspace to DSL
2. Edit the DSL manually
3. Import the modified DSL as a new workspace

Or manually edit the workspace JSON file in the storage directory.

---

## Export & Integration

### Q18: What export formats are supported?

**A:** The server supports multiple export formats:

| Format | CLI Required? | Tool |
|--------|---------------|------|
| DSL | No | `export_to_dsl` |
| PlantUML | Yes | `export_to_plantuml` |
| Mermaid | Yes | `export_to_mermaid` |
| JSON | No | `get_workspace` (returns JSON) |

**Without CLI:**
```
Export workspace abc-123 to DSL
```

**With CLI:**
```
Export the SystemContext view to PlantUML
Export the Containers view to Mermaid
```

---

### Q19: Can I use this with existing Structurizr Cloud workspaces?

**A:** Not directly in the current version. The server manages local workspaces only.

**Workaround:**
1. Download workspace from cloud as DSL using Structurizr CLI
2. Import DSL into MCP server workspace
3. Make changes using MCP tools
4. Export back to DSL
5. Upload to cloud using Structurizr CLI

**Planned for future:** Direct cloud sync via API integration.

---

### Q20: How do I migrate from manually drawn diagrams?

**A:** The best approach is to recreate diagrams using Claude's assistance:

1. **Start with a description**:
   ```
   Create a C4 model for a web application with:
   - React frontend
   - Node.js API
   - PostgreSQL database
   - Redis cache
   Users access the frontend, which calls the API,
   which uses the database and cache
   ```

2. **Let Claude create the model** using MCP tools automatically.

3. **Refine iteratively**:
   ```
   Add a messaging queue between the API and a worker service
   Create a deployment diagram showing AWS infrastructure
   ```

4. **Export to your preferred format**:
   ```
   Export to PlantUML for documentation
   ```

This is faster than manual recreation and ensures consistency.

---

## Troubleshooting

### Q21: Why does the server keep disconnecting?

**A:** Server disconnection usually indicates a PHP error or resource issue:

1. **Check logs** for errors:
   ```bash
   tail -f ~/Library/Logs/Claude/mcp-server-structurizr.log
   ```

2. **Test server manually**:
   ```bash
   php /path/to/server.php
   ```

3. **Increase memory limit** if handling large workspaces:
   ```ini
   # In php.ini
   memory_limit = 256M
   ```

4. **Verify no output to stdout** - Only stderr is allowed:
   ```json
   "env": {
     "LOG_PATH": "php://stderr"
   }
   ```

See [Debugging Guide](debugging.md) for detailed troubleshooting.

---

### Q22: "Invalid DSL" errors - how do I fix them?

**A:** DSL syntax errors usually involve missing quotes or invalid syntax:

**Common mistakes:**

❌ Wrong:
```dsl
user = person User A description
user -> system
description It's a system
```

✅ Correct:
```dsl
user = person "User" "A description"
user -> system "Uses"
description "It is a system"
```

**Validation:**
```bash
# If you have Structurizr CLI
./bin/structurizr.sh validate -w workspace.dsl
```

**Tips:**
- Always quote multi-word strings
- Include descriptions in relationships
- Escape special characters
- Check bracket matching

See [Common Issues - Issue 13](common-issues.md#issue-13-invalid-dsl-errors-during-import) for examples.

---

### Q23: How do I enable debug logging?

**A:** Set `LOG_LEVEL` to `DEBUG` in your Claude Desktop config:

```json
{
  "env": {
    "LOG_LEVEL": "DEBUG",
    "LOG_PATH": "php://stderr"
  }
}
```

Then restart Claude Desktop completely.

View logs:
```bash
# macOS
tail -f ~/Library/Logs/Claude/mcp-server-structurizr.log

# Linux
tail -f ~/.config/Claude/logs/mcp-server-structurizr.log
```

See [Debugging Guide - Enabling Debug Logging](debugging.md#enabling-debug-logging) for details.

---

## Advanced Topics

### Q24: Can I use this in CI/CD pipelines?

**A:** Yes! While designed for MCP, the underlying classes can be used programmatically:

```php
<?php
require 'vendor/autoload.php';

use StructurizrMcp\Structurizr\WorkspaceManager;

$manager = new WorkspaceManager('/path/to/workspaces');

// Create workspace
$ws = $manager->createWorkspace('CI Pipeline', 'Generated architecture');

// Add elements
$systemId = $manager->addSoftwareSystem($ws, 'App', 'Main application');

// Export
$dsl = $manager->exportToDsl($ws);
file_put_contents('architecture.dsl', $dsl);
```

You could integrate this into:
- Documentation generation
- Architecture validation
- Automatic diagram updates from code

---

### Q25: How do I back up my workspaces?

**A:** Workspaces are stored as JSON files in `WORKSPACE_STORAGE_PATH`. Back them up like any files:

**Simple backup:**
```bash
# Copy entire workspace directory
cp -r /path/to/workspaces /path/to/backup/

# Or tar archive
tar -czf workspaces-backup.tar.gz /path/to/workspaces/
```

**Version control (recommended):**
```bash
cd /path/to/workspaces/
git init
git add *.json
git commit -m "Backup workspaces"
git push origin main
```

**Export as DSL (portable):**
```
Export all workspaces to DSL format
```

DSL files are human-readable and can be version-controlled easily.

---

### Q26: Can I customize the C4 diagram styles?

**A:** The server currently creates views without custom styling. To add styles:

1. **Export to DSL**:
   ```
   Export workspace to DSL
   ```

2. **Add styles section** to the DSL:
   ```dsl
   views {
       systemContext mySystem {
           include *
       }

       styles {
           element "Software System" {
               background #1168bd
               color #ffffff
           }
           element "Person" {
               shape person
               background #08427b
               color #ffffff
           }
       }
   }
   ```

3. **Re-import** the modified DSL:
   ```
   Import this modified DSL
   ```

**Future enhancement:** Direct style manipulation via MCP tools is planned.

---

### Q27: What's the difference between MCP Resources and Tools?

**A:** Both are MCP capabilities, but serve different purposes:

**Tools** (23 available):
- **Purpose**: Perform actions and make changes
- **Examples**: `create_workspace`, `add_software_system`, `export_to_dsl`
- **Usage**: Claude calls tools when you ask to do something
- **Returns**: Success/failure and results

**Resources** (7 available):
- **Purpose**: Read-only data access via URIs
- **Examples**: `structurizr://workspace/{id}`, `structurizr://workspace/{id}/model`
- **Usage**: Claude reads resources to understand context
- **Returns**: Data only, no side effects

**Example:**
```
Tool: "Create a workspace called X" → creates workspace
Resource: "Show me workspace X" → reads workspace data
```

See [Resources Guide](../resources/README.md) for details.

---

### Q28: How do I handle very large workspaces?

**A:** Large workspaces can cause performance issues. Here are optimization strategies:

**1. Split into multiple workspaces:**
```
- workspace-frontend.json (UI architecture)
- workspace-backend.json (API architecture)
- workspace-infrastructure.json (Deployment)
```

**2. Increase PHP limits:**
```ini
# php.ini
memory_limit = 512M
max_execution_time = 300
```

**3. Use focused views:**
Instead of including everything:
```dsl
container mySystem {
    include element.id  # Specific elements only
}
```

**4. Export incrementally:**
Export specific views rather than entire workspace.

---

### Q29: Can I use this with other MCP clients?

**A:** Theoretically yes! The server implements the standard MCP protocol. Any MCP-compatible client should work.

**Tested clients:**
- ✅ Claude Desktop (macOS, Windows, Linux)
- ✅ Claude Code (VS Code extension)

**Untested but should work:**
- MCP Inspector (debugging tool)
- Custom MCP clients implementing the protocol

**Transport support:**
- ✅ stdio (local processes)
- ❌ HTTP/SSE (not yet implemented)

---

### Q30: How can I contribute or request features?

**A:** Contributions welcome!

**Request features:**
- Open an issue: [github.com/Cubical6/structurizr-mcp/issues](https://github.com/Cubical6/structurizr-mcp/issues)
- Describe your use case
- Suggest specific tools or capabilities

**Contribute code:**
- Fork the repository
- Create feature branch
- Add tests for new features
- Ensure PHPStan and code style checks pass
- Submit pull request

**Areas for contribution:**
- Additional export formats
- Cloud integration
- Advanced styling
- Performance optimizations
- Documentation improvements

See [CONTRIBUTING.md](../../CONTRIBUTING.md) for guidelines.

---

## Still Need Help?

If your question isn't answered here:

1. **Check other documentation**:
   - [Common Issues](common-issues.md) - Specific problems and solutions
   - [Debugging Guide](debugging.md) - Detailed debugging techniques
   - [Getting Started](../getting-started/README.md) - Installation and setup

2. **Search GitHub issues**: [github.com/Cubical6/structurizr-mcp/issues](https://github.com/Cubical6/structurizr-mcp/issues)

3. **Create a new issue** with:
   - Clear description of your question/problem
   - PHP version and OS
   - Relevant logs or error messages
   - Steps you've already tried

---

<p align="right">
  <strong>Back to:</strong> <a href="common-issues.md">Common Issues</a> | <a href="debugging.md">Debugging Guide</a>
</p>
