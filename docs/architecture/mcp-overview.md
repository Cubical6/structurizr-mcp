# MCP Overview

## Introduction

The Model Context Protocol (MCP) is an open protocol that standardizes how applications provide context to Large Language Models (LLMs). This server implements MCP to provide Structurizr integration for Claude and other MCP-compatible clients.

## What is MCP?

MCP is a **universal, vendor-neutral standard** for interactions between LLMs and external systems. It provides a consistent way to expose capabilities, data, and functionality to AI assistants.

### Core Principles

1. **Standardization** - One protocol for all integrations
2. **Separation of Concerns** - Clear boundaries between client and server
3. **Extensibility** - Easy to add new capabilities
4. **Security** - Controlled access to resources

## MCP Architecture

### Client-Server Model

```
┌─────────────────┐         ┌──────────────────┐
│                 │         │                  │
│  MCP Client     │◄───────►│   MCP Server     │
│  (Claude)       │  JSON   │  (Structurizr)   │
│                 │  RPC    │                  │
└─────────────────┘         └──────────────────┘
                                     │
                                     │
                            ┌────────▼────────┐
                            │                 │
                            │  Structurizr    │
                            │  CLI            │
                            │                 │
                            └─────────────────┘
```

### Components

#### MCP Client
The client (like Claude Desktop) connects to MCP servers and:
- Discovers available capabilities
- Requests tool execution
- Accesses resources
- Uses prompt templates
- Receives notifications

#### MCP Server
The server (this project) exposes capabilities and:
- Registers tools, resources, and prompts
- Handles requests from the client
- Executes operations (workspace management, etc.)
- Returns structured responses
- Manages state

## MCP Protocol

### Transport Layer

MCP supports multiple transport mechanisms:

**stdio (Standard Input/Output)**
- Used for local servers
- Simple process-based communication
- Default for Claude Desktop
- What this server uses

**HTTP with Server-Sent Events (SSE)**
- Used for remote servers
- Web-based communication
- Enables cloud deployments

### Message Format

MCP uses **JSON-RPC 2.0** for all communication:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "create_workspace",
    "arguments": {
      "name": "My Architecture",
      "description": "System architecture diagram"
    }
  }
}
```

### Message Types

**Request** - Client asks server to do something
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "method_name",
  "params": { }
}
```

**Response** - Server returns result
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": { }
}
```

**Notification** - One-way message (no response expected)
```json
{
  "jsonrpc": "2.0",
  "method": "notifications/message",
  "params": { }
}
```

**Error** - Something went wrong
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "error": {
    "code": -32600,
    "message": "Invalid Request"
  }
}
```

## MCP Capabilities

### Tools

**Tools** are functions the LLM can call to perform actions.

**Characteristics:**
- Input schema validation
- Synchronous execution
- Structured return values
- Error handling

**Example Tool Definition:**
```php
#[McpTool(
    name: 'create_workspace',
    description: 'Creates a new Structurizr workspace'
)]
public function createWorkspace(
    #[Schema(description: 'Workspace name', minLength: 1)]
    string $name,

    #[Schema(description: 'Workspace description')]
    string $description = ''
): array {
    // Implementation
}
```

**Tool Discovery:**
```json
{
  "method": "tools/list",
  "result": {
    "tools": [
      {
        "name": "create_workspace",
        "description": "Creates a new Structurizr workspace",
        "inputSchema": {
          "type": "object",
          "properties": {
            "name": { "type": "string" },
            "description": { "type": "string" }
          },
          "required": ["name"]
        }
      }
    ]
  }
}
```

### Resources

**Resources** are data sources that the LLM can read.

**Characteristics:**
- URI-based addressing
- Read-only access
- MIME type support
- Text or binary content

**URI Scheme:**
```
structurizr://workspace/{workspaceId}
structurizr://workspace/{workspaceId}/model
structurizr://workspace/{workspaceId}/views
structurizr://workspace/{workspaceId}/dsl
structurizr://workspace/{workspaceId}/element/{elementId}
structurizr://workspace/{workspaceId}/view/{viewKey}
structurizr://config
```

**Resource Definition:**
```php
#[McpResource(
    uri: 'structurizr://workspace/{workspaceId}',
    name: 'Structurizr Workspace',
    description: 'Complete workspace data',
    mimeType: 'application/json'
)]
public function getWorkspace(string $workspaceId): ResourceContents
{
    // Return workspace data
}
```

### Prompts

**Prompts** are reusable templates that help the LLM perform common tasks.

**Characteristics:**
- Parameterized templates
- Best practices encoded
- Consistent behavior
- User-friendly

**Example Prompt:**
```php
#[McpPrompt(
    name: 'analyze_architecture',
    description: 'Comprehensive architecture analysis'
)]
public function analyzeArchitecture(
    #[Argument(description: 'Workspace ID to analyze')]
    string $workspaceId
): PromptMessage {
    return PromptMessage::user(
        "Analyze the architecture in workspace {$workspaceId}..."
    );
}
```

## Server Lifecycle

### 1. Initialization

```
Client                          Server
  │                               │
  ├──── initialize ──────────────►│
  │                               ├─── Load configuration
  │                               ├─── Setup logging
  │                               ├─── Initialize components
  │◄──── initialized ─────────────┤
  │                               │
```

### 2. Capability Discovery

```
Client                          Server
  │                               │
  ├──── tools/list ──────────────►│
  │◄──── [tool definitions] ─────┤
  │                               │
  ├──── resources/list ──────────►│
  │◄──── [resource definitions] ─┤
  │                               │
  ├──── prompts/list ────────────►│
  │◄──── [prompt definitions] ───┤
  │                               │
```

### 3. Operation

```
Client                          Server
  │                               │
  ├──── tools/call ──────────────►│
  │                               ├─── Validate input
  │                               ├─── Execute operation
  │                               ├─── Update state
  │◄──── result ──────────────────┤
  │                               │
  ├──── resources/read ──────────►│
  │                               ├─── Load resource
  │                               ├─── Format response
  │◄──── content ─────────────────┤
  │                               │
```

### 4. Shutdown

```
Client                          Server
  │                               │
  ├──── shutdown ────────────────►│
  │                               ├─── Cleanup resources
  │                               ├─── Close connections
  │◄──── acknowledged ────────────┤
  │                               │
```

## Server Implementation

### PHP MCP SDK

This server uses the official PHP MCP SDK from `mcp/sdk`.

**Key Classes:**

```php
use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpPrompt;
```

### Auto-Discovery

The server uses attribute-based auto-discovery:

```php
$server = Server::builder()
    ->setServerInfo(
        name: 'structurizr-mcp-server',
        version: '1.0.0',
        description: 'MCP server for Structurizr'
    )
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['src'],
        excludeDirs: ['vendor', 'tests'],
        cache: $cache
    )
    ->build();
```

**How it works:**
1. Scans specified directories
2. Finds classes with MCP attributes
3. Registers capabilities automatically
4. Caches discovery results
5. Rebuilds when code changes

### Request Handling

```php
// Tool handler
#[McpTool(name: 'tool_name')]
public function toolMethod(
    #[Schema] string $param
): array {
    try {
        // Validate input
        // Perform operation
        // Return result
        return ['success' => true, 'data' => $result];
    } catch (Exception $e) {
        throw new ToolCallException($e->getMessage());
    }
}

// Resource handler
#[McpResource(uri: 'scheme://resource/{id}')]
public function resourceMethod(string $id): ResourceContents {
    // Load resource
    // Format content
    return ResourceContents::text($content);
}

// Prompt handler
#[McpPrompt(name: 'prompt_name')]
public function promptMethod(
    #[Argument] string $arg
): PromptMessage {
    return PromptMessage::user("Prompt text: {$arg}");
}
```

### Error Handling

MCP defines standard error codes:

```php
// JSON-RPC standard errors
-32700  Parse error
-32600  Invalid Request
-32601  Method not found
-32602  Invalid params
-32603  Internal error

// Application errors
-32000 to -32099  Server-defined errors
```

**Custom Exception Handling:**

```php
try {
    // Operation
} catch (WorkspaceNotFoundException $e) {
    throw new ToolCallException(
        "Workspace not found: {$workspaceId}",
        -32001
    );
} catch (InvalidDslException $e) {
    throw new ToolCallException(
        "Invalid DSL: {$e->getMessage()}",
        -32002
    );
}
```

## Configuration

### Claude Desktop Setup

The client configuration file specifies how to connect:

**macOS:** `~/Library/Application Support/Claude/claude_desktop_config.json`
**Windows:** `%APPDATA%\Claude\claude_desktop_config.json`

```json
{
  "mcpServers": {
    "structurizr": {
      "command": "php",
      "args": ["/path/to/structurizr-mcp/server.php"],
      "env": {
        "STRUCTURIZR_CLI_PATH": "./bin/structurizr-cli.sh",
        "WORKSPACE_STORAGE_PATH": "./workspaces",
        "LOG_LEVEL": "INFO"
      }
    }
  }
}
```

### Environment Variables

| Variable | Required | Description |
|----------|----------|-------------|
| `STRUCTURIZR_CLI_PATH` | Yes | Path to Structurizr CLI |
| `WORKSPACE_STORAGE_PATH` | Yes | Workspace storage directory |
| `LOG_LEVEL` | No | Logging level (DEBUG/INFO/WARNING/ERROR) |
| `LOG_PATH` | No | Log file path (default: php://stderr) |
| `SERVER_NAME` | No | Server name in discovery |
| `SERVER_VERSION` | No | Server version in discovery |

## Best Practices

### Tool Design

**1. Single Responsibility**
Each tool should do one thing well.

```php
// Good: Focused tool
#[McpTool(name: 'create_workspace')]
public function createWorkspace(string $name): array

// Bad: Tool does too much
#[McpTool(name: 'create_and_populate_workspace')]
public function createAndPopulate(/* many params */)
```

**2. Clear Naming**
Use verb-noun format: `create_workspace`, `add_person`, `export_to_dsl`

**3. Descriptive Schemas**
Provide helpful descriptions for all parameters:

```php
#[Schema(
    description: 'Workspace name (max 100 characters)',
    minLength: 1,
    maxLength: 100
)]
string $name
```

**4. Meaningful Returns**
Return structured data with clear success indicators:

```php
return [
    'success' => true,
    'workspaceId' => $id,
    'message' => 'Workspace created successfully'
];
```

### Resource Design

**1. Consistent URI Schemes**
Use hierarchical URIs:

```
structurizr://workspace/{id}           # Workspace
structurizr://workspace/{id}/model     # Model subset
structurizr://workspace/{id}/views     # Views subset
```

**2. Appropriate MIME Types**
Use standard MIME types:
- `application/json` for JSON data
- `text/plain` for DSL/PlantUML/Mermaid
- `text/markdown` for documentation

**3. Caching Strategy**
Resources should be deterministic and cacheable when possible.

### Prompt Design

**1. Clear Instructions**
Prompts should provide clear, actionable instructions:

```php
return PromptMessage::user(
    "Analyze the architecture in workspace {$workspaceId}. " .
    "Focus on:\n" .
    "1. System boundaries\n" .
    "2. Component coupling\n" .
    "3. Security considerations"
);
```

**2. Parameterization**
Allow customization through arguments:

```php
#[McpPrompt(name: 'review_security')]
public function reviewSecurity(
    #[Argument] string $workspaceId,
    #[Argument] string $focus = 'all'
)
```

## Security Considerations

### 1. Input Validation

Always validate inputs:

```php
#[Schema(
    type: 'string',
    minLength: 1,
    maxLength: 100,
    pattern: '^[a-zA-Z0-9_-]+$'
)]
string $workspaceId
```

### 2. Path Traversal Prevention

Sanitize file paths:

```php
// Sanitize workspace ID
$sanitizedId = preg_replace('/[^a-zA-Z0-9_-]/', '', $id);

// Validate resolved paths
$resolvedPath = realpath($path);
if (strpos($resolvedPath, $this->storagePath) !== 0) {
    throw new SecurityException('Invalid path');
}
```

### 3. Command Injection Prevention

Use array-form commands:

```php
// Good: Array form (no shell)
$process = new Process([
    '/path/to/cli',
    'validate',
    '--workspace',
    $workspacePath
]);

// Bad: String form (shell injection risk)
$process = new Process("cli validate --workspace $workspacePath");
```

### 4. Credential Protection

Never log credentials:

```php
private function sanitizeForLogging(array $command): array {
    $sanitized = [];
    $redactNext = false;

    foreach ($command as $arg) {
        if ($redactNext) {
            $sanitized[] = '[REDACTED]';
            $redactNext = false;
        } elseif (in_array($arg, ['-key', '-secret'])) {
            $sanitized[] = $arg;
            $redactNext = true;
        } else {
            $sanitized[] = $arg;
        }
    }

    return $sanitized;
}
```

## Performance Optimization

### 1. Caching

**Discovery Cache:**
```php
$cache = new Psr16Cache(
    new PhpFilesAdapter(directory: __DIR__ . '/cache')
);
```

**Resource Caching:**
Consider caching expensive resource computations.

### 2. Lazy Loading

Load resources only when requested:

```php
public function getWorkspace(string $id): ResourceContents {
    // Load only when resource is accessed
    $workspace = $this->manager->load($id);
    return ResourceContents::json($workspace->toArray());
}
```

### 3. Timeouts

Set appropriate timeouts for operations:

```php
private const TIMEOUT_VALIDATION = 30;
private const TIMEOUT_EXPORT = 30;
private const TIMEOUT_CLOUD_OPS = 60;
```

## Debugging

### Logging

The server logs to stderr by default:

```php
$logger->debug('Operation started', ['workspace' => $id]);
$logger->info('Workspace created', ['id' => $id]);
$logger->warning('Validation warnings found');
$logger->error('Operation failed', ['error' => $e->getMessage()]);
```

**View logs:**
```bash
# macOS
tail -f ~/Library/Logs/Claude/mcp*.log

# Linux
tail -f ~/.config/Claude/logs/mcp*.log
```

### Testing Tools

**Inspector:**
Use the MCP Inspector to test your server:

```bash
npm install -g @modelcontextprotocol/inspector
mcp-inspector php server.php
```

**Manual Testing:**
Test with curl (for HTTP/SSE transport):

```bash
curl -X POST http://localhost:3000/rpc \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

## Resources

### MCP Documentation
- [Model Context Protocol](https://modelcontextprotocol.io)
- [MCP Specification](https://spec.modelcontextprotocol.io)
- [MCP PHP SDK](https://github.com/modelcontextprotocol/php-sdk)

### Related Documentation
- [Workspace Management](/docs/architecture/workspace-management.md)
- [CLI Integration](/docs/architecture/cli-integration.md)
- [DSL Builder](/docs/architecture/dsl-builder.md)

### Tools Reference
- [Workspace Tools](/docs/tools/workspace-tools.md)
- [Model Tools](/docs/tools/model-tools.md)
- [View Tools](/docs/tools/view-tools.md)
