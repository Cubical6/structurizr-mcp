# E-Commerce Platform Architecture

- [Introduction](#introduction)
- [Learning Objectives](#learning-objectives)
- [Prerequisites](#prerequisites)
- [Architecture Overview](#architecture-overview)
- [Step 1: Create the Workspace](#step-1-create-the-workspace)
- [Step 2: Define People and External Systems](#step-2-define-people-and-external-systems)
- [Step 3: Build the Core Systems](#step-3-build-the-core-systems)
- [Step 4: Add Containers to Web Application](#step-4-add-containers-to-web-application)
- [Step 5: Add Containers to Order Service](#step-5-add-containers-to-order-service)
- [Step 6: Define All Relationships](#step-6-define-all-relationships)
- [Step 7: Create Comprehensive Views](#step-7-create-comprehensive-views)
- [Step 8: Add Dynamic Views](#step-8-add-dynamic-views)
- [Step 9: Document the Architecture](#step-9-document-the-architecture)
- [Step 10: Export and Analyze](#step-10-export-and-analyze)
- [Understanding the Architecture](#understanding-the-architecture)
- [What You've Learned](#what-youve-learned)
- [Next Steps](#next-steps)

---

## Introduction

Welcome to the E-Commerce Platform Architecture example! This comprehensive tutorial demonstrates how to model a complete, production-ready e-commerce system using the Structurizr MCP Server.

This example goes beyond basic C4 modeling to show you how to:
- Model complex multi-system architectures
- Integrate with multiple external services
- Create dynamic views showing runtime behavior
- Document architectural decisions
- Analyze system dependencies

**Time required:** 30-40 minutes

---

## Learning Objectives

By completing this tutorial, you will:

- ✅ Model a complete e-commerce platform with multiple systems
- ✅ Define clear boundaries between internal and external systems
- ✅ Create container-level architecture for microservices
- ✅ Document integration points with third-party services
- ✅ Build dynamic views showing checkout and order flows
- ✅ Add comprehensive documentation and ADRs
- ✅ Analyze dependencies and validate architecture
- ✅ Export to multiple diagram formats

---

## Prerequisites

Before starting, ensure you have:

- ✅ Completed the [Basic C4 Model Tutorial](basic-c4.md)
- ✅ Understanding of e-commerce domain concepts
- ✅ Familiarity with microservices architecture
- ✅ Claude Desktop configured with Structurizr MCP Server

> **New to microservices?** This example uses a hybrid architecture with some monolithic and some microservice components, making it accessible to all experience levels.

---

## Architecture Overview

We'll build an e-commerce platform called **ShopFlow** with the following capabilities:

### User-Facing Systems
- **Web Application** - Customer-facing online store
- **Mobile App** - Native iOS/Android shopping apps
- **Admin Portal** - Back-office management interface

### Core Systems
- **Order Service** - Order processing and management
- **Inventory Service** - Product catalog and stock management
- **Customer Service** - User accounts and authentication

### External Integrations
- **Payment Gateway** - Stripe payment processing
- **Shipping Service** - FedEx shipping integration
- **Email Service** - SendGrid transactional emails
- **Analytics Platform** - Google Analytics tracking

### Technology Stack
- Frontend: React (web), React Native (mobile)
- Backend: Node.js microservices
- Databases: PostgreSQL, MongoDB, Redis
- Message Queue: RabbitMQ
- API Gateway: Kong

---

## Step 1: Create the Workspace

Let's start by creating our workspace.

### Ask Claude

```
Create a new Structurizr workspace named "ShopFlow E-Commerce Platform"
with description "Complete architecture for a modern e-commerce platform with web, mobile, and admin interfaces"
```

### Expected Response

```json
{
  "success": true,
  "workspaceId": "ws_shopflow_001",
  "name": "ShopFlow E-Commerce Platform",
  "description": "Complete architecture for a modern e-commerce platform...",
  "message": "Workspace 'ShopFlow E-Commerce Platform' created successfully"
}
```

---

## Step 2: Define People and External Systems

### Step 2.1: Add People

**Ask Claude:**

```
Add three people to the workspace:
1. "Customer" - "A person who browses products and makes purchases"
2. "Admin User" - "Back-office staff who manage products, orders, and inventory"
3. "Customer Support Agent" - "Support staff who help customers with orders and issues"
```

### Step 2.2: Add External Systems

**Ask Claude:**

```
Add the following external systems with tag "External System":
1. "Payment Gateway" - "Stripe payment processing service for handling credit card transactions"
2. "Shipping Service" - "FedEx shipping API for calculating rates and creating shipping labels"
3. "Email Service" - "SendGrid transactional email service for order confirmations and notifications"
4. "Analytics Platform" - "Google Analytics for tracking user behavior and conversions"
5. "SMS Service" - "Twilio SMS service for order status notifications"
```

> **Note:** Tagging external systems helps visualize system boundaries and dependencies clearly.

---

## Step 3: Build the Core Systems

### Step 3.1: Add Web Application System

**Ask Claude:**

```
Add a software system named "Web Application"
with description "Customer-facing e-commerce website for browsing products and placing orders"
```

### Step 3.2: Add Mobile App System

**Ask Claude:**

```
Add a software system named "Mobile App"
with description "Native iOS and Android mobile shopping applications"
```

### Step 3.3: Add Admin Portal System

**Ask Claude:**

```
Add a software system named "Admin Portal"
with description "Back-office web application for managing products, orders, inventory, and customers"
```

### Step 3.4: Add Backend Services

**Ask Claude:**

```
Add the following software systems for backend services:
1. "Order Service" - "Microservice responsible for order processing, payment coordination, and order fulfillment"
2. "Inventory Service" - "Microservice managing product catalog, stock levels, and product search"
3. "Customer Service" - "Microservice handling user authentication, profiles, and preferences"
4. "Notification Service" - "Microservice for sending emails, SMS, and push notifications"
```

### Step 3.5: Add Infrastructure Components

**Ask Claude:**

```
Add these infrastructure systems with tag "Infrastructure":
1. "API Gateway" - "Kong API Gateway for routing, authentication, and rate limiting"
2. "Message Queue" - "RabbitMQ message broker for asynchronous communication between services"
3. "Cache" - "Redis distributed cache for session management and performance"
```

> **Great!** You've defined all the major systems in your e-commerce platform.

---

## Step 4: Add Containers to Web Application

Let's zoom into the Web Application system and define its containers.

### Ask Claude

```
Add the following containers to the Web Application system:
1. "React SPA" - "Single-page application providing the shopping interface" using "React, Redux, TypeScript"
2. "Web Server" - "Serves static assets and handles server-side rendering" using "Node.js, Express"
3. "CDN" - "Content delivery network for static assets" using "Cloudflare" with tag "External"
```

### Response

You should see three containers created within the Web Application system.

> **Tip:** The CDN is technically external but we model it as a container because it's integral to the web application's operation.

---

## Step 5: Add Containers to Order Service

The Order Service is our most complex microservice. Let's define its internal structure.

### Step 5.1: Add Application Containers

**Ask Claude:**

```
Add these containers to the Order Service:
1. "Order API" - "REST API for order operations" using "Node.js, Express, TypeScript"
2. "Order Database" - "Stores order data, line items, and status history" using "PostgreSQL" with tag "Database"
3. "Order Event Handler" - "Processes order-related events from message queue" using "Node.js Worker"
```

### Step 5.2: Add Components to Order API

**Ask Claude:**

```
Add these components to the Order API container:
1. "Order Controller" - "Handles HTTP requests for order operations" using "Express Router"
2. "Payment Orchestrator" - "Coordinates payment processing workflow" using "TypeScript Service"
3. "Order Repository" - "Data access layer for orders" using "TypeORM Repository"
4. "Order Validator" - "Validates order data and business rules" using "TypeScript Service"
5. "Shipping Coordinator" - "Manages shipping label creation and tracking" using "TypeScript Service"
```

> **Excellent!** You've documented the internal structure of a critical microservice.

---

## Step 6: Define All Relationships

Now let's connect all these systems and containers with relationships.

### Step 6.1: Customer Interactions

**Ask Claude:**

```
Create relationships showing how customers interact with the system:
1. Customer uses Web Application - "Browses products and places orders using" with technology "HTTPS"
2. Customer uses Mobile App - "Shops and tracks orders using"
3. Admin User uses Admin Portal - "Manages products and orders using" with technology "HTTPS"
4. Customer Support Agent uses Admin Portal - "Views and manages customer orders using" with technology "HTTPS"
```

### Step 6.2: Frontend to Backend Communication

**Ask Claude:**

```
Create these frontend-to-backend relationships:
1. Web Application uses API Gateway - "Makes API calls via" with technology "HTTPS/REST"
2. Mobile App uses API Gateway - "Makes API calls via" with technology "HTTPS/REST"
3. Admin Portal uses API Gateway - "Makes API calls via" with technology "HTTPS/REST"
```

### Step 6.3: API Gateway Routing

**Ask Claude:**

```
Create relationships from API Gateway to backend services:
1. API Gateway uses Order Service - "Routes order requests to" with technology "HTTP/REST"
2. API Gateway uses Inventory Service - "Routes product requests to" with technology "HTTP/REST"
3. API Gateway uses Customer Service - "Routes user requests to" with technology "HTTP/REST"
```

### Step 6.4: Service-to-Service Communication

**Ask Claude:**

```
Create these service integration relationships:
1. Order Service uses Message Queue - "Publishes order events to" with technology "AMQP"
2. Order Service uses Customer Service - "Validates customer information with" with technology "REST API"
3. Order Service uses Inventory Service - "Checks stock availability with" with technology "REST API"
4. Order Service uses Payment Gateway - "Processes payments via" with technology "Stripe API"
5. Order Service uses Shipping Service - "Creates shipping labels via" with technology "FedEx API"
6. Notification Service uses Message Queue - "Consumes order events from" with technology "AMQP"
7. Notification Service uses Email Service - "Sends emails via" with technology "SendGrid API"
8. Notification Service uses SMS Service - "Sends SMS via" with technology "Twilio API"
```

### Step 6.5: Database Relationships

**Ask Claude:**

```
Create these data access relationships:
1. Order Service uses Order Database - "Reads from and writes to" with technology "PostgreSQL/TypeORM"
2. Inventory Service uses its database - "Manages product data in" with technology "MongoDB"
3. Customer Service uses its database - "Stores user data in" with technology "PostgreSQL"
```

### Step 6.6: Caching Relationships

**Ask Claude:**

```
Create caching relationships:
1. API Gateway uses Cache - "Stores session data in" with technology "Redis"
2. Inventory Service uses Cache - "Caches product data in" with technology "Redis"
3. Customer Service uses Cache - "Caches user sessions in" with technology "Redis"
```

### Step 6.7: Analytics Tracking

**Ask Claude:**

```
Create analytics relationships:
1. Web Application uses Analytics Platform - "Sends user behavior events to" with technology "Google Analytics"
2. Mobile App uses Analytics Platform - "Sends app usage events to" with technology "Google Analytics SDK"
```

> **Perfect!** You've created a comprehensive relationship map showing all system interactions.

---

## Step 7: Create Comprehensive Views

### Step 7.1: System Context View

**Ask Claude:**

```
Create a system context view for the Web Application
with key "WebAppContext"
and description "System context showing the web application and its external dependencies"
and apply left-to-right auto-layout
```

### Step 7.2: System Landscape View

**Ask Claude:**

```
Create a system landscape view showing all systems
(Note: Request Claude to help create a comprehensive view showing all systems in the platform)
```

### Step 7.3: Container View for Order Service

**Ask Claude:**

```
Create a container view for the Order Service
with key "OrderServiceContainers"
and description "Container diagram showing the internal structure of the Order Service"
and apply top-to-bottom auto-layout
```

### Step 7.4: Component View for Order API

**Ask Claude:**

```
Create a component view for the Order API container
with key "OrderAPIComponents"
and description "Component diagram showing the internal components of the Order API"
and apply left-to-right auto-layout
```

> **Excellent!** You now have multiple views showing different perspectives on your architecture.

---

## Step 8: Add Dynamic Views

Dynamic views show runtime behavior and sequence of interactions. Let's create views for key workflows.

### Step 8.1: Checkout Flow Dynamic View

**Ask Claude:**

```
Create a dynamic view for the Order Service with key "CheckoutFlow"
and description "Shows the sequence of interactions during customer checkout"

Then add these steps to show the checkout workflow:
1. Customer submits order to Web Application
2. Web Application sends order request to API Gateway
3. API Gateway routes to Order Service
4. Order Service validates with Inventory Service
5. Order Service processes payment with Payment Gateway
6. Order Service publishes order event to Message Queue
7. Order Service returns success to API Gateway
8. Notification Service consumes event from Message Queue
9. Notification Service sends confirmation via Email Service
```

**Expected approach:**

Claude will help you create a dynamic view showing the temporal sequence of the checkout process.

> **Note:** Dynamic views are powerful for documenting complex workflows and helping developers understand system behavior.

### Step 8.2: Order Fulfillment Flow

**Ask Claude:**

```
Create a dynamic view showing the order fulfillment process:
1. Order Event Handler picks up order from Message Queue
2. Order Service creates shipping label via Shipping Service
3. Order Service updates Order Database with tracking info
4. Order Service publishes shipping event to Message Queue
5. Notification Service sends shipping notification via SMS Service
```

---

## Step 9: Document the Architecture

### Step 9.1: Add System Overview

**Ask Claude:**

```
Add a documentation section titled "System Overview" with this content:

"ShopFlow is a modern e-commerce platform designed to handle high-volume online retail operations.

## Architecture Principles

1. **Microservices Architecture**: Core business capabilities are decomposed into independent services
2. **API-First Design**: All services expose RESTful APIs through a central API gateway
3. **Event-Driven Communication**: Services communicate asynchronously via message queue for loose coupling
4. **Scalability**: Each service can scale independently based on load
5. **Resilience**: Circuit breakers and retry logic protect against cascading failures

## Key Components

- **Web Application**: React-based SPA for optimal user experience
- **Mobile App**: Native iOS/Android apps built with React Native
- **Order Service**: Handles the critical order processing workflow
- **Inventory Service**: Manages product catalog and real-time stock levels
- **Customer Service**: Handles authentication, user profiles, and preferences

## External Integrations

- **Stripe**: PCI-compliant payment processing
- **FedEx**: Shipping rate calculation and label generation
- **SendGrid**: Transactional emails (order confirmations, shipping notifications)
- **Twilio**: SMS notifications for order updates
- **Google Analytics**: User behavior tracking and conversion analytics"
```

### Step 9.2: Add Architecture Decision Records

**Ask Claude:**

```
Add an ADR with:
- ID: "001"
- Date: "2024-01-15"
- Title: "Use Microservices Architecture for Core Services"
- Status: "Accepted"
- Content:

"## Context

We need to build a scalable e-commerce platform that can handle:
- Variable traffic patterns (flash sales, seasonal peaks)
- Frequent updates to different business capabilities
- Multiple development teams working in parallel

## Decision

We will use a microservices architecture for core business capabilities (Orders, Inventory, Customers) while keeping the frontend applications as separate systems.

## Consequences

Positive:
- Independent deployment and scaling of services
- Team autonomy and faster development cycles
- Technology flexibility per service
- Better fault isolation

Negative:
- Increased operational complexity
- Need for robust monitoring and distributed tracing
- Network latency between services
- Data consistency challenges

## Alternatives Considered

1. Monolithic architecture - Rejected due to scaling and team coordination challenges
2. Serverless functions - Rejected due to vendor lock-in and cold start issues"
```

### Step 9.3: Add Technology Choices ADR

**Ask Claude:**

```
Add an ADR documenting the choice of Node.js:
- ID: "002"
- Date: "2024-01-20"
- Title: "Use Node.js and TypeScript for Backend Services"
- Status: "Accepted"
- Content explaining the decision to use Node.js with TypeScript for microservices
```

---

## Step 10: Export and Analyze

### Step 10.1: Validate the Workspace

**Ask Claude:**

```
Validate the workspace to ensure all relationships and elements are correctly defined
```

**Expected Response:**

```json
{
  "valid": true,
  "errors": [],
  "warnings": [],
  "message": "Workspace validation successful"
}
```

### Step 10.2: Analyze Dependencies

**Ask Claude:**

```
Analyze dependencies for the Order Service
```

**Expected Response:**

You'll see a complete analysis showing:
- **Incoming dependencies**: What depends on Order Service
- **Outgoing dependencies**: What Order Service depends on
- **Transitive dependencies**: Indirect dependencies

This helps identify:
- Tight coupling issues
- Single points of failure
- Opportunities for caching or optimization

### Step 10.3: Export to DSL

**Ask Claude:**

```
Export the workspace to DSL format
```

This gives you a complete, version-controllable definition of your architecture.

### Step 10.4: Export Views to Different Formats

**Ask Claude:**

```
Export the OrderServiceContainers view to PlantUML
```

Then:

```
Export the CheckoutFlow dynamic view to Mermaid
```

> **Success!** You now have your architecture documented in multiple formats for different audiences and tools.

---

## Understanding the Architecture

Let's examine key architectural patterns in the ShopFlow platform.

### Layered Architecture Pattern

```
┌─────────────────────────────────────┐
│  Presentation Layer                 │
│  (Web, Mobile, Admin)               │
└─────────────────┬───────────────────┘
                  │
┌─────────────────▼───────────────────┐
│  API Gateway Layer                  │
│  (Routing, Auth, Rate Limiting)     │
└─────────────────┬───────────────────┘
                  │
┌─────────────────▼───────────────────┐
│  Business Logic Layer               │
│  (Microservices)                    │
└─────────────────┬───────────────────┘
                  │
┌─────────────────▼───────────────────┐
│  Data Layer                         │
│  (Databases, Cache)                 │
└─────────────────────────────────────┘
```

### Microservices Pattern

Each service owns its data and exposes a clean API:

```dsl
orderService -> orderDatabase "owns"
inventoryService -> inventoryDatabase "owns"
customerService -> customerDatabase "owns"
```

This ensures:
- **Data autonomy**: No shared databases
- **Bounded contexts**: Clear service boundaries
- **Independent deployment**: Services can evolve separately

### Event-Driven Pattern

Services communicate asynchronously via events:

```dsl
orderService -> messageQueue "publishes OrderPlaced event"
notificationService -> messageQueue "subscribes to OrderPlaced event"
```

Benefits:
- **Loose coupling**: Services don't directly depend on each other
- **Resilience**: Message queue buffers during service outages
- **Scalability**: Consumers can scale independently

### API Gateway Pattern

```dsl
webApp -> apiGateway -> orderService
mobileApp -> apiGateway -> inventoryService
adminPortal -> apiGateway -> customerService
```

The gateway provides:
- **Single entry point**: Simplified client configuration
- **Cross-cutting concerns**: Auth, logging, rate limiting
- **Protocol translation**: HTTPS to internal protocols

---

## What You've Learned

Congratulations! You've built a production-ready e-commerce architecture. Here's what you've mastered:

### Architecture Patterns

- ✅ **Microservices Architecture** - Decomposing systems into independent services
- ✅ **API Gateway Pattern** - Centralizing API access and cross-cutting concerns
- ✅ **Event-Driven Architecture** - Asynchronous communication via message queues
- ✅ **Database per Service** - Data autonomy and bounded contexts
- ✅ **External System Integration** - Clean boundaries with third-party services

### C4 Modeling Techniques

- ✅ **System Landscape** - Showing multiple systems and their relationships
- ✅ **System Context** - Documenting external dependencies clearly
- ✅ **Container Diagrams** - Detailing technology choices and deployment units
- ✅ **Component Diagrams** - Internal structure of critical services
- ✅ **Dynamic Views** - Runtime behavior and workflows

### Documentation Practices

- ✅ **Architecture Overview** - High-level system description
- ✅ **Architecture Decision Records** - Documenting key decisions and trade-offs
- ✅ **Relationship Tagging** - Technology details on integrations
- ✅ **Element Tagging** - Categorizing systems (External, Infrastructure, Database)

### Analysis Skills

- ✅ **Dependency Analysis** - Understanding coupling and dependencies
- ✅ **Workspace Validation** - Ensuring model correctness
- ✅ **Multi-Format Export** - DSL, PlantUML, Mermaid for different audiences

---

## Next Steps

### Enhance This Architecture

**Add more detail:**

```
Add a component view for the Inventory Service showing:
- Product Search Component
- Stock Manager Component
- Price Calculator Component
- Product Repository
```

**Add deployment architecture:**

```
Create a deployment diagram showing:
- Kubernetes clusters
- Database instances
- External service endpoints
- Load balancers and ingress
```

**Add more ADRs:**

```
Document decisions about:
- Database technology choices (PostgreSQL vs MongoDB)
- Message queue selection (RabbitMQ vs Kafka)
- Caching strategy (Redis configuration)
- API versioning approach
```

### Apply to Your Project

- Model your organization's e-commerce or SaaS platform
- Document service boundaries and dependencies
- Create ADRs for major architectural decisions
- Generate diagrams for architecture review meetings

### Explore Advanced Topics

- **[Microservices Example](microservices.md)** - Deep dive into service mesh, observability
- **[Security Best Practices](../advanced/security.md)** - Secure your architecture
- **[Performance Optimization](../advanced/performance.md)** - Scale your diagrams
- **[Cloud Integration](../advanced/cloud-integration.md)** - Deploy to Structurizr Cloud

### Learn More Patterns

- **[MCP Prompts](../prompts/overview.md)** - Use AI to analyze your architecture
- **[Analysis Tools](../tools/analysis-tools.md)** - Advanced dependency analysis
- **[Batch Operations](../advanced/batch-operations.md)** - Efficient bulk updates

> **Tip:** Use the `analyze_architecture` prompt to get AI-powered insights about your e-commerce architecture: "Analyze the ShopFlow architecture for scalability, security, and maintainability issues."

---

## Common E-Commerce Patterns

Here are additional patterns you can add to your architecture:

### CQRS Pattern

Separate read and write models for better performance:

```
Add a Read Service for product searches that uses:
- Elasticsearch for full-text search
- Materialized views from Inventory Database
- Separate scaling from write operations
```

### Saga Pattern

Distributed transactions across microservices:

```
Add an Order Saga Coordinator that manages:
1. Reserve inventory
2. Process payment
3. Create shipment
4. Send notifications
With compensating transactions if any step fails
```

### API Versioning

Support multiple API versions:

```
Add versioned API containers:
- API Gateway routes /v1/* to Order Service V1
- API Gateway routes /v2/* to Order Service V2
```

### Cache-Aside Pattern

```
Add explicit caching flow:
1. Check Redis cache
2. If miss, query database
3. Update cache
4. Return result
```

---

## Troubleshooting

### Common Issues

**"Too many relationships to visualize clearly"**
- Create focused views on specific subsystems
- Use tags to filter elements in views
- Consider multiple container views for different services

**"Services have circular dependencies"**
- Review your service boundaries
- Consider extracting shared functionality into a new service
- Use events instead of direct service calls

**"Unclear which database belongs to which service"**
- Use consistent naming: `{ServiceName} Database`
- Always tag databases with "Database" tag
- Document database ownership in relationships

**"Dynamic view is too complex"**
- Break into multiple dynamic views (one per workflow)
- Focus on happy path first, create separate error flow views
- Limit to 7-10 steps per view for clarity

> **Need help?** See the [Troubleshooting Guide](../troubleshooting/common-issues.md) for detailed solutions.

---

<p align="right">
  <strong>Previous:</strong> <a href="basic-c4.md">← Basic C4 Model</a><br>
  <strong>Next:</strong> <a href="microservices.md">Microservices Architecture →</a>
</p>
