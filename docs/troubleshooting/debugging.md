# Debugging Guide

- [Enabling Debug Logging](#enabling-debug-logging)
- [Reading Log Output](#reading-log-output)
- [Testing Server Manually](#testing-server-manually)
- [Verifying Configuration](#verifying-configuration)
- [Common Error Messages](#common-error-messages)
- [Advanced Debugging](#advanced-debugging)

---

## Enabling Debug Logging

Debug logging provides detailed information about server operations, MCP communication, and tool execution.

### Step 1: Configure Debug Level

Update your Claude Desktop configuration to enable DEBUG logging:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/absolute/path/to/structurizr-mcp/server.php"],
      "env": {
        "LOG_LEVEL": "DEBUG",
        "LOG_PATH": "php://stderr",
        "STRUCTURIZR_CLI_PATH": "/path/to/bin/structurizr.sh",
        "WORKSPACE_STORAGE_PATH": "/path/to/workspaces"
      }
    }
  }
}
```

### Step 2: Restart Claude Desktop

Completely quit and restart Claude Desktop for changes to take effect:

```bash
# macOS
killall "Claude Desktop"
open -a "Claude Desktop"

# Linux
killall claude
claude &

# Windows
# Use Task Manager to end Claude.exe, then restart
```

### Log Levels

Choose the appropriate log level for your needs:

| Level | When to Use | Output Volume |
|-------|-------------|---------------|
| `DEBUG` | Development, troubleshooting issues | Very high - shows all operations |
| `INFO` | Normal operation, general monitoring | Medium - shows important events |
| `WARNING` | Production, only show warnings | Low - only warnings and errors |
| `ERROR` | Production, critical issues only | Very low - errors only |

---

## Reading Log Output

### Log Output Locations

Depending on your `LOG_PATH` configuration:

#### Option 1: Standard Error (Recommended)

```json
{
  "env": {
    "LOG_PATH": "php://stderr"
  }
}
```

**Location:**
- macOS: `~/Library/Logs/Claude/mcp*.log`
- Windows: `%APPDATA%\Claude\logs\mcp*.log`
- Linux: `~/.config/Claude/logs/mcp*.log`

**Reading logs:**

```bash
# macOS/Linux - Real-time monitoring
tail -f ~/Library/Logs/Claude/mcp-server-structurizr.log

# View last 100 lines
tail -100 ~/Library/Logs/Claude/mcp-server-structurizr.log

# Search for errors
grep ERROR ~/Library/Logs/Claude/mcp-server-structurizr.log
```

#### Option 2: Custom Log File

```json
{
  "env": {
    "LOG_PATH": "/absolute/path/to/structurizr-mcp.log"
  }
}
```

**Reading logs:**

```bash
# Real-time monitoring
tail -f /absolute/path/to/structurizr-mcp.log

# Filter by level
grep "\[ERROR\]" /absolute/path/to/structurizr-mcp.log
grep "\[WARNING\]" /absolute/path/to/structurizr-mcp.log
```

### Understanding Log Format

Logs follow this format:

```
[YYYY-MM-DD HH:MM:SS] channel.LEVEL: Message {context}
```

Example:

```
[2025-11-16 14:30:45] structurizr-mcp.INFO: Server ready
[2025-11-16 14:30:50] structurizr-mcp.DEBUG: Received tool call: create_workspace {"name":"My App"}
[2025-11-16 14:30:51] structurizr-mcp.INFO: Workspace created {"workspaceId":"550e8400-..."}
```

### Key Log Messages

#### Successful Startup

```
[INFO] Structurizr MCP Server starting...
[INFO] Cache initialized
[INFO] Workspace manager initialized
[INFO] Discovered 23 tools, 7 resources, 7 prompts
[INFO] Server ready
```

#### Tool Execution

```
[DEBUG] Received tool call: create_workspace {"name":"My Architecture"}
[DEBUG] Executing tool: create_workspace
[INFO] Workspace created successfully {"workspaceId":"abc-123-def"}
[DEBUG] Tool execution completed {"duration":"45ms"}
```

#### Errors

```
[ERROR] Failed to create workspace: Permission denied
[ERROR] Workspace not found {"workspaceId":"invalid-id"}
[ERROR] CLI execution failed {"command":"export","error":"Java not found"}
```

---

## Testing Server Manually

Test the server independently of Claude Desktop to isolate issues.

### Method 1: Direct Execution

Run the server directly from command line:

```bash
cd /path/to/structurizr-mcp
php server.php
```

**Expected output:**

```
[INFO] Structurizr MCP Server starting...
[INFO] Cache initialized
[INFO] Workspace manager initialized
[INFO] Discovered 23 tools, 7 resources, 7 prompts
[INFO] Server ready
```

**Press Ctrl+C to stop.**

### Method 2: Test with Environment Variables

```bash
STRUCTURIZR_CLI_PATH=/path/to/cli \
WORKSPACE_STORAGE_PATH=/path/to/workspaces \
LOG_LEVEL=DEBUG \
php server.php
```

### Method 3: Check PHP Syntax

Verify there are no syntax errors:

```bash
php -l server.php
php -l src/**/*.php
```

**Expected output:**

```
No syntax errors detected
```

### Method 4: Test Tool Discovery

Check that all tools are discovered:

```bash
# Run server and look for discovery message
php server.php 2>&1 | grep "Discovered"
```

**Expected:**

```
Discovered 23 tools, 7 resources, 7 prompts
```

If numbers are wrong, clear cache:

```bash
rm -rf cache/*
php server.php
```

### Method 5: Test Workspace Operations

Create a test script to verify workspace operations:

```php
<?php
// test-workspace.php

require 'vendor/autoload.php';

use StructurizrMcp\Structurizr\WorkspaceManager;

$manager = new WorkspaceManager('/tmp/test-workspaces');

// Create workspace
$workspace = $manager->createWorkspace('Test', 'Test workspace');
echo "Created workspace: " . $workspace->getId() . "\n";

// Add system
$systemId = $manager->addSoftwareSystem($workspace, 'System', 'Test system');
echo "Created system: $systemId\n";

// Retrieve workspace
$retrieved = $manager->getWorkspace($workspace->getId());
echo "Retrieved workspace: " . $retrieved->getName() . "\n";

echo "All tests passed!\n";
```

Run the test:

```bash
php test-workspace.php
```

---

## Verifying Configuration

### Check Environment Variables

Create a diagnostic script:

```php
<?php
// check-config.php

require 'vendor/autoload.php';

use StructurizrMcp\Configuration;

echo "=== Structurizr MCP Configuration ===\n\n";

$config = Configuration::loadConfiguration();

echo "Server Name: " . $config->getServerName() . "\n";
echo "Server Version: " . $config->getServerVersion() . "\n";
echo "Log Level: " . $config->getLogLevel() . "\n";
echo "Log Path: " . $config->getLogPath() . "\n";
echo "Workspace Storage: " . $config->getWorkspaceStoragePath() . "\n";
echo "CLI Path: " . ($config->getCliPath() ?? 'Not configured') . "\n";

// Check if directories exist and are writable
$storagePath = $config->getWorkspaceStoragePath();
echo "\nWorkspace Storage Check:\n";
echo "  Exists: " . (is_dir($storagePath) ? 'Yes' : 'No') . "\n";
echo "  Writable: " . (is_writable($storagePath) ? 'Yes' : 'No') . "\n";

// Check CLI
$cliPath = $config->getCliPath();
if ($cliPath) {
    echo "\nCLI Check:\n";
    echo "  Exists: " . (file_exists($cliPath) ? 'Yes' : 'No') . "\n";
    echo "  Executable: " . (is_executable($cliPath) ? 'Yes' : 'No') . "\n";
}

echo "\n";
```

Run the configuration check:

```bash
php check-config.php
```

### Verify Claude Desktop Configuration

Check your Claude Desktop config file:

**macOS:**
```bash
cat ~/Library/Application\ Support/Claude/claude_desktop_config.json
```

**Linux:**
```bash
cat ~/.config/Claude/claude_desktop_config.json
```

**Windows:**
```powershell
type %APPDATA%\Claude\claude_desktop_config.json
```

### Validate JSON Syntax

Ensure configuration JSON is valid:

```bash
# macOS/Linux
cat ~/Library/Application\ Support/Claude/claude_desktop_config.json | python -m json.tool

# Or use jq
cat ~/Library/Application\ Support/Claude/claude_desktop_config.json | jq .
```

### Common Configuration Mistakes

❌ **Wrong:**
```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["./server.php"],  // Relative path
      "env": {
        "WORKSPACE_STORAGE_PATH": "./workspaces"  // Relative path
      }
    }
  }
}
```

✅ **Correct:**
```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/Users/john/structurizr-mcp/server.php"],  // Absolute path
      "env": {
        "WORKSPACE_STORAGE_PATH": "/Users/john/structurizr-mcp/workspaces"  // Absolute path
      }
    }
  }
}
```

---

## Common Error Messages

### Error: "Class 'Mcp\Server' not found"

**Cause:** Composer dependencies not installed.

**Solution:**

```bash
cd /path/to/structurizr-mcp
composer install
```

### Error: "Cannot write to cache directory"

**Cause:** Cache directory not writable.

**Solution:**

```bash
chmod -R 755 cache/
# Or
chmod -R 777 cache/  # Less secure but works
```

### Error: "Workspace file does not exist"

**Cause:** Workspace storage path incorrect or workspace deleted.

**Solution:**

```bash
# Check storage path
ls -la /path/to/workspaces/

# Verify path in configuration
php check-config.php

# Create missing directories
mkdir -p /path/to/workspaces
chmod 755 /path/to/workspaces
```

### Error: "Failed to execute CLI command"

**Cause:** Structurizr CLI not found or not executable.

**Solution:**

```bash
# Check CLI exists
ls -la /path/to/bin/structurizr.sh

# Make executable
chmod +x /path/to/bin/structurizr.sh

# Test CLI
/path/to/bin/structurizr.sh version

# Check Java is installed
java -version
```

### Error: "Invalid JSON-RPC request"

**Cause:** MCP protocol communication error.

**Solution:**

1. Ensure log output goes to stderr only:

```json
{
  "env": {
    "LOG_PATH": "php://stderr"
  }
}
```

2. Update MCP SDK:

```bash
composer update mcp/sdk
```

3. Clear cache:

```bash
rm -rf cache/*
```

### Error: "Memory limit exhausted"

**Cause:** PHP memory limit too low for large workspaces.

**Solution:**

```bash
# Check current limit
php -r "echo ini_get('memory_limit') . PHP_EOL;"

# Increase in php.ini
memory_limit = 256M

# Or via environment
php -d memory_limit=256M server.php
```

---

## Advanced Debugging

### Enable Xdebug

For step-by-step debugging:

1. **Install Xdebug:**

```bash
# macOS (Homebrew)
pecl install xdebug

# Ubuntu/Debian
sudo apt-get install php-xdebug
```

2. **Configure php.ini:**

```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_port=9003
```

3. **Configure your IDE** (VS Code, PHPStorm) for remote debugging.

### Trace MCP Messages

Monitor JSON-RPC messages between Claude and server:

```bash
# Run server with strace (Linux)
strace -e read,write -s 1000 php server.php

# Run with verbose output
php -d display_errors=1 -d error_reporting=E_ALL server.php
```

### Profile Performance

Identify slow operations:

```bash
# Install xhprof
pecl install xhprof

# Enable profiling
php -d xhprof.output_dir=/tmp server.php
```

### Debug Tool Execution

Add debug output to tool methods:

```php
// In src/Tools/WorkspaceTools.php
public function createWorkspace(string $name, string $description = ''): array
{
    error_log("DEBUG: createWorkspace called with name=$name");

    try {
        $workspace = $this->workspaceManager->createWorkspace($name, $description);
        error_log("DEBUG: Workspace created with ID=" . $workspace->getId());

        return [
            'success' => true,
            'workspaceId' => $workspace->getId(),
        ];
    } catch (\Exception $e) {
        error_log("ERROR: " . $e->getMessage());
        throw $e;
    }
}
```

### Test with curl (HTTP Transport)

If using HTTP transport instead of stdio:

```bash
# Test server info endpoint
curl -X POST http://localhost:3000 \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "method": "initialize",
    "params": {
      "protocolVersion": "2024-11-05",
      "capabilities": {},
      "clientInfo": {"name": "test", "version": "1.0"}
    },
    "id": 1
  }'
```

### Check System Resources

Monitor resource usage during operation:

```bash
# Watch memory usage
watch -n 1 "ps aux | grep php | grep server.php"

# Check disk I/O
iostat -x 1

# Monitor file operations
lsof -p $(pgrep -f "php.*server.php")
```

### Validate Workspace JSON

Check workspace file structure:

```bash
# Pretty print workspace JSON
cat workspaces/workspace-id.json | jq .

# Validate structure
cat workspaces/workspace-id.json | jq '.model, .views' > /dev/null && echo "Valid"

# Check for specific elements
cat workspaces/workspace-id.json | jq '.model.people, .model.softwareSystems'
```

---

## Getting Help

If you're still experiencing issues after following this guide:

1. **Check the FAQ** - [FAQ](faq.md)
2. **Review common issues** - [Common Issues](common-issues.md)
3. **Enable DEBUG logging** and capture full logs
4. **Check GitHub issues** - [github.com/Cubical6/structurizr-mcp/issues](https://github.com/Cubical6/structurizr-mcp/issues)
5. **Create a bug report** with:
   - PHP version (`php -v`)
   - Operating system
   - Claude Desktop version
   - Full error logs
   - Steps to reproduce

---

<p align="right">
  <strong>Next:</strong> <a href="faq.md">FAQ →</a>
</p>
