# Tools Overview

- [Introduction](#introduction)
- [What Are MCP Tools?](#what-are-mcp-tools)
- [Quick Reference](#quick-reference)
- [Tools by Category](#tools-by-category)
- [Common Workflows](#common-workflows)
- [Detailed Tool Documentation](#detailed-tool-documentation)
- [Choosing the Right Tool](#choosing-the-right-tool)
- [Next Steps](#next-steps)

---

## Introduction

The Structurizr MCP Server provides **23 powerful tools** organized into **6 logical categories** that work together to help you create, manage, and analyze C4 architecture models. Whether you're building your first diagram or working with complex enterprise architectures, these tools provide everything you need.

This overview will help you understand:

- What each tool does
- When to use each tool
- How tools work together in workflows
- Where to find detailed documentation for each tool

> **Tip:** You don't need to memorize all 23 tools. Start with the basic workflow and expand as needed. Claude can help you find the right tool for any architecture task.

---

## What Are MCP Tools?

MCP (Model Context Protocol) tools are **executable actions** that your Claude instance can perform when you ask for them. When you have the Structurizr MCP Server configured, you can ask Claude to use these tools through natural language.

### How It Works

When you ask Claude something like **"Create a workspace for our e-commerce platform,"** Claude:

1. **Understands** your request and determines which tool to use
2. **Prepares** the tool with your parameters
3. **Executes** the tool through the MCP Server
4. **Returns** the result back to you
5. **Continues** the conversation with the result

You never have to manually call tools—Claude handles everything naturally.

### Example

**You say:** "Create a workspace called 'My Architecture' and add the Customer person"

**Claude does:**
- Calls `create_workspace` with name "My Architecture"
- Uses the returned workspace ID
- Calls `add_person` to add the Customer

**You get:** A ready-to-use workspace with your first element

> **Note:** All tool calls go through Claude. You can see what tools Claude is using in the conversation, but you don't need to interact with them directly.

---

## Quick Reference

This table shows all 23 tools at a glance:

| # | Tool | Category | Purpose |
|---|------|----------|---------|
| 1 | `create_workspace` | Workspace | Create a new workspace |
| 2 | `get_workspace` | Workspace | Retrieve workspace data |
| 3 | `list_workspaces` | Workspace | List all workspaces |
| 4 | `delete_workspace` | Workspace | Delete a workspace |
| 5 | `add_person` | Model | Add a person/user to the model |
| 6 | `add_software_system` | Model | Add a software system |
| 7 | `add_container` | Model | Add a container to a system |
| 8 | `add_component` | Model | Add a component to a container |
| 9 | `add_relationship` | Model | Create relationships between elements |
| 10 | `create_system_context_view` | Views | Create system context diagram (C4 Level 1) |
| 11 | `create_container_view` | Views | Create container diagram (C4 Level 2) |
| 12 | `create_component_view` | Views | Create component diagram (C4 Level 3) |
| 13 | `create_dynamic_view` | Views | Create dynamic/sequence diagram |
| 14 | `apply_auto_layout` | Views | Apply automatic layout to views |
| 15 | `add_documentation_section` | Documentation | Add documentation section |
| 16 | `add_adr` | Documentation | Add Architecture Decision Record |
| 17 | `export_to_dsl` | Export | Export workspace to Structurizr DSL |
| 18 | `export_to_plantuml` | Export | Export to PlantUML format |
| 19 | `export_to_mermaid` | Export | Export to Mermaid format |
| 20 | `import_from_dsl` | Import | Import workspace from DSL |
| 21 | `validate_workspace` | Analysis | Validate workspace structure |
| 22 | `find_element` | Analysis | Search for elements by name/type |
| 23 | `analyze_dependencies` | Analysis | Analyze element dependencies |

---

## Tools by Category

### Workspace Management (4 tools)

Workspace management tools handle creating, retrieving, and deleting workspaces—the containers that hold your entire architecture model.

#### `create_workspace`
**Creates** a new workspace with a name and description.

**When to use:** At the start of any new architecture project or when you need a fresh workspace for experimentation.

**Related:** [Workspace Tools Documentation](workspace-tools.md#create-workspace)

#### `get_workspace`
**Retrieves** all data for a specific workspace, including model elements and views.

**When to use:** To fetch complete workspace data, verify contents, or restore workspace information.

**Related:** [Workspace Tools Documentation](workspace-tools.md#get-workspace)

#### `list_workspaces`
**Lists** all available workspaces with basic information about each.

**When to use:** To see what workspaces exist, find a specific workspace ID, or manage multiple projects.

**Related:** [Workspace Tools Documentation](workspace-tools.md#list-workspaces)

#### `delete_workspace`
**Removes** a workspace and all its contents permanently.

**When to use:** To clean up test workspaces or remove completed projects.

> **Warning:** Workspace deletion is permanent. The workspace and all data cannot be recovered.

**Related:** [Workspace Tools Documentation](workspace-tools.md#delete-workspace)

---

### Model Building (5 tools)

Model building tools let you construct the actual architecture model by adding elements (people, systems, containers, components) and defining relationships between them.

#### `add_person`
**Adds** a person or user role to the model.

**When to use:** To represent external users, customers, administrators, or any human actor in your system.

**Syntax:** `add_person(workspace_id, name, description, tags?)`

**Example:** Customer, Admin, External API Consumer

**Related:** [Model Tools Documentation](model-tools.md#add-person)

#### `add_software_system`
**Adds** a software system to the model.

**When to use:** To represent applications, services, or complete software systems (e.g., "E-commerce System", "Payment Gateway").

**Syntax:** `add_software_system(workspace_id, name, description, tags?)`

**Example:** E-commerce Platform, CRM System, Identity Service

**Related:** [Model Tools Documentation](model-tools.md#add-software-system)

#### `add_container`
**Adds** a container (deployable unit) within a software system.

**When to use:** To show the technology choices and deployment architecture within a system (e.g., Web App, Database, API Server).

**Syntax:** `add_container(system_id, name, description, technology?, tags?)`

**Example:** React Web App, PostgreSQL Database, Node.js API

**Related:** [Model Tools Documentation](model-tools.md#add-container)

#### `add_component`
**Adds** a component (logical grouping) within a container.

**When to use:** To break down a container into logical, reusable pieces (e.g., Controllers, Services, Repositories).

**Syntax:** `add_component(container_id, name, description, technology?, tags?)`

**Example:** Authentication Controller, Product Service, User Repository

**Related:** [Model Tools Documentation](model-tools.md#add-component)

#### `add_relationship`
**Creates** a relationship (connection) between any two elements.

**When to use:** To show how elements interact, depend on each other, or communicate.

**Syntax:** `add_relationship(source_id, dest_id, description, technology?, tags?)`

**Example:** "Customer uses E-commerce System", "API calls Database using SQL"

> **Tip:** Relationships are the connectors that make your architecture meaningful. They show dependencies, data flow, and interactions.

**Related:** [Model Tools Documentation](model-tools.md#add-relationship)

---

### Views (5 tools)

View tools create different levels of C4 diagrams and apply visual layout rules to them.

#### `create_system_context_view`
**Creates** a C4 Level 1 System Context view showing your system and its external dependencies.

**When to use:** To provide the highest-level, big-picture view of your architecture.

**Scope:** Shows the system, people, and external systems—nothing inside the system.

**Related:** [View Tools Documentation](view-tools.md#create-system-context-view)

#### `create_container_view`
**Creates** a C4 Level 2 Container view showing the technology decisions and deployment architecture.

**When to use:** To show how your system is decomposed into deployable units (web apps, databases, services).

**Scope:** Shows containers within the system and external systems it connects to.

**Related:** [View Tools Documentation](view-tools.md#create-container-view)

#### `create_component_view`
**Creates** a C4 Level 3 Component view showing the logical structure of a single container.

**When to use:** To explain the design of a specific container, showing its internal components.

**Scope:** Shows components within a container and external dependencies.

**Related:** [View Tools Documentation](view-tools.md#create-component-view)

#### `create_dynamic_view`
**Creates** a dynamic/sequence view showing how elements interact at runtime.

**When to use:** To show the sequence of interactions, user flows, or transaction patterns.

**Scope:** Shows interactions and message flows between elements.

**Related:** [View Tools Documentation](view-tools.md#create-dynamic-view)

#### `apply_auto_layout`
**Applies** automatic layout to a view, positioning elements automatically.

**When to use:** After creating a view, to automatically arrange elements in a readable layout.

**Options:** `TopBottom`, `BottomTop`, `LeftRight`, `RightLeft`

> **Tip:** Auto-layout saves time and creates professional-looking diagrams. Most views benefit from automatic layout.

**Related:** [View Tools Documentation](view-tools.md#apply-auto-layout)

---

### Documentation (2 tools)

Documentation tools help you document your architecture decisions and add supplementary information.

#### `add_documentation_section`
**Adds** a documentation section to the workspace.

**When to use:** To document design decisions, architecture principles, deployment procedures, or any narrative documentation.

**Syntax:** `add_documentation_section(workspace_id, title, content)`

**Example:** "Deployment Guide", "Architecture Decision Log", "System Design Rationale"

**Related:** [Documentation Tools Documentation](documentation-tools.md#add-documentation-section)

#### `add_adr`
**Adds** an Architecture Decision Record (ADR) documenting a specific architectural decision.

**When to use:** To capture why important decisions were made, along with context, consequences, and status.

**Syntax:** `add_adr(workspace_id, id, date, title, status, content)`

**Example:** "ADR-001: Use microservices architecture", "ADR-002: PostgreSQL for primary database"

> **Tip:** ADRs are lightweight but powerful. They help future developers understand the "why" behind architectural choices.

**Related:** [Documentation Tools Documentation](documentation-tools.md#add-adr)

---

### Export/Import (4 tools)

Export and import tools let you move your architecture model between formats and systems.

#### `export_to_dsl`
**Exports** your complete workspace as Structurizr DSL—a text-based format perfect for version control.

**When to use:** To save your architecture as code, enable version control, or transfer to other systems.

**Output:** Complete `.dsl` file with model, views, and styles.

> **Benefit:** DSL files can be committed to git, reviewed in pull requests, and treated like any other code.

**Related:** [Export Tools Documentation](export-tools.md#export-to-dsl)

#### `export_to_plantuml`
**Exports** a specific view as PlantUML format.

**When to use:** To generate PlantUML diagrams for embedding in documentation or generating PNG/SVG files.

**Output:** PlantUML diagram syntax for a single view.

**Related:** [Export Tools Documentation](export-tools.md#export-to-plantuml)

#### `export_to_mermaid`
**Exports** a specific view as Mermaid format.

**When to use:** To generate Mermaid diagrams that can be rendered directly in Markdown files on GitHub, GitLab, etc.

**Output:** Mermaid diagram syntax for a single view.

**Related:** [Export Tools Documentation](export-tools.md#export-to-mermaid)

#### `import_from_dsl`
**Imports** a workspace from Structurizr DSL format.

**When to use:** To load an existing DSL file, restore a workspace from version control, or migrate from another system.

**Input:** Complete DSL file content.

> **Tip:** This creates a perfect round-trip: export to DSL, edit, and import back.

**Related:** [Export Tools Documentation](export-tools.md#import-from-dsl)

---

### Analysis (3 tools)

Analysis tools help you understand, validate, and improve your architecture model.

#### `validate_workspace`
**Validates** your workspace for structural correctness and consistency.

**When to use:** To check for errors like missing relationships, orphaned elements, or invalid configurations before exporting.

**Returns:** Validation status and any issues found.

**Related:** [Analysis Tools Documentation](analysis-tools.md#validate-workspace)

#### `find_element`
**Searches** for elements by name or type.

**When to use:** To locate elements when you're not sure of their exact ID, useful in large workspaces.

**Syntax:** `find_element(workspace_id, name)`

**Returns:** Element details including ID, type, and relationships.

> **Tip:** If you forget an element's ID, use this tool to search by name.

**Related:** [Analysis Tools Documentation](analysis-tools.md#find-element)

#### `analyze_dependencies`
**Analyzes** the dependencies between elements.

**When to use:** To understand the dependency graph, identify circular dependencies, or find tightly coupled components.

**Options:** Analyze all dependencies or focus on a specific element.

**Returns:** Dependency relationships, impact analysis, and insights.

> **Benefit:** Helps identify architectural issues like tight coupling or complex dependencies.

**Related:** [Analysis Tools Documentation](analysis-tools.md#analyze-dependencies)

---

## Common Workflows

### Workflow 1: Create a Basic C4 Model (5 minutes)

**Goal:** Create a system context diagram with minimal effort.

```
1. create_workspace("My System", "System architecture")
   → Get workspace_id

2. add_person(workspace_id, "User", "End user")
   → Get person_id

3. add_software_system(workspace_id, "System", "Main system")
   → Get system_id

4. add_relationship(person_id, system_id, "Uses the system")

5. create_system_context_view(system_id, "Context")

6. apply_auto_layout("Context", "LeftRight")

Result: Complete system context diagram ready to view
```

**Tools used:** 6 tools
**Time:** ~5 minutes
**Output:** System context view

**Learn more:** [Quick Start Guide](../getting-started/quick-start.md)

---

### Workflow 2: Build a Multi-Level C4 Model (20 minutes)

**Goal:** Create a complete architecture with system context, container, and component views.

```
1. create_workspace("E-commerce", "Complete e-commerce architecture")

2. [Add People]
   - add_person(workspace_id, "Customer", "Shops online")
   - add_person(workspace_id, "Admin", "Manages inventory")

3. [Add Software Systems]
   - add_software_system(workspace_id, "E-commerce System", "Main platform")
   - add_software_system(workspace_id, "Email Service", "Sends emails")
   - add_software_system(workspace_id, "Payment Gateway", "Processes payments")

4. [Add Relationships]
   - add_relationship(customer, ecommerce, "Uses")
   - add_relationship(ecommerce, payment, "Calls")
   - add_relationship(ecommerce, email, "Uses")

5. [Create System Context View]
   - create_system_context_view(ecommerce, "SystemContext")
   - apply_auto_layout("SystemContext", "TopBottom")

6. [Add Containers]
   - add_container(ecommerce, "Web App", "Browser-based", "React/TypeScript")
   - add_container(ecommerce, "API", "REST API", "Node.js/Express")
   - add_container(ecommerce, "Database", "Data store", "PostgreSQL")

7. [Add Container Relationships]
   - add_relationship(webapp, api, "Calls")
   - add_relationship(api, database, "Uses")

8. [Create Container View]
   - create_container_view(ecommerce, "Containers")
   - apply_auto_layout("Containers", "LeftRight")

9. [Export]
   - export_to_dsl(workspace_id)

Result: Complete multi-level architecture with multiple views
```

**Tools used:** 14 tools
**Time:** ~20 minutes
**Output:** System context and container views + DSL

**Learn more:** [E-Commerce Example](../examples/ecommerce.md)

---

### Workflow 3: Build and Document Architecture (30 minutes)

**Goal:** Create a complete architecture with detailed documentation.

```
[Steps 1-9 from Workflow 2]

10. [Add Documentation]
    - add_documentation_section(workspace_id, "Overview", "System overview...")
    - add_documentation_section(workspace_id, "Deployment", "Deployment guide...")

11. [Add ADRs]
    - add_adr(workspace_id, "001", "2024-01-15", "Use Microservices", "Accepted", "...")
    - add_adr(workspace_id, "002", "2024-01-20", "PostgreSQL", "Accepted", "...")

12. [Analyze]
    - validate_workspace(workspace_id)
    - analyze_dependencies(workspace_id)

13. [Export]
    - export_to_dsl(workspace_id)
    - export_to_mermaid("SystemContext")
    - export_to_plantuml("Containers")

Result: Production-ready architecture with docs, ADRs, and multiple export formats
```

**Tools used:** 23 tools (all!)
**Time:** ~30 minutes
**Output:** DSL, Mermaid, PlantUML, documentation

---

### Workflow 4: Analyze and Improve Existing Architecture

**Goal:** Review and optimize an existing architecture.

```
1. get_workspace(workspace_id)
   → Review current state

2. analyze_dependencies(workspace_id)
   → Find coupling issues

3. find_element(workspace_id, "ElementName")
   → Locate specific elements

4. validate_workspace(workspace_id)
   → Check for errors

5. analyze_dependencies(workspace_id, element_id)
   → Deep dive on specific element

6. [Make improvements]
   - add_relationship(...)
   - add_documentation_section(...)
   - add_adr(...)

7. validate_workspace(workspace_id)
   → Verify changes

8. export_to_dsl(workspace_id)
   → Save updated model

Result: Improved architecture with analysis and updates documented
```

**Tools used:** 8 tools
**Time:** ~15 minutes

---

## Detailed Tool Documentation

For comprehensive documentation on each tool, including parameters, examples, and error handling, see:

### By Category

- **[Workspace Tools](workspace-tools.md)** - Create, retrieve, list, and delete workspaces
- **[Model Building Tools](model-tools.md)** - Add people, systems, containers, components, relationships
- **[View Tools](view-tools.md)** - Create C4 views and apply layout
- **[Export/Import Tools](export-tools.md)** - Convert between formats
- **[Documentation Tools](documentation-tools.md)** - Add documentation and ADRs
- **[Analysis Tools](analysis-tools.md)** - Validate, analyze, and search

### Complete Reference

- **[Complete API Reference](../reference/api.md)** - All tools with full details, parameters, and examples

---

## Choosing the Right Tool

### By Goal

| Goal | Tools | Time |
|------|-------|------|
| Create workspace | `create_workspace` | 1 min |
| Add basic elements | `add_person`, `add_software_system` | 5 min |
| Create relationships | `add_relationship` | 5 min |
| Make system context view | `create_system_context_view`, `apply_auto_layout` | 3 min |
| Add containers | `add_container`, `add_relationship` | 10 min |
| Add components | `add_component`, `add_relationship` | 15 min |
| Create all views | All view tools + layout | 15 min |
| Document decisions | `add_documentation_section`, `add_adr` | 10 min |
| Export to file | `export_to_dsl` | 2 min |
| Check for errors | `validate_workspace` | 1 min |
| Find something | `find_element` | 1 min |
| Analyze structure | `analyze_dependencies` | 5 min |

### Quick Decision Tree

```
Are you managing workspaces?
├─ YES → Use Workspace Management tools (1-4)
└─ NO ↓

Are you building the model?
├─ YES → Use Model Building tools (5-9)
└─ NO ↓

Are you creating views?
├─ YES → Use View tools (10-14)
└─ NO ↓

Are you adding documentation?
├─ YES → Use Documentation tools (15-16)
└─ NO ↓

Are you exporting?
├─ YES → Use Export tools (17-20)
└─ NO ↓

Are you analyzing?
├─ YES → Use Analysis tools (21-23)
└─ NO ↓

Done!
```

---

## Getting Help with Tools

### Tool Documentation

Each tool has comprehensive documentation with:
- **Parameters:** Exactly what to pass to the tool
- **Examples:** Real-world usage examples
- **Return values:** What you'll get back
- **Error handling:** What can go wrong and how to fix it
- **Tips:** Best practices and common patterns

### Asking Claude for Help

You don't need to remember tool details. Just ask Claude naturally:

- **"Create a workspace for my microservices architecture"**
  → Claude figures out `create_workspace` and uses it

- **"Add a Customer person and an E-commerce System"**
  → Claude uses `add_person` and `add_software_system`

- **"Create a system context view and lay it out automatically"**
  → Claude uses `create_system_context_view` and `apply_auto_layout`

- **"Show me all the dependencies in my workspace"**
  → Claude uses `analyze_dependencies`

Claude will handle:
- Choosing the right tools
- Remembering workspace IDs
- Managing element IDs
- Tracking what's been created
- Formatting parameters correctly

> **Tip:** You can ask Claude to explain what tools it's using and why. This helps you learn the system.

---

## Best Practices

### 1. Start Simple
Begin with a basic system context view. Add complexity gradually.

### 2. Name Clearly
Use clear, descriptive names for elements and relationships. They're your documentation.

### 3. Document Decisions
Use `add_adr` to record important architectural decisions. Future developers will thank you.

### 4. Validate Frequently
Use `validate_workspace` after making changes to catch issues early.

### 5. Export Regularly
Export to DSL and commit to version control. Your architecture is now part of your codebase.

### 6. Use Tags
Add tags to elements for styling and categorization:
- `"External System"` for external dependencies
- `"Database"` for data stores
- `"Mobile"` for mobile-specific elements

### 7. Organize Views
Create views for different audiences:
- **System Context:** For stakeholders and big-picture thinking
- **Container:** For technical teams and deployment planning
- **Component:** For developers and detailed implementation

### 8. Keep Relationships Clear
Use descriptive relationship descriptions that explain the "why" not just the "what."

---

## Tool Dependencies

Some tools depend on outputs from other tools:

```
create_workspace
    ├─→ add_person (needs workspace_id)
    ├─→ add_software_system (needs workspace_id)
    │   ├─→ add_container (needs system_id)
    │   │   └─→ add_component (needs container_id)
    │   └─→ create_system_context_view (needs system_id)
    ├─→ add_relationship (needs element IDs)
    ├─→ add_documentation_section (needs workspace_id)
    ├─→ add_adr (needs workspace_id)
    └─→ export_to_dsl (needs workspace_id)
```

> **Note:** Claude automatically tracks these IDs, so you don't have to.

---

## Common Tool Combinations

### To build a view, you typically need:
1. Elements (people, systems, containers, components)
2. Relationships between elements
3. A view definition
4. Layout rules

### To export properly, you need:
1. A complete, validated model
2. Views defined
3. Proper export format choice

### To analyze dependencies, you need:
1. Elements defined
2. Relationships defined
3. Validation passing

---

## Next Steps

### New to Structurizr MCP?

1. **[Quick Start](../getting-started/quick-start.md)** - Create your first diagram in 5 minutes
2. **[Understanding the C4 Model](../architecture/c4-model.md)** - Learn the architecture framework
3. **[View Tools](view-tools.md)** - Deep dive into creating different views

### Ready to dive deeper?

1. **[Complete Tools Reference](../reference/api.md)** - Full API documentation
2. **[Real Examples](../examples/basic-c4.md)** - See tools in action
3. **[Workspace Tools](workspace-tools.md)** - Manage multiple workspaces
4. **[Analysis Tools](analysis-tools.md)** - Advanced analysis techniques

### Want to master specific areas?

- **Creating diagrams** → [View Tools](view-tools.md)
- **Building models** → [Model Building Tools](model-tools.md)
- **Exporting** → [Export Tools](export-tools.md)
- **Documentation** → [Documentation Tools](documentation-tools.md)
- **Analysis** → [Analysis Tools](analysis-tools.md)

### Looking for examples?

- **[E-Commerce System](../examples/ecommerce.md)** - Multi-level C4 model with all views
- **[Microservices](../examples/microservices.md)** - Distributed system architecture
- **[Basic C4](../examples/basic-c4.md)** - Simple introductory example

### Need help?

- **[Troubleshooting](../troubleshooting/common-issues.md)** - Solutions to common problems
- **[FAQ](../troubleshooting/faq.md)** - Frequently asked questions
- **[Contact Support](../prologue/contributing.md)** - How to get help

---

## Summary

The Structurizr MCP Server gives you 23 tools to:

- **Create** workspaces and models
- **Build** C4 architecture with people, systems, containers, and components
- **Define** relationships showing how elements interact
- **Visualize** with multiple views and automatic layout
- **Document** decisions and architecture choices
- **Export** to DSL, PlantUML, Mermaid, and other formats
- **Analyze** dependencies, validate structure, and search elements
- **Import** from existing DSL files

All through natural conversation with Claude. You describe what you want, and Claude uses the right tools to make it happen.

> **Let's get started!** Move on to [Quick Start](../getting-started/quick-start.md) or pick a specific tool category above.

---

<p align="right">
  <strong>Next:</strong> <a href="../getting-started/quick-start.md">Quick Start Guide →</a>
</p>
