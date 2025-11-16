# Resource Reference

## Introduction

This reference provides complete documentation for all 7 MCP resources available in the Structurizr MCP server. Each resource section includes URI patterns, response formats, use cases, and practical examples.

## Resource Categories

- [Configuration Resource](#configuration-resource) - Server configuration
- [Workspace Resources](#workspace-resources) - Workspace data access
- [Element Resource](#element-resource) - Individual element access
- [View Resource](#view-resource) - Individual view access

---

## Configuration Resource

### `structurizr://config`

Access server configuration and metadata.

#### Resource Details

| Property | Value |
|----------|-------|
| **URI** | `structurizr://config` |
| **Name** | `server_config` |
| **Type** | Static resource (no parameters) |
| **MIME Type** | `application/json` |
| **Class** | `StructurizrMcp\Resources\ConfigResource` |

#### Description

The configuration resource provides read-only access to server settings, capabilities, and current state. This is useful for diagnostics, understanding server capabilities, and checking workspace availability.

#### Response Format

```json
{
  "server": {
    "name": "structurizr-mcp-server",
    "version": "1.0.0"
  },
  "structurizr": {
    "cliPath": "./bin/structurizr-cli.sh",
    "apiUrl": "https://api.structurizr.com"
  },
  "storage": {
    "workspacePath": "./workspaces",
    "workspaceCount": 5
  },
  "logging": {
    "level": "INFO",
    "path": "php://stderr"
  }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `server.name` | string | MCP server name |
| `server.version` | string | Server version |
| `structurizr.cliPath` | string | Path to Structurizr CLI executable |
| `structurizr.apiUrl` | string | Structurizr API endpoint (if configured) |
| `storage.workspacePath` | string | Directory storing workspace files |
| `storage.workspaceCount` | integer | Number of workspaces available |
| `logging.level` | string | Current log level (DEBUG, INFO, WARNING, ERROR) |
| `logging.path` | string | Log output destination |

#### When to Use

- **Server diagnostics** - Check server health and configuration
- **Capability discovery** - Understand server capabilities before operations
- **Troubleshooting** - Verify paths and settings during debugging
- **Monitoring** - Track workspace count and server state
- **Documentation** - Generate environment-specific setup guides

#### Example Usage

##### Basic Access

```php
// Access configuration resource
$uri = 'structurizr://config';
// Returns full server configuration
```

##### Prompt Integration

```php
#[McpPrompt(name: 'server_status')]
public function serverStatus(): array
{
    $config = // ... fetch config resource

    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'resource',
                        'resource' => [
                            'uri' => 'structurizr://config',
                            'mimeType' => 'application/json',
                            'text' => json_encode($config)
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Summarize the server status and configuration.'
                    ]
                ]
            ]
        ]
    ];
}
```

##### AI Assistant Usage

```
User: Check the server configuration
AI: [Accesses structurizr://config]
    The server is running version 1.0.0 with 5 workspaces available.
    Storage path: ./workspaces
    CLI path: ./bin/structurizr-cli.sh
    Log level: INFO
```

#### Implementation

```php
#[McpResource(
    uri: 'structurizr://config',
    name: 'server_config',
    description: 'Server configuration and metadata',
    mimeType: 'application/json'
)]
public function getConfig(): array
{
    $workspaces = $this->workspaceManager->list();

    return [
        'server' => [
            'name' => $this->config->getServerName(),
            'version' => $this->config->getServerVersion(),
        ],
        'structurizr' => [
            'cliPath' => $this->config->getStructurizrCliPath(),
            'apiUrl' => $this->config->getStructurizrApiUrl(),
        ],
        'storage' => [
            'workspacePath' => $this->config->getWorkspacePath(),
            'workspaceCount' => count($workspaces),
        ],
        'logging' => [
            'level' => $this->config->getLogLevel(),
            'path' => $this->config->getLogPath(),
        ],
    ];
}
```

---

## Workspace Resources

### `structurizr://workspace/{workspaceId}`

Access complete workspace data including model, views, documentation, and DSL.

#### Resource Details

| Property | Value |
|----------|-------|
| **URI Template** | `structurizr://workspace/{workspaceId}` |
| **Name** | `workspace_full` |
| **Parameters** | `workspaceId` - Workspace identifier |
| **MIME Type** | `application/json` |
| **Class** | `StructurizrMcp\Resources\WorkspaceResource` |

#### Description

The full workspace resource provides comprehensive access to all workspace data. This is the most complete view, containing model elements, view configurations, documentation sections, ADRs, and DSL representation.

#### Response Format

```json
{
  "id": "abc123",
  "name": "E-commerce Platform",
  "description": "Online shopping system architecture",
  "model": {
    "people": [
      {
        "id": "customer",
        "name": "Customer",
        "description": "Online shopper",
        "tags": "Person",
        "relationships": [...]
      }
    ],
    "softwareSystems": [
      {
        "id": "ecommerce-system",
        "name": "E-commerce System",
        "description": "Main shopping platform",
        "tags": "Software System",
        "containers": [...]
      }
    ]
  },
  "views": {
    "systemContextViews": [...],
    "containerViews": [...],
    "componentViews": [...],
    "styles": {...}
  },
  "documentation": {
    "sections": [...],
    "decisions": [...]
  },
  "dsl": "workspace \"E-commerce Platform\" {...}"
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | string | Unique workspace identifier |
| `name` | string | Workspace display name |
| `description` | string | Workspace description |
| `model` | object | Complete model with all elements and relationships |
| `model.people` | array | Person elements (users, actors) |
| `model.softwareSystems` | array | Software system elements |
| `views` | object | All view definitions and styles |
| `views.systemContextViews` | array | System context diagrams |
| `views.containerViews` | array | Container diagrams |
| `views.componentViews` | array | Component diagrams |
| `views.dynamicViews` | array | Dynamic/sequence diagrams |
| `views.styles` | object | Visual styling configuration |
| `documentation` | object | Documentation sections and ADRs |
| `dsl` | string | DSL source code representation |

#### When to Use

- **Comprehensive analysis** - Review entire architecture
- **Full export** - Export all workspace data
- **Cross-cutting reviews** - Analyze patterns across model and views
- **Documentation generation** - Generate complete documentation
- **Backup/restore** - Create complete workspace snapshots

#### Example Usage

##### Comprehensive Analysis

```php
// Access full workspace
$uri = "structurizr://workspace/abc123";
// AI receives complete workspace for holistic analysis
```

##### Architecture Review Prompt

```php
#[McpPrompt(name: 'analyze_architecture')]
public function analyzeArchitecture(string $workspaceId): array
{
    $workspace = $this->manager->load($workspaceId);

    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'resource',
                        'resource' => [
                            'uri' => "structurizr://workspace/{$workspaceId}",
                            'mimeType' => 'application/json',
                            'text' => json_encode($workspace->toArray())
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Analyze this architecture and provide insights on:\n' .
                                  '1. Architectural patterns used\n' .
                                  '2. Complexity assessment\n' .
                                  '3. Key dependencies\n' .
                                  '4. Potential risks\n' .
                                  '5. Suggested improvements'
                    ]
                ]
            ]
        ]
    ];
}
```

##### AI Assistant Usage

```
User: Analyze workspace abc123
AI: [Accesses structurizr://workspace/abc123]
    I've analyzed the E-commerce Platform architecture:

    1. Patterns: Microservices with event-driven communication
    2. Complexity: Moderate - 5 systems, 12 containers
    3. Dependencies: Heavy reliance on shared database
    4. Risks: Single point of failure in payment gateway
    5. Improvements: Consider CQRS for order processing
```

#### Error Handling

| Exception | Condition | Response |
|-----------|-----------|----------|
| `ResourceNotFoundException` | Workspace ID doesn't exist | 404 - Workspace not found |
| `ResourceReadException` | File read or parse error | 500 - Failed to load workspace |

---

### `structurizr://workspace/{workspaceId}/model`

Access only the model portion of a workspace (elements and relationships).

#### Resource Details

| Property | Value |
|----------|-------|
| **URI Template** | `structurizr://workspace/{workspaceId}/model` |
| **Name** | `workspace_model` |
| **Parameters** | `workspaceId` - Workspace identifier |
| **MIME Type** | `application/json` |
| **Class** | `StructurizrMcp\Resources\WorkspaceResource` |

#### Description

The model resource provides focused access to architectural elements and their relationships, excluding view configurations and documentation. Use this when you need to analyze structure without visualization concerns.

#### Response Format

```json
{
  "workspaceId": "abc123",
  "name": "E-commerce Platform",
  "model": {
    "people": [
      {
        "id": "customer",
        "name": "Customer",
        "description": "Online shopper",
        "tags": "Person",
        "relationships": [
          {
            "id": "rel-1",
            "sourceId": "customer",
            "destinationId": "ecommerce-system",
            "description": "Places orders using",
            "tags": ""
          }
        ]
      }
    ],
    "softwareSystems": [
      {
        "id": "ecommerce-system",
        "name": "E-commerce System",
        "description": "Main shopping platform",
        "tags": "Software System",
        "containers": [
          {
            "id": "web-app",
            "name": "Web Application",
            "description": "Customer-facing web interface",
            "technology": "React",
            "tags": "Container,Web",
            "components": [...]
          }
        ]
      }
    ]
  }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | string | Workspace identifier |
| `name` | string | Workspace name |
| `model.people` | array | All person elements with relationships |
| `model.softwareSystems` | array | All software systems with containers and components |

#### When to Use

- **Structural analysis** - Examine element relationships
- **Dependency mapping** - Track dependencies between elements
- **Architectural validation** - Verify model completeness
- **Element discovery** - Find elements by type or properties
- **Relationship auditing** - Review all connections

#### Example Usage

##### Dependency Analysis

```php
// Access model for dependency analysis
$uri = "structurizr://workspace/abc123/model";
// AI analyzes element relationships and dependencies
```

##### Security Review Prompt

```php
#[McpPrompt(name: 'review_security')]
public function reviewSecurity(string $workspaceId): array
{
    $workspace = $this->manager->load($workspaceId);

    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'resource',
                        'resource' => [
                            'uri' => "structurizr://workspace/{$workspaceId}/model",
                            'mimeType' => 'application/json',
                            'text' => json_encode([
                                'workspaceId' => $workspace->id,
                                'name' => $workspace->name,
                                'model' => $workspace->model
                            ])
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Review this architecture for security concerns:\n' .
                                  '1. Authentication boundaries\n' .
                                  '2. Data flow security\n' .
                                  '3. External system trust\n' .
                                  '4. Sensitive data handling'
                    ]
                ]
            ]
        ]
    ];
}
```

##### AI Assistant Usage

```
User: What are the dependencies of the API system?
AI: [Accesses structurizr://workspace/abc123/model]
    The API system has the following dependencies:
    - Database (PostgreSQL) - Stores customer data
    - Cache (Redis) - Session management
    - Payment Gateway (External) - Payment processing
    - Email Service (External) - Notifications
```

---

### `structurizr://workspace/{workspaceId}/views`

Access only the view definitions from a workspace (diagrams and styles).

#### Resource Details

| Property | Value |
|----------|-------|
| **URI Template** | `structurizr://workspace/{workspaceId}/views` |
| **Name** | `workspace_views` |
| **Parameters** | `workspaceId` - Workspace identifier |
| **MIME Type** | `application/json` |
| **Class** | `StructurizrMcp\Resources\WorkspaceResource` |

#### Description

The views resource provides access to diagram configurations, element visibility settings, layout information, and styling rules without including the underlying model data.

#### Response Format

```json
{
  "workspaceId": "abc123",
  "name": "E-commerce Platform",
  "views": {
    "systemContextViews": [
      {
        "key": "SystemContext",
        "softwareSystemId": "ecommerce-system",
        "description": "System context for E-commerce Platform",
        "elements": [
          {"id": "customer", "x": 100, "y": 100},
          {"id": "ecommerce-system", "x": 500, "y": 100}
        ],
        "relationships": [
          {"id": "rel-1"}
        ],
        "automaticLayout": {
          "rankDirection": "LeftRight",
          "rankSeparation": 300,
          "nodeSeparation": 300
        }
      }
    ],
    "containerViews": [...],
    "componentViews": [...],
    "styles": {
      "elements": [
        {
          "tag": "Person",
          "background": "#08427b",
          "color": "#ffffff",
          "shape": "Person"
        }
      ]
    }
  }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | string | Workspace identifier |
| `name` | string | Workspace name |
| `views.systemContextViews` | array | System context diagram configurations |
| `views.containerViews` | array | Container diagram configurations |
| `views.componentViews` | array | Component diagram configurations |
| `views.dynamicViews` | array | Dynamic/sequence diagram configurations |
| `views.deploymentViews` | array | Deployment diagram configurations |
| `views.styles` | object | Visual styling rules |

#### When to Use

- **Visualization review** - Check diagram configurations
- **Layout analysis** - Review auto-layout settings
- **Style auditing** - Verify consistent styling
- **View documentation** - Generate view documentation
- **Diagram validation** - Ensure all elements are included

#### Example Usage

##### View Configuration Review

```php
// Access views for configuration review
$uri = "structurizr://workspace/abc123/views";
// AI reviews diagram configurations and layout
```

##### AI Assistant Usage

```
User: What views are defined for workspace abc123?
AI: [Accesses structurizr://workspace/abc123/views]
    The workspace has the following views:

    1. SystemContext - System context with left-right layout
    2. Containers - Container view with top-bottom layout
    3. WebApp-Components - Component view for web application
    4. OrderFlow - Dynamic view showing order processing

    All views use consistent color coding:
    - People: Blue (#08427b)
    - Systems: Dark blue (#1168bd)
    - Containers: Light blue (#438dd5)
```

---

### `structurizr://workspace/{workspaceId}/dsl`

Access the DSL (Domain Specific Language) representation of a workspace.

#### Resource Details

| Property | Value |
|----------|-------|
| **URI Template** | `structurizr://workspace/{workspaceId}/dsl` |
| **Name** | `workspace_dsl` |
| **Parameters** | `workspaceId` - Workspace identifier |
| **MIME Type** | `text/plain` |
| **Class** | `StructurizrMcp\Resources\WorkspaceResource` |

#### Description

The DSL resource provides the workspace definition in Structurizr DSL format. If the workspace was originally created from DSL, the original source is returned. Otherwise, a DSL representation is generated from the workspace model.

#### Response Format

```dsl
workspace "E-commerce Platform" "Online shopping system architecture" {

    model {
        customer = person "Customer" "Online shopper"

        ecommerceSystem = softwareSystem "E-commerce System" "Main shopping platform" {
            webApp = container "Web Application" "Customer-facing interface" "React" {
                productCatalog = component "Product Catalog" "Displays products"
                shoppingCart = component "Shopping Cart" "Manages cart"
            }

            apiGateway = container "API Gateway" "Backend API" "Node.js"
            database = container "Database" "Data storage" "PostgreSQL"
        }

        customer -> ecommerceSystem "Places orders using"
        webApp -> apiGateway "Makes API calls to" "HTTPS/JSON"
        apiGateway -> database "Reads from and writes to" "JDBC"
    }

    views {
        systemContext ecommerceSystem "SystemContext" {
            include *
            autoLayout lr
        }

        container ecommerceSystem "Containers" {
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

#### When to Use

- **Code review** - Review DSL source code
- **DSL generation** - Generate DSL from existing workspaces
- **Version control** - Track DSL changes over time
- **Learning** - Understand DSL syntax and structure
- **Migration** - Export for use with Structurizr CLI

#### Example Usage

##### DSL Review

```php
// Access DSL for code review
$uri = "structurizr://workspace/abc123/dsl";
// Returns DSL source code as text
```

##### AI Assistant Usage

```
User: Show me the DSL for workspace abc123
AI: [Accesses structurizr://workspace/abc123/dsl]
    Here's the Structurizr DSL for the E-commerce Platform:

    [Shows formatted DSL code]

    Key features:
    - Hierarchical structure (system → containers → components)
    - Clear relationship definitions
    - View configurations with auto-layout
    - Consistent styling rules
```

##### DSL Learning Prompt

```php
#[McpPrompt(name: 'explain_dsl')]
public function explainDsl(string $workspaceId): array
{
    $workspace = $this->manager->load($workspaceId);

    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'resource',
                        'resource' => [
                            'uri' => "structurizr://workspace/{$workspaceId}/dsl",
                            'mimeType' => 'text/plain',
                            'text' => $workspace->dsl
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Explain this Structurizr DSL code line by line, ' .
                                  'highlighting key concepts and patterns.'
                    ]
                ]
            ]
        ]
    ];
}
```

---

## Element Resource

### `structurizr://workspace/{workspaceId}/element/{elementId}`

Access a specific architectural element from the workspace model.

#### Resource Details

| Property | Value |
|----------|-------|
| **URI Template** | `structurizr://workspace/{workspaceId}/element/{elementId}` |
| **Name** | `workspace_element` |
| **Parameters** | `workspaceId` - Workspace identifier<br>`elementId` - Element identifier |
| **MIME Type** | `application/json` |
| **Class** | `StructurizrMcp\Resources\ElementResource` |

#### Description

The element resource provides focused access to a single architectural element (person, software system, container, or component) including its properties, tags, and relationships.

#### Response Format

```json
{
  "workspaceId": "abc123",
  "element": {
    "id": "web-app",
    "name": "Web Application",
    "description": "Customer-facing web interface",
    "technology": "React",
    "tags": "Container,Web",
    "url": "https://shop.example.com",
    "properties": {
      "Team": "Frontend",
      "Repository": "github.com/example/web-app"
    },
    "relationships": [
      {
        "id": "rel-5",
        "sourceId": "web-app",
        "destinationId": "api-gateway",
        "description": "Makes API calls to",
        "technology": "HTTPS/JSON",
        "tags": ""
      },
      {
        "id": "rel-6",
        "sourceId": "web-app",
        "destinationId": "cdn",
        "description": "Serves static assets from",
        "technology": "HTTPS",
        "tags": ""
      }
    ],
    "components": [
      {
        "id": "product-catalog",
        "name": "Product Catalog",
        "description": "Displays products",
        "technology": "React Components"
      }
    ]
  }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | string | Workspace identifier |
| `element.id` | string | Unique element identifier |
| `element.name` | string | Element display name |
| `element.description` | string | Element description |
| `element.technology` | string | Technology/implementation (for containers/components) |
| `element.tags` | string | Comma-separated tags |
| `element.url` | string | External URL (optional) |
| `element.properties` | object | Custom key-value properties |
| `element.relationships` | array | All relationships from this element |
| `element.containers` | array | Child containers (for systems) |
| `element.components` | array | Child components (for containers) |

#### When to Use

- **Element inspection** - Review specific element details
- **Dependency analysis** - Examine element relationships
- **Property validation** - Check element configuration
- **Impact analysis** - Understand element connections
- **Documentation** - Generate element-specific docs

#### Example Usage

##### Element Inspection

```php
// Access specific element
$uri = "structurizr://workspace/abc123/element/web-app";
// Returns complete element data
```

##### Dependency Analysis

```php
#[McpPrompt(name: 'analyze_element_dependencies')]
public function analyzeElementDeps(string $workspaceId, string $elementId): array
{
    $workspace = $this->manager->load($workspaceId);
    $element = $this->findElement($workspace, $elementId);

    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'resource',
                        'resource' => [
                            'uri' => "structurizr://workspace/{$workspaceId}/element/{$elementId}",
                            'mimeType' => 'application/json',
                            'text' => json_encode([
                                'workspaceId' => $workspaceId,
                                'element' => $element
                            ])
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Analyze the dependencies of this element:\n' .
                                  '1. List all direct dependencies\n' .
                                  '2. Identify critical dependencies\n' .
                                  '3. Suggest redundancy improvements'
                    ]
                ]
            ]
        ]
    ];
}
```

##### AI Assistant Usage

```
User: Show me the dependencies of the web-app container
AI: [Accesses structurizr://workspace/abc123/element/web-app]
    The Web Application has these dependencies:

    Critical:
    - API Gateway (HTTPS/JSON) - Core functionality
    - Authentication Service (OAuth 2.0) - User login

    Supporting:
    - CDN (HTTPS) - Static assets
    - Analytics Service (JavaScript) - Tracking

    The API Gateway is a single point of failure. Consider:
    1. Adding API Gateway redundancy
    2. Implementing circuit breakers
    3. Adding fallback mechanisms
```

#### Error Handling

| Exception | Condition | Response |
|-----------|-----------|----------|
| `ResourceNotFoundException` | Workspace or element ID doesn't exist | 404 - Element not found |
| `ResourceReadException` | Error loading workspace | 500 - Failed to retrieve element |

---

## View Resource

### `structurizr://workspace/{workspaceId}/view/{viewKey}`

Access a specific view (diagram) from the workspace.

#### Resource Details

| Property | Value |
|----------|-------|
| **URI Template** | `structurizr://workspace/{workspaceId}/view/{viewKey}` |
| **Name** | `workspace_view` |
| **Parameters** | `workspaceId` - Workspace identifier<br>`viewKey` - View key/identifier |
| **MIME Type** | `application/json` |
| **Class** | `StructurizrMcp\Resources\ViewResource` |

#### Description

The view resource provides focused access to a single view configuration, including which elements and relationships are included, layout settings, and view-specific properties.

#### Response Format

```json
{
  "workspaceId": "abc123",
  "view": {
    "type": "containerViews",
    "key": "Containers",
    "softwareSystemId": "ecommerce-system",
    "description": "Container diagram for E-commerce System",
    "elements": [
      {"id": "web-app", "x": 200, "y": 100},
      {"id": "api-gateway", "x": 600, "y": 100},
      {"id": "database", "x": 600, "y": 400},
      {"id": "cache", "x": 1000, "y": 100}
    ],
    "relationships": [
      {"id": "rel-5", "vertices": [], "routing": "Direct"},
      {"id": "rel-6", "vertices": [], "routing": "Direct"}
    ],
    "automaticLayout": {
      "rankDirection": "TopBottom",
      "rankSeparation": 300,
      "nodeSeparation": 300,
      "edgeSeparation": 10,
      "vertices": false
    },
    "dimensions": {
      "width": 1400,
      "height": 800
    },
    "externalSoftwareSystemBoundariesVisible": true
  }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | string | Workspace identifier |
| `view.type` | string | View type (systemContextViews, containerViews, etc.) |
| `view.key` | string | Unique view identifier |
| `view.softwareSystemId` | string | Related system ID (for context/container views) |
| `view.description` | string | View description |
| `view.elements` | array | Included elements with positions |
| `view.relationships` | array | Included relationships with routing |
| `view.automaticLayout` | object | Auto-layout configuration |
| `view.dimensions` | object | Canvas dimensions |
| `view.externalSoftwareSystemBoundariesVisible` | boolean | Show system boundaries |

#### When to Use

- **View inspection** - Review specific diagram configuration
- **Layout analysis** - Check element positioning and auto-layout
- **Completeness check** - Verify all relevant elements are included
- **Export preparation** - Understand view before exporting
- **Documentation** - Generate view-specific documentation

#### Example Usage

##### View Configuration Review

```php
// Access specific view
$uri = "structurizr://workspace/abc123/view/Containers";
// Returns complete view configuration
```

##### Layout Analysis Prompt

```php
#[McpPrompt(name: 'review_view_layout')]
public function reviewViewLayout(string $workspaceId, string $viewKey): array
{
    $workspace = $this->manager->load($workspaceId);
    $view = $this->findView($workspace, $viewKey);

    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'resource',
                        'resource' => [
                            'uri' => "structurizr://workspace/{$workspaceId}/view/{$viewKey}",
                            'mimeType' => 'application/json',
                            'text' => json_encode([
                                'workspaceId' => $workspaceId,
                                'view' => $view
                            ])
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Review this view configuration:\n' .
                                  '1. Assess layout effectiveness\n' .
                                  '2. Check element inclusion\n' .
                                  '3. Suggest improvements'
                    ]
                ]
            ]
        ]
    ];
}
```

##### AI Assistant Usage

```
User: Review the Containers view layout
AI: [Accesses structurizr://workspace/abc123/view/Containers]
    Container View Analysis:

    Configuration:
    - Layout: Top-bottom auto-layout
    - Rank separation: 300px
    - 4 containers included

    Strengths:
    - Clear hierarchical flow
    - Good spacing between elements
    - Logical grouping

    Suggestions:
    - Consider left-right layout for wider screens
    - Add deployment nodes for context
    - Group related containers with boundaries
```

#### View Types

The `type` field indicates which view collection the view belongs to:

| Type | Description | Typical Use |
|------|-------------|-------------|
| `systemLandscapeViews` | Multiple systems and users | Enterprise context |
| `systemContextViews` | Single system with users and external systems | System boundaries |
| `containerViews` | Containers within a system | Application architecture |
| `componentViews` | Components within a container | Detailed design |
| `dynamicViews` | Runtime behavior/sequences | Process flows |
| `deploymentViews` | Infrastructure topology | Deployment architecture |

#### Error Handling

| Exception | Condition | Response |
|-----------|-----------|----------|
| `ResourceNotFoundException` | Workspace or view key doesn't exist | 404 - View not found |
| `ResourceReadException` | Error loading workspace | 500 - Failed to retrieve view |

---

## Common Patterns

### Pattern 1: Full Workspace Analysis

```php
// Use full workspace for comprehensive review
$workspace = "structurizr://workspace/{$workspaceId}";
// Embedded in analyze_architecture prompt
```

**When**: Architecture reviews, pattern analysis, documentation generation

### Pattern 2: Model-Only Security Review

```php
// Use model resource for security analysis
$model = "structurizr://workspace/{$workspaceId}/model";
// Embedded in review_security prompt
```

**When**: Security reviews, dependency analysis, trust boundary identification

### Pattern 3: Element Dependency Check

```php
// Use element resource for specific analysis
$element = "structurizr://workspace/{$workspaceId}/element/{$elementId}";
// Check dependencies, relationships, properties
```

**When**: Impact analysis, change planning, documentation

### Pattern 4: View Layout Review

```php
// Use view resource for layout analysis
$view = "structurizr://workspace/{$workspaceId}/view/{$viewKey}";
// Review configuration, positioning, inclusion
```

**When**: Diagram quality checks, export preparation, layout optimization

### Pattern 5: DSL Code Review

```php
// Use DSL resource for source review
$dsl = "structurizr://workspace/{$workspaceId}/dsl";
// Review syntax, structure, best practices
```

**When**: Code reviews, learning, migration, version control

---

## Resource Integration

### With Tools

Resources provide read access while tools provide write access:

```
Read Operations (Resources)
├── structurizr://config → Server configuration
├── structurizr://workspace/{id} → Workspace data
└── structurizr://workspace/{id}/element/{elementId} → Element details

Write Operations (Tools)
├── create_workspace(name, desc) → Create new workspace
├── add_software_system(wsId, name) → Add element
└── add_relationship(src, dest) → Add relationship
```

### With Prompts

Resources are embedded in prompts to provide context:

```php
// Prompt embeds resource for AI context
#[McpPrompt(name: 'analyze_architecture')]
public function analyze(string $workspaceId): array
{
    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'resource', // Embedded resource
                        'resource' => [
                            'uri' => "structurizr://workspace/{$workspaceId}",
                            'text' => json_encode($workspaceData)
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Analyze this architecture...'
                    ]
                ]
            ]
        ]
    ];
}
```

---

## Error Handling

### Exception Types

| Exception | HTTP Status | When Thrown |
|-----------|-------------|-------------|
| `ResourceNotFoundException` | 404 | Workspace, element, or view not found |
| `ResourceReadException` | 500 | File read error, parse error, or other failure |

### Error Response Format

```json
{
  "error": {
    "code": "ResourceNotFoundException",
    "message": "Workspace 'invalid-id' not found"
  }
}
```

### Handling Missing Resources

```php
try {
    $workspace = $this->manager->load($workspaceId);
} catch (WorkspaceNotFoundException $e) {
    throw new ResourceNotFoundException("Workspace not found: {$workspaceId}");
}
```

### Handling Read Errors

```php
try {
    $workspace = $this->manager->load($workspaceId);
    return $workspace->toArray();
} catch (\Exception $e) {
    throw new ResourceReadException("Failed to retrieve workspace: " . $e->getMessage());
}
```

---

## Performance Considerations

### Caching Strategy

```php
// WorkspaceManager implements caching
private array $cache = [];

public function load(string $id): ?Workspace
{
    // Check cache first
    if (isset($this->cache[$id])) {
        return $this->cache[$id];
    }

    // Load from storage
    $workspace = $this->loadFromStorage($id);
    $this->cache[$id] = $workspace;

    return $workspace;
}
```

### Selective Loading

```php
// Model resource - only load model data
public function getModel(string $workspaceId): array
{
    $workspace = $this->manager->load($workspaceId);

    return [
        'workspaceId' => $workspace->id,
        'name' => $workspace->name,
        'model' => $workspace->model, // Only model, not views/docs
    ];
}
```

### Lazy Generation

```php
// Generate DSL only if not cached
public function getDsl(string $workspaceId): string
{
    $workspace = $this->manager->load($workspaceId);

    if (!empty($workspace->dsl)) {
        return $workspace->dsl; // Use cached DSL
    }

    return $this->generateDsl($workspace); // Generate if needed
}
```

---

## Best Practices

### ✅ Do

- Use the most specific resource for your needs
- Cache workspace data when making multiple resource calls
- Handle `ResourceNotFoundException` gracefully
- Embed resources in prompts for AI context
- Use model resource for structural analysis
- Use views resource for visualization analysis

### ❌ Don't

- Load full workspace when you only need model or views
- Ignore error handling
- Assume resources exist without validation
- Parse JSON manually (use provided structures)
- Mix resource URIs with tool names
- Use resources for write operations (use tools instead)

---

## Next Steps

- [Resources Overview](/docs/resources/overview.md) - Conceptual understanding
- [Prompts Reference](/docs/prompts/reference.md) - How to embed resources
- [Tools Reference](/docs/tools/reference.md) - Complement with write operations
- [Examples](/docs/examples/workflow.md) - Real-world usage patterns
