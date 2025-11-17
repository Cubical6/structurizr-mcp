# View Management Tools

- [Introduction](#introduction)
- [create_system_context_view](#create_system_context_view)
- [create_container_view](#create_container_view)
- [create_component_view](#create_component_view)
- [create_dynamic_view](#create_dynamic_view)
- [apply_auto_layout](#apply_auto_layout)
- [Common Patterns](#common-patterns)

---

## Introduction

The View Management tools enable you to create C4 diagram views from your architecture model. Views are visual representations that show different perspectives and levels of detail in your architecture. While the model defines what elements exist and how they relate, views determine what gets shown in each diagram.

The C4 model provides four standard view types corresponding to its hierarchical levels:

1. **System Context View** - Shows a software system and its relationships with users and other systems (Level 1)
2. **Container View** - Shows the containers (applications, services, data stores) within a system (Level 2)
3. **Component View** - Shows the components within a single container (Level 3)
4. **Dynamic View** - Shows runtime behavior and sequences of interactions across levels

> **Note:** Views don't create elements - they visualize elements that already exist in the model. You must add people, systems, containers, and components before creating views to display them.

> **Tip:** Views automatically include all relationships between visible elements. You control which elements appear; relationships follow automatically.

---

## create_system_context_view

Create a system context diagram view showing a software system and its environment.

### When To Use

- Showing the big picture of how a system fits into its environment
- Documenting who uses the system (people/actors)
- Identifying external systems the system depends on
- Communicating with non-technical stakeholders
- Creating executive-level architecture documentation
- Establishing system boundaries and scope
- Starting point for detailed architecture documentation

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID containing the model |
| `systemId` | `string` | Yes | - | Min: 1 char | The software system to focus the view on (from `add_software_system`) |
| `key` | `string` | Yes | - | Min: 1 char<br>Max: 50 chars<br>Pattern: `^[a-zA-Z0-9_-]+$` | Unique identifier for the view (alphanumeric, hyphens, underscores only) |
| `description` | `string` | No | `""` | Max: 500 chars | Optional description explaining the purpose of this view |

### Return Value

```json
{
  "workspaceId": "string",
  "viewKey": "string",
  "systemId": "string",
  "type": "systemContext",
  "description": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID |
| `viewKey` | `string` | Unique key for the view (use with `apply_auto_layout` and export tools) |
| `systemId` | `string` | The software system this view focuses on |
| `type` | `string` | Always "systemContext" |
| `description` | `string` | The description provided |

### Usage Example

**Create a basic system context view:**
```
Create a system context view for system "ecommerce" in workspace "ecommerce-platform-abc123" with key "SystemContext" and description "Shows how customers interact with the e-commerce platform"
```

**Create multiple context views:**
```
In workspace "multi-system":
1. Create system context view for system "paymentservice" with key "PaymentContext"
2. Create system context view for system "orderservice" with key "OrderContext"
```

**Create context view with specific description:**
```
For the "api-gateway" system, create a system context view with key "GatewayOverview" and description "API Gateway in the context of microservices architecture"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "viewKey": "SystemContext",
  "systemId": "ecommerce",
  "type": "systemContext",
  "description": "Shows how customers interact with the e-commerce platform"
}
```

### Common Use Cases

**E-Commerce Platform**
```
Create system context view for "E-Commerce Platform" showing:
- Customer using the platform to browse and purchase
- Admin managing the system
- Payment Gateway for processing payments
- Email Service for sending notifications
```

**Microservice in Context**
```
Create system context view for "User Service" showing:
- Mobile App using the service
- Web App using the service
- Other microservices (Order Service, Payment Service) depending on it
- Database storing user data
```

**SaaS Application**
```
Create system context view for "Analytics Platform" showing:
- End Users analyzing data
- Admin Users managing the platform
- Data Sources being analyzed
- External BI Tools integrating with the platform
```

**System Integration**
```
Create system context view showing:
- Internal CRM System
- Salesforce (external)
- Marketing Automation (external)
- Customer Data Platform (internal)
- How they all integrate
```

### Tips and Warnings

> **Tip:** System context views should focus on a single software system. Everything else (people, other systems) appears as context around that central system.

> **Tip:** Use descriptive view keys like "SystemContext", "PaymentContext", or "Overview". You'll reference these keys when exporting diagrams.

> **Tip:** System context views are excellent for stakeholder communication because they show the big picture without technical details.

> **Important:** The `systemId` must exist in the model. Create the software system with `add_software_system` before creating its view.

> **Note:** The view automatically includes all people and systems that have relationships with the specified system. You don't manually select what appears.

> **Best Practice:** Create a system context view for each major software system in your workspace. This provides navigation entry points into your architecture documentation.

---

## create_container_view

Create a container diagram view showing the containers within a software system.

### When To Use

- Documenting the high-level technology architecture
- Showing deployment units and their technology choices
- Communicating with developers and architects
- Identifying applications, services, and data stores
- Planning deployment and infrastructure
- Documenting microservices architecture
- Showing how technology components interact

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID containing the model |
| `systemId` | `string` | Yes | - | Min: 1 char | The software system to show containers for (from `add_software_system`) |
| `key` | `string` | Yes | - | Min: 1 char<br>Max: 50 chars<br>Pattern: `^[a-zA-Z0-9_-]+$` | Unique identifier for the view |
| `description` | `string` | No | `""` | Max: 500 chars | Optional description of what this view shows |

### Return Value

```json
{
  "workspaceId": "string",
  "viewKey": "string",
  "systemId": "string",
  "type": "container",
  "description": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID |
| `viewKey` | `string` | Unique key for the view |
| `systemId` | `string` | The software system being decomposed |
| `type` | `string` | Always "container" |
| `description` | `string` | The description provided |

### Usage Example

**Create a basic container view:**
```
Create a container view for system "ecommerce" in workspace "ecommerce-platform-abc123" with key "Containers" and description "Technology architecture of the e-commerce platform"
```

**Create container views for multiple systems:**
```
In workspace "microservices-architecture":
1. Create container view for system "userservice" with key "UserServiceContainers"
2. Create container view for system "orderservice" with key "OrderServiceContainers"
3. Create container view for system "paymentservice" with key "PaymentServiceContainers"
```

**Create detailed container view:**
```
For system "analytics-platform", create container view with key "AnalyticsContainers" and description "Shows all microservices, databases, and message queues in the analytics system"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "viewKey": "Containers",
  "systemId": "ecommerce",
  "type": "container",
  "description": "Technology architecture of the e-commerce platform"
}
```

### Common Use Cases

**Web Application Architecture**
```
Create container view for "Web Application" showing:
- React Web App (frontend)
- Mobile App (React Native)
- API Server (Node.js)
- Database (PostgreSQL)
- Cache (Redis)
- File Storage (AWS S3)
```

**Microservices System**
```
Create container view for "Order Management System" showing:
- Order API (Java Spring Boot)
- Order Database (PostgreSQL)
- Event Bus (Kafka)
- Worker Service (Python)
- Notification Service (Node.js)
```

**Data Platform**
```
Create container view for "Analytics Platform" showing:
- Data Ingestion API (Python)
- Data Lake (S3)
- ETL Pipeline (Apache Spark)
- Data Warehouse (Snowflake)
- Analytics API (FastAPI)
- Dashboard App (React)
```

**Legacy Modernization**
```
Create container view showing:
- Legacy Monolith (Java)
- New Microservices (various)
- Strangler Pattern migration
- Shared Database vs. separate databases
- API Gateway routing between old and new
```

### Tips and Warnings

> **Tip:** Container views show what you deploy. If two things always deploy together, they're likely the same container.

> **Tip:** Container views are perfect for showing technology decisions. Include technology names in container descriptions (React, Java, PostgreSQL, etc.).

> **Tip:** Use container views to communicate with development teams about the technical architecture and deployment topology.

> **Important:** The system must have containers added with `add_container` before the view will show anything meaningful.

> **Note:** The view shows containers within the specified system plus any external systems or people that interact with those containers.

> **Best Practice:** Create one container view per software system. For complex systems, you might create multiple views focusing on different aspects (e.g., "DataFlow", "Deployment", "Security").

---

## create_component_view

Create a component diagram view showing the components within a container.

### When To Use

- Documenting the internal structure of an application
- Showing code-level organization (packages, namespaces, modules)
- Communicating detailed design to developers
- Identifying interfaces and dependencies within code
- Planning refactoring efforts
- Understanding component responsibilities
- Documenting layered architecture patterns

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID containing the model |
| `containerId` | `string` | Yes | - | Min: 1 char | The container to show components for (from `add_container`) |
| `key` | `string` | Yes | - | Min: 1 char<br>Max: 50 chars<br>Pattern: `^[a-zA-Z0-9_-]+$` | Unique identifier for the view |
| `description` | `string` | No | `""` | Max: 500 chars | Optional description of what this view shows |

### Return Value

```json
{
  "workspaceId": "string",
  "viewKey": "string",
  "containerId": "string",
  "type": "component",
  "description": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID |
| `viewKey` | `string` | Unique key for the view |
| `containerId` | `string` | The container being decomposed |
| `type` | `string` | Always "component" |
| `description` | `string` | The description provided |

### Usage Example

**Create a basic component view:**
```
Create a component view for container "api" in workspace "ecommerce-platform-abc123" with key "ApiComponents" and description "Internal structure of the API server"
```

**Create component views for multiple containers:**
```
In workspace "microservices":
1. Create component view for container "orderapi" with key "OrderApiComponents"
2. Create component view for container "webapp" with key "WebAppComponents"
3. Create component view for container "worker" with key "WorkerComponents"
```

**Create detailed design view:**
```
For container "paymentservice", create component view with key "PaymentInternals" and description "Shows controllers, services, repositories, and their dependencies"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "viewKey": "ApiComponents",
  "containerId": "api",
  "type": "component",
  "description": "Internal structure of the API server"
}
```

### Common Use Cases

**Spring Boot API**
```
Create component view for "API Server" showing:
- Controllers (OrderController, ProductController, UserController)
- Services (OrderService, PaymentService, NotificationService)
- Repositories (OrderRepository, ProductRepository)
- External Service Clients (StripeClient, SendGridClient)
```

**React Application**
```
Create component view for "Web App" showing:
- Pages (HomePage, ProductPage, CheckoutPage)
- Components (Header, Footer, ProductCard)
- Services (ApiService, AuthService)
- Stores (OrderStore, CartStore, UserStore)
```

**Node.js Microservice**
```
Create component view for "User Service" showing:
- Routes (AuthRoutes, UserRoutes, ProfileRoutes)
- Middleware (AuthMiddleware, ValidationMiddleware)
- Services (UserService, TokenService)
- Models (User, Session, Profile)
- Database Client
```

**Layered Architecture**
```
Create component view showing:
- Presentation Layer (Controllers, Views)
- Business Logic Layer (Services, Domain Models)
- Data Access Layer (Repositories, DAO)
- Infrastructure Layer (Logging, Configuration)
```

### Tips and Warnings

> **Tip:** Component views show code organization. Use names that match your actual code structure (class names, module names, package names).

> **Tip:** Component diagrams are most valuable for complex containers with many internal parts. Simple containers may not need component views.

> **Tip:** Use component views for onboarding new developers - they provide a visual map of how code is organized.

> **Important:** The container must have components added with `add_component` before the view will show the internal structure.

> **Note:** Component views can become complex quickly. Consider creating multiple views focusing on specific aspects (e.g., "Persistence", "API", "BusinessLogic").

> **Best Practice:** Create component views only for the most important or complex containers. Not every container needs a component view - focus on where it adds value.

---

## create_dynamic_view

Create a dynamic diagram view showing runtime behavior and interactions.

### When To Use

- Documenting sequences of interactions
- Showing data flow through the system
- Explaining use case scenarios step-by-step
- Illustrating runtime behavior
- Documenting API call chains
- Showing message flows in event-driven systems
- Communicating complex interaction patterns
- Creating sequence diagram equivalents

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID containing the model |
| `elementId` | `string` | Yes | - | Min: 1 char | The scope element (system or container) that bounds this dynamic view |
| `key` | `string` | Yes | - | Min: 1 char<br>Max: 50 chars<br>Pattern: `^[a-zA-Z0-9_-]+$` | Unique identifier for the view |
| `description` | `string` | No | `""` | Max: 500 chars | Optional description of the scenario shown (e.g., "User login flow") |

### Return Value

```json
{
  "workspaceId": "string",
  "viewKey": "string",
  "elementId": "string",
  "type": "dynamic",
  "description": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID |
| `viewKey` | `string` | Unique key for the view |
| `elementId` | `string` | The scope element (system or container) |
| `type` | `string` | Always "dynamic" |
| `description` | `string` | The description provided |

### Usage Example

**Create a user flow dynamic view:**
```
Create a dynamic view for element "ecommerce" in workspace "ecommerce-platform-abc123" with key "CheckoutFlow" and description "Shows the sequence of interactions during checkout"
```

**Create multiple scenario views:**
```
In workspace "microservices":
1. Create dynamic view for element "orderservice" with key "CreateOrderFlow" and description "Order creation sequence"
2. Create dynamic view for element "orderservice" with key "PaymentFlow" and description "Payment processing sequence"
3. Create dynamic view for element "orderservice" with key "FulfillmentFlow" and description "Order fulfillment sequence"
```

**Create API call chain view:**
```
For element "apigateway", create dynamic view with key "ApiCallChain" and description "Shows how API requests flow through microservices"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "viewKey": "CheckoutFlow",
  "elementId": "ecommerce",
  "type": "dynamic",
  "description": "Shows the sequence of interactions during checkout"
}
```

### Common Use Cases

**E-Commerce Checkout Flow**
```
Create dynamic view showing:
1. Customer submits order (Web App -> API)
2. API validates inventory (API -> Database)
3. API processes payment (API -> Payment Gateway)
4. API creates order (API -> Database)
5. API sends confirmation (API -> Email Service)
6. Response returns to customer (API -> Web App)
```

**User Authentication Flow**
```
Create dynamic view showing:
1. User submits credentials (Mobile App -> Auth Service)
2. Auth Service validates (Auth Service -> User Database)
3. Auth Service generates token (Auth Service -> Token Service)
4. Token returned to app (Auth Service -> Mobile App)
5. App stores token locally (Mobile App -> Local Storage)
```

**Microservices Saga Pattern**
```
Create dynamic view showing:
1. Order Service receives request
2. Order Service publishes OrderCreated event
3. Payment Service processes payment
4. Payment Service publishes PaymentCompleted event
5. Inventory Service reserves items
6. Inventory Service publishes InventoryReserved event
7. Notification Service sends confirmation
```

**Data Pipeline Flow**
```
Create dynamic view showing:
1. API receives data (External System -> Ingestion API)
2. Data written to queue (Ingestion API -> Message Queue)
3. Worker picks up message (Message Queue -> ETL Worker)
4. Worker transforms data (ETL Worker -> Processing Engine)
5. Data written to warehouse (Processing Engine -> Data Warehouse)
6. Analytics updated (Data Warehouse -> Analytics Service)
```

### Tips and Warnings

> **Tip:** Dynamic views tell a story. Use clear descriptions like "User Login Flow", "Order Processing Sequence", "Payment Failed Scenario".

> **Tip:** Focus on one scenario per dynamic view. Create multiple views for different scenarios rather than cramming everything into one diagram.

> **Tip:** Dynamic views are excellent for documentation and communication. They show "how" things work at runtime, complementing static views that show "what" exists.

> **Important:** The `elementId` determines the scope. Use a system ID to show cross-container interactions, or a container ID to show component-level sequences.

> **Note:** Dynamic views build upon relationships defined in the model. The relationships show possible connections; dynamic views show which connections are used in specific scenarios.

> **Best Practice:** Use dynamic views for:
> - Complex workflows that need explanation
> - Onboarding documentation showing how features work
> - Troubleshooting guides showing failure scenarios
> - API documentation showing request/response flows

---

## apply_auto_layout

Apply automatic layout configuration to a view to control diagram element positioning.

### When To Use

- Controlling the direction of diagram layouts
- Organizing elements for better readability
- Ensuring consistent diagram presentation
- Optimizing space usage in diagrams
- Aligning diagrams with standard conventions
- Preparing diagrams for export and presentation

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID |
| `viewKey` | `string` | Yes | - | Min: 1 char<br>Max: 50 chars<br>Pattern: `^[a-zA-Z0-9_-]+$` | The unique key of the view to apply layout to |
| `direction` | `string` | No | `"lr"` | Enum: `tb`, `bt`, `lr`, `rl` | Layout direction:<br>`tb` = top-to-bottom<br>`bt` = bottom-to-top<br>`lr` = left-to-right (default)<br>`rl` = right-to-left |

### Return Value

```json
{
  "workspaceId": "string",
  "viewKey": "string",
  "autoLayout": "string",
  "message": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID |
| `viewKey` | `string` | The view that was modified |
| `autoLayout` | `string` | The layout direction applied |
| `message` | `string` | Confirmation message |

### Usage Example

**Apply left-to-right layout:**
```
Apply auto-layout to view "SystemContext" in workspace "ecommerce-platform-abc123" with direction "lr"
```

**Apply top-to-bottom layout:**
```
Apply auto-layout direction "tb" to view "Containers" in workspace "microservices"
```

**Apply layout to multiple views:**
```
In workspace "architecture":
1. Apply auto-layout "lr" to view "SystemContext"
2. Apply auto-layout "tb" to view "Containers"
3. Apply auto-layout "lr" to view "Components"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "viewKey": "SystemContext",
  "autoLayout": "lr",
  "message": "Auto-layout 'lr' applied to view 'SystemContext'"
}
```

### Common Use Cases

**System Context Views - Left to Right**
```
Apply "lr" layout to system context views:
- Users on the left
- System in the center
- External systems on the right
- Natural left-to-right reading flow
```

**Container Views - Top to Bottom**
```
Apply "tb" layout to container views:
- Web/mobile apps at top
- API servers in middle
- Databases at bottom
- Matches typical architecture diagrams
```

**Component Views - Left to Right**
```
Apply "lr" layout to component views:
- Controllers on left
- Services in middle
- Repositories on right
- Matches layered architecture flow
```

**Hierarchical Systems - Top to Bottom**
```
Apply "tb" layout for:
- Organizational hierarchies
- System decomposition diagrams
- Parent-child relationships
- Top-down process flows
```

### Tips and Warnings

> **Tip:** Left-to-right (`lr`) is the default and works well for most diagrams. It follows natural reading direction.

> **Tip:** Use top-to-bottom (`tb`) for hierarchical relationships or when showing flow from high-level to low-level components.

> **Tip:** Apply auto-layout immediately after creating a view for consistent presentation across all diagrams.

> **Tip:** Experiment with different directions to find what makes your specific diagram most readable.

> **Important:** The `viewKey` must match a view created with one of the view creation tools.

> **Note:** Auto-layout is applied when exporting diagrams to PlantUML, Mermaid, or rendering in visualization tools.

> **Best Practice:** Choose layout direction based on diagram content:
> - **lr (left-to-right)**: User interactions, API flows, sequential processes
> - **tb (top-to-bottom)**: Hierarchies, layers, frontend-to-database flows
> - **bt (bottom-to-top)**: Reverse hierarchies, dependency direction
> - **rl (right-to-left)**: Rarely used, for specific cultural or design requirements

---

## Common Patterns

### Complete Architecture Documentation

Create a full set of views for comprehensive documentation:

```
1. System Context View (lr)
   - Show the system in its environment
   - Who uses it, what it integrates with

2. Container View (tb)
   - Show deployment architecture
   - Technology choices
   - High-level structure

3. Component Views (lr)
   - One per significant container
   - Show internal organization
   - Code structure

4. Dynamic Views (lr or tb)
   - Key user scenarios
   - Important workflows
   - Integration patterns
```

### Microservices Architecture Views

```
For each microservice:

1. System Context View
   - Show the microservice in the ecosystem
   - Other services it depends on
   - Clients that use it

2. Container View
   - API, Database, Workers
   - Message queues
   - Caches and storage

3. Dynamic Views
   - Request/response flows
   - Event-driven interactions
   - Saga patterns

Auto-layout: Use "lr" for flows, "tb" for hierarchies
```

### Stakeholder-Specific Views

```
For Executives:
- System Context Views (lr)
- High-level, business-focused
- Show business value and integrations

For Architects:
- Container Views (tb)
- Technology decisions
- Deployment topology

For Developers:
- Component Views (lr)
- Code organization
- Internal dependencies
- Dynamic Views showing workflows

Auto-layout varies by audience preference
```

### Progressive Disclosure Pattern

```
Start broad, get detailed:

1. System Landscape (if multiple systems)
   - All systems and relationships
   - Layout: "lr"

2. System Context (per system)
   - Focus on one system at a time
   - Layout: "lr"

3. Container View (per system)
   - Technology architecture
   - Layout: "tb"

4. Component Views (per container)
   - Only for complex containers
   - Layout: "lr"

5. Dynamic Views (as needed)
   - Key scenarios
   - Layout: varies by scenario
```

### View Naming Conventions

Use descriptive, consistent view keys:

```
System Context Views:
- "SystemContext" (primary system)
- "PaymentContext" (specific system)
- "UserServiceContext" (microservice)

Container Views:
- "Containers" (primary system)
- "PaymentContainers" (specific system)
- "DataPlatformContainers" (complex system)

Component Views:
- "ApiComponents" (container name + Components)
- "WebAppComponents"
- "OrderServiceComponents"

Dynamic Views:
- "CheckoutFlow" (use case + Flow)
- "LoginSequence"
- "PaymentProcessing"
- "CreateOrderFlow"
```

### Layout Direction Guidelines

```
Left-to-Right (lr):
✓ System context views
✓ Horizontal process flows
✓ User journey diagrams
✓ API call chains
✓ Component dependencies

Top-to-Bottom (tb):
✓ Container views (frontend -> backend -> database)
✓ Layered architectures
✓ Organizational hierarchies
✓ Parent-child relationships
✓ Data flow diagrams (ingestion -> processing -> storage)

Bottom-to-Top (bt):
✓ Dependency graphs (showing what depends on what)
✓ Build dependency trees
✓ Reverse hierarchies

Right-to-Left (rl):
✓ Rarely used
✓ Specific cultural contexts
✓ Reverse flow diagrams
```

### Multi-View Workspace Organization

```
Typical workspace structure:

Views for Primary System:
1. SystemContext (lr)
2. Containers (tb)
3. ApiComponents (lr)
4. WebAppComponents (lr)
5. CheckoutFlow (lr)
6. LoginFlow (lr)

Views for External Systems:
7. PaymentGatewayContext (lr)
8. EmailServiceContext (lr)

Comparison Views:
9. BeforeArchitecture (tb)
10. AfterArchitecture (tb)
```

---

## Navigation

- [← Back to Tools Overview](overview.md)
- [Model Building Tools ←](model-tools.md)
- [Export Tools →](export-tools.md)
- [Documentation Tools →](documentation-tools.md)

---

## Related Resources

- [View Resources](../resources/reference.md#view-resources) - MCP resources for accessing view data
- [Export Tools](export-tools.md) - Export views to PlantUML, Mermaid, and other formats
- [C4 Model](https://c4model.com) - Official C4 model documentation
- [Structurizr DSL Views](https://github.com/structurizr/dsl/blob/master/docs/language-reference.md#views) - DSL view syntax reference
- [Quick Start Guide](../getting-started/quick-start.md) - Create your first views
