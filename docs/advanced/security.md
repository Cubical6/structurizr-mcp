# Security Best Practices

- [Introduction](#introduction)
- [Input Validation](#input-validation)
- [Path Traversal Prevention](#path-traversal-prevention)
- [Credential Management](#credential-management)
- [Command Injection Protection](#command-injection-protection)
- [File Permissions](#file-permissions)
- [Security Checklist](#security-checklist)

---

## Introduction

Structurizr MCP Server implements multiple security layers to protect your architecture data and prevent common vulnerabilities. This guide covers the security features built into the server and best practices for maintaining a secure deployment.

> **Important:** Security is a shared responsibility. While the server implements robust protections, proper configuration and operational practices are essential for maintaining security.

---

## Input Validation

All user inputs are validated before processing to prevent malicious data from affecting the system.

### Schema-Based Validation

The server uses JSON Schema attributes to enforce input constraints at the protocol level:

```php
#[Schema(description: 'Workspace name', minLength: 1, maxLength: 100)]
string $name,

#[Schema(description: 'System description', maxLength: 500)]
string $description = ''
```

This provides:
- **Length limits** - Prevents buffer overflow and excessive resource usage
- **Type safety** - Ensures data matches expected types
- **Required fields** - Validates all mandatory inputs are provided

### String Sanitization

All string inputs are sanitized before use:

```php
// Special characters are escaped
$safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

// Path components are validated
$safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $workspaceId);
```

> **Best Practice:** Never trust user input. Always validate and sanitize before processing.

### DSL Content Validation

When importing DSL content, the server validates structure before execution:

```php
// Validates DSL syntax without executing commands
$result = $this->cliWrapper->validateDsl($dslContent);

if (!$result->isSuccess()) {
    throw new InvalidDslException($result->getError());
}
```

This prevents:
- Malformed DSL from corrupting workspaces
- Injection of malicious DSL commands
- Resource exhaustion from invalid structures

---

## Path Traversal Prevention

Path traversal attacks are prevented through multiple defensive layers.

### Workspace ID Validation

Workspace IDs are strictly validated to prevent directory traversal:

```php
private function validateWorkspaceId(string $workspaceId): void
{
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $workspaceId)) {
        throw new InvalidArgumentException(
            'Workspace ID contains invalid characters'
        );
    }

    if (str_contains($workspaceId, '..')) {
        throw new SecurityException(
            'Path traversal detected in workspace ID'
        );
    }
}
```

### Path Resolution

All file paths are resolved to absolute paths and validated:

```php
$workspacePath = $this->storagePath . '/' . $workspaceId;
$realPath = realpath($workspacePath);

// Ensure path is within allowed directory
if (!$realPath || !str_starts_with($realPath, $this->storagePath)) {
    throw new SecurityException('Invalid workspace path');
}
```

This prevents:
- `../../../etc/passwd` style attacks
- Symbolic link traversal
- Access to files outside workspace directory

### Allowed Directories

File operations are restricted to specific directories:

```php
private const ALLOWED_DIRECTORIES = [
    'workspaces',
    'cache',
    'sessions',
];

private function isPathAllowed(string $path): bool
{
    $realPath = realpath($path);

    foreach (self::ALLOWED_DIRECTORIES as $dir) {
        $allowedPath = realpath($this->basePath . '/' . $dir);
        if (str_starts_with($realPath, $allowedPath)) {
            return true;
        }
    }

    return false;
}
```

> **Security Note:** The server never accesses files outside designated directories. System files and user home directories are protected.

---

## Credential Management

API credentials and sensitive data are handled securely throughout the server lifecycle.

### Environment Variables

Credentials are loaded exclusively from environment variables, never from code or configuration files:

```bash
# Correct - Environment variable
export STRUCTURIZR_API_KEY="your-api-key"
export STRUCTURIZR_API_SECRET="your-api-secret"

# Wrong - Never commit credentials to version control
```

### Credential Sanitization

Credentials are sanitized in logs and error messages:

```php
private function sanitizeForLog(string $message): string
{
    $patterns = [
        '/api[_-]?key["\s:=]+([^\s"]+)/i' => 'api_key="***"',
        '/api[_-]?secret["\s:=]+([^\s"]+)/i' => 'api_secret="***"',
        '/password["\s:=]+([^\s"]+)/i' => 'password="***"',
        '/token["\s:=]+([^\s"]+)/i' => 'token="***"',
    ];

    return preg_replace(
        array_keys($patterns),
        array_values($patterns),
        $message
    );
}
```

### Memory Cleanup

Sensitive data is cleared from memory after use:

```php
try {
    $apiKey = $this->config->getApiKey();
    $result = $this->apiClient->authenticate($apiKey);
} finally {
    // Clear sensitive data
    $apiKey = null;
    if (function_exists('sodium_memzero')) {
        sodium_memzero($apiKey);
    }
}
```

### Configuration File Security

When using configuration files (not recommended), ensure proper permissions:

```bash
# Make config files readable only by owner
chmod 600 .env
chmod 600 config/credentials.php

# Never commit sensitive files
echo ".env" >> .gitignore
echo "config/credentials.php" >> .gitignore
```

> **Best Practice:** Use environment variables or secure credential management systems like HashiCorp Vault or AWS Secrets Manager in production.

---

## Command Injection Protection

The server executes external commands (Structurizr CLI) with strict injection prevention.

### Argument Escaping

All command arguments are properly escaped:

```php
use Symfony\Component\Process\Process;

private function executeCommand(array $arguments): ProcessResult
{
    // Symfony Process handles escaping automatically
    $process = new Process([
        $this->cliPath,
        ...$arguments
    ]);

    // Never use shell syntax
    // Wrong: $process = Process::fromShellCommandline("cli $arg");

    $process->run();

    return new ProcessResult(
        $process->getExitCode(),
        $process->getOutput(),
        $process->getErrorOutput()
    );
}
```

### Input Sanitization

User inputs are validated before being passed to CLI commands:

```php
private function sanitizeCliArgument(string $argument): string
{
    // Remove shell metacharacters
    $dangerous = ['&', '|', ';', '>', '<', '`', '$', '(', ')', '{', '}'];
    $safe = str_replace($dangerous, '', $argument);

    // Validate result
    if ($safe !== $argument) {
        throw new SecurityException(
            'Argument contains potentially dangerous characters'
        );
    }

    return $safe;
}
```

### Command Whitelisting

Only whitelisted CLI commands are allowed:

```php
private const ALLOWED_CLI_COMMANDS = [
    'validate',
    'export',
    'push',
    'pull',
];

private function validateCommand(string $command): void
{
    if (!in_array($command, self::ALLOWED_CLI_COMMANDS, true)) {
        throw new SecurityException(
            "Command '$command' is not allowed"
        );
    }
}
```

### Process Timeout

Commands are executed with timeouts to prevent resource exhaustion:

```php
$process = new Process($command);
$process->setTimeout(30); // 30 second timeout
$process->setIdleTimeout(10); // 10 second idle timeout

try {
    $process->mustRun();
} catch (ProcessTimedOutException $e) {
    throw new CliExecutionException(
        'Command timed out after 30 seconds'
    );
}
```

> **Security Note:** The server never uses `shell_exec()`, `exec()`, or `system()` functions. All command execution goes through Symfony Process with proper escaping.

---

## File Permissions

Proper file permissions are critical for security.

### Directory Permissions

Set appropriate permissions for server directories:

```bash
# Server installation directory
chmod 755 /path/to/structurizr-mcp

# Application code (read-only)
chmod 755 src/
find src/ -type f -exec chmod 644 {} \;

# Writable directories
chmod 755 workspaces/
chmod 755 cache/
chmod 755 sessions/

# Files in writable directories
find workspaces/ -type f -exec chmod 644 {} \;
find cache/ -type f -exec chmod 644 {} \;
find sessions/ -type f -exec chmod 644 {} \;
```

### Server Process User

Run the server as a dedicated user with minimal privileges:

```bash
# Create dedicated user
sudo useradd -r -s /bin/false structurizr

# Set ownership
sudo chown -R structurizr:structurizr /path/to/structurizr-mcp

# Run server as dedicated user
sudo -u structurizr php server.php
```

> **Production Tip:** Never run the MCP server as root or with elevated privileges.

### File Creation Mask

Set umask to create files with secure permissions:

```php
// Set umask at server startup
umask(0022); // Creates files as 644, directories as 755

// Or more restrictive
umask(0077); // Creates files as 600, directories as 700
```

### Workspace File Protection

Workspace files contain sensitive architecture data:

```bash
# Restrict workspace access to owner only
chmod 700 workspaces/
find workspaces/ -type f -exec chmod 600 {} \;

# Or group-readable for team access
chmod 750 workspaces/
find workspaces/ -type f -exec chmod 640 {} \;
```

---

## Security Checklist

Use this checklist to ensure your deployment follows security best practices:

### Installation
- [ ] PHP version 8.1 or higher with latest security patches
- [ ] All dependencies up-to-date (`composer update`)
- [ ] Development dependencies removed in production (`composer install --no-dev`)
- [ ] Server running as non-root user with minimal privileges

### Configuration
- [ ] Credentials stored in environment variables, not files
- [ ] `.env` file not committed to version control
- [ ] API keys rotated regularly (every 90 days recommended)
- [ ] Logging configured to sanitize sensitive data
- [ ] Error messages don't expose system details

### File System
- [ ] Workspace directory outside web root
- [ ] Directory permissions set correctly (755 for directories, 644 for files)
- [ ] Workspace files protected (700/600 for private, 750/640 for shared)
- [ ] Symbolic links disabled or strictly controlled
- [ ] No world-writable files or directories

### Network
- [ ] MCP server not directly exposed to internet
- [ ] Communication through trusted MCP client only
- [ ] TLS/SSL enabled for any network transport
- [ ] Firewall rules restrict access to authorized clients

### Operations
- [ ] Logs monitored for suspicious activity
- [ ] Regular backups of workspace data
- [ ] Backup files encrypted and stored securely
- [ ] Incident response plan documented
- [ ] Security updates applied promptly

### Monitoring
- [ ] Failed authentication attempts logged
- [ ] Path traversal attempts detected and logged
- [ ] Command injection attempts blocked and logged
- [ ] Resource usage monitored for DoS attacks
- [ ] Unusual workspace access patterns alerted

> **Compliance Note:** If handling sensitive architecture data subject to regulations (GDPR, HIPAA, SOC 2), consult with your security team to ensure compliance requirements are met.

---

## Security Updates

Stay informed about security updates:

1. **Watch the repository** for security announcements
2. **Subscribe to PHP security advisories** at [php.net/security](https://www.php.net/security/)
3. **Monitor dependency vulnerabilities** with `composer audit`
4. **Review security logs** regularly for anomalies

### Reporting Security Issues

If you discover a security vulnerability, please report it responsibly:

1. **Do not** create a public GitHub issue
2. **Email** security details to the maintainers
3. **Include** steps to reproduce and potential impact
4. **Allow** reasonable time for a fix before public disclosure

We take security seriously and will respond promptly to verified reports.

---

<p align="right">
  <strong>Next:</strong> <a href="performance.md">Performance Optimization →</a>
</p>
