# C4 Model

## Introduction

The **C4 model** is a lean graphical notation technique for modeling software architecture. It provides a structured approach to creating architecture diagrams with four levels of abstraction, making complex systems easier to understand and communicate.

## What is C4?

C4 stands for **Context, Containers, Components, and Code** - the four hierarchical levels of abstraction used to visualize software architecture.

### Core Principles

1. **Abstraction First** - Different levels for different audiences
2. **Static Structure** - Focus on system building blocks
3. **Multiple Views** - One model, many perspectives
4. **Simple Notation** - Boxes and lines, easy to learn

### Why C4?

**Problems it solves:**

- **Inconsistent diagrams** - Everyone draws differently
- **Too much detail** - Technical diagrams overwhelming stakeholders
- **Too little detail** - High-level diagrams don't help developers
- **Outdated documentation** - Manual diagrams get stale

**C4 Solutions:**

- **Standardized notation** - Consistent across teams
- **Multiple levels** - Right detail for each audience
- **Code-based** - Single source of truth
- **Tool support** - Automated generation

## The Four Levels

```
Level 1: System Context
         │
         │ Zoom in
         ▼
Level 2: Container
         │
         │ Zoom in
         ▼
Level 3: Component
         │
         │ Zoom in (optional)
         ▼
Level 4: Code
```

### Visual Hierarchy

```
┌────────────────────────────────────────────────────────┐
│  LEVEL 1: SYSTEM CONTEXT                               │
│                                                        │
│  ┌─────────┐         ┌──────────────┐                │
│  │  User   │────────►│   System     │                │
│  └─────────┘         └──────────────┘                │
│                                                        │
└────────────────────────────────────────────────────────┘
                        │
                        │ Zoom in
                        ▼
┌────────────────────────────────────────────────────────┐
│  LEVEL 2: CONTAINER                                    │
│                                                        │
│  ┌──────────────┐    ┌──────────────┐                │
│  │ Web App      │───►│  Database    │                │
│  │ (React)      │    │ (PostgreSQL) │                │
│  └──────────────┘    └──────────────┘                │
│         │                                              │
│         ▼                                              │
│  ┌──────────────┐                                     │
│  │  API         │                                     │
│  │ (Node.js)    │                                     │
│  └──────────────┘                                     │
└────────────────────────────────────────────────────────┘
                        │
                        │ Zoom in
                        ▼
┌────────────────────────────────────────────────────────┐
│  LEVEL 3: COMPONENT (Web App)                          │
│                                                        │
│  ┌──────────────┐    ┌──────────────┐                │
│  │ Auth         │───►│  User        │                │
│  │ Controller   │    │  Service     │                │
│  └──────────────┘    └──────────────┘                │
│         │                                              │
│         ▼                                              │
│  ┌──────────────┐                                     │
│  │ Security     │                                     │
│  │ Component    │                                     │
│  └──────────────┘                                     │
└────────────────────────────────────────────────────────┘
```

## Level 1: System Context

### Purpose

Shows the **big picture** - your system and its relationships with users and other systems.

**Audience:** Everyone (technical and non-technical)

**Questions it answers:**
- Who uses the system?
- What are the system boundaries?
- What external systems does it integrate with?

### Elements

**Person:**
- A human user
- Internal or external to the organization
- Role-based (e.g., "Customer", "Administrator")

**Software System:**
- The system you're describing
- External systems it depends on
- Clear system boundaries

**Relationships:**
- How people use systems
- How systems communicate
- Purpose and protocol

### Example

**E-commerce System Context:**

```dsl
workspace "E-commerce Platform" {
    model {
        # People
        customer = person "Customer" "A customer of the platform"
        admin = person "Administrator" "Platform administrator"

        # Your system
        ecommerce = softwareSystem "E-commerce Platform" "Online shopping platform" {
            tags "Internal"
        }

        # External systems
        payment = softwareSystem "Payment Gateway" "External payment processor" {
            tags "External"
        }
        shipping = softwareSystem "Shipping Provider" "External shipping service" {
            tags "External"
        }
        email = softwareSystem "Email Service" "Transactional email service" {
            tags "External"
        }

        # Relationships
        customer -> ecommerce "Browse products, place orders"
        admin -> ecommerce "Manage products, view orders"
        ecommerce -> payment "Process payments" "HTTPS/JSON"
        ecommerce -> shipping "Track shipments" "REST API"
        ecommerce -> email "Send notifications" "SMTP"
    }

    views {
        systemContext ecommerce "SystemContext" {
            include *
            autoLayout lr
        }
    }
}
```

**Visual Output:**

```
┌─────────────┐                                      ┌─────────────┐
│             │    Browse products, place orders     │             │
│  Customer   ├─────────────────────────────────────►│ E-commerce  │
│             │                                      │  Platform   │
└─────────────┘                                      │             │
                                                     │  [Internal] │
┌─────────────┐                                      │             │
│             │    Manage products, view orders      │             │
│Administrator├─────────────────────────────────────►│             │
│             │                                      └──────┬──────┘
└─────────────┘                                             │
                                                            │
                  ┌─────────────────────────────────────────┼──────────────┐
                  │                                         │              │
                  ▼                                         ▼              ▼
         ┌────────────────┐                       ┌────────────┐  ┌──────────────┐
         │    Payment     │                       │  Shipping  │  │    Email     │
         │    Gateway     │                       │  Provider  │  │   Service    │
         │   [External]   │                       │ [External] │  │  [External]  │
         └────────────────┘                       └────────────┘  └──────────────┘
```

### Best Practices

1. **Focus on relationships** - Show why, not just what
2. **Limit scope** - One system per diagram
3. **Use tags** - Differentiate internal/external
4. **Be consistent** - Standard naming conventions

## Level 2: Container

### Purpose

Shows the **high-level technology choices** - applications, databases, microservices that make up the system.

**Audience:** Technical people (developers, architects, operations)

**Questions it answers:**
- What are the deployable units?
- What technologies are used?
- How do containers communicate?

### What is a Container?

In C4, a **container** is:
- A separately deployable/executable unit
- Not a Docker container (though it could be)
- Examples: web app, mobile app, database, microservice

**NOT code-level components:**
- Classes
- Modules
- Libraries

### Elements

**Container:**
- Name (e.g., "Web Application")
- Description (what it does)
- Technology (e.g., "React, TypeScript")
- Part of a software system

**Relationships:**
- Inter-container communication
- Technology/protocol specified

### Example

**E-commerce Containers:**

```dsl
workspace "E-commerce Platform" {
    model {
        customer = person "Customer"
        admin = person "Administrator"

        ecommerce = softwareSystem "E-commerce Platform" {
            # Frontend containers
            webapp = container "Web Application" "Customer-facing web UI" "React, TypeScript" {
                tags "Web Browser"
            }

            adminapp = container "Admin Portal" "Administrative interface" "React, TypeScript" {
                tags "Web Browser"
            }

            # Backend containers
            api = container "API Application" "REST API for web/mobile clients" "Node.js, Express" {
                tags "API"
            }

            # Data containers
            database = container "Database" "Stores product, order, customer data" "PostgreSQL" {
                tags "Database"
            }

            cache = container "Cache" "Session and product cache" "Redis" {
                tags "Database"
            }

            # Background processing
            worker = container "Background Worker" "Processes orders, sends emails" "Node.js" {
                tags "Background"
            }
        }

        # External systems
        payment = softwareSystem "Payment Gateway"
        email = softwareSystem "Email Service"

        # User -> Container relationships
        customer -> webapp "Uses" "HTTPS"
        admin -> adminapp "Uses" "HTTPS"

        # Container relationships
        webapp -> api "Makes API calls to" "JSON/HTTPS"
        adminapp -> api "Makes API calls to" "JSON/HTTPS"
        api -> database "Reads from and writes to" "SQL/TCP"
        api -> cache "Reads from and writes to" "Redis Protocol"
        worker -> database "Reads from and writes to" "SQL/TCP"
        worker -> email "Sends emails using" "HTTPS/REST"
        api -> payment "Processes payments via" "HTTPS/REST"
    }

    views {
        container ecommerce "Containers" {
            include *
            autoLayout tb
        }
    }
}
```

**Visual Output:**

```
┌──────────┐              ┌──────────┐
│ Customer │              │  Admin   │
└────┬─────┘              └────┬─────┘
     │                         │
     │ HTTPS                   │ HTTPS
     ▼                         ▼
┌──────────────┐         ┌──────────────┐
│     Web      │         │    Admin     │
│ Application  │         │    Portal    │
│  (React)     │         │   (React)    │
└──────┬───────┘         └──────┬───────┘
       │                        │
       │ JSON/HTTPS             │ JSON/HTTPS
       │                        │
       └────────┬───────────────┘
                │
                ▼
       ┌──────────────┐
       │     API      │◄──────────┐
       │ Application  │           │
       │  (Node.js)   │           │
       └──┬─────────┬─┘           │
          │         │             │
          │         │             │
   ┌──────▼─────┐  │         ┌───┴──────┐
   │  Database  │  │         │Background│
   │(PostgreSQL)│◄─┤         │  Worker  │
   └────────────┘  │         │(Node.js) │
                   │         └──────────┘
            ┌──────▼─────┐
            │   Cache    │
            │  (Redis)   │
            └────────────┘
```

### Container Types

**Web Application:**
- Runs in web browser
- Technologies: React, Angular, Vue.js

**Mobile Application:**
- Runs on mobile device
- Technologies: iOS, Android, React Native

**Server-Side Application:**
- Backend service
- Technologies: Node.js, Java, Python, Go

**Database:**
- Data storage
- Technologies: PostgreSQL, MySQL, MongoDB

**File System:**
- File storage
- Technologies: S3, local filesystem

**Message Queue:**
- Async communication
- Technologies: RabbitMQ, Kafka, SQS

### Best Practices

1. **Technology decisions** - Specify actual tech stack
2. **Deployment units** - One container = one deployable thing
3. **Communication protocols** - Show how containers talk
4. **Infrastructure** - Include databases, queues, caches

## Level 3: Component

### Purpose

Shows the **internal structure** of a container - logical components and their interactions.

**Audience:** Developers and architects working on specific containers

**Questions it answers:**
- How is this container structured?
- What are the responsibilities?
- How do components interact?

### What is a Component?

A **component** is:
- A grouping of related functionality
- Encapsulated behind a well-defined interface
- Implemented by one or more code elements

**Examples:**
- Controllers (MVC)
- Services (business logic)
- Repositories (data access)
- Utilities (helpers)

### Elements

**Component:**
- Name (e.g., "ProductController")
- Description (responsibility)
- Technology (e.g., "Express Controller")
- Part of a container

**Relationships:**
- Component dependencies
- External system connections

### Example

**API Application Components:**

```dsl
workspace "E-commerce Platform" {
    model {
        customer = person "Customer"

        ecommerce = softwareSystem "E-commerce Platform" {
            webapp = container "Web Application" "React"
            database = container "Database" "PostgreSQL"

            api = container "API Application" "REST API" "Node.js" {
                # Controllers
                productController = component "Product Controller" "Handles product requests" "Express Controller"
                orderController = component "Order Controller" "Handles order requests" "Express Controller"
                authController = component "Auth Controller" "Handles authentication" "Express Controller"

                # Services
                productService = component "Product Service" "Business logic for products" "Service"
                orderService = component "Order Service" "Business logic for orders" "Service"
                authService = component "Auth Service" "Authentication logic" "Service"

                # Repositories
                productRepo = component "Product Repository" "Data access for products" "Repository"
                orderRepo = component "Order Repository" "Data access for orders" "Repository"
                userRepo = component "User Repository" "Data access for users" "Repository"

                # Utilities
                validator = component "Validator" "Input validation" "Utility"
                logger = component "Logger" "Application logging" "Utility"
            }
        }

        payment = softwareSystem "Payment Gateway"

        # External relationships
        customer -> webapp "Uses"
        webapp -> productController "Makes API calls"

        # Component relationships
        productController -> productService "Uses"
        productController -> validator "Validates with"
        productService -> productRepo "Uses"
        productRepo -> database "Reads/writes"

        orderController -> orderService "Uses"
        orderService -> orderRepo "Uses"
        orderService -> payment "Processes payments"
        orderRepo -> database "Reads/writes"

        authController -> authService "Uses"
        authService -> userRepo "Uses"
        userRepo -> database "Reads/writes"

        productService -> logger "Logs with"
        orderService -> logger "Logs with"
    }

    views {
        component api "Components" {
            include *
            autoLayout lr
        }
    }
}
```

**Visual Output:**

```
┌─────────────┐
│   Web App   │
└──────┬──────┘
       │
       ▼
┌──────────────────────────────────────────────────────────────┐
│                    API Application                           │
│                                                              │
│  ┌──────────────┐       ┌──────────────┐                    │
│  │   Product    │──────►│   Product    │──────┐             │
│  │  Controller  │       │   Service    │      │             │
│  └──────┬───────┘       └──────────────┘      │             │
│         │                                      │             │
│         │               ┌──────────────┐      │             │
│  ┌──────▼───────┐       │   Product    │◄─────┘             │
│  │  Validator   │       │  Repository  │                    │
│  └──────────────┘       └──────┬───────┘                    │
│                                │                             │
│  ┌──────────────┐       ┌──────▼───────┐                    │
│  │    Order     │──────►│    Order     │──────┐             │
│  │  Controller  │       │   Service    │      │             │
│  └──────────────┘       └──────┬───────┘      │             │
│                                │               │             │
│                                │        ┌──────▼───────┐     │
│  ┌──────────────┐              │        │    Order     │     │
│  │    Auth      │              │        │  Repository  │     │
│  │  Controller  │              │        └──────┬───────┘     │
│  └──────┬───────┘              │               │             │
│         │                      │               │             │
│         ▼               ┌──────▼───────┐       │             │
│  ┌──────────────┐       │   Logger     │◄──────┘             │
│  │    Auth      │       └──────────────┘                     │
│  │   Service    │                                            │
│  └──────┬───────┘                                            │
│         │                                                    │
│         ▼                                                    │
│  ┌──────────────┐                                            │
│  │    User      │                                            │
│  │  Repository  │                                            │
│  └──────┬───────┘                                            │
│         │                                                    │
└─────────┼────────────────────────────────────────────────────┘
          │
          ▼
    ┌──────────┐
    │ Database │
    └──────────┘
```

### Component Patterns

**Layered Architecture:**
```
Controller Layer (HTTP)
      ↓
Service Layer (Business Logic)
      ↓
Repository Layer (Data Access)
      ↓
Database
```

**Hexagonal Architecture:**
```
Controllers (Adapters)
      ↓
Domain Services (Core)
      ↓
Repositories (Ports)
```

**Clean Architecture:**
```
Frameworks & Drivers
      ↓
Interface Adapters
      ↓
Use Cases
      ↓
Entities
```

### Best Practices

1. **Consistent patterns** - Follow architectural style
2. **Clear responsibilities** - Single Responsibility Principle
3. **Limit dependencies** - Avoid tight coupling
4. **Technology agnostic** - Focus on logical structure

## Level 4: Code

### Purpose

Shows the **implementation details** - classes, interfaces, and relationships.

**Audience:** Developers implementing the system

**Questions it answers:**
- How is this component implemented?
- What are the class relationships?
- What patterns are used?

### Generation

Level 4 diagrams are typically **auto-generated** from code:

- **UML class diagrams** from source code
- **IDE integration** (IntelliJ, VS Code)
- **Code analysis tools** (Doxygen, JavaDoc)

### When to Use

Use sparingly:
- Complex algorithms
- Design patterns
- Core domain models
- Library/framework integration

**Most teams skip Level 4** - code is the documentation.

### Example

**ProductService Class Diagram:**

```
┌─────────────────────────────┐
│   <<interface>>             │
│   IProductService           │
├─────────────────────────────┤
│ + getProduct(id)            │
│ + listProducts()            │
│ + createProduct(data)       │
│ + updateProduct(id, data)   │
│ + deleteProduct(id)         │
└─────────────────────────────┘
              ▲
              │ implements
              │
┌─────────────┴───────────────┐
│   ProductService            │
├─────────────────────────────┤
│ - repository: IProductRepo  │
│ - validator: IValidator     │
│ - logger: ILogger           │
├─────────────────────────────┤
│ + getProduct(id)            │
│ + listProducts()            │
│ + createProduct(data)       │
│ + updateProduct(id, data)   │
│ + deleteProduct(id)         │
│ - validate(data)            │
│ - logOperation(action)      │
└─────────────────────────────┘
```

## Additional Diagram Types

### System Landscape

Shows **multiple systems** and their relationships.

**Use when:**
- Multiple systems in scope
- Enterprise architecture
- System portfolio view

**Example:**

```dsl
systemLandscape "EnterpriseView" {
    include *
    autoLayout
}
```

### Dynamic Diagram

Shows **runtime behavior** - how elements collaborate.

**Use for:**
- Sequence of interactions
- Flow of control
- Error handling scenarios

**Example:**

```dsl
dynamic ecommerce "OrderFlow" "Order placement flow" {
    customer -> webapp "1. Place order"
    webapp -> api "2. Submit order"
    api -> orderService "3. Process order"
    orderService -> payment "4. Process payment"
    payment -> orderService "5. Payment confirmation"
    orderService -> database "6. Save order"
    orderService -> worker "7. Queue notification"
    worker -> email "8. Send confirmation email"
    autoLayout lr
}
```

**Visual:**

```
Customer → Web App → API → Order Service → Payment Gateway
                            ↓              ↓
                            Database       Worker → Email Service
```

### Deployment Diagram

Shows **infrastructure** - how containers map to infrastructure.

**Use for:**
- Production topology
- Scaling strategy
- Infrastructure planning

**Example:**

```dsl
deployment ecommerce "Production" {
    deploymentNode "AWS" {
        deploymentNode "us-east-1" {
            deploymentNode "Load Balancer" {
                containerInstance webapp
            }
            deploymentNode "ECS Cluster" {
                containerInstance api
            }
            deploymentNode "RDS" {
                containerInstance database
            }
        }
    }
}
```

## Creating C4 Diagrams with Structurizr MCP

### System Context Example

```php
// Create workspace
create_workspace(
    name: "E-commerce Platform",
    description: "Online shopping system"
)

// Add people
add_person(
    workspace_id: "ws_abc123",
    name: "Customer",
    description: "Platform user"
)

// Add systems
add_software_system(
    workspace_id: "ws_abc123",
    name: "E-commerce Platform",
    description: "Online shopping platform"
)

add_software_system(
    workspace_id: "ws_abc123",
    name: "Payment Gateway",
    description: "External payment processor",
    tags: "External"
)

// Add relationships
add_relationship(
    source_id: "person_1",
    dest_id: "system_1",
    description: "Browse products, place orders",
    technology: "HTTPS"
)

add_relationship(
    source_id: "system_1",
    dest_id: "system_2",
    description: "Process payments",
    technology: "REST API"
)

// Create system context view
create_system_context_view(
    system_id: "system_1",
    key: "SystemContext",
    description: "System context for E-commerce Platform"
)

// Apply auto-layout
apply_auto_layout(
    view_key: "SystemContext",
    direction: "lr"
)
```

### Container Example

```php
// Add containers to system
add_container(
    system_id: "system_1",
    name: "Web Application",
    description: "Customer-facing web UI",
    technology: "React, TypeScript"
)

add_container(
    system_id: "system_1",
    name: "API Application",
    description: "REST API",
    technology: "Node.js, Express"
)

add_container(
    system_id: "system_1",
    name: "Database",
    description: "Product and order data",
    technology: "PostgreSQL",
    tags: "Database"
)

// Add relationships
add_relationship(
    source_id: "container_1",
    dest_id: "container_2",
    description: "Makes API calls to",
    technology: "JSON/HTTPS"
)

add_relationship(
    source_id: "container_2",
    dest_id: "container_3",
    description: "Reads from and writes to",
    technology: "SQL/TCP"
)

// Create container view
create_container_view(
    system_id: "system_1",
    key: "Containers",
    description: "Container view"
)
```

### Component Example

```php
// Add components to container
add_component(
    container_id: "container_2",
    name: "Product Controller",
    description: "Handles product requests",
    technology: "Express Controller"
)

add_component(
    container_id: "container_2",
    name: "Product Service",
    description: "Business logic for products",
    technology: "Service"
)

add_component(
    container_id: "container_2",
    name: "Product Repository",
    description: "Data access for products",
    technology: "Repository"
)

// Add relationships
add_relationship(
    source_id: "component_1",
    dest_id: "component_2",
    description: "Uses"
)

add_relationship(
    source_id: "component_2",
    dest_id: "component_3",
    description: "Uses"
)

// Create component view
create_component_view(
    container_id: "container_2",
    key: "Components",
    description: "API components"
)
```

## Best Practices

### General Guidelines

**1. Audience-Appropriate Detail**
- Level 1 for stakeholders
- Level 2 for operations/DevOps
- Level 3 for developers
- Level 4 sparingly

**2. Consistency**
- Use standard notation
- Consistent naming
- Same style across diagrams

**3. Keep it Simple**
- One diagram, one purpose
- Remove clutter
- Focus on what matters

**4. Maintain Currency**
- Diagrams as code
- Version control
- Automated generation

### Naming Conventions

**Elements:**
- Nouns: "Product Service", "User Database"
- Role-based: "Customer", "Administrator"
- Technology in description: "PostgreSQL Database"

**Relationships:**
- Verbs: "Uses", "Reads from", "Sends to"
- Purpose over protocol: "Authenticates users" not "HTTP"
- Technology in details: "HTTPS/REST"

### Tags and Styling

**Use tags to:**
- Differentiate element types
- Show locations (Internal/External)
- Highlight concerns (Security, PII)
- Group related elements

**Example:**

```dsl
element "Software System" {
    background #1168bd
    color #ffffff
}

element "External" {
    background #999999
}

element "Database" {
    shape cylinder
}
```

## Resources

### C4 Model
- [C4 Model Official Site](https://c4model.com)
- [C4 Model on GitHub](https://github.com/structurizr/c4model)
- [C4 PlantUML](https://github.com/plantuml-stdlib/C4-PlantUML)

### Structurizr
- [Structurizr](https://structurizr.com)
- [Structurizr DSL](https://github.com/structurizr/dsl)
- [Structurizr Examples](https://structurizr.com/share)

### Related Documentation
- [DSL Builder](/docs/architecture/dsl-builder.md)
- [Model Tools](/docs/tools/model-tools.md)
- [View Tools](/docs/tools/view-tools.md)
