# Upgrade Guide

- [Introduction](#introduction)
- [Version Compatibility](#version-compatibility)
- [Before Upgrading](#before-upgrading)
- [Upgrade Procedures](#upgrade-procedures)
- [Post-Upgrade Steps](#post-upgrade-steps)
- [Rollback Procedures](#rollback-procedures)
- [Troubleshooting](#troubleshooting)

---

## Introduction

This guide helps you safely upgrade your Structurizr MCP Server installation, whether you're upgrading between minor versions or applying patches.

> **Note:** Version 1.0.0 is the current stable release. Future minor and patch versions will maintain backward compatibility.

---

## Version Compatibility

### Compatibility Matrix

| From Version | To Version | Compatible | Notes |
|---|---|---|---|
| 1.0.0 | 1.0.x | ✅ Yes | Patch releases are fully compatible |
| 1.0.x | 1.1.0 | ✅ Yes | Minor versions maintain API compatibility |
| 1.x.x | 2.0.0 | ⚠️ Check | Major versions may require migration |

### Supported Versions

- **Current Release:** 1.0.0 (stable, production-ready)
- **Support Duration:** Until next major version (typically 2+ years)

### PHP Version Compatibility

| PHP Version | Status | Notes |
|---|---|---|
| PHP 8.0 | ❌ Not Supported | Missing required features |
| PHP 8.1+ | ✅ Supported | Required version and above |
| PHP 8.2+ | ✅ Supported | Fully compatible |
| PHP 8.3+ | ✅ Supported | Fully compatible |

---

## Before Upgrading

### Pre-Upgrade Checklist

Before you begin any upgrade, complete this checklist:

- [ ] Review release notes for the target version
- [ ] Check PHP version compatibility
- [ ] Backup your workspace files
- [ ] Export critical workspaces to DSL format
- [ ] Note your current configuration values
- [ ] Review any breaking changes (if applicable)
- [ ] Disable MCP clients during upgrade
- [ ] Have a rollback plan ready

### Backup Your Data

#### Backup Workspaces

Always backup your workspace files before upgrading:

```bash
# Create a timestamped backup directory
BACKUP_DIR="workspace-backup-$(date +%Y%m%d-%H%M%S)"
mkdir -p backups/$BACKUP_DIR

# Backup workspace directory
cp -r workspaces/* backups/$BACKUP_DIR/

# Backup session data
cp -r sessions/* backups/$BACKUP_DIR/

echo "Backup created in: backups/$BACKUP_DIR"
```

#### Export Critical Workspaces

For important workspaces, export to DSL format to ensure data recovery:

```bash
# Ask Claude to export a workspace
# "Export workspace [ID] to DSL format"

# Or use the Structurizr CLI directly
./bin/structurizr.sh export -w [workspace-id] -f dsl
```

This creates a DSL file that can be imported into any future version.

### Document Current Configuration

Save your current environment configuration:

```bash
# Create a configuration backup
cat > config-backup.txt << EOF
STRUCTURIZR_API_KEY=<value>
STRUCTURIZR_API_SECRET=<value>
STRUCTURIZR_API_URL=<value>
WORKSPACE_STORAGE_PATH=<value>
LOG_LEVEL=<value>
LOG_PATH=<value>
SERVER_NAME=<value>
SERVER_VERSION=<value>
EOF

# Keep this file in a safe location
```

---

## Upgrade Procedures

### Upgrading from 1.0.0 to 1.0.x (Patch Release)

Patch releases contain bug fixes and security updates. They're safe to apply immediately.

#### Step 1: Stop the Server

If the server is running, stop it gracefully:

```bash
# Gracefully stop the server (Ctrl+C)
# Wait for in-flight requests to complete

# Or kill the process if necessary
pkill -f "php server.php"
```

#### Step 2: Pull Latest Changes

Update your repository:

```bash
cd /path/to/structurizr-mcp

# Fetch latest changes
git fetch origin

# View changes
git log --oneline main..origin/main

# Merge changes
git merge origin/main
```

#### Step 3: Update Dependencies

Update PHP dependencies:

```bash
composer install --no-dev
```

> **Tip:** Use `--no-dev` in production to skip development dependencies and reduce package size.

#### Step 4: Clear Cache

Clear the discovery and session cache:

```bash
# Clear discovery cache
rm -rf cache/*

# Clear session cache
rm -rf sessions/*
```

#### Step 5: Restart the Server

Start the server with the updated version:

```bash
php server.php
```

Monitor logs for any issues:

```bash
# If using file logging
tail -f path/to/logs/structurizr-mcp.log

# Or check stderr output
```

### Upgrading to Minor Versions (1.0.0 to 1.1.0+)

Minor versions add new features while maintaining backward compatibility.

#### Complete the Patch Upgrade Steps

Follow all steps from [Patch Release Upgrade](#upgrading-from-100-to-10x-patch-release) above.

#### Check for New Features

Review the release notes for new tools, resources, or configuration options:

```bash
# Read release notes
cat CHANGELOG.md | grep "^## Version 1.1.0" -A 50

# Check for new environment variables
grep "new environment variable" CHANGELOG.md
```

#### Update Configuration (if needed)

If new features require configuration:

```bash
# Review Configuration documentation
cat docs/getting-started/configuration.md

# Add any new required environment variables
# No changes to existing variables are needed
```

#### Clear Discovery Cache

Minor versions may add new tools or resources:

```bash
# Clear discovery cache to force re-indexing
rm -rf cache/*

# Start server to regenerate cache
php server.php
```

### Manual Upgrade (without Git)

If you're not using Git, follow this procedure:

#### Step 1: Download New Release

1. Download the latest release from GitHub
2. Extract to a temporary directory

#### Step 2: Compare Changes

Compare key files:

```bash
# Compare configuration changes
diff old/Configuration.php new/Configuration.php

# Compare tool definitions
diff old/src/Tools new/src/Tools
```

#### Step 3: Backup Current Installation

```bash
# Create backup of current version
tar -czf structurizr-mcp-backup-1.0.0.tar.gz structurizr-mcp/

# Keep the backup file safe
```

#### Step 4: Install New Version

```bash
# Copy new files over
cp -r new/* structurizr-mcp/

# Update dependencies
cd structurizr-mcp
composer install --no-dev
```

#### Step 5: Verify Migration

```bash
# Test server startup
php server.php

# Check version (if available)
php server.php --version
```

---

## Post-Upgrade Steps

### Verify Installation

Test that the upgrade was successful:

#### Test Server Startup

```bash
# Start server
php server.php

# You should see initialization messages
# Server should not report any errors
```

#### Test with Claude

Ask Claude to list workspaces:

```
List all available workspaces
```

This confirms that the MCP connection and tools are working.

#### Check Logs

Review logs for any warnings or errors:

```bash
# View recent log entries
tail -50 path/to/logs/structurizr-mcp.log

# Look for errors or warnings
grep -E "ERROR|WARNING" path/to/logs/structurizr-mcp.log
```

### Test Critical Workflows

Test your most important use cases:

```bash
# Test workspace creation
# "Create a test workspace named 'upgrade-test'"

# Test adding elements
# "Add a person named 'Test User' to workspace [ID]"

# Test view creation
# "Create a system context view for the system in workspace [ID]"

# Test export
# "Export workspace [ID] to DSL"
```

### Update Documentation

Update any internal documentation that references the server version:

```bash
# Check for version references
grep -r "1.0.0" docs/ config/ README.md

# Update to new version
sed -i 's/1.0.0/1.0.x/g' docs/*.md
```

### Monitor for Issues

After upgrading, monitor the server for any problems:

- Check logs regularly for errors
- Monitor resource usage (CPU, memory, disk)
- Test all regular workflows
- Verify workspace creation and access
- Confirm export functionality works

---

## Rollback Procedures

If you encounter issues after upgrading, you can rollback to the previous version.

### Quick Rollback (using Git)

If you upgraded using Git, rollback is simple:

#### Step 1: Stop the Server

```bash
pkill -f "php server.php"
```

#### Step 2: Revert Changes

```bash
# Revert to previous version
git checkout v1.0.0

# Or reset to previous commit
git reset --hard HEAD~1
```

#### Step 3: Restore Dependencies

```bash
# Clear composer cache
composer clear-cache

# Reinstall dependencies for previous version
composer install --no-dev
```

#### Step 4: Clear Cache

```bash
# Clear discovery and session cache
rm -rf cache/* sessions/*
```

#### Step 5: Restart Server

```bash
# Start server with previous version
php server.php
```

### Manual Rollback (without Git)

If you don't use Git:

#### Step 1: Restore from Backup

```bash
# Stop server
pkill -f "php server.php"

# Restore backup
tar -xzf structurizr-mcp-backup-1.0.0.tar.gz

# Verify backup restored correctly
ls -la structurizr-mcp/
```

#### Step 2: Restore Dependencies

```bash
cd structurizr-mcp

# Clear cache
rm -rf cache/* sessions/*

# Reinstall dependencies
composer install --no-dev
```

#### Step 3: Restart Server

```bash
php server.php
```

### Data Recovery

If you need to recover workspace data:

#### From Backup

```bash
# Restore workspace backup
cp -r backups/workspace-backup-TIMESTAMP/* workspaces/

# Restart server
php server.php
```

#### From DSL Export

If you exported workspaces to DSL:

```bash
# Ask Claude to import DSL
# "Import the following DSL: [paste DSL content]"

# Or use the API tool directly
# import_from_dsl(dsl_content: "workspace ...")
```

---

## Troubleshooting

### Common Upgrade Issues

#### Issue: "Class not found" after upgrade

**Cause:** Composer dependencies not updated properly.

**Solution:**

```bash
# Clear composer cache
composer clear-cache

# Reinstall dependencies
composer install --no-dev

# Clear application cache
rm -rf cache/* sessions/*

# Restart server
php server.php
```

#### Issue: Server won't start after upgrade

**Cause:** PHP version incompatibility or missing dependencies.

**Solution:**

```bash
# Check PHP version
php -v

# Verify PHP version is 8.1 or higher
# If not, upgrade PHP before proceeding

# Check for errors
php -l server.php

# Verify all files extracted correctly
ls -la src/
```

#### Issue: Workspaces not accessible after upgrade

**Cause:** Workspace storage path issue or corrupted workspace files.

**Solution:**

```bash
# Verify workspace directory exists
ls -la workspaces/

# Check workspace permissions
chmod -R 755 workspaces/ cache/ sessions/

# Restore from backup if necessary
cp -r backups/workspace-backup-TIMESTAMP/* workspaces/

# Restart server
php server.php
```

#### Issue: Discovery cache issues

**Cause:** Old cache incompatible with new version.

**Solution:**

```bash
# Clear discovery cache completely
rm -rf cache/*

# Force cache regeneration
mkdir -p cache

# Restart server
php server.php
```

### Getting Help

If you encounter issues:

1. **Check Release Notes** - Review changes for your target version
2. **Check Logs** - Look for detailed error messages
3. **Consult Documentation** - See [Troubleshooting Guide](../troubleshooting/common-issues.md)
4. **Report Issues** - Open an issue on [GitHub](https://github.com/Cubical6/structurizr-mcp/issues)

---

## Version History

| Version | Release Date | Status | PHP Requirement |
|---|---|---|---|
| 1.0.0 | Nov 16, 2024 | Stable | PHP 8.1+ |

---

<p align="center">
  <strong>Questions about upgrading?</strong> Check the <a href="./faq.md">FAQ</a> or open an <a href="https://github.com/Cubical6/structurizr-mcp/issues">issue</a>.
</p>
