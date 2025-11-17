# Claude Desktop Setup

- [Introduction](#introduction)
- [Locating the Configuration File](#locating-the-configuration-file)
- [Adding Structurizr MCP Server](#adding-structurizr-mcp-server)
- [Restarting Claude Desktop](#restarting-claude-desktop)
- [Verifying the Connection](#verifying-the-connection)
- [Troubleshooting](#troubleshooting)

---

## Introduction

Claude Desktop is an AI assistant application that supports the Model Context Protocol (MCP). By adding the Structurizr MCP Server to Claude Desktop, you enable Claude to create and manage C4 architecture diagrams directly in conversation.

> **Note:** This guide assumes you've already [installed](installation.md) and [configured](configuration.md) the Structurizr MCP Server.

---

## Locating the Configuration File

The Claude Desktop configuration file location depends on your operating system:

### macOS

```bash
~/Library/Application Support/Claude/claude_desktop_config.json
```

### Windows

```powershell
%APPDATA%\Claude\claude_desktop_config.json
```

Or in expanded form:

```powershell
C:\Users\YourUsername\AppData\Roaming\Claude\claude_desktop_config.json
```

### Linux

```bash
~/.config/Claude/claude_desktop_config.json
```

> **Tip:** If the file doesn't exist, create it with an empty JSON object: `{}`

---

## Adding Structurizr MCP Server

Open the configuration file in your preferred text editor and add the Structurizr server configuration:

### Basic Configuration (macOS/Linux)

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/absolute/path/to/structurizr-mcp/server.php"]
    }
  }
}
```

> **Important:** Replace `/absolute/path/to/structurizr-mcp` with the actual absolute path to where you cloned the repository.

### Windows Configuration

> ⚠️ **CRITICAL for Windows users**: JSON requires backslashes to be escaped. You have two options:

**Option 1: Use forward slashes (recommended, cleaner syntax)**
```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["C:/Users/YourName/Projects/structurizr-mcp/server.php"]
    }
  }
}
```

**Option 2: Use escaped backslashes (double backslashes)**
```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["C:\\Users\\YourName\\Projects\\structurizr-mcp\\server.php"]
    }
  }
}
```

**❌ This WILL FAIL with "Cannot read properties of undefined (reading 'cmd')" error:**
```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["C:\Users\YourName\Projects\structurizr-mcp\server.php"]
    }
  }
}
```

> **Why?** Single backslashes in JSON are escape characters (like `\n` for newline). The sequences `\U`, `\P`, etc. are invalid, causing JSON parsing to fail silently and Claude Desktop cannot read the configuration.

### Finding the Absolute Path

#### macOS/Linux

Navigate to the project directory and run:

```bash
cd structurizr-mcp
pwd
```

This will output something like:
```
/Users/yourname/Projects/structurizr-mcp
```

Use this full path in the configuration.

#### Windows

Navigate to the project directory and run:

```powershell
cd structurizr-mcp
cd
```

This will output something like:
```
C:\Users\YourName\Projects\structurizr-mcp
```

### Advanced Configuration (with Environment Variables)

For more control, you can pass environment variables:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/absolute/path/to/structurizr-mcp/server.php"],
      "env": {
        "LOG_LEVEL": "INFO",
        "WORKSPACE_STORAGE_PATH": "/Users/yourname/architectures",
        "STRUCTURIZR_CLI_PATH": "/usr/local/bin/structurizr"
      }
    }
  }
}
```

> **Tip:** See the [Configuration Guide](configuration.md) for all available environment variables.

### Multiple MCP Servers

You can add multiple MCP servers to Claude Desktop:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/path/to/structurizr-mcp/server.php"]
    },
    "another-server": {
      "command": "node",
      "args": ["/path/to/another-server/index.js"]
    }
  }
}
```

---

## Restarting Claude Desktop

After modifying the configuration file:

1. **Quit Claude Desktop completely** (don't just close the window)
   - macOS: `Cmd+Q`
   - Windows: Right-click taskbar icon → Exit
   - Linux: `Ctrl+Q` or close via system tray

2. **Wait 2-3 seconds** for the process to fully terminate

3. **Relaunch Claude Desktop**

> **Note:** Simply closing the window is not sufficient. You must fully quit the application.

---

## Verifying the Connection

Once Claude Desktop has restarted, verify the connection:

### 1. Check for the Tools Icon

Look for a tools/plug icon in the Claude Desktop interface, typically in the chat input area or toolbar.

### 2. Ask Claude About Available Tools

Start a new conversation and type:

```
What MCP tools do you have access to?
```

Claude should respond with a list including Structurizr tools like:
- `create_workspace`
- `add_software_system`
- `add_person`
- `create_system_context_view`
- And 19 more...

### 3. Test a Simple Command

Try creating a workspace:

```
Create a new Structurizr workspace called "My Architecture"
with description "My first C4 model"
```

If Claude successfully creates a workspace and returns a workspace ID, your setup is working!

---

## Troubleshooting

### Error: "Cannot read properties of undefined (reading 'cmd')"

**This is the most common Windows configuration error.**

**Cause:** Single backslashes in JSON path (e.g., `C:\Users\...`)

**Solution:** Use one of these options:

```json
// ✅ Option 1: Forward slashes (recommended)
"args": ["C:/Users/YourName/Projects/structurizr-mcp/server.php"]

// ✅ Option 2: Escaped backslashes
"args": ["C:\\Users\\YourName\\Projects\\structurizr-mcp\\server.php"]

// ❌ WRONG - this causes the error
"args": ["C:\Users\YourName\Projects\structurizr-mcp\server.php"]
```

**Steps to fix:**

1. Open `%APPDATA%\Claude\claude_desktop_config.json`
2. Find your path in the `args` field
3. Either:
   - Replace all `\` with `/`, OR
   - Replace all `\` with `\\`
4. Validate your JSON at [jsonlint.com](https://jsonlint.com)
5. Save and restart Claude Desktop (fully quit, not just close)

### Claude Desktop Doesn't Show Structurizr Tools

**Possible causes:**

1. **Configuration file syntax error (especially Windows paths)**
   - **Windows users:** Check backslash escaping (see above)
   - Verify JSON is valid using [jsonlint.com](https://jsonlint.com)
   - Ensure all quotes are double quotes (`"`)
   - Check for missing commas between entries

2. **Incorrect PHP path**
   - Verify PHP is in your PATH: `which php` (macOS/Linux) or `where php` (Windows)
   - Try using absolute PHP path:
     ```json
     // macOS/Linux
     "command": "/usr/bin/php"

     // Windows (with escaped backslashes or forward slashes)
     "command": "C:/PHP/php.exe"
     ```

3. **Incorrect server.php path**
   - Ensure you're using an absolute path, not relative
   - Verify the file exists: `ls /path/to/server.php` (macOS/Linux) or `dir C:\path\to\server.php` (Windows)

4. **PHP version too old**
   - Check: `php -v`
   - Requires PHP 8.1 or higher

### Claude Shows Error When Using Tools

**Check the logs:**

1. **Enable debug logging** in Claude Desktop config:
   ```json
   "env": {
     "LOG_LEVEL": "DEBUG"
   }
   ```

2. **View logs** in Claude Desktop:
   - Look for error messages in the UI
   - Check system console/terminal for output

3. **Test server manually:**
   ```bash
   php /path/to/server.php
   ```

   If you see errors, they'll be displayed immediately.

### Workspace Creation Fails

**Verify permissions:**

```bash
chmod -R 755 workspaces/ cache/ sessions/
```

**Check storage path:**

Ensure `WORKSPACE_STORAGE_PATH` directory exists and is writable.

### Still Having Issues?

See the complete [Troubleshooting Guide](../troubleshooting/common-issues.md) or [open an issue](https://github.com/Cubical6/structurizr-mcp/issues) on GitHub.

---

## Configuration Examples

### Example 1: Basic macOS Setup

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/Users/john/Projects/structurizr-mcp/server.php"]
    }
  }
}
```

### Example 2: Windows with Custom Workspace Path

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["C:\\Projects\\structurizr-mcp\\server.php"],
      "env": {
        "WORKSPACE_STORAGE_PATH": "C:\\Users\\John\\Documents\\Architectures"
      }
    }
  }
}
```

### Example 3: Development Setup with Debug Logging

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/home/dev/structurizr-mcp/server.php"],
      "env": {
        "LOG_LEVEL": "DEBUG",
        "WORKSPACE_STORAGE_PATH": "/home/dev/workspaces",
        "STRUCTURIZR_CLI_PATH": "/home/dev/structurizr-mcp/bin/structurizr.sh"
      }
    }
  }
}
```

---

## Next Steps

Now that Claude Desktop is connected to Structurizr MCP Server, you're ready to:

- **Create your first diagram** → [Quick Start Guide](quick-start.md)
- **Learn about C4 models** → [The C4 Model](../architecture/c4-model.md)
- **Explore all tools** → [Tools Reference](../tools/overview.md)
- **Try examples** → [Examples & Tutorials](../examples/basic-c4.md)

---

<p align="right">
  <strong>Next:</strong> <a href="quick-start.md">Quick Start Guide →</a>
</p>
