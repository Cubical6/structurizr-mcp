# CliWrapper.php - Dependency Map

## Class Dependencies

```
CliWrapper.php (TO BE CREATED)
├── Depends On (Injected)
│   ├── LoggerInterface (PSR-3)
│   │   └── Provided by: Monolog\Logger
│   │
│   └── Symfony\Component\Process\Process
│       └── Package: symfony/process ^6.0|^7.0 (in composer.json)
│
├── Uses (References)
│   ├── Symfony\Component\Filesystem\Filesystem
│   │   └── Package: symfony/filesystem ^6.0|^7.0 (in composer.json)
│   │
│   ├── CliExecutionException (custom)
│   │   └── Location: src/Exception/CliExecutionException.php ✅
│   │
│   └── InvalidDslException (custom)
│       └── Location: src/Exception/InvalidDslException.php ✅
│
└── Creates & Returns (DTOs to create)
    ├── ProcessResult (NEW)
    │   └── Location: src/Structurizr/ProcessResult.php (NEW)
    │
    └── ValidationResult (NEW)
        └── Location: src/Structurizr/ValidationResult.php (NEW)
```

## Who Uses CliWrapper

```
CliWrapper.php
├── Used by: ExportTools.php (PHASE 4.4, NOT YET CREATED)
│   ├── exportToPlantUml() → calls export(dslPath, 'plantuml', viewKey)
│   ├── exportToMermaid() → calls export(dslPath, 'mermaid', viewKey)
│   └── importFromDsl() → calls validate(dslPath)
│
├── Used by: AnalysisTools.php (PHASE 4.5, NOT YET CREATED)
│   └── validateWorkspace() → calls validate(dslPath)
│
└── Injected into: server.php (NEEDS UPDATE)
    └── Created and passed to ExportTools and AnalysisTools
```

## Integration Path

```
server.php (Entry Point)
    ↓
Creates Configuration
    ↓ reads STRUCTURIZR_CLI_PATH
    ↓
Creates Logger (Monolog)
    ↓
Creates CliWrapper (NEW)
    ├── Parameter: Configuration->getStructurizrCliPath()
    └── Parameter: Logger instance
    ↓
Creates ExportTools (Phase 4.4)
    ├── Parameter: CliWrapper instance (dependency)
    ├── Parameter: WorkspaceManager instance
    └── Methods call: cliWrapper->export(), cliWrapper->validate()
    ↓
Creates ModelTools, WorkspaceTools, etc.
    ↓
Server runs with all tools available
```

## File System Flow

```
User creates workspace
    ↓
WorkspaceTools.createWorkspace()
    ↓
WorkspaceManager.save()
    ↓
stores to: workspaces/ws_xxx.json
    ↓
User calls: export_to_plantuml
    ↓
ExportTools.exportToPlantUml(workspaceId)
    ↓
Load from: workspaces/ws_xxx.json
    ↓
Write temp file: /tmp/ws_xxx_temp.dsl
    ↓
CliWrapper.export(tempPath, 'plantuml')
    ↓
Execute: ./bin/structurizr-cli export --workspace /tmp/ws_xxx_temp.dsl --format plantuml
    ↓
Return PlantUML output
    ↓
Delete: /tmp/ws_xxx_temp.dsl
    ↓
Return to user
```

## Composer Dependency Tree

```
structurizr-mcp (This project)
├── mcp/sdk
│   └── [MCP Protocol classes]
│
├── symfony/process ← USED BY CLIWRAPPER
│   ├── Symfony\Component\Process\Process
│   └── Symfony\Component\Process\ProcessBuilder
│
├── symfony/filesystem ← USED BY CLIWRAPPER
│   └── Symfony\Component\Filesystem\Filesystem
│
├── monolog/monolog ← Logger Implementation
│   ├── Monolog\Logger
│   ├── Monolog\Handler\StreamHandler
│   └── Psr\Log\LoggerInterface (interface)
│
├── guzzlehttp/guzzle
│   └── [HTTP Client - NOT YET USED]
│
└── Development only:
    ├── phpunit/phpunit
    ├── phpstan/phpstan
    └── friendsofphp/php-cs-fixer
```

## Configuration Dependencies

```
Configuration.php
└── Environment Variables:
    ├── STRUCTURIZR_CLI_PATH
    │   ├── Source: .env file or system environment
    │   ├── Default: ./bin/structurizr-cli.sh
    │   └── Used by: CliWrapper constructor
    │
    ├── STRUCTURIZR_API_KEY (optional, for cloud)
    ├── STRUCTURIZR_API_SECRET (optional, for cloud)
    └── STRUCTURIZR_API_URL (optional, for cloud)
```

## Exception Hierarchy

```
Exception (PHP Built-in)
└── StructurizrException (custom, extends Exception)
    ├── CliExecutionException ← USED BY CLIWRAPPER
    │   └── Thrown when: CLI command fails
    │
    ├── InvalidDslException ← USED BY CLIWRAPPER
    │   └── Thrown when: DSL validation fails
    │
    ├── WorkspaceNotFoundException
    │   └── Thrown when: Workspace not found
    │
    ├── ApiAuthenticationException
    │   └── Thrown when: API credentials invalid
    │
    └── [Other custom exceptions]
```

## External Tool Dependencies

```
Structurizr CLI (External Executable)
├── Download: https://github.com/structurizr/cli/releases
├── Location: ./bin/structurizr-cli.sh (configurable)
├── Requirements:
│   ├── Linux/macOS: Java Runtime Environment (JRE)
│   ├── Windows: Java Runtime Environment (JRE)
│   └── Must be executable: chmod +x bin/structurizr-cli.sh
│
└── Supported Commands (via CliWrapper):
    ├── validate --workspace <path>
    ├── export --workspace <path> --format <format>
    ├── push --workspace <path> --id <id> --key <key> --secret <secret>
    └── pull --workspace <path> --id <id> --key <key> --secret <secret>
```

## Data Flow - Method Dependencies

```
CliWrapper.executeCommand(args, timeout)
    ↓ Uses: Symfony\Component\Process\Process
    ↓ Returns: ProcessResult (NEW DTO)
    ├── int $exitCode
    ├── string $output (stdout)
    ├── string $error (stderr)
    └── bool $isSuccessful (exitCode === 0)
         ↑
         Used by all other methods

CliWrapper.validate(dslPath)
    ↓ Calls: executeCommand(['validate', '--workspace', dslPath])
    ↓ Parses output
    ↓ Returns: ValidationResult (NEW DTO)
    ├── bool $isValid
    ├── array $errors
    ├── array $warnings
    └── string $output

CliWrapper.export(dslPath, format, viewKey)
    ↓ Validates format is supported
    ↓ Calls: executeCommand(['export', '--workspace', dslPath, '--format', format, ...])
    ↓ Returns: string (exported content)

CliWrapper.push(dslPath, id, key, secret)
    ↓ Validates parameters
    ↓ Calls: executeCommand(['push', '--workspace', dslPath, ...])
    ↓ Returns: bool (success)

CliWrapper.pull(id, key, secret, outputPath)
    ↓ Validates parameters
    ↓ Calls: executeCommand(['pull', '--workspace', outputPath, ...])
    ↓ Returns: string (DSL content)
```

## Dependency Injection Chain (server.php)

```
server.php
    ↓
$logger = new Logger('structurizr-mcp')
    ↓
$config = new Configuration()
    ↓
$cliPath = $config->getStructurizrCliPath()
    ↓
$cliWrapper = new CliWrapper($cliPath, $logger)  ← NEW
    ↓
$workspaceManager = new WorkspaceManager(...)
    ↓
$exportTools = new ExportTools($cliWrapper, $workspaceManager, $logger)  ← NEEDS CLIWRAPPER
    ↓
$server->registerTool('export_to_plantuml', [$exportTools, 'exportToPlantUml'])
$server->registerTool('export_to_mermaid', [$exportTools, 'exportToMermaid'])
$server->registerTool('import_from_dsl', [$exportTools, 'importFromDsl'])
```

## Files to Create/Modify

### Files to Create (NEW)
1. **src/Structurizr/CliWrapper.php** (350-450 lines)
   - Main implementation
   - Depends on: Symfony\Component\Process\Process, LoggerInterface
   - Throws: CliExecutionException, InvalidDslException

2. **src/Structurizr/ProcessResult.php** (30-40 lines)
   - DTO for executeCommand() return value
   - No dependencies

3. **src/Structurizr/ValidationResult.php** (40-50 lines)
   - DTO for validate() return value
   - No dependencies

4. **tests/Unit/Structurizr/CliWrapperTest.php** (200+ lines)
   - Unit tests for CliWrapper
   - Test fixtures for sample DSL files

### Files to Modify (EXISTING)
1. **server.php**
   - Add: Create CliWrapper instance
   - Add: Inject into ExportTools and AnalysisTools
   - Lines: ~50-60 (server initialization)

2. **.env.example**
   - Add: STRUCTURIZR_CLI_PATH=./bin/structurizr-cli.sh
   - Document: CLI installation instructions

3. **docs/README.md** or new **docs/CLI_SETUP.md**
   - Document: How to install Structurizr CLI
   - Document: Configuration options

## Execution Environment Requirements

```
Runtime Environment (PHP 8.1+)
├── PHP 8.1+ installed
├── Composer installed
├── ext-json loaded
├── ext-pcntl available (for Process timeout)
└── Structurizr CLI installed
    ├── Java Runtime Environment (JRE) 8+
    ├── Located at: ./bin/structurizr-cli.sh (or configured path)
    └── Executable: chmod +x bin/structurizr-cli.sh

Filesystem Requirements
├── Writable: /tmp (for temp DSL files)
├── Readable: workspaces/ (for loading DSL)
├── Executable: bin/structurizr-cli.sh
└── Readable: .env (for configuration)
```

## Security Dependency Chain

```
User Input
    ↓
ExportTools.exportToPlantUml(workspaceId)  ← Validates workspaceId
    ↓
WorkspaceManager.load(workspaceId)  ← Sanitizes file paths
    ↓
Workspace object with safe DSL
    ↓
Write to: /tmp/{uniqid}.dsl (sanitized filename)
    ↓
CliWrapper.export(tempPath, format)  ← Uses Process argument arrays
    ↓
Symfony Process  ← Escapes arguments, prevents shell injection
    ↓
Structurizr CLI executes safely
    ↓
Delete temp file
    ↓
Output to user (safe)
```

