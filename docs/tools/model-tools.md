# Model Building Tools

- [Introduction](#introduction)
- [add_person](#add_person)
- [add_software_system](#add_software_system)
- [add_container](#add_container)
- [add_component](#add_component)
- [add_relationship](#add_relationship)

---

## Introduction

The Model Building tools enable you to create and populate the C4 model with architectural elements. These tools form the core of Structurizr's power: defining your architecture as code through a hierarchical model of people, systems, containers, and components.

The C4 model follows a strict hierarchy:
1. **People** - Users and actors that interact with systems
2. **Software Systems** - Applications or services (top level)
3. **Containers** - Deployable/executable units within systems
4. **Components** - Logical groupings within containers
5. **Relationships** - Connections between any elements

> **Note:** Always create elements in hierarchical order. A container cannot be added until its parent software system exists. Similarly, a component requires a parent container. People and relationships can be added after the systems they reference exist.

---

## add_person

Add a person (user, actor, or role) to the C4 model.

### When To Use

- Defining users or actors that interact with the system
- Representing different user roles (customer, admin, employee, etc.)
- Identifying stakeholders in the architecture
- Creating system context diagrams that show who uses the system
- Documenting external roles that interact with your software

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID containing the model |
| `name` | `string` | Yes | - | Min: 1 char<br>Max: 100 chars | The name of the person (e.g., "Customer", "Administrator", "Support Agent") |
| `description` | `string` | No | `""` | Max: 500 chars | A description of the person's role or responsibilities |
| `tags` | `array` | No | `[]` | Array of strings | Optional tags for styling, filtering, or grouping |

### Return Value

```json
{
  "workspaceId": "string",
  "elementId": "string",
  "name": "string",
  "type": "person",
  "description": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID where the person was added |
| `elementId` | `string` | Unique identifier for the person (used in relationships) |
| `name` | `string` | The person's name |
| `type` | `string` | Always "person" |
| `description` | `string` | The description provided |

### Usage Example

**Add a single user:**
```
Add a person named "Customer" to workspace "ecommerce-platform-abc123" with description "A user of the e-commerce platform"
```

**Add different user roles:**
```
Add these people to the "microservices" workspace:
1. Person "End User" - "Customer using the system"
2. Person "Support Agent" - "Provides customer support"
3. Person "Administrator" - "System administrator managing the infrastructure"
```

**Add external users:**
```
Add a person "Third-party Partner" to workspace "api-platform" with description "External organization using our APIs"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "elementId": "customer",
  "name": "Customer",
  "type": "person",
  "description": "A user of the e-commerce platform"
}
```

### Common Use Cases

**E-Commerce System**
```
Create these people for an e-commerce workspace:
- Customer: "Browses and purchases products"
- Support Agent: "Handles customer inquiries"
- Admin: "Manages inventory and orders"
```

**SaaS Application**
```
Add people for a SaaS workspace:
- End User: "Uses the application features"
- Account Manager: "Manages user accounts and billing"
- System Administrator: "Manages infrastructure and security"
```

**Banking System**
```
Define banking system users:
- Bank Customer: "Accesses banking services"
- Bank Teller: "Processes customer transactions"
- Bank Manager: "Manages accounts and staff"
```

### Tips and Warnings

> **Tip:** Use descriptive names that clearly indicate the user role (e.g., "Customer", "Admin", "Support Agent" rather than "User1", "User2").

> **Tip:** Save the returned `elementId` if you plan to add relationships involving this person. You'll need it when calling `add_relationship`.

> **Note:** The `elementId` is automatically generated from the name but can be used to reference the person in relationships and views.

> **Best Practice:** Add all people to the model before defining relationships. This ensures all referenced elements exist.

---

## add_software_system

Add a software system to the C4 model.

### When To Use

- Creating the top-level abstraction for applications or services
- Defining systems external to your primary application
- Establishing system boundaries for the model
- Creating system context diagrams
- Representing third-party or legacy systems in your architecture
- Modeling multi-system landscapes

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID |
| `name` | `string` | Yes | - | Min: 1 char<br>Max: 100 chars | The name of the software system |
| `description` | `string` | No | `""` | Max: 500 chars | What the system does and its purpose |
| `location` | `string` | No | `"Internal"` | Enum: `Internal`, `External` | Whether the system is internal or external |
| `tags` | `array` | No | `[]` | Array of strings | Tags for styling and categorization |

### Return Value

```json
{
  "workspaceId": "string",
  "elementId": "string",
  "name": "string",
  "type": "softwareSystem",
  "location": "string",
  "description": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID |
| `elementId` | `string` | Unique identifier for the system (used when adding containers and relationships) |
| `name` | `string` | The system name |
| `type` | `string` | Always "softwareSystem" |
| `location` | `string` | Either "Internal" or "External" |
| `description` | `string` | The description provided |

### Usage Example

**Add a primary system:**
```
Add a software system named "E-Commerce Platform" to workspace "ecommerce-platform-abc123" with description "Allows customers to browse and purchase products online" and location "Internal"
```

**Add multiple systems:**
```
To the "microservices" workspace, add:
1. Software system "User Service" - "Handles user authentication and profiles" (Internal)
2. Software system "Product Service" - "Manages product catalog" (Internal)
3. Software system "Stripe" - "Payment processing" (External)
```

**Add external services:**
```
Add software system "AWS" to workspace "cloud-architecture" with description "Cloud infrastructure provider" and location "External"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "elementId": "ecommerce",
  "name": "E-Commerce Platform",
  "type": "softwareSystem",
  "location": "Internal",
  "description": "Allows customers to browse and purchase products online"
}
```

### Common Use Cases

**Microservices Architecture**
```
Create these systems:
- "User Service" (Internal): "Manages user accounts and authentication"
- "Order Service" (Internal): "Handles order processing and fulfillment"
- "Payment Service" (Internal): "Processes payments securely"
- "Stripe API" (External): "Payment gateway provider"
```

**System Landscape**
```
Define multiple systems:
- "Customer Portal" (Internal): "Customer-facing web application"
- "Admin Dashboard" (Internal): "Internal administration system"
- "Legacy System" (Internal): "Existing billing system"
- "Salesforce" (External): "CRM platform"
```

**Multi-Tenant SaaS**
```
Create systems for a SaaS application:
- "Main Application" (Internal): "Core SaaS application"
- "API Gateway" (Internal): "Exposes APIs to customers"
- "Analytics Service" (Internal): "User behavior tracking"
- "Twilio" (External): "SMS notifications provider"
```

### Tips and Warnings

> **Tip:** Use "Internal" for systems you control and "External" for third-party services, APIs, or systems outside your organization.

> **Tip:** Save the returned `elementId` - you'll need it to add containers to this system.

> **Tip:** Create software systems before adding containers, as containers must belong to a system.

> **Note:** A workspace typically has 1-3 primary software systems. If you're modeling multiple independent systems, consider whether they should be in separate workspaces.

> **Best Practice:** Define all systems first, then add containers and relationships. This provides a clear top-level architecture before diving into implementation details.

---

## add_container

Add a container (deployable/executable unit) to a software system.

### When To Use

- Defining applications, services, or deployment units
- Creating deployment architecture diagrams
- Specifying technology stacks (web apps, APIs, databases, etc.)
- Documenting microservices within a system
- Showing runtime deployment units
- Identifying technology choices at the container level

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID |
| `systemId` | `string` | Yes | - | Min: 1 char | The parent software system ID (from `add_software_system`) |
| `name` | `string` | Yes | - | Min: 1 char<br>Max: 100 chars | Name of the container (e.g., "Web App", "API Server", "Database") |
| `description` | `string` | No | `""` | Max: 500 chars | What the container does |
| `technology` | `string` | No | `""` | Max: 200 chars | Technology/platform (e.g., "React", "Java Spring Boot", "PostgreSQL") |
| `tags` | `array` | No | `[]` | Array of strings | Tags for styling and filtering |

### Return Value

```json
{
  "workspaceId": "string",
  "elementId": "string",
  "systemId": "string",
  "name": "string",
  "type": "container",
  "technology": "string",
  "description": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID |
| `elementId` | `string` | Unique identifier for the container (used when adding components and relationships) |
| `systemId` | `string` | The parent system ID |
| `name` | `string` | The container name |
| `type` | `string` | Always "container" |
| `technology` | `string` | The technology specified |
| `description` | `string` | The description provided |

### Usage Example

**Add web and database containers:**
```
To system "ecommerce" in workspace "ecommerce-platform-abc123", add:
1. Container "Web Application" - "Delivers e-commerce UI to customers" - Technology: "React"
2. Container "API Server" - "Provides REST API for web and mobile apps" - Technology: "Java Spring Boot"
3. Container "Database" - "Stores product, order, and customer data" - Technology: "PostgreSQL"
```

**Add microservice containers:**
```
To system "microservices" in workspace "microservices-architecture", add:
1. Container "User Service" - "Handles user management" - Technology: "Node.js/Express"
2. Container "Order Service" - "Manages orders" - Technology: "Java"
3. Container "Notification Service" - "Sends emails and SMS" - Technology: "Python/Celery"
```

**Add containers with various technologies:**
```
To system "Platform" in workspace "multi-tech", add:
1. Container "Mobile App" - Technology: "iOS Swift"
2. Container "Mobile App" - Technology: "Android Kotlin"
3. Container "Cache" - Technology: "Redis"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "elementId": "webapp",
  "systemId": "ecommerce",
  "name": "Web Application",
  "type": "container",
  "technology": "React",
  "description": "Delivers e-commerce UI to customers"
}
```

### Common Use Cases

**E-Commerce System**
```
Add containers to "E-Commerce Platform":
- "Web App" (React): "Customer-facing storefront"
- "Mobile App" (React Native): "Mobile shopping app"
- "API Server" (Node.js): "Backend API"
- "Database" (PostgreSQL): "Data storage"
- "Cache" (Redis): "Performance caching"
- "Message Queue" (RabbitMQ): "Async processing"
```

**Microservices Architecture**
```
Add containers to various systems:
- "User Service" (Java Spring Boot): "User management"
- "Order Service" (Python FastAPI): "Order processing"
- "Payment Service" (Go): "Payment handling"
- "Notification Service" (Node.js): "Email/SMS notifications"
```

**Monolithic Application**
```
Add containers to "Legacy System":
- "Web Application" (ASP.NET): "Monolithic web app"
- "Database" (SQL Server): "Application database"
- "Admin Portal" (ASP.NET): "Admin interface"
```

### Tips and Warnings

> **Tip:** A container represents something you deploy independently. If something always deploys together, it's the same container.

> **Tip:** Specify the technology stack clearly - this information is valuable for architecture documentation and deployment planning.

> **Tip:** Save the `elementId` of containers you'll add components to or create relationships from.

> **Important:** The `systemId` must match a system that was previously added with `add_software_system`.

> **Note:** Common container types include web applications, APIs, databases, caches, message queues, and microservices.

> **Best Practice:** Organize containers by technology or deployment unit. For example, separate frontend and backend containers even if part of the same logical system.

---

## add_component

Add a component (logical grouping) to a container.

### When To Use

- Decomposing a container into logical parts
- Documenting internal architecture of an application
- Showing how code is organized (controllers, services, repositories, etc.)
- Creating component diagrams for detailed design documentation
- Identifying interfaces between parts of a system
- Specifying internal dependencies and communication patterns

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID |
| `containerId` | `string` | Yes | - | Min: 1 char | The parent container ID (from `add_container`) |
| `name` | `string` | Yes | - | Min: 1 char<br>Max: 100 chars | Component name (e.g., "OrderController", "PaymentService", "UserRepository") |
| `description` | `string` | No | `""` | Max: 500 chars | What the component does |
| `technology` | `string` | No | `""` | Max: 200 chars | Technology/framework (e.g., "Spring Controller", "Repository Pattern") |
| `tags` | `array` | No | `[]` | Array of strings | Tags for styling and filtering |

### Return Value

```json
{
  "workspaceId": "string",
  "elementId": "string",
  "containerId": "string",
  "name": "string",
  "type": "component",
  "technology": "string",
  "description": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID |
| `elementId` | `string` | Unique identifier for the component (used in relationships) |
| `containerId` | `string` | The parent container ID |
| `name` | `string` | The component name |
| `type` | `string` | Always "component" |
| `technology` | `string` | The technology specified |
| `description` | `string` | The description provided |

### Usage Example

**Add REST API components:**
```
To container "api" in workspace "ecommerce-platform-abc123", add:
1. Component "OrderController" - "Handles REST endpoints for orders" - Technology: "Spring MVC"
2. Component "ProductController" - "Handles product endpoints" - Technology: "Spring MVC"
3. Component "OrderService" - "Business logic for orders" - Technology: "Spring Service"
4. Component "OrderRepository" - "Data access layer for orders" - Technology: "Spring Data"
```

**Add web application components:**
```
To container "webapp" in workspace "frontend-architecture", add:
1. Component "OrderPage" - "React component for order management"
2. Component "OrderService" - "API client for orders" - Technology: "React Hooks"
3. Component "OrderStore" - "State management for orders" - Technology: "Redux"
```

**Add microservice components:**
```
To container "UserService" in workspace "microservices", add:
1. Component "AuthController" - "Authentication endpoints" - Technology: "FastAPI"
2. Component "UserRepository" - "User data access" - Technology: "SQLAlchemy"
3. Component "JwtValidator" - "JWT token validation" - Technology: "python-jose"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "elementId": "orderController",
  "containerId": "api",
  "name": "OrderController",
  "type": "component",
  "technology": "Spring MVC",
  "description": "Handles REST endpoints for orders"
}
```

### Common Use Cases

**Layered Web Application**
```
Add components to "API Server" container:

Controllers:
- OrderController: "Handles order REST endpoints"
- ProductController: "Handles product endpoints"

Services:
- OrderService: "Order business logic"
- PaymentService: "Payment processing logic"

Repositories:
- OrderRepository: "Order data access"
- ProductRepository: "Product data access"
```

**Frontend Application**
```
Add components to "Web App" container:

Pages:
- HomePage: "Landing page component"
- CheckoutPage: "Payment page component"

Services:
- OrderService: "API calls for orders"
- AuthService: "User authentication"

Stores:
- OrderStore: "Order state management"
- CartStore: "Shopping cart state"
```

**Node.js Microservice**
```
Add components to "User Service" container:
- AuthRoute: "Authentication routes" - Technology: "Express Routes"
- UserRoute: "User CRUD routes" - Technology: "Express Routes"
- AuthMiddleware: "JWT validation" - Technology: "Middleware"
- UserService: "User business logic" - Technology: "Service class"
- UserModel: "User database model" - Technology: "Mongoose"
```

### Tips and Warnings

> **Tip:** Use clear, descriptive names that indicate the component's responsibility (e.g., "OrderService" not "Service1").

> **Tip:** Components should represent cohesive units of functionality. If something seems like it should be split, create separate components.

> **Tip:** Save the `elementId` of components you'll create relationships from or show in component diagrams.

> **Important:** The `containerId` must match a container previously added with `add_container`.

> **Note:** Components represent code-level organization and are best shown in component diagrams of individual containers.

> **Best Practice:** Use naming conventions that match your code structure (e.g., Java package names, React component names, Python class names) to make the documentation mirror the actual implementation.

---

## add_relationship

Add a relationship (interaction or dependency) between two elements.

### When To Use

- Connecting people to systems they use
- Showing system-to-system interactions
- Defining container-to-container communication
- Documenting component dependencies
- Specifying communication protocols between elements
- Creating dependency diagrams
- Showing data flow between architectural elements

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID |
| `sourceId` | `string` | Yes | - | Min: 1 char | ID of the element initiating the relationship (person, system, container, or component) |
| `destinationId` | `string` | Yes | - | Min: 1 char | ID of the element being used or referenced |
| `description` | `string` | Yes | - | Min: 1 char<br>Max: 200 chars | Description of the interaction (e.g., "Uses", "Queries", "Sends events to") |
| `technology` | `string` | No | `""` | Max: 200 chars | Technology/protocol (e.g., "HTTPS", "REST API", "gRPC", "SQL", "Message Queue") |
| `tags` | `array` | No | `[]` | Array of strings | Tags for styling and filtering |

### Return Value

```json
{
  "workspaceId": "string",
  "relationshipId": "string",
  "sourceId": "string",
  "destinationId": "string",
  "description": "string",
  "technology": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `workspaceId` | `string` | The workspace ID |
| `relationshipId` | `string` | Unique identifier for the relationship |
| `sourceId` | `string` | The source element ID |
| `destinationId` | `string` | The destination element ID |
| `description` | `string` | The interaction description |
| `technology` | `string` | The technology/protocol specified |

### Usage Example

**Connect people to systems:**
```
In workspace "ecommerce-platform-abc123":
1. Add relationship from "customer" (person) to "ecommerce" (system) - Description: "Browses products and places orders" - Technology: "HTTPS"
2. Add relationship from "supportagent" (person) to "ecommerce" (system) - Description: "Provides customer support" - Technology: "HTTPS"
```

**Connect containers:**
```
In workspace "microservices":
1. Add relationship from "webapp" (container) to "api" (container) - Description: "Makes requests to" - Technology: "REST/HTTPS"
2. Add relationship from "api" (container) to "database" (container) - Description: "Reads/writes data" - Technology: "SQL"
3. Add relationship from "api" (container) to "messagqueue" (container) - Description: "Publishes events to" - Technology: "AMQP"
```

**Connect components:**
```
In workspace "api-design":
1. Add relationship from "ordercontroller" to "orderservice" - Description: "Calls" - Technology: "Synchronous"
2. Add relationship from "orderservice" to "orderrepository" - Description: "Uses" - Technology: "ORM"
3. Add relationship from "orderservice" to "paymentservice" - Description: "Calls to process payment" - Technology: "Service invocation"
```

### Response Example

```json
{
  "workspaceId": "ecommerce-platform-abc123",
  "relationshipId": "customer_ecommerce_1",
  "sourceId": "customer",
  "destinationId": "ecommerce",
  "description": "Browses products and places orders",
  "technology": "HTTPS"
}
```

### Common Use Cases

**System Context Diagram**
```
Create relationships for system context:
- Customer -> ECommerce: "Browses and purchases" (HTTPS)
- ECommerce -> PaymentGateway: "Processes payments" (HTTPS)
- ECommerce -> EmailService: "Sends confirmation" (HTTPS)
```

**Container Architecture**
```
Create relationships between containers:
- WebApp -> API: "Makes requests to" (REST/HTTPS)
- API -> Database: "Reads/writes data" (SQL)
- API -> Cache: "Caches data" (Redis protocol)
- API -> MessageQueue: "Publishes events" (AMQP)
- Worker -> MessageQueue: "Consumes events" (AMQP)
```

**Component Interactions**
```
Create relationships for Java API:
- OrderController -> OrderService: "Calls" (Method invocation)
- OrderService -> OrderRepository: "Uses" (Spring Data)
- OrderService -> PaymentService: "Calls" (Service invocation)
- OrderService -> Logger: "Writes logs to" (SLF4J)
```

**Complex Microservices**
```
Create relationships between microservices:
- UserService -> AuthService: "Validates tokens" (gRPC)
- OrderService -> UserService: "Retrieves user data" (REST)
- OrderService -> PaymentService: "Processes payment" (REST)
- OrderService -> NotificationService: "Sends notifications" (Message Queue)
- NotificationService -> EmailProvider: "Sends emails" (SMTP)
```

### Tips and Warnings

> **Tip:** Relationship descriptions should be directional and descriptive. Use verbs like "Uses", "Calls", "Queries", "Publishes to", "Listens to".

> **Tip:** Specify the technology/protocol clearly - this helps readers understand communication patterns (REST, gRPC, SQL, Message Queue, etc.).

> **Tip:** You can create relationships between any two elements, including cross-level relationships (person to container, system to component).

> **Important:** Both `sourceId` and `destinationId` must be IDs of elements that were previously added to the model.

> **Note:** Relationships are directional - "A uses B" is different from "B uses A".

> **Best Practice:** Create relationships that clarify the architecture:
> 1. Person -> System relationships show who uses what
> 2. System -> System relationships show external dependencies
> 3. Container -> Container relationships show deployment dependencies
> 4. Component -> Component relationships show code-level dependencies

> **Documentation Value:** Use relationship descriptions and technologies to document important architectural decisions and integration points.

---

## Model Building Workflow

### Recommended Order

1. **Create all People** - Identify all user roles and external actors
2. **Create all Software Systems** - Define primary system and external systems
3. **Add Containers** - Decompose systems into deployable units
4. **Add Components** - Further decompose containers into code-level units
5. **Create Relationships** - Connect all elements with meaningful interactions

### Example Workflow

```
1. List all workspaces to find workspace ID
   → list_workspaces

2. Create people
   → add_person (Customer)
   → add_person (Admin)

3. Create systems
   → add_software_system (E-Commerce Platform)
   → add_software_system (Payment Gateway)

4. Add containers to systems
   → add_container (to E-Commerce: Web App, API, Database)
   → add_container (to Payment: API, Database)

5. Add components to containers (optional, for detailed design)
   → add_component (to Web App: HomePage, CheckoutPage)
   → add_component (to API: OrderController, OrderService)

6. Create all relationships
   → add_relationship (Customer -> E-Commerce)
   → add_relationship (E-Commerce -> PaymentGateway)
   → add_relationship (WebApp -> API)
   → add_relationship (API -> Database)
```

---

## Common Patterns

### Simple E-Commerce

```
People:
- Customer (browses and purchases)

Systems:
- E-Commerce Platform (internal)

Containers:
- Web Application (React)
- API Server (Java)
- Database (PostgreSQL)

Relationships:
- Customer -> ECommerce (HTTPS)
- WebApp -> API (REST)
- API -> Database (SQL)
```

### Microservices

```
People:
- Customer
- Admin

Systems:
- User Service
- Order Service
- Payment Service

Containers (for each system):
- API (Node.js, Java, Python)
- Database (PostgreSQL)

Relationships:
- Customer -> Services (HTTPS/gRPC)
- Services -> Databases (SQL)
- Services -> Services (REST/gRPC)
- Message Queue for async
```

### Multi-Tenant SaaS

```
People:
- End User
- Account Manager
- System Admin

Systems:
- Main App (Internal)
- API Gateway (Internal)
- Analytics (Internal)
- Stripe (External)
- Twilio (External)

Containers:
- Web App, API, Database for each system

Relationships:
- Users -> Main App, API Gateway
- Main App -> Stripe, Twilio
- Analytics -> all services
```

---

## Navigation

- [← Back to Tools Overview](overview.md)
- [Workspace Management Tools ←](workspace-tools.md)
- [View Management Tools →](view-tools.md)
- [Export Tools →](export-tools.md)

---

## Related Resources

- [C4 Model Documentation](https://c4model.com) - Learn about the C4 model structure
- [Structurizr DSL Guide](https://github.com/structurizr/dsl) - Details on DSL syntax
- [Architecture Patterns](../getting-started/patterns.md) - Common architectural patterns
- [View Creation Guide](view-tools.md) - How to visualize your model
