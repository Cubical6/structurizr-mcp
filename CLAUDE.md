# Structurizr MCP Server

## Project Overview

Dit project implementeert een Model Context Protocol (MCP) server voor Structurizr, een tool voor het maken van software architectuurdiagrammen als code op basis van het C4 model.

## Wat is Structurizr?

Structurizr is een toolset voor het creëren van software architectuurdiagrammen via code in plaats van handmatig tekenen. Het lost het fundamentele probleem op van het gesynchroniseerd en consistent houden van architectuurdocumentatie.

### Kernwaarde
- **Single source of truth**: Eén definitie, meerdere gegenereerde views
- **Versiecontrole**: Architectuur als code in git
- **Automatische generatie**: Meerdere diagrammen uit één model
- **Consistentie**: Geen divergerende of tegenstrijdige documentatie

## Het C4 Model

Het C4 model biedt een gestructureerde methode voor het visualiseren van software architectuur met vier hiërarchische abstractieniveaus:

### 1. System Context (Niveau 1)
- Hoogste abstractieniveau
- Toont het systeem in context met gebruikers en externe systemen
- **Doel**: Big picture overzicht

### 2. Container (Niveau 2)
- Belangrijkste bouwstenen binnen een systeem
- Containers = applicaties, databases, microservices, etc.
- **Doel**: High-level technologie beslissingen en deployment architectuur

### 3. Component (Niveau 3)
- Logische groeperingen binnen containers
- Components = samenhangende functionaliteit
- **Doel**: Gedetailleerd ontwerp van een enkele container

### 4. Code (Niveau 4)
- Laagste niveau - daadwerkelijke code structuur
- **Doel**: Implementatie details (vaak gegenereerd uit code)

### Aanvullende Diagram Types
- **System Landscape**: Meerdere systemen en hun interacties
- **Dynamic**: Sequenties en runtime gedrag
- **Deployment**: Infrastructuur en deployment topologie

## Structurizr DSL

De Structurizr Domain Specific Language (DSL) is een tekstgebaseerd formaat voor het definiëren van architectuurmodellen.

### Basis Structuur

```dsl
workspace "Naam" "Beschrijving" {
    model {
        # Definieer elementen en relaties
        user = person "Gebruiker" "Beschrijving"
        system = softwareSystem "Systeem" "Beschrijving" {
            webapp = container "Web App" "Beschrijving" "Technology"
        }

        user -> system "Gebruikt"
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

### Belangrijkste Elementen
- **person**: Gebruikers van het systeem
- **softwareSystem**: Software systemen
- **container**: Deploybare/uitvoerbare eenheden
- **component**: Logische groeperingen binnen containers
- **Relaties**: `element1 -> element2 "Beschrijving" "Technology"`

## Structurizr Interfaces

### File Formats
1. **DSL (.dsl)**: Primair formaat voor authoring
2. **JSON (.json)**: Interne workspace definitie
3. **Export formats**: PlantUML, Mermaid, DOT, etc.

### APIs
1. **Workspace API (REST)**
   - `GET /workspace/{id}` - Ophalen workspace
   - `PUT /workspace/{id}` - Update workspace
   - HMAC authenticatie

2. **Structurizr voor Java Libraries**
   - structurizr-core: Workspace programmeren
   - structurizr-client: Upload/download
   - structurizr-export: Export naar andere formaten
   - structurizr-component: Component discovery

3. **Structurizr CLI**
   - `push`: Upload DSL naar workspace
   - `pull`: Download workspace als JSON
   - `export`: Export naar verschillende formaten
   - `validate`: Valideer workspace

## Model Context Protocol (MCP)

MCP is een universele, vendor-neutrale standaard voor interacties tussen Large Language Models en externe systemen.

### Kern Concepten

1. **Servers**: Expose capabilities (tools, resources, prompts)
2. **Clients**: Verbinden met servers en integreren met LLMs
3. **Resources**: URI-geadresseerde databronnen
4. **Tools**: Uitvoerbare acties met input schemas
5. **Prompts**: Herbruikbare instructie templates

### Architectuur
- **JSON-RPC 2.0** voor message exchange
- **Transport opties**: stdio (lokaal), HTTP/SSE (web)
- **Request handlers** voor capabilities

## Structurizr MCP Server Design

### Aanbevolen Tools

#### Workspace Management
- `create_workspace(name, description)` → workspace_id
- `get_workspace(workspace_id)` → workspace JSON
- `list_workspaces()` → workspace lijst
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

### Aanbevolen Resources

```
workspace://{id}                              - Volledige workspace JSON
workspace://{id}/model                        - Alleen model
workspace://{id}/views                        - Alleen views
element://{workspace_id}/{element_id}         - Specifiek element
view://{workspace_id}/{view_key}              - Specifieke view
dsl://{workspace_id}                          - DSL representatie
```

### Aanbevolen Prompts

- `analyze_architecture(workspace_id)` - Architectuur analyse
- `review_security(workspace_id)` - Security review
- `generate_system_context(description)` - Genereer context uit beschrijving
- `suggest_improvements(workspace_id)` - Verbeter suggesties
- `explain_c4_model()` - Leg C4 model uit
- `create_example_workspace(type)` - Voorbeeld workspace

## Technologie Stack

### Implementatie in PHP
- **PHP 8.1+** met moderne features (attributes, enums, type hints)
- **MCP SDK**: `mcp/sdk` (via Composer)
- **Transport**: stdio voor Claude Desktop, HTTP voor web
- **Structurizr CLI** voor DSL parsing en export
- **Guzzle HTTP client** voor Structurizr Cloud/On-Premises API
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

### Project Structuur
```
structurizr-mcp/
├── src/
│   ├── Tools/                    # Tool implementations
│   │   ├── WorkspaceTools.php    # Workspace CRUD
│   │   ├── ModelTools.php        # Element & relationship management
│   │   ├── ViewTools.php         # View creation
│   │   └── ExportTools.php       # Export functionaliteit
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

## PHP Implementation Voorbeelden

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

// Setup logging (naar STDERR!)
$logger = new Logger('structurizr-mcp');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));

// Setup dependency injection container
$container = new Container();
$container->set(LoggerInterface::class, $logger);

// Setup cache voor discovery
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

// Run met STDIO transport
$transport = new StdioTransport(logger: $logger);
$exitCode = $server->run($transport);
exit($exitCode);
```

### Tool Definition met Attributes

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

### Claude Desktop Configuratie

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

### Lokale Development
1. `composer install` - Installeer dependencies
2. DSL file creëren/bewerken in `workspaces/`
3. `php server.php` - Start MCP server
4. Tools aanroepen via MCP client (Claude Desktop)
5. Workspace valideren met Structurizr CLI
6. Export naar gewenst formaat

### Testing
```bash
# Run PHPUnit tests
./vendor/bin/phpunit

# Static analysis
./vendor/bin/phpstan analyse src

# Test server manually
php server.php < test_request.json
```

### Integratie met Structurizr Cloud
1. Configureer API credentials via environment variables
2. Workspace push/pull via API client
3. Sync lokale en cloud workspaces

## Next Steps

Zie [TASKS.md](./TASKS.md) voor de gedetailleerde implementatie roadmap.

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
