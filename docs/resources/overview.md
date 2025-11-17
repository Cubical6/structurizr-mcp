# Resources

## Introduction

MCP Resources provide URI-addressable data sources that can be accessed by LLMs during conversations. Unlike tools (which perform actions), resources are read-only endpoints that expose structured data from your Structurizr workspaces.

## What Are Resources?

Resources in the Model Context Protocol serve as a standardized way to expose data to AI assistants. Think of them as RESTful API endpoints that LLMs can access to retrieve information about your architecture models.

### Key Characteristics

- **URI-addressable**: Each resource has a unique URI (e.g., `structurizr://workspace/abc123`)
- **Read-only**: Resources provide data access without modification
- **Structured**: Return JSON or text in predictable formats
- **Dynamic**: Support URI templates with parameters (e.g., `{workspaceId}`)
- **Context-aware**: Can be embedded directly in prompts for AI analysis

## Why Use Resources?

Resources solve several important challenges when working with AI assistants and architecture models:

### 1. Contextual Understanding

Instead of manually copying workspace data into conversations, resources allow the AI to directly access the current state of your architecture:

```
Human: Analyze the security of workspace abc123
AI: [Accesses structurizr://workspace/abc123 resource]
    Based on the workspace data, I notice...
```

### 2. Data Consistency

Resources always return the current state from storage, ensuring the AI works with up-to-date information rather than stale copies.

### 3. Selective Access

Different resources expose different views of the same data:

- Full workspace for comprehensive analysis
- Model-only for element relationships
- Views-only for diagram structure
- DSL for code review

### 4. Prompt Integration

Resources can be embedded in prompts to provide context automatically:

```php
#[McpPrompt(name: 'analyze_architecture')]
public function analyze(string $workspaceId): array
{
    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'resource',
                        'resource' => [
                            'uri' => "structurizr://workspace/{$workspaceId}",
                            'text' => $workspaceData
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Please analyze this architecture...'
                    ]
                ]
            ]
        ]
    ];
}
```

## Resource Types

The Structurizr MCP server provides 7 resources across 3 categories:

### Static Resources

**Configuration** - Server and environment information

- `structurizr://config`

### Workspace Resources

**Workspace Data** - Different views of workspace content

- `structurizr://workspace/{workspaceId}` - Complete workspace
- `structurizr://workspace/{workspaceId}/model` - Model elements only
- `structurizr://workspace/{workspaceId}/views` - View definitions only
- `structurizr://workspace/{workspaceId}/dsl` - DSL representation

### Element & View Resources

**Granular Access** - Individual elements and views

- `structurizr://workspace/{workspaceId}/element/{elementId}` - Single element
- `structurizr://workspace/{workspaceId}/view/{viewKey}` - Single view

## How Resources Work

### URI Templates

Resources use URI templates with placeholders that are filled at runtime:

```
Template: structurizr://workspace/{workspaceId}/element/{elementId}
Runtime:  structurizr://workspace/abc123/element/person-1
```

### Content Types

Resources return data in two primary formats:

| MIME Type | Usage | Example Resources |
|-----------|-------|-------------------|
| `application/json` | Structured data | Workspaces, elements, views, config |
| `text/plain` | Plain text | DSL representation |

### Discovery

MCP clients can discover available resources through the protocol's capability negotiation:

1. Client connects to server
2. Server advertises resource templates
3. Client can list and access matching resources
4. AI can intelligently select appropriate resources

## Common Patterns

### Pattern 1: Full Workspace Analysis

Use the complete workspace resource when you need comprehensive understanding:

```php
// Access full workspace
$uri = "structurizr://workspace/{$workspaceId}";
// Returns: { id, name, description, model, views, dsl, ... }
```

**Use when:**
- Performing comprehensive architecture reviews
- Analyzing cross-cutting concerns
- Generating documentation
- Understanding overall structure

### Pattern 2: Focused Element Inspection

Use element resources when examining specific components:

```php
// Access single element
$uri = "structurizr://workspace/{$workspaceId}/element/{$elementId}";
// Returns: { workspaceId, element: { id, name, type, relationships, ... } }
```

**Use when:**
- Reviewing element dependencies
- Checking element properties
- Analyzing specific relationships
- Validating element configuration

### Pattern 3: View-Specific Operations

Use view resources when working with diagram configurations:

```php
// Access single view
$uri = "structurizr://workspace/{$workspaceId}/view/{$viewKey}";
// Returns: { workspaceId, view: { key, type, elements, layout, ... } }
```

**Use when:**
- Reviewing diagram layouts
- Checking view element inclusion
- Analyzing auto-layout settings
- Validating view configuration

### Pattern 4: Model vs. Views Separation

Access model or views independently for focused analysis:

```php
// Model only
$modelUri = "structurizr://workspace/{$workspaceId}/model";

// Views only
$viewsUri = "structurizr://workspace/{$workspaceId}/views";
```

**Use when:**
- Analyzing architectural structure (model)
- Reviewing visualization strategy (views)
- Checking documentation completeness
- Performing focused validation

## Resource Lifecycle

### 1. Resource Definition

Resources are defined using PHP attributes in resource classes:

```php
#[McpResourceTemplate(
    uriTemplate: 'structurizr://workspace/{workspaceId}',
    name: 'workspace_full',
    description: 'Complete workspace data',
    mimeType: 'application/json'
)]
public function getWorkspace(string $workspaceId): array
{
    // Load and return workspace data
}
```

### 2. Resource Discovery

When an MCP client connects, the server advertises available resources:

```json
{
  "resources": [
    {
      "uri": "structurizr://config",
      "name": "server_config",
      "description": "Server configuration and metadata",
      "mimeType": "application/json"
    },
    {
      "uriTemplate": "structurizr://workspace/{workspaceId}",
      "name": "workspace_full",
      "description": "Complete workspace data",
      "mimeType": "application/json"
    }
  ]
}
```

### 3. Resource Access

Clients request resources by URI, and the server routes to the appropriate handler:

```
Request:  GET structurizr://workspace/abc123
Route:    WorkspaceResource::getWorkspace('abc123')
Response: { id: 'abc123', name: 'My Workspace', ... }
```

### 4. Error Handling

Resources throw specific exceptions for error conditions:

- `ResourceNotFoundException` - Workspace or element doesn't exist
- `ResourceReadException` - Error loading or processing data

## Best Practices

### Choose the Right Granularity

Select the resource that provides just enough data for your use case:

- ✅ Use element resource to check a single element
- ❌ Don't load full workspace just to check one element
- ✅ Use model resource when you only need elements
- ❌ Don't load full workspace if you don't need views

### Leverage Resource Embedding

Embed resources in prompts for consistent AI context:

```php
// ✅ Good - Resource embedded in prompt
#[McpPrompt(name: 'review_security')]
public function reviewSecurity(string $workspaceId): array
{
    $workspace = $this->manager->load($workspaceId);
    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'resource', 'resource' => [...]],
                    ['type' => 'text', 'text' => 'Review security...']
                ]
            ]
        ]
    ];
}

// ❌ Bad - Manual data copying
"Please review this workspace: " . json_encode($workspace)
```

### Handle Missing Resources Gracefully

Always validate resource existence before processing:

```php
// ✅ Good - Check before access
$workspace = $this->manager->load($workspaceId);
if ($workspace === null) {
    throw new ResourceNotFoundException(...);
}

// ❌ Bad - Assume existence
return $this->manager->load($workspaceId)->toArray();
```

### Use Descriptive Resource Names

Resource names should clearly indicate their purpose:

```php
// ✅ Good names
name: 'workspace_full'
name: 'workspace_model'
name: 'workspace_element'

// ❌ Ambiguous names
name: 'data'
name: 'info'
name: 'get'
```

## Integration with Tools

Resources complement tools by providing read access while tools provide write access:

| Operation | Capability | Example |
|-----------|-----------|---------|
| Read workspace | Resource | `structurizr://workspace/{id}` |
| Create workspace | Tool | `create_workspace(name, description)` |
| Read element | Resource | `structurizr://workspace/{id}/element/{elementId}` |
| Add element | Tool | `add_software_system(workspaceId, name, ...)` |

### Workflow Example

```
1. Create workspace (tool)
   → create_workspace('My App', 'Description')
   → Returns: { workspaceId: 'abc123' }

2. Add elements (tool)
   → add_software_system(workspaceId, 'API', ...)
   → add_container(systemId, 'Database', ...)

3. Review architecture (resource)
   → Access: structurizr://workspace/abc123
   → AI analyzes complete workspace

4. Add improvements (tool)
   → add_relationship(apiId, dbId, 'Stores data in')
   → create_container_view(systemId, 'Containers')

5. Validate changes (resource)
   → Access: structurizr://workspace/abc123/model
   → Verify relationships are correct
```

## Integration with Prompts

Resources are commonly embedded in prompts to provide architectural context:

### Analysis Prompts

```php
#[McpPrompt(name: 'analyze_architecture')]
public function analyze(string $workspaceId): array
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
                        'text' => 'Analyze this architecture for patterns, complexity, dependencies, and risks.'
                    ]
                ]
            ]
        ]
    ];
}
```

### Security Review Prompts

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
                                'model' => $workspace->model
                            ])
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Review this architecture for security concerns...'
                    ]
                ]
            ]
        ]
    ];
}
```

## Performance Considerations

### Caching

Resources can benefit from caching to reduce I/O:

```php
// WorkspaceManager caches loaded workspaces
private array $cache = [];

public function load(string $id): ?Workspace
{
    if (isset($this->cache[$id])) {
        return $this->cache[$id];
    }

    $workspace = $this->loadFromStorage($id);
    $this->cache[$id] = $workspace;

    return $workspace;
}
```

### Lazy Loading

Only load what's needed when it's needed:

```php
// ✅ Good - Only load DSL if it exists
public function getDsl(string $workspaceId): string
{
    $workspace = $this->manager->load($workspaceId);

    if (!empty($workspace->dsl)) {
        return $workspace->dsl;
    }

    // Generate if not cached
    return $this->generateDsl($workspace);
}
```

### Selective Data Return

Return only relevant data for each resource type:

```php
// Model resource - exclude views
public function getModel(string $workspaceId): array
{
    $workspace = $this->manager->load($workspaceId);

    return [
        'workspaceId' => $workspace->id,
        'name' => $workspace->name,
        'model' => $workspace->model, // Only model data
    ];
}
```

## Next Steps

- [Resource Reference](/docs/resources/reference.md) - Detailed documentation for each resource
- [Prompts Overview](/docs/prompts/overview.md) - How resources integrate with prompts
- [Tools Overview](/docs/tools/overview.md) - Complement resources with actions
- [Examples](/docs/examples/workflow.md) - Real-world usage patterns
