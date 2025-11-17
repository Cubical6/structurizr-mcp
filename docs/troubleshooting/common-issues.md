# Common Issues

- [Installation Issues](#installation-issues)
- [Configuration Problems](#configuration-problems)
- [Claude Desktop Connection](#claude-desktop-connection)
- [Tool Execution Errors](#tool-execution-errors)
- [Workspace Storage](#workspace-storage)
- [CLI Integration](#cli-integration)

---

## Installation Issues

### Issue 1: "composer: command not found"

**Problem:** Composer is not installed or not in your system PATH.

**Solution:**

Install Composer by following the official guide at [getcomposer.org](https://getcomposer.org/download/):

```bash
# macOS/Linux
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

Verify installation:

```bash
composer --version
```

---

### Issue 2: "Your requirements could not be resolved"

**Problem:** Incompatible PHP version or missing PHP extensions.

**Solution:**

1. **Check PHP version** (must be 8.1 or higher):

```bash
php -v
```

2. **Install required PHP extensions**:

```bash
# Ubuntu/Debian
sudo apt-get install php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip

# macOS (Homebrew)
brew install php@8.1

# Windows (check php.ini and enable extensions)
extension=mbstring
extension=xml
extension=curl
```

3. **Clear Composer cache and retry**:

```bash
composer clear-cache
composer install
```

---

### Issue 3: "Permission denied" during installation

**Problem:** Insufficient permissions for directories.

**Solution:**

Set proper permissions for cache, sessions, and workspaces directories:

```bash
chmod -R 755 cache/ sessions/ workspaces/
# If still having issues, use 777 (less secure, but works)
chmod -R 777 cache/ sessions/ workspaces/
```

On shared hosting or restricted environments:

```bash
# Change ownership to web server user
chown -R www-data:www-data cache/ sessions/ workspaces/
```

---

## Configuration Problems

### Issue 4: "STRUCTURIZR_CLI_PATH environment variable not set"

**Problem:** Server cannot find Structurizr CLI path.

**Solution:**

1. **Option A - Download and configure CLI:**

```bash
# Create bin directory
mkdir -p bin/

# Download latest CLI (replace URL with latest version)
wget https://github.com/structurizr/cli/releases/download/v1.30.0/structurizr-cli-1.30.0.zip

# Extract
unzip structurizr-cli-1.30.0.zip -d bin/
chmod +x bin/structurizr.sh
```

Add to Claude Desktop config:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/absolute/path/to/structurizr-mcp/server.php"],
      "env": {
        "STRUCTURIZR_CLI_PATH": "/absolute/path/to/structurizr-mcp/bin/structurizr.sh",
        "WORKSPACE_STORAGE_PATH": "/absolute/path/to/structurizr-mcp/workspaces"
      }
    }
  }
}
```

2. **Option B - Skip CLI (limited functionality):**

The server works without CLI but some export features will be unavailable. Simply omit the environment variable.

---

### Issue 5: "Failed to create workspace storage directory"

**Problem:** WORKSPACE_STORAGE_PATH is invalid or not writable.

**Solution:**

1. **Verify path exists and is absolute**:

```bash
# Create directory
mkdir -p /absolute/path/to/structurizr-mcp/workspaces

# Set permissions
chmod 755 /absolute/path/to/structurizr-mcp/workspaces
```

2. **Update Claude Desktop config** with absolute path:

```json
{
  "env": {
    "WORKSPACE_STORAGE_PATH": "/Users/yourname/projects/structurizr-mcp/workspaces"
  }
}
```

> **Important:** Always use absolute paths, not relative paths like `./workspaces`.

---

### Issue 6: Invalid log level or log path

**Problem:** Server fails to start due to invalid LOG_LEVEL or LOG_PATH.

**Solution:**

Use valid log levels and paths:

```json
{
  "env": {
    "LOG_LEVEL": "INFO",
    "LOG_PATH": "php://stderr"
  }
}
```

Valid log levels (case-sensitive):
- `DEBUG` - Most verbose
- `INFO` - Normal operation
- `WARNING` - Warnings only
- `ERROR` - Errors only

Valid log paths:
- `php://stderr` - Standard error (default, recommended for Claude Desktop)
- `/absolute/path/to/file.log` - File path
- `php://stdout` - Standard output (not recommended)

---

## Claude Desktop Connection

### Issue 7: Tools not appearing in Claude Desktop

**Problem:** MCP server is running but tools don't show up in Claude.

**Solution:**

1. **Check server is running properly**:

```bash
# Test server manually
php /absolute/path/to/structurizr-mcp/server.php
```

Look for "Server ready" message.

2. **Verify Claude Desktop configuration**:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/absolute/path/to/structurizr-mcp/server.php"],
      "env": {
        "STRUCTURIZR_CLI_PATH": "/absolute/path/to/bin/structurizr.sh",
        "WORKSPACE_STORAGE_PATH": "/absolute/path/to/workspaces"
      }
    }
  }
}
```

3. **Restart Claude Desktop completely**:
   - Quit Claude Desktop (not just close window)
   - Kill any background processes
   - Start Claude Desktop again

4. **Check Claude Desktop logs**:
   - macOS: `~/Library/Logs/Claude/`
   - Windows: `%APPDATA%\Claude\logs\`
   - Linux: `~/.config/Claude/logs/`

---

### Issue 8: "Server disconnected" or connection timeouts

**Problem:** Server starts but disconnects immediately.

**Solution:**

1. **Check for PHP errors**:

```bash
# Run server with error output
php -d display_errors=1 /absolute/path/to/structurizr-mcp/server.php
```

2. **Verify PHP memory limit**:

```bash
# Check current limit
php -r "echo ini_get('memory_limit') . PHP_EOL;"

# Increase if needed (in php.ini)
memory_limit = 256M
```

3. **Check for conflicting processes**:

```bash
# Find processes using stdio
lsof | grep stdio
```

4. **Enable debug logging**:

```json
{
  "env": {
    "LOG_LEVEL": "DEBUG"
  }
}
```

---

### Issue 9: "Invalid JSON-RPC message" errors

**Problem:** Communication protocol errors between Claude and server.

**Solution:**

1. **Ensure no output to stdout** - The server should only log to stderr:

```json
{
  "env": {
    "LOG_PATH": "php://stderr"
  }
}
```

2. **Check for syntax errors**:

```bash
php -l server.php
```

3. **Update MCP SDK** to latest version:

```bash
composer update mcp/sdk
```

4. **Clear discovery cache**:

```bash
rm -rf cache/*
```

---

### Issue 10: MCP server shows "Unknown" in Claude Desktop

**Problem:** Server connects but shows as "Unknown" or has no metadata.

**Solution:**

This usually means the server info isn't being sent properly. Check configuration:

```json
{
  "env": {
    "SERVER_NAME": "structurizr-mcp-server",
    "SERVER_VERSION": "1.0.0"
  }
}
```

If still showing "Unknown", restart Claude Desktop completely.

---

## Tool Execution Errors

### Issue 11: "Workspace not found" errors

**Problem:** Tools fail with "Workspace with ID 'xxx' not found".

**Solution:**

1. **List available workspaces**:

Use the `list_workspaces` tool in Claude to see all workspace IDs.

2. **Check workspace file exists**:

```bash
ls -la /path/to/workspaces/
```

You should see `.json` files with workspace IDs as filenames.

3. **Create workspace if missing**:

Use `create_workspace` tool to create a new workspace:

```
Create a new workspace called "My Architecture"
```

4. **Verify workspace ID format**:

Workspace IDs should be UUIDs (e.g., `550e8400-e29b-41d4-a716-446655440000`).

---

### Issue 12: "Element not found" when adding relationships

**Problem:** Cannot create relationships between elements.

**Solution:**

1. **Verify element IDs** - Use exact IDs returned from element creation:

```
✓ Correct: Use the systemId returned from add_software_system
✗ Wrong: Guessing the element ID
```

2. **Check element exists in workspace**:

Use `get_workspace` to see all elements and their IDs.

3. **Ensure elements are in same workspace**:

Relationships can only be created between elements in the same workspace.

---

### Issue 13: "Invalid DSL" errors during import

**Problem:** Importing DSL content fails with syntax errors.

**Solution:**

1. **Validate DSL syntax** before importing:

```bash
# If you have Structurizr CLI
./bin/structurizr.sh validate -w workspace.dsl
```

2. **Common DSL syntax errors**:

```dsl
# Wrong - missing quotes
user = person User A description

# Correct - quotes around multi-word descriptions
user = person "User" "A description"

# Wrong - invalid relationship syntax
user -> system

# Correct - include description
user -> system "Uses"
```

3. **Check for special characters**:

Escape special characters in descriptions:

```dsl
# Problematic
description "It's a system"

# Better
description "It is a system"
```

---

### Issue 14: View creation fails with "Key already exists"

**Problem:** Cannot create view with duplicate key.

**Solution:**

1. **Use unique view keys**:

```
✓ Correct: SystemContext, Containers, Components-WebApp
✗ Wrong: View, View, View
```

2. **List existing views** using `get_workspace` to see used keys.

3. **Delete or rename conflicting view**:

Currently, you need to manually edit the workspace JSON file or create a new workspace.

---

### Issue 15: Export fails with "CLI not configured"

**Problem:** PlantUML or Mermaid export fails.

**Solution:**

1. **Install Structurizr CLI** (see Issue 4).

2. **Configure CLI path** in environment:

```json
{
  "env": {
    "STRUCTURIZR_CLI_PATH": "/absolute/path/to/bin/structurizr.sh"
  }
}
```

3. **Verify CLI works**:

```bash
/path/to/bin/structurizr.sh version
```

4. **Use DSL export as alternative**:

DSL export works without CLI - use `export_to_dsl` tool.

---

## Workspace Storage

### Issue 16: Workspaces not persisting between sessions

**Problem:** Workspaces disappear after restarting server.

**Solution:**

1. **Check workspace storage path is persistent**:

```bash
# Verify files exist
ls -la /path/to/workspaces/

# Check permissions
ls -ld /path/to/workspaces/
```

2. **Ensure path is absolute** in configuration:

```json
{
  "env": {
    "WORKSPACE_STORAGE_PATH": "/Users/john/structurizr-mcp/workspaces"
  }
}
```

3. **Check disk space**:

```bash
df -h /path/to/workspaces/
```

---

### Issue 17: Workspace files corrupted

**Problem:** Workspace JSON files are invalid or corrupted.

**Solution:**

1. **Backup corrupted file**:

```bash
cp workspaces/workspace-id.json workspaces/workspace-id.json.backup
```

2. **Validate JSON syntax**:

```bash
cat workspaces/workspace-id.json | python -m json.tool
```

3. **Restore from backup** or create new workspace:

```bash
# If you have git tracking
git checkout workspaces/workspace-id.json

# Or create new workspace
rm workspaces/workspace-id.json
# Use create_workspace tool to start fresh
```

---

### Issue 18: "Cannot write to workspace file" errors

**Problem:** Permission denied when saving workspace.

**Solution:**

1. **Fix file permissions**:

```bash
chmod 644 workspaces/*.json
chmod 755 workspaces/
```

2. **Check file ownership**:

```bash
ls -la workspaces/
# Should match the user running PHP
chown youruser:yourgroup workspaces/*.json
```

3. **Verify directory is writable**:

```bash
touch workspaces/test.txt
rm workspaces/test.txt
```

---

## CLI Integration

### Issue 19: "Structurizr CLI execution failed"

**Problem:** CLI commands fail to execute.

**Solution:**

1. **Verify CLI is executable**:

```bash
chmod +x /path/to/bin/structurizr.sh

# Test execution
/path/to/bin/structurizr.sh version
```

2. **Check Java is installed** (CLI requires Java):

```bash
java -version
```

If Java is not installed:

```bash
# Ubuntu/Debian
sudo apt-get install default-jre

# macOS
brew install openjdk

# Windows - download from java.com
```

3. **Set JAVA_HOME** if needed:

```bash
# Find Java installation
/usr/libexec/java_home  # macOS
update-alternatives --list java  # Linux

# Set in environment
export JAVA_HOME=/path/to/java
```

4. **Check CLI script** has correct shebang:

```bash
head -1 /path/to/bin/structurizr.sh
# Should be: #!/bin/bash or similar
```

---

### Issue 20: CLI timeout on large workspaces

**Problem:** Export operations timeout on complex workspaces.

**Solution:**

1. **Increase PHP execution timeout**:

```bash
# In php.ini
max_execution_time = 300

# Or via code (if allowed)
ini_set('max_execution_time', 300);
```

2. **Optimize workspace** by reducing complexity:
   - Remove unused elements
   - Limit number of views
   - Split into multiple workspaces

3. **Use asynchronous processing** (future enhancement):

Currently, all operations are synchronous. For very large workspaces, consider:
- Exporting smaller views individually
- Using CLI directly outside of MCP server
- Breaking workspace into smaller modules

---

## Additional Resources

- [Debugging Guide](debugging.md) - Detailed debugging techniques
- [FAQ](faq.md) - Frequently asked questions
- [Configuration Guide](../getting-started/configuration.md) - Environment setup
- [MCP Documentation](https://modelcontextprotocol.io) - MCP protocol details

---

<p align="right">
  <strong>Next:</strong> <a href="debugging.md">Debugging Guide →</a>
</p>
