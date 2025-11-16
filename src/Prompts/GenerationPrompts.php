<?php

declare(strict_types=1);

namespace StructurizrMcp\Prompts;

use Mcp\Capability\Attribute\McpPrompt;
use Mcp\Capability\Attribute\Schema;
use Psr\Log\LoggerInterface;

/**
 * Generation prompts for creating C4 models and documentation
 *
 * These prompts help generate C4 architecture models from descriptions,
 * explain the C4 model concept, and create example workspaces.
 */
class GenerationPrompts
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Generate C4 system context from a natural language description
     *
     * This prompt helps convert a high-level system description into
     * a structured C4 system context diagram specification.
     *
     * @return array{messages: array<int, array{role: string, content: array<int, array{type: string, text?: string}>}>}
     */
    #[McpPrompt(
        name: 'generate_system_context',
        description: 'Generate a C4 system context model from a natural language description of a software system'
    )]
    public function generateSystemContext(
        #[Schema(
            description: 'Natural language description of the software system, its purpose, users, and external dependencies',
            minLength: 10,
            maxLength: 5000
        )]
        string $description
    ): array {
        $this->logger->info('Generating system context prompt', [
            'descriptionLength' => strlen($description)
        ]);

        return [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Based on the following system description, please generate a C4 System Context diagram:\n\n" .
                                "**System Description:**\n{$description}\n\n" .
                                "**Instructions:**\n" .
                                "Create a complete C4 system context model using the Structurizr MCP tools."
                        ]
                    ]
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "**Generation Guidelines:**\n\n" .
                                "1. **Identify Users/Actors**:\n" .
                                "   - List all user types or personas\n" .
                                "   - Use `add_person` tool for each user\n" .
                                "   - Provide clear descriptions of their roles\n\n" .
                                "2. **Identify the System**:\n" .
                                "   - Extract the main software system name and purpose\n" .
                                "   - Use `add_software_system` tool with location='Internal'\n" .
                                "   - Write a concise system description\n\n" .
                                "3. **Identify External Systems**:\n" .
                                "   - List all external dependencies (databases, APIs, services)\n" .
                                "   - Use `add_software_system` for each with location='External'\n" .
                                "   - Describe what each external system provides\n\n" .
                                "4. **Define Relationships**:\n" .
                                "   - Map interactions between users and the system\n" .
                                "   - Map dependencies between the system and external systems\n" .
                                "   - Use `add_relationship` with descriptive verbs (uses, sends data to, authenticates with, etc.)\n" .
                                "   - Include technology where relevant (REST API, HTTPS, etc.)\n\n" .
                                "5. **Create System Context View**:\n" .
                                "   - Use `create_system_context_view` with a meaningful key\n" .
                                "   - Apply `apply_auto_layout` with appropriate direction (lr or tb)\n\n" .
                                "**Output Format:**\n" .
                                "Generate the exact sequence of MCP tool calls needed to create this model. " .
                                "For each tool call, explain what element is being created and why."
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Create a complete C4 model from an architecture description
     *
     * This prompt generates a multi-level C4 model (Context, Container, Component)
     * from a comprehensive architecture description.
     *
     * @return array{messages: array<int, array{role: string, content: array<int, array{type: string, text?: string}>}>}
     */
    #[McpPrompt(
        name: 'create_from_description',
        description: 'Create a complete multi-level C4 model (Context, Container, Component) from a detailed architecture description'
    )]
    public function createFromDescription(
        #[Schema(
            description: 'Comprehensive architecture description including system purpose, components, technologies, and interactions',
            minLength: 50,
            maxLength: 10000
        )]
        string $architectureDescription
    ): array {
        $this->logger->info('Generating full C4 model prompt', [
            'descriptionLength' => strlen($architectureDescription)
        ]);

        return [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Create a complete C4 model from this architecture description:\n\n" .
                                "**Architecture Description:**\n{$architectureDescription}\n\n" .
                                "Please generate a comprehensive C4 model with all appropriate levels (Context, Container, and Component where needed)."
                        ]
                    ]
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "**Step-by-Step Generation Process:**\n\n" .
                                "**Phase 1: Workspace Setup**\n" .
                                "1. Create workspace with `create_workspace`\n" .
                                "   - Use a descriptive name\n" .
                                "   - Include a clear description\n\n" .
                                "**Phase 2: System Context (Level 1)**\n" .
                                "2. Add all persons/users with `add_person`\n" .
                                "3. Add the main software system with `add_software_system` (Internal)\n" .
                                "4. Add all external systems with `add_software_system` (External)\n" .
                                "5. Add relationships between users and systems\n" .
                                "6. Create system context view with `create_system_context_view`\n\n" .
                                "**Phase 3: Container (Level 2)**\n" .
                                "7. Identify main deployable units (web app, mobile app, API, database, etc.)\n" .
                                "8. Add containers to the system using `add_container`\n" .
                                "   - Specify technology for each (e.g., 'React SPA', 'Spring Boot API', 'PostgreSQL')\n" .
                                "   - Add appropriate tags\n" .
                                "9. Add relationships between containers\n" .
                                "10. Create container view with `create_container_view`\n\n" .
                                "**Phase 4: Component (Level 3)** - For complex containers\n" .
                                "11. For key containers, identify logical components\n" .
                                "12. Add components using `add_component`\n" .
                                "    - Group related functionality\n" .
                                "    - Specify implementation technology\n" .
                                "13. Add component relationships\n" .
                                "14. Create component view with `create_component_view`\n\n" .
                                "**Phase 5: Documentation (Optional)**\n" .
                                "15. Add documentation sections with `add_documentation_section`\n" .
                                "16. Add ADRs for major decisions with `add_adr`\n\n" .
                                "**Phase 6: Styling and Export**\n" .
                                "17. Apply auto-layout to all views\n" .
                                "18. Export to DSL with `export_to_dsl`\n\n" .
                                "**Important Guidelines:**\n" .
                                "- Keep element names concise but descriptive\n" .
                                "- Add meaningful descriptions to explain purpose\n" .
                                "- Use consistent naming conventions\n" .
                                "- Specify technology stack where relevant\n" .
                                "- Group related components logically\n" .
                                "- Ensure all relationships have clear descriptions\n\n" .
                                "**Output:**\n" .
                                "Provide the complete sequence of MCP tool calls with parameters. " .
                                "Explain the rationale for the architecture decisions."
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Explain the C4 model methodology
     *
     * This prompt provides a comprehensive explanation of the C4 model,
     * its levels, benefits, and how to use it effectively.
     *
     * @return array{messages: array<int, array{role: string, content: array<int, array{type: string, text?: string}>}>}
     */
    #[McpPrompt(
        name: 'explain_c4_model',
        description: 'Provide a comprehensive explanation of the C4 model methodology, its levels, and best practices'
    )]
    public function explainC4Model(): array
    {
        $this->logger->info('Generating C4 model explanation prompt');

        return [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Please provide a comprehensive explanation of the C4 model for software architecture documentation."
                        ]
                    ]
                ],
                [
                    'role' => 'assistant',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "# The C4 Model: Software Architecture Documentation\n\n" .
                                "The C4 model is a lean, pragmatic approach to software architecture diagramming. " .
                                "It provides a structured way to visualize software architecture at different levels of abstraction.\n\n" .
                                "## The Four C's\n\n" .
                                "### 1. **Context** (Level 1) - System Context Diagram\n" .
                                "**Purpose**: Show the big picture - how your system fits into the world\n\n" .
                                "**Elements:**\n" .
                                "- Your software system (the thing you're building)\n" .
                                "- Users/Actors (people and roles)\n" .
                                "- External systems (things your system depends on or interacts with)\n\n" .
                                "**Questions Answered:**\n" .
                                "- Who uses the system?\n" .
                                "- What does the system do?\n" .
                                "- What external systems does it integrate with?\n\n" .
                                "**Example Elements:**\n" .
                                "- Person: \"Customer\", \"Admin\", \"Support Agent\"\n" .
                                "- System: \"E-commerce Platform\" (Internal)\n" .
                                "- External: \"Payment Gateway\", \"Email Service\", \"Analytics System\"\n\n" .
                                "---\n\n" .
                                "### 2. **Container** (Level 2) - Container Diagram\n" .
                                "**Purpose**: Zoom into the system and show the high-level technology choices\n\n" .
                                "**What is a Container?**\n" .
                                "A container is a separately deployable/executable unit that executes code or stores data:\n" .
                                "- Web application (React SPA, Angular app)\n" .
                                "- Mobile app (iOS, Android)\n" .
                                "- Backend API (Spring Boot, Node.js)\n" .
                                "- Database (PostgreSQL, MongoDB)\n" .
                                "- Message queue (RabbitMQ, Kafka)\n" .
                                "- File storage (S3, Azure Blob)\n\n" .
                                "**Note**: Container ≠ Docker container (though it could be)\n\n" .
                                "**Questions Answered:**\n" .
                                "- What are the main building blocks?\n" .
                                "- What technologies are used?\n" .
                                "- How do containers communicate?\n" .
                                "- Where is data stored?\n\n" .
                                "---\n\n" .
                                "### 3. **Component** (Level 3) - Component Diagram\n" .
                                "**Purpose**: Zoom into a container to show its internal structure\n\n" .
                                "**What is a Component?**\n" .
                                "A component is a grouping of related functionality with a well-defined interface:\n" .
                                "- Controllers/REST endpoints\n" .
                                "- Services/Business logic\n" .
                                "- Repositories/Data access\n" .
                                "- Authentication/Authorization modules\n\n" .
                                "**Questions Answered:**\n" .
                                "- How is the container organized internally?\n" .
                                "- What are the main responsibilities?\n" .
                                "- How do components interact?\n\n" .
                                "**When to Create:**\n" .
                                "Only for the most important/complex containers\n\n" .
                                "---\n\n" .
                                "### 4. **Code** (Level 4) - Class/ER Diagrams\n" .
                                "**Purpose**: Show actual implementation details\n\n" .
                                "**Examples:**\n" .
                                "- UML class diagrams\n" .
                                "- Entity-relationship diagrams\n" .
                                "- Detailed sequence diagrams\n\n" .
                                "**Note**: Often auto-generated from code (IDE tools, Doxygen, JavaDoc)\n\n" .
                                "---\n\n" .
                                "## Additional Diagram Types\n\n" .
                                "### System Landscape\n" .
                                "Shows multiple systems and how they interact (higher than Context)\n\n" .
                                "### Dynamic Diagrams\n" .
                                "Show runtime behavior and collaboration:\n" .
                                "- Sequence of interactions\n" .
                                "- Message flows\n" .
                                "- Request/response patterns\n\n" .
                                "### Deployment Diagrams\n" .
                                "Show infrastructure and deployment topology:\n" .
                                "- Servers, cloud resources\n" .
                                "- Container instances\n" .
                                "- Network topology\n\n" .
                                "---\n\n" .
                                "## Core Principles\n\n" .
                                "### 1. **Abstraction First**\n" .
                                "Start at Level 1 (Context) and zoom in as needed. Not every system needs all 4 levels.\n\n" .
                                "### 2. **Audience-Driven**\n" .
                                "- Level 1: Everyone (stakeholders, product managers)\n" .
                                "- Level 2: Technical leaders, architects\n" .
                                "- Level 3: Developers working on specific containers\n" .
                                "- Level 4: Developers working on specific features\n\n" .
                                "### 3. **Notation Independence**\n" .
                                "C4 is not a notation - use any diagramming tool:\n" .
                                "- Structurizr (diagrams as code)\n" .
                                "- PlantUML, Mermaid\n" .
                                "- Draw.io, Lucidchart\n" .
                                "- Even whiteboard sketches\n\n" .
                                "### 4. **Consistent Terminology**\n" .
                                "Use the same names across all levels for the same element.\n\n" .
                                "---\n\n" .
                                "## Benefits of C4 Model\n\n" .
                                "✅ **Clear Hierarchy**: Structured levels prevent mixing abstractions\n" .
                                "✅ **Easy to Understand**: Simple notation (boxes and lines)\n" .
                                "✅ **Scalable**: Works for small and large systems\n" .
                                "✅ **Version Control**: Architecture as code can be versioned\n" .
                                "✅ **Always Up-to-Date**: Generated from code or kept in sync\n" .
                                "✅ **Communication**: Different views for different audiences\n" .
                                "✅ **Onboarding**: New team members understand the system quickly\n\n" .
                                "---\n\n" .
                                "## Best Practices\n\n" .
                                "1. **Start Simple**: Begin with System Context, add detail as needed\n" .
                                "2. **One Diagram = One Story**: Each diagram should answer specific questions\n" .
                                "3. **Keep It Current**: Review and update diagrams regularly\n" .
                                "4. **Use Meaningful Names**: Avoid acronyms, be explicit\n" .
                                "5. **Add Descriptions**: Every element should have a purpose description\n" .
                                "6. **Show Technology**: Specify technologies at Container and Component levels\n" .
                                "7. **Limit Scope**: Don't try to show everything in one diagram\n" .
                                "8. **Include Relationships**: Show how elements interact\n" .
                                "9. **Document Decisions**: Use ADRs (Architecture Decision Records)\n" .
                                "10. **Review Together**: Diagrams are collaboration tools\n\n" .
                                "---\n\n" .
                                "## Using C4 with Structurizr MCP\n\n" .
                                "With this MCP server, you can:\n\n" .
                                "1. **Create models** using natural language\n" .
                                "2. **Generate diagrams** in multiple formats (PlantUML, Mermaid)\n" .
                                "3. **Analyze architectures** for patterns and issues\n" .
                                "4. **Document decisions** with ADRs\n" .
                                "5. **Version control** your architecture as DSL code\n\n" .
                                "**Example Workflow:**\n" .
                                "```\n" .
                                "1. Describe your system in natural language\n" .
                                "2. Use 'generate_system_context' prompt to get started\n" .
                                "3. Use MCP tools to add containers and components\n" .
                                "4. Export to PlantUML/Mermaid for visualization\n" .
                                "5. Use 'analyze_architecture' to review the model\n" .
                                "6. Store DSL in git for version control\n" .
                                "```\n\n" .
                                "---\n\n" .
                                "## Common Questions\n\n" .
                                "**Q: How many levels should I create?**\n" .
                                "A: Start with Level 1 (Context). Add Level 2 (Container) if you have multiple deployable units. " .
                                "Only add Level 3 (Component) for complex containers that need internal documentation.\n\n" .
                                "**Q: What's the difference between a component and a container?**\n" .
                                "A: Containers are separately deployable (can run independently). " .
                                "Components live inside containers and can't run alone.\n\n" .
                                "**Q: Should I model database tables as components?**\n" .
                                "A: No. The database itself is a container. Tables are Level 4 (Code) details.\n\n" .
                                "**Q: How do I handle microservices?**\n" .
                                "A: Each microservice is typically a separate software system at Level 1, " .
                                "or a container within a larger system at Level 2, depending on your context.\n\n" .
                                "**Q: Can I use C4 for legacy systems?**\n" .
                                "A: Absolutely! C4 is great for documenting existing systems and helps with modernization planning.\n\n" .
                                "---\n\n" .
                                "## Resources\n\n" .
                                "- **Official C4 Model**: https://c4model.com\n" .
                                "- **Structurizr**: https://structurizr.com\n" .
                                "- **Examples**: Use 'create_example_workspace' prompt to see practical examples\n" .
                                "- **Community**: C4 model community and discussions on GitHub\n\n" .
                                "Would you like to create your first C4 model? I can help you get started!"
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Create an example workspace based on common architecture patterns
     *
     * This prompt generates a complete example workspace for learning purposes,
     * demonstrating C4 modeling best practices.
     *
     * @return array{messages: array<int, array{role: string, content: array<int, array{type: string, text?: string}>}>}
     */
    #[McpPrompt(
        name: 'create_example_workspace',
        description: 'Generate a complete example C4 workspace based on common architecture patterns (ecommerce, microservices, monolith, saas)'
    )]
    public function createExampleWorkspace(
        #[Schema(
            description: 'Type of example architecture to generate',
            enum: ['ecommerce', 'microservices', 'monolith', 'saas']
        )]
        string $type
    ): array {
        $this->logger->info('Generating example workspace prompt', ['type' => $type]);

        $exampleSpecs = $this->getExampleSpecification($type);

        return [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Create a complete example C4 workspace for a {$type} architecture pattern.\n\n" .
                                "This should be a realistic, production-like example that demonstrates best practices."
                        ]
                    ]
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $exampleSpecs
                        ]
                    ]
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "**Implementation Steps:**\n\n" .
                                "1. Create workspace with `create_workspace`\n" .
                                "2. Add all users/personas\n" .
                                "3. Add the main system and external systems\n" .
                                "4. Create system context view\n" .
                                "5. Add all containers with appropriate technologies\n" .
                                "6. Create container view\n" .
                                "7. For the most complex container, add components\n" .
                                "8. Create component view\n" .
                                "9. Add all relationships between elements\n" .
                                "10. Apply auto-layout to all views\n" .
                                "11. Add documentation section explaining the architecture\n" .
                                "12. Add an ADR for a key architectural decision\n" .
                                "13. Export to DSL\n\n" .
                                "**Quality Checklist:**\n" .
                                "✓ All elements have meaningful descriptions\n" .
                                "✓ Technologies are specified for containers and components\n" .
                                "✓ Relationships have clear descriptions\n" .
                                "✓ Views are properly organized\n" .
                                "✓ Documentation explains key decisions\n" .
                                "✓ Example demonstrates best practices\n\n" .
                                "Please generate the complete sequence of MCP tool calls to create this example."
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get detailed specification for each example type
     */
    private function getExampleSpecification(string $type): string
    {
        return match ($type) {
            'ecommerce' => "**E-Commerce Platform Example**\n\n" .
                "**System Overview:**\n" .
                "A modern e-commerce platform for online retail with inventory management, " .
                "order processing, payment integration, and customer management.\n\n" .
                "**Users:**\n" .
                "- Customer: Browses products, places orders, tracks shipments\n" .
                "- Admin: Manages products, inventory, orders\n" .
                "- Support Agent: Handles customer inquiries\n\n" .
                "**Containers:**\n" .
                "- Web Application (React SPA): Customer-facing storefront\n" .
                "- Admin Portal (React): Internal management interface\n" .
                "- API Gateway (Node.js): Request routing and authentication\n" .
                "- Product Service (Java Spring Boot): Product catalog management\n" .
                "- Order Service (Java Spring Boot): Order processing\n" .
                "- Payment Service (Java Spring Boot): Payment processing\n" .
                "- Database (PostgreSQL): Primary data store\n" .
                "- Cache (Redis): Session and catalog cache\n" .
                "- Message Queue (RabbitMQ): Async order processing\n\n" .
                "**External Systems:**\n" .
                "- Payment Gateway (Stripe): Payment processing\n" .
                "- Email Service (SendGrid): Transactional emails\n" .
                "- Shipping Provider (FedEx API): Shipping integration\n" .
                "- Analytics (Google Analytics): User tracking\n\n" .
                "**Key Relationships:**\n" .
                "- Customers use Web Application to browse and purchase\n" .
                "- Web App calls API Gateway for all backend operations\n" .
                "- Services communicate via REST APIs\n" .
                "- Order events published to Message Queue\n" .
                "- Payment Service integrates with Payment Gateway\n\n" .
                "**Component Example (Payment Service):**\n" .
                "- Payment Controller: REST endpoints\n" .
                "- Payment Processor: Business logic\n" .
                "- Payment Gateway Client: External integration\n" .
                "- Fraud Detection: Risk assessment\n" .
                "- Payment Repository: Data persistence\n\n" .
                "**ADR Example:**\n" .
                "Decision to use event-driven architecture for order processing " .
                "to handle peak loads during sales events.",

            'microservices' => "**Microservices Architecture Example**\n\n" .
                "**System Overview:**\n" .
                "A cloud-native microservices platform demonstrating service decomposition, " .
                "API gateway pattern, event-driven communication, and distributed data management.\n\n" .
                "**Users:**\n" .
                "- End User: Consumes services via mobile/web\n" .
                "- Developer: Manages and deploys microservices\n" .
                "- Operator: Monitors system health\n\n" .
                "**Containers (Microservices):**\n" .
                "- API Gateway (Kong/Nginx): Routing, auth, rate limiting\n" .
                "- User Service (Node.js): User management, authentication\n" .
                "- Product Service (Go): Product catalog\n" .
                "- Inventory Service (Java): Stock management\n" .
                "- Order Service (Python): Order orchestration\n" .
                "- Notification Service (Node.js): Email/SMS notifications\n" .
                "- User DB (PostgreSQL): User data\n" .
                "- Product DB (MongoDB): Product catalog\n" .
                "- Inventory DB (PostgreSQL): Stock levels\n" .
                "- Order DB (PostgreSQL): Order data\n" .
                "- Event Bus (Kafka): Async messaging\n" .
                "- Service Registry (Consul): Service discovery\n" .
                "- Config Server (Spring Cloud Config): Centralized configuration\n\n" .
                "**External Systems:**\n" .
                "- Authentication Provider (Auth0): OAuth/SSO\n" .
                "- Monitoring (Datadog): APM and metrics\n" .
                "- Logging (ELK Stack): Centralized logging\n\n" .
                "**Key Patterns:**\n" .
                "- API Gateway pattern for client communication\n" .
                "- Database per service for data isolation\n" .
                "- Event-driven communication via Kafka\n" .
                "- Circuit breaker for resilience\n" .
                "- Service discovery for dynamic routing\n\n" .
                "**Component Example (Order Service):**\n" .
                "- Order Controller: API endpoints\n" .
                "- Order Orchestrator: Saga coordinator\n" .
                "- Inventory Client: Calls Inventory Service\n" .
                "- Payment Client: Calls Payment Service\n" .
                "- Event Publisher: Kafka producer\n" .
                "- Order Repository: Database access\n\n" .
                "**ADR Example:**\n" .
                "Decision to use Kafka for event streaming to ensure eventual consistency " .
                "across microservices with guaranteed message delivery.",

            'monolith' => "**Modular Monolith Example**\n\n" .
                "**System Overview:**\n" .
                "A well-structured monolithic application with clear module boundaries, " .
                "demonstrating that monoliths can be clean and maintainable.\n\n" .
                "**Users:**\n" .
                "- Regular User: Uses core application features\n" .
                "- Administrator: Manages system configuration\n\n" .
                "**Containers:**\n" .
                "- Web Application (Spring Boot): Monolithic backend\n" .
                "- Frontend (Angular): SPA client\n" .
                "- Database (PostgreSQL): Relational data store\n" .
                "- Cache (Redis): Session and query cache\n" .
                "- File Storage (S3): Document storage\n\n" .
                "**External Systems:**\n" .
                "- Authentication (LDAP): Enterprise SSO\n" .
                "- Email Server (SMTP): Email delivery\n" .
                "- Reporting Service: Analytics and reports\n\n" .
                "**Component Example (Web Application - showing modular design):**\n" .
                "- Security Module:\n" .
                "  - Authentication Controller\n" .
                "  - Authorization Service\n" .
                "  - User Repository\n" .
                "- Core Business Module:\n" .
                "  - Business Controller\n" .
                "  - Business Service\n" .
                "  - Business Repository\n" .
                "- Reporting Module:\n" .
                "  - Report Controller\n" .
                "  - Report Generator\n" .
                "  - Report Repository\n" .
                "- Integration Module:\n" .
                "  - External API Client\n" .
                "  - Message Sender\n" .
                "- Common Module:\n" .
                "  - Configuration\n" .
                "  - Utilities\n" .
                "  - Validation\n\n" .
                "**Key Patterns:**\n" .
                "- Layered architecture (Presentation, Business, Data)\n" .
                "- Module boundaries enforced via packages\n" .
                "- Shared database with clear schema separation\n" .
                "- Dependency injection for loose coupling\n\n" .
                "**ADR Example:**\n" .
                "Decision to start with a modular monolith rather than microservices " .
                "to reduce operational complexity while maintaining clean architecture boundaries.",

            'saas' => "**Multi-Tenant SaaS Platform Example**\n\n" .
                "**System Overview:**\n" .
                "A B2B SaaS platform demonstrating multi-tenancy, subscription management, " .
                "and scalable architecture for serving multiple customers.\n\n" .
                "**Users:**\n" .
                "- Tenant Admin: Manages organization settings\n" .
                "- Tenant User: Uses application features\n" .
                "- Platform Admin: Manages platform and tenants\n\n" .
                "**Containers:**\n" .
                "- Web Application (React): Multi-tenant SPA\n" .
                "- API Service (Node.js/Express): Business logic\n" .
                "- Auth Service (Node.js): Authentication & authorization\n" .
                "- Billing Service (Java): Subscription management\n" .
                "- Tenant Service (Go): Tenant provisioning\n" .
                "- Application Database (PostgreSQL): Multi-tenant data\n" .
                "- Tenant Database Cluster (PostgreSQL): Per-tenant databases\n" .
                "- Cache Layer (Redis Cluster): Multi-tenant cache\n" .
                "- Background Workers (Node.js): Async jobs\n" .
                "- Message Queue (RabbitMQ): Job queue\n" .
                "- CDN (CloudFront): Static assets\n\n" .
                "**External Systems:**\n" .
                "- Payment Processor (Stripe): Subscription billing\n" .
                "- Email Service (AWS SES): Transactional emails\n" .
                "- SSO Provider (Okta): Enterprise authentication\n" .
                "- Analytics (Mixpanel): Usage tracking\n" .
                "- Monitoring (New Relic): APM\n\n" .
                "**Key Features:**\n" .
                "- Tenant isolation (data, users, configuration)\n" .
                "- Subscription tiers (Free, Pro, Enterprise)\n" .
                "- Usage metering and billing\n" .
                "- Custom domains per tenant\n" .
                "- Role-based access control per tenant\n\n" .
                "**Component Example (API Service):**\n" .
                "- Tenant Middleware: Tenant identification\n" .
                "- API Controllers: REST endpoints\n" .
                "- Business Services: Core logic\n" .
                "- Multi-Tenant Repository: Data access with tenant context\n" .
                "- Usage Tracker: Metering component\n" .
                "- Feature Flag Service: Per-tenant features\n\n" .
                "**ADR Example:**\n" .
                "Decision to use shared database with tenant_id isolation for cost efficiency " .
                "at lower tiers, with option for dedicated databases for enterprise customers.",

            default => "Unknown example type: {$type}"
        };
    }
}
