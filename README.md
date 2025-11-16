# Structurizr MCP Server

[![Production Ready](https://img.shields.io/badge/status-production%20ready-brightgreen.svg)](https://github.com/Cubical6/structurizr-mcp)
[![PHP 8.1+](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://www.php.net/)
[![Tests](https://img.shields.io/badge/tests-355%20passing-brightgreen.svg)](https://github.com/Cubical6/structurizr-mcp)
[![Coverage](https://img.shields.io/badge/coverage-%3E95%25-brightgreen.svg)](https://github.com/Cubical6/structurizr-mcp)

A [Model Context Protocol (MCP)](https://modelcontextprotocol.io) server for [Structurizr](https://structurizr.com), enabling AI assistants like Claude to create and manage C4 architecture diagrams as code.

**✅ Production Ready** - All features implemented, tested (>95% coverage), and ready for use.

## What is This?

This MCP server allows Large Language Models to:
- Create and manage Structurizr workspaces
- Build C4 architecture models (People, Systems, Containers, Components)
- Define relationships between architectural elements
- Generate system context, container, and component views
- Export to Structurizr DSL format

## Features

✨ **23 MCP Tools** - Complete workspace and model management
- **Workspace Management** (4 tools): Create, retrieve, list, delete
- **C4 Model Building** (5 tools): Add people, systems, containers, components, relationships
- **Views & Visualization** (5 tools): System context, container, component, dynamic views, auto-layout
- **Documentation** (2 tools): Add documentation sections and ADRs
- **Export/Import** (4 tools): DSL, PlantUML, Mermaid formats
- **Analysis** (3 tools): Dependency analysis, element search, workspace validation

🔍 **7 MCP Resources** - URI-based data access
- Static configuration endpoint
- Workspace, model, and views retrieval
- Element and view-specific queries
- DSL representation access

💬 **7 MCP Prompts** - LLM-guided assistance
- **Analysis**: Architecture review, security analysis, improvement suggestions
- **Generation**: System context, full C4 models, examples, C4 explanations

🏗️ **Full C4 Model Support**
- All four C4 levels: System Context, Container, Component, Code
- Dynamic views for runtime behavior
- Hierarchical element organization
- Complete relationship management

📝 **Multiple Export Formats**
- Structurizr DSL (native format)
- PlantUML diagrams
- Mermaid diagrams
- Workspace validation

🔒 **Production Quality**
- >95% test coverage (355 tests passing)
- PHPStan Level 8 compliance (maximum static analysis)
- PSR-12 code style compliance
- Comprehensive error handling and security

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- Claude Desktop (or another MCP-compatible client)

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/Cubical6/structurizr-mcp.git
   cd structurizr-mcp
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment (optional)**
   ```bash
   cp .env.example .env
   # Edit .env if you need to customize paths or logging
   ```

4. **Configure Claude Desktop**

   Add to your Claude Desktop MCP settings file:

   **macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`
   **Windows**: `%APPDATA%\Claude\claude_desktop_config.json`

   ```json
   {
     "mcpServers": {
       "structurizr": {
         "command": "php",
         "args": ["/absolute/path/to/structurizr-mcp/server.php"]
       }
     }
   }
   ```

5. **Restart Claude Desktop**

## Available Tools (23 Total)

### Workspace Management (4 tools)

- **`create_workspace`** - Create a new Structurizr workspace
- **`get_workspace`** - Retrieve workspace details (JSON or DSL format)
- **`list_workspaces`** - List all available workspaces
- **`delete_workspace`** - Delete a workspace

### Model Building (5 tools)

- **`add_person`** - Add a person (user, actor) to the model
- **`add_software_system`** - Add a software system
- **`add_container`** - Add a container to a system
- **`add_component`** - Add a component to a container
- **`add_relationship`** - Create a relationship between elements

### Views (5 tools)

- **`create_system_context_view`** - Create a system context diagram
- **`create_container_view`** - Create a container diagram
- **`create_component_view`** - Create a component diagram
- **`create_dynamic_view`** - Create a dynamic diagram (runtime behavior)
- **`apply_auto_layout`** - Apply automatic layout to a view

### Documentation (2 tools)

- **`add_documentation_section`** - Add a documentation section
- **`add_adr`** - Add an Architecture Decision Record

### Export/Import (4 tools)

- **`export_to_dsl`** - Export workspace to Structurizr DSL
- **`export_to_plantuml`** - Export view to PlantUML
- **`export_to_mermaid`** - Export view to Mermaid
- **`import_from_dsl`** - Import workspace from DSL

### Analysis (3 tools)

- **`analyze_dependencies`** - Analyze element dependencies
- **`find_element`** - Find elements by name
- **`validate_workspace`** - Validate workspace structure

## Available Resources (7 Total)

### Static Resources

- **`structurizr://config`** - Server configuration and status

### Workspace Resources

- **`structurizr://workspace/{workspaceId}`** - Full workspace data
- **`structurizr://workspace/{workspaceId}/model`** - Model elements only
- **`structurizr://workspace/{workspaceId}/views`** - View definitions only
- **`structurizr://workspace/{workspaceId}/dsl`** - DSL representation

### Element & View Resources

- **`structurizr://workspace/{workspaceId}/element/{elementId}`** - Specific element data
- **`structurizr://workspace/{workspaceId}/view/{viewKey}`** - Specific view data

## Available Prompts (7 Total)

### Analysis Prompts (3 prompts)

- **`analyze_architecture`** - Comprehensive architecture analysis with 7-point framework
- **`review_security`** - Security review with 6-point checklist
- **`suggest_improvements`** - Improvement suggestions with customizable focus areas

### Generation Prompts (4 prompts)

- **`generate_system_context`** - Generate C4 system context diagram from description
- **`create_from_description`** - Create complete multi-level C4 model (6-phase process)
- **`explain_c4_model`** - Comprehensive C4 model explanation with examples
- **`create_example_workspace`** - Generate example workspaces (e-commerce, microservices, monolith, SaaS)

## Usage Examples

### Example 1: Simple E-commerce System

```
Claude, help me create a C4 model for an e-commerce system:

1. Create a workspace called "E-commerce Platform"
2. Add a customer (person)
3. Add an e-commerce system
4. Add a payment gateway (external system)
5. Create relationships between them
6. Generate a system context view
7. Export the DSL
```

Claude will use the MCP tools to:
- Create the workspace
- Add all elements
- Define relationships
- Generate the view
- Provide the complete DSL

### Example 2: Microservices Architecture

```
Create a microservices architecture model:
- API Gateway system with these containers:
  - Web API (Spring Boot)
  - Redis Cache
- User Service with PostgreSQL database
- Order Service with MongoDB database
- Add relationships showing the data flow
```

### Example 3: Inspect Existing Workspace

```
Show me all workspaces, then export the DSL for workspace ID ws_abc123
```

## Architecture

```
structurizr-mcp/
├── server.php              # MCP server entry point
├── src/
│   ├── Configuration.php   # Environment configuration
│   ├── Tools/             # MCP tool implementations
│   │   ├── WorkspaceTools.php
│   │   └── ModelTools.php
│   ├── Structurizr/       # Core domain logic
│   │   ├── Workspace.php
│   │   ├── WorkspaceManager.php
│   │   └── DslBuilder.php
│   └── Exception/         # Custom exceptions
├── workspaces/            # Local workspace storage
├── cache/                 # Discovery cache
└── sessions/              # Session storage
```

## Development

### Run Tests
```bash
composer test
```

### Static Analysis
```bash
composer stan
```

### Code Style
```bash
composer cs-fix
```

## Implementation Status

### ✅ Core Features - Complete

- [x] Workspace CRUD operations (create, read, update, delete)
- [x] C4 model element creation (Person, System, Container, Component)
- [x] Relationship management with technology and tags
- [x] System context, container, and component views
- [x] Dynamic views for runtime behavior
- [x] Auto-layout support
- [x] DSL export and import
- [x] Export to PlantUML and Mermaid
- [x] Documentation sections and ADRs
- [x] Dependency analysis and validation
- [x] MCP Resources (7 endpoints)
- [x] MCP Prompts (7 analysis and generation prompts)

### 🔵 Future Enhancements (Optional)

- [ ] Structurizr Cloud push/pull integration
- [ ] Custom styling and themes
- [ ] Component discovery from code
- [ ] Batch operations
- [ ] HTTP transport support

## Contributing

Contributions welcome! Please see [CLAUDE.md](./CLAUDE.md) for project documentation and [TASKS.md](./TASKS.md) for the implementation roadmap.

## License

MIT License - see [LICENSE](./LICENSE) for details.

## Resources

- [Model Context Protocol](https://modelcontextprotocol.io)
- [Structurizr](https://structurizr.com)
- [C4 Model](https://c4model.com)
- [Structurizr DSL](https://github.com/structurizr/dsl)

## Support

- 🐛 [Report Issues](https://github.com/Cubical6/structurizr-mcp/issues)
- 📚 [Documentation](./CLAUDE.md)
- 💬 Questions? Open a GitHub Discussion

---

Built with ❤️ using the [PHP MCP SDK](https://github.com/modelcontextprotocol/php-sdk)
