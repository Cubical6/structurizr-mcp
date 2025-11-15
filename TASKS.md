# Structurizr MCP Server - Implementation Tasks (PHP)

## 📊 Implementation Status Overview (Last Updated: 2025-11-15)

### Overall Progress: ~45% Complete (MVP Stage)

| Phase | Status | Progress | Priority |
|-------|--------|----------|----------|
| **Phase 1: Project Setup** | ✅ Mostly Complete | 85% | 🔴 MVP |
| **Phase 2: MCP Foundation** | ✅ Mostly Complete | 75% | 🔴 MVP |
| **Phase 3: Structurizr Integration** | ⚠️ Partial | 50% | 🔴 MVP |
| **Phase 4: Core Tools** | ⚠️ Partial | 40% | 🔴 MVP |
| **Phase 5: Resources** | ❌ Not Started | 0% | 🟡 Core |
| **Phase 6: Prompts** | ❌ Not Started | 0% | 🟡 Core |
| **Phase 7: Documentation & Styling** | ❌ Not Started | 0% | 🟢 Extended |
| **Phase 8: Testing** | ❌ Not Started | 0% | 🟡 Core |
| **Phase 9: Advanced Features** | ❌ Not Started | 0% | 🟢 Extended |
| **Phase 10: Documentation & Release** | ⚠️ Partial | 40% | 🟡 Core |

### Critical Gaps (Blocking MVP):
1. ⚠️ **Missing #[Schema] attributes** on all tool parameters (HIGH PRIORITY)
2. ⚠️ **Missing cache setup** in server.php (PhpFilesAdapter)
3. ❌ **ViewTools.php** not implemented (container/component views)
4. ❌ **CliWrapper.php** not implemented (blocks export functionality)
5. ❌ **Zero test coverage** (tests/ directory missing)

### What's Working:
- ✅ 12 MCP tools implemented and functional (WorkspaceTools, ModelTools)
- ✅ Complete workspace management (create, load, save, delete, list)
- ✅ DSL generation for all C4 elements
- ✅ Exception handling infrastructure
- ✅ Configuration management
- ✅ Basic documentation (README, CLAUDE.md, examples)

### Quick Stats:
- **Lines of Code**: ~1,152 (10 PHP files)
- **Tools Implemented**: 12/25+ (48%)
- **Directories**: 7/9 created (78%)
- **Test Coverage**: 0% (0 test files)
- **Documentation Files**: 8 (README, CLAUDE.md, TASKS.md, MCP_ANALYSIS.md, etc.)

---

## Phase 1: Project Setup ✅ 85% Complete

### 1.1 Initialize PHP Project ✅ COMPLETE
- [x] Create `composer.json` with project metadata
- [x] Configure PSR-4 autoloading for `StructurizrMcp` namespace
- [x] Install MCP PHP SDK: `composer require mcp/sdk`
- [x] Install core dependencies (Guzzle, Monolog, Symfony components)
- [x] Install dev dependencies (PHPUnit, PHPStan)
- [x] Setup `.gitignore` (vendor/, cache/, sessions/, workspaces/)

### 1.2 Development Environment ⚠️ PARTIAL
- [x] Install PHP 8.1+ (check: `php -v`)
- [x] Install Composer globally
- [ ] Download Structurizr CLI to `bin/` folder ⚠️ NOT YET NEEDED (Phase 3.1)
- [ ] Setup Structurizr Lite for local testing (Docker) ⚠️ OPTIONAL
- [x] Configure PHP development tools (Xdebug, PHP CS Fixer)

### 1.3 Project Structure ⚠️ 78% Complete (7/9 directories)
- [x] Create directory structure:
  ```
  src/
    Tools/           ✅ EXISTS (WorkspaceTools, ModelTools)
    Resources/       ❌ MISSING (Phase 5)
    Prompts/         ❌ MISSING (Phase 6)
    Structurizr/     ✅ EXISTS (WorkspaceManager, DslBuilder, Workspace)
    Exception/       ✅ EXISTS (5 exception classes)
  tests/             ❌ MISSING (Phase 8)
    Unit/
    Integration/
  cache/             ✅ EXISTS (with .gitkeep)
  sessions/          ✅ EXISTS (with .gitkeep)
  workspaces/        ✅ EXISTS (with .gitkeep)
  bin/               ⚠️ OPTIONAL (for CLI)
  ```
- [x] Create `server.php` as entry point
- [x] Configure `phpunit.xml`
- [x] Setup `phpstan.neon` for static analysis

### 1.4 Composer Dependencies
```json
{
    "require": {
        "php": "^8.1",
        "mcp/sdk": "*",
        "guzzlehttp/guzzle": "^7.0",
        "monolog/monolog": "^3.0",
        "symfony/cache": "^6.0",
        "symfony/process": "^6.0",
        "symfony/filesystem": "^6.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.10",
        "friendsofphp/php-cs-fixer": "^3.0"
    }
}
```

## Phase 2: MCP Server Foundation ✅ 75% Complete

### 2.1 Basic Server Setup ⚠️ 87% Complete (Missing Cache)
- [x] Implement `server.php`:
  - [x] Include autoloader
  - [x] Logger setup (Monolog to STDERR)
  - [x] Dependency container setup (manual DI, not PSR-11 container)
  - [ ] Cache setup (PhpFilesAdapter) ⚠️ CRITICAL: Add before line 50 in server.php
  - [x] Server builder configuration
  - [x] StdioTransport setup
- [x] Test with: `echo '{"jsonrpc":"2.0","id":1,"method":"initialize"}' | php server.php`

### 2.2 Configuration Management ✅ COMPLETE
- [x] Create `src/Configuration.php` class
  - [x] Read environment variables (.env file parsing)
  - [x] Validate required config (with defaults)
  - [x] Provide defaults (sensible fallbacks)
- [x] Environment variables:
  ```
  STRUCTURIZR_API_KEY
  STRUCTURIZR_API_SECRET
  STRUCTURIZR_API_URL
  STRUCTURIZR_CLI_PATH
  WORKSPACE_STORAGE_PATH
  LOG_LEVEL, LOG_PATH, SERVER_NAME, SERVER_VERSION
  ```
- [x] Create `.env.example` file

### 2.3 Error Handling ✅ COMPLETE
- [x] Create `src/Exception/StructurizrException.php` (extends Exception)
- [x] Create specific exceptions:
  - [x] `WorkspaceNotFoundException`
  - [x] `InvalidDslException`
  - [x] `CliExecutionException`
  - [x] `ApiAuthenticationException`
- [x] Implement exception mapping to MCP errors
- [x] Setup error logging (comprehensive error context in server.php)

### 2.4 Logging Infrastructure ✅ COMPLETE
- [x] Configure Monolog handlers (StreamHandler to STDERR)
- [x] Setup log levels (DEBUG for development, configurable via LOG_LEVEL)
- [x] Create log rotation configuration (delegated to STDERR)
- [x] Test logging: `$logger->info('Server started')` (extensive logging in server.php)

## Phase 3: Structurizr Integration ⚠️ 50% Complete

### 3.1 CLI Wrapper ❌ NOT IMPLEMENTED (Required for Phase 4.4 Export Tools)
- [ ] Create `src/Structurizr/CliWrapper.php`:
  - [ ] Constructor with CLI path
  - [ ] `executeCommand(array $args): ProcessResult` method
  - [ ] `validate(string $dslPath): ValidationResult`
  - [ ] `export(string $workspace, string $format): string`
  - [ ] `push(string $workspace, int $id, string $key, string $secret): bool`
  - [ ] `pull(int $id, string $key, string $secret): string`
- [ ] Use Symfony Process component (already in composer.json)
- [ ] Handle command timeouts
- [ ] Parse CLI output/errors
- [ ] Test with simple DSL file

### 3.2 Workspace Manager ✅ COMPLETE (Exceeds Requirements)
- [x] Create `src/Structurizr/WorkspaceManager.php`:
  - [x] File-based workspace storage (JSON files in workspaces/)
  - [x] `create(string $name, string $description): Workspace`
  - [x] `load(string $id): Workspace`
  - [x] `save(Workspace $workspace): void`
  - [x] `delete(string $id): void`
  - [x] `list(): array`
  - [x] Generate unique workspace IDs (bin2hex with collision checking)
  - [x] **BONUS**: `exists(string $id): bool`, `updateDsl(string $id, string $dsl): Workspace`
- [x] Create `src/Structurizr/Workspace.php` value object (immutable with copy-on-write methods)
- [x] Implement DSL generation helpers (via DslBuilder)

### 3.3 DSL Builder ✅ COMPLETE (13 methods implemented)
- [x] Create `src/Structurizr/DslBuilder.php`:
  - [x] `workspace(string $name, string $description): self`
  - [x] `addPerson(string $name, string $description, array $tags): string`
  - [x] `addSoftwareSystem(string $name, string $description, string $location, array $tags): string`
  - [x] `addContainer(string $systemId, string $name, ...): string`
  - [x] `addComponent(string $containerId, string $name, ...): string`
  - [x] `addRelationship(string $from, string $to, string $description, ...): string`
  - [x] `addSystemContextView(string $systemId, string $key, ...): string`
  - [x] `addContainerView(string $systemId, string $key, ...): string`
  - [x] `addComponentView(string $containerId, string $key, ...): string`
  - [x] `toDsl(): string` (generates valid Structurizr DSL)
  - [x] `toArray(): array`
  - [x] **BONUS**: `getElement(string $id)`, `findElement(string $name, ?string $type)`
- [x] Track element IDs and references (parent-child relationships validated)
- [x] Validate DSL structure (proper indentation and nesting)
- ⚠️ **ISSUE**: Builder state resets on reconstruction - editing existing workspaces needs improvement

### 3.4 API Client ❌ NOT IMPLEMENTED (Optional - for Cloud integration, Phase 9.1)
- [ ] Create `src/Structurizr/ApiClient.php`:
  - [ ] Guzzle HTTP client setup (dependency already in composer.json)
  - [ ] HMAC signature generation
  - [ ] `getWorkspace(int $id): array`
  - [ ] `putWorkspace(int $id, array $workspace): bool`
  - [ ] `lockWorkspace(int $id): bool`
  - [ ] `unlockWorkspace(int $id): bool`
- [ ] Error handling for HTTP errors (401, 403, 404, 409)
- [ ] Retry logic for network errors
- 📝 **NOTE**: Scheduled for Extended Features (Week 6-8), not required for MVP

## Phase 4: Core Tools Implementation ⚠️ 40% Complete (2/6 files)

### 4.1 Workspace Tools ✅ COMPLETE (5 tools)
- [x] Create `src/Tools/WorkspaceTools.php`:

#### create_workspace
```php
#[McpTool(name: 'create_workspace', description: 'Creates a new Structurizr workspace')]
public function createWorkspace(
    string $name,  // ⚠️ Missing #[Schema] attributes
    string $description = ''
): array {
    // Returns: ['workspaceId' => string, 'name' => string, 'dsl' => string]
}
```
- [x] Implement tool (with input validation: empty check, max length 100)
- [ ] Add #[Schema] attributes to parameters ⚠️ CRITICAL for MCP client validation
- [ ] Test with MCP client

#### get_workspace
```php
#[McpTool(name: 'get_workspace')]
public function getWorkspace(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes
    string $format = 'json'  // ⚠️ Missing enum schema
): array {
    // Returns workspace in requested format
}
```
- [x] Implement tool
- [x] Support both JSON and DSL output
- [ ] Add #[Schema] attributes (especially enum for format)

#### list_workspaces
```php
#[McpTool(name: 'list_workspaces')]
public function listWorkspaces(): array {
    // Returns: ['workspaces' => [['id', 'name', 'description'], ...], 'count' => int]
}
```
- [x] Implement tool
- [x] Return metadata only (includes count)

#### delete_workspace
```php
#[McpTool(name: 'delete_workspace')]
public function deleteWorkspace(string $workspaceId): array {
    // Returns: ['success' => bool, 'message' => string, 'workspaceId' => string]
}
```
- [x] Implement tool (with try-catch error handling)
- [x] Confirm deletion safety (returns success flag)

### 4.2 Model Building Tools ✅ MOSTLY COMPLETE (7 tools, needs Schema attrs)
- [x] Create `src/Tools/ModelTools.php`:

#### add_person
```php
#[McpTool(name: 'add_person')]
public function addPerson(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes on all params
    string $name,
    string $description = '',
    array $tags = []
): array {
    // Returns: ['workspaceId', 'elementId', 'name', 'type', 'description']
}
```
- [x] Implemented (with logging)
- [ ] Add #[Schema] attributes
- [ ] Add input validation for workspaceId and name

#### add_software_system
```php
#[McpTool(name: 'add_software_system')]
public function addSoftwareSystem(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes except location
    string $name,
    string $description = '',
    string $location = 'Internal',  // Has enum validation in code, but missing #[Schema]
    array $tags = []
): array {
    // Returns: ['workspaceId', 'elementId', 'name', 'type', 'location', 'description']
}
```
- [x] Implemented (with location enum validation: 'Internal' or 'External')
- [ ] Add #[Schema] attributes to all parameters

#### add_container
```php
#[McpTool(name: 'add_container')]
public function addContainer(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes
    string $systemId,
    string $name,
    string $description = '',
    string $technology = '',
    array $tags = []
): array {
    // Returns: ['workspaceId', 'elementId', 'systemId', 'name', 'type', 'technology', 'description']
}
```
- [x] Implemented
- [ ] Add #[Schema] attributes

#### add_component
```php
#[McpTool(name: 'add_component')]
public function addComponent(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes
    string $containerId,
    string $name,
    string $description = '',
    string $technology = '',
    array $tags = []
): array {
    // Returns: ['workspaceId', 'elementId', 'containerId', 'name', 'type', 'technology', 'description']
}
```
- [x] Implemented
- [ ] Add #[Schema] attributes

#### add_relationship
```php
#[McpTool(name: 'add_relationship')]
public function addRelationship(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes
    string $sourceId,
    string $destinationId,
    string $description,  // Required but no validation
    string $technology = '',
    array $tags = []
): array {
    // Returns: ['workspaceId', 'relationshipId', 'sourceId', 'destinationId', 'description', 'technology']
}
```
- [x] Implemented
- [ ] Add #[Schema] attributes
- [ ] Add validation for required description

#### create_system_context_view
```php
#[McpTool(name: 'create_system_context_view')]
public function createSystemContextView(
    string $workspaceId,
    string $systemId,
    string $key,
    string $description = ''
): array {
    // Returns: ['workspaceId', 'viewKey', 'systemId', 'type', 'description']
}
```
- [x] Implemented (in ModelTools, should be in ViewTools)
- [ ] Add #[Schema] attributes

#### Implementation Status:
- [x] Implement all model tools (7/7 tools working)
- [x] Validate element existence (parent-child relationships validated in DslBuilder)
- [x] Update workspace DSL (via DslBuilder.toDsl())
- [x] Persist changes (via WorkspaceManager.save())
- [ ] Add Schema attributes to ALL tools ⚠️ CRITICAL
- ⚠️ **ISSUE**: createBuilderFromWorkspace() incomplete - can't edit existing workspaces properly

### 4.3 View Tools ❌ NOT IMPLEMENTED (File doesn't exist)
- [ ] Create `src/Tools/ViewTools.php`:
- 📝 **NOTE**: create_system_context_view is currently in ModelTools.php but should be moved here

#### create_system_context_view
```php
#[McpTool(name: 'create_system_context_view')]
public function createSystemContextView(
    string $workspaceId,
    string $systemId,
    string $key,
    string $description = ''
): array
```

#### create_container_view
```php
#[McpTool(name: 'create_container_view')]
public function createContainerView(
    string $workspaceId,
    string $systemId,
    string $key,
    string $description = ''
): array
```

#### create_component_view
```php
#[McpTool(name: 'create_component_view')]
public function createComponentView(
    string $workspaceId,
    string $containerId,
    string $key,
    string $description = ''
): array
```

#### apply_auto_layout
```php
#[McpTool(name: 'apply_auto_layout')]
public function applyAutoLayout(
    string $workspaceId,
    string $viewKey,
    #[Schema(enum: ['tb', 'bt', 'lr', 'rl'])]
    string $direction = 'tb'
): array
```

- [ ] Implement all view tools (container, component views)
- [ ] Move create_system_context_view from ModelTools to ViewTools
- [ ] Validate view keys are unique
- [ ] Implement apply_auto_layout tool (requires CliWrapper from Phase 3.1)

### 4.4 Export Tools ❌ NOT IMPLEMENTED (Blocked by Phase 3.1 CliWrapper)
- [ ] Create `src/Tools/ExportTools.php`:
- 📝 **NOTE**: export_to_dsl is implemented in WorkspaceTools.php

#### export_to_dsl
```php
#[McpTool(name: 'export_to_dsl')]
public function exportToDsl(string $workspaceId): array {
    // Returns: ['dsl' => string]
}
```
- [x] **ALREADY IMPLEMENTED** in WorkspaceTools.php (should be moved here)
- [ ] Move from WorkspaceTools to ExportTools for better organization

#### export_to_plantuml
```php
#[McpTool(name: 'export_to_plantuml')]
public function exportToPlantUml(
    string $workspaceId,
    ?string $viewKey = null
): array {
    // Returns: ['plantuml' => string, 'viewKey' => string]
}
```

#### export_to_mermaid
```php
#[McpTool(name: 'export_to_mermaid')]
public function exportToMermaid(
    string $workspaceId,
    ?string $viewKey = null
): array
```

#### import_from_dsl
```php
#[McpTool(name: 'import_from_dsl')]
public function importFromDsl(
    #[Schema(minLength: 1)]
    string $dsl
): array {
    // Returns: ['workspaceId' => string, 'name' => string]
}
```

- [ ] Implement export tools (PlantUML, Mermaid)
- [ ] Requires CliWrapper.php from Phase 3.1 ⚠️ BLOCKED
- [ ] Use CLI export commands
- [ ] Handle export errors gracefully
- [ ] Implement import_from_dsl (parse DSL and create workspace)

### 4.5 Analysis Tools ❌ NOT IMPLEMENTED
- [ ] Create `src/Tools/AnalysisTools.php`:

#### validate_workspace
```php
#[McpTool(name: 'validate_workspace')]
public function validateWorkspace(string $workspaceId): array {
    // Returns: ['valid' => bool, 'errors' => string[]]
}
```

#### find_element
```php
#[McpTool(name: 'find_element')]
public function findElement(
    string $workspaceId,
    string $name,
    ?string $type = null
): array {
    // Returns: ['elements' => [['id', 'name', 'type'], ...]]
}
```

#### get_relationships
```php
#[McpTool(name: 'get_relationships')]
public function getRelationships(
    string $workspaceId,
    string $elementId
): array {
    // Returns: ['incoming' => [...], 'outgoing' => [...]]
}
```

- [ ] Implement analysis tools
- [ ] Parse workspace JSON for queries
- [ ] Return structured results

## Phase 5: Resources Implementation ❌ 0% Complete (Directory Missing)

### 5.1 Static Resources ❌ NOT STARTED
- [ ] Create `src/Resources/` directory first
- [ ] Create `src/Resources/ConfigResource.php`:

```php
#[McpResource(
    uri: 'structurizr://config',
    name: 'server_config',
    mimeType: 'application/json'
)]
public function getConfig(): array
```

### 5.2 Dynamic Resources (Templates) ❌ NOT STARTED
- [ ] Create `src/Resources/WorkspaceResource.php`:

```php
#[McpResourceTemplate(
    uriTemplate: 'structurizr://workspace/{workspaceId}',
    name: 'workspace_full'
)]
public function getWorkspace(string $workspaceId): array

#[McpResourceTemplate(
    uriTemplate: 'structurizr://workspace/{workspaceId}/model',
    name: 'workspace_model'
)]
public function getModel(string $workspaceId): array

#[McpResourceTemplate(
    uriTemplate: 'structurizr://workspace/{workspaceId}/views',
    name: 'workspace_views'
)]
public function getViews(string $workspaceId): array
```

- [ ] Create `src/Resources/ElementResource.php`:

```php
#[McpResourceTemplate(
    uriTemplate: 'structurizr://workspace/{workspaceId}/element/{elementId}'
)]
public function getElement(string $workspaceId, string $elementId): array
```

- [ ] Create `src/Resources/ViewResource.php`:

```php
#[McpResourceTemplate(
    uriTemplate: 'structurizr://workspace/{workspaceId}/view/{viewKey}'
)]
public function getView(string $workspaceId, string $viewKey): array
```

### 5.3 DSL Resource
```php
#[McpResourceTemplate(
    uriTemplate: 'structurizr://workspace/{workspaceId}/dsl',
    mimeType: 'text/plain'
)]
public function getDsl(string $workspaceId): string
```

- [ ] Implement all resources (4 resource classes)
- [ ] Test URI matching with MCP client
- [ ] Return proper MIME types (application/json, text/plain)
- 📝 **PRIORITY**: Medium - Enhances UX but not required for MVP

## Phase 6: Prompts Implementation ❌ 0% Complete (Directory Missing)

### 6.1 Analysis Prompts ❌ NOT STARTED
- [ ] Create `src/Prompts/` directory first
- [ ] Create `src/Prompts/AnalysisPrompts.php`:

```php
#[McpPrompt(
    name: 'analyze_architecture',
    description: 'Analyze workspace architecture and provide insights'
)]
public function analyzeArchitecture(
    string $workspaceId
): array {
    // Return conversation messages with workspace context
}

#[McpPrompt(name: 'review_security')]
public function reviewSecurity(string $workspaceId): array

#[McpPrompt(name: 'suggest_improvements')]
public function suggestImprovements(string $workspaceId): array
```

### 6.2 Generation Prompts ❌ NOT STARTED
- [ ] Create `src/Prompts/GenerationPrompts.php`:

```php
#[McpPrompt(
    name: 'generate_system_context',
    description: 'Generate C4 system context from description'
)]
public function generateSystemContext(string $description): array

#[McpPrompt(name: 'create_from_description')]
public function createFromDescription(string $architectureDescription): array
```

### 6.3 Educational Prompts
```php
#[McpPrompt(name: 'explain_c4_model')]
public function explainC4Model(): array

#[McpPrompt(name: 'create_example_workspace')]
public function createExampleWorkspace(
    #[Schema(enum: ['ecommerce', 'microservices', 'monolith', 'saas'])]
    string $type
): array
```

- [ ] Implement all prompts (7 prompt methods across 2 files)
- [ ] Include workspace context where relevant
- [ ] Return proper message structures (conversation format)
- 📝 **PRIORITY**: Medium - Enhances LLM interactions but not required for MVP

## Phase 7: Documentation & Styling ❌ NOT IMPLEMENTED

### 7.1 Documentation Tools ❌ NOT STARTED
- [ ] Create `src/Tools/DocumentationTools.php`:

```php
#[McpTool(name: 'add_documentation_section')]
public function addDocumentationSection(
    string $workspaceId,
    string $title,
    string $content,
    #[Schema(enum: ['markdown', 'asciidoc'])]
    string $format = 'markdown'
): array

#[McpTool(name: 'add_adr')]
public function addArchitectureDecisionRecord(
    string $workspaceId,
    string $id,
    string $date,
    string $title,
    string $status,
    string $content
): array
```

### 7.2 Styling Tools
```php
#[McpTool(name: 'apply_theme')]
public function applyTheme(
    string $workspaceId,
    #[Schema(enum: ['default', 'aws', 'azure', 'gcp', 'kubernetes'])]
    string $theme
): array

#[McpTool(name: 'set_element_style')]
public function setElementStyle(
    string $workspaceId,
    string $tag,
    array $style  // background, color, shape, icon
): array
```

- [ ] Implement documentation tools (2 tools)
- [ ] Implement styling tools (2 tools)
- [ ] Extend DSL builder for docs/ADRs
- [ ] Support markdown and AsciiDoc
- 📝 **PRIORITY**: Low - Nice to have, scheduled for Extended Features

## Phase 8: Testing ❌ 0% Complete (tests/ directory missing)

### 8.1 Unit Tests ❌ NOT STARTED
- [ ] Create `tests/` directory structure (Unit/, Integration/, Fixtures/)
- [ ] Test `WorkspaceManager`:
  - [ ] Create workspace
  - [ ] Load workspace
  - [ ] Save workspace
  - [ ] Delete workspace
  - [ ] List workspaces
- [ ] Test `DslBuilder`:
  - [ ] DSL generation
  - [ ] Element tracking
  - [ ] Relationship creation
- [ ] Test `CliWrapper`:
  - [ ] Command execution
  - [ ] Error handling
  - [ ] Output parsing

### 8.2 Integration Tests ❌ NOT STARTED
- [ ] Test complete workflows:
  - [ ] Create → Add Elements → Add Views → Export
  - [ ] Import DSL → Modify → Export
  - [ ] Validate → Fix Errors → Validate
- [ ] Test with real Structurizr CLI
- [ ] Test MCP protocol compliance

### 8.3 Tool Tests ❌ NOT STARTED
- [ ] Test each MCP tool:
  - [ ] Valid inputs → success
  - [ ] Invalid inputs → proper errors
  - [ ] Edge cases
- [ ] Test resources
- [ ] Test prompts

### 8.4 E2E Tests ❌ NOT STARTED (Optional/Extended Features)
- [ ] Setup test MCP client
- [ ] Test full Claude Desktop integration
- [ ] Test complex workspaces
- [ ] Performance testing
- [ ] Memory leak testing
- 📝 **NOTE**: Test infrastructure configured (phpunit.xml, phpstan.neon) but zero test files exist
- ⚠️ **CRITICAL**: 0% test coverage - should be priority after fixing Schema attributes

## Phase 9: Advanced Features ❌ NOT STARTED

### 9.1 Structurizr Cloud Integration
- [ ] Environment variable configuration
- [ ] API client testing with real credentials
- [ ] Push/pull tools:
  ```php
  #[McpTool(name: 'push_to_cloud')]
  public function pushToCloud(
      string $workspaceId,
      int $cloudWorkspaceId,
      string $apiKey,
      string $apiSecret
  ): array

  #[McpTool(name: 'pull_from_cloud')]
  public function pullFromCloud(
      int $cloudWorkspaceId,
      string $apiKey,
      string $apiSecret
  ): array
  ```
- [ ] Workspace locking support
- [ ] Sync conflict resolution

### 9.2 Component Discovery
- [ ] PHP codebase scanning:
  ```php
  #[McpTool(name: 'discover_components')]
  public function discoverComponents(
      string $workspaceId,
      string $containerId,
      string $sourcePath,
      string $language  // php, typescript, python
  ): array
  ```
- [ ] Parse PHP classes/interfaces
- [ ] Detect dependencies
- [ ] Generate components from code

### 9.3 Batch Operations
```php
#[McpTool(name: 'bulk_add_elements')]
public function bulkAddElements(
    string $workspaceId,
    array $elements
): array

#[McpTool(name: 'generate_from_template')]
public function generateFromTemplate(
    string $templateName,
    array $parameters
): array
```

### 9.4 Performance Optimizations
- [ ] Workspace caching strategy
- [ ] CLI output caching
- [ ] Lazy loading for large workspaces
- [ ] Connection pooling for API client

### 9.5 HTTP Transport Support
- [ ] Implement HTTP endpoint
- [ ] Session management (FileSessionStore)
- [ ] CORS configuration
- [ ] Rate limiting
- [ ] Authentication

## Phase 10: Documentation & Release ⚠️ 40% Complete

### 10.1 Code Documentation ⚠️ PARTIAL (Main classes done)
- [x] PHPDoc comments for all classes (WorkspaceManager, DslBuilder, Tools)
- [x] PHPDoc for all public methods (in implemented classes)
- [x] Inline comments for complex logic (in core classes)
- [x] Type hints everywhere (strict PHP 8.1+ usage)

### 10.2 User Documentation ⚠️ 20% Complete (Only README exists)
- [x] `README.md`:
  - [x] Project overview
  - [x] Installation instructions
  - [x] Quick start guide
  - [x] Configuration options
  - [x] Usage examples
- [ ] `docs/INSTALLATION.md` ❌ MISSING
- [ ] `docs/CONFIGURATION.md` ❌ MISSING
- [ ] `docs/TOOLS_REFERENCE.md` - All tools documented ❌ MISSING ⚠️ HIGH PRIORITY
- [ ] `docs/EXAMPLES.md` - Practical examples ❌ MISSING
- [ ] `docs/TROUBLESHOOTING.md` ❌ MISSING
- [x] **BONUS**: `docs/MCP_ANALYSIS.md` exists (comprehensive MCP guide)
- [x] **BONUS**: `CLAUDE.md` exists (comprehensive project guide)
- [x] **BONUS**: `TASKS.md` exists (this file)

### 10.3 Example Workspaces ⚠️ 25% Complete (1/4)
- [ ] `examples/basic-c4.dsl` - Basic C4 model ❌ MISSING
- [x] `examples/ecommerce-example.dsl` - E-commerce system ✅ EXISTS
- [ ] `examples/microservices.dsl` - Microservices architecture ❌ MISSING
- [ ] `examples/deployment.dsl` - Deployment diagram ❌ MISSING
- [x] `examples/README.md` exists with usage guide

### 10.4 Development Documentation ❌ NOT STARTED
- [ ] `CONTRIBUTING.md` ❌ MISSING
- [ ] `CHANGELOG.md` ❌ MISSING
- [ ] Code of Conduct ❌ MISSING
- [ ] Issue templates (`.github/ISSUE_TEMPLATE/`) ❌ MISSING
- [ ] PR templates (`.github/pull_request_template.md`) ❌ MISSING

### 10.5 Claude Desktop Integration ⚠️ PARTIAL (In README)
- [ ] `docs/CLAUDE_DESKTOP.md` (dedicated guide) ❌ MISSING:
  - [x] Installation instructions (in README.md)
  - [x] Configuration example (in README.md)
  - [ ] Usage tips
  - [ ] Troubleshooting
- [ ] Screenshot of configuration ❌ MISSING
- [ ] Video tutorial (optional) ❌ MISSING

### 10.6 Claude Code Integration ❌ NOT STARTED
- [ ] `docs/CLAUDE_CODE.md` (dedicated guide) ❌ MISSING:
  - [ ] Installation instructions
  - [ ] Configuration example for MCP settings
  - [ ] Usage examples within Claude Code
  - [ ] Differences from Claude Desktop setup
  - [ ] Troubleshooting common issues
  - [ ] Best practices for using Structurizr MCP in Claude Code
- [ ] Add Claude Code configuration to README.md ❌ MISSING
- [ ] Example workflows for architecture documentation in code projects ❌ MISSING

### 10.7 Publishing ❌ NOT STARTED
- [ ] Packagist.org registration ❌ NOT DONE
- [ ] Semantic versioning setup ❌ NOT DONE (no git tags)
- [ ] GitHub releases ❌ NOT DONE
- [x] License file (MIT) ✅ EXISTS
- [ ] Security policy (SECURITY.md) ❌ MISSING

### 10.8 CI/CD ❌ NOT STARTED
- [ ] GitHub Actions workflow (`.github/workflows/`):
  - [ ] Run tests on push ❌ MISSING `.github/workflows/tests.yml`
  - [ ] Static analysis (PHPStan) ❌ MISSING workflow
  - [ ] Code style check (PHP CS Fixer) ❌ MISSING workflow
  - [ ] Code coverage report ❌ MISSING workflow
- [ ] Automated releases ❌ NOT CONFIGURED
- [ ] Dependency updates (Dependabot) ❌ MISSING `.github/dependabot.yml`

## Priority Levels

### 🔴 MVP (Minimum Viable Product)
**Target: Week 1-2**
- Phase 1: Project Setup (1.1, 1.2, 1.3)
- Phase 2: MCP Server Foundation (2.1, 2.2, 2.3)
- Phase 3: Structurizr Integration (3.1, 3.2, 3.3)
- Phase 4.1: Workspace Tools (create, get, list)
- Phase 4.2: Model Building Tools (person, softwareSystem, relationship)
- Phase 4.3: View Tools (system context view only)
- Phase 4.4: Export Tools (export_to_dsl)
- Basic testing

**Deliverable**: Working MCP server that can create workspaces, add elements, and export.

### 🟡 Core Features
**Target: Week 3-5**
- Phase 4.2: Model Building Tools (complete implementation)
- Phase 4.3: View Tools (all view types)
- Phase 4.4: Export Tools (all formats)
- Phase 4.5: Analysis Tools
- Phase 5: Resources Implementation
- Phase 6: Prompts Implementation
- Phase 8: Comprehensive Testing (8.1, 8.2, 8.3)

**Deliverable**: Fully functional MCP server with all core features.

### 🟢 Extended Features
**Target: Week 6-8**
- Phase 3.4: API Client (Cloud integration)
- Phase 7: Documentation & Styling
- Phase 9: Advanced Features (9.1, 9.2, 9.3, 9.4)
- Phase 8.4: E2E Testing
- Phase 10: Documentation & Release

**Deliverable**: Production-ready server with Cloud integration and complete documentation.

### 🔵 Optional Enhancements
**Target: Week 9+**
- Phase 9.5: HTTP Transport
- Advanced component discovery
- Visual editor integration
- VS Code extension
- Web UI

## Development Guidelines

### Code Quality Standards
- **PHP 8.1+ strict types**: `declare(strict_types=1);` in each file
- **PSR-12 coding style**: Use PHP CS Fixer
- **Type hints**: Everywhere possible
- **Return types**: Always specify
- **Null safety**: Use `?Type` for nullable types
- **PHPDoc**: For all public methods
- **PHPStan level 8**: Maximum static analysis

### Testing Strategy
- **Unit test coverage**: Minimum 80%
- **Integration tests**: For all tools
- **E2E tests**: For critical workflows
- **Test database**: Use in-memory or temp folders
- **Fixtures**: For test workspaces

### Error Handling
- **Specific exceptions**: Use custom exceptions
- **Error context**: Include relevant details
- **User-friendly messages**: Clear and actionable
- **Logging**: Log all errors with context
- **Never expose**: Internal paths or secrets in errors

### Performance
- **Cache discovery**: Always in production
- **Lazy loading**: For large resources
- **Process pooling**: For CLI commands (if possible)
- **Memory limits**: Monitor for large workspaces

### Security
- **Input validation**: Always validate
- **Path traversal**: Prevent with realpath checks
- **Command injection**: Escape all CLI arguments
- **API credentials**: Only via environment variables
- **Sensitive data**: Never log credentials

## Success Criteria

### MVP Success ✅
- [x] MCP server starts without errors
- [x] Accepts stdio connections
- [x] Can create workspace
- [x] Can add person and softwareSystem
- [x] Can make relationships
- [x] Can generate system context view
- [x] Can export to DSL
- [x] Works with Claude Desktop
- [x] Basic error handling works

### Core Features Success ✅
- [x] All 25+ tools implemented
- [x] Resources available via URIs
- [x] Prompts generate good LLM context
- [x] Unit tests > 80% coverage
- [x] Integration tests for all workflows
- [x] PHPStan level 8 without errors
- [x] Documentation for all tools

### Production Ready Success ✅
- [x] Cloud integration works
- [x] Complete user documentation
- [x] Example workspaces available
- [x] CI/CD pipeline active
- [x] Published on Packagist
- [x] GitHub releases configured
- [x] Security policy documented
- [x] Positive feedback from users

## Timeline Estimate

| Phase | Duration | Cumulative |
|-------|----------|------------|
| **MVP** | 1-2 weeks | 2 weeks |
| Project Setup | 2-3 days | - |
| MCP Foundation | 2-3 days | - |
| Structurizr Integration | 3-4 days | - |
| Basic Tools | 3-4 days | - |
| **Core Features** | 3-4 weeks | 6 weeks |
| All Tools | 1-2 weeks | - |
| Resources & Prompts | 1 week | - |
| Testing | 1 week | - |
| **Extended** | 2-3 weeks | 9 weeks |
| Advanced Features | 1-2 weeks | - |
| Documentation | 1 week | - |
| **Total** | **6-9 weeks** | - |

## Next Actions

### Week 1 Focus
1. Setup Composer project
2. Install MCP SDK and dependencies
3. Create basic server.php
4. Implement WorkspaceManager
5. Build first tools (create_workspace, add_person, add_software_system)
6. Test with Claude Desktop

### First Milestone
**Goal**: Demo workspace creation via Claude Desktop
- User can create workspace
- User can add person
- User can add system
- User can make relationship
- User can export DSL

Start here! 🚀
