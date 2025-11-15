# Structurizr MCP Server - Implementation Tasks (PHP)

## Phase 1: Project Setup ⏳

### 1.1 Initialize PHP Project
- [ ] Create `composer.json` with project metadata
- [ ] Configure PSR-4 autoloading for `StructurizrMcp` namespace
- [ ] Install MCP PHP SDK: `composer require mcp/sdk`
- [ ] Install core dependencies (Guzzle, Monolog, Symfony components)
- [ ] Install dev dependencies (PHPUnit, PHPStan)
- [ ] Setup `.gitignore` (vendor/, cache/, sessions/, workspaces/)

### 1.2 Development Environment
- [ ] Install PHP 8.1+ (check: `php -v`)
- [ ] Install Composer globally
- [ ] Download Structurizr CLI to `bin/` folder
- [ ] Setup Structurizr Lite for local testing (Docker)
- [ ] Configure PHP development tools (Xdebug, PHP CS Fixer)

### 1.3 Project Structure
- [ ] Create directory structure:
  ```
  src/
    Tools/
    Resources/
    Prompts/
    Structurizr/
    Exception/
  tests/
    Unit/
    Integration/
  cache/
  sessions/
  workspaces/
  bin/
  ```
- [ ] Create `server.php` as entry point
- [ ] Configure `phpunit.xml`
- [ ] Setup `phpstan.neon` for static analysis

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

## Phase 2: MCP Server Foundation 🏗️

### 2.1 Basic Server Setup
- [ ] Implement `server.php`:
  - [ ] Include autoloader
  - [ ] Logger setup (Monolog to STDERR)
  - [ ] Dependency container setup
  - [ ] Cache setup (PhpFilesAdapter)
  - [ ] Server builder configuration
  - [ ] StdioTransport setup
- [ ] Test with: `echo '{"jsonrpc":"2.0","id":1,"method":"initialize"}' | php server.php`

### 2.2 Configuration Management
- [ ] Create `src/Configuration.php` class
  - [ ] Read environment variables
  - [ ] Validate required config
  - [ ] Provide defaults
- [ ] Environment variables:
  ```
  STRUCTURIZR_API_KEY
  STRUCTURIZR_API_SECRET
  STRUCTURIZR_API_URL
  STRUCTURIZR_CLI_PATH
  WORKSPACE_STORAGE_PATH
  ```
- [ ] Create `.env.example` file

### 2.3 Error Handling
- [ ] Create `src/Exception/StructurizrException.php` (extends Exception)
- [ ] Create specific exceptions:
  - [ ] `WorkspaceNotFoundException`
  - [ ] `InvalidDslException`
  - [ ] `CliExecutionException`
  - [ ] `ApiAuthenticationException`
- [ ] Implement exception mapping to MCP errors
- [ ] Setup error logging

### 2.4 Logging Infrastructure
- [ ] Configure Monolog handlers
- [ ] Setup log levels (DEBUG for development)
- [ ] Create log rotation configuration
- [ ] Test logging: `$logger->info('Server started')`

## Phase 3: Structurizr Integration 🔌

### 3.1 CLI Wrapper
- [ ] Create `src/Structurizr/CliWrapper.php`:
  - [ ] Constructor with CLI path
  - [ ] `executeCommand(array $args): ProcessResult` method
  - [ ] `validate(string $dslPath): ValidationResult`
  - [ ] `export(string $workspace, string $format): string`
  - [ ] `push(string $workspace, int $id, string $key, string $secret): bool`
  - [ ] `pull(int $id, string $key, string $secret): string`
- [ ] Use Symfony Process component
- [ ] Handle command timeouts
- [ ] Parse CLI output/errors
- [ ] Test with simple DSL file

### 3.2 Workspace Manager
- [ ] Create `src/Structurizr/WorkspaceManager.php`:
  - [ ] File-based workspace storage
  - [ ] `create(string $name, string $description): Workspace`
  - [ ] `load(string $id): Workspace`
  - [ ] `save(Workspace $workspace): void`
  - [ ] `delete(string $id): void`
  - [ ] `list(): array`
  - [ ] Generate unique workspace IDs
- [ ] Create `src/Structurizr/Workspace.php` value object
- [ ] Implement DSL generation helpers

### 3.3 DSL Builder
- [ ] Create `src/Structurizr/DslBuilder.php`:
  - [ ] `workspace(string $name, string $description): self`
  - [ ] `addPerson(string $name, string $description): string`
  - [ ] `addSoftwareSystem(string $name, string $description): string`
  - [ ] `addContainer(string $systemId, ...): string`
  - [ ] `addRelationship(string $from, string $to, ...): string`
  - [ ] `addView(string $type, ...): string`
  - [ ] `toDsl(): string`
  - [ ] `toArray(): array`
- [ ] Track element IDs and references
- [ ] Validate DSL structure

### 3.4 API Client (Optional - for Cloud integration)
- [ ] Create `src/Structurizr/ApiClient.php`:
  - [ ] Guzzle HTTP client setup
  - [ ] HMAC signature generation
  - [ ] `getWorkspace(int $id): array`
  - [ ] `putWorkspace(int $id, array $workspace): bool`
  - [ ] `lockWorkspace(int $id): bool`
  - [ ] `unlockWorkspace(int $id): bool`
- [ ] Error handling for HTTP errors (401, 403, 404, 409)
- [ ] Retry logic for network errors

## Phase 4: Core Tools Implementation 🛠️

### 4.1 Workspace Tools
- [ ] Create `src/Tools/WorkspaceTools.php`:

#### create_workspace
```php
#[McpTool(name: 'create_workspace', description: 'Creates a new Structurizr workspace')]
public function createWorkspace(
    #[Schema(description: 'Workspace name', minLength: 1, maxLength: 100)]
    string $name,
    #[Schema(description: 'Workspace description', maxLength: 500)]
    string $description = ''
): array {
    // Returns: ['workspaceId' => string, 'name' => string, 'dsl' => string]
}
```
- [ ] Implement tool
- [ ] Test with MCP client

#### get_workspace
```php
#[McpTool(name: 'get_workspace')]
public function getWorkspace(
    #[Schema(type: 'string')]
    string $workspaceId,
    #[Schema(enum: ['json', 'dsl'])]
    string $format = 'json'
): array {
    // Returns workspace in requested format
}
```
- [ ] Implement tool
- [ ] Support both JSON and DSL output

#### list_workspaces
```php
#[McpTool(name: 'list_workspaces')]
public function listWorkspaces(): array {
    // Returns: ['workspaces' => [['id', 'name', 'description'], ...]]
}
```
- [ ] Implement tool
- [ ] Return metadata only

#### delete_workspace
```php
#[McpTool(name: 'delete_workspace')]
public function deleteWorkspace(string $workspaceId): array {
    // Returns: ['success' => bool, 'message' => string]
}
```
- [ ] Implement tool
- [ ] Confirm deletion safety

### 4.2 Model Building Tools
- [ ] Create `src/Tools/ModelTools.php`:

#### add_person
```php
#[McpTool(name: 'add_person')]
public function addPerson(
    string $workspaceId,
    string $name,
    string $description = '',
    array $tags = []
): array {
    // Returns: ['elementId' => string, 'name' => string]
}
```

#### add_software_system
```php
#[McpTool(name: 'add_software_system')]
public function addSoftwareSystem(
    string $workspaceId,
    string $name,
    string $description = '',
    #[Schema(enum: ['Internal', 'External'])]
    string $location = 'Internal',
    array $tags = []
): array
```

#### add_container
```php
#[McpTool(name: 'add_container')]
public function addContainer(
    string $workspaceId,
    string $systemId,
    string $name,
    string $description = '',
    string $technology = '',
    array $tags = []
): array
```

#### add_component
```php
#[McpTool(name: 'add_component')]
public function addComponent(
    string $workspaceId,
    string $containerId,
    string $name,
    string $description = '',
    string $technology = '',
    array $tags = []
): array
```

#### add_relationship
```php
#[McpTool(name: 'add_relationship')]
public function addRelationship(
    string $workspaceId,
    string $sourceId,
    string $destinationId,
    string $description,
    string $technology = '',
    array $tags = []
): array
```

- [ ] Implement all model tools
- [ ] Validate element existence
- [ ] Update workspace DSL
- [ ] Persist changes

### 4.3 View Tools
- [ ] Create `src/Tools/ViewTools.php`:

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

- [ ] Implement all view tools
- [ ] Validate view keys are unique
- [ ] Use Structurizr CLI for auto-layout

### 4.4 Export Tools
- [ ] Create `src/Tools/ExportTools.php`:

#### export_to_dsl
```php
#[McpTool(name: 'export_to_dsl')]
public function exportToDsl(string $workspaceId): array {
    // Returns: ['dsl' => string]
}
```

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

- [ ] Implement export tools
- [ ] Use CLI export commands
- [ ] Handle export errors gracefully

### 4.5 Analysis Tools
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

## Phase 5: Resources Implementation 📦

### 5.1 Static Resources
- [ ] Create `src/Resources/ConfigResource.php`:

```php
#[McpResource(
    uri: 'structurizr://config',
    name: 'server_config',
    mimeType: 'application/json'
)]
public function getConfig(): array
```

### 5.2 Dynamic Resources (Templates)
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

- [ ] Implement all resources
- [ ] Test URI matching
- [ ] Return proper MIME types

## Phase 6: Prompts Implementation 💭

### 6.1 Analysis Prompts
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

### 6.2 Generation Prompts
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

- [ ] Implement all prompts
- [ ] Include workspace context where relevant
- [ ] Return proper message structures

## Phase 7: Documentation & Styling 📝

### 7.1 Documentation Tools
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

- [ ] Implement documentation tools
- [ ] Extend DSL builder for docs/ADRs
- [ ] Support markdown and AsciiDoc

## Phase 8: Testing 🧪

### 8.1 Unit Tests
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

### 8.2 Integration Tests
- [ ] Test complete workflows:
  - [ ] Create → Add Elements → Add Views → Export
  - [ ] Import DSL → Modify → Export
  - [ ] Validate → Fix Errors → Validate
- [ ] Test with real Structurizr CLI
- [ ] Test MCP protocol compliance

### 8.3 Tool Tests
- [ ] Test each MCP tool:
  - [ ] Valid inputs → success
  - [ ] Invalid inputs → proper errors
  - [ ] Edge cases
- [ ] Test resources
- [ ] Test prompts

### 8.4 E2E Tests
- [ ] Setup test MCP client
- [ ] Test full Claude Desktop integration
- [ ] Test complex workspaces
- [ ] Performance testing
- [ ] Memory leak testing

## Phase 9: Advanced Features 🚀

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

## Phase 10: Documentation & Release 📦

### 10.1 Code Documentation
- [ ] PHPDoc comments for all classes
- [ ] PHPDoc for all public methods
- [ ] Inline comments for complex logic
- [ ] Type hints everywhere

### 10.2 User Documentation
- [ ] `README.md`:
  - [ ] Project overview
  - [ ] Installation instructions
  - [ ] Quick start guide
  - [ ] Configuration options
  - [ ] Usage examples
- [ ] `docs/INSTALLATION.md`
- [ ] `docs/CONFIGURATION.md`
- [ ] `docs/TOOLS_REFERENCE.md` - All tools documented
- [ ] `docs/EXAMPLES.md` - Practical examples
- [ ] `docs/TROUBLESHOOTING.md`

### 10.3 Example Workspaces
- [ ] `examples/basic-c4.dsl` - Basic C4 model
- [ ] `examples/ecommerce.dsl` - E-commerce system
- [ ] `examples/microservices.dsl` - Microservices architecture
- [ ] `examples/deployment.dsl` - Deployment diagram

### 10.4 Development Documentation
- [ ] `CONTRIBUTING.md`
- [ ] `CHANGELOG.md`
- [ ] Code of Conduct
- [ ] Issue templates
- [ ] PR templates

### 10.5 Claude Desktop Integration
- [ ] `docs/CLAUDE_DESKTOP.md`:
  - [ ] Installation instructions
  - [ ] Configuration example
  - [ ] Usage tips
  - [ ] Troubleshooting
- [ ] Screenshot of configuration
- [ ] Video tutorial (optional)

### 10.6 Publishing
- [ ] Packagist.org registration
- [ ] Semantic versioning setup
- [ ] GitHub releases
- [ ] License file (MIT)
- [ ] Security policy

### 10.7 CI/CD
- [ ] GitHub Actions workflow:
  - [ ] Run tests on push
  - [ ] Static analysis (PHPStan)
  - [ ] Code style check (PHP CS Fixer)
  - [ ] Code coverage report
- [ ] Automated releases
- [ ] Dependency updates (Dependabot)

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
