# Export Tools

- [Introduction](#introduction)
- [Export Formats Overview](#export-formats-overview)
    - [DSL Format](#dsl-format)
    - [PlantUML Format](#plantuml-format)
    - [Mermaid Format](#mermaid-format)
- [Export to DSL](#export-to-dsl)
    - [Basic Usage](#export-to-dsl-basic-usage)
    - [Response Format](#export-to-dsl-response-format)
    - [Use Cases](#export-to-dsl-use-cases)
- [Export to PlantUML](#export-to-plantuml)
    - [Basic Usage](#export-to-plantuml-basic-usage)
    - [Exporting Specific Views](#exporting-specific-views)
    - [Response Format](#export-to-plantuml-response-format)
    - [Use Cases](#export-to-plantuml-use-cases)
- [Export to Mermaid](#export-to-mermaid)
    - [Basic Usage](#export-to-mermaid-basic-usage)
    - [Exporting Specific Views](#exporting-specific-views-mermaid)
    - [Response Format](#export-to-mermaid-response-format)
    - [Use Cases](#export-to-mermaid-use-cases)
- [Import from DSL](#import-from-dsl)
    - [Basic Usage](#import-from-dsl-basic-usage)
    - [Response Format](#import-from-dsl-response-format)
    - [DSL Validation](#dsl-validation)
    - [Use Cases](#import-from-dsl-use-cases)
- [Integration with External Tools](#integration-with-external-tools)
    - [PlantUML Integration](#plantuml-integration)
    - [Mermaid Integration](#mermaid-integration)
    - [Version Control](#version-control)
- [Error Handling](#error-handling)
- [Best Practices](#best-practices)

## Introduction

The Export Tools provide comprehensive import and export capabilities for Structurizr workspaces, enabling seamless integration with various diagram formats and external tools. These tools allow you to:

- **Export** workspaces to different formats for visualization and documentation
- **Import** existing DSL definitions to create new workspaces
- **Share** architecture diagrams in universally supported formats
- **Integrate** with external documentation systems and rendering tools

All export operations preserve the complete structure of your workspace, including models, views, styles, and documentation. Import operations validate DSL syntax and automatically extract workspace metadata.

## Export Formats Overview

### DSL Format

The **Structurizr DSL** (Domain Specific Language) is the native text-based format for defining software architecture diagrams. DSL is:

- **Version-controllable**: Store in Git with meaningful diffs
- **Human-readable**: Easy to read, write, and review
- **Composable**: Support for includes and extensions
- **Portable**: Can be imported into any Structurizr-compatible tool

**When to use DSL:**
- Storing architecture definitions in version control
- Sharing complete workspace definitions
- Creating reusable architecture templates
- Collaborating on architecture design through code review

### PlantUML Format

**PlantUML** is a widely-adopted component for creating UML diagrams from text. PlantUML export:

- **Universal compatibility**: Supported by most documentation tools
- **Rendering options**: Multiple output formats (PNG, SVG, PDF)
- **Integration**: Works with Confluence, GitLab, GitHub, and more
- **Customization**: Extensive styling and theming options

**When to use PlantUML:**
- Embedding diagrams in wikis and documentation
- Generating images for presentations
- Integration with existing PlantUML workflows
- Automated diagram generation in CI/CD

### Mermaid Format

**Mermaid** is a JavaScript-based diagramming tool with native support in many platforms. Mermaid export:

- **Native GitHub/GitLab support**: Renders directly in markdown files
- **Interactive**: JavaScript-based rendering with tooltips and navigation
- **Modern**: Clean, contemporary diagram aesthetics
- **Lightweight**: No external rendering service required

**When to use Mermaid:**
- Documentation in GitHub/GitLab repositories
- Interactive web-based documentation
- Modern documentation platforms (Docusaurus, VuePress)
- Lightweight embedding without image generation

## Export to DSL

<a name="export-to-dsl-basic-usage"></a>
### Basic Usage

The `export_to_dsl` tool exports a workspace to Structurizr DSL format. This is the canonical representation of your workspace.

```javascript
// Export workspace to DSL
const result = await use_mcp_tool({
    server_name: "structurizr",
    tool_name: "export_to_dsl",
    arguments: {
        workspaceId: "my-ecommerce-platform"
    }
});
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `workspaceId` | string | Yes | The ID of the workspace to export (min length: 1) |

<a name="export-to-dsl-response-format"></a>
### Response Format

The tool returns the complete DSL definition along with workspace metadata:

```javascript
{
    workspaceId: "my-ecommerce-platform",
    name: "E-Commerce Platform",
    dsl: "workspace \"E-Commerce Platform\" \"Online retail system\" {\n\n    model {\n        user = person \"Customer\" \"A user of the e-commerce platform\"\n        \n        ecommerce = softwareSystem \"E-Commerce System\" \"Allows customers to browse and purchase products\" {\n            webapp = container \"Web Application\" \"Delivers content and handles user interactions\" \"React\"\n            api = container \"API Gateway\" \"Provides REST API\" \"Node.js\"\n            database = container \"Database\" \"Stores product and order data\" \"PostgreSQL\"\n        }\n        \n        user -> ecommerce \"Browses products and places orders\"\n        webapp -> api \"Makes API calls to\" \"HTTPS/JSON\"\n        api -> database \"Reads from and writes to\" \"SQL/TCP\"\n    }\n    \n    views {\n        systemContext ecommerce \"SystemContext\" {\n            include *\n            autoLayout\n        }\n        \n        container ecommerce \"Containers\" {\n            include *\n            autoLayout\n        }\n        \n        styles {\n            element \"Software System\" {\n                background #1168bd\n                color #ffffff\n            }\n            element \"Person\" {\n                shape person\n                background #08427b\n                color #ffffff\n            }\n        }\n    }\n}"
}
```

<a name="export-to-dsl-use-cases"></a>
### Use Cases

**1. Version Control Integration**

Export workspace DSL to commit to version control:

```javascript
// Export workspace
const { dsl } = await export_to_dsl({ workspaceId: "project-x" });

// Save to file (conceptual - MCP doesn't write files directly)
// File would be saved as: architecture/workspace.dsl
// Then committed to Git for version history
```

**2. Workspace Backup**

Create a complete backup of workspace definition:

```javascript
// Export multiple workspaces for backup
const workspaces = ["api-gateway", "data-pipeline", "web-app"];

for (const id of workspaces) {
    const backup = await export_to_dsl({ workspaceId: id });
    // Store backup.dsl with timestamp
}
```

**3. Template Creation**

Export a workspace to use as a template for new projects:

```javascript
// Export reference architecture
const template = await export_to_dsl({
    workspaceId: "microservices-template"
});

// Modify the DSL and import as new workspace
const modified = template.dsl.replace(
    "Microservices Template",
    "New Microservices Project"
);

await import_from_dsl({ dsl: modified });
```

**4. Documentation Generation**

Include DSL in documentation for transparency:

```javascript
// Export for documentation
const { name, dsl } = await export_to_dsl({
    workspaceId: "payment-service"
});

// Include in documentation as code block
// ```dsl
// ${dsl}
// ```
```

## Export to PlantUML

<a name="export-to-plantuml-basic-usage"></a>
### Basic Usage

The `export_to_plantuml` tool exports workspace views to PlantUML format for universal diagram rendering.

```javascript
// Export entire workspace to PlantUML
const result = await use_mcp_tool({
    server_name: "structurizr",
    tool_name: "export_to_plantuml",
    arguments: {
        workspaceId: "my-ecommerce-platform"
    }
});
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `workspaceId` | string | Yes | The ID of the workspace to export (min length: 1) |
| `viewKey` | string | No | Optional view key to export a specific view (min length: 1) |

<a name="exporting-specific-views"></a>
### Exporting Specific Views

Export a single view by specifying the view key:

```javascript
// Export only the system context view
const systemContext = await use_mcp_tool({
    server_name: "structurizr",
    tool_name: "export_to_plantuml",
    arguments: {
        workspaceId: "my-ecommerce-platform",
        viewKey: "SystemContext"
    }
});

// Export only the container view
const containers = await use_mcp_tool({
    server_name: "structurizr",
    tool_name: "export_to_plantuml",
    arguments: {
        workspaceId: "my-ecommerce-platform",
        viewKey: "Containers"
    }
});
```

<a name="export-to-plantuml-response-format"></a>
### Response Format

The tool returns PlantUML diagram syntax:

```javascript
{
    workspaceId: "my-ecommerce-platform",
    name: "E-Commerce Platform",
    viewKey: null,  // or specific view key if requested
    format: "plantuml",
    content: "@startuml\n!include https://raw.githubusercontent.com/plantuml-stdlib/C4-PlantUML/master/C4_Container.puml\n\nPerson(user, \"Customer\", \"A user of the e-commerce platform\")\n\nSystem_Boundary(ecommerce, \"E-Commerce System\") {\n    Container(webapp, \"Web Application\", \"React\", \"Delivers content and handles user interactions\")\n    Container(api, \"API Gateway\", \"Node.js\", \"Provides REST API\")\n    ContainerDb(database, \"Database\", \"PostgreSQL\", \"Stores product and order data\")\n}\n\nRel(user, webapp, \"Uses\", \"HTTPS\")\nRel(webapp, api, \"Makes API calls to\", \"HTTPS/JSON\")\nRel(api, database, \"Reads from and writes to\", \"SQL/TCP\")\n\n@enduml"
}
```

<a name="export-to-plantuml-use-cases"></a>
### Use Cases

**1. Confluence Integration**

Export PlantUML for embedding in Confluence pages:

```javascript
// Export system context for Confluence
const { content } = await export_to_plantuml({
    workspaceId: "payment-system",
    viewKey: "SystemContext"
});

// Wrap in Confluence PlantUML macro:
// {plantuml}
// ${content}
// {plantuml}
```

**2. Image Generation**

Generate PNG/SVG images using PlantUML server:

```javascript
// Export all views
const { content } = await export_to_plantuml({
    workspaceId: "microservices-arch"
});

// Send to PlantUML server for rendering
// POST to: https://www.plantuml.com/plantuml/svg/
// Body: content (encoded)
// Returns: SVG image
```

**3. GitLab/GitHub Wiki**

Include diagrams in repository wikis:

```javascript
// Export container diagram
const diagram = await export_to_plantuml({
    workspaceId: "api-platform",
    viewKey: "Containers"
});

// Include in wiki with PlantUML rendering:
// ```plantuml
// ${diagram.content}
// ```
```

**4. PDF Documentation**

Generate diagrams for PDF reports:

```javascript
// Export all architectural views
const views = ["SystemContext", "Containers", "Components"];

for (const viewKey of views) {
    const { content } = await export_to_plantuml({
        workspaceId: "enterprise-app",
        viewKey
    });

    // Render to PNG and include in PDF
    // Using PlantUML CLI or server
}
```

## Export to Mermaid

<a name="export-to-mermaid-basic-usage"></a>
### Basic Usage

The `export_to_mermaid` tool exports workspace views to Mermaid format for modern, interactive diagrams.

```javascript
// Export entire workspace to Mermaid
const result = await use_mcp_tool({
    server_name: "structurizr",
    tool_name: "export_to_mermaid",
    arguments: {
        workspaceId: "my-ecommerce-platform"
    }
});
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `workspaceId` | string | Yes | The ID of the workspace to export (min length: 1) |
| `viewKey` | string | No | Optional view key to export a specific view (min length: 1) |

<a name="exporting-specific-views-mermaid"></a>
### Exporting Specific Views

Export individual views for targeted documentation:

```javascript
// Export system landscape
const landscape = await use_mcp_tool({
    server_name: "structurizr",
    tool_name: "export_to_mermaid",
    arguments: {
        workspaceId: "enterprise-systems",
        viewKey: "SystemLandscape"
    }
});

// Export deployment diagram
const deployment = await use_mcp_tool({
    server_name: "structurizr",
    tool_name: "export_to_mermaid",
    arguments: {
        workspaceId: "enterprise-systems",
        viewKey: "DeploymentProduction"
    }
});
```

<a name="export-to-mermaid-response-format"></a>
### Response Format

The tool returns Mermaid diagram syntax:

```javascript
{
    workspaceId: "my-ecommerce-platform",
    name: "E-Commerce Platform",
    viewKey: "Containers",
    format: "mermaid",
    content: "graph TB\n  user[\"Customer<br/>[Person]<br/><br/>A user of the e-commerce platform\"]\n  \n  subgraph ecommerce[E-Commerce System]\n    webapp[\"Web Application<br/>[Container: React]<br/><br/>Delivers content and handles user interactions\"]\n    api[\"API Gateway<br/>[Container: Node.js]<br/><br/>Provides REST API\"]\n    database[(\"Database<br/>[Container: PostgreSQL]<br/><br/>Stores product and order data\")]\n  end\n  \n  user -->|Uses<br/>[HTTPS]| webapp\n  webapp -->|Makes API calls to<br/>[HTTPS/JSON]| api\n  api -->|Reads from and writes to<br/>[SQL/TCP]| database\n  \n  classDef person fill:#08427b,stroke:#052e56,color:#ffffff\n  classDef container fill:#1168bd,stroke:#0b4884,color:#ffffff\n  classDef database fill:#1168bd,stroke:#0b4884,color:#ffffff\n  \n  class user person\n  class webapp,api container\n  class database database"
}
```

<a name="export-to-mermaid-use-cases"></a>
### Use Cases

**1. GitHub README Documentation**

Embed diagrams directly in GitHub README files:

```javascript
// Export system context
const { content } = await export_to_mermaid({
    workspaceId: "open-source-project",
    viewKey: "SystemContext"
});

// Include in README.md:
// ## Architecture
//
// ```mermaid
// ${content}
// ```
```

**2. Docusaurus Documentation**

Include interactive diagrams in Docusaurus sites:

```javascript
// Export container view for docs
const diagram = await export_to_mermaid({
    workspaceId: "product-platform",
    viewKey: "Containers"
});

// Add to .mdx file in Docusaurus:
// ```mermaid
// ${diagram.content}
// ```
// Renders as interactive SVG automatically
```

**3. GitLab Merge Request Descriptions**

Document architecture changes in MRs:

```javascript
// Export updated architecture
const { content } = await export_to_mermaid({
    workspaceId: "microservices-refactor",
    viewKey: "ProposedArchitecture"
});

// Include in MR description:
// ## Proposed Architecture
//
// ```mermaid
// ${content}
// ```
```

**4. Notion/Obsidian Documentation**

Use in knowledge management tools with Mermaid support:

```javascript
// Export component diagram
const components = await export_to_mermaid({
    workspaceId: "authentication-service",
    viewKey: "Components"
});

// Paste into Notion page as code block with language: mermaid
// Renders inline without external dependencies
```

## Import from DSL

<a name="import-from-dsl-basic-usage"></a>
### Basic Usage

The `import_from_dsl` tool creates a new workspace by importing Structurizr DSL content. This enables workspace creation from external sources or templates.

```javascript
// Import workspace from DSL
const result = await use_mcp_tool({
    server_name: "structurizr",
    tool_name: "import_from_dsl",
    arguments: {
        dsl: `workspace "Payment Gateway" "Secure payment processing system" {

    model {
        customer = person "Customer" "Makes payments"
        merchant = person "Merchant" "Receives payments"

        paymentGateway = softwareSystem "Payment Gateway" "Processes secure payments" {
            api = container "Payment API" "REST API for payments" "Node.js"
            processor = container "Payment Processor" "Processes transactions" "Java"
            database = container "Transaction DB" "Stores transactions" "PostgreSQL"
        }

        externalBank = softwareSystem "Bank System" "External banking system" {
            tags "External"
        }

        customer -> paymentGateway "Makes payment using"
        merchant -> paymentGateway "Receives payments through"
        paymentGateway -> externalBank "Processes payments via"

        api -> processor "Sends transactions to"
        processor -> database "Stores in"
        processor -> externalBank "Authorizes with"
    }

    views {
        systemContext paymentGateway "SystemContext" {
            include *
            autoLayout
        }

        container paymentGateway "Containers" {
            include *
            autoLayout
        }

        styles {
            element "External" {
                background #999999
                color #ffffff
            }
        }
    }
}`
    }
});
```

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `dsl` | string | Yes | DSL content to import (min length: 1) |

<a name="import-from-dsl-response-format"></a>
### Response Format

The tool returns the created workspace details:

```javascript
{
    workspaceId: "payment-gateway-abc123",  // Auto-generated unique ID
    name: "Payment Gateway",                 // Extracted from DSL
    description: "Secure payment processing system",  // Extracted from DSL
    dsl: "workspace \"Payment Gateway\" \"Secure payment processing system\" { ... }"
}
```

**Automatic Metadata Extraction:**

The import tool automatically extracts:
- **Workspace name**: From the `workspace "Name"` declaration
- **Workspace description**: From the `workspace "Name" "Description"` declaration
- **Default values**: If not found in DSL, uses "Imported Workspace" as name

<a name="dsl-validation"></a>
### DSL Validation

The import process validates DSL syntax before creating the workspace:

```javascript
// Valid DSL - imports successfully
await import_from_dsl({
    dsl: 'workspace "Valid" { model { } views { } }'
});

// Invalid DSL - throws validation error
try {
    await import_from_dsl({
        dsl: 'workspace "Invalid" { model { invalid syntax } }'
    });
} catch (error) {
    // Error: "Invalid DSL: Unexpected token at line 1..."
}

// Empty DSL - throws error
try {
    await import_from_dsl({
        dsl: '   '
    });
} catch (error) {
    // Error: "DSL content cannot be empty"
}
```

<a name="import-from-dsl-use-cases"></a>
### Use Cases

**1. Template Instantiation**

Create new workspaces from predefined templates:

```javascript
// Load template DSL
const templateDsl = `workspace "Microservices Template" "Standard microservices architecture" {
    model {
        user = person "User"
        system = softwareSystem "System" {
            api = container "API Gateway" "Routes requests" "Kong"
            service = container "Service" "Business logic" "Spring Boot"
            db = container "Database" "Data storage" "PostgreSQL"
        }
    }
    views {
        systemContext system { include *; autoLayout }
        container system { include *; autoLayout }
    }
}`;

// Customize for new project
const customizedDsl = templateDsl
    .replace("Microservices Template", "Order Management System")
    .replace("Standard microservices architecture", "Handles customer orders");

// Import as new workspace
const workspace = await import_from_dsl({ dsl: customizedDsl });
```

**2. Migration from Files**

Import existing DSL files into the MCP server:

```javascript
// Read DSL from external source (conceptual)
const dslContent = `workspace "Legacy System" "Migrated architecture" {
    model {
        // ... existing model ...
    }
    views {
        // ... existing views ...
    }
}`;

// Import into MCP server
const migrated = await import_from_dsl({ dsl: dslContent });

console.log(`Migrated workspace: ${migrated.workspaceId}`);
```

**3. Workspace Cloning**

Clone an existing workspace with modifications:

```javascript
// Export existing workspace
const original = await export_to_dsl({
    workspaceId: "production-system"
});

// Modify for testing environment
const testDsl = original.dsl
    .replace('"Production System"', '"Test System"')
    .replace('"production"', '"test"');

// Import as new workspace
const testWorkspace = await import_from_dsl({ dsl: testDsl });
```

**4. Collaborative Design**

Import workspace definitions from team members:

```javascript
// Team member shares DSL via email/chat
const sharedDsl = `workspace "Proposed Design" "New feature architecture" {
    // ... team member's design ...
}`;

// Import for review and iteration
const proposal = await import_from_dsl({ dsl: sharedDsl });

// Review, modify, and export
const reviewed = await export_to_dsl({
    workspaceId: proposal.workspaceId
});
```

**5. Automated Workspace Generation**

Generate workspaces programmatically:

```javascript
// Function to generate workspace DSL
function generateServiceArchitecture(serviceName, technology) {
    return `workspace "${serviceName}" "Microservice architecture" {
        model {
            user = person "User"
            ${serviceName.toLowerCase()} = softwareSystem "${serviceName}" {
                api = container "API" "REST endpoints" "${technology}"
                db = container "Database" "Data store" "PostgreSQL"
            }
            user -> ${serviceName.toLowerCase()} "Uses"
        }
        views {
            systemContext ${serviceName.toLowerCase()} {
                include *
                autoLayout
            }
        }
    }`;
}

// Generate multiple service workspaces
const services = [
    { name: "User Service", tech: "Node.js" },
    { name: "Order Service", tech: "Java" },
    { name: "Payment Service", tech: "Go" }
];

for (const service of services) {
    const dsl = generateServiceArchitecture(service.name, service.tech);
    const workspace = await import_from_dsl({ dsl });
    console.log(`Created ${service.name}: ${workspace.workspaceId}`);
}
```

## Integration with External Tools

<a name="plantuml-integration"></a>
### PlantUML Integration

**Local Rendering with PlantUML CLI:**

```bash
# Export PlantUML from MCP
# Save content to diagram.puml

# Render to PNG
plantuml diagram.puml

# Render to SVG
plantuml -tsvg diagram.puml

# Render to PDF
plantuml -tpdf diagram.puml
```

**Online PlantUML Server:**

```javascript
// Export diagram
const { content } = await export_to_plantuml({
    workspaceId: "my-system"
});

// Encode for PlantUML server
const encoded = plantumlEncoder.encode(content);

// Generate URL
const imageUrl = `https://www.plantuml.com/plantuml/svg/${encoded}`;

// Use in HTML
// <img src="${imageUrl}" alt="Architecture Diagram" />
```

**Confluence Cloud:**

```javascript
// Export PlantUML
const diagram = await export_to_plantuml({
    workspaceId: "product-architecture",
    viewKey: "SystemContext"
});

// Insert in Confluence using PlantUML macro:
// {plantuml:title=System Context|theme=cerulean}
// ${diagram.content}
// {plantuml}
```

**VS Code Integration:**

```javascript
// Export to file
const { content } = await export_to_plantuml({
    workspaceId: "service-arch"
});

// Save as .puml file
// Use PlantUML VS Code extension for preview
// Extension: jebbs.plantuml
```

<a name="mermaid-integration"></a>
### Mermaid Integration

**GitHub/GitLab Markdown:**

```javascript
// Export Mermaid
const { content } = await export_to_mermaid({
    workspaceId: "api-platform",
    viewKey: "Containers"
});

// Create README.md:
// # API Platform Architecture
//
// ```mermaid
// ${content}
// ```
```

**Mermaid Live Editor:**

```javascript
// Export diagram
const diagram = await export_to_mermaid({
    workspaceId: "data-pipeline"
});

// Open in Mermaid Live Editor
// https://mermaid.live/
// Paste diagram.content for interactive editing
```

**Docusaurus:**

```javascript
// Export for Docusaurus
const { content } = await export_to_mermaid({
    workspaceId: "platform-docs",
    viewKey: "Components"
});

// In .mdx file:
// ```mermaid
// ${content}
// ```
// Renders automatically with @docusaurus/theme-mermaid
```

**Notion:**

```javascript
// Export diagram
const diagram = await export_to_mermaid({
    workspaceId: "team-workspace"
});

// In Notion:
// 1. Type /code
// 2. Select "Mermaid" as language
// 3. Paste diagram.content
// Renders inline in Notion page
```

<a name="version-control"></a>
### Version Control

**Git Workflow:**

```bash
# Directory structure
architecture/
├── workspace.dsl          # Exported DSL
├── diagrams/
│   ├── system-context.puml
│   ├── containers.puml
│   └── components.puml
└── images/
    ├── system-context.svg
    └── containers.svg
```

```javascript
// Export DSL for version control
const workspace = await export_to_dsl({
    workspaceId: "production-system"
});

// Save to architecture/workspace.dsl
// Commit with meaningful message:
// git add architecture/workspace.dsl
// git commit -m "feat: add payment service to architecture"

// Export views for documentation
const systemContext = await export_to_plantuml({
    workspaceId: "production-system",
    viewKey: "SystemContext"
});

// Save to architecture/diagrams/system-context.puml
```

**CI/CD Integration:**

```yaml
# .github/workflows/architecture.yml
name: Architecture Diagrams

on:
  push:
    paths:
      - 'architecture/workspace.dsl'

jobs:
  generate-diagrams:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup PlantUML
        run: |
          apt-get update
          apt-get install -y plantuml

      - name: Generate SVG diagrams
        run: |
          plantuml -tsvg architecture/diagrams/*.puml
          mv architecture/diagrams/*.svg architecture/images/

      - name: Commit diagrams
        run: |
          git add architecture/images/
          git commit -m "chore: update architecture diagrams"
          git push
```

## Error Handling

All export and import tools provide comprehensive error handling:

**Workspace Not Found:**

```javascript
try {
    await export_to_dsl({ workspaceId: "nonexistent" });
} catch (error) {
    // Error: "Workspace not found: nonexistent"
}
```

**Invalid DSL Syntax:**

```javascript
try {
    await import_from_dsl({
        dsl: 'workspace "Bad" { invalid }'
    });
} catch (error) {
    // Error: "Invalid DSL: Unexpected token 'invalid' at line 1"
}
```

**Empty DSL Content:**

```javascript
try {
    await import_from_dsl({ dsl: '' });
} catch (error) {
    // Error: "DSL content cannot be empty"
}
```

**Export Format Errors:**

```javascript
try {
    await export_to_plantuml({
        workspaceId: "broken-workspace"
    });
} catch (error) {
    // Error: "Failed to export workspace to PlantUML: [specific error]"
}
```

**General Exception Handling:**

```javascript
// Robust error handling
async function exportWorkspaceSafely(workspaceId) {
    try {
        const dsl = await export_to_dsl({ workspaceId });
        const plantuml = await export_to_plantuml({ workspaceId });
        const mermaid = await export_to_mermaid({ workspaceId });

        return { dsl, plantuml, mermaid };
    } catch (error) {
        console.error(`Export failed: ${error.message}`);

        // Fallback or retry logic
        return null;
    }
}
```

## Best Practices

**1. Always Export DSL for Backup**

Maintain DSL as the source of truth:

```javascript
// Export DSL after significant changes
async function saveWorkspaceBackup(workspaceId) {
    const { dsl, name } = await export_to_dsl({ workspaceId });

    // Save with timestamp
    const timestamp = new Date().toISOString();
    const filename = `${workspaceId}_${timestamp}.dsl`;

    // Store in version control
    return { filename, content: dsl };
}
```

**2. Export Specific Views for Documentation**

Only export needed views to reduce payload:

```javascript
// Export only required views
const documentationDiagrams = await Promise.all([
    export_to_mermaid({
        workspaceId: "system-x",
        viewKey: "SystemContext"
    }),
    export_to_mermaid({
        workspaceId: "system-x",
        viewKey: "Containers"
    })
]);
```

**3. Validate DSL Before Import**

Always validate DSL syntax before importing:

```javascript
async function importWithValidation(dsl) {
    // Check for basic structure
    if (!dsl.includes('workspace')) {
        throw new Error('DSL must contain workspace declaration');
    }

    if (!dsl.includes('model')) {
        throw new Error('DSL must contain model section');
    }

    // Import if validation passes
    return await import_from_dsl({ dsl });
}
```

**4. Use Appropriate Format for Use Case**

Choose the right export format:

```javascript
// Decision matrix
async function exportForPurpose(workspaceId, purpose) {
    switch (purpose) {
        case 'version-control':
            return await export_to_dsl({ workspaceId });

        case 'confluence':
            return await export_to_plantuml({ workspaceId });

        case 'github-readme':
            return await export_to_mermaid({ workspaceId });

        case 'presentation':
            // PlantUML for high-quality images
            return await export_to_plantuml({ workspaceId });

        default:
            throw new Error(`Unknown purpose: ${purpose}`);
    }
}
```

**5. Handle Large Workspaces Efficiently**

For workspaces with many views, export selectively:

```javascript
// Export large workspace view by view
async function exportLargeWorkspace(workspaceId) {
    // Get workspace metadata first
    const { name } = await export_to_dsl({ workspaceId });

    // Export views individually
    const views = ['SystemContext', 'Containers', 'Components', 'Deployment'];
    const exports = {};

    for (const viewKey of views) {
        try {
            exports[viewKey] = await export_to_mermaid({
                workspaceId,
                viewKey
            });
        } catch (error) {
            console.warn(`Could not export ${viewKey}: ${error.message}`);
        }
    }

    return exports;
}
```

**6. Maintain Format Consistency**

Use consistent formats across your team:

```javascript
// Team standard: Mermaid for docs, PlantUML for presentations
const TEAM_STANDARDS = {
    documentation: 'mermaid',
    presentations: 'plantuml',
    backup: 'dsl'
};

async function exportByStandard(workspaceId, standard) {
    const format = TEAM_STANDARDS[standard];

    switch (format) {
        case 'mermaid':
            return await export_to_mermaid({ workspaceId });
        case 'plantuml':
            return await export_to_plantuml({ workspaceId });
        case 'dsl':
            return await export_to_dsl({ workspaceId });
    }
}
```

**7. Automate Regular Exports**

Set up automated exports for critical workspaces:

```javascript
// Automated daily export
async function dailyArchitectureBackup() {
    const criticalWorkspaces = [
        'production-system',
        'payment-gateway',
        'user-service'
    ];

    const backups = [];

    for (const workspaceId of criticalWorkspaces) {
        try {
            const backup = await export_to_dsl({ workspaceId });
            backups.push({
                workspace: workspaceId,
                timestamp: new Date().toISOString(),
                dsl: backup.dsl
            });
        } catch (error) {
            console.error(`Backup failed for ${workspaceId}: ${error.message}`);
        }
    }

    return backups;
}
```

---

**Related Documentation:**
- [Workspace Tools](/docs/tools/workspace-tools.md) - Creating and managing workspaces
- [View Tools](/docs/tools/view-tools.md) - Creating views for export
- [Model Tools](/docs/tools/model-tools.md) - Building models to export
- [Resources](/docs/resources/README.md) - Accessing workspace data via resources
