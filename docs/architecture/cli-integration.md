# CLI Integration

## Introduction

The Structurizr CLI is the official command-line tool for validating, exporting, and synchronizing Structurizr workspaces. The `CliWrapper` class provides a **secure, type-safe interface** for executing CLI commands from the MCP server.

## Architecture

```
┌──────────────────────────────────────────────────────────┐
│                      CliWrapper                           │
│                                                           │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐      │
│  │  validate() │  │   export()  │  │push()/pull()│      │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘      │
│         │                │                 │              │
│         └────────────────┴─────────────────┘              │
│                         │                                 │
│                  ┌──────▼──────────┐                      │
│                  │ executeCommand()│                      │
│                  └──────┬──────────┘                      │
│                         │                                 │
└─────────────────────────┼─────────────────────────────────┘
                          │
                          ▼
              ┌────────────────────────┐
              │  Symfony Process       │
              │  (Array form, secure)  │
              └───────────┬────────────┘
                          │
                          ▼
                  ┌───────────────┐
                  │ Structurizr   │
                  │     CLI       │
                  └───────────────┘
```

## Structurizr CLI Overview

### What is Structurizr CLI?

The Structurizr CLI is a Java-based command-line tool that provides:

1. **Validation** - Check DSL syntax and model consistency
2. **Export** - Convert to PlantUML, Mermaid, DOT, etc.
3. **Push/Pull** - Sync with Structurizr Cloud/On-premises
4. **List** - Show workspace contents

### Installation

**Download:**

```bash
# Download from GitHub releases
wget https://github.com/structurizr/cli/releases/latest/download/structurizr-cli.zip

# Extract
unzip structurizr-cli.zip -d /path/to/structurizr-cli

# Make executable
chmod +x /path/to/structurizr-cli/structurizr.sh
```

**Configure environment:**

```bash
export STRUCTURIZR_CLI_PATH="/path/to/structurizr-cli/structurizr.sh"
```

### CLI Commands

**Validate:**
```bash
structurizr.sh validate -workspace workspace.dsl
```

**Export:**
```bash
structurizr.sh export -workspace workspace.dsl -format plantuml
structurizr.sh export -workspace workspace.dsl -format mermaid
structurizr.sh export -workspace workspace.dsl -format dot
```

**Push to cloud:**
```bash
structurizr.sh push -id 12345 -key API_KEY -secret API_SECRET -workspace workspace.dsl
```

**Pull from cloud:**
```bash
structurizr.sh pull -id 12345 -key API_KEY -secret API_SECRET -workspace workspace.json
```

## CliWrapper Implementation

### Initialization

```php
class CliWrapper
{
    private readonly string $cliPath;

    public function __construct(
        string $cliPath,
        private readonly LoggerInterface $logger,
    ) {
        // Validate CLI path exists
        $resolvedPath = realpath($cliPath);
        if ($resolvedPath === false) {
            throw new CliExecutionException(
                'structurizr-cli',
                "CLI executable not found at path: {$cliPath}"
            );
        }

        // Validate CLI is executable
        if (!is_executable($resolvedPath)) {
            throw new CliExecutionException(
                'structurizr-cli',
                "CLI path is not executable: {$resolvedPath}"
            );
        }

        $this->cliPath = $resolvedPath;
        $this->logger->info('CliWrapper initialized', ['cliPath' => $this->cliPath]);
    }
}
```

**Validation checks:**

1. **Path exists** - File exists on filesystem
2. **Executable** - File has execute permissions
3. **Absolute path** - Resolved to absolute path with `realpath()`

### Command Execution

#### Core Execute Method

```php
public function executeCommand(
    array $args,
    int $timeout = 30
): ProcessResult {
    // Build command array (ARRAY form for security)
    $command = array_merge([$this->cliPath], $args);

    // Sanitize command for logging (remove credentials)
    $sanitizedCommand = $this->sanitizeCommandForLogging($command);

    $this->logger->debug('Executing CLI command', [
        'command' => $sanitizedCommand,
        'timeout' => $timeout,
    ]);

    try {
        // Create process with array form (prevents shell injection)
        $process = new Process($command);
        $process->setTimeout($timeout);
        $process->run();

        $result = new ProcessResult(
            exitCode: $process->getExitCode() ?? 1,
            stdout: $process->getOutput(),
            stderr: $process->getErrorOutput(),
            success: $process->isSuccessful(),
        );

        if ($result->isSuccess()) {
            $this->logger->debug('CLI command successful', [
                'exitCode' => $result->getExitCode(),
            ]);
        } else {
            $this->logger->warning('CLI command failed', [
                'exitCode' => $result->getExitCode(),
                'error' => $result->getErrorMessage(),
            ]);
        }

        return $result;
    } catch (ProcessFailedException $e) {
        $this->logger->error('CLI process failed', [
            'command' => $sanitizedCommand,
            'error' => $e->getMessage(),
        ]);

        throw new CliExecutionException(
            implode(' ', $sanitizedCommand),
            $e->getMessage(),
            $e
        );
    }
}
```

**Key Features:**

1. **Array Form** - Prevents shell injection
2. **Timeout** - Prevents hanging processes
3. **Logging** - Tracks all operations
4. **Error Handling** - Converts to custom exceptions

#### ProcessResult

The result object encapsulates command output:

```php
readonly class ProcessResult
{
    public function __construct(
        public int $exitCode,
        public string $stdout,
        public string $stderr,
        public bool $success,
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getOutput(): string
    {
        return $this->stdout . $this->stderr;
    }

    public function getStdout(): string
    {
        return $this->stdout;
    }

    public function getErrorMessage(): string
    {
        return trim($this->stderr);
    }
}
```

## Validation

### Validate Method

```php
public function validate(string $dslPath): ValidationResult
{
    // Validate and resolve path
    $resolvedPath = $this->validateFilePath($dslPath, 'DSL file');

    $this->logger->info('Validating DSL file', ['path' => $resolvedPath]);

    $result = $this->executeCommand(
        ['validate', '-workspace', $resolvedPath],
        self::TIMEOUT_VALIDATION  // 30 seconds
    );

    return $this->parseValidationResult($result);
}
```

### ValidationResult

```php
readonly class ValidationResult
{
    public function __construct(
        public bool $valid,
        public array $errors,
        public array $warnings,
    ) {}

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }
}
```

### Parsing Validation Output

```php
private function parseValidationResult(ProcessResult $result): ValidationResult
{
    $errors = [];
    $warnings = [];

    $output = $result->getOutput();
    $lines = explode("\n", $output);

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        // Parse error lines
        if (stripos($line, 'ERROR') === 0 || stripos($line, '[ERROR]') !== false) {
            $errors[] = $this->extractMessage($line);
        }

        // Parse warning lines
        if (stripos($line, 'WARNING') === 0 || stripos($line, '[WARNING]') !== false) {
            $warnings[] = $this->extractMessage($line);
        }
    }

    // If process failed but no errors parsed, add stderr
    if (!$result->isSuccess() && empty($errors)) {
        $errorMsg = $result->getErrorMessage();
        if (!empty($errorMsg)) {
            $errors[] = $errorMsg;
        }
    }

    $valid = $result->isSuccess() && empty($errors);

    return new ValidationResult(
        valid: $valid,
        errors: $errors,
        warnings: $warnings,
    );
}

private function extractMessage(string $line): string
{
    // Remove common prefixes
    $patterns = [
        '/^\[?ERROR\]?:?\s*/i',
        '/^\[?WARNING\]?:?\s*/i',
        '/^\[?INFO\]?:?\s*/i',
    ];

    foreach ($patterns as $pattern) {
        $result = preg_replace($pattern, '', $line);
        if ($result !== null) {
            $line = $result;
        }
    }

    return trim($line);
}
```

**Example Validation:**

```php
$validation = $cliWrapper->validate('/path/to/workspace.dsl');

if ($validation->isValid()) {
    echo "✓ Workspace is valid\n";
} else {
    echo "✗ Validation failed\n";
    foreach ($validation->errors as $error) {
        echo "  ERROR: {$error}\n";
    }
    foreach ($validation->warnings as $warning) {
        echo "  WARNING: {$warning}\n";
    }
}
```

## Export

### Export Method

```php
public function export(
    string $workspacePath,
    string $format,
    ?string $outputPath = null
): string {
    // Validate workspace path
    $resolvedWorkspacePath = $this->validateFilePath($workspacePath, 'Workspace file');

    $this->logger->info('Exporting workspace', [
        'workspace' => $resolvedWorkspacePath,
        'format' => $format,
        'output' => $outputPath,
    ]);

    $args = ['export', '-workspace', $resolvedWorkspacePath, '-format', $format];

    if ($outputPath !== null) {
        // Validate output directory exists
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            throw new CliExecutionException(
                'export',
                "Output directory does not exist: {$outputDir}"
            );
        }

        $args[] = '-output';
        $args[] = $outputPath;
    }

    $result = $this->executeCommand($args, self::TIMEOUT_EXPORT);

    if (!$result->isSuccess()) {
        throw new CliExecutionException(
            'export',
            "Export failed: {$result->getErrorMessage()}"
        );
    }

    // Return output path if specified, otherwise return stdout
    return $outputPath ?? $result->getStdout();
}
```

### Supported Export Formats

| Format | Output | Use Case |
|--------|--------|----------|
| `plantuml` | PlantUML diagram | Detailed diagrams with PlantUML |
| `mermaid` | Mermaid diagram | Markdown-embedded diagrams |
| `dot` | Graphviz DOT | Custom visualization with Graphviz |
| `ilograph` | Ilograph JSON | Interactive diagrams |
| `json` | Workspace JSON | Programmatic access |
| `dsl` | Structurizr DSL | Source format |

### Export Examples

**PlantUML:**

```php
$plantuml = $cliWrapper->export(
    workspacePath: '/path/to/workspace.dsl',
    format: 'plantuml',
    outputPath: '/path/to/output.puml'
);
```

**Mermaid:**

```php
$mermaid = $cliWrapper->export(
    workspacePath: '/path/to/workspace.dsl',
    format: 'mermaid'
);

echo $mermaid;
```

**DOT:**

```php
$dot = $cliWrapper->export(
    workspacePath: '/path/to/workspace.dsl',
    format: 'dot',
    outputPath: '/path/to/output.dot'
);

// Generate PNG with Graphviz
exec("dot -Tpng {$dot} -o diagram.png");
```

## Cloud Sync

### Push to Cloud

```php
public function push(
    string $workspacePath,
    int $workspaceId,
    string $apiKey,
    string $apiSecret,
    ?string $apiUrl = null,
): ProcessResult {
    // Validate workspace path
    $resolvedWorkspacePath = $this->validateFilePath($workspacePath, 'Workspace file');

    $this->logger->info('Pushing workspace to Structurizr', [
        'workspace' => $resolvedWorkspacePath,
        'workspaceId' => $workspaceId,
        'apiUrl' => $apiUrl ?? self::DEFAULT_API_URL,
    ]);

    $args = [
        'push',
        '-id', (string)$workspaceId,
        '-key', $apiKey,
        '-secret', $apiSecret,
        '-workspace', $resolvedWorkspacePath,
    ];

    if ($apiUrl !== null) {
        $args[] = '-url';
        $args[] = $apiUrl;
    }

    $result = $this->executeCommand($args, self::TIMEOUT_CLOUD_OPS);

    if (!$result->isSuccess()) {
        throw new CliExecutionException(
            'push',
            "Push failed: {$result->getErrorMessage()}"
        );
    }

    $this->logger->info('Workspace pushed successfully', ['workspaceId' => $workspaceId]);

    return $result;
}
```

### Pull from Cloud

```php
public function pull(
    int $workspaceId,
    string $apiKey,
    string $apiSecret,
    string $outputPath,
    ?string $apiUrl = null,
): ProcessResult {
    // Validate output directory exists
    $outputDir = dirname($outputPath);
    if (!is_dir($outputDir)) {
        throw new CliExecutionException(
            'pull',
            "Output directory does not exist: {$outputDir}"
        );
    }

    $this->logger->info('Pulling workspace from Structurizr', [
        'workspaceId' => $workspaceId,
        'output' => $outputPath,
        'apiUrl' => $apiUrl ?? self::DEFAULT_API_URL,
    ]);

    $args = [
        'pull',
        '-id', (string)$workspaceId,
        '-key', $apiKey,
        '-secret', $apiSecret,
        '-workspace', $outputPath,
    ];

    if ($apiUrl !== null) {
        $args[] = '-url';
        $args[] = $apiUrl;
    }

    $result = $this->executeCommand($args, self::TIMEOUT_CLOUD_OPS);

    if (!$result->isSuccess()) {
        throw new CliExecutionException(
            'pull',
            "Pull failed: {$result->getErrorMessage()}"
        );
    }

    $this->logger->info('Workspace pulled successfully', [
        'workspaceId' => $workspaceId,
        'output' => $outputPath,
    ]);

    return $result;
}
```

**Cloud Sync Example:**

```php
// Push local workspace to cloud
$cliWrapper->push(
    workspacePath: '/path/to/workspace.dsl',
    workspaceId: 12345,
    apiKey: getenv('STRUCTURIZR_API_KEY'),
    apiSecret: getenv('STRUCTURIZR_API_SECRET')
);

// Pull workspace from cloud
$cliWrapper->pull(
    workspaceId: 12345,
    apiKey: getenv('STRUCTURIZR_API_KEY'),
    apiSecret: getenv('STRUCTURIZR_API_SECRET'),
    outputPath: '/path/to/downloaded.json'
);
```

## Security

### Command Injection Prevention

**Use Array Form (Not String):**

```php
// Good: Array form (no shell)
$process = new Process([
    '/path/to/cli',
    'validate',
    '--workspace',
    $userInputPath
]);

// Bad: String form (shell injection risk)
$process = Process::fromShellCommandline(
    "cli validate --workspace {$userInputPath}"
);
```

**Why array form is safe:**

- No shell interpretation
- Arguments are passed directly to executable
- Special characters don't cause injection
- Symfony Process escapes arguments automatically

### Path Validation

```php
private function validateFilePath(string $path, string $description): string
{
    $resolvedPath = realpath($path);

    if ($resolvedPath === false) {
        throw new CliExecutionException(
            'validate-path',
            "{$description} not found: {$path}"
        );
    }

    if (!is_file($resolvedPath)) {
        throw new CliExecutionException(
            'validate-path',
            "{$description} is not a file: {$resolvedPath}"
        );
    }

    if (!is_readable($resolvedPath)) {
        throw new CliExecutionException(
            'validate-path',
            "{$description} is not readable: {$resolvedPath}"
        );
    }

    return $resolvedPath;
}
```

**Protection:**

1. **realpath()** - Resolves symlinks, prevents traversal
2. **is_file()** - Ensures it's a file, not directory
3. **is_readable()** - Verifies permissions
4. **Absolute path** - No ambiguity

### Credential Sanitization

Never log credentials:

```php
private function sanitizeCommandForLogging(array $command): array
{
    $sanitized = [];
    $redactNext = false;

    foreach ($command as $arg) {
        // If previous arg was a credential flag, redact this value
        if ($redactNext) {
            $sanitized[] = '[REDACTED]';
            $redactNext = false;
            continue;
        }

        // Check if this is a credential flag
        if (in_array($arg, ['-key', '-secret', '-apiKey', '-apiSecret'], true)) {
            $sanitized[] = $arg;
            $redactNext = true;
            continue;
        }

        $sanitized[] = $arg;
    }

    return $sanitized;
}
```

**Example:**

```php
// Original command
['/path/to/cli', 'push', '-key', 'abc123', '-secret', 'secret456']

// Sanitized for logging
['/path/to/cli', 'push', '-key', '[REDACTED]', '-secret', '[REDACTED]']
```

## Timeouts

Different operations have different timeout requirements:

```php
private const TIMEOUT_VALIDATION = 30;      // 30 seconds
private const TIMEOUT_EXPORT = 30;          // 30 seconds
private const TIMEOUT_CLOUD_OPS = 60;       // 60 seconds
```

**Why timeouts matter:**

1. **Prevent hanging** - Network issues, large files
2. **Resource management** - Free up resources
3. **User experience** - Don't wait forever
4. **Error detection** - Catch stuck processes

**Customizing timeouts:**

```php
// Long-running operation
$result = $this->executeCommand(
    ['export', '-workspace', $path, '-format', 'plantuml'],
    timeout: 120  // 2 minutes
);

// Quick operation
$result = $this->executeCommand(
    ['validate', '-workspace', $path],
    timeout: 10  // 10 seconds
);
```

## Error Handling

### CliExecutionException

```php
class CliExecutionException extends StructurizrException
{
    public function __construct(
        string $command,
        string $message,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            "CLI execution failed for command '{$command}': {$message}",
            0,
            $previous
        );
    }
}
```

### Exception Handling

```php
try {
    $validation = $cliWrapper->validate('/path/to/workspace.dsl');

    if (!$validation->isValid()) {
        foreach ($validation->errors as $error) {
            echo "Validation error: {$error}\n";
        }
    }
} catch (CliExecutionException $e) {
    echo "CLI execution failed: {$e->getMessage()}\n";
    // Check if CLI is installed
    // Check file permissions
    // Check file format
}
```

### Common Errors

**CLI not found:**
```
CliExecutionException: CLI executable not found at path: /path/to/cli
```

**Solution:** Install CLI, update `STRUCTURIZR_CLI_PATH`

**Not executable:**
```
CliExecutionException: CLI path is not executable: /path/to/cli
```

**Solution:** `chmod +x /path/to/structurizr-cli.sh`

**Invalid DSL:**
```
ValidationResult: valid=false, errors=["Line 5: Expected '}'"]
```

**Solution:** Fix DSL syntax error

**File not found:**
```
CliExecutionException: DSL file not found: /path/to/workspace.dsl
```

**Solution:** Check file path, ensure file exists

## Integration with Tools

### Validation Tool

```php
class AnalysisTools
{
    public function validateWorkspace(string $workspaceId): array
    {
        $workspace = $this->workspaceManager->load($workspaceId);

        // Write DSL to temp file
        $tempFile = sys_get_temp_dir() . "/workspace_{$workspaceId}.dsl";
        file_put_contents($tempFile, $workspace->dsl);

        try {
            // Validate with CLI
            $validation = $this->cliWrapper->validate($tempFile);

            return [
                'valid' => $validation->isValid(),
                'errors' => $validation->errors,
                'warnings' => $validation->warnings,
            ];
        } finally {
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
```

### Export Tools

```php
class ExportTools
{
    public function exportToPlantUml(string $workspaceId): string
    {
        $workspace = $this->workspaceManager->load($workspaceId);

        // Write DSL to temp file
        $tempFile = sys_get_temp_dir() . "/workspace_{$workspaceId}.dsl";
        file_put_contents($tempFile, $workspace->dsl);

        try {
            // Export to PlantUML
            $plantuml = $this->cliWrapper->export(
                workspacePath: $tempFile,
                format: 'plantuml'
            );

            return $plantuml;
        } finally {
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function exportToMermaid(string $workspaceId): string
    {
        $workspace = $this->workspaceManager->load($workspaceId);

        // Write DSL to temp file
        $tempFile = sys_get_temp_dir() . "/workspace_{$workspaceId}.dsl";
        file_put_contents($tempFile, $workspace->dsl);

        try {
            // Export to Mermaid
            $mermaid = $this->cliWrapper->export(
                workspacePath: $tempFile,
                format: 'mermaid'
            );

            return $mermaid;
        } finally {
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
```

## Best Practices

### 1. Always Use Temp Files

When working with in-memory DSL, write to temp file:

```php
$tempFile = sys_get_temp_dir() . '/workspace_' . uniqid() . '.dsl';
file_put_contents($tempFile, $dsl);

try {
    $result = $cliWrapper->validate($tempFile);
    // Use result
} finally {
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
}
```

### 2. Set Appropriate Timeouts

```php
// Quick operations: 10-30 seconds
$validation = $cliWrapper->validate($path);

// Export operations: 30-60 seconds
$export = $cliWrapper->export($path, 'plantuml');

// Cloud operations: 60-120 seconds
$push = $cliWrapper->push($path, $id, $key, $secret);
```

### 3. Handle Validation Errors Gracefully

```php
$validation = $cliWrapper->validate($path);

if ($validation->hasErrors()) {
    // Log errors
    foreach ($validation->errors as $error) {
        $logger->error("Validation error: {$error}");
    }

    // Return user-friendly message
    return [
        'success' => false,
        'message' => 'Workspace validation failed',
        'errors' => $validation->errors,
    ];
}
```

### 4. Never Log Credentials

```php
// Good: Sanitize before logging
$sanitized = $this->sanitizeCommandForLogging($command);
$logger->debug('Executing command', ['command' => $sanitized]);

// Bad: Log raw command with credentials
$logger->debug('Executing command', ['command' => $command]);
```

### 5. Validate Paths

```php
// Good: Validate path
$resolvedPath = $this->validateFilePath($path, 'Workspace file');
$result = $cliWrapper->validate($resolvedPath);

// Bad: Use user input directly
$result = $cliWrapper->validate($_GET['path']);
```

## Testing

### Mocking CLI Wrapper

```php
class CliWrapperTest extends TestCase
{
    private CliWrapper $cliWrapper;

    protected function setUp(): void
    {
        $cliPath = getenv('STRUCTURIZR_CLI_PATH');
        if (!$cliPath || !file_exists($cliPath)) {
            $this->markTestSkipped('Structurizr CLI not available');
        }

        $logger = new NullLogger();
        $this->cliWrapper = new CliWrapper($cliPath, $logger);
    }

    public function testValidateDsl(): void
    {
        $dsl = <<<DSL
        workspace "Test" {
            model {
                user = person "User"
            }
        }
        DSL;

        $tempFile = sys_get_temp_dir() . '/test.dsl';
        file_put_contents($tempFile, $dsl);

        try {
            $validation = $this->cliWrapper->validate($tempFile);
            $this->assertTrue($validation->isValid());
        } finally {
            unlink($tempFile);
        }
    }

    public function testValidateInvalidDsl(): void
    {
        $dsl = "invalid dsl content";
        $tempFile = sys_get_temp_dir() . '/invalid.dsl';
        file_put_contents($tempFile, $dsl);

        try {
            $validation = $this->cliWrapper->validate($tempFile);
            $this->assertFalse($validation->isValid());
            $this->assertNotEmpty($validation->errors);
        } finally {
            unlink($tempFile);
        }
    }

    public function testExportToPlantUml(): void
    {
        $dsl = <<<DSL
        workspace "Test" {
            model {
                user = person "User"
                system = softwareSystem "System"
                user -> system "Uses"
            }
            views {
                systemContext system "Context" {
                    include *
                    autoLayout lr
                }
            }
        }
        DSL;

        $tempFile = sys_get_temp_dir() . '/test.dsl';
        file_put_contents($tempFile, $dsl);

        try {
            $plantuml = $this->cliWrapper->export($tempFile, 'plantuml');
            $this->assertStringContainsString('@startuml', $plantuml);
            $this->assertStringContainsString('@enduml', $plantuml);
        } finally {
            unlink($tempFile);
        }
    }
}
```

## Debugging

### Enable Debug Logging

```php
$logger = new Logger('structurizr-cli');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));

$cliWrapper = new CliWrapper($cliPath, $logger);
```

**Log output:**

```
[DEBUG] Executing CLI command: ["/path/to/cli", "validate", "-workspace", "/path/to/file.dsl"]
[DEBUG] CLI command successful: exitCode=0
```

### Check CLI Version

```php
public function getVersion(): string
{
    $result = $this->executeCommand(['version'], 5);

    if (!$result->isSuccess()) {
        return 'unknown';
    }

    return trim($result->getStdout());
}
```

### Manual Testing

Test CLI directly:

```bash
# Validate workspace
/path/to/structurizr-cli.sh validate -workspace workspace.dsl

# Export to PlantUML
/path/to/structurizr-cli.sh export -workspace workspace.dsl -format plantuml

# Check version
/path/to/structurizr-cli.sh version
```

## Resources

### Structurizr CLI
- [Structurizr CLI GitHub](https://github.com/structurizr/cli)
- [CLI Documentation](https://github.com/structurizr/cli/blob/master/docs/usage.md)
- [CLI Releases](https://github.com/structurizr/cli/releases)

### Related Documentation
- [Workspace Management](/docs/architecture/workspace-management.md)
- [DSL Builder](/docs/architecture/dsl-builder.md)
- [Export Tools](/docs/tools/export-tools.md)

### Code Reference
- [`src/Structurizr/CliWrapper.php`](/src/Structurizr/CliWrapper.php)
- [`src/Structurizr/ProcessResult.php`](/src/Structurizr/ProcessResult.php)
- [`src/Structurizr/ValidationResult.php`](/src/Structurizr/ValidationResult.php)
- [`tests/Unit/Structurizr/CliWrapperTest.php`](/tests/Unit/Structurizr/CliWrapperTest.php)

### Security
- [Symfony Process Security](https://symfony.com/doc/current/components/process.html#process-signals)
- [Command Injection Prevention](https://owasp.org/www-community/attacks/Command_Injection)
