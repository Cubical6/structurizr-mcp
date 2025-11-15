# CliWrapper.php Implementation Specification

**Location**: `/home/user/structurizr-mcp/src/Structurizr/CliWrapper.php`

**Status**: NOT IMPLEMENTED (Phase 3.1) - BLOCKS Phase 4.4 Export Tools

**Priority**: 🔴 MVP - Required for export functionality

---

## 1. EXECUTIVE SUMMARY

CliWrapper.php is a critical integration layer that wraps the Structurizr CLI tool and provides PHP method interfaces for:
- Validating DSL files
- Exporting workspaces to multiple formats (PlantUML, Mermaid, etc.)
- Push/pull operations to/from Structurizr Cloud
- Command execution with proper error handling and timeout management

This class is **blocking** three export tools (export_to_plantuml, export_to_mermaid, import_from_dsl) in Phase 4.4.

---

## 2. SPECIFICATIONS FROM TASKS.MD (Phase 3.1, Lines 148-159)

### 2.1 Required Methods

```php
public function __construct(string $cliPath)
public function executeCommand(array $args): ProcessResult
public function validate(string $dslPath): ValidationResult
public function export(string $workspace, string $format): string
public function push(string $workspace, int $id, string $key, string $secret): bool
public function pull(int $id, string $key, string $secret): string
```

### 2.2 Key Requirements
- Use Symfony Process component (ALREADY IN composer.json as symfony/process: ^6.0|^7.0)
- Handle command timeouts
- Parse CLI output and errors
- Test with simple DSL files

---

## 3. DEPENDENCIES

### 3.1 Required Composer Dependencies (Already Available)

| Package | Version | Purpose |
|---------|---------|---------|
| `symfony/process` | ^6.0\|^7.0 | Execute Structurizr CLI commands |
| `symfony/filesystem` | ^6.0\|^7.0 | File operations for DSL validation |
| `psr/log` (via monolog) | - | Logging CLI operations |
| `mcp/sdk` | dev-main | Exception throwing for MCP |

### 3.2 Internal Dependencies

| Class | Location | Purpose |
|-------|----------|---------|
| `LoggerInterface` | PSR-3 | Dependency injection for logging |
| `CliExecutionException` | src/Exception/ | Thrown on CLI execution failures |
| `InvalidDslException` | src/Exception/ | Thrown on DSL validation failures |
| `Configuration` | src/Configuration.php | Get CLI path from config |
| `Workspace` | src/Structurizr/Workspace.php | Type hints for workspace operations |
| `Symfony\Component\Process\Process` | Vendor | Process execution |
| `Symfony\Component\Filesystem\Filesystem` | Vendor | File operations |

### 3.3 Configuration Integration

From `Configuration.php` (lines 40, 91-93):
```php
'cli_path' => $this->getEnv('STRUCTURIZR_CLI_PATH', './bin/structurizr-cli.sh'),

public function getStructurizrCliPath(): string
{
    return $this->get('structurizr.cli_path', './bin/structurizr-cli.sh');
}
```

**Default CLI path**: `./bin/structurizr-cli.sh`

---

## 4. DETAILED METHOD SPECIFICATIONS

### 4.1 Constructor

```php
public function __construct(
    string $cliPath,
    LoggerInterface $logger
)
```

**Responsibilities**:
- Store CLI path for later use
- Validate CLI executable exists and is readable
- Initialize logger
- Throw exception if CLI not found

**Example**:
```php
$cliWrapper = new CliWrapper('./bin/structurizr-cli.sh', $logger);
```

---

### 4.2 executeCommand() - Core Method

```php
public function executeCommand(array $args, int $timeout = 30000): ProcessResult
```

**Parameters**:
- `array $args`: Command arguments (e.g., ['export', '--workspace', 'file.dsl', '--format', 'plantuml'])
- `int $timeout`: Milliseconds timeout (default: 30 seconds)

**Returns**: `ProcessResult` (Custom DTO)
```php
class ProcessResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $output,
        public readonly string $error,
        public readonly bool $isSuccessful  // exitCode === 0
    ) {}
}
```

**Responsibilities**:
- Create Symfony Process with CLI path + args
- Set timeout (in milliseconds, Symfony uses seconds)
- Execute process
- Capture both stdout and stderr
- Log command execution (with level based on result)
- Return ProcessResult with exit code, output, and error

**Exceptions Thrown**:
- `CliExecutionException` if process times out
- `CliExecutionException` if process fails to execute (CLI not found, etc.)

**Security Considerations**:
- Escape/validate arguments to prevent command injection
- Use Process::escape() or Process argument arrays to prevent shell injection

**Example**:
```php
$result = $this->executeCommand([
    'export',
    '--workspace', $dslPath,
    '--format', 'plantuml'
], 30000);

if (!$result->isSuccessful) {
    throw new CliExecutionException('export', $result->error);
}
return $result->output;
```

---

### 4.3 validate() - DSL Validation

```php
public function validate(string $dslPath): ValidationResult
```

**Parameters**:
- `string $dslPath`: Path to DSL file (absolute or relative)

**Returns**: `ValidationResult` (Custom DTO)
```php
class ValidationResult
{
    public function __construct(
        public readonly bool $isValid,
        public readonly array $errors,      // ['message' => string, ...]
        public readonly array $warnings,    // ['message' => string, ...]
        public readonly string $output      // Raw CLI output
    ) {}
}
```

**Responsibilities**:
- Validate DSL file exists
- Execute: `structurizr-cli validate --workspace {dslPath}`
- Parse CLI output for errors/warnings
- Return ValidationResult

**Exceptions Thrown**:
- `InvalidDslException` if file doesn't exist
- `CliExecutionException` if CLI validation command fails

**CLI Command Example**:
```bash
./bin/structurizr-cli validate --workspace file.dsl
```

**Example**:
```php
$result = $this->validate('/path/to/workspace.dsl');
if (!$result->isValid) {
    foreach ($result->errors as $error) {
        $logger->error("DSL Error: " . $error['message']);
    }
}
```

---

### 4.4 export() - Multi-Format Export

```php
public function export(
    string $dslPath,
    string $format,
    ?string $viewKey = null
): string
```

**Parameters**:
- `string $dslPath`: Path to DSL file
- `string $format`: Export format - one of:
  - `'plantuml'` - PlantUML diagrams
  - `'mermaid'` - Mermaid diagrams
  - `'json'` - JSON workspace format
  - `'dot'` - GraphViz DOT format
  - `'ilograph'` - iloGraph format
  - `'d2'` - D2 diagram format
- `?string $viewKey`: Specific view to export (optional)

**Returns**: `string` - Exported content

**Responsibilities**:
- Validate format is supported
- Build CLI command with proper arguments
- Execute export command
- Parse and return output
- Handle format-specific output requirements

**Exceptions Thrown**:
- `InvalidArgumentException` if format unsupported
- `CliExecutionException` if export fails

**CLI Command Examples**:
```bash
# Export entire workspace to PlantUML
./bin/structurizr-cli export --workspace file.dsl --format plantuml

# Export specific view
./bin/structurizr-cli export --workspace file.dsl --format plantuml --view viewKey
```

**Example**:
```php
// Export to PlantUML
$plantuml = $this->export('/path/to/workspace.dsl', 'plantuml');

// Export specific view to Mermaid
$mermaid = $this->export('/path/to/workspace.dsl', 'mermaid', 'SystemContext');
```

---

### 4.5 push() - Cloud Push

```php
public function push(
    string $dslPath,
    int $cloudWorkspaceId,
    string $apiKey,
    string $apiSecret
): bool
```

**Parameters**:
- `string $dslPath`: Local DSL file path
- `int $cloudWorkspaceId`: Structurizr Cloud workspace ID
- `string $apiKey`: Structurizr API key
- `string $apiSecret`: Structurizr API secret

**Returns**: `bool` - Success status

**Responsibilities**:
- Validate workspace ID is positive integer
- Validate credentials are non-empty
- Build CLI push command
- Execute with credentials (via environment or args)
- Log success/failure
- Return true on success

**Exceptions Thrown**:
- `CliExecutionException` if push fails
- `InvalidArgumentException` if credentials invalid

**CLI Command Example**:
```bash
./bin/structurizr-cli push \
  --workspace file.dsl \
  --id 12345 \
  --key api-key \
  --secret api-secret
```

**Example**:
```php
$success = $this->push(
    '/path/to/workspace.dsl',
    12345,
    'your-api-key',
    'your-api-secret'
);

if ($success) {
    $logger->info("Workspace pushed to cloud successfully");
}
```

---

### 4.6 pull() - Cloud Pull

```php
public function pull(
    int $cloudWorkspaceId,
    string $apiKey,
    string $apiSecret,
    ?string $outputPath = null
): string
```

**Parameters**:
- `int $cloudWorkspaceId`: Structurizr Cloud workspace ID
- `string $apiKey`: Structurizr API key
- `string $apiSecret`: Structurizr API secret
- `?string $outputPath`: Where to save DSL file (optional)

**Returns**: `string` - DSL content (or file path if outputPath specified)

**Responsibilities**:
- Validate credentials and ID
- Build CLI pull command
- Execute pull operation
- Return DSL content
- Save to file if outputPath provided

**Exceptions Thrown**:
- `CliExecutionException` if pull fails
- `InvalidArgumentException` if credentials invalid

**CLI Command Example**:
```bash
./bin/structurizr-cli pull \
  --workspace workspace.dsl \
  --id 12345 \
  --key api-key \
  --secret api-secret
```

**Example**:
```php
// Get DSL content directly
$dsl = $this->pull(12345, 'api-key', 'api-secret');

// Or save to file
$filePath = $this->pull(
    12345,
    'api-key',
    'api-secret',
    '/path/to/output.dsl'
);
```

---

## 5. BLOCKING DEPENDENCIES (Phase 4.4 Export Tools)

### 5.1 export_to_plantuml Tool

**File**: `src/Tools/ExportTools.php` (NOT YET CREATED)

**Blocked Method**: `exportToPlantUml(string $workspaceId, ?string $viewKey = null): array`

**How CliWrapper is Used**:
```php
// In ExportTools.php
public function exportToPlantUml(string $workspaceId, ?string $viewKey = null): array
{
    $workspace = $this->workspaceManager->load($workspaceId);
    
    // 1. Write workspace DSL to temp file
    $tempPath = sys_get_temp_dir() . '/' . uniqid('ws_', true) . '.dsl';
    file_put_contents($tempPath, $workspace->dsl);
    
    // 2. Use CliWrapper to export
    $plantuml = $this->cliWrapper->export($tempPath, 'plantuml', $viewKey);
    
    // 3. Clean up temp file
    unlink($tempPath);
    
    return [
        'plantuml' => $plantuml,
        'viewKey' => $viewKey,
        'workspaceId' => $workspaceId
    ];
}
```

---

### 5.2 export_to_mermaid Tool

**Blocked Method**: `exportToMermaid(string $workspaceId, ?string $viewKey = null): array`

**How CliWrapper is Used**:
```php
public function exportToMermaid(string $workspaceId, ?string $viewKey = null): array
{
    $workspace = $this->workspaceManager->load($workspaceId);
    
    $tempPath = sys_get_temp_dir() . '/' . uniqid('ws_', true) . '.dsl';
    file_put_contents($tempPath, $workspace->dsl);
    
    $mermaid = $this->cliWrapper->export($tempPath, 'mermaid', $viewKey);
    
    unlink($tempPath);
    
    return [
        'mermaid' => $mermaid,
        'viewKey' => $viewKey,
        'workspaceId' => $workspaceId
    ];
}
```

---

### 5.3 import_from_dsl Tool

**Blocked Method**: `importFromDsl(string $dsl): array`

**How CliWrapper is Used**:
```php
public function importFromDsl(string $dsl): array
{
    // 1. Write DSL to temp file
    $tempPath = sys_get_temp_dir() . '/' . uniqid('ws_', true) . '.dsl';
    file_put_contents($tempPath, $dsl);
    
    // 2. Validate DSL using CliWrapper
    $validation = $this->cliWrapper->validate($tempPath);
    
    if (!$validation->isValid) {
        unlink($tempPath);
        throw new InvalidDslException(
            "DSL validation failed: " . implode(', ', $validation->errors)
        );
    }
    
    // 3. Parse DSL to extract workspace name
    $workspaceName = $this->extractWorkspaceName($dsl);
    
    // 4. Create workspace in local storage
    $workspace = $this->workspaceManager->create($workspaceName);
    
    // 5. Save DSL to workspace
    $updated = $workspace->withDsl($dsl);
    $this->workspaceManager->save($updated);
    
    unlink($tempPath);
    
    return [
        'workspaceId' => $updated->id,
        'name' => $updated->name,
        'dsl' => $dsl
    ];
}
```

---

## 6. INTERNAL DATA STRUCTURES

### 6.1 ProcessResult DTO

```php
namespace StructurizrMcp\Structurizr;

/**
 * Result of executing a CLI command
 */
class ProcessResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $output,
        public readonly string $error,
        public readonly bool $isSuccessful = true
    ) {
    }
    
    public static function success(string $output): self
    {
        return new self(0, $output, '', true);
    }
    
    public static function failure(int $code, string $output, string $error): self
    {
        return new self($code, $output, $error, false);
    }
}
```

### 6.2 ValidationResult DTO

```php
namespace StructurizrMcp\Structurizr;

/**
 * Result of DSL validation
 */
class ValidationResult
{
    /**
     * @param bool $isValid
     * @param array<array{message: string}> $errors
     * @param array<array{message: string}> $warnings
     * @param string $output Raw CLI output
     */
    public function __construct(
        public readonly bool $isValid,
        public readonly array $errors,
        public readonly array $warnings,
        public readonly string $output
    ) {
    }
    
    public static function valid(string $output): self
    {
        return new self(true, [], [], $output);
    }
    
    public static function invalid(
        array $errors,
        array $warnings = [],
        string $output = ''
    ): self {
        return new self(false, $errors, $warnings, $output);
    }
}
```

---

## 7. IMPLEMENTATION GUIDELINES

### 7.1 Code Standards
- PHP 8.1+ strict types: `declare(strict_types=1);`
- PSR-12 coding style
- Full type hints on all parameters and returns
- PHPDoc for all public methods
- Never expose internal paths or secrets in errors

### 7.2 Error Handling Strategy

```
CLI Command Execution
        ↓
    Is Successful? → YES → Parse output → Return ProcessResult
        ↓ NO
    Timeout? → YES → Throw CliExecutionException("Timeout...")
        ↓ NO
    File not found? → YES → Throw CliExecutionException("CLI not found...")
        ↓ NO
    Other error → Throw CliExecutionException with stderr
```

### 7.3 Logging Strategy

```php
// Successful commands
$this->logger->debug("CLI command executed", [
    'command' => implode(' ', $args),
    'exitCode' => 0,
]);

// Failed commands
$this->logger->error("CLI command failed", [
    'command' => implode(' ', $args),
    'exitCode' => $result->exitCode,
    'error' => $result->error,
]);

// Timeouts
$this->logger->critical("CLI command timeout", [
    'command' => implode(' ', $args),
    'timeout' => $timeout,
]);
```

### 7.4 Security Requirements

1. **Command Injection Prevention**:
   - Use Symfony Process argument arrays (not shell strings)
   - Never concatenate user input into commands
   - Example: `new Process(['./cli', '--option', $userInput])`

2. **Credential Handling**:
   - Never log API keys/secrets
   - Accept credentials as parameters
   - Pass via environment or arguments only
   - Never store in files

3. **Path Traversal Prevention**:
   - Validate DSL file paths
   - Use `realpath()` to resolve absolute paths
   - Prevent `../` sequences in file names

4. **Timeout Handling**:
   - Prevent infinite execution
   - Default 30 seconds for most commands
   - Longer timeout for large workspaces (configurable)

---

## 8. TESTING REQUIREMENTS (Phase 8)

### 8.1 Unit Tests (`tests/Unit/Structurizr/CliWrapperTest.php`)

```php
// Test successful command execution
testExecuteCommandSuccessful()

// Test command timeout
testExecuteCommandTimeout()

// Test DSL validation
testValidateSuccessful()
testValidateWithErrors()

// Test export formats
testExportToPlantUml()
testExportToMermaid()
testExportWithViewKey()

// Test cloud operations
testPushToCloud()
testPullFromCloud()

// Test error handling
testCliNotFound()
testInvalidDslPath()
testInvalidCredentials()
```

### 8.2 Integration Tests

```php
// Test with real Structurizr CLI
testExportRealWorkspace()

// Test complete export workflow
testExportToMultipleFormats()

// Test cloud sync
testPushPullCycle()
```

---

## 9. INSTALLATION REQUIREMENTS

### 9.1 Structurizr CLI Setup

The Structurizr CLI must be available:

**Option 1**: Download and place in `bin/` directory
```bash
# From project root
wget https://github.com/structurizr/cli/releases/download/v2.2.1/structurizr-cli-2.2.1-linux.zip
unzip structurizr-cli-2.2.1-linux.zip -d bin/
chmod +x bin/structurizr-cli.sh
```

**Option 2**: Use Docker or system-wide installation
```bash
# Configure via environment variable
export STRUCTURIZR_CLI_PATH=/usr/local/bin/structurizr-cli
```

### 9.2 File Permissions
- CLI executable must be readable and executable
- Temp directory must be writable for DSL files

### 9.3 Dependencies
- PHP 8.1+
- Symfony Process component (^6.0|^7.0)
- Monolog for logging

---

## 10. EXAMPLE USAGE IN CONTEXT

### 10.1 Complete Export Flow

```php
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Structurizr\WorkspaceManager;

// Setup
$cliWrapper = new CliWrapper('./bin/structurizr-cli.sh', $logger);
$workspaceManager = new WorkspaceManager('./workspaces', $logger);

// Load workspace
$workspace = $workspaceManager->load('ws_abc123');

// Write to temp file
$tempDsl = sys_get_temp_dir() . '/ws_export_' . uniqid() . '.dsl';
file_put_contents($tempDsl, $workspace->dsl);

try {
    // Validate DSL
    $validation = $cliWrapper->validate($tempDsl);
    if (!$validation->isValid) {
        throw new InvalidDslException("DSL is invalid");
    }
    
    // Export to multiple formats
    $plantuml = $cliWrapper->export($tempDsl, 'plantuml');
    $mermaid = $cliWrapper->export($tempDsl, 'mermaid');
    $json = $cliWrapper->export($tempDsl, 'json');
    
    return [
        'plantuml' => $plantuml,
        'mermaid' => $mermaid,
        'json' => $json,
    ];
    
} finally {
    // Always clean up temp file
    if (file_exists($tempDsl)) {
        unlink($tempDsl);
    }
}
```

---

## 11. PRIORITY & TIMELINE

- **Phase**: 3.1 (MVP Critical)
- **Priority**: 🔴 High - Blocks Phase 4.4
- **Estimated Effort**: 3-4 days
- **Blocked By**: None
- **Blocks**: 
  - Phase 4.4 Export Tools (3 tools)
  - Phase 4.5 Analysis Tools (validate_workspace)

---

## 12. DELIVERABLES CHECKLIST

- [ ] Create `src/Structurizr/CliWrapper.php` with all 6 methods
- [ ] Create `src/Structurizr/ProcessResult.php` DTO
- [ ] Create `src/Structurizr/ValidationResult.php` DTO
- [ ] Implement comprehensive error handling
- [ ] Add logging for all operations
- [ ] Update `server.php` to inject CliWrapper
- [ ] Test with sample DSL files
- [ ] Create integration test suite
- [ ] Document CLI requirements in README.md
- [ ] Add example `.env` configuration
- [ ] Verify no security vulnerabilities (credential handling, command injection)

