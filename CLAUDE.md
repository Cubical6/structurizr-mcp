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

#### Workspace Management
- `create_workspace(name, description)` → workspace_id
- `get_workspace(workspace_id)` → workspace JSON
- `list_workspaces()` → workspace list
- `delete_workspace(workspace_id)`

#### Model Building
- `add_person(workspace_id, name, description, tags?)`
- `add_software_system(workspace_id, name, description, tags?)`
- `add_container(system_id, name, description, technology?, tags?)`
- `add_component(container_id, name, description, technology?, tags?)`
- `add_relationship(source_id, dest_id, description, technology?, tags?)`

#### Views
- `create_system_context_view(system_id, key, description?)`
- `create_container_view(system_id, key, description?)`
- `create_component_view(container_id, key, description?)`
- `create_dynamic_view(element_id, key, description?)`
- `apply_auto_layout(view_key, direction)`

#### Documentation
- `add_documentation_section(workspace_id, title, content)`
- `add_adr(workspace_id, id, date, title, status, content)`

#### Export/Import
- `export_to_dsl(workspace_id)` → DSL string
- `export_to_plantuml(view_key)` → PlantUML
- `export_to_mermaid(view_key)` → Mermaid
- `import_from_dsl(dsl_content)` → workspace

#### Analysis
- `analyze_dependencies(workspace_id, element_id?)`
- `find_element(workspace_id, name)`
- `validate_workspace(workspace_id)`

### Recommended Resources

```
workspace://{id}                              - Complete workspace JSON
workspace://{id}/model                        - Model only
workspace://{id}/views                        - Views only
element://{workspace_id}/{element_id}         - Specific element
view://{workspace_id}/{view_key}              - Specific view
dsl://{workspace_id}                          - DSL representation
```

### Recommended Prompts

- `analyze_architecture(workspace_id)` - Architecture analysis
- `review_security(workspace_id)` - Security review
- `generate_system_context(description)` - Generate context from description
- `suggest_improvements(workspace_id)` - Improvement suggestions
- `explain_c4_model()` - Explain C4 model
- `create_example_workspace(type)` - Example workspace

## Technology Stack

### PHP Implementation
- **PHP 8.1+** with modern features (attributes, enums, type hints)
- **MCP SDK**: `mcp/sdk` (via Composer)
- **Transport**: stdio for Claude Desktop, HTTP for web
- **Structurizr CLI** for DSL parsing and export
- **Guzzle HTTP client** for Structurizr Cloud/On-Premises API
- **PSR standards**: PSR-3 (logging), PSR-11 (container), PSR-16 (cache)

### Composer Dependencies
```json
{
    "require": {
        "php": "^8.1",
        "mcp/sdk": "*",
        "guzzlehttp/guzzle": "^7.0",
        "monolog/monolog": "^3.0",
        "symfony/cache": "^6.0",
        "symfony/process": "^6.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.10"
    }
}
```

### Project Structure
```
structurizr-mcp/
├── src/
│   ├── Tools/                    # Tool implementations
│   │   ├── WorkspaceTools.php    # Workspace CRUD
│   │   ├── ModelTools.php        # Element & relationship management
│   │   ├── ViewTools.php         # View creation
│   │   └── ExportTools.php       # Export functionality
│   ├── Resources/                # Resource handlers
│   │   ├── WorkspaceResource.php
│   │   └── ConfigResource.php
│   ├── Prompts/                  # Prompt templates
│   │   ├── AnalysisPrompts.php
│   │   └── GenerationPrompts.php
│   ├── Structurizr/              # Structurizr integration
│   │   ├── CliWrapper.php        # CLI command wrapper
│   │   ├── ApiClient.php         # REST API client
│   │   └── WorkspaceManager.php  # Workspace state management
│   └── Exception/                # Custom exceptions
│       └── StructurizrException.php
├── tests/                        # PHPUnit tests
│   ├── Unit/
│   └── Integration/
├── docs/                         # Documentation
├── cache/                        # Discovery cache
├── sessions/                     # Session storage
├── workspaces/                   # Local workspace files
├── server.php                    # MCP server entry point
├── composer.json
└── phpunit.xml
```

## PHP Implementation Examples

### Server Setup (server.php)

```php
<?php

require 'vendor/autoload.php';

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Mcp\Server\Session\FileSessionStore;
use Mcp\Capability\Registry\Container;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Setup logging (to STDERR!)
$logger = new Logger('structurizr-mcp');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));

// Setup dependency injection container
$container = new Container();
$container->set(LoggerInterface::class, $logger);

// Setup cache for discovery
$cache = new Psr16Cache(
    new PhpFilesAdapter(directory: __DIR__ . '/cache')
);

// Build MCP server
$server = Server::builder()
    ->setServerInfo(
        name: 'Structurizr MCP Server',
        version: '1.0.0',
        description: 'MCP server for Structurizr workspace management'
    )
    ->setInstructions(
        'Use this server to create and manage Structurizr workspaces, ' .
        'add architectural elements, and generate C4 diagrams.'
    )
    ->setContainer($container)
    ->setLogger($logger)
    ->setSession(new FileSessionStore(__DIR__ . '/sessions'))
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['src'],
        excludeDirs: ['vendor', 'tests', 'cache'],
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
use Psr\Log\LoggerInterface;

class WorkspaceTools
{
    public function __construct(
        private LoggerInterface $logger,
        private StructurizrCliWrapper $cli
    ) {}

    #[McpTool(
        name: 'create_workspace',
        description: 'Creates a new Structurizr workspace'
    )]
    public function createWorkspace(
        #[Schema(description: 'Workspace name', minLength: 1, maxLength: 100)]
        string $name,

        #[Schema(description: 'Workspace description', maxLength: 500)]
        string $description = ''
    ): array {
        $this->logger->info("Creating workspace: {$name}");

        try {
            $workspaceId = $this->cli->createWorkspace($name, $description);

            return [
                'workspaceId' => $workspaceId,
                'name' => $name,
                'description' => $description
            ];
        } catch (\Exception $e) {
            throw new ToolCallException(
                "Failed to create workspace: " . $e->getMessage()
            );
        }
    }

    #[McpTool(name: 'add_software_system')]
    public function addSoftwareSystem(
        #[Schema(type: 'integer', minimum: 1)]
        int $workspaceId,

        #[Schema(minLength: 1, maxLength: 100)]
        string $name,

        string $description = '',

        #[Schema(enum: ['Internal', 'External'])]
        string $location = 'Internal'
    ): array {
        // Implementation
        return ['systemId' => 'sys-001', 'name' => $name];
    }
}
```

### Resource Definition

```php
<?php

namespace StructurizrMcp\Resources;

use Mcp\Capability\Attribute\McpResourceTemplate;

class WorkspaceResource
{
    #[McpResourceTemplate(
        uriTemplate: 'structurizr://workspace/{workspaceId}',
        name: 'workspace_details',
        description: 'Get workspace by ID',
        mimeType: 'application/json'
    )]
    public function getWorkspace(string $workspaceId): array {
        return [
            'id' => $workspaceId,
            'name' => 'Example Workspace',
            'model' => [...],
            'views' => [...]
        ];
    }
}
```

### Claude Desktop Configuration

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/path/to/structurizr-mcp/server.php"],
      "env": {
        "STRUCTURIZR_API_KEY": "your-key",
        "STRUCTURIZR_API_URL": "https://api.structurizr.com"
      }
    }
  }
}
```

## Development Workflow

### Local Development
1. `composer install` - Install dependencies
2. Create/edit DSL file in `workspaces/`
3. `php server.php` - Start MCP server
4. Call tools via MCP client (Claude Desktop)
5. Validate workspace with Structurizr CLI
6. Export to desired format

### Testing
```bash
# Run PHPUnit tests
./vendor/bin/phpunit

# Static analysis
./vendor/bin/phpstan analyse src

# Test server manually
php server.php < test_request.json
```

### Integration with Structurizr Cloud
1. Configure API credentials via environment variables
2. Workspace push/pull via API client
3. Sync local and cloud workspaces

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
