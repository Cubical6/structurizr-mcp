# CliWrapper.php Implementation - Executive Summary

## Quick Facts

| Item | Value |
|------|-------|
| **File Location** | `/home/user/structurizr-mcp/src/Structurizr/CliWrapper.php` |
| **Status** | NOT IMPLEMENTED (Phase 3.1) |
| **Blocks** | 3 export tools + validation in Phase 4.4 |
| **Estimated Effort** | 3-4 days |
| **Priority** | 🔴 MVP Critical |
| **Key Dependency** | Symfony Process (already in composer.json) |

---

## What CliWrapper Needs to Do

CliWrapper.php wraps the **Structurizr CLI tool** (a standalone executable) and provides 6 methods for:

1. **executeCommand()** - Core: Run arbitrary CLI commands with timeout handling
2. **validate()** - Validate DSL files for syntax errors
3. **export()** - Export workspaces to PlantUML, Mermaid, JSON, DOT, etc.
4. **push()** - Upload workspaces to Structurizr Cloud
5. **pull()** - Download workspaces from Structurizr Cloud
6. Constructor - Initialize with CLI path and logger

---

## 6 Required Methods

```php
// 1. Initialize the wrapper with CLI path
public function __construct(string $cliPath, LoggerInterface $logger)

// 2. Core method - Execute any CLI command with timeout
public function executeCommand(array $args, int $timeout = 30000): ProcessResult

// 3. Validate DSL syntax
public function validate(string $dslPath): ValidationResult

// 4. Export to multiple formats (plantuml, mermaid, json, dot, etc.)
public function export(string $dslPath, string $format, ?string $viewKey = null): string

// 5. Push to Structurizr Cloud
public function push(string $dslPath, int $cloudWorkspaceId, string $apiKey, string $apiSecret): bool

// 6. Pull from Structurizr Cloud
public function pull(int $cloudWorkspaceId, string $apiKey, string $apiSecret, ?string $outputPath = null): string
```

---

## Dependencies: What's Already Available

### Composer Dependencies (Installed & Ready)
- `symfony/process` (^6.0|^7.0) - Execute commands ✅
- `symfony/filesystem` (^6.0|^7.0) - File operations ✅
- `monolog/monolog` (^3.0) - Logging ✅

### Custom Exception Classes (Already Exist)
- `CliExecutionException` - Throw when CLI fails ✅
- `InvalidDslException` - Throw when DSL invalid ✅
- `WorkspaceNotFoundException` - For missing files ✅

### Integration Classes (Already Exist)
- `WorkspaceManager` - Load/save workspaces ✅
- `Workspace` - Value object for workspace data ✅
- `Configuration` - Get CLI path from config ✅
- `DslBuilder` - Generate DSL from model ✅

### Configuration (Already Set Up)
```php
// From Configuration.php
'cli_path' => $this->getEnv('STRUCTURIZR_CLI_PATH', './bin/structurizr-cli.sh')
```

---

## Blocking These Phase 4.4 Export Tools

### 1. export_to_plantuml Tool

```php
// In ExportTools.php (not yet created)
public function exportToPlantUml(string $workspaceId, ?string $viewKey = null): array
{
    // Needs: cliWrapper->export($dslPath, 'plantuml', $viewKey)
    // Returns: ['plantuml' => string, 'viewKey' => string, 'workspaceId' => string]
}
```

### 2. export_to_mermaid Tool

```php
public function exportToMermaid(string $workspaceId, ?string $viewKey = null): array
{
    // Needs: cliWrapper->export($dslPath, 'mermaid', $viewKey)
    // Returns: ['mermaid' => string, 'viewKey' => string, 'workspaceId' => string]
}
```

### 3. import_from_dsl Tool

```php
public function importFromDsl(string $dsl): array
{
    // Needs: cliWrapper->validate($tempDsl) to check DSL validity
    // Returns: ['workspaceId' => string, 'name' => string, 'dsl' => string]
}
```

---

## Two Required DTOs to Create

### ProcessResult (Returned by executeCommand)

```php
class ProcessResult {
    public readonly int $exitCode;       // 0 = success
    public readonly string $output;      // stdout
    public readonly string $error;       // stderr
    public readonly bool $isSuccessful;  // exitCode === 0
}
```

### ValidationResult (Returned by validate)

```php
class ValidationResult {
    public readonly bool $isValid;           // true if DSL valid
    public readonly array $errors;           // DSL error messages
    public readonly array $warnings;         // DSL warnings
    public readonly string $output;          // Raw CLI output
}
```

---

## Security Requirements

1. **Command Injection Prevention**
   - Use Symfony Process argument arrays, NOT shell strings
   - Never concatenate user input into commands
   - ✅ Example: `new Process(['./cli', '--option', $userInput])`
   - ❌ Wrong: `new Process('./cli --option ' . $userInput)`

2. **Credential Handling**
   - NEVER log API keys or secrets
   - Accept only as method parameters
   - Never store in files or environment

3. **Path Safety**
   - Validate DSL file paths exist
   - Use `realpath()` to resolve absolute paths
   - Prevent directory traversal (../)

4. **Timeout Protection**
   - Default 30 seconds timeout
   - Prevent infinite command execution

---

## Installation Requirements

### Structurizr CLI Must Be Available

**Option 1: Download to bin/ folder**
```bash
wget https://github.com/structurizr/cli/releases/download/v2.2.1/structurizr-cli-2.2.1-linux.zip
unzip structurizr-cli-2.2.1-linux.zip -d bin/
chmod +x bin/structurizr-cli.sh
```

**Option 2: System-wide or Docker**
```bash
export STRUCTURIZR_CLI_PATH=/usr/local/bin/structurizr-cli
```

**CLI must be executable**: `chmod +x bin/structurizr-cli.sh`

---

## How It All Connects

```
WorkspaceTools.createWorkspace()
    ↓
WorkspaceManager.save() → stores DSL to workspaces/ws_xxx.json
    ↓
Later: User calls export_to_plantuml(workspaceId)
    ↓
ExportTools.exportToPlantUml()
    ↓
CliWrapper.export(dslPath, 'plantuml')  ← NEEDS THIS
    ↓
Writes temp file → ./bin/structurizr-cli export --workspace ... → Returns PlantUML
    ↓
Returns to user
```

---

## Structurizr CLI Commands Used

```bash
# Validate DSL
./bin/structurizr-cli validate --workspace file.dsl

# Export to format
./bin/structurizr-cli export --workspace file.dsl --format plantuml
./bin/structurizr-cli export --workspace file.dsl --format mermaid --view viewKey

# Push to cloud
./bin/structurizr-cli push --workspace file.dsl --id 12345 --key KEY --secret SECRET

# Pull from cloud
./bin/structurizr-cli pull --workspace output.dsl --id 12345 --key KEY --secret SECRET
```

---

## Testing Strategy

### Unit Tests Needed
- ✅ executeCommand() with success/timeout/failure
- ✅ validate() with valid/invalid DSL
- ✅ export() for each format
- ✅ Error handling for missing CLI
- ✅ Error handling for invalid credentials

### Integration Tests Needed
- ✅ Real Structurizr CLI execution
- ✅ Complete export workflow
- ✅ Cloud push/pull cycle

---

## Files to Create

1. **src/Structurizr/CliWrapper.php** (350-450 lines)
   - Main implementation with 6 methods

2. **src/Structurizr/ProcessResult.php** (30 lines)
   - DTO for command results

3. **src/Structurizr/ValidationResult.php** (40 lines)
   - DTO for validation results

4. **tests/Unit/Structurizr/CliWrapperTest.php** (200+ lines)
   - Unit tests for all methods

5. **Update server.php**
   - Inject CliWrapper into tools

---

## Critical Notes

1. **Structurizr CLI is external**: Must download and place separately
2. **No external APIs**: Uses local CLI tool, not REST APIs
3. **Temp files**: Must clean up temp DSL files after operations
4. **Timeout critical**: Export can be slow for large workspaces
5. **Cloud optional**: Push/pull only needed if cloud integration enabled

---

## Priority Timeline

- **Phase**: 3.1 (MVP - Week 2)
- **Duration**: 3-4 days
- **Blocked By**: Nothing
- **Blocks**: 3 export tools, import tool, 1 validation tool
- **Success Criteria**: 
  - All 6 methods working
  - All error cases handled
  - Tests passing
  - No command injection vulnerabilities

