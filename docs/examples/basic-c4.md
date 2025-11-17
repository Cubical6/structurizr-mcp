# Basic C4 Model Tutorial

- [Introduction](#introduction)
- [Learning Objectives](#learning-objectives)
- [Prerequisites](#prerequisites)
- [The Scenario](#the-scenario)
- [Step 1: Create the Workspace](#step-1-create-the-workspace)
- [Step 2: Build the System Context](#step-2-build-the-system-context)
- [Step 3: Define Containers](#step-3-define-containers)
- [Step 4: Add Components](#step-4-add-components)
- [Step 5: Create Views](#step-5-create-views)
- [Step 6: Export and Visualize](#step-6-export-and-visualize)
- [Understanding the Output](#understanding-the-output)
- [What You've Learned](#what-youve-learned)
- [Next Steps](#next-steps)

---

## Introduction

Welcome to the Basic C4 Model Tutorial! This hands-on guide will teach you the fundamentals of creating C4 architecture diagrams using the Structurizr MCP Server.

You'll build a complete architecture model for an Internet Banking System, covering all four levels of the C4 model: System Context, Container, Component, and (optionally) Code.

**Time required:** 15-20 minutes

---

## Learning Objectives

By completing this tutorial, you will:

- ✅ Understand the four levels of the C4 model
- ✅ Create a workspace and manage architectural elements
- ✅ Build System Context, Container, and Component diagrams
- ✅ Define relationships between elements
- ✅ Apply styling and auto-layout to views
- ✅ Export architecture to DSL and other formats
- ✅ Validate and analyze your architecture model

---

## Prerequisites

Before starting this tutorial, ensure you have:

- ✅ Installed and configured the Structurizr MCP Server
- ✅ Set up Claude Desktop with the MCP server
- ✅ Completed the [Quick Start Guide](../getting-started/quick-start.md)
- ✅ Basic understanding of software architecture concepts

> **New to Structurizr?** Review [The C4 Model](../architecture/c4-model.md) first for key concepts.

---

## The Scenario

You're documenting the architecture of an **Internet Banking System** that allows customers to view account information and make payments.

### System Overview

The system consists of:
- **Users**: Personal banking customers
- **Main System**: Internet Banking System (our focus)
- **External Systems**: Mainframe Banking System, Email System
- **Containers**: Web Application, API Application, Database
- **Components**: Sign In Controller, Account Summary Controller, etc.

### Architecture Goals

- Separate web presentation from business logic
- Use a database for storing account information
- Send email notifications
- Integrate with existing mainframe systems

---

## Step 1: Create the Workspace

Let's start by creating a new workspace for our Internet Banking System.

### Ask Claude

```
Create a new Structurizr workspace named "Internet Banking System"
with the description "Architecture model for the internet banking application"
```

### Expected Response

```json
{
  "success": true,
  "workspaceId": "ws_banking_001",
  "name": "Internet Banking System",
  "description": "Architecture model for the internet banking application",
  "message": "Workspace 'Internet Banking System' created successfully"
}
```

> **Tip:** Claude will remember your workspace ID throughout the conversation. You can always retrieve it later using `list_workspaces`.

---

## Step 2: Build the System Context

The System Context diagram shows the big picture: who uses the system and what external systems it interacts with.

### Step 2.1: Add the Person

First, let's add our user.

**Ask Claude:**

```
Add a person named "Personal Banking Customer"
with description "A customer of the bank, with personal bank accounts"
```

**Response:**

```json
{
  "success": true,
  "personId": "1",
  "name": "Personal Banking Customer",
  "message": "Person 'Personal Banking Customer' added successfully"
}
```

### Step 2.2: Add the Main System

Now add our Internet Banking System.

**Ask Claude:**

```
Add a software system named "Internet Banking System"
with description "Allows customers to view information about their bank accounts and make payments"
```

**Response:**

```json
{
  "success": true,
  "systemId": "2",
  "name": "Internet Banking System",
  "message": "Software system 'Internet Banking System' added successfully"
}
```

### Step 2.3: Add External Systems

Add the systems that our banking system depends on.

**Ask Claude:**

```
Add two external software systems:
1. "Mainframe Banking System" - "Stores all of the core banking information about customers, accounts, transactions, etc." with tag "External System"
2. "Email System" - "The internal Microsoft Exchange email system" with tag "External System"
```

**Response:**

```json
{
  "success": true,
  "systemId": "3",
  "name": "Mainframe Banking System",
  "message": "Software system 'Mainframe Banking System' added successfully"
}

{
  "success": true,
  "systemId": "4",
  "name": "Email System",
  "message": "Software system 'Email System' added successfully"
}
```

### Step 2.4: Create Relationships

Connect these elements with relationships.

**Ask Claude:**

```
Create the following relationships:
1. Personal Banking Customer uses Internet Banking System - "Views account balances and makes payments using"
2. Internet Banking System uses Mainframe Banking System - "Gets account information from, and makes payments using"
3. Internet Banking System uses Email System - "Sends email using"
4. Email System delivers to Personal Banking Customer - "Sends emails to"
```

**Response:**

```json
{
  "success": true,
  "relationshipId": "1->2",
  "description": "Views account balances and makes payments using"
}

{
  "success": true,
  "relationshipId": "2->3",
  "description": "Gets account information from, and makes payments using"
}

{
  "success": true,
  "relationshipId": "2->4",
  "description": "Sends email using"
}

{
  "success": true,
  "relationshipId": "4->1",
  "description": "Sends emails to"
}
```

### Step 2.5: Create System Context View

Now create a view to visualize the system context.

**Ask Claude:**

```
Create a system context view for the Internet Banking System
with key "SystemContext" and description "The system context diagram for the Internet Banking System"
and apply top-to-bottom auto-layout
```

**Response:**

```json
{
  "success": true,
  "viewKey": "SystemContext",
  "message": "System context view created successfully"
}

{
  "success": true,
  "viewKey": "SystemContext",
  "direction": "TopBottom",
  "message": "Auto-layout applied successfully"
}
```

> **Success!** You've created your first C4 System Context diagram! This shows the big picture of how the Internet Banking System fits into its environment.

---

## Step 3: Define Containers

The Container diagram zooms into the Internet Banking System to show its high-level building blocks (applications, databases, etc.).

### Step 3.1: Add Web Application

**Ask Claude:**

```
Add a container to the Internet Banking System named "Web Application"
with description "Delivers the static content and the internet banking single page application"
using technology "Java and Spring MVC"
```

**Response:**

```json
{
  "success": true,
  "containerId": "5",
  "name": "Web Application",
  "message": "Container 'Web Application' added successfully"
}
```

### Step 3.2: Add API Application

**Ask Claude:**

```
Add a container to the Internet Banking System named "API Application"
with description "Provides internet banking functionality via a JSON/HTTPS API"
using technology "Java and Spring Boot"
```

### Step 3.3: Add Database

**Ask Claude:**

```
Add a container to the Internet Banking System named "Database"
with description "Stores user registration information, hashed authentication credentials, access logs, etc."
using technology "Oracle Database Schema"
with tag "Database"
```

### Step 3.4: Add Mobile App

**Ask Claude:**

```
Add a container to the Internet Banking System named "Mobile App"
with description "Provides a limited subset of the internet banking functionality to customers via their mobile device"
using technology "Xamarin"
```

### Step 3.5: Create Container Relationships

Define how containers interact with each other and external systems.

**Ask Claude:**

```
Create the following relationships:
1. Personal Banking Customer uses Web Application - "Visits bigbank.com/ib using" with technology "HTTPS"
2. Personal Banking Customer uses Mobile App - "Views account balances and makes payments using"
3. Web Application uses API Application - "Makes API calls to" using "JSON/HTTPS"
4. Mobile App uses API Application - "Makes API calls to" using "JSON/HTTPS"
5. API Application uses Database - "Reads from and writes to" using "JDBC"
6. API Application uses Mainframe Banking System - "Makes API calls to" using "XML/HTTPS"
7. API Application uses Email System - "Sends email using" using "SMTP"
```

> **Note:** These relationships show the technical details of how containers communicate, including protocols like HTTPS, JDBC, and SMTP.

### Step 3.6: Create Container View

**Ask Claude:**

```
Create a container view for the Internet Banking System
with key "Containers" and description "The container diagram for the Internet Banking System"
and apply top-to-bottom auto-layout
```

> **Great!** You've now documented the high-level technology choices and how the system is decomposed into containers.

---

## Step 4: Add Components

The Component diagram zooms into a specific container to show its internal structure. Let's focus on the API Application.

### Step 4.1: Add Sign In Controller

**Ask Claude:**

```
Add a component to the API Application container named "Sign In Controller"
with description "Allows users to sign in to the Internet Banking System"
using technology "Spring MVC Rest Controller"
```

**Response:**

```json
{
  "success": true,
  "componentId": "9",
  "name": "Sign In Controller",
  "message": "Component 'Sign In Controller' added successfully"
}
```

### Step 4.2: Add Account Summary Controller

**Ask Claude:**

```
Add a component to the API Application container named "Accounts Summary Controller"
with description "Provides customers with a summary of their bank accounts"
using technology "Spring MVC Rest Controller"
```

### Step 4.3: Add Reset Password Controller

**Ask Claude:**

```
Add a component to the API Application container named "Reset Password Controller"
with description "Allows users to reset their passwords with a single use URL"
using technology "Spring MVC Rest Controller"
```

### Step 4.4: Add Security Component

**Ask Claude:**

```
Add a component to the API Application container named "Security Component"
with description "Provides functionality related to signing in, changing passwords, etc."
using technology "Spring Bean"
```

### Step 4.5: Add Mainframe Banking System Facade

**Ask Claude:**

```
Add a component to the API Application container named "Mainframe Banking System Facade"
with description "A facade onto the mainframe banking system"
using technology "Spring Bean"
```

### Step 4.6: Add Email Component

**Ask Claude:**

```
Add a component to the API Application container named "Email Component"
with description "Sends emails to users"
using technology "Spring Bean"
```

### Step 4.7: Create Component Relationships

Define how components interact.

**Ask Claude:**

```
Create the following relationships:
1. Sign In Controller uses Security Component - "Uses"
2. Accounts Summary Controller uses Mainframe Banking System Facade - "Uses"
3. Reset Password Controller uses Security Component - "Uses"
4. Reset Password Controller uses Email Component - "Uses"
5. Security Component uses Database - "Reads from and writes to" using "JDBC"
6. Mainframe Banking System Facade uses Mainframe Banking System - "Uses" using "XML/HTTPS"
7. Email Component uses Email System - "Sends email using" using "SMTP"
8. Web Application uses Sign In Controller - "Makes API calls to" using "JSON/HTTPS"
9. Web Application uses Accounts Summary Controller - "Makes API calls to" using "JSON/HTTPS"
10. Web Application uses Reset Password Controller - "Makes API calls to" using "JSON/HTTPS"
11. Mobile App uses Sign In Controller - "Makes API calls to" using "JSON/HTTPS"
12. Mobile App uses Accounts Summary Controller - "Makes API calls to" using "JSON/HTTPS"
13. Mobile App uses Reset Password Controller - "Makes API calls to" using "JSON/HTTPS"
```

### Step 4.8: Create Component View

**Ask Claude:**

```
Create a component view for the API Application container
with key "Components" and description "The component diagram for the API Application"
and apply top-to-bottom auto-layout
```

> **Excellent!** You've now documented the internal structure of the API Application, showing how it's organized into cohesive components.

---

## Step 5: Create Views

Let's create all the views and apply consistent styling.

### Step 5.1: Verify All Views

**Ask Claude:**

```
List all views in the workspace
```

You should see:
- SystemContext - System Context diagram
- Containers - Container diagram
- Components - Component diagram

### Step 5.2: Add Documentation

Add an overview document to your workspace.

**Ask Claude:**

```
Add a documentation section to the workspace with title "System Overview"
and content:
"This is an example of an Internet Banking System. It includes:
- System Context showing external dependencies
- Container diagram showing high-level technology choices
- Component diagram showing internal structure of the API Application

The system allows customers to view their bank accounts and make payments securely."
```

**Response:**

```json
{
  "success": true,
  "title": "System Overview",
  "message": "Documentation section added successfully"
}
```

---

## Step 6: Export and Visualize

Now let's export our architecture in various formats.

### Step 6.1: Export to DSL

**Ask Claude:**

```
Export the workspace to DSL format
```

**Response:**

You'll receive complete Structurizr DSL code that defines your entire architecture model. This code can be:
- Saved to a `.dsl` file
- Committed to version control
- Shared with your team
- Used to regenerate diagrams

### Step 6.2: Export System Context to PlantUML

**Ask Claude:**

```
Export the SystemContext view to PlantUML format
```

**Response:**

```plantuml
@startuml
!include https://raw.githubusercontent.com/plantuml-stdlib/C4-PlantUML/master/C4_Context.puml

Person(1, "Personal Banking Customer", "A customer of the bank, with personal bank accounts")
System(2, "Internet Banking System", "Allows customers to view information about their bank accounts and make payments")
System_Ext(3, "Mainframe Banking System", "Stores all of the core banking information")
System_Ext(4, "Email System", "The internal Microsoft Exchange email system")

Rel(1, 2, "Views account balances and makes payments using")
Rel(2, 3, "Gets account information from, and makes payments using")
Rel(2, 4, "Sends email using")
Rel(4, 1, "Sends emails to")

@enduml
```

> **Tip:** Copy this PlantUML code to [PlantUML Online](https://www.plantuml.com/plantuml/) to visualize it immediately.

### Step 6.3: Export Container View to Mermaid

**Ask Claude:**

```
Export the Containers view to Mermaid format
```

**Response:**

You'll receive Mermaid diagram syntax that can be rendered in:
- GitHub markdown
- GitLab wikis
- Confluence pages
- VS Code with Mermaid extension

### Step 6.4: Validate the Workspace

**Ask Claude:**

```
Validate the workspace
```

**Response:**

```json
{
  "valid": true,
  "errors": [],
  "warnings": [],
  "message": "Workspace validation successful"
}
```

> **Success!** Your architecture model is complete and valid.

---

## Understanding the Output

Let's examine key parts of the generated DSL to understand what you've created.

### Model Hierarchy

```dsl
model {
    personalBankingCustomer = person "Personal Banking Customer" "..."

    internetBankingSystem = softwareSystem "Internet Banking System" "..." {
        webApplication = container "Web Application" "..." "Java and Spring MVC"

        apiApplication = container "API Application" "..." "Java and Spring Boot" {
            signInController = component "Sign In Controller" "..." "Spring MVC Rest Controller"
            accountsSummaryController = component "Accounts Summary Controller" "..." "Spring MVC Rest Controller"
            // ... more components
        }

        database = container "Database" "..." "Oracle Database Schema" {
            tags "Database"
        }
    }

    mainframeBankingSystem = softwareSystem "Mainframe Banking System" "..." {
        tags "External System"
    }
}
```

**This shows:**
- **Hierarchy**: People > Systems > Containers > Components
- **Nesting**: Containers are inside systems, components inside containers
- **Properties**: Name, description, technology, tags

### Relationships

```dsl
personalBankingCustomer -> webApplication "Visits bigbank.com/ib using" "HTTPS"
webApplication -> apiApplication "Makes API calls to" "JSON/HTTPS"
apiApplication -> database "Reads from and writes to" "JDBC"
```

**This shows:**
- **Direction**: A -> B means "A uses B"
- **Description**: What the relationship does
- **Technology**: How it's implemented (optional)

### Views Configuration

```dsl
views {
    systemContext internetBankingSystem "SystemContext" {
        include *
        autoLayout tb
    }

    container internetBankingSystem "Containers" {
        include *
        autoLayout tb
    }

    component apiApplication "Components" {
        include *
        autoLayout tb
    }
}
```

**This shows:**
- **View types**: systemContext, container, component
- **Scope**: Which element to focus on
- **Elements**: `include *` means include all related elements
- **Layout**: `tb` = top-to-bottom, `lr` = left-to-right

---

## What You've Learned

Congratulations! You've completed the Basic C4 Model tutorial. Here's what you've mastered:

### Core Concepts

- ✅ **C4 Model Levels** - System Context, Container, Component, and their purposes
- ✅ **Element Types** - People, Software Systems, Containers, Components
- ✅ **Relationships** - How to connect elements and specify technology
- ✅ **Views** - Creating different perspectives on your architecture

### MCP Tools

- ✅ **Workspace Management** - `create_workspace`, `get_workspace`
- ✅ **Model Building** - `add_person`, `add_software_system`, `add_container`, `add_component`
- ✅ **Relationships** - `add_relationship` with descriptions and technologies
- ✅ **Views** - `create_system_context_view`, `create_container_view`, `create_component_view`
- ✅ **Layout** - `apply_auto_layout` for automatic positioning
- ✅ **Export** - `export_to_dsl`, `export_to_plantuml`, `export_to_mermaid`
- ✅ **Validation** - `validate_workspace` for quality assurance

### Best Practices

- ✅ **Clear naming** - Descriptive names for all elements
- ✅ **Appropriate tags** - Marking external systems and databases
- ✅ **Technology details** - Specifying implementation technologies
- ✅ **Consistent relationships** - Clear descriptions of interactions
- ✅ **Documentation** - Adding context with documentation sections

---

## Next Steps

Ready to expand your knowledge? Here are suggested next steps:

### Enhance This Model

**Add more detail:**
```
Add a dynamic view showing the sign-in process:
1. Customer submits credentials to Web Application
2. Web Application calls Sign In Controller
3. Sign In Controller validates with Security Component
4. Security Component checks Database
```

**Add Architecture Decision Records:**
```
Add an ADR to the workspace documenting why we chose Spring Boot for the API Application
```

### Explore More Examples

- **[E-Commerce System](ecommerce.md)** - Learn to model complex multi-system architectures
- **[Microservices Architecture](microservices.md)** - Distributed systems and service meshes
- **[Migration Guide](migration.md)** - Convert existing diagrams to Structurizr

### Master Advanced Features

- **[Analysis Tools](../tools/analysis-tools.md)** - Analyze dependencies and validate architecture
- **[MCP Prompts](../prompts/overview.md)** - Use AI-assisted architecture analysis
- **[MCP Resources](../resources/overview.md)** - Access workspace data programmatically
- **[Dynamic Views](../tools/view-tools.md#create-dynamic-view)** - Show runtime behavior

### Apply to Your Projects

- Model your current project's architecture
- Share DSL files with your team via git
- Generate diagrams for documentation
- Keep architecture in sync with code

> **Tip:** The best way to learn is by doing. Take a real system you're working on and model it using what you've learned!

---

## Troubleshooting

### Common Issues

**"Container not found when adding component"**
- Make sure you're using the container ID, not the system ID
- Use `find_element` to locate the correct container ID

**"Relationships not appearing in view"**
- Ensure both source and destination elements are included in the view
- Check that element IDs are correct (case-sensitive)

**"Auto-layout not working as expected"**
- Try different directions: `TopBottom`, `BottomTop`, `LeftRight`, `RightLeft`
- Consider manually positioning elements in Structurizr visualizer

**"Export format not rendering correctly"**
- Validate the workspace first to catch any errors
- Check that all relationships have valid source and destination IDs

> **Need more help?** See the [Troubleshooting Guide](../troubleshooting/common-issues.md) for detailed solutions.

---

<p align="right">
  <strong>Back to:</strong> <a href="README.md">← Examples Index</a><br>
  <strong>Next:</strong> <a href="ecommerce.md">E-Commerce Example →</a>
</p>
