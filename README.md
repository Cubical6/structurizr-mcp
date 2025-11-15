# Structurizr MCP Server

A [Model Context Protocol (MCP)](https://modelcontextprotocol.io) server for [Structurizr](https://structurizr.com), enabling AI assistants like Claude to create and manage C4 architecture diagrams as code.

## What is This?

This MCP server allows Large Language Models to:
- Create and manage Structurizr workspaces
- Build C4 architecture models (People, Systems, Containers, Components)
- Define relationships between architectural elements
- Generate system context, container, and component views
- Export to Structurizr DSL format

## Features

✨ **Workspace Management**
- Create, retrieve, list, and delete workspaces
- Local file-based storage

🏗️ **C4 Model Building**
- Add people, software systems, containers, and components
- Define relationships with technology and tags
- Hierarchical element organization

📊 **Views & Visualization**
- System context views
- Container views
- Component views
- Automatic layout support

📝 **Export Capabilities**
- Export to Structurizr DSL
- Future: PlantUML, Mermaid support

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

## Available Tools

### Workspace Management

- **`create_workspace`** - Create a new Structurizr workspace
- **`get_workspace`** - Retrieve workspace details (JSON or DSL format)
- **`list_workspaces`** - List all available workspaces
- **`delete_workspace`** - Delete a workspace
- **`export_to_dsl`** - Export workspace to Structurizr DSL

### Model Building

- **`add_person`** - Add a person (user, actor) to the model
- **`add_software_system`** - Add a software system
- **`add_container`** - Add a container to a system
- **`add_component`** - Add a component to a container
- **`add_relationship`** - Create a relationship between elements

### Views

- **`create_system_context_view`** - Create a system context diagram

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

## Roadmap

- [x] Basic workspace CRUD operations
- [x] C4 model element creation (Person, System, Container, Component)
- [x] Relationship management
- [x] System context views
- [ ] Container views (full implementation)
- [ ] Component views (full implementation)
- [ ] DSL parsing (import existing DSL)
- [ ] Export to PlantUML
- [ ] Export to Mermaid
- [ ] Structurizr Cloud API integration
- [ ] Documentation and ADR support
- [ ] Custom styling and themes
- [ ] Component discovery from code

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
