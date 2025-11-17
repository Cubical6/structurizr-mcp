# Quick Start

- [Introduction](#introduction)
- [Your Goal](#your-goal)
- [Step 1: Create a Workspace](#step-1-create-a-workspace)
- [Step 2: Add a Person](#step-2-add-a-person)
- [Step 3: Add a Software System](#step-3-add-a-software-system)
- [Step 4: Create Relationships](#step-4-create-relationships)
- [Step 5: Generate a System Context View](#step-5-generate-a-system-context-view)
- [Step 6: Export to DSL](#step-6-export-to-dsl)
- [What You've Learned](#what-youve-learned)
- [Next Steps](#next-steps)

---

## Introduction

Welcome! In this guide, you'll create your first C4 architecture diagram in just 5 minutes using the Structurizr MCP Server with Claude.

> **Note:** This guide assumes you've already completed [installation](installation.md) and [configuration](configuration.md). If not, please complete those steps first.

---

## Your Goal

By the end of this guide, you'll have created a complete C4 System Context diagram for an e-commerce platform showing:

- A **Customer** (person) who uses the system
- An **E-commerce System** (software system)
- A **Payment Gateway** (external system)
- **Relationships** showing how they interact

You'll also learn how to export your architecture model to Structurizr DSL format.

---

## Step 1: Create a Workspace

First, let's create a new workspace to hold our architecture model.

**Ask Claude:**

```
Create a new Structurizr workspace named "E-commerce Platform"
with the description "Online shopping platform architecture"
```

**What Claude does:**

Claude will use the `create_workspace` tool and respond with:

```json
{
  "success": true,
  "workspaceId": "ws_abc123def456",
  "name": "E-commerce Platform",
  "description": "Online shopping platform architecture",
  "message": "Workspace 'E-commerce Platform' created successfully"
}
```

> **Tip:** Save the `workspaceId` - you'll need it for subsequent operations. Claude will remember it for you during your conversation.

---

## Step 2: Add a Person

Now let's add a customer to our model.

**Ask Claude:**

```
Add a person named "Customer" to the workspace with the description
"A user who browses and purchases products"
```

**What Claude does:**

Claude will use the `add_person` tool:

```json
{
  "success": true,
  "personId": "1",
  "name": "Customer",
  "message": "Person 'Customer' added successfully"
}
```

> **Note:** Each element gets a unique ID. The Structurizr MCP Server automatically manages IDs for you.

---

## Step 3: Add a Software System

Let's add two software systems: our main e-commerce system and an external payment gateway.

**Ask Claude:**

```
Add two software systems:
1. "E-commerce System" - "Allows customers to browse and purchase products online"
2. "Payment Gateway" - "External payment processing service" with tag "External System"
```

**What Claude does:**

Claude will use the `add_software_system` tool twice:

```json
{
  "success": true,
  "systemId": "2",
  "name": "E-commerce System",
  "message": "Software system 'E-commerce System' added successfully"
}

{
  "success": true,
  "systemId": "3",
  "name": "Payment Gateway",
  "message": "Software system 'Payment Gateway' added successfully"
}
```

> **Tip:** Tags like "External System" help visually distinguish different types of elements in your diagrams. They can be styled differently in the final visualization.

---

## Step 4: Create Relationships

Now we'll connect these elements by defining their relationships.

**Ask Claude:**

```
Create the following relationships:
1. Customer uses E-commerce System - "Browses and purchases products"
2. E-commerce System uses Payment Gateway - "Processes payments" using "HTTPS/REST"
```

**What Claude does:**

Claude will use the `add_relationship` tool:

```json
{
  "success": true,
  "relationshipId": "1->2",
  "description": "Browses and purchases products",
  "message": "Relationship added successfully"
}

{
  "success": true,
  "relationshipId": "2->3",
  "description": "Processes payments",
  "technology": "HTTPS/REST",
  "message": "Relationship added successfully"
}
```

> **Note:** Relationships can optionally include technology details (like "HTTPS/REST") to show implementation specifics.

---

## Step 5: Generate a System Context View

Let's create a System Context view to visualize our architecture.

**Ask Claude:**

```
Create a system context view for the E-commerce System with key "SystemContext"
and apply left-to-right auto-layout
```

**What Claude does:**

Claude will use the `create_system_context_view` tool followed by `apply_auto_layout`:

```json
{
  "success": true,
  "viewKey": "SystemContext",
  "message": "System context view created successfully"
}

{
  "success": true,
  "viewKey": "SystemContext",
  "direction": "LeftRight",
  "message": "Auto-layout applied successfully"
}
```

> **Tip:** Auto-layout automatically positions elements in your diagram. Available directions are: `TopBottom`, `BottomTop`, `LeftRight`, `RightLeft`.

---

## Step 6: Export to DSL

Finally, let's export our architecture model to Structurizr DSL format.

**Ask Claude:**

```
Export the workspace to DSL format
```

**What Claude does:**

Claude will use the `export_to_dsl` tool and return your complete architecture as code:

```dsl
workspace "E-commerce Platform" "Online shopping platform architecture" {
    model {
        customer = person "Customer" "A user who browses and purchases products"

        ecommerceSystem = softwareSystem "E-commerce System" "Allows customers to browse and purchase products online"

        paymentGateway = softwareSystem "Payment Gateway" "External payment processing service" {
            tags "External System"
        }

        customer -> ecommerceSystem "Browses and purchases products"
        ecommerceSystem -> paymentGateway "Processes payments" "HTTPS/REST"
    }

    views {
        systemContext ecommerceSystem "SystemContext" {
            include *
            autoLayout lr
        }

        styles {
            element "Software System" {
                background #1168bd
                color #ffffff
            }
            element "External System" {
                background #999999
                color #ffffff
            }
            element "Person" {
                background #08427b
                color #ffffff
                shape person
            }
        }
    }
}
```

> **Success!** You've just created your first C4 architecture model as code!

---

## Understanding the DSL Output

Let's break down what you've created:

### Model Section

```dsl
model {
    customer = person "Customer" "..."
    ecommerceSystem = softwareSystem "E-commerce System" "..."
    paymentGateway = softwareSystem "Payment Gateway" "..." {
        tags "External System"
    }

    customer -> ecommerceSystem "Browses and purchases products"
    ecommerceSystem -> paymentGateway "Processes payments" "HTTPS/REST"
}
```

This defines:
- **Elements**: The people and systems in your architecture
- **Relationships**: How they interact (shown with `->`)
- **Tags**: Categories for styling (like "External System")

### Views Section

```dsl
views {
    systemContext ecommerceSystem "SystemContext" {
        include *
        autoLayout lr
    }
}
```

This defines:
- **View type**: `systemContext` shows the big picture
- **Scope**: Which system to focus on (`ecommerceSystem`)
- **Elements**: `include *` includes all related elements
- **Layout**: `lr` means left-to-right

### Styles Section

```dsl
styles {
    element "Software System" {
        background #1168bd
        color #ffffff
    }
}
```

This defines visual styling for your diagrams.

---

## What You Can Do Next

Now that you have a DSL file, you can:

### 1. Visualize with Structurizr

Upload to [Structurizr Cloud](https://structurizr.com) or [Structurizr Lite](https://structurizr.com/help/lite) to see your diagram rendered.

### 2. Export to Other Formats

**Ask Claude:**

```
Export the SystemContext view to PlantUML format
```

Or:

```
Export the SystemContext view to Mermaid format
```

### 3. Version Control Your Architecture

Save the DSL to a file and commit it to git:

```bash
# Save the DSL output to a file
echo "workspace ..." > architecture.dsl

# Commit to version control
git add architecture.dsl
git commit -m "Add initial e-commerce architecture"
```

> **Tip:** Architecture as code means your documentation lives alongside your codebase and evolves with it.

---

## What You've Learned

Congratulations! In just 5 minutes, you've learned how to:

- ✅ **Create a workspace** - The container for your architecture model
- ✅ **Add people** - Users and actors in your system
- ✅ **Add software systems** - The applications and services
- ✅ **Define relationships** - How elements interact
- ✅ **Create views** - Visualizations of your architecture
- ✅ **Export to DSL** - Generate architecture as code

These are the fundamental building blocks of C4 modeling with Structurizr.

---

## Next Steps

Ready to dive deeper? Here's what to explore next:

### Expand Your Model

- **Add containers** → [Container Views Guide](../tools/view-tools.md#create-container-view)
- **Add components** → [Component Views Guide](../tools/view-tools.md#create-component-view)
- **Add documentation** → [Documentation Tools](../tools/documentation-tools.md)

### Learn More Concepts

- **Understand C4 Model** → [The C4 Model](../architecture/c4-model.md)
- **Master DSL syntax** → [DSL Syntax Guide](../reference/dsl-syntax.md)
- **Explore all tools** → [Complete Tools Reference](../tools/overview.md)

### See Real Examples

- **E-commerce system** → [E-Commerce Example](../examples/ecommerce.md)
- **Microservices** → [Microservices Example](../examples/microservices.md)
- **Complete tutorial** → [Your First C4 Diagram](first-diagram.md)

### Advanced Features

- **Use MCP Resources** → [Understanding Resources](../resources/overview.md)
- **Try MCP Prompts** → [Using Prompts](../prompts/overview.md)
- **Analyze architecture** → [Analysis Tools](../tools/analysis-tools.md)

> **Tip:** You can ask Claude to help with any of these tasks. For example: "Help me add a container view to my e-commerce system" or "Analyze the dependencies in my workspace."

---

## Troubleshooting

### Common Issues

**"Workspace not found"**
- Make sure you're using the correct workspace ID
- Use `list_workspaces` to see all available workspaces

**"Element not found"**
- Element IDs are case-sensitive
- Use `find_element` to locate elements by name

**"Invalid relationship"**
- Both source and destination must exist
- Check element IDs carefully

> **Need help?** See the [Troubleshooting Guide](../troubleshooting/common-issues.md) for detailed solutions.

---

<p align="right">
  <strong>Previous:</strong> <a href="configuration.md">← Configuration</a><br>
  <strong>Next:</strong> <a href="claude-desktop.md">Claude Desktop Setup →</a>
</p>
