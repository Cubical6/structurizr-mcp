<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Tools;

use Mcp\Exception\ToolCallException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Structurizr\ValidationResult;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Tools\AnalysisTools;

/**
 * Unit tests for AnalysisTools
 *
 * @covers \StructurizrMcp\Tools\AnalysisTools
 */
class AnalysisToolsTest extends TestCase
{
    private WorkspaceManager&MockObject $workspaceManager;
    private CliWrapper&MockObject $cliWrapper;
    private LoggerInterface&MockObject $logger;
    private AnalysisTools $tools;

    protected function setUp(): void
    {
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->cliWrapper = $this->createMock(CliWrapper::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tools = new AnalysisTools(
            $this->workspaceManager,
            $this->cliWrapper,
            $this->logger,
        );
    }

    /**
     * Create a sample DSL with multiple elements and relationships
     */
    private function createSampleDsl(): string
    {
        return <<<'DSL'
workspace "E-Commerce System" {
    model {
        customer = person "Customer" "A customer of the e-commerce system"
        admin = person "Administrator" "System administrator"

        ecommerceSystem = softwareSystem "E-Commerce System" "Main e-commerce platform"
        paymentGateway = softwareSystem "Payment Gateway" "External payment processor"
        emailSystem = softwareSystem "Email System" "Email notification service"

        customer -> ecommerceSystem "Browses products and places orders"
        admin -> ecommerceSystem "Manages products and orders"
        ecommerceSystem -> paymentGateway "Processes payments"
        ecommerceSystem -> emailSystem "Sends order confirmations"
    }
}
DSL;
    }

    /**
     * Create a DSL with nested containers
     */
    private function createDslWithContainers(): string
    {
        return <<<'DSL'
workspace "System" {
    model {
        user = person "User" "A user"
        system = softwareSystem "System" "Main system" {
            webapp = container "Web Application" "Frontend app" "React"
            api = container "API" "REST API" "Spring Boot"
            db = container "Database" "Data storage" "PostgreSQL"
        }

        user -> webapp "Uses"
        webapp -> api "Makes API calls to"
        api -> db "Reads from and writes to"
    }
}
DSL;
    }

    // ========================================
    // Tests for analyzeDependencies()
    // ========================================

    public function testAnalyzeDependenciesForEntireWorkspace(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_test')
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Analyzing dependencies for workspace: ws_test', ['elementId' => null]);

        $result = $this->tools->analyzeDependencies('ws_test');

        $this->assertEquals('ws_test', $result['workspaceId']);
        $this->assertEquals(5, $result['totalElements']); // 2 persons + 3 systems
        $this->assertEquals(4, $result['totalRelationships']);
        $this->assertArrayHasKey('dependencyGraph', $result);
        $this->assertArrayHasKey('relationships', $result);

        // Verify dependency graph structure
        $graph = $result['dependencyGraph'];
        $this->assertArrayHasKey('ecommerceSystem', $graph);

        // E-commerce system should have most dependencies (2 inbound, 2 outbound = 4 total)
        $firstElement = array_key_first($graph);
        $this->assertEquals(4, $graph[$firstElement]['totalDependencies']);
    }

    public function testAnalyzeDependenciesForSpecificElement(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Analyzing dependencies for workspace: ws_test', ['elementId' => 'ecommerceSystem']);

        $result = $this->tools->analyzeDependencies('ws_test', 'ecommerceSystem');

        $this->assertEquals('ws_test', $result['workspaceId']);
        $this->assertEquals('ecommerceSystem', $result['elementId']);
        $this->assertArrayHasKey('element', $result);
        $this->assertEquals('E-Commerce System', $result['element']['name']);
        $this->assertEquals('softwareSystem', $result['element']['type']);

        // Verify inbound dependencies (from customer and admin)
        $this->assertEquals(2, $result['totalInbound']);
        $this->assertCount(2, $result['inboundDependencies']);

        // Verify outbound dependencies (to payment and email)
        $this->assertEquals(2, $result['totalOutbound']);
        $this->assertCount(2, $result['outboundDependencies']);
    }

    public function testAnalyzeDependenciesWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->tools->analyzeDependencies('nonexistent');
    }

    public function testAnalyzeDependenciesElementNotFound(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->willReturn($workspace);

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Element not found: nonexistentElement');

        $this->tools->analyzeDependencies('ws_test', 'nonexistentElement');
    }

    public function testAnalyzeDependenciesSortsByMostConnected(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->willReturn($workspace);

        $result = $this->tools->analyzeDependencies('ws_test');

        $graph = $result['dependencyGraph'];
        $graphValues = array_values($graph);

        // Verify sorting - first element should have most dependencies
        $previousCount = PHP_INT_MAX;
        foreach ($graphValues as $item) {
            $this->assertLessThanOrEqual($previousCount, $item['totalDependencies']);
            $previousCount = $item['totalDependencies'];
        }

        // E-commerce system should be first (4 total dependencies)
        $this->assertEquals('ecommerceSystem', array_key_first($graph));
        $this->assertEquals(4, $graphValues[0]['totalDependencies']);
    }

    public function testAnalyzeDependenciesWithNoRelationships(): void
    {
        $dsl = <<<'DSL'
workspace "Test" {
    model {
        user = person "User" "A user"
        system = softwareSystem "System" "A system"
    }
}
DSL;

        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test',
            description: '',
            dsl: $dsl,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        $result = $this->tools->analyzeDependencies('ws_test');

        $this->assertEquals(0, $result['totalRelationships']);
        $this->assertEmpty($result['relationships']);

        // All elements should have 0 dependencies
        foreach ($result['dependencyGraph'] as $element) {
            $this->assertEquals(0, $element['totalDependencies']);
        }
    }

    // ========================================
    // Tests for findElement()
    // ========================================

    public function testFindElementExactMatch(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_test')
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Finding elements in workspace: ws_test', ['searchName' => 'Customer']);

        $result = $this->tools->findElement('ws_test', 'Customer');

        $this->assertEquals('ws_test', $result['workspaceId']);
        $this->assertEquals('Customer', $result['searchTerm']);
        $this->assertEquals(1, $result['count']);
        $this->assertCount(1, $result['matches']);

        $match = $result['matches'][0];
        $this->assertEquals('customer', $match['id']);
        $this->assertEquals('Customer', $match['name']);
        $this->assertEquals('person', $match['type']);
        $this->assertEquals('A customer of the e-commerce system', $match['description']);
    }

    public function testFindElementPartialMatch(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        $result = $this->tools->findElement('ws_test', 'System');

        // Should match "E-Commerce System", "Payment Gateway", and "Email System"
        $this->assertGreaterThanOrEqual(2, $result['count']);

        // Verify all matches contain "System" in name
        foreach ($result['matches'] as $match) {
            $this->assertStringContainsStringIgnoringCase('System', $match['name']);
        }
    }

    public function testFindElementCaseInsensitive(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        // Search with lowercase when element is "Customer"
        $result = $this->tools->findElement('ws_test', 'customer');

        $this->assertEquals(1, $result['count']);
        $this->assertEquals('Customer', $result['matches'][0]['name']);
    }

    public function testFindElementNoMatches(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        $result = $this->tools->findElement('ws_test', 'NonExistent');

        $this->assertEquals(0, $result['count']);
        $this->assertEmpty($result['matches']);
    }

    public function testFindElementWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->tools->findElement('nonexistent', 'Test');
    }

    public function testFindElementMultipleMatches(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        // Search for 'e' should match multiple elements
        $result = $this->tools->findElement('ws_test', 'e');

        $this->assertGreaterThan(1, $result['count']);
        $this->assertGreaterThan(1, count($result['matches']));
    }

    public function testFindElementWithContainers(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createDslWithContainers(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        $result = $this->tools->findElement('ws_test', 'Application');

        $this->assertEquals(1, $result['count']);
        $match = $result['matches'][0];
        $this->assertEquals('Web Application', $match['name']);
        $this->assertEquals('container', $match['type']);
        $this->assertEquals('React', $match['technology']);
    }

    // ========================================
    // Tests for validateWorkspace()
    // ========================================

    public function testValidateWorkspaceValid(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $validationResult = new ValidationResult(
            valid: true,
            errors: [],
            warnings: [],
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_test')
            ->willReturn($workspace);

        $this->cliWrapper
            ->expects($this->once())
            ->method('validate')
            ->willReturn($validationResult);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Validating workspace: ws_test');

        $result = $this->tools->validateWorkspace('ws_test');

        $this->assertEquals('ws_test', $result['workspaceId']);
        $this->assertTrue($result['isValid']);
        $this->assertEmpty($result['errors']);
        $this->assertEmpty($result['warnings']);
        $this->assertEquals(0, $result['errorCount']);
        $this->assertEquals(0, $result['warningCount']);
        $this->assertArrayHasKey('summary', $result);
    }

    public function testValidateWorkspaceWithErrors(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: 'workspace "Test" { invalid syntax here }',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $validationResult = new ValidationResult(
            valid: false,
            errors: [
                'Line 1: Unexpected token "invalid"',
                'Line 1: Missing closing brace',
            ],
            warnings: [],
        );

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->cliWrapper->method('validate')->willReturn($validationResult);

        $result = $this->tools->validateWorkspace('ws_test');

        $this->assertFalse($result['isValid']);
        $this->assertCount(2, $result['errors']);
        $this->assertEquals(2, $result['errorCount']);
        $this->assertEquals(0, $result['warningCount']);
        $this->assertStringContainsString('Unexpected token', $result['errors'][0]);
    }

    public function testValidateWorkspaceWithWarnings(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $validationResult = new ValidationResult(
            valid: true,
            errors: [],
            warnings: [
                'Element "customer" has no relationships',
                'View "SystemContext" not defined',
            ],
        );

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->cliWrapper->method('validate')->willReturn($validationResult);

        $result = $this->tools->validateWorkspace('ws_test');

        $this->assertTrue($result['isValid']);
        $this->assertEmpty($result['errors']);
        $this->assertCount(2, $result['warnings']);
        $this->assertEquals(0, $result['errorCount']);
        $this->assertEquals(2, $result['warningCount']);
    }

    public function testValidateWorkspaceEmptyDsl(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: '',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->willReturn($workspace);

        // CLI validate should NOT be called for empty DSL
        $this->cliWrapper
            ->expects($this->never())
            ->method('validate');

        $result = $this->tools->validateWorkspace('ws_test');

        $this->assertFalse($result['isValid']);
        $this->assertCount(1, $result['errors']);
        $this->assertEquals('Workspace DSL is empty', $result['errors'][0]);
        $this->assertEmpty($result['warnings']);
        $this->assertStringContainsString('empty', $result['summary']);
    }

    public function testValidateWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->tools->validateWorkspace('nonexistent');
    }

    public function testValidateWorkspaceWithWhitespaceOnlyDsl(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test Workspace',
            description: 'Test',
            dsl: "   \n\t   \n  ",
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->cliWrapper->expects($this->never())->method('validate');

        $result = $this->tools->validateWorkspace('ws_test');

        $this->assertFalse($result['isValid']);
        $this->assertEquals('Workspace DSL is empty', $result['errors'][0]);
    }

    // ========================================
    // General tests
    // ========================================

    public function testLoggingBehaviorForAllMethods(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test',
            description: '',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        // Test analyzeDependencies logging
        $this->logger->expects($this->exactly(3))->method('info');

        $this->tools->analyzeDependencies('ws_test');
        $this->tools->findElement('ws_test', 'Test');
        $this->tools->validateWorkspace('ws_test');
    }

    public function testErrorWrappingInToolCallException(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test',
            description: '',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        // Make cliWrapper throw a generic exception
        $this->cliWrapper
            ->method('validate')
            ->willThrowException(new \RuntimeException('CLI execution failed'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Failed to validate workspace: CLI execution failed');

        $this->tools->validateWorkspace('ws_test');
    }

    public function testAnalyzeDependenciesWrapsGenericException(): void
    {
        $this->workspaceManager
            ->method('load')
            ->willThrowException(new \RuntimeException('Database connection failed'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Failed to analyze dependencies');

        $this->tools->analyzeDependencies('ws_test');
    }

    public function testFindElementWrapsGenericException(): void
    {
        $this->workspaceManager
            ->method('load')
            ->willThrowException(new \RuntimeException('File system error'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Failed to find elements');

        $this->tools->findElement('ws_test', 'Test');
    }

    public function testAnalyzeDependenciesWithComplexDsl(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test',
            description: '',
            dsl: $this->createDslWithContainers(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        $result = $this->tools->analyzeDependencies('ws_test');

        // Should parse containers correctly
        $this->assertGreaterThan(1, $result['totalElements']);
        $this->assertGreaterThan(0, $result['totalRelationships']);

        // Verify containers are in dependency graph
        $elementIds = array_keys($result['dependencyGraph']);
        $this->assertContains('webapp', $elementIds);
        $this->assertContains('api', $elementIds);
        $this->assertContains('db', $elementIds);
    }

    public function testValidateWorkspaceHandlesErrorsAndWarningsTogether(): void
    {
        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test',
            description: '',
            dsl: $this->createSampleDsl(),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $validationResult = new ValidationResult(
            valid: false,
            errors: ['Critical error in model'],
            warnings: ['Missing view definition', 'Unused element'],
        );

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->cliWrapper->method('validate')->willReturn($validationResult);

        $result = $this->tools->validateWorkspace('ws_test');

        $this->assertFalse($result['isValid']);
        $this->assertEquals(1, $result['errorCount']);
        $this->assertEquals(2, $result['warningCount']);
        $this->assertCount(1, $result['errors']);
        $this->assertCount(2, $result['warnings']);
    }
}
