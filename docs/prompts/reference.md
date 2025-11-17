# Prompts Reference

## Introduction

This reference guide provides complete documentation for all 7 MCP prompts available in the Structurizr MCP server. Each prompt includes usage instructions, parameters, examples, and best practices.

---

## Analysis Prompts

Analysis prompts help you understand and improve existing architecture models by providing structured review frameworks.

### analyze_architecture

Perform comprehensive architecture analysis covering patterns, complexity, dependencies, and risks.

#### What It Does

Analyzes a C4 workspace and provides detailed insights across seven key dimensions:

1. **Architecture Patterns**: Identifies patterns like microservices, layered, event-driven
2. **Complexity Assessment**: Evaluates model complexity and maintainability
3. **Dependencies**: Analyzes relationships and coupling between elements
4. **Completeness**: Checks if all C4 levels are appropriately modeled
5. **Best Practices**: Compares against C4 model best practices
6. **Potential Risks**: Identifies architectural risks or anti-patterns
7. **Improvement Suggestions**: Recommends specific enhancements

#### When to Use It

- Reviewing an existing architecture model
- Getting a comprehensive health check
- Identifying architectural patterns in use
- Finding areas for improvement
- Preparing for architecture reviews
- Onboarding new team members to understand the system

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `workspaceId` | string | Yes | The workspace ID to analyze (1-50 characters) |

#### Example Usage

**Basic Analysis:**

```javascript
// Analyze a workspace
analyze_architecture(workspaceId: "my-ecommerce-system")
```

**Expected conversation flow:**

```
USER: Please analyze the following C4 architecture model:

**Workspace**: E-Commerce Platform
**Description**: Online retail platform
**Elements**: 15
**Views**: 3

I need a comprehensive analysis covering...

[RESOURCE: structurizr://workspace/my-ecommerce-system]
{full workspace JSON}

**Analysis Requirements:**

1. **Architecture Patterns**: Identify architectural patterns...
2. **Complexity Assessment**: Evaluate model complexity...
[etc.]
```

#### Expected Output

The LLM will provide a structured analysis:

```markdown
# Architecture Analysis: E-Commerce Platform

## 1. Architecture Patterns

The system follows a **microservices architecture** with:
- Service decomposition by business capability
- API Gateway pattern for client communication
- Event-driven communication via message queue
- Database per service pattern

## 2. Complexity Assessment

**Overall Complexity**: Medium

- 15 elements across 3 systems
- Clear separation of concerns
- Manageable number of relationships (23)
- Appropriate abstraction levels

**Maintainability**: Good

- Well-documented elements
- Consistent naming
- Clear technology choices

## 3. Dependencies

**External Dependencies**: 3
- Payment Gateway (critical)
- Email Service (important)
- Analytics (optional)

**Internal Coupling**: Low-Medium
- Services communicate via well-defined APIs
- Message queue decouples order processing
- Shared database in Payment service could be improved

## 4. Completeness

✓ System Context view present
✓ Container view present
✗ Component view missing for complex services
✗ Dynamic view would help understand order flow
✓ Element descriptions are comprehensive

## 5. Best Practices

**Following Best Practices:**
- Clear external vs internal system distinction
- Technology specified for all containers
- Relationship descriptions are meaningful

**Areas for Improvement:**
- Add component view for Order Service
- Consider adding dynamic view for critical flows
- Document deployment architecture

## 6. Potential Risks

1. **Single Point of Failure**: Payment Gateway dependency
   - Risk: Payment processing unavailable if gateway is down
   - Mitigation: Consider backup payment provider

2. **Data Consistency**: Distributed data across services
   - Risk: Eventual consistency challenges
   - Mitigation: Document consistency guarantees

3. **Scalability**: Shared database in some services
   - Risk: Database becomes bottleneck
   - Mitigation: Plan for read replicas

## 7. Improvement Suggestions

**High Priority:**
1. Add Circuit Breaker pattern for Payment Gateway
2. Create component view for Order Service
3. Add ADR documenting microservices decision

**Medium Priority:**
4. Consider adding health check endpoints
5. Document retry and timeout policies
6. Add deployment diagram

**Low Priority:**
7. Add system landscape view if multiple systems exist
8. Consider API versioning strategy
9. Document monitoring and observability approach
```

#### Best Practices

**Do:**
- Run this analysis after making significant changes
- Use the output to create ADRs documenting decisions
- Share analysis with the team for review
- Compare analyses over time to track improvements

**Don't:**
- Analyze empty or incomplete workspaces
- Ignore the suggestions - track them as improvements
- Run analysis without understanding the output framework

#### Related Prompts

- `review_security` - Security-focused analysis
- `suggest_improvements` - Specific improvement recommendations

---

### review_security

Review security aspects of the architecture including authentication, authorization, data protection, and security boundaries.

#### What It Does

Performs a security-focused review of your C4 architecture model using a 6-point checklist:

1. **Authentication & Authorization**: How users authenticate, authorization boundaries, RBAC
2. **Data Protection**: Sensitive data storage, encryption, validation
3. **External Interactions**: External system access, credential management, input validation
4. **Security Boundaries**: Trust boundaries, defense in depth, container isolation
5. **Common Vulnerabilities**: OWASP Top 10, injection risks, XSS/CSRF
6. **Recommendations**: Security improvements, missing controls, priority

#### When to Use It

- Performing security reviews
- Preparing for security audits
- Identifying security gaps
- Documenting security architecture
- Onboarding security team members
- Before production deployment

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `workspaceId` | string | Yes | The workspace ID to review (1-50 characters) |

#### Example Usage

```javascript
review_security(workspaceId: "payment-system")
```

#### Expected Output

```markdown
# Security Review: Payment System

## 1. Authentication & Authorization

**Authentication Mechanisms:**
- Users authenticate via OAuth 2.0 (Auth0)
- API authentication using JWT tokens
- Admin users require MFA

**Authorization:**
- Role-based access control (RBAC) implemented
- Roles: User, Admin, Support Agent
- Permissions enforced at API Gateway level

**Findings:**
✓ Strong authentication mechanism
✓ RBAC properly implemented
⚠️ Service-to-service authentication not documented
⚠️ No session timeout policy visible

## 2. Data Protection

**Sensitive Data:**
- Payment card data (PCI DSS scope)
- User PII (email, address)
- Transaction history

**Protection Measures:**
✓ Payment data tokenized (stored in Payment Gateway)
✓ Database encryption at rest
✓ TLS 1.3 for data in transit
⚠️ PII encryption in database not documented
⚠️ No data retention policy visible

**Recommendations:**
- Document PII encryption strategy
- Add data retention and deletion policies
- Consider field-level encryption for PII

## 3. External Interactions

**External Systems:**
1. Payment Gateway (Stripe)
   - API key authentication
   - HTTPS only
   - Webhook signature verification

2. Email Service (SendGrid)
   - API token authentication
   - Rate limiting implemented

**Findings:**
✓ Credential management via environment variables
✓ No credentials in code
⚠️ Webhook endpoint security not fully documented
⚠️ Rate limiting on external calls not visible

**Recommendations:**
- Use secrets management service (AWS Secrets Manager, Vault)
- Document webhook authentication mechanism
- Add circuit breaker for external services

## 4. Security Boundaries

**Trust Boundaries:**
- Internet → API Gateway (DMZ)
- API Gateway → Backend Services (internal network)
- Backend Services → Database (data tier)

**Defense in Depth:**
✓ API Gateway provides first line of defense
✓ Service-level authentication required
✓ Database network isolation
⚠️ No WAF (Web Application Firewall) documented
⚠️ Container isolation strategy not clear

**Recommendations:**
- Add WAF in front of API Gateway
- Document container isolation (network policies, security groups)
- Consider service mesh for mutual TLS

## 5. Common Vulnerabilities

**OWASP Top 10 Assessment:**

1. **Injection (SQL, NoSQL, Command)**
   ✓ Parameterized queries used
   ⚠️ Input validation not documented

2. **Broken Authentication**
   ✓ OAuth 2.0 with JWT
   ⚠️ Token expiration policy not documented

3. **Sensitive Data Exposure**
   ⚠️ Encryption strategy needs documentation
   ⚠️ Logging may expose sensitive data

4. **XML External Entities (XXE)**
   ℹ️ Not applicable (JSON APIs)

5. **Broken Access Control**
   ✓ RBAC implemented
   ⚠️ Object-level authorization not documented

6. **Security Misconfiguration**
   ⚠️ Default configurations not documented
   ⚠️ Security headers not visible

7. **Cross-Site Scripting (XSS)**
   ⚠️ Content Security Policy not documented
   ⚠️ Output encoding strategy not visible

8. **Insecure Deserialization**
   ⚠️ Deserialization controls not documented

9. **Using Components with Known Vulnerabilities**
   ⚠️ Dependency scanning not mentioned
   ⚠️ Update policy not documented

10. **Insufficient Logging & Monitoring**
    ⚠️ Security event logging not documented
    ⚠️ Monitoring and alerting not visible

## 6. Recommendations

### Critical (Implement Immediately)

1. **Add WAF**: Deploy Web Application Firewall
   - Effort: Medium
   - Impact: High
   - Protects against common attacks

2. **Document Authentication**: Complete auth flow documentation
   - Effort: Low
   - Impact: High
   - Essential for security reviews

3. **Secrets Management**: Move to dedicated secrets service
   - Effort: Medium
   - Impact: High
   - Prevents credential exposure

### High Priority

4. **Input Validation**: Document validation strategy
5. **Logging Review**: Ensure no sensitive data in logs
6. **Token Management**: Document token lifecycle
7. **Object-Level Authorization**: Implement and document

### Medium Priority

8. **Security Headers**: Add CSP, HSTS, etc.
9. **Dependency Scanning**: Automate vulnerability scanning
10. **Penetration Testing**: Schedule regular pen tests

### Documentation Needed

11. Create ADR for security architecture decisions
12. Document incident response procedures
13. Create security runbook
14. Document compliance requirements (PCI DSS, GDPR)

## Summary

**Overall Security Posture**: Good with gaps

**Strengths:**
- Strong authentication foundation
- Proper use of external security services
- Network segmentation in place

**Critical Gaps:**
- Missing WAF
- Incomplete documentation
- Secrets management needs improvement

**Next Steps:**
1. Implement critical recommendations
2. Document all security controls
3. Create ADRs for security decisions
4. Schedule follow-up security review
```

#### Best Practices

**Do:**
- Run security reviews regularly (quarterly)
- Document findings as ADRs
- Prioritize critical and high-priority findings
- Involve security team in review
- Re-run review after implementing fixes

**Don't:**
- Rely solely on automated tools
- Ignore documentation gaps
- Skip external system security
- Forget about compliance requirements

#### Related Prompts

- `analyze_architecture` - General architecture analysis
- `suggest_improvements` - Get actionable improvements

---

### suggest_improvements

Get targeted improvement suggestions for scalability, maintainability, performance, or documentation.

#### What It Does

Analyzes your workspace and provides actionable recommendations focused on specific areas or across all dimensions.

#### When to Use It

- Planning refactoring efforts
- Preparing for scale
- Improving code maintainability
- Performance optimization
- Documentation enhancement
- Technical debt reduction

#### Parameters

| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| `workspaceId` | string | Yes | Workspace ID to analyze | 1-50 characters |
| `focusArea` | string | No | Focus area for suggestions (default: 'all') | `scalability`, `maintainability`, `performance`, `documentation`, `all` |

#### Example Usage

**All Areas:**

```javascript
suggest_improvements(workspaceId: "my-system")
```

**Scalability Focus:**

```javascript
suggest_improvements(
    workspaceId: "my-system",
    focusArea: "scalability"
)
```

**Performance Focus:**

```javascript
suggest_improvements(
    workspaceId: "high-traffic-api",
    focusArea: "performance"
)
```

#### Expected Output (Scalability Focus)

```markdown
# Improvement Suggestions: E-Commerce Platform
**Focus**: Scalability

## 1. Horizontal Scaling Opportunities

**Web Application (React SPA)**
- ✓ Already stateless - can scale horizontally
- Add CDN for static assets
- Consider edge computing for personalization

**API Gateway**
- ✓ Stateless design enables scaling
- Current: Single instance
- Recommendation: Deploy multiple instances with load balancer

**Order Service**
- Current: Single instance, stateful sessions
- ⚠️ **Blocker**: Session state stored in memory
- Recommendation:
  1. Move session state to Redis
  2. Enable horizontal scaling
  3. Add load balancer

**Payment Service**
- ✓ Stateless design
- ⚠️ Database connection pool may limit scaling
- Recommendation: Use connection pooler (PgBouncer)

## 2. Scalability Bottlenecks

**Database (PostgreSQL)**
- Current: Single primary instance
- ⚠️ **Critical Bottleneck**: All writes go to one instance
- Impact: Limits to ~10,000 concurrent users
- Recommendations:
  1. **Short-term**: Add read replicas
  2. **Medium-term**: Implement connection pooling
  3. **Long-term**: Consider sharding by tenant/region

**Message Queue (RabbitMQ)**
- Current: Single instance
- ⚠️ **Risk**: Becomes bottleneck at high order volume
- Recommendations:
  1. Deploy cluster mode (3+ nodes)
  2. Partition queues by order type
  3. Monitor queue depth

**File Storage**
- Current: Single S3 bucket
- ✓ S3 auto-scales
- Recommendation: Enable CloudFront CDN

## 3. Caching Strategy

**Current Caching:**
- Product catalog cached in Redis (60 min TTL)
- User sessions in Redis

**Missing Caching Opportunities:**

1. **API Gateway Level**
   - Cache GET responses for product lists
   - Cache user profile data
   - Estimated impact: 30% reduction in backend calls

2. **Database Query Results**
   - Cache expensive aggregation queries
   - Cache search results
   - Estimated impact: 50% reduction in DB load

3. **CDN Edge Caching**
   - Static assets (already planned)
   - API responses for anonymous users
   - Estimated impact: 40% reduction in origin requests

**Recommendations:**
- Implement multi-level caching (CDN → Gateway → App → DB)
- Use cache invalidation strategy (TTL + event-based)
- Monitor cache hit rates

## 4. Load Balancing

**Current:**
- No load balancer configured
- Single instance per service

**Recommendations:**

1. **Application Load Balancer**
   - Route traffic to multiple instances
   - Health checks for automatic failover
   - SSL termination
   - Priority: **High**

2. **Database Load Balancing**
   - Read/write split
   - Route reads to replicas
   - Priority: **High**

3. **Geographic Load Balancing**
   - Multi-region deployment
   - Route users to nearest region
   - Priority: **Low** (future consideration)

## 5. State Management

**Stateful Components** (need attention):

1. **Order Service**
   - Issue: In-memory session state
   - Fix: Redis session store
   - Effort: 1-2 weeks

2. **User uploaded files**
   - Issue: Stored locally during processing
   - Fix: Process directly from/to S3
   - Effort: 1 week

**Stateless Components** ✓:
- Web Application
- API Gateway
- Payment Service
- Notification Service

## 6. Database Scaling Strategies

**Current State:**
- Single PostgreSQL instance
- All services share same database
- No partitioning

**Recommended Strategy:**

**Phase 1: Vertical Scaling** (Immediate)
- Upgrade to larger instance
- Estimated capacity: 3x current load
- Effort: 1 day
- Cost: +$500/month

**Phase 2: Read Replicas** (3 months)
- 2-3 read replicas
- Route analytical queries to replicas
- Estimated capacity: 5x current load
- Effort: 2 weeks
- Cost: +$1500/month

**Phase 3: Database per Service** (6-12 months)
- Separate databases for each service
- True microservices pattern
- Estimated capacity: 10x+ current load
- Effort: 3-4 months
- Cost: +$3000/month

**Phase 4: Sharding** (12+ months)
- Shard by tenant or geography
- Required for massive scale
- Estimated capacity: 100x+ current load
- Effort: 6+ months
- Cost: Significant

## 7. Async Processing

**Current:**
- Order processing is synchronous
- Email sending is synchronous
- Report generation is synchronous

**Opportunities for Async:**

1. **Order Confirmation Emails**
   - Current: Blocks order response
   - Fix: Publish event to queue
   - Impact: 200ms faster response time

2. **Inventory Updates**
   - Current: Synchronous across all operations
   - Fix: Event-driven updates
   - Impact: Better reliability

3. **Report Generation**
   - Current: User waits for report
   - Fix: Async job with notification
   - Impact: Better UX for large reports

4. **Image Processing**
   - Current: Blocks product upload
   - Fix: Background job
   - Impact: Faster uploads

## 8. Resource Limits & Capacity Planning

**Current Limits:**

| Component | Current Limit | At Limit When |
|-----------|---------------|---------------|
| Web Server | 100 concurrent connections | 1,000 users |
| API Gateway | 500 req/sec | 10,000 users |
| Database | 100 connections | 50 backend instances |
| Redis | 10,000 ops/sec | 20,000 users |
| Message Queue | 1,000 msg/sec | 50,000 orders/hour |

**Recommendations:**

1. **Immediate** (2x current capacity):
   - Increase web server connections to 500
   - Scale API Gateway to 3 instances
   - Add database connection pooler

2. **6 Months** (5x current capacity):
   - Implement read replicas
   - Add Redis cluster
   - Scale to 10 instances per service

3. **12 Months** (10x current capacity):
   - Implement full database sharding
   - Multi-region deployment
   - Auto-scaling groups

## Priority Recommendations

### Must Do (Critical)

1. **Add Load Balancer**
   - Impact: Enable horizontal scaling
   - Effort: 1 week
   - Cost: $50/month

2. **Redis for Sessions**
   - Impact: Enable Order Service scaling
   - Effort: 2 weeks
   - Cost: $200/month

3. **Database Connection Pooler**
   - Impact: Support more instances
   - Effort: 1 week
   - Cost: Minimal

### Should Do (High Priority)

4. **Read Replicas**
   - Impact: 3x read capacity
   - Effort: 2 weeks
   - Cost: $1500/month

5. **Multi-level Caching**
   - Impact: 40% load reduction
   - Effort: 3 weeks
   - Cost: $100/month

6. **Async Processing**
   - Impact: Faster responses, better UX
   - Effort: 4 weeks
   - Cost: Minimal

### Could Do (Medium Priority)

7. **Database per Service**
8. **CDN for Static Assets**
9. **Auto-scaling Groups**

### Future Consideration

10. **Multi-region Deployment**
11. **Database Sharding**
12. **Edge Computing**

## Summary

**Current Capacity**: ~5,000 concurrent users
**Target Capacity**: 50,000 concurrent users
**Path**: 3-phase scaling plan over 12 months
**Total Estimated Cost**: $5,000/month additional infrastructure
**Critical Blockers**: 3 (must address immediately)
**Quick Wins**: 2 (implement this quarter)
```

#### Expected Output (All Areas)

When using `focusArea: "all"`, you get top 10 recommendations across all dimensions with effort and impact ratings:

```markdown
# Top 10 Prioritized Recommendations

## 1. Add Load Balancer (Scalability)
**Impact**: High | **Effort**: Low | **Priority**: Critical
Enables horizontal scaling of all services

## 2. Implement Read Replicas (Scalability)
**Impact**: High | **Effort**: Medium | **Priority**: Critical
Increases database read capacity 3-5x

## 3. Move Sessions to Redis (Scalability)
**Impact**: High | **Effort**: Low | **Priority**: High
Enables Order Service horizontal scaling

## 4. Add Component Views (Documentation)
**Impact**: Medium | **Effort**: Low | **Priority**: High
Improves understanding of complex services

## 5. Implement Multi-level Caching (Performance)
**Impact**: High | **Effort**: Medium | **Priority**: High
Reduces backend load by 40%

## 6. Separate Concerns in Order Service (Maintainability)
**Impact**: High | **Effort**: High | **Priority**: Medium
Improves long-term maintainability

## 7. Add Database Indexes (Performance)
**Impact**: High | **Effort**: Low | **Priority**: High
Query performance improvement 10-100x

## 8. Create ADRs for Key Decisions (Documentation)
**Impact**: Medium | **Effort**: Low | **Priority**: Medium
Documents architectural rationale

## 9. Async Email Sending (Performance)
**Impact**: Medium | **Effort**: Low | **Priority**: Medium
200ms faster response times

## 10. Database per Service (Maintainability)
**Impact**: High | **Effort**: Very High | **Priority**: Low
True microservices isolation
```

#### Best Practices

**Do:**
- Start with `focusArea: "all"` to get overview
- Use specific focus areas for deep dives
- Track recommendations as backlog items
- Prioritize by impact vs effort
- Re-run after implementing changes

**Don't:**
- Try to implement everything at once
- Ignore "low effort, high impact" items
- Skip the analysis - implement blindly
- Forget to measure impact of changes

#### Related Prompts

- `analyze_architecture` - Overall architecture health
- `review_security` - Security-specific improvements

---

## Generation Prompts

Generation prompts help you create new architecture models and learn C4 methodology.

### generate_system_context

Generate a C4 system context model from a natural language description of a software system.

#### What It Does

Converts a high-level system description into a structured C4 system context diagram specification with:

- User identification and persona creation
- Main software system definition
- External system dependencies
- Relationships and interactions
- System context view generation

#### When to Use It

- Starting a new architecture model
- Documenting an existing system at high level
- Getting guidance on what elements to create
- Learning how to model system context
- Preparing for stakeholder presentations

#### Parameters

| Parameter | Type | Required | Description | Length |
|-----------|------|----------|-------------|--------|
| `description` | string | Yes | Natural language description of the software system, its purpose, users, and external dependencies | 10-5000 characters |

#### Example Usage

```javascript
generate_system_context(
    description: "A mobile banking application that allows customers to check " +
                "balances, transfer money, pay bills, and deposit checks. " +
                "It integrates with the core banking system, credit bureaus " +
                "for credit scores, and payment networks for bill pay. " +
                "Bank customers and customer service representatives use the system."
)
```

#### Expected Output

The prompt provides structured guidance:

```markdown
Based on the following system description, please generate a C4 System Context diagram:

**System Description:**
A mobile banking application that allows customers to check balances...

**Generation Guidelines:**

1. **Identify Users/Actors**:
   - List all user types or personas
   - Use `add_person` tool for each user
   - Provide clear descriptions of their roles

   **Identified Users:**
   - Bank Customer: Primary user who manages their finances
   - Customer Service Representative: Assists customers with issues

   **Tool Calls:**
   ```javascript
   add_person(
       workspaceId: "mobile-banking",
       name: "Bank Customer",
       description: "A retail banking customer who manages finances via mobile app"
   )

   add_person(
       workspaceId: "mobile-banking",
       name: "Customer Service Rep",
       description: "Bank employee who assists customers with account issues and questions"
   )
   ```

2. **Identify the System**:
   - Extract the main software system name and purpose
   - Use `add_software_system` tool with location='Internal'
   - Write a concise system description

   **Main System:**
   - Mobile Banking App: The mobile application for customer banking

   **Tool Call:**
   ```javascript
   add_software_system(
       workspaceId: "mobile-banking",
       name: "Mobile Banking App",
       description: "Enables customers to manage accounts, transfer money, pay bills, and deposit checks via mobile device",
       location: "Internal"
   )
   ```

3. **Identify External Systems**:
   - List all external dependencies
   - Use `add_software_system` for each with location='External'
   - Describe what each external system provides

   **External Systems:**
   - Core Banking System: Processes transactions and stores account data
   - Credit Bureau: Provides credit scores and reports
   - Payment Network: Processes bill payments

   **Tool Calls:**
   ```javascript
   add_software_system(
       workspaceId: "mobile-banking",
       name: "Core Banking System",
       description: "Legacy mainframe system that processes all banking transactions and maintains account balances",
       location: "External",
       tags: "External System"
   )

   add_software_system(
       workspaceId: "mobile-banking",
       name: "Credit Bureau",
       description: "Third-party service providing credit scores and credit reports",
       location: "External",
       tags: "External System,Third Party"
   )

   add_software_system(
       workspaceId: "mobile-banking",
       name: "Payment Network",
       description: "Processes bill payments to merchants and service providers",
       location: "External",
       tags: "External System"
   )
   ```

4. **Define Relationships**:
   - Map interactions between users and the system
   - Map dependencies between the system and external systems
   - Use `add_relationship` with descriptive verbs
   - Include technology where relevant

   **Relationships:**
   ```javascript
   // User relationships
   add_relationship(
       sourceId: "bank-customer-id",
       destinationId: "mobile-banking-app-id",
       description: "Checks balances, transfers money, pays bills, deposits checks using",
       technology: "HTTPS/Mobile App"
   )

   add_relationship(
       sourceId: "customer-service-rep-id",
       destinationId: "mobile-banking-app-id",
       description: "Assists customers and resolves issues using",
       technology: "Web Portal"
   )

   // System relationships
   add_relationship(
       sourceId: "mobile-banking-app-id",
       destinationId: "core-banking-system-id",
       description: "Retrieves account information and processes transactions using",
       technology: "IBM MQ/SOAP"
   )

   add_relationship(
       sourceId: "mobile-banking-app-id",
       destinationId: "credit-bureau-id",
       description: "Fetches credit scores and reports from",
       technology: "REST API/HTTPS"
   )

   add_relationship(
       sourceId: "mobile-banking-app-id",
       destinationId: "payment-network-id",
       description: "Submits bill payments to",
       technology: "REST API/HTTPS"
   )
   ```

5. **Create System Context View**:
   - Use `create_system_context_view` with a meaningful key
   - Apply `apply_auto_layout` with appropriate direction

   **View Creation:**
   ```javascript
   create_system_context_view(
       systemId: "mobile-banking-app-id",
       key: "SystemContext",
       description: "System context diagram for Mobile Banking Application"
   )

   apply_auto_layout(
       viewKey: "SystemContext",
       direction: "lr"  // left-to-right layout
   )
   ```

## Complete Tool Sequence

Here's the complete sequence of MCP tool calls to create this system context:

1. Create workspace
2. Add 2 persons (Bank Customer, CSR)
3. Add 1 internal system (Mobile Banking App)
4. Add 3 external systems (Core Banking, Credit Bureau, Payment Network)
5. Add 5 relationships
6. Create system context view
7. Apply auto-layout

**Rationale for Architecture Decisions:**

- **Mobile App as Internal System**: This is the system being built/documented
- **Core Banking as External**: Legacy system, treated as external dependency
- **Credit Bureau Integration**: Provides value-add feature (credit scores)
- **Payment Network**: Enables bill pay functionality
- **Left-to-Right Layout**: Shows flow from users → app → external systems
```

#### Best Practices

**Do:**
- Provide detailed descriptions (100-500 words ideal)
- Mention all users and external systems
- Describe the system's purpose clearly
- Include integration points
- Specify technologies if known

**Don't:**
- Be too vague ("a web app")
- Forget to mention users
- Skip external dependencies
- Mix different abstraction levels

**Description Template:**

```
[System Name] is a [type of system] that [primary purpose].

Users:
- [User Type 1]: [what they do]
- [User Type 2]: [what they do]

It integrates with:
- [External System 1]: [purpose]
- [External System 2]: [purpose]

Key capabilities:
- [Capability 1]
- [Capability 2]
```

#### Related Prompts

- `create_from_description` - Create full multi-level model
- `explain_c4_model` - Learn C4 methodology first

---

### create_from_description

Create a complete multi-level C4 model (Context, Container, Component) from a detailed architecture description.

#### What It Does

Generates a comprehensive C4 model with all appropriate levels:

- **Level 1 (Context)**: Users, systems, external dependencies
- **Level 2 (Container)**: Applications, databases, services
- **Level 3 (Component)**: Internal structure of complex containers
- **Documentation**: Sections and ADRs
- **Export**: DSL format for version control

#### When to Use It

- Creating a complete architecture model from scratch
- Documenting an existing system comprehensively
- Learning how to build multi-level C4 models
- Migrating documentation from other formats
- Preparing detailed architecture documentation

#### Parameters

| Parameter | Type | Required | Description | Length |
|-----------|------|----------|-------------|--------|
| `architectureDescription` | string | Yes | Comprehensive architecture description including system purpose, components, technologies, and interactions | 50-10000 characters |

#### Example Usage

```javascript
create_from_description(
    architectureDescription:
    "We're building TaskMaster, a cloud-based project management SaaS platform.

    **Users:**
    - Project Managers: Create projects, assign tasks, track progress
    - Team Members: Complete tasks, update status, log time
    - Executives: View dashboards, generate reports

    **Architecture:**
    The system follows a modern microservices architecture:

    **Frontend:**
    - React SPA hosted on CloudFront CDN
    - Mobile apps (iOS/Android) built with React Native

    **Backend Services:**
    - API Gateway (Node.js/Express): Authentication, routing, rate limiting
    - Project Service (Java/Spring Boot): Project and task management
    - User Service (Node.js): User profiles and authentication
    - Notification Service (Python): Email and push notifications
    - Analytics Service (Python): Usage analytics and reporting

    **Data Storage:**
    - PostgreSQL: Primary database for projects and users
    - MongoDB: Document storage for comments and attachments
    - Redis: Caching and session management
    - S3: File attachments storage

    **Message Queue:**
    - RabbitMQ: Async task processing and notifications

    **External Systems:**
    - Auth0: OAuth authentication
    - SendGrid: Email delivery
    - Twilio: SMS notifications
    - Stripe: Subscription billing

    **Key Interactions:**
    - Users access via web or mobile apps
    - All requests go through API Gateway
    - Services communicate via REST APIs
    - Async notifications via RabbitMQ
    - File uploads go directly to S3

    **Component Detail (Project Service):**
    The Project Service is the core component with:
    - Project Controller: REST endpoints
    - Task Controller: Task management endpoints
    - Project Service Layer: Business logic
    - Task Service Layer: Task operations
    - Authorization Service: Permission checks
    - Project Repository: Database access
    - Event Publisher: Publishes events to RabbitMQ"
)
```

#### Expected Output

The prompt provides a complete 6-phase implementation plan:

```markdown
Create a complete C4 model from this architecture description:
[Your description...]

**Step-by-Step Generation Process:**

**Phase 1: Workspace Setup**

1. Create workspace
```javascript
create_workspace(
    name: "TaskMaster",
    description: "Cloud-based project management SaaS platform"
)
```

**Phase 2: System Context (Level 1)**

2-4. Add all persons
```javascript
add_person(workspaceId, name: "Project Manager", ...)
add_person(workspaceId, name: "Team Member", ...)
add_person(workspaceId, name: "Executive", ...)
```

5. Add main system
```javascript
add_software_system(
    workspaceId,
    name: "TaskMaster",
    description: "Cloud-based project management platform",
    location: "Internal"
)
```

6-9. Add external systems
```javascript
add_software_system(workspaceId, name: "Auth0", location: "External", ...)
add_software_system(workspaceId, name: "SendGrid", location: "External", ...)
add_software_system(workspaceId, name: "Twilio", location: "External", ...)
add_software_system(workspaceId, name: "Stripe", location: "External", ...)
```

10-15. Add relationships
```javascript
add_relationship(
    sourceId: "project-manager-id",
    destinationId: "taskmaster-id",
    description: "Creates projects, assigns tasks using"
)
// ... more relationships
```

16. Create system context view
```javascript
create_system_context_view(
    systemId: "taskmaster-id",
    key: "SystemContext"
)
apply_auto_layout(viewKey: "SystemContext", direction: "tb")
```

**Phase 3: Container (Level 2)**

17-26. Add containers
```javascript
add_container(
    systemId: "taskmaster-id",
    name: "Web Application",
    description: "React-based single page application",
    technology: "React, Redux, TypeScript"
)

add_container(
    systemId: "taskmaster-id",
    name: "Mobile App",
    description: "Native mobile application for iOS and Android",
    technology: "React Native"
)

add_container(
    systemId: "taskmaster-id",
    name: "API Gateway",
    description: "Routes requests, handles authentication, rate limiting",
    technology: "Node.js, Express, JWT"
)

add_container(
    systemId: "taskmaster-id",
    name: "Project Service",
    description: "Manages projects, tasks, and assignments",
    technology: "Java 17, Spring Boot, JPA"
)

add_container(
    systemId: "taskmaster-id",
    name: "User Service",
    description: "User profile management and authentication",
    technology: "Node.js, Express"
)

add_container(
    systemId: "taskmaster-id",
    name: "Notification Service",
    description: "Sends email and push notifications",
    technology: "Python, Celery"
)

add_container(
    systemId: "taskmaster-id",
    name: "Analytics Service",
    description: "Usage analytics and reporting",
    technology: "Python, Pandas"
)

add_container(
    systemId: "taskmaster-id",
    name: "PostgreSQL Database",
    description: "Stores projects, tasks, and user data",
    technology: "PostgreSQL 14"
)

add_container(
    systemId: "taskmaster-id",
    name: "MongoDB",
    description: "Stores comments and file metadata",
    technology: "MongoDB 5.0"
)

add_container(
    systemId: "taskmaster-id",
    name: "Redis Cache",
    description: "Session cache and query results cache",
    technology: "Redis 7.0"
)

add_container(
    systemId: "taskmaster-id",
    name: "Message Queue",
    description: "Async task processing",
    technology: "RabbitMQ"
)

add_container(
    systemId: "taskmaster-id",
    name: "File Storage",
    description: "File attachment storage",
    technology: "AWS S3"
)
```

27-40. Add container relationships
```javascript
add_relationship(
    sourceId: "web-app-id",
    destinationId: "api-gateway-id",
    description: "Makes API calls to",
    technology: "HTTPS/REST"
)
// ... more relationships
```

41. Create container view
```javascript
create_container_view(
    systemId: "taskmaster-id",
    key: "Containers"
)
apply_auto_layout(viewKey: "Containers", direction: "tb")
```

**Phase 4: Component (Level 3)**

For Project Service (most complex container):

42-48. Add components
```javascript
add_component(
    containerId: "project-service-id",
    name: "Project Controller",
    description: "REST API endpoints for project management",
    technology: "Spring MVC, REST"
)

add_component(
    containerId: "project-service-id",
    name: "Task Controller",
    description: "REST API endpoints for task management",
    technology: "Spring MVC, REST"
)

add_component(
    containerId: "project-service-id",
    name: "Project Service",
    description: "Business logic for project operations",
    technology: "Spring Service"
)

add_component(
    containerId: "project-service-id",
    name: "Task Service",
    description: "Business logic for task operations",
    technology: "Spring Service"
)

add_component(
    containerId: "project-service-id",
    name: "Authorization Service",
    description: "Permission checks and access control",
    technology: "Spring Security"
)

add_component(
    containerId: "project-service-id",
    name: "Project Repository",
    description: "Data access layer for projects and tasks",
    technology: "Spring Data JPA"
)

add_component(
    containerId: "project-service-id",
    name: "Event Publisher",
    description: "Publishes domain events to message queue",
    technology: "Spring AMQP"
)
```

49-55. Add component relationships
```javascript
add_relationship(
    sourceId: "project-controller-id",
    destinationId: "project-service-id",
    description: "Uses"
)
// ... more relationships
```

56. Create component view
```javascript
create_component_view(
    containerId: "project-service-id",
    key: "ProjectServiceComponents"
)
apply_auto_layout(viewKey: "ProjectServiceComponents", direction: "tb")
```

**Phase 5: Documentation**

57. Add documentation section
```javascript
add_documentation_section(
    workspaceId,
    title: "Architecture Overview",
    content: "TaskMaster follows a microservices architecture pattern...

## Key Architectural Decisions

1. **Microservices Architecture**: Each service has a single responsibility
2. **API Gateway Pattern**: Centralized authentication and routing
3. **Event-Driven Communication**: Async processing via RabbitMQ
4. **Polyglot Persistence**: Right database for each use case
5. **Cloud-Native**: Designed for AWS deployment

## Technology Stack

- **Frontend**: React, React Native
- **Backend**: Java (Spring Boot), Node.js, Python
- **Databases**: PostgreSQL, MongoDB, Redis
- **Messaging**: RabbitMQ
- **Auth**: Auth0 (OAuth 2.0)
- **Infrastructure**: AWS (CloudFront, S3, EC2, RDS)"
)
```

58. Add ADR
```javascript
add_adr(
    workspaceId,
    id: "001",
    date: "2024-01-15",
    title: "Use Microservices Architecture",
    status: "Accepted",
    content: "## Context

We need to build a scalable SaaS platform that can serve thousands of tenants.

## Decision

We will use a microservices architecture with domain-driven service boundaries.

## Consequences

**Positive:**
- Independent scaling of services
- Technology flexibility per service
- Team autonomy
- Fault isolation

**Negative:**
- Increased operational complexity
- Distributed system challenges
- Need for sophisticated monitoring

## Alternatives Considered

- Modular monolith: Simpler but limits scaling
- Serverless: Cost-effective but vendor lock-in"
)
```

**Phase 6: Styling and Export**

59. Export to DSL
```javascript
export_to_dsl(workspaceId)
```

**Rationale for Architecture Decisions:**

1. **Microservices**: Enables independent scaling and development
2. **API Gateway**: Centralized security and routing
3. **RabbitMQ**: Reliable async processing
4. **Polyglot Persistence**: PostgreSQL for transactional, MongoDB for flexible schema
5. **Auth0**: Production-ready authentication without building from scratch
6. **S3**: Scalable, cost-effective file storage
```

#### Best Practices

**Writing Good Descriptions:**

```
Structure your description with:

1. **System Overview** (2-3 sentences)
   - What is it?
   - What problem does it solve?
   - Who uses it?

2. **Users** (list all user types)
   - Role name: What they do

3. **Architecture Pattern** (microservices, monolith, etc.)

4. **Containers** (deployable units)
   - Name (Technology): Purpose

5. **Data Storage**
   - Database type: What it stores

6. **External Systems**
   - Name: Integration purpose

7. **Key Interactions**
   - How components communicate

8. **Component Detail** (for 1-2 complex containers)
   - List internal components
```

**Do:**
- Include technology choices
- Specify communication patterns
- Mention data storage strategy
- Describe external integrations
- Detail 1-2 complex containers

**Don't:**
- Be too vague about technologies
- Skip communication patterns
- Forget data storage
- Mix abstraction levels
- Include implementation details (code, configs)

#### Related Prompts

- `generate_system_context` - Start with just context level
- `create_example_workspace` - See examples first

---

### explain_c4_model

Get a comprehensive explanation of the C4 model methodology, its levels, and best practices.

#### What It Does

Provides educational content about the C4 model including:

- The four C's (Context, Container, Component, Code)
- When to use each level
- Benefits and best practices
- Common questions and answers
- How to use C4 with Structurizr MCP

#### When to Use It

- Learning C4 model for the first time
- Teaching C4 to team members
- Refreshing knowledge before creating models
- Understanding when to use each level
- Deciding architecture documentation approach

#### Parameters

None - this prompt requires no parameters.

#### Example Usage

```javascript
explain_c4_model()
```

#### Expected Output

A comprehensive guide covering:

1. **The Four C's**: Detailed explanation of each level
2. **Additional Diagram Types**: System Landscape, Dynamic, Deployment
3. **Core Principles**: Abstraction, audience-driven, notation independence
4. **Benefits**: Clear hierarchy, easy to understand, scalable
5. **Best Practices**: 10 key practices
6. **Using C4 with Structurizr MCP**: Workflow examples
7. **Common Questions**: FAQ section
8. **Resources**: Links and next steps

(See full output in the implementation - it's quite extensive!)

#### Best Practices

**Do:**
- Read this before creating your first model
- Share with team members
- Reference when unsure about abstraction level
- Use as training material

**Don't:**
- Skip reading if you're new to C4
- Try to memorize everything - refer back as needed

#### Related Prompts

- `create_example_workspace` - See C4 in action
- `generate_system_context` - Start creating your model

---

### create_example_workspace

Generate a complete example C4 workspace based on common architecture patterns.

#### What It Does

Creates a realistic, production-like example workspace demonstrating:

- C4 modeling best practices
- Complete context, container, and component views
- Proper documentation and ADRs
- Technology choices and patterns
- Real-world architecture patterns

#### When to Use It

- Learning by example
- Studying architecture patterns
- Getting inspiration for your own model
- Understanding best practices
- Training new team members
- Comparing different architectural approaches

#### Parameters

| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| `type` | string | Yes | Type of example architecture to generate | `ecommerce`, `microservices`, `monolith`, `saas` |

#### Example Usage

**E-Commerce Example:**

```javascript
create_example_workspace(type: "ecommerce")
```

**Microservices Example:**

```javascript
create_example_workspace(type: "microservices")
```

**Monolith Example:**

```javascript
create_example_workspace(type: "monolith")
```

**SaaS Example:**

```javascript
create_example_workspace(type: "saas")
```

#### Example Types

##### ecommerce

Modern e-commerce platform with:

- **Users**: Customer, Admin, Support Agent
- **Containers**: React SPA, Admin Portal, API Gateway, Product/Order/Payment Services
- **Database**: PostgreSQL, Redis, RabbitMQ
- **External**: Stripe, SendGrid, FedEx, Google Analytics
- **Pattern**: Microservices with event-driven order processing

**Best for learning:**
- Service decomposition
- Payment integration patterns
- Event-driven architecture
- Cache strategies

##### microservices

Cloud-native microservices platform with:

- **Users**: End User, Developer, Operator
- **Containers**: API Gateway, multiple services in different languages
- **Pattern**: Database per service, event bus, service discovery
- **External**: Auth0, Datadog, ELK Stack
- **Key Features**: Service mesh, circuit breakers, saga pattern

**Best for learning:**
- Microservices patterns
- Polyglot architecture
- Service communication
- Distributed systems

##### monolith

Modular monolith showing clean architecture:

- **Users**: Regular User, Administrator
- **Containers**: Spring Boot app, Angular SPA, PostgreSQL
- **Components**: Security, Business, Reporting, Integration modules
- **Pattern**: Layered architecture with clear module boundaries

**Best for learning:**
- When monoliths make sense
- Modular design within monolith
- Clean architecture principles
- Migration path to microservices

##### saas

Multi-tenant SaaS platform:

- **Users**: Tenant Admin, Tenant User, Platform Admin
- **Containers**: React SPA, Auth Service, Billing Service, Tenant Service
- **Pattern**: Multi-tenant isolation, subscription management
- **External**: Stripe, Okta, Mixpanel
- **Key Features**: Tenant isolation, usage metering, tier management

**Best for learning:**
- Multi-tenancy patterns
- Subscription billing
- Tenant isolation strategies
- SaaS-specific concerns

#### Expected Output

For each example type, you get:

1. **Complete specification** with all details
2. **Implementation steps** (13 steps)
3. **Quality checklist**
4. **Full tool call sequence**
5. **Exported DSL**

Example output structure:

```markdown
Create a complete example C4 workspace for a microservices architecture pattern.

**Microservices Architecture Example**

**System Overview:**
A cloud-native microservices platform demonstrating service decomposition...

**Users:**
- End User: Consumes services via mobile/web
- Developer: Manages and deploys microservices
- Operator: Monitors system health

**Containers (Microservices):**
- API Gateway (Kong/Nginx): Routing, auth, rate limiting
- User Service (Node.js): User management
- Product Service (Go): Product catalog
[... detailed list ...]

**External Systems:**
- Authentication Provider (Auth0)
- Monitoring (Datadog)
- Logging (ELK Stack)

**Key Patterns:**
- API Gateway pattern
- Database per service
- Event-driven communication
- Circuit breaker
- Service discovery

**Component Example (Order Service):**
- Order Controller
- Order Orchestrator (Saga)
- Inventory Client
- Payment Client
- Event Publisher
- Order Repository

**ADR Example:**
Decision to use Kafka for event streaming...

**Implementation Steps:**

1. Create workspace
2. Add users (End User, Developer, Operator)
3. Add main system and external systems
4. Create system context view
5. Add all containers (API Gateway, services, databases)
6. Create container view
7. Add components to Order Service
8. Create component view
9. Add all relationships
10. Apply auto-layout
11. Add documentation
12. Add ADR
13. Export to DSL

**Quality Checklist:**
✓ All elements have meaningful descriptions
✓ Technologies specified
✓ Relationships are clear
✓ Views properly organized
✓ Documentation explains decisions
✓ Demonstrates best practices

[Then provides the complete MCP tool call sequence]
```

#### Best Practices

**Do:**
- Study the example before creating your own
- Export to DSL and read the code
- Try all four example types
- Use as templates for your systems
- Compare patterns for your use case

**Don't:**
- Blindly copy without understanding
- Use wrong pattern for your context
- Skip reading the ADRs
- Ignore the technology choices rationale

**Learning Path:**

```
1. Run: explain_c4_model()
   → Understand the methodology

2. Run: create_example_workspace(type: "monolith")
   → Start with simpler example

3. Run: export_to_dsl(workspaceId)
   → Study the structure

4. Run: create_example_workspace(type: "microservices")
   → See more complex pattern

5. Compare the two
   → Understand tradeoffs

6. Run: generate_system_context(description: "your system")
   → Create your own
```

#### Related Prompts

- `explain_c4_model` - Learn methodology first
- `create_from_description` - Create your own model
- `analyze_architecture` - Analyze the example

---

## Quick Reference

### By Use Case

| Use Case | Prompt |
|----------|--------|
| Learn C4 methodology | `explain_c4_model` |
| See examples | `create_example_workspace` |
| Start new model | `generate_system_context` |
| Create complete model | `create_from_description` |
| Review architecture | `analyze_architecture` |
| Security review | `review_security` |
| Get improvements | `suggest_improvements` |

### By Skill Level

**Beginner:**
1. `explain_c4_model` - Learn basics
2. `create_example_workspace` - See examples
3. `generate_system_context` - Create simple model

**Intermediate:**
4. `create_from_description` - Create complex models
5. `analyze_architecture` - Understand analysis
6. `suggest_improvements` - Improve models

**Advanced:**
7. `review_security` - Deep security analysis
8. `suggest_improvements` with focus areas - Targeted optimization

### By Workflow

**New Project:**
```
explain_c4_model
→ generate_system_context
→ [use tools to build]
→ analyze_architecture
→ [refine]
```

**Existing System:**
```
create_from_description
→ analyze_architecture
→ review_security
→ suggest_improvements
→ [implement changes]
```

**Learning:**
```
explain_c4_model
→ create_example_workspace (all types)
→ [study outputs]
→ generate_system_context (practice)
```

## See Also

- [Overview](overview.md) - Understanding MCP prompts
- [Tools Reference](../tools/reference.md) - Implementing prompt suggestions
- [Resources](../resources/reference.md) - Data accessed by prompts
- [Examples](../examples/) - Practical usage scenarios
