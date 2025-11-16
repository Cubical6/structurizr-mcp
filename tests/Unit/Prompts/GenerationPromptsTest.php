<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Prompts;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Prompts\GenerationPrompts;

class GenerationPromptsTest extends TestCase
{
    private LoggerInterface $logger;
    private GenerationPrompts $generationPrompts;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->generationPrompts = new GenerationPrompts($this->logger);
    }

    public function testGenerateSystemContextReturnsValidMessageStructure(): void
    {
        $description = 'An e-commerce platform that allows users to browse products, place orders, and track shipments.';

        $result = $this->generationPrompts->generateSystemContext($description);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('messages', $result);
        $this->assertIsArray($result['messages']);
        $this->assertCount(2, $result['messages']);

        // Verify first message includes the description
        $this->assertEquals('user', $result['messages'][0]['role']);
        $this->assertStringContainsString($description, $result['messages'][0]['content'][0]['text']);
        $this->assertStringContainsString('System Context diagram', $result['messages'][0]['content'][0]['text']);

        // Verify second message has generation guidelines
        $this->assertEquals('user', $result['messages'][1]['role']);
        $guidelinesText = $result['messages'][1]['content'][0]['text'];
        $this->assertStringContainsString('Generation Guidelines', $guidelinesText);
        $this->assertStringContainsString('add_person', $guidelinesText);
        $this->assertStringContainsString('add_software_system', $guidelinesText);
        $this->assertStringContainsString('add_relationship', $guidelinesText);
        $this->assertStringContainsString('create_system_context_view', $guidelinesText);
    }

    public function testGenerateSystemContextLogsCorrectly(): void
    {
        $description = 'Test system description';

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Generating system context prompt', ['descriptionLength' => strlen($description)]);

        $this->generationPrompts->generateSystemContext($description);
    }

    public function testGenerateSystemContextIncludesToolInstructions(): void
    {
        $description = 'A microservices platform with multiple services.';

        $result = $this->generationPrompts->generateSystemContext($description);

        $guidelinesText = $result['messages'][1]['content'][0]['text'];

        // Verify all key steps are mentioned
        $this->assertStringContainsString('Identify Users/Actors', $guidelinesText);
        $this->assertStringContainsString('Identify the System', $guidelinesText);
        $this->assertStringContainsString('Identify External Systems', $guidelinesText);
        $this->assertStringContainsString('Define Relationships', $guidelinesText);
        $this->assertStringContainsString('Create System Context View', $guidelinesText);
        $this->assertStringContainsString('apply_auto_layout', $guidelinesText);
    }

    public function testCreateFromDescriptionReturnsValidMessageStructure(): void
    {
        $description = 'A SaaS platform with web frontend, REST API, database, and integration with external payment service.';

        $result = $this->generationPrompts->createFromDescription($description);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('messages', $result);
        $this->assertCount(2, $result['messages']);

        // Verify description is included
        $this->assertStringContainsString($description, $result['messages'][0]['content'][0]['text']);
        $this->assertStringContainsString('complete C4 model', $result['messages'][0]['content'][0]['text']);

        // Verify step-by-step process is included
        $processText = $result['messages'][1]['content'][0]['text'];
        $this->assertStringContainsString('Step-by-Step Generation Process', $processText);
    }

    public function testCreateFromDescriptionIncludesAllPhases(): void
    {
        $description = 'Comprehensive architecture description';

        $result = $this->generationPrompts->createFromDescription($description);

        $processText = $result['messages'][1]['content'][0]['text'];

        // Verify all phases are mentioned
        $this->assertStringContainsString('Phase 1: Workspace Setup', $processText);
        $this->assertStringContainsString('Phase 2: System Context (Level 1)', $processText);
        $this->assertStringContainsString('Phase 3: Container (Level 2)', $processText);
        $this->assertStringContainsString('Phase 4: Component (Level 3)', $processText);
        $this->assertStringContainsString('Phase 5: Documentation', $processText);
        $this->assertStringContainsString('Phase 6: Styling and Export', $processText);

        // Verify key tools are mentioned
        $this->assertStringContainsString('create_workspace', $processText);
        $this->assertStringContainsString('add_container', $processText);
        $this->assertStringContainsString('add_component', $processText);
        $this->assertStringContainsString('create_container_view', $processText);
        $this->assertStringContainsString('create_component_view', $processText);
        $this->assertStringContainsString('export_to_dsl', $processText);
    }

    public function testCreateFromDescriptionLogsCorrectly(): void
    {
        $description = 'Test architecture';

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Generating full C4 model prompt', ['descriptionLength' => strlen($description)]);

        $this->generationPrompts->createFromDescription($description);
    }

    public function testExplainC4ModelReturnsValidMessageStructure(): void
    {
        $result = $this->generationPrompts->explainC4Model();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('messages', $result);
        $this->assertCount(2, $result['messages']);

        // First message is user request
        $this->assertEquals('user', $result['messages'][0]['role']);
        $this->assertStringContainsString('comprehensive explanation', $result['messages'][0]['content'][0]['text']);

        // Second message is assistant explanation
        $this->assertEquals('assistant', $result['messages'][1]['role']);
        $explanationText = $result['messages'][1]['content'][0]['text'];
        $this->assertStringContainsString('C4 Model', $explanationText);
    }

    public function testExplainC4ModelIncludesAllLevels(): void
    {
        $result = $this->generationPrompts->explainC4Model();

        $explanationText = $result['messages'][1]['content'][0]['text'];

        // Verify all 4 levels are explained
        $this->assertStringContainsString('Context', $explanationText);
        $this->assertStringContainsString('Container', $explanationText);
        $this->assertStringContainsString('Component', $explanationText);
        $this->assertStringContainsString('Code', $explanationText);

        // Verify key concepts
        $this->assertStringContainsString('System Context Diagram', $explanationText);
        $this->assertStringContainsString('Container Diagram', $explanationText);
        $this->assertStringContainsString('Component Diagram', $explanationText);
    }

    public function testExplainC4ModelIncludesBestPractices(): void
    {
        $result = $this->generationPrompts->explainC4Model();

        $explanationText = $result['messages'][1]['content'][0]['text'];

        $this->assertStringContainsString('Best Practices', $explanationText);
        $this->assertStringContainsString('Benefits', $explanationText);
        $this->assertStringContainsString('Core Principles', $explanationText);
    }

    public function testExplainC4ModelIncludesStructurizrMcpInstructions(): void
    {
        $result = $this->generationPrompts->explainC4Model();

        $explanationText = $result['messages'][1]['content'][0]['text'];

        $this->assertStringContainsString('Using C4 with Structurizr MCP', $explanationText);
        $this->assertStringContainsString('generate_system_context', $explanationText);
        $this->assertStringContainsString('analyze_architecture', $explanationText);
    }

    public function testExplainC4ModelLogsCorrectly(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Generating C4 model explanation prompt');

        $this->generationPrompts->explainC4Model();
    }

    public function testCreateExampleWorkspaceEcommerceReturnsValidStructure(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('ecommerce');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('messages', $result);
        $this->assertCount(3, $result['messages']);

        // Verify introduction
        $this->assertStringContainsString('ecommerce', $result['messages'][0]['content'][0]['text']);

        // Verify specification
        $specText = $result['messages'][1]['content'][0]['text'];
        $this->assertStringContainsString('E-Commerce Platform Example', $specText);
        $this->assertStringContainsString('Product Service', $specText);
        $this->assertStringContainsString('Order Service', $specText);
        $this->assertStringContainsString('Payment Gateway', $specText);

        // Verify implementation steps
        $stepsText = $result['messages'][2]['content'][0]['text'];
        $this->assertStringContainsString('Implementation Steps', $stepsText);
        $this->assertStringContainsString('create_workspace', $stepsText);
    }

    public function testCreateExampleWorkspaceMicroservicesReturnsValidStructure(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('microservices');

        $specText = $result['messages'][1]['content'][0]['text'];

        $this->assertStringContainsString('Microservices Architecture Example', $specText);
        $this->assertStringContainsString('API Gateway', $specText);
        $this->assertStringContainsString('Event Bus', $specText);
        $this->assertStringContainsString('Kafka', $specText);
        $this->assertStringContainsString('Service Registry', $specText);
    }

    public function testCreateExampleWorkspaceMonolithReturnsValidStructure(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('monolith');

        $specText = $result['messages'][1]['content'][0]['text'];

        $this->assertStringContainsString('Modular Monolith Example', $specText);
        $this->assertStringContainsString('Spring Boot', $specText);
        $this->assertStringContainsString('Security Module', $specText);
        $this->assertStringContainsString('Core Business Module', $specText);
        $this->assertStringContainsString('Layered architecture', $specText);
    }

    public function testCreateExampleWorkspaceSaasReturnsValidStructure(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('saas');

        $specText = $result['messages'][1]['content'][0]['text'];

        $this->assertStringContainsString('Multi-Tenant SaaS Platform Example', $specText);
        $this->assertStringContainsString('Tenant Admin', $specText);
        $this->assertStringContainsString('Auth Service', $specText);
        $this->assertStringContainsString('Billing Service', $specText);
        $this->assertStringContainsString('Multi-tenant', $specText);
        $this->assertStringContainsString('Subscription', $specText);
    }

    public function testCreateExampleWorkspaceIncludesQualityChecklist(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('ecommerce');

        $stepsText = $result['messages'][2]['content'][0]['text'];

        $this->assertStringContainsString('Quality Checklist', $stepsText);
        $this->assertStringContainsString('meaningful descriptions', $stepsText);
        $this->assertStringContainsString('Technologies are specified', $stepsText);
        $this->assertStringContainsString('best practices', $stepsText);
    }

    public function testCreateExampleWorkspaceLogsCorrectly(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Generating example workspace prompt', ['type' => 'ecommerce']);

        $this->generationPrompts->createExampleWorkspace('ecommerce');
    }

    public function testAllExampleTypesIncludeAdrRecommendation(): void
    {
        $types = ['ecommerce', 'microservices', 'monolith', 'saas'];

        foreach ($types as $type) {
            $result = $this->generationPrompts->createExampleWorkspace($type);
            $specText = $result['messages'][1]['content'][0]['text'];

            $this->assertStringContainsString('ADR', $specText, "Type {$type} should include ADR example");
        }
    }

    public function testAllExampleTypesIncludeComponentExample(): void
    {
        $types = ['ecommerce', 'microservices', 'monolith', 'saas'];

        foreach ($types as $type) {
            $result = $this->generationPrompts->createExampleWorkspace($type);
            $specText = $result['messages'][1]['content'][0]['text'];

            $this->assertStringContainsString('Component Example', $specText, "Type {$type} should include component example");
        }
    }

    public function testAllExampleTypesIncludeExternalSystems(): void
    {
        $types = ['ecommerce', 'microservices', 'monolith', 'saas'];

        foreach ($types as $type) {
            $result = $this->generationPrompts->createExampleWorkspace($type);
            $specText = $result['messages'][1]['content'][0]['text'];

            $this->assertStringContainsString('External', $specText, "Type {$type} should include external systems");
        }
    }

    public function testCreateExampleWorkspaceIncludesImplementationSteps(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('microservices');

        $stepsText = $result['messages'][2]['content'][0]['text'];

        // Verify all major steps are included
        $this->assertStringContainsString('Create workspace', $stepsText);
        $this->assertStringContainsString('Add all users', $stepsText);
        $this->assertStringContainsString('Create container view', $stepsText);
        $this->assertStringContainsString('Create component view', $stepsText);
        $this->assertStringContainsString('Apply auto-layout', $stepsText);
        $this->assertStringContainsString('Export to DSL', $stepsText);
    }

    public function testEcommerceExampleIncludesRelevantTechnologies(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('ecommerce');
        $specText = $result['messages'][1]['content'][0]['text'];

        // Check for e-commerce specific technologies
        $this->assertStringContainsString('React', $specText);
        $this->assertStringContainsString('PostgreSQL', $specText);
        $this->assertStringContainsString('Redis', $specText);
        $this->assertStringContainsString('Stripe', $specText);
    }

    public function testMicroservicesExampleIncludesRelevantPatterns(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('microservices');
        $specText = $result['messages'][1]['content'][0]['text'];

        // Check for microservices patterns
        $this->assertStringContainsString('API Gateway pattern', $specText);
        $this->assertStringContainsString('Database per service', $specText);
        $this->assertStringContainsString('Event-driven', $specText);
        $this->assertStringContainsString('Circuit breaker', $specText);
        $this->assertStringContainsString('Service discovery', $specText);
    }

    public function testMonolithExampleEmphasizesModularity(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('monolith');
        $specText = $result['messages'][1]['content'][0]['text'];

        // Check for modular monolith concepts
        $this->assertStringContainsString('Modular', $specText);
        $this->assertStringContainsString('Module boundaries', $specText);
        $this->assertStringContainsString('Layered architecture', $specText);
    }

    public function testSaasExampleIncludesMultiTenancyConcepts(): void
    {
        $result = $this->generationPrompts->createExampleWorkspace('saas');
        $specText = $result['messages'][1]['content'][0]['text'];

        // Check for SaaS/multi-tenancy concepts
        $this->assertStringContainsString('Multi-Tenant', $specText);
        $this->assertStringContainsString('Tenant isolation', $specText);
        $this->assertStringContainsString('Subscription', $specText);
        $this->assertStringContainsString('Usage metering', $specText);
    }
}
