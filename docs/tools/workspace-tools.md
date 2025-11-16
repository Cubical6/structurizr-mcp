# Workspace Management Tools

- [Introduction](#introduction)
- [create_workspace](#create_workspace)
- [get_workspace](#get_workspace)
- [list_workspaces](#list_workspaces)
- [delete_workspace](#delete_workspace)

---

## Introduction

The Workspace Management tools provide complete CRUD (Create, Read, Update, Delete) operations for Structurizr workspaces. These are the foundational tools for managing architecture documentation projects within the MCP server.

> **Note:** All workspace tools operate on local workspace storage. Workspaces are stored as DSL files and can be exported to various formats including PlantUML, Mermaid, and JSON.

---

## create_workspace

Create a new Structurizr workspace with a name and optional description.

### When To Use

- Starting a new architecture documentation project
- Creating separate workspaces for different systems or bounded contexts
- Organizing architecture models by team, domain, or application
- Prototyping new C4 diagrams before integrating into existing workspaces

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `name` | `string` | Yes | - | Min: 1 char<br>Max: 100 chars | The name of the workspace |
| `description` | `string` | No | `""` | Max: 500 chars | Optional description of the workspace purpose |

### Return Value

```json
{
  "workspaceId": "string",
  "name": "string",
  "description": "string",
  "dsl": "string",
  "createdAt": "string (ISO 8601)"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | Unique identifier for the workspace (use this for all subsequent operations) |
| `name` | `string` | The workspace name |
| `description` | `string` | The workspace description |
| `dsl` | `string` | Initial DSL content for the workspace |
| `createdAt` | `string` | ISO 8601 timestamp of workspace creation |

### Usage Example

Ask Claude to create a workspace using natural language:

```
Create a new Structurizr workspace called "E-Commerce Platform" with description "Architecture documentation for the online shopping system"
```

Or be more specific:

```
Use the create_workspace tool to create a workspace named "Payment Service" with description "Microservice handling payment processing and fraud detection"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "name": "E-Commerce Platform",
  "description": "Architecture documentation for the online shopping system",
  "dsl": "workspace \"E-Commerce Platform\" \"Architecture documentation for the online shopping system\" {\n    model {\n    }\n    views {\n    }\n}\n",
  "createdAt": "2025-11-16T10:30:45+00:00"
}
```

### Common Use Cases

**Separate Workspaces by System**
```
Create three workspaces:
1. "Customer Portal" - Customer-facing web application
2. "Admin Dashboard" - Internal management system
3. "API Gateway" - Backend API infrastructure
```

**Domain-Driven Design Contexts**
```
Create a workspace for the "Order Management" bounded context with description "Handles order lifecycle from creation to fulfillment"
```

**Team-Based Organization**
```
Create a workspace called "Platform Team - Infrastructure" for documenting cloud infrastructure and deployment architecture
```

### Tips and Warnings

> **Tip:** Use descriptive workspace names that clearly indicate the system or domain being documented. This makes it easier to manage multiple workspaces.

> **Tip:** Save the returned `workspaceId` immediately - you'll need it for all subsequent operations like adding elements, creating views, and exporting.

> **Warning:** Workspace names are used to generate workspace IDs. Keep names unique to avoid confusion when managing multiple workspaces.

> **Note:** The initial DSL contains an empty workspace structure. You'll add elements (people, systems, containers) and views in subsequent steps.

---

## get_workspace

Retrieve the complete details of a workspace including its model, views, and DSL representation.

### When To Use

- Reviewing the current state of a workspace
- Inspecting workspace structure before making changes
- Retrieving DSL for version control or backup
- Debugging workspace issues
- Sharing workspace definitions with team members

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The ID of the workspace to retrieve |
| `format` | `string` | No | `"json"` | Enum: `json`, `dsl` | Output format for the workspace data |

### Return Value

#### JSON Format (default)

```json
{
  "id": "string",
  "name": "string",
  "description": "string",
  "dsl": "string",
  "model": {
    "people": [...],
    "softwareSystems": [...],
    "deploymentNodes": [...]
  },
  "views": {
    "systemContextViews": [...],
    "containerViews": [...],
    "componentViews": [...],
    "dynamicViews": [...]
  },
  "documentation": {
    "sections": [...],
    "decisions": [...]
  },
  "createdAt": "string (ISO 8601)",
  "updatedAt": "string (ISO 8601)"
}
```

#### DSL Format

```json
{
  "workspaceId": "string",
  "name": "string",
  "dsl": "string"
}
```

### Usage Example

**Get full workspace data (JSON format):**
```
Get the complete details of workspace "ecommerce-platform-abc123"
```

**Get workspace DSL only:**
```
Get workspace "ecommerce-platform-abc123" in DSL format
```

**Review before making changes:**
```
Show me the current state of the "Payment Service" workspace before I add new containers
```

### Response Example

#### JSON Format Response

```json
{
  "id": "ecommerce-platform-abc123",
  "name": "E-Commerce Platform",
  "description": "Architecture documentation for the online shopping system",
  "dsl": "workspace \"E-Commerce Platform\" \"Architecture documentation for the online shopping system\" {\n    model {\n        customer = person \"Customer\" \"A user of the e-commerce platform\"\n        ecommerce = softwareSystem \"E-Commerce System\" \"Allows customers to browse and purchase products\" {\n            webapp = container \"Web Application\" \"Delivers content to customers\" \"React\"\n        }\n        customer -> ecommerce \"Browses products and places orders\"\n    }\n    views {\n        systemContext ecommerce \"SystemContext\" {\n            include *\n            autoLayout lr\n        }\n    }\n}\n",
  "model": {
    "people": [
      {
        "id": "customer",
        "name": "Customer",
        "description": "A user of the e-commerce platform",
        "tags": "Element,Person"
      }
    ],
    "softwareSystems": [
      {
        "id": "ecommerce",
        "name": "E-Commerce System",
        "description": "Allows customers to browse and purchase products",
        "containers": [
          {
            "id": "webapp",
            "name": "Web Application",
            "description": "Delivers content to customers",
            "technology": "React"
          }
        ]
      }
    ]
  },
  "views": {
    "systemContextViews": [
      {
        "key": "SystemContext",
        "softwareSystemId": "ecommerce",
        "elements": [...]
      }
    ]
  },
  "createdAt": "2025-11-16T10:30:45+00:00",
  "updatedAt": "2025-11-16T11:15:22+00:00"
}
```

#### DSL Format Response

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "name": "E-Commerce Platform",
  "dsl": "workspace \"E-Commerce Platform\" \"Architecture documentation for the online shopping system\" {\n    model {\n        customer = person \"Customer\" \"A user of the e-commerce platform\"\n        ecommerce = softwareSystem \"E-Commerce System\" \"Allows customers to browse and purchase products\" {\n            webapp = container \"Web Application\" \"Delivers content to customers\" \"React\"\n        }\n        customer -> ecommerce \"Browses products and places orders\"\n    }\n    views {\n        systemContext ecommerce \"SystemContext\" {\n            include *\n            autoLayout lr\n        }\n    }\n}\n"
}
```

### Common Use Cases

**Review Before Export**
```
Get the workspace "payment-service" to review before exporting to PlantUML
```

**Backup Workspace DSL**
```
Retrieve the DSL for workspace "production-architecture" so I can save it to version control
```

**Debugging Issues**
```
Show me the full JSON structure of workspace "api-gateway" to debug why the view isn't rendering correctly
```

**Sharing with Team**
```
Get the DSL format of the "microservices-architecture" workspace to share with the development team
```

### Tips and Warnings

> **Tip:** Use `format: "dsl"` when you only need the workspace definition for backup, version control, or sharing. The DSL format is more compact and human-readable.

> **Tip:** Use `format: "json"` (default) when you need to inspect the complete workspace structure, including parsed model elements and view configurations.

> **Note:** The JSON format includes the full object graph with all relationships, elements, and views. This is useful for programmatic access but can be verbose for large workspaces.

> **Warning:** If a workspace doesn't exist, you'll receive a "Workspace not found" error. Use `list_workspaces` to verify available workspace IDs.

---

## list_workspaces

List all available workspaces with their metadata.

### When To Use

- Discovering available workspaces in the system
- Finding a workspace ID when you know the name
- Getting an overview of all architecture documentation projects
- Auditing workspace inventory
- Building workspace selection interfaces

### Parameters

This tool takes no parameters.

### Return Value

```json
{
  "workspaces": [
    {
      "id": "string",
      "name": "string",
      "description": "string",
      "createdAt": "string (ISO 8601)",
      "updatedAt": "string (ISO 8601)"
    }
  ],
  "count": "number"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaces` | `array` | Array of workspace metadata objects |
| `workspaces[].id` | `string` | Workspace unique identifier |
| `workspaces[].name` | `string` | Workspace name |
| `workspaces[].description` | `string` | Workspace description |
| `workspaces[].createdAt` | `string` | ISO 8601 creation timestamp |
| `workspaces[].updatedAt` | `string` | ISO 8601 last modification timestamp |
| `count` | `number` | Total number of workspaces |

### Usage Example

```
List all available workspaces
```

```
Show me all the architecture workspaces I have
```

```
What workspaces exist in the system?
```

### Response Example

```json
{
  "workspaces": [
    {
      "id": "ecommerce-platform-abc123",
      "name": "E-Commerce Platform",
      "description": "Architecture documentation for the online shopping system",
      "createdAt": "2025-11-16T10:30:45+00:00",
      "updatedAt": "2025-11-16T11:15:22+00:00"
    },
    {
      "id": "payment-service-def456",
      "name": "Payment Service",
      "description": "Microservice handling payment processing and fraud detection",
      "createdAt": "2025-11-16T09:20:10+00:00",
      "updatedAt": "2025-11-16T09:45:33+00:00"
    },
    {
      "id": "api-gateway-ghi789",
      "name": "API Gateway",
      "description": "Central API gateway for microservices architecture",
      "createdAt": "2025-11-15T14:10:00+00:00",
      "updatedAt": "2025-11-16T08:30:15+00:00"
    }
  ],
  "count": 3
}
```

### Common Use Cases

**Finding a Workspace**
```
List all workspaces and find the ID for "Payment Service"
```

**Workspace Inventory**
```
Show me all workspaces to see what architecture documentation we have
```

**Before Creating New Workspace**
```
List workspaces to check if "Customer Portal" already exists before creating a new one
```

**Recent Activity Check**
```
List all workspaces and show me which ones were updated recently
```

### Tips and Warnings

> **Tip:** The `count` field provides a quick way to check how many workspaces exist without iterating through the array.

> **Tip:** Use the `updatedAt` timestamp to identify recently modified workspaces when working with multiple projects.

> **Note:** Workspaces are sorted by creation date by default, with newest workspaces appearing first.

> **Note:** If no workspaces exist, the `workspaces` array will be empty and `count` will be 0.

---

## delete_workspace

Permanently delete a workspace and all its data.

### When To Use

- Removing obsolete or prototype workspaces
- Cleaning up test workspaces
- Deleting duplicate workspaces
- Freeing up storage space
- Removing incorrect or invalid workspace data

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The ID of the workspace to delete |

### Return Value

```json
{
  "success": "boolean",
  "message": "string",
  "workspaceId": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `success` | `boolean` | `true` if deletion succeeded, `false` if workspace not found |
| `message` | `string` | Human-readable confirmation or error message |
| `workspaceId` | `string` | The workspace ID that was deleted or not found |

### Usage Example

```
Delete workspace "old-prototype-xyz123"
```

```
Remove the workspace "test-workspace-abc456"
```

```
Permanently delete the "duplicate-workspace" workspace
```

### Response Example

#### Successful Deletion

```json
{
  "success": true,
  "message": "Workspace old-prototype-xyz123 deleted successfully",
  "workspaceId": "old-prototype-xyz123"
}
```

#### Workspace Not Found

```json
{
  "success": false,
  "message": "Workspace not found: nonexistent-workspace",
  "workspaceId": "nonexistent-workspace"
}
```

### Common Use Cases

**Clean Up Prototypes**
```
Delete the "prototype-v1" workspace now that we've finalized the design in "production-architecture"
```

**Remove Test Data**
```
Delete all test workspaces: "test-workspace-1", "test-workspace-2", "test-workspace-3"
```

**Fix Mistakes**
```
Delete workspace "wrong-name-abc123" because I created it with the wrong name
```

**Storage Management**
```
List all workspaces, then delete old workspaces that haven't been updated in 6 months
```

### Tips and Warnings

> **Warning:** This operation is **permanent and cannot be undone**. All workspace data including model elements, views, documentation, and ADRs will be deleted.

> **Warning:** Always verify you have the correct workspace ID before deleting. Use `get_workspace` or `list_workspaces` to confirm the workspace details first.

> **Tip:** Export the workspace to DSL format using `get_workspace` before deleting if you might need to restore it later.

> **Tip:** The tool returns `success: false` instead of throwing an error when a workspace doesn't exist. Check the `success` field to determine if deletion was successful.

> **Note:** If you need to preserve workspace data but remove it from the active list, consider exporting it to a file instead of deleting.

> **Best Practice:** Before deleting a workspace:
> 1. Use `get_workspace` to review its contents
> 2. Export the DSL to a backup file
> 3. Confirm with team members if it's a shared workspace
> 4. Then proceed with deletion

---

## Navigation

- [← Back to Tools Overview](overview.md)
- [Model Building Tools →](model-tools.md)
- [View Management Tools →](view-tools.md)
- [Export Tools →](export-tools.md)

---

## Related Resources

- [Workspace Resources](../resources/reference.md#workspace-resources) - MCP resources for accessing workspace data
- [Configuration](../getting-started/configuration.md) - Workspace storage configuration
- [Quick Start Guide](../getting-started/quick-start.md) - Create your first workspace
