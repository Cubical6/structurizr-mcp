# Structurizr MCP Server

## Project Overview

This project implements a Model Context Protocol (MCP) server for Structurizr, a tool for creating software architecture diagrams as code based on the C4 model.

## What is Structurizr?

Structurizr is a toolset for creating software architecture diagrams via code instead of manual drawing. It solves the fundamental problem of keeping architecture documentation synchronized and consistent.

### Core Value
- **Single source of truth**: One definition, multiple generated views
- **Version control**: Architecture as code in git
- **Automatic generation**: Multiple diagrams from one model
- **Consistency**: No diverging or contradictory documentation

## The C4 Model

The C4 model provides a structured method for visualizing software architecture with four hierarchical abstraction levels:

### 1. System Context (Level 1)
- Highest abstraction level
- Shows the system in context with users and external systems
- **Purpose**: Big picture overview

### 2. Container (Level 2)
- Main building blocks within a system
- Containers = applications, databases, microservices, etc.
- **Purpose**: High-level technology decisions and deployment architecture

### 3. Component (Level 3)
- Logical groupings within containers
- Components = cohesive functionality
- **Purpose**: Detailed design of a single container

### 4. Code (Level 4)
- Lowest level - actual code structure
- **Purpose**: Implementation details (often generated from code)

### Additional Diagram Types
- **System Landscape**: Multiple systems and their interactions
- **Dynamic**: Sequences and runtime behavior
- **Deployment**: Infrastructure and deployment topology

## Structurizr DSL

The Structurizr Domain Specific Language (DSL) is a text-based format for defining architecture models.

### Basic Structure

```dsl
workspace "Name" "Description" {
    model {
        # Define elements and relationships
        user = person "User" "Description"
        system = softwareSystem "System" "Description" {
            webapp = container "Web App" "Description" "Technology"
        }

        user -> system "Uses"
    }

    views {
        systemContext system "SystemContext" {
            include *
            autoLayout lr
        }

        container system "Containers" {
            include *
            autoLayout tb
        }

        styles {
            element "Software System" {
                background #1168bd
                color #ffffff
            }
        }
    }
}
```

### Main Elements
- **person**: Users of the system
- **softwareSystem**: Software systems
- **container**: Deployable/executable units
- **component**: Logical groupings within containers
- **Relationships**: `element1 -> element2 "Description" "Technology"`

## Structurizr Interfaces

### File Formats
1. **DSL (.dsl)**: Primary format for authoring
2. **JSON (.json)**: Internal workspace definition
3. **Export formats**: PlantUML, Mermaid, DOT, etc.

### APIs
1. **Workspace API (REST)**
   - `GET /workspace/{id}` - Retrieve workspace
   - `PUT /workspace/{id}` - Update workspace
   - HMAC authentication

2. **Structurizr for Java Libraries**
   - structurizr-core: Workspace programming
   - structurizr-client: Upload/download
   - structurizr-export: Export to other formats
   - structurizr-component: Component discovery

3. **Structurizr CLI**
   - `push`: Upload DSL to workspace
   - `pull`: Download workspace as JSON
   - `export`: Export to various formats
   - `validate`: Validate workspace

## Model Context Protocol (MCP)

MCP is a universal, vendor-neutral standard for interactions between Large Language Models and external systems.

### Core Concepts

1. **Servers**: Expose capabilities (tools, resources, prompts)
2. **Clients**: Connect with servers and integrate with LLMs
3. **Resources**: URI-addressed data sources
4. **Tools**: Executable actions with input schemas
5. **Prompts**: Reusable instruction templates

### Architecture
- **JSON-RPC 2.0** for message exchange
- **Transport options**: stdio (local), HTTP/SSE (web)
- **Request handlers** for capabilities

## Structurizr MCP Server Design

### Recommended Tools

**All 23 tools are fully implemented and tested.**

#### Workspace Management (4/4 implemented ✓)
- ✓ `create_workspace(name, description)` → workspace_id
- ✓ `get_workspace(workspace_id)` → workspace JSON
- ✓ `list_workspaces()` → workspace list
- ✓ `delete_workspace(workspace_id)`

#### Model Building (5/5 implemented ✓)
- ✓ `add_person(workspace_id, name, description, tags?)`
- ✓ `add_software_system(workspace_id, name, description, tags?)`
- ✓ `add_container(system_id, name, description, technology?, tags?)`
- ✓ `add_component(container_id, name, description, technology?, tags?)`
- ✓ `add_relationship(source_id, dest_id, description, technology?, tags?)`

#### Views (5/5 implemented ✓)
- ✓ `create_system_context_view(system_id, key, description?)`
- ✓ `create_container_view(system_id, key, description?)`
- ✓ `create_component_view(container_id, key, description?)`
- ✓ `create_dynamic_view(element_id, key, description?)`
- ✓ `apply_auto_layout(view_key, direction)`

#### Documentation (2/2 implemented ✓)
- ✓ `add_documentation_section(workspace_id, title, content)`
- ✓ `add_adr(workspace_id, id, date, title, status, content)`

#### Export/Import (4/4 implemented ✓)
- ✓ `export_to_dsl(workspace_id)` → DSL string
- ✓ `export_to_plantuml(view_key)` → PlantUML
- ✓ `export_to_mermaid(view_key)` → Mermaid
- ✓ `import_from_dsl(dsl_content)` → workspace

#### Analysis (3/3 implemented ✓)
- ✓ `analyze_dependencies(workspace_id, element_id?)`
- ✓ `find_element(workspace_id, name)`
- ✓ `validate_workspace(workspace_id)`

### Resources

**All 7 MCP resources are fully implemented and tested.**

#### Static Resources (1/1 implemented ✓)
- ✓ `structurizr://config` → Server configuration

#### Workspace Resources (4/4 implemented ✓)
- ✓ `structurizr://workspace/{workspaceId}` → Full workspace data
- ✓ `structurizr://workspace/{workspaceId}/model` → Model elements only
- ✓ `structurizr://workspace/{workspaceId}/views` → View definitions only
- ✓ `structurizr://workspace/{workspaceId}/dsl` → DSL representation

#### Element & View Resources (2/2 implemented ✓)
- ✓ `structurizr://workspace/{workspaceId}/element/{elementId}` → Element data
- ✓ `structurizr://workspace/{workspaceId}/view/{viewKey}` → View data

### Prompts

**Note**: Prompts are planned for future enhancement and are not currently implemented. The server focuses on providing comprehensive tool and resource capabilities for workspace management.

## Technology Stack

### PHP Implementation
- **PHP 8.1+** with modern features (attributes, enums, type hints, readonly properties)
- **MCP SDK**: `mcp/sdk` (dev-main branch via Composer)
- **Transport**: stdio for Claude Desktop
- **Structurizr CLI** for DSL parsing, validation, and export
- **PSR standards**: PSR-3 (logging), PSR-16 (simple cache)
- **Symfony components**: Process, Cache, Filesystem

### Composer Dependencies
```json
{
    "require": {
        "php": "^8.1",
        "mcp/sdk": "dev-main",
        "guzzlehttp/guzzle": "^7.0",
        "monolog/monolog": "^3.0",
        "psr/simple-cache": "^3.0",
        "symfony/cache": "^6.0|^7.0",
        "symfony/filesystem": "^6.0|^7.0",
        "symfony/process": "^6.0|^7.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.10",
        "friendsofphp/php-cs-fixer": "^3.0"
    }
}
```

### Project Structure
```
structurizr-mcp/
├── src/
│   ├── Tools/                         # MCP tool implementations
│   │   ├── AbstractWorkspaceTool.php  # Base class for all tools
│   │   ├── WorkspaceTools.php         # Workspace CRUD operations
│   │   ├── ModelTools.php             # Element & relationship management
│   │   ├── ViewTools.php              # View creation & auto-layout
│   │   ├── ExportTools.php            # DSL, PlantUML, Mermaid export
│   │   ├── DocumentationTools.php     # Documentation & ADR management
│   │   └── AnalysisTools.php          # Dependency analysis & validation
│   ├── Resources/                     # MCP resource implementations
│   │   ├── ConfigResource.php         # Static server configuration
│   │   ├── WorkspaceResource.php      # Workspace data resources
│   │   ├── ElementResource.php        # Element retrieval resource
│   │   └── ViewResource.php           # View retrieval resource
│   ├── Structurizr/                   # Core Structurizr integration
│   │   ├── CliWrapper.php             # CLI command execution wrapper
│   │   ├── DslBuilder.php             # DSL generation utility
│   │   ├── Workspace.php              # Workspace data model
│   │   ├── WorkspaceManager.php       # Workspace state management
│   │   ├── ProcessResult.php          # CLI process result object
│   │   └── ValidationResult.php       # Validation result object
│   ├── Exception/                     # Custom exceptions
│   │   ├── ApiAuthenticationException.php
│   │   ├── WorkspaceNotFoundException.php
│   │   ├── InvalidDslException.php
│   │   ├── CliExecutionException.php
│   │   └── StructurizrException.php
│   └── Configuration.php              # Environment configuration
├── tests/                             # PHPUnit tests (286 tests, all passing)
│   ├── Unit/
│   │   ├── Tools/
│   │   │   ├── WorkspaceToolsTest.php
│   │   │   ├── ModelToolsTest.php
│   │   │   ├── ViewToolsTest.php
│   │   │   ├── ExportToolsTest.php
│   │   │   ├── DocumentationToolsTest.php
│   │   │   └── AnalysisToolsTest.php
│   │   ├── Resources/
│   │   │   ├── ConfigResourceTest.php
│   │   │   ├── WorkspaceResourceTest.php
│   │   │   ├── ElementResourceTest.php
│   │   │   └── ViewResourceTest.php
│   │   ├── Structurizr/
│   │   │   ├── DslBuilderTest.php
│   │   │   ├── WorkspaceManagerTest.php
│   │   │   └── CliWrapperTest.php
│   │   └── ConfigurationTest.php
│   └── Integration/
│       └── WorkflowTest.php
├── examples/                          # Example workspaces
├── docs/                              # Documentation
├── cache/                             # Discovery cache
├── sessions/                          # Session storage
├── workspaces/                        # Local workspace files
├── .editorconfig                      # Editor configuration
├── .php-cs-fixer.dist.php             # Code style configuration
├── server.php                         # MCP server entry point
├── composer.json
├── phpunit.xml
└── phpstan.neon                       # PHPStan configuration
```

## PHP Implementation Examples

### Server Setup (server.php)

```php
<?php

require 'vendor/autoload.php';

use StructurizrMcp\Configuration;
use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Load configuration from environment
$config = Configuration::loadConfiguration();

// Setup logging (to STDERR by default)
$logger = new Logger('structurizr-mcp');
$logPath = $config->getLogPath();
$logLevel = $config->getLogLevel();
$logger->pushHandler(new StreamHandler($logPath, $logLevel));

// Setup cache for discovery
$cache = new Psr16Cache(
    new PhpFilesAdapter(directory: __DIR__ . '/cache')
);

// Build MCP server with auto-discovery
$server = Server::builder()
    ->setServerInfo(
        name: $config->getServerName(),
        version: $config->getServerVersion(),
        description: 'MCP server for Structurizr workspace management and C4 model creation'
    )
    ->setInstructions(
        'Use this server to create and manage Structurizr workspaces, ' .
        'add architectural elements, create views, and generate C4 diagrams. ' .
        'All 23 tools are available for comprehensive workspace management.'
    )
    ->setLogger($logger)
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['src'],
        excludeDirs: ['vendor', 'tests', 'cache', 'sessions', 'workspaces'],
        cache: $cache
    )
    ->build();

// Run with STDIO transport
$transport = new StdioTransport(logger: $logger);
$exitCode = $server->run($transport);
exit($exitCode);
```

### Tool Definition with Attributes

```php
<?php

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use StructurizrMcp\Structurizr\WorkspaceManager;

class WorkspaceTools extends AbstractWorkspaceTool
{
    public function __construct(
        WorkspaceManager $workspaceManager
    ) {
        parent::__construct($workspaceManager);
    }

    #[McpTool(
        name: 'create_workspace',
        description: 'Creates a new Structurizr workspace with the given name and description'
    )]
    public function createWorkspace(
        #[Schema(description: 'Workspace name', minLength: 1, maxLength: 100)]
        string $name,

        #[Schema(description: 'Workspace description', maxLength: 500)]
        string $description = ''
    ): array {
        try {
            $workspace = $this->workspaceManager->createWorkspace($name, $description);

            return [
                'success' => true,
                'workspaceId' => $workspace->getId(),
                'name' => $workspace->getName(),
                'description' => $workspace->getDescription(),
                'message' => "Workspace '{$name}' created successfully"
            ];
        } catch (\Exception $e) {
            throw new ToolCallException(
                "Failed to create workspace: " . $e->getMessage()
            );
        }
    }

    #[McpTool(
        name: 'add_software_system',
        description: 'Adds a software system to the workspace model'
    )]
    public function addSoftwareSystem(
        #[Schema(description: 'Workspace ID', type: 'string')]
        string $workspaceId,

        #[Schema(description: 'System name', minLength: 1, maxLength: 100)]
        string $name,

        #[Schema(description: 'System description')]
        string $description = '',

        #[Schema(description: 'Comma-separated tags')]
        string $tags = ''
    ): array {
        $workspace = $this->getWorkspace($workspaceId);

        $systemId = $this->workspaceManager->addSoftwareSystem(
            $workspace,
            $name,
            $description,
            $tags
        );

        return [
            'success' => true,
            'systemId' => $systemId,
            'name' => $name,
            'message' => "Software system '{$name}' added successfully"
        ];
    }
}
```

### Abstract Base Tool

All tool classes extend `AbstractWorkspaceTool` which provides common workspace retrieval functionality:

```php
<?php

namespace StructurizrMcp\Tools;

use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Structurizr\WorkspaceManager;

abstract class AbstractWorkspaceTool
{
    public function __construct(
        protected readonly WorkspaceManager $workspaceManager
    ) {}

    /**
     * Get workspace by ID or throw exception
     */
    protected function getWorkspace(string $workspaceId): Workspace
    {
        $workspace = $this->workspaceManager->getWorkspace($workspaceId);

        if ($workspace === null) {
            throw new WorkspaceNotFoundException(
                "Workspace with ID '{$workspaceId}' not found"
            );
        }

        return $workspace;
    }
}
```

### Claude Desktop Configuration

All configuration is handled via environment variables:

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/path/to/structurizr-mcp/server.php"],
      "env": {
        "STRUCTURIZR_API_KEY": "your-key",
        "STRUCTURIZR_API_SECRET": "your-secret",
        "STRUCTURIZR_API_URL": "https://api.structurizr.com",
        "STRUCTURIZR_WORKSPACE_ID": "12345",
        "STRUCTURIZR_CLI_PATH": "./bin/structurizr-cli.sh",
        "WORKSPACE_STORAGE_PATH": "./workspaces",
        "LOG_LEVEL": "INFO",
        "LOG_PATH": "php://stderr",
        "SERVER_NAME": "structurizr-mcp-server",
        "SERVER_VERSION": "1.0.0"
      }
    }
  }
}
```

**Required Environment Variables:**
- `STRUCTURIZR_CLI_PATH`: Path to Structurizr CLI executable
- `WORKSPACE_STORAGE_PATH`: Directory for storing workspace files

**Optional Environment Variables:**
- `STRUCTURIZR_API_KEY`: API key for Structurizr Cloud (for future cloud sync)
- `STRUCTURIZR_API_SECRET`: API secret for Structurizr Cloud
- `STRUCTURIZR_API_URL`: API URL (default: https://api.structurizr.com)
- `STRUCTURIZR_WORKSPACE_ID`: Default workspace ID for cloud operations
- `LOG_LEVEL`: Logging level (DEBUG, INFO, WARNING, ERROR)
- `LOG_PATH`: Log file path (default: php://stderr)
- `SERVER_NAME`: Server name (default: structurizr-mcp-server)
- `SERVER_VERSION`: Server version (default: 1.0.0)

## Implementation Status

### Tools Implementation

| Category | Tools | Status |
|----------|-------|--------|
| Workspace Management | 4 tools | ✅ 100% Complete |
| Model Building | 5 tools | ✅ 100% Complete |
| Views | 5 tools | ✅ 100% Complete |
| Documentation | 2 tools | ✅ 100% Complete |
| Export/Import | 4 tools | ✅ 100% Complete |
| Analysis | 3 tools | ✅ 100% Complete |
| **Total Tools** | **23 tools** | **✅ 100% Complete** |

### Resources Implementation

| Category | Resources | Status |
|----------|-----------|--------|
| Static Resources | 1 resource | ✅ 100% Complete |
| Workspace Resources | 4 resources | ✅ 100% Complete |
| Element & View Resources | 2 resources | ✅ 100% Complete |
| **Total Resources** | **7 resources** | **✅ 100% Complete** |

### Test Coverage

| Test Suite | Tests | Coverage |
|------------|-------|----------|
| Unit Tests (Tools) | 224 tests | >95% coverage |
| Unit Tests (Resources) | 31 tests | >95% coverage |
| Unit Tests (Core) | 23 tests | >95% coverage |
| Integration Tests | 8 tests | 100% passing |
| **Total** | **286 tests** | **✅ 100% passing** |

### Quality Assurance
- **PHPStan Level 8**: All code passes strict static analysis
- **Code Style**: PSR-12 compliant with PHP-CS-Fixer
- **Documentation**: Comprehensive inline documentation and examples
- **Error Handling**: Robust exception handling with custom exception classes

## Development Workflow

### Local Development
1. `composer install` - Install dependencies
2. Download Structurizr CLI to `./bin/` directory
3. Set environment variables (STRUCTURIZR_CLI_PATH, WORKSPACE_STORAGE_PATH)
4. `php server.php` - Start MCP server
5. Call tools via MCP client (Claude Desktop)
6. Validate workspace with Structurizr CLI
7. Export to desired format (DSL, PlantUML, Mermaid)

### Testing
```bash
# Run all PHPUnit tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Unit/Tools/WorkspaceToolsTest.php

# Run with coverage report
./vendor/bin/phpunit --coverage-html coverage

# Static analysis (PHPStan Level 8)
./vendor/bin/phpstan analyse src

# Code style check
./vendor/bin/php-cs-fixer fix --dry-run --diff

# Code style fix
./vendor/bin/php-cs-fixer fix
```

### Integration with Structurizr Cloud
**Note**: Cloud integration is planned for future enhancement. Current implementation focuses on local workspace management with CLI-based operations.

## Next Steps

See [TASKS.md](./TASKS.md) for the detailed implementation roadmap.

## Resources

### Structurizr
- [Structurizr Documentation](https://structurizr.com)
- [C4 Model](https://c4model.com)
- [Structurizr DSL](https://github.com/structurizr/dsl)
- [Structurizr CLI](https://github.com/structurizr/cli)

### Model Context Protocol
- [Model Context Protocol](https://modelcontextprotocol.io)
- [MCP Specification](https://spec.modelcontextprotocol.io)
- [MCP PHP SDK](https://github.com/modelcontextprotocol/php-sdk)

### PHP Development
- [PHP 8.1 Documentation](https://www.php.net/manual/en/)
- [Composer](https://getcomposer.org/)
- [PSR Standards](https://www.php-fig.org/psr/)
