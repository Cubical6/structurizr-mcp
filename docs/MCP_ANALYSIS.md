# Model Context Protocol (MCP) - Comprehensive Analysis

## Executive Summary

This document provides a comprehensive analysis of the Model Context Protocol (MCP) based on the official documentation at https://modelcontextprotocol.io/llms-full.txt. It serves as a guide for implementing an MCP server for Structurizr C4 modeling.

---

## 1. What is MCP and Its Purpose?

### Definition
The Model Context Protocol (MCP) is a **universal, vendor-neutral standard protocol** that enables seamless interactions between Large Language Models (LLMs) and external systems. It provides a standardized way for AI applications to access external data sources, tools, and capabilities.

### Core Purpose
- **Interoperability**: Create a consistent interface for LLM-to-external-system communication
- **Standardization**: Eliminate the need for custom integrations for each data source or tool
- **Scalability**: Enable horizontal scaling where one server can serve multiple clients
- **Vendor Neutrality**: Avoid lock-in to specific vendors or platforms

### Key Value Proposition
MCP aims to become the standard for "model-to-world interactions," allowing AI applications to reliably access external capabilities through a consistent interface, similar to how HTTP standardized web communication.

---

## 2. Core Concepts

### 2.1 Architecture Overview
MCP uses a **client-server architecture** with the following components:

```
┌─────────────┐         ┌─────────────┐
│             │         │             │
│  MCP Client │◄───────►│  MCP Server │
│             │         │             │
└──────┬──────┘         └──────┬──────┘
       │                       │
       │                       │
   ┌───▼────┐            ┌─────▼──────┐
   │  LLM   │            │ Resources  │
   │(Claude)│            │ Tools      │
   └────────┘            │ Prompts    │
                         └────────────┘
```

### 2.2 Servers
**MCP Servers** are programs that:
- Expose capabilities (resources, tools, prompts) to clients
- Listen for incoming connections from MCP clients
- Execute operations requested by clients
- Maintain protocol communication state
- Can serve multiple clients simultaneously

**Key Characteristics:**
- Can be implemented in multiple languages (Python, TypeScript, Java, Kotlin, C#)
- Support different transport mechanisms (stdio, HTTP/SSE)
- Stateless or stateful depending on transport choice
- Horizontally scalable in enterprise deployments

### 2.3 Clients
**MCP Clients** are applications that:
- Connect to one or more MCP servers
- Discover available capabilities from servers
- Integrate server capabilities with LLMs
- Execute tools/retrieve resources on behalf of LLMs
- Return results back to LLMs for response generation

**Key Characteristics:**
- Can connect to multiple servers simultaneously
- Act as intermediaries between LLMs and external systems
- Handle protocol initialization and message routing
- Format server capabilities as function definitions for LLMs

### 2.4 Resources
**Resources** represent accessible data or information sources.

**Characteristics:**
- URI-style hierarchical naming (e.g., `file:///path/to/document`, `db://table/record`)
- Support optional listing capabilities
- Deliver text content
- Include metadata about availability and updates
- Read-only or read-write depending on implementation

**Use Cases:**
- File system access
- Database records
- API endpoints
- Documentation
- Configuration data

### 2.5 Tools
**Tools** enable clients to perform actions or computations.

**Components:**
- **Unique identifier**: Machine-readable name
- **Human-readable name**: Display name for users
- **Description**: Explains what the tool does
- **Input schema**: JSON Schema defining parameters
- **Execution logic**: Handler function that performs the action

**Use Cases:**
- Data manipulation
- External API calls
- Calculations
- System operations
- Business logic execution

### 2.6 Prompts
**Prompts** are reusable instruction templates with variable substitution.

**Components:**
- **Name**: Identifier for the prompt
- **Description**: Explains the prompt's purpose
- **Arguments**: Variables for customization
- **Template**: The actual prompt text with placeholders

**Use Cases:**
- Common task templates
- Structured query formats
- Instruction patterns
- Conversation starters

---

## 3. MCP Server Architecture

### 3.1 How MCP Servers Work

#### Server Lifecycle
1. **Initialization**: Server starts and prepares transport layer
2. **Listening**: Server waits for client connections
3. **Connection**: Client establishes connection via transport
4. **Protocol Handshake**: Version negotiation and capability exchange
5. **Operation**: Handle client requests for tools, resources, prompts
6. **Cleanup**: Graceful shutdown and resource cleanup

#### Server Responsibilities
- **Capability Registration**: Define and register tools, resources, prompts
- **Request Handling**: Process incoming JSON-RPC 2.0 messages
- **Execution**: Run tool logic, fetch resource data, generate prompts
- **Error Management**: Return meaningful errors when operations fail
- **State Management**: Maintain connection state (if stateful)

### 3.2 Transport Mechanisms

#### Stdio Transport
**Use Case**: Local processes, development, simple deployments

**Characteristics:**
- Direct input/output streams
- Process-level communication
- Simple to implement
- Stateful connections

**Example:**
```bash
node server.js  # Server communicates via stdin/stdout
```

#### HTTP/SSE Transport
**Use Case**: Web-based clients, stateless deployments, enterprise scale

**Characteristics:**
- HTTP-based communication
- Server-Sent Events for server-to-client messages
- Stateless server architecture
- Horizontal scaling support
- Load balancer friendly

**Example:**
```
GET /sse HTTP/1.1
Host: mcp-server.example.com
Accept: text/event-stream
```

### 3.3 Scalability Patterns

#### Horizontal Scaling
- **One Server, Multiple Clients**: Single server instance serves many clients
- **Multiple Servers, One Client**: Client connects to multiple specialized servers
- **Stateless Design**: HTTP/SSE enables load-balanced deployments

#### Deployment Options
- **Local Process**: Simple stdio-based server for development
- **Containerized**: Docker containers for consistent deployment
- **Cloud Functions**: Serverless deployments for auto-scaling
- **Kubernetes**: Orchestrated multi-instance deployments

---

## 4. Key Message Types and Protocol Flows

### 4.1 JSON-RPC 2.0 Foundation

MCP uses **JSON-RPC 2.0** for all message exchange.

#### Message Structure
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "tool_name",
    "arguments": {}
  }
}
```

#### Response Structure
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "content": []
  }
}
```

### 4.2 Core Message Types

#### Initialization Messages
- **Client → Server**: Initialize connection with protocol version
- **Server → Client**: Confirm version and capabilities

#### Capability Discovery
- **tools/list**: Request list of available tools
- **resources/list**: Request list of available resources
- **prompts/list**: Request list of available prompts

#### Execution Messages
- **tools/call**: Execute a specific tool with arguments
- **resources/read**: Retrieve resource content
- **prompts/get**: Retrieve prompt template with arguments

### 4.3 Protocol Flow Examples

#### Tool Execution Flow
```
Client                          Server
  │                              │
  ├─ tools/list ────────────────►│
  │◄──────── tool definitions ───┤
  │                              │
  ├─ tools/call(name, args) ────►│
  │                              ├─ Execute tool
  │◄──────── result/error ───────┤
  │                              │
```

#### Resource Access Flow
```
Client                          Server
  │                              │
  ├─ resources/list ────────────►│
  │◄──────── resource URIs ──────┤
  │                              │
  ├─ resources/read(uri) ────────►│
  │                              ├─ Fetch data
  │◄──────── content ────────────┤
  │                              │
```

#### Session Lifecycle
```
1. Transport Connection Establishment
   ↓
2. Protocol Initialization (version negotiation)
   ↓
3. Capability Discovery (tools, resources, prompts)
   ↓
4. Request/Response Cycles
   ↓
5. Graceful Closure
```

---

## 5. Implementing an MCP Server

### 5.1 Development Workflow

#### Step 1: Project Setup
```bash
# For TypeScript/Node.js
npm init
npm install @modelcontextprotocol/sdk

# For Python
pip install mcp
```

#### Step 2: Server Class Creation
Define a server class that implements required interfaces.

**TypeScript Example:**
```typescript
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";

const server = new Server(
  {
    name: "structurizr-server",
    version: "1.0.0"
  },
  {
    capabilities: {
      tools: {},
      resources: {},
      prompts: {}
    }
  }
);
```

**Python Example:**
```python
from mcp.server import Server
import asyncio

server = Server("structurizr-server")
```

#### Step 3: Capability Definition

**Registering Tools:**
```typescript
server.setRequestHandler(ListToolsRequestSchema, async () => ({
  tools: [
    {
      name: "create_workspace",
      description: "Create a new Structurizr workspace",
      inputSchema: {
        type: "object",
        properties: {
          name: { type: "string" },
          description: { type: "string" }
        },
        required: ["name"]
      }
    }
  ]
}));
```

**Registering Resources:**
```typescript
server.setRequestHandler(ListResourcesRequestSchema, async () => ({
  resources: [
    {
      uri: "workspace://current",
      name: "Current Workspace",
      mimeType: "application/json"
    }
  ]
}));
```

**Registering Prompts:**
```typescript
server.setRequestHandler(ListPromptsRequestSchema, async () => ({
  prompts: [
    {
      name: "analyze_architecture",
      description: "Analyze C4 architecture model",
      arguments: [
        {
          name: "workspace_id",
          description: "ID of workspace to analyze",
          required: true
        }
      ]
    }
  ]
}));
```

#### Step 4: Handler Implementation

**Tool Execution Handler:**
```typescript
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  if (name === "create_workspace") {
    const result = await createWorkspace(args.name, args.description);
    return {
      content: [
        {
          type: "text",
          text: JSON.stringify(result, null, 2)
        }
      ]
    };
  }

  throw new Error(`Unknown tool: ${name}`);
});
```

**Resource Read Handler:**
```typescript
server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  const { uri } = request.params;

  if (uri === "workspace://current") {
    const workspace = await getCurrentWorkspace();
    return {
      contents: [
        {
          uri,
          mimeType: "application/json",
          text: JSON.stringify(workspace, null, 2)
        }
      ]
    };
  }

  throw new Error(`Unknown resource: ${uri}`);
});
```

**Prompt Get Handler:**
```typescript
server.setRequestHandler(GetPromptRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  if (name === "analyze_architecture") {
    return {
      messages: [
        {
          role: "user",
          content: {
            type: "text",
            text: `Analyze the architecture in workspace ${args.workspace_id}`
          }
        }
      ]
    };
  }

  throw new Error(`Unknown prompt: ${name}`);
});
```

#### Step 5: Server Startup

**Stdio Transport:**
```typescript
const transport = new StdioServerTransport();
await server.connect(transport);
```

**HTTP/SSE Transport:**
```typescript
import { SSEServerTransport } from "@modelcontextprotocol/sdk/server/sse.js";
import express from "express";

const app = express();
const transport = new SSEServerTransport("/message", res);
await server.connect(transport);

app.listen(3000);
```

### 5.2 Best Practices

#### Input Validation
```typescript
function validateInput(args: unknown): WorkspaceArgs {
  if (!args || typeof args !== "object") {
    throw new Error("Invalid arguments");
  }

  const { name, description } = args as Record<string, unknown>;

  if (typeof name !== "string" || name.trim() === "") {
    throw new Error("Name must be a non-empty string");
  }

  return { name, description: description as string };
}
```

#### Error Handling
```typescript
try {
  const result = await riskyOperation();
  return { content: [{ type: "text", text: result }] };
} catch (error) {
  return {
    content: [
      {
        type: "text",
        text: `Error: ${error.message}`
      }
    ],
    isError: true
  };
}
```

#### Resource Management
```typescript
const connections = new Map<string, Connection>();

async function cleanup() {
  for (const [id, conn] of connections) {
    await conn.close();
    connections.delete(id);
  }
}

process.on("SIGINT", cleanup);
process.on("SIGTERM", cleanup);
```

#### Documentation
```typescript
{
  name: "create_workspace",
  description: "Create a new Structurizr workspace with the specified name and optional description. Returns the workspace ID and URL.",
  inputSchema: {
    type: "object",
    properties: {
      name: {
        type: "string",
        description: "The name of the workspace (required, 1-100 characters)"
      },
      description: {
        type: "string",
        description: "Optional description of the workspace purpose"
      }
    },
    required: ["name"]
  }
}
```

#### Async Support
```typescript
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const startTime = Date.now();

  try {
    const result = await longRunningOperation(request.params.arguments);

    console.log(`Operation completed in ${Date.now() - startTime}ms`);

    return {
      content: [{ type: "text", text: result }]
    };
  } catch (error) {
    console.error(`Operation failed after ${Date.now() - startTime}ms:`, error);
    throw error;
  }
});
```

#### Security Considerations
```typescript
// Authentication
function verifyAuth(token: string): boolean {
  // Verify JWT or API key
  return true;
}

// Authorization
function checkPermissions(user: User, resource: string): boolean {
  // Check if user can access resource
  return true;
}

// Sanitization
function sanitizePath(path: string): string {
  // Prevent directory traversal
  return path.replace(/\.\./g, "");
}
```

---

## 6. Resources, Tools, and Prompts in Detail

### 6.1 Resources - Deep Dive

#### Resource Structure
```typescript
interface Resource {
  uri: string;           // Unique identifier (URI format)
  name: string;          // Human-readable name
  description?: string;  // Optional description
  mimeType?: string;     // Content type
}
```

#### Resource URI Patterns
```
workspace://123                    # Workspace by ID
workspace://123/model              # Workspace model
workspace://123/views              # Workspace views
workspace://123/documentation      # Workspace docs
element://system1                  # Specific element
relationship://sys1-to-sys2        # Relationships
```

#### Resource Implementation Pattern
```typescript
// List resources
server.setRequestHandler(ListResourcesRequestSchema, async () => {
  const workspaces = await getAllWorkspaces();

  return {
    resources: workspaces.map(ws => ({
      uri: `workspace://${ws.id}`,
      name: ws.name,
      description: ws.description,
      mimeType: "application/json"
    }))
  };
});

// Read resource
server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  const uri = request.params.uri;
  const match = uri.match(/^workspace:\/\/(\d+)$/);

  if (match) {
    const workspaceId = match[1];
    const workspace = await getWorkspace(workspaceId);

    return {
      contents: [{
        uri,
        mimeType: "application/json",
        text: JSON.stringify(workspace, null, 2)
      }]
    };
  }

  throw new Error(`Resource not found: ${uri}`);
});
```

#### Resource Best Practices
- Use clear, hierarchical URI schemes
- Include mime types for proper content handling
- Support both list and read operations
- Provide meaningful descriptions
- Handle missing resources gracefully
- Cache frequently accessed resources
- Support resource templates/patterns

### 6.2 Tools - Deep Dive

#### Tool Structure
```typescript
interface Tool {
  name: string;                    // Unique identifier
  description: string;             // What the tool does
  inputSchema: {                   // JSON Schema for inputs
    type: "object";
    properties: Record<string, SchemaProperty>;
    required?: string[];
  };
}
```

#### Tool Implementation Patterns

**Simple Tool:**
```typescript
{
  name: "get_workspace_count",
  description: "Get the total number of workspaces",
  inputSchema: {
    type: "object",
    properties: {}
  }
}

// Handler
if (name === "get_workspace_count") {
  const count = await countWorkspaces();
  return {
    content: [{
      type: "text",
      text: `Total workspaces: ${count}`
    }]
  };
}
```

**Complex Tool with Validation:**
```typescript
{
  name: "create_c4_element",
  description: "Create a new C4 model element (Person, System, Container, or Component)",
  inputSchema: {
    type: "object",
    properties: {
      workspace_id: {
        type: "string",
        description: "The workspace ID"
      },
      element_type: {
        type: "string",
        enum: ["Person", "SoftwareSystem", "Container", "Component"],
        description: "Type of C4 element to create"
      },
      name: {
        type: "string",
        description: "Name of the element"
      },
      description: {
        type: "string",
        description: "Description of the element"
      },
      tags: {
        type: "array",
        items: { type: "string" },
        description: "Optional tags for the element"
      }
    },
    required: ["workspace_id", "element_type", "name"]
  }
}

// Handler
if (name === "create_c4_element") {
  const { workspace_id, element_type, name, description, tags } = args;

  // Validate workspace exists
  const workspace = await getWorkspace(workspace_id);
  if (!workspace) {
    throw new Error(`Workspace not found: ${workspace_id}`);
  }

  // Create element
  const element = await createElement({
    workspaceId: workspace_id,
    type: element_type,
    name,
    description,
    tags: tags || []
  });

  return {
    content: [{
      type: "text",
      text: `Created ${element_type} "${name}" with ID ${element.id}`
    }]
  };
}
```

**Tool with Multiple Return Types:**
```typescript
// Handler can return different content types
return {
  content: [
    {
      type: "text",
      text: "Created diagram successfully"
    },
    {
      type: "resource",
      resource: {
        uri: `workspace://${workspaceId}/diagram/${diagramId}`,
        mimeType: "image/svg+xml",
        text: svgContent
      }
    }
  ]
};
```

#### Tool Best Practices
- Use clear, action-oriented names (create_*, get_*, update_*, delete_*)
- Provide detailed descriptions explaining what the tool does
- Define comprehensive input schemas with descriptions
- Validate all inputs before execution
- Return structured, parseable results
- Include error details in error responses
- Log tool executions for debugging
- Support idempotent operations where possible

### 6.3 Prompts - Deep Dive

#### Prompt Structure
```typescript
interface Prompt {
  name: string;                    // Unique identifier
  description: string;             // What the prompt does
  arguments?: PromptArgument[];    // Optional parameters
}

interface PromptArgument {
  name: string;                    // Argument name
  description: string;             // What the argument is for
  required: boolean;               // Whether it's required
}
```

#### Prompt Implementation Patterns

**Simple Prompt:**
```typescript
{
  name: "analyze_workspace",
  description: "Generate an analysis of a Structurizr workspace",
  arguments: [
    {
      name: "workspace_id",
      description: "The ID of the workspace to analyze",
      required: true
    }
  ]
}

// Handler
server.setRequestHandler(GetPromptRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  if (name === "analyze_workspace") {
    const workspace = await getWorkspace(args.workspace_id);

    return {
      messages: [
        {
          role: "user",
          content: {
            type: "text",
            text: `Analyze this Structurizr workspace and provide insights:

Workspace: ${workspace.name}
Description: ${workspace.description}

Model Elements: ${workspace.model.elements.length}
Views: ${workspace.views.length}
Documentation Sections: ${workspace.documentation.sections.length}

Please analyze:
1. Architecture patterns used
2. Complexity assessment
3. Documentation completeness
4. Suggested improvements`
          }
        }
      ]
    };
  }
});
```

**Advanced Prompt with Resource Embedding:**
```typescript
{
  name: "review_c4_model",
  description: "Review a C4 architecture model for best practices",
  arguments: [
    {
      name: "workspace_id",
      description: "The workspace ID to review",
      required: true
    },
    {
      name: "focus_area",
      description: "Specific area to focus on (e.g., 'security', 'scalability', 'maintainability')",
      required: false
    }
  ]
}

// Handler
if (name === "review_c4_model") {
  const workspace = await getWorkspace(args.workspace_id);
  const focusArea = args.focus_area || "general best practices";

  return {
    messages: [
      {
        role: "user",
        content: {
          type: "text",
          text: `Review the following C4 architecture model with focus on ${focusArea}:`
        }
      },
      {
        role: "user",
        content: {
          type: "resource",
          resource: {
            uri: `workspace://${args.workspace_id}`,
            mimeType: "application/json",
            text: JSON.stringify(workspace, null, 2)
          }
        }
      },
      {
        role: "user",
        content: {
          type: "text",
          text: `Please provide:
1. Overall assessment
2. Strengths
3. Weaknesses
4. Specific recommendations for ${focusArea}
5. Priority improvements`
        }
      }
    ]
  };
}
```

#### Prompt Best Practices
- Create prompts for common workflows
- Use clear, descriptive names
- Support customization via arguments
- Embed relevant resources when needed
- Structure prompts for clear LLM understanding
- Include specific questions or analysis points
- Provide context about the domain (Structurizr, C4)
- Test prompts with actual LLMs for effectiveness

---

## 7. Available SDKs and Libraries

### 7.1 Official SDKs

#### TypeScript/JavaScript SDK
**Package**: `@modelcontextprotocol/sdk`

**Installation:**
```bash
npm install @modelcontextprotocol/sdk
```

**Key Features:**
- Full TypeScript type definitions
- ESM module support
- Browser and Node.js compatible
- Stdio and HTTP/SSE transports
- Async/await based API

**Example:**
```typescript
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
```

#### Python SDK
**Package**: `mcp`

**Installation:**
```bash
pip install mcp
```

**Key Features:**
- Asyncio support
- Type hints
- Decorator-based tool registration
- Stdio and HTTP transports

**Example:**
```python
from mcp.server import Server
import asyncio

server = Server("my-server")

@server.tool()
async def my_tool(param: str) -> str:
    return f"Result: {param}"
```

#### Java SDK
**Integration**: Spring AI

**Key Features:**
- Spring Framework integration
- Synchronous and asynchronous clients
- Dependency injection support
- Enterprise-ready

#### Kotlin SDK
**Key Features:**
- Coroutine-based async
- Idiomatic Kotlin API
- Multiplatform support

#### C# SDK
**Platform**: .NET

**Key Features:**
- .NET dependency injection
- Async/await patterns
- LINQ support
- Enterprise integration

### 7.2 SDK Selection Guidelines

**Choose TypeScript/JavaScript if:**
- Building for Node.js backend
- Need browser compatibility
- Want rapid development
- Prefer ecosystem familiarity

**Choose Python if:**
- Integrating with data science tools
- Working with AI/ML pipelines
- Prefer Python ecosystem
- Need scripting flexibility

**Choose Java/Kotlin if:**
- Building enterprise applications
- Need Spring ecosystem integration
- Require robust type safety
- Have existing Java infrastructure

**Choose C# if:**
- Building .NET applications
- Need Windows integration
- Prefer Microsoft ecosystem
- Want enterprise features

---

## 8. Building a Structurizr MCP Server

### 8.1 Recommended Architecture

```
structurizr-mcp/
├── src/
│   ├── index.ts                 # Server entry point
│   ├── server.ts                # MCP server setup
│   ├── tools/                   # Tool implementations
│   │   ├── workspace.ts         # Workspace management tools
│   │   ├── model.ts             # Model manipulation tools
│   │   ├── views.ts             # View creation tools
│   │   └── export.ts            # Export/import tools
│   ├── resources/               # Resource handlers
│   │   ├── workspaces.ts        # Workspace resources
│   │   └── elements.ts          # Element resources
│   ├── prompts/                 # Prompt templates
│   │   ├── analysis.ts          # Analysis prompts
│   │   └── generation.ts        # Generation prompts
│   ├── structurizr/             # Structurizr client
│   │   ├── client.ts            # API client
│   │   └── types.ts             # Type definitions
│   └── utils/                   # Utilities
│       ├── validation.ts        # Input validation
│       └── errors.ts            # Error handling
├── tests/                       # Test files
├── package.json
├── tsconfig.json
└── README.md
```

### 8.2 Core Tools to Implement

#### Workspace Management
- `create_workspace` - Create new workspace
- `get_workspace` - Retrieve workspace details
- `update_workspace` - Update workspace metadata
- `delete_workspace` - Delete workspace
- `list_workspaces` - List all workspaces

#### Model Management
- `add_person` - Add person to model
- `add_software_system` - Add software system
- `add_container` - Add container
- `add_component` - Add component
- `add_relationship` - Add relationship between elements
- `update_element` - Update element properties
- `delete_element` - Remove element

#### View Management
- `create_system_landscape_view` - Create landscape view
- `create_system_context_view` - Create context view
- `create_container_view` - Create container view
- `create_component_view` - Create component view
- `create_deployment_view` - Create deployment view
- `update_view_layout` - Update element positions

#### Documentation
- `add_documentation_section` - Add documentation
- `add_adr` - Add architecture decision record
- `get_documentation` - Retrieve documentation

#### Export/Import
- `export_workspace` - Export to JSON/DSL
- `import_workspace` - Import from JSON/DSL
- `export_diagram` - Export diagram as image

### 8.3 Core Resources to Implement

```typescript
// Workspace resources
workspace://{id}                       // Full workspace JSON
workspace://{id}/model                 // Model only
workspace://{id}/views                 // Views only
workspace://{id}/documentation         // Documentation
workspace://{id}/styles                // Styles

// Element resources
element://{workspace_id}/{element_id}  // Specific element
elements://{workspace_id}/people       // All people
elements://{workspace_id}/systems      // All systems
elements://{workspace_id}/containers   // All containers
elements://{workspace_id}/components   // All components

// Relationship resources
relationships://{workspace_id}         // All relationships
relationship://{workspace_id}/{rel_id} // Specific relationship

// View resources
view://{workspace_id}/{view_key}       // Specific view
```

### 8.4 Core Prompts to Implement

```typescript
// Analysis prompts
- "analyze_architecture"     // Analyze overall architecture
- "review_security"          // Security-focused review
- "assess_complexity"        // Complexity assessment
- "suggest_improvements"     // Improvement suggestions

// Generation prompts
- "generate_system_context"  // Generate context from description
- "create_c4_model"          // Create full C4 model
- "generate_documentation"   // Generate docs from model
- "suggest_views"            // Suggest useful views

// Migration prompts
- "modernize_architecture"   // Modernization suggestions
- "identify_microservices"   // Microservice boundaries
- "cloud_migration_plan"     // Cloud migration analysis
```

### 8.5 Implementation Example

```typescript
// src/index.ts
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  ListResourcesRequestSchema,
  ReadResourceRequestSchema,
  ListPromptsRequestSchema,
  GetPromptRequestSchema
} from "@modelcontextprotocol/sdk/types.js";

// Import handlers
import { workspaceTools } from "./tools/workspace.js";
import { modelTools } from "./tools/model.js";
import { workspaceResources } from "./resources/workspaces.js";
import { analysisPrompts } from "./prompts/analysis.js";

// Create server
const server = new Server(
  {
    name: "structurizr-server",
    version: "1.0.0"
  },
  {
    capabilities: {
      tools: {},
      resources: {},
      prompts: {}
    }
  }
);

// Register tool list handler
server.setRequestHandler(ListToolsRequestSchema, async () => ({
  tools: [
    ...workspaceTools.list(),
    ...modelTools.list()
  ]
}));

// Register tool call handler
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  // Route to appropriate handler
  if (name.startsWith("workspace_")) {
    return await workspaceTools.execute(name, args);
  }
  if (name.startsWith("model_")) {
    return await modelTools.execute(name, args);
  }

  throw new Error(`Unknown tool: ${name}`);
});

// Register resource handlers
server.setRequestHandler(ListResourcesRequestSchema, async () => ({
  resources: await workspaceResources.list()
}));

server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
  return await workspaceResources.read(request.params.uri);
});

// Register prompt handlers
server.setRequestHandler(ListPromptsRequestSchema, async () => ({
  prompts: analysisPrompts.list()
}));

server.setRequestHandler(GetPromptRequestSchema, async (request) => {
  return await analysisPrompts.get(request.params.name, request.params.arguments);
});

// Start server
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error("Structurizr MCP server running on stdio");
}

main().catch(console.error);
```

### 8.6 Integration with Structurizr

#### API Client Setup
```typescript
// src/structurizr/client.ts
import axios from "axios";

export class StructurizrClient {
  private apiKey: string;
  private apiSecret: string;
  private baseUrl: string;

  constructor(config: { apiKey: string; apiSecret: string; baseUrl?: string }) {
    this.apiKey = config.apiKey;
    this.apiSecret = config.apiSecret;
    this.baseUrl = config.baseUrl || "https://api.structurizr.com";
  }

  async getWorkspace(workspaceId: string) {
    // Implement Structurizr API authentication
    // Make GET request to /workspace/{id}
  }

  async putWorkspace(workspaceId: string, workspace: Workspace) {
    // Implement Structurizr API authentication
    // Make PUT request to /workspace/{id}
  }

  // Additional API methods...
}
```

### 8.7 Configuration

```typescript
// src/config.ts
export interface Config {
  structurizr: {
    apiKey: string;
    apiSecret: string;
    baseUrl?: string;
    workspaceId?: string;
  };
  server: {
    name: string;
    version: string;
  };
}

export function loadConfig(): Config {
  return {
    structurizr: {
      apiKey: process.env.STRUCTURIZR_API_KEY || "",
      apiSecret: process.env.STRUCTURIZR_API_SECRET || "",
      baseUrl: process.env.STRUCTURIZR_BASE_URL,
      workspaceId: process.env.STRUCTURIZR_WORKSPACE_ID
    },
    server: {
      name: "structurizr-mcp",
      version: "1.0.0"
    }
  };
}
```

### 8.8 Testing Strategy

```typescript
// tests/tools/workspace.test.ts
import { describe, it, expect } from "vitest";
import { workspaceTools } from "../../src/tools/workspace";

describe("Workspace Tools", () => {
  it("should create workspace with valid parameters", async () => {
    const result = await workspaceTools.execute("create_workspace", {
      name: "Test Workspace",
      description: "A test workspace"
    });

    expect(result.content).toBeDefined();
    expect(result.content[0].text).toContain("created");
  });

  it("should validate required parameters", async () => {
    await expect(
      workspaceTools.execute("create_workspace", {})
    ).rejects.toThrow("name is required");
  });
});
```

---

## 9. Key Takeaways for Implementation

### 9.1 Technical Requirements
✓ Use TypeScript for type safety
✓ Implement with @modelcontextprotocol/sdk
✓ Support stdio transport (and optionally HTTP/SSE)
✓ Follow JSON-RPC 2.0 protocol
✓ Validate all inputs with JSON Schema
✓ Handle errors gracefully
✓ Use async/await for all operations

### 9.2 Structurizr-Specific Considerations
✓ Authenticate with Structurizr API (API key + secret)
✓ Handle workspace locking/versioning
✓ Support C4 model hierarchy (Person → System → Container → Component)
✓ Respect relationship semantics
✓ Support all C4 view types
✓ Handle styling and theming
✓ Support documentation and ADRs
✓ Enable export in multiple formats

### 9.3 User Experience
✓ Clear, descriptive tool names and descriptions
✓ Comprehensive input schemas with examples
✓ Meaningful error messages
✓ Structured, parseable responses
✓ Support for common workflows via prompts
✓ Resource URIs that are intuitive and hierarchical

### 9.4 Production Readiness
✓ Environment-based configuration
✓ Comprehensive error handling
✓ Logging for debugging
✓ Input validation and sanitization
✓ Rate limiting consideration
✓ Connection pooling
✓ Graceful shutdown
✓ Health checks

---

## 10. Next Steps

### Phase 1: Foundation
1. Set up TypeScript project with MCP SDK
2. Implement basic server with stdio transport
3. Create Structurizr API client
4. Implement authentication

### Phase 2: Core Features
1. Implement workspace management tools
2. Implement model manipulation tools
3. Add workspace and element resources
4. Create basic analysis prompts

### Phase 3: Advanced Features
1. Add view management tools
2. Implement documentation tools
3. Add export/import capabilities
4. Create advanced prompts

### Phase 4: Polish
1. Add comprehensive tests
2. Improve error handling
3. Add logging and monitoring
4. Create documentation
5. Package for distribution

---

## Conclusion

The Model Context Protocol provides a standardized way to connect LLMs with external systems. For Structurizr, an MCP server enables AI assistants to:

- Create and modify C4 architecture models
- Generate diagrams and documentation
- Analyze architectural patterns
- Suggest improvements
- Automate common modeling tasks

By following the patterns and best practices outlined in this analysis, we can build a robust, production-ready MCP server that makes Structurizr accessible to AI-powered workflows.

The key is to focus on:
1. **Clear interfaces**: Well-defined tools, resources, and prompts
2. **Type safety**: Comprehensive schemas and validation
3. **Error handling**: Graceful failures with meaningful messages
4. **Documentation**: Clear descriptions for all capabilities
5. **Testing**: Comprehensive test coverage

This will enable seamless integration with Claude and other LLM-powered tools, making architecture modeling more accessible and efficient.
