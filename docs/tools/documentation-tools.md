# Documentation Tools

- [Introduction](#introduction)
- [add_documentation_section](#add_documentation_section)
- [add_adr](#add_adr)

---

## Introduction

The Documentation Tools enable you to add comprehensive written documentation to your Structurizr workspaces. While C4 diagrams visualize your architecture, textual documentation provides crucial context, rationale, and detailed explanations that diagrams alone cannot convey.

### Documentation in Structurizr

Structurizr supports two main types of documentation:

1. **Documentation Sections** - Free-form documentation about any aspect of your architecture. These sections can cover topics like architecture overview, deployment, operations, development guidelines, or any other relevant information.

2. **Architecture Decision Records (ADRs)** - Structured records of important architectural decisions, following the ADR format popularized by Michael Nygard. ADRs capture the context, decision, and consequences of architectural choices.

### Format Support

Both documentation sections and ADRs support **Markdown** format, allowing you to:
- Use headings, lists, and emphasis for structured content
- Include code blocks with syntax highlighting
- Add links to external resources
- Embed images (when hosted externally)
- Create tables for structured data
- Use blockquotes for important callouts

> **Note:** The MCP server stores documentation as metadata within the workspace model. This keeps your documentation co-located with your architecture diagrams, ensuring consistency and version control.

### When to Use Documentation

**Use Documentation Sections for:**
- Architecture overviews and high-level descriptions
- Deployment and infrastructure documentation
- Development setup and guidelines
- Operations and runbooks
- Quality attributes and constraints
- Security and compliance considerations
- Integration patterns and protocols

**Use ADRs for:**
- Technology stack decisions (languages, frameworks, databases)
- Architectural pattern choices (microservices, monolith, event-driven)
- Infrastructure decisions (cloud provider, deployment strategy)
- Security and authentication mechanisms
- Third-party service selections
- Breaking changes or major refactoring decisions

> **Best Practice:** Combine diagrams with documentation. Use C4 diagrams to show structure and relationships, and use documentation sections to explain the "why" behind architectural choices, operational concerns, and implementation details.

---

## add_documentation_section

Add a documentation section to the workspace with a title and Markdown content.

### When To Use

- Providing architectural overview and context
- Documenting deployment procedures and infrastructure
- Explaining system constraints and quality attributes
- Describing development workflows and guidelines
- Recording operational procedures and troubleshooting guides
- Documenting integration patterns and APIs
- Adding contextual information that complements diagrams

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID to add documentation to |
| `title` | `string` | Yes | - | Min: 1 char<br>Max: 200 chars | The section title (e.g., "Architecture Overview", "Deployment Guide") |
| `content` | `string` | Yes | - | Min: 1 char | The section content in Markdown format |

### Return Value

```json
{
  "success": true,
  "workspaceId": "string",
  "section": {
    "id": "string",
    "title": "string",
    "format": "Markdown",
    "createdAt": "string (ISO 8601)"
  },
  "message": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `success` | `boolean` | Always `true` for successful operations |
| `workspaceId` | `string` | The workspace ID |
| `section.id` | `string` | Unique identifier for the documentation section |
| `section.title` | `string` | The section title |
| `section.format` | `string` | Always "Markdown" |
| `section.createdAt` | `string` | ISO 8601 timestamp of creation |
| `message` | `string` | Confirmation message |

### Usage Example

**Add an architecture overview:**
```
Add a documentation section to workspace "ecommerce-platform-abc123" titled "Architecture Overview" with content explaining the overall system design and key architectural decisions
```

**Add deployment documentation:**
```
Add documentation to workspace "microservices" with title "Deployment Guide" describing the CI/CD pipeline and deployment process
```

**Add operational procedures:**
```
Create a documentation section in workspace "payment-service" titled "Incident Response" with runbook procedures for common operational issues
```

### Response Example

```json
{
  "success": true,
  "workspaceId": "ecommerce-platform-abc123",
  "section": {
    "id": "doc_a1b2c3d4e5f6g7h8",
    "title": "Architecture Overview",
    "format": "Markdown",
    "createdAt": "2025-11-16T14:30:00.123456+00:00"
  },
  "message": "Documentation section 'Architecture Overview' added successfully"
}
```

### Common Use Cases

**Architecture Overview**
```
Add a documentation section to the "enterprise-platform" workspace titled "Architecture Overview" with this content:

# Architecture Overview

This system follows a microservices architecture pattern with the following key characteristics:

## Design Principles
- Domain-driven design with bounded contexts
- Event-driven communication between services
- API-first design approach
- Polyglot persistence (different databases for different needs)

## Key Components
1. **API Gateway** - Single entry point for all client requests
2. **Service Mesh** - Handles service-to-service communication
3. **Event Bus** - Asynchronous event distribution
4. **Shared Services** - Authentication, logging, monitoring

## Quality Attributes
- **Scalability**: Horizontal scaling for individual services
- **Resilience**: Circuit breakers and fallback mechanisms
- **Security**: OAuth2 + JWT for authentication
- **Observability**: Distributed tracing and centralized logging
```

**Deployment Guide**
```
Create documentation section titled "Deployment Guide" in workspace "saas-application":

# Deployment Guide

## Prerequisites
- Kubernetes cluster (version 1.24+)
- Helm 3.x installed
- kubectl configured for the target cluster
- Docker registry credentials

## Deployment Steps

1. **Build Docker images**
   ```bash
   docker build -t myapp/api:latest ./api
   docker build -t myapp/web:latest ./web
   ```

2. **Push to registry**
   ```bash
   docker push myapp/api:latest
   docker push myapp/web:latest
   ```

3. **Deploy with Helm**
   ```bash
   helm upgrade --install myapp ./helm/myapp \
     --namespace production \
     --set image.tag=latest
   ```

## Environment Configuration
- **Production**: prod.example.com
- **Staging**: staging.example.com
- **Development**: dev.example.com
```

**Integration Documentation**
```
Add documentation section "External Integrations" to workspace "payment-platform":

# External Integrations

## Payment Providers

### Stripe Integration
- **Purpose**: Credit card processing
- **Authentication**: API keys (secret stored in vault)
- **Endpoints**:
  - Create payment: `POST /v1/payment_intents`
  - Refund: `POST /v1/refunds`
- **Webhooks**: `https://api.ourapp.com/webhooks/stripe`
- **Error Handling**: Retry with exponential backoff

### PayPal Integration
- **Purpose**: Alternative payment method
- **Authentication**: OAuth 2.0
- **SDK**: PayPal REST SDK v2
- **Supported Operations**: Payment, refund, subscription

## Third-Party Services

### SendGrid (Email)
- Transactional email delivery
- API key authentication
- Rate limit: 100 emails/second

### Twilio (SMS)
- Two-factor authentication codes
- Order notifications
- Rate limit: 10 messages/second
```

**Development Guidelines**
```
Create documentation titled "Development Guidelines" in workspace "platform":

# Development Guidelines

## Code Standards
- Follow PSR-12 coding style
- Use type hints for all parameters and return values
- Write PHPDoc for all public methods
- Minimum 80% code coverage for unit tests

## Branch Strategy
- `main` - production-ready code
- `develop` - integration branch
- `feature/*` - new features
- `bugfix/*` - bug fixes
- `hotfix/*` - production hotfixes

## Pull Request Process
1. Create feature branch from `develop`
2. Write tests for new functionality
3. Ensure all tests pass (`./vendor/bin/phpunit`)
4. Run static analysis (`./vendor/bin/phpstan`)
5. Create PR with description and test plan
6. Require 2 approvals before merge

## Deployment Process
- Merge to `develop` triggers staging deployment
- Merge to `main` triggers production deployment
- Use semantic versioning for releases
```

**Security Documentation**
```
Add section "Security Considerations" to workspace "financial-platform":

# Security Considerations

## Authentication & Authorization
- OAuth 2.0 with PKCE flow for web clients
- JWT tokens with 15-minute expiration
- Refresh tokens with 7-day expiration
- Role-based access control (RBAC)

## Data Protection
- All PII encrypted at rest (AES-256)
- TLS 1.3 for data in transit
- Database encryption enabled
- Regular key rotation (90 days)

## Security Monitoring
- Failed login attempt tracking
- Anomaly detection for unusual access patterns
- Real-time alerts for security events
- Quarterly security audits

## Compliance
- PCI DSS Level 1 compliant
- GDPR data protection measures
- SOC 2 Type II certified
- Regular penetration testing
```

### Tips and Warnings

> **Tip:** Use descriptive titles that clearly indicate the documentation topic. Good titles make it easier to find relevant documentation later.

> **Tip:** Organize content with Markdown headings (##, ###) to create a clear structure. This improves readability and makes the documentation scannable.

> **Tip:** Include code examples in documentation sections using Markdown code blocks with language identifiers (```bash, ```php, ```json) for syntax highlighting.

> **Tip:** Add multiple documentation sections to cover different aspects of your architecture. There's no limit to the number of sections you can add.

> **Note:** Each documentation section is assigned a unique ID (`doc_` prefix) that you can use to reference it later. The ID is automatically generated.

> **Best Practice:** Keep documentation close to the diagrams it describes. If you have a container view showing your microservices, add a documentation section explaining the communication patterns, data flow, and operational considerations.

> **Best Practice:** Update documentation when the architecture changes. Treat documentation as code - version it, review it, and keep it synchronized with your diagrams.

> **Warning:** Documentation sections are stored as metadata in the workspace. Very large content (>50KB) may impact workspace load times. Consider breaking large documentation into multiple focused sections.

---

## add_adr

Add an Architecture Decision Record (ADR) to document an important architectural decision.

### When To Use

- Recording technology choices (programming languages, frameworks, databases)
- Documenting architectural patterns (microservices, event-driven, layered)
- Capturing infrastructure decisions (cloud provider, container orchestration)
- Explaining security and authentication mechanisms
- Recording breaking changes or major refactoring decisions
- Documenting third-party service selections
- Tracking deprecated decisions and their replacements

### Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| `workspaceId` | `string` | Yes | - | Min: 1 char | The workspace ID |
| `id` | `string` | Yes | - | Pattern: `^\d+$` | ADR number/ID (e.g., "001", "042"). Must be numeric and unique within workspace |
| `date` | `string` | Yes | - | Pattern: `^\d{4}-\d{2}-\d{2}$` | Decision date in YYYY-MM-DD format |
| `title` | `string` | Yes | - | Min: 1 char<br>Max: 200 chars | ADR title describing the decision (e.g., "Use PostgreSQL for primary database") |
| `status` | `string` | Yes | - | Enum: `Proposed`, `Accepted`, `Rejected`, `Deprecated`, `Superseded` | Current status of the ADR |
| `content` | `string` | Yes | - | Min: 1 char | ADR content in Markdown, including context, decision, and consequences |

### ADR Status Values

| Status | Meaning | When to Use |
|--------|---------|-------------|
| `Proposed` | Decision is suggested but not yet approved | Initial ADR creation, pending team review |
| `Accepted` | Decision is approved and should be followed | After team consensus, active decisions |
| `Rejected` | Decision was considered but not approved | Documenting decisions that were evaluated but declined |
| `Deprecated` | Decision is outdated but may still be in use | Old decisions being phased out |
| `Superseded` | Decision has been replaced by a newer ADR | When a new ADR explicitly replaces this one |

### Return Value

```json
{
  "success": true,
  "workspaceId": "string",
  "adr": {
    "id": "string",
    "date": "string",
    "title": "string",
    "status": "string",
    "format": "Markdown",
    "createdAt": "string (ISO 8601)"
  },
  "message": "string"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `success` | `boolean` | Always `true` for successful operations |
| `workspaceId` | `string` | The workspace ID |
| `adr.id` | `string` | The ADR number/ID provided |
| `adr.date` | `string` | The decision date |
| `adr.title` | `string` | The ADR title |
| `adr.status` | `string` | The ADR status |
| `adr.format` | `string` | Always "Markdown" |
| `adr.createdAt` | `string` | ISO 8601 timestamp of ADR creation |
| `message` | `string` | Confirmation message |

### ADR Format and Structure

ADRs typically follow this structure:

```markdown
# [Title - Short description of the decision]

## Status
[Proposed | Accepted | Rejected | Deprecated | Superseded]

## Context
What is the issue we're facing? What factors are influencing this decision?
Describe the problem, constraints, and requirements.

## Decision
What decision did we make? Be specific and concrete.

## Consequences
What are the results of this decision?
- **Positive consequences** - Benefits and advantages
- **Negative consequences** - Tradeoffs and downsides
- **Risks** - Potential issues to watch for

## Alternatives Considered
What other options did we evaluate? Why did we reject them?
```

### Usage Example

**Record a database decision:**
```
Add ADR 001 to workspace "ecommerce-platform-abc123" with:
- Date: 2025-11-16
- Title: "Use PostgreSQL as primary database"
- Status: Accepted
- Content: [Markdown content with context, decision, and consequences]
```

**Document an architectural pattern:**
```
Create ADR 005 in workspace "microservices" about adopting event-driven architecture with status "Proposed" for team review
```

**Supersede an old decision:**
```
Add ADR 012 to workspace "api-platform" documenting the switch from REST to GraphQL, and mark the old REST ADR as "Superseded"
```

### Response Example

```json
{
  "success": true,
  "workspaceId": "ecommerce-platform-abc123",
  "adr": {
    "id": "001",
    "date": "2025-11-16",
    "title": "Use PostgreSQL as primary database",
    "status": "Accepted",
    "format": "Markdown",
    "createdAt": "2025-11-16T14:45:30.987654+00:00"
  },
  "message": "ADR 001 'Use PostgreSQL as primary database' added successfully with status 'Accepted'"
}
```

### Common Use Cases

**Database Selection**
```
Add ADR 001 to workspace "platform" with date "2025-11-16", title "Use PostgreSQL as primary database", status "Accepted", and content:

# Use PostgreSQL as primary database

## Status
Accepted

## Context
We need to select a relational database for our application that will:
- Handle complex queries with multiple joins
- Support ACID transactions
- Scale to millions of records
- Provide good performance for both reads and writes
- Have strong community support and tooling

We are building an e-commerce platform with complex product catalogs, order processing, and user management requirements.

## Decision
We will use PostgreSQL 15+ as our primary relational database.

Key factors in this decision:
- **JSONB support** - Flexible schema for product attributes
- **Full-text search** - Built-in search capabilities
- **Performance** - Excellent query optimization
- **Extensions** - PostGIS for location data, pg_trgm for fuzzy matching
- **Open source** - No licensing costs
- **Community** - Large ecosystem and strong support

## Consequences

### Positive
- Rich feature set reduces need for additional services
- JSONB allows flexible product schema while maintaining relational integrity
- Full-text search eliminates need for separate search infrastructure initially
- Strong ACID guarantees for financial transactions
- Excellent ORM support (Eloquent, Doctrine, TypeORM)

### Negative
- Horizontal scaling more complex than NoSQL solutions
- Requires careful index management for performance
- JSONB queries less performant than native columns
- Team needs PostgreSQL-specific knowledge (vs MySQL familiarity)

### Risks
- May need to add read replicas as traffic grows
- Very large tables (>100M rows) may require partitioning
- JSON queries need careful optimization to avoid full scans

## Alternatives Considered

### MySQL
- More familiar to team
- Simpler replication
- Rejected: Less feature-rich, weaker JSON support

### MongoDB
- Better horizontal scaling
- Schema flexibility
- Rejected: ACID guarantees less robust, complex transactions difficult

### Amazon Aurora
- Managed service, automatic scaling
- PostgreSQL compatible
- Rejected: Cloud vendor lock-in, higher costs
```

**Microservices Communication**
```
Add ADR 003 to workspace "distributed-system" with:

# Use event-driven architecture for service communication

## Status
Accepted

## Context
Our microservices need to communicate and stay synchronized. We have 12 services that need to:
- React to state changes in other services
- Maintain eventual consistency
- Avoid tight coupling between services
- Support service autonomy
- Handle high throughput (10K+ events/second)

Direct synchronous REST calls between services have led to:
- Cascading failures when services are down
- Tight coupling making deployments risky
- Difficulty adding new services without modifying existing ones

## Decision
We will use an event-driven architecture with Apache Kafka as the event streaming platform.

Services will:
- Publish domain events to Kafka topics when state changes
- Subscribe to topics for events they care about
- Process events asynchronously
- Maintain their own data stores (no shared databases)

## Consequences

### Positive
- **Loose coupling** - Services don't need to know about consumers
- **Resilience** - Services can be down and catch up when back online
- **Scalability** - Easy to add new event consumers
- **Audit trail** - Kafka retains event history
- **Replay capability** - Can rebuild state by replaying events

### Negative
- **Complexity** - More moving parts, harder to debug
- **Eventual consistency** - Can't guarantee immediate consistency
- **Event schema management** - Need to version events carefully
- **Operational overhead** - Kafka cluster to manage
- **Learning curve** - Team needs to understand event-driven patterns

### Risks
- Event ordering challenges across partitions
- Need to handle duplicate events (idempotency)
- Schema evolution requires careful planning
- Kafka outage impacts entire system

## Alternatives Considered

### REST API calls
- Simpler, more familiar
- Rejected: Creates tight coupling, cascade failures

### RabbitMQ
- Simpler than Kafka
- Rejected: Lower throughput, no event replay, harder to scale

### AWS EventBridge
- Fully managed
- Rejected: Vendor lock-in, limited throughput, higher latency
```

**Authentication Decision**
```
Create ADR 007 documenting OAuth 2.0 + JWT authentication:

# Implement OAuth 2.0 with JWT tokens for authentication

## Status
Accepted

## Context
We need a secure, scalable authentication mechanism for:
- Web application (SPA)
- Mobile apps (iOS, Android)
- Third-party API consumers
- Internal microservices

Requirements:
- Stateless authentication (no server-side sessions)
- Support for multiple client types
- Token-based with expiration
- Ability to revoke access
- Standard protocol for easier integration

## Decision
Implement OAuth 2.0 with JWT (JSON Web Tokens) for authentication and authorization.

Implementation details:
- **Authorization Server** - Dedicated service issuing tokens
- **Access Tokens** - JWT tokens, 15-minute expiration
- **Refresh Tokens** - Opaque tokens, 7-day expiration
- **Grant Types** - Authorization Code + PKCE for web/mobile, Client Credentials for service-to-service
- **Token Storage** - HttpOnly cookies for web, secure storage for mobile

## Consequences

### Positive
- **Stateless** - No server-side session storage needed
- **Scalable** - Tokens can be validated without database lookup
- **Standard** - OAuth 2.0 widely adopted, good library support
- **Flexible** - Supports multiple client types
- **Secure** - PKCE extension prevents authorization code interception
- **Decoupled** - Services can validate tokens independently

### Negative
- **Complexity** - OAuth 2.0 flow more complex than simple sessions
- **Token size** - JWTs larger than session IDs
- **Revocation** - Can't immediately revoke valid JWTs (until expiration)
- **Key management** - Need to securely manage signing keys
- **Learning curve** - Team needs OAuth 2.0 expertise

### Risks
- Compromised signing key exposes all tokens
- Short expiration requires frequent refreshes
- Need robust refresh token rotation strategy
- Client-side token storage vulnerabilities

## Alternatives Considered

### Session-based authentication
- Simpler to implement
- Rejected: Requires server-side storage, doesn't scale horizontally easily

### API keys
- Very simple
- Rejected: No expiration, hard to revoke, no user context

### SAML
- Enterprise standard
- Rejected: XML-based (verbose), primarily for SSO not API auth
```

**Technology Stack Decision**
```
Add ADR 002 about choosing React for frontend:

# Use React for frontend development

## Status
Accepted

## Context
We need to select a frontend framework for our web application that:
- Supports building complex, interactive UIs
- Has strong community and ecosystem
- Enables component reusability
- Provides good developer experience
- Supports mobile app development (future requirement)

Our team:
- 3 frontend developers with JavaScript experience
- 1 developer with React experience
- 2 developers new to modern frontend frameworks

## Decision
We will use React 18+ as our frontend framework, with TypeScript for type safety.

Supporting tools:
- **Vite** - Build tool and dev server
- **React Router** - Client-side routing
- **TanStack Query** - Server state management
- **Zustand** - Client state management
- **Tailwind CSS** - Utility-first CSS framework

## Consequences

### Positive
- **Large ecosystem** - Extensive library support
- **Component model** - Reusable UI components
- **Declarative** - Easier to reason about UI state
- **React Native** - Can reuse knowledge for mobile apps
- **TypeScript** - Type safety catches errors early
- **Developer tools** - Excellent debugging experience
- **Hiring** - Large talent pool

### Negative
- **Learning curve** - Hooks and lifecycle need understanding
- **Rapid changes** - React ecosystem evolves quickly
- **Bundle size** - Need to optimize for performance
- **SEO challenges** - Client-side rendering requires SSR/SSG for SEO
- **State management** - Multiple options can be confusing

### Risks
- Team needs training on React best practices
- Need to establish coding standards early
- Performance optimization required for complex UIs
- Accessibility requires careful attention

## Alternatives Considered

### Vue.js
- Gentler learning curve
- Good documentation
- Rejected: Smaller ecosystem, fewer senior developers available

### Angular
- Full framework with opinions
- TypeScript by default
- Rejected: Steeper learning curve, more complex for our needs

### Svelte
- Excellent performance
- Simpler syntax
- Rejected: Smaller ecosystem, less mature, harder to hire
```

**Infrastructure Decision**
```
Create ADR 010 about Kubernetes adoption:

# Deploy applications using Kubernetes

## Status
Accepted

## Context
Our microservices architecture has grown to 15 services, and we're facing:
- Complex deployment processes with manual steps
- Inconsistent environments between dev, staging, production
- Difficulty scaling individual services
- Service discovery challenges
- No automated health checks or restarts
- Increasing operational overhead

Current state:
- Docker containers running on EC2 instances
- Manual service discovery via Consul
- Custom deployment scripts
- No automatic scaling

## Decision
We will adopt Kubernetes (AWS EKS) as our container orchestration platform.

Migration plan:
1. Start with non-critical services
2. Create Helm charts for each service
3. Implement CI/CD pipeline for Kubernetes deployments
4. Migrate services incrementally
5. Decommission EC2-based deployment after 6 months

## Consequences

### Positive
- **Automation** - Declarative deployments, auto-healing, auto-scaling
- **Consistency** - Same environment config across all stages
- **Scalability** - Easy horizontal and vertical scaling
- **Service discovery** - Built-in DNS and load balancing
- **Rolling updates** - Zero-downtime deployments
- **Resource optimization** - Better resource utilization
- **Ecosystem** - Rich tooling (Helm, Istio, Prometheus, etc.)

### Negative
- **Complexity** - Steep learning curve
- **Operational overhead** - Cluster management and monitoring
- **Cost** - EKS control plane costs, potentially higher EC2 usage
- **Migration effort** - Significant time to migrate all services
- **Debugging** - More complex troubleshooting
- **YAML** - Extensive YAML configuration required

### Risks
- Team needs Kubernetes expertise (plan 3-month training)
- Initial productivity dip during learning phase
- Potential for misconfigurations leading to outages
- Need to establish best practices and standards
- Backup and disaster recovery more complex

## Alternatives Considered

### AWS ECS
- Simpler than Kubernetes
- AWS-native integration
- Rejected: Less portable, limited ecosystem, vendor lock-in

### Docker Swarm
- Simpler than Kubernetes
- Native Docker orchestration
- Rejected: Smaller community, fewer features, uncertain future

### HashiCorp Nomad
- Simpler than Kubernetes
- Multi-cloud support
- Rejected: Smaller ecosystem, less enterprise adoption
```

**Deprecating a Decision**
```
Update ADR 004 status to "Deprecated" and add ADR 015:

ADR 004 (deprecated):
# Use REST for all API endpoints

## Status
Deprecated (superseded by ADR-015)

## Context
[Original context about choosing REST...]

## Decision
All services will expose RESTful APIs...

## Note
This decision has been superseded by ADR-015 "Migrate to GraphQL for client-facing APIs"
while REST is retained for service-to-service communication.

---

ADR 015 (new):
# Migrate to GraphQL for client-facing APIs

## Status
Accepted

## Context
Our REST APIs have led to:
- Over-fetching (clients get unnecessary data)
- Under-fetching (need multiple requests for one view)
- API versioning complexity
- Mobile apps suffering from poor network performance
- Frontend team blocked waiting for backend API changes

Current situation:
- 47 REST endpoints
- Average mobile app makes 12 API calls to load home screen
- Frequent breaking changes requiring API versioning

## Decision
Migrate client-facing APIs to GraphQL while maintaining REST for service-to-service communication.

Implementation:
- GraphQL server (Apollo Server)
- Federated schema across microservices
- Migrate one client app at a time
- Maintain REST endpoints for 12 months deprecation period

## Consequences

### Positive
- **Efficiency** - Clients request exactly what they need
- **Single request** - Reduce number of round trips
- **Type safety** - Schema provides contract
- **Developer experience** - GraphQL playground for exploration
- **Mobile performance** - Significant bandwidth reduction
- **Frontend autonomy** - Less dependent on backend changes

### Negative
- **Complexity** - Additional layer, learning curve
- **Caching** - HTTP caching doesn't work as well
- **File uploads** - Need special handling
- **Query complexity** - Potential for expensive queries
- **Monitoring** - Harder to monitor than REST
- **Dual maintenance** - Supporting both REST and GraphQL

### Risks
- Need query depth limiting to prevent abuse
- Require query cost analysis for performance
- Team needs GraphQL training
- Schema design requires careful planning
- Need to implement proper rate limiting

## Alternatives Considered

### Stick with REST + BFF pattern
- Backend-for-frontend services
- Rejected: Still multiple requests, harder to maintain

### gRPC
- Better performance
- Rejected: Not browser-friendly, harder client integration

## Supersedes
ADR-004 "Use REST for all API endpoints" (REST retained only for service-to-service)
```

### Tips and Warnings

> **Tip:** Number ADRs sequentially (001, 002, 003...) to make it easy to reference and track them chronologically. Left-pad numbers with zeros for proper sorting.

> **Tip:** Write ADRs when the decision is made, not later. Capturing context while it's fresh ensures you don't forget important details.

> **Tip:** Keep ADRs concise but comprehensive. Include enough context for someone new to understand why the decision was made, but don't write a novel.

> **Tip:** Use the "Alternatives Considered" section to show that you evaluated multiple options. This helps others understand the tradeoffs and builds confidence in the decision.

> **Tip:** Be honest about negative consequences and risks. Every decision has tradeoffs - acknowledging them shows thoughtful decision-making.

> **Note:** ADRs are immutable once accepted. If a decision changes, create a new ADR and mark the old one as "Superseded" rather than editing the original.

> **Best Practice:** Store ADRs with your architecture diagrams in Structurizr. This keeps decisions co-located with the architecture they describe.

> **Best Practice:** When an ADR reaches "Superseded" status, reference the new ADR number in the content. This creates a decision history trail.

> **Best Practice:** Review ADRs periodically (quarterly or bi-annually) to ensure they're still relevant. Mark outdated decisions as "Deprecated."

> **Warning:** ADR IDs must be unique within a workspace. The tool will prevent duplicate IDs, so plan your numbering scheme carefully.

> **Warning:** Use YYYY-MM-DD format for dates exactly as specified. Other formats will be rejected (e.g., "11/16/2025" or "16-Nov-2025" won't work).

> **Pro Tip:** Create an ADR index documentation section that lists all ADRs with their status and titles. This provides a quick reference to all architectural decisions.

> **Pro Tip:** For major architectural changes, create a "Proposed" ADR first, share it with the team for review, then update the status to "Accepted" after consensus.

### ADR Best Practices

1. **Write ADRs for significant decisions only**
   - Technology choices (languages, frameworks, databases)
   - Architectural patterns (microservices, event-driven, etc.)
   - Infrastructure decisions (cloud providers, deployment strategies)
   - Not for: Coding style, minor library choices, implementation details

2. **Use the standard format**
   - Status, Context, Decision, Consequences, Alternatives
   - Keeps ADRs consistent and scannable
   - Makes it easy for team members to write and review

3. **Make decisions reversible in your mind**
   - Acknowledge that decisions can change
   - Use "Superseded" status when replacing decisions
   - Keep historical ADRs for context

4. **Include concrete details**
   - Don't just say "Use microservices" - explain how many, what boundaries, communication patterns
   - Specific details make ADRs actionable

5. **Reference ADRs in code and documentation**
   - Link to ADRs from README files, code comments, and documentation
   - Example: "See ADR-007 for authentication implementation details"

6. **Review ADRs during onboarding**
   - New team members should read all "Accepted" ADRs
   - Helps them understand architectural philosophy and constraints

---

## Navigation

- [← Back to Tools Overview](overview.md)
- [Workspace Management Tools →](workspace-tools.md)
- [Model Building Tools →](model-tools.md)
- [Export & Analysis Tools →](overview.md#export-tools)

---

## Related Resources

- [Workspace Resources](../resources/reference.md#workspace-resources) - Access workspace documentation via MCP resources
- [ADR Best Practices](https://adr.github.io/) - More about Architecture Decision Records
- [Markdown Guide](https://www.markdownguide.org/) - Markdown syntax reference
- [C4 Model Documentation](https://c4model.com/#Documentation) - C4 documentation guidelines
