# Troubleshooting

Welcome to the Structurizr MCP Server troubleshooting guide. This section provides comprehensive resources for diagnosing and resolving issues.

---

## Quick Navigation

### 🔧 [Common Issues](common-issues.md)
**Top 20 problems and their solutions**, organized by category:
- Installation issues (3 issues)
- Configuration problems (3 issues)
- Claude Desktop connection (4 issues)
- Tool execution errors (5 issues)
- Workspace storage (3 issues)
- CLI integration (2 issues)

**Start here if:** You're experiencing a specific error or problem.

---

### 🐛 [Debugging Guide](debugging.md)
**Detailed debugging techniques and diagnostics** for troubleshooting:
- Enabling debug logging
- Reading and interpreting log output
- Testing server manually
- Verifying configuration
- Understanding common error messages
- Advanced debugging with Xdebug and profiling

**Start here if:** You need to investigate an issue in depth or collect diagnostic information.

---

### ❓ [FAQ](faq.md)
**30 frequently asked questions** covering all aspects of the server:
- Getting started (4 questions)
- Installation & setup (4 questions)
- Using the server (4 questions)
- Workspaces & models (5 questions)
- Export & integration (3 questions)
- Troubleshooting (3 questions)
- Advanced topics (7 questions)

**Start here if:** You have a general question about how something works.

---

## Quick Problem Solver

### "Tools not showing in Claude Desktop"
→ See [FAQ Q5](faq.md#q5-why-arent-tools-showing-up-in-claude-desktop) and [Common Issues #7](common-issues.md#issue-7-tools-not-appearing-in-claude-desktop)

### "Workspace not found" errors
→ See [FAQ Q13](faq.md#q13-how-do-i-fix-workspace-not-found-errors) and [Common Issues #11](common-issues.md#issue-11-workspace-not-found-errors)

### Server disconnects or crashes
→ See [FAQ Q21](faq.md#q21-why-does-the-server-keep-disconnecting) and [Debugging Guide](debugging.md#testing-server-manually)

### CLI integration problems
→ See [FAQ Q6](faq.md#q6-do-i-need-to-install-the-structurizr-cli) and [Common Issues #4, #19](common-issues.md#issue-4-structurizr_cli_path-environment-variable-not-set)

### Configuration issues
→ See [Common Issues - Configuration Problems](common-issues.md#configuration-problems) and [Debugging - Verifying Configuration](debugging.md#verifying-configuration)

---

## Troubleshooting Workflow

Follow this systematic approach to resolve issues:

### Step 1: Identify the Problem
- Note the exact error message
- Determine when the issue occurs (startup, tool execution, etc.)
- Check if it's reproducible

### Step 2: Check Documentation
1. **Search the FAQ** for your question → [FAQ](faq.md)
2. **Review Common Issues** for your error → [Common Issues](common-issues.md)
3. **Check relevant guides**:
   - [Installation Guide](../getting-started/installation.md)
   - [Configuration Guide](../getting-started/configuration.md)
   - [Quick Start](../getting-started/quick-start.md)

### Step 3: Enable Debugging
1. **Enable DEBUG logging** → [Debugging Guide](debugging.md#enabling-debug-logging)
2. **Test server manually** → [Debugging Guide](debugging.md#testing-server-manually)
3. **Verify configuration** → [Debugging Guide](debugging.md#verifying-configuration)

### Step 4: Collect Information
Gather this information before seeking help:
- PHP version: `php -v`
- Operating system and version
- Claude Desktop version
- Full error logs with DEBUG enabled
- Configuration (sanitized, no secrets)
- Steps to reproduce

### Step 5: Seek Help
If still unresolved:
1. **Search GitHub issues**: [github.com/Cubical6/structurizr-mcp/issues](https://github.com/Cubical6/structurizr-mcp/issues)
2. **Create new issue** with collected information
3. **Check MCP community** for protocol-related issues

---

## Most Common Issues

Based on user reports, these are the top issues:

| Issue | Frequency | Solution |
|-------|-----------|----------|
| Tools not showing in Claude | Very High | [FAQ Q5](faq.md#q5-why-arent-tools-showing-up-in-claude-desktop) |
| Relative paths in config | High | [Common Issues #5](common-issues.md#issue-5-failed-to-create-workspace-storage-directory) |
| Workspace not found | High | [FAQ Q13](faq.md#q13-how-do-i-fix-workspace-not-found-errors) |
| CLI not configured | Medium | [Common Issues #4](common-issues.md#issue-4-structurizr_cli_path-environment-variable-not-set) |
| Permission denied | Medium | [Common Issues #3](common-issues.md#issue-3-permission-denied-during-installation) |
| Server disconnects | Medium | [FAQ Q21](faq.md#q21-why-does-the-server-keep-disconnecting) |
| Invalid DSL syntax | Low | [FAQ Q22](faq.md#q22-invalid-dsl-errors---how-do-i-fix-them) |
| Memory limit exceeded | Low | [Debugging Guide](debugging.md#error-memory-limit-exhausted) |

---

## Prevention Best Practices

Avoid common issues by following these practices:

### ✅ Configuration
- **Always use absolute paths** in Claude Desktop config
- **Set required environment variables** (WORKSPACE_STORAGE_PATH minimum)
- **Use php://stderr for logging** in MCP context
- **Test configuration** before deploying

### ✅ Installation
- **Verify PHP 8.1+** before installing
- **Install all Composer dependencies** with `composer install`
- **Set proper permissions** on cache, sessions, workspaces directories
- **Download CLI** if you need PlantUML/Mermaid export

### ✅ Usage
- **Keep workspace IDs** returned from create operations
- **List workspaces regularly** to verify what exists
- **Export backups** of important workspaces
- **Use version control** for workspace files

### ✅ Maintenance
- **Monitor logs** for warnings and errors
- **Clear cache** if experiencing discovery issues
- **Update dependencies** periodically with `composer update`
- **Backup workspaces** before major changes

---

## Environment-Specific Issues

### macOS
- **Homebrew PHP**: May need to link PHP 8.1 - `brew link php@8.1`
- **Permissions**: Use `~/Library/Application Support/Claude/` for config
- **Java for CLI**: Install with `brew install openjdk`

### Linux
- **PHP Extensions**: Install php-mbstring, php-xml, php-curl
- **SELinux**: May block file access - check with `getenforce`
- **Permissions**: Ensure web server user can write to directories

### Windows
- **Path separators**: Use forward slashes in config: `C:/path/to/file`
- **PHP extensions**: Enable in php.ini: `extension=mbstring`
- **Line endings**: Use Git with `core.autocrlf=true`

---

## Related Documentation

### Getting Started
- [Installation](../getting-started/installation.md) - Setup instructions
- [Configuration](../getting-started/configuration.md) - Environment variables
- [Quick Start](../getting-started/quick-start.md) - First workspace tutorial

### Reference
- [Tools Overview](../tools/overview.md) - All 23 MCP tools
- [Resources Overview](../resources/overview.md) - All 7 MCP resources
- [Prompts](../prompts/README.md) - All 7 MCP prompts

### Advanced
- [Extending the Server](../advanced/extending.md) - Custom tools and resources
- [Performance Optimization](../advanced/performance.md) - Handling large workspaces
- [Security Considerations](../advanced/security.md) - Best practices

---

## Contributing to Troubleshooting Docs

Found a solution not documented here? Help others by:

1. **Opening an issue** describing the problem and solution
2. **Submitting a PR** to add it to this documentation
3. **Sharing your experience** in GitHub discussions

Guidelines:
- Provide clear problem descriptions
- Include exact error messages
- Show concrete solutions with commands/code
- Test solutions before documenting
- Follow the Laravel documentation style

---

<p align="right">
  <strong>Back to:</strong> <a href="../README.md">Documentation Home</a>
</p>
