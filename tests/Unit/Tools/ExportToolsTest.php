<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use StructurizrMcp\Tools\ExportTools;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Exception\InvalidDslException;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for ExportTools
 *
 * @covers \StructurizrMcp\Tools\ExportTools
 */
class ExportToolsTest extends TestCase
{
    private WorkspaceManager&MockObject $workspaceManager;
    private CliWrapper&MockObject $cliWrapper;
    private LoggerInterface&MockObject $logger;
    private ExportTools $tools;

    protected function setUp(): void
    {
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->cliWrapper = $this->createMock(CliWrapper::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tools = new ExportTools(
            $this->workspaceManager,
            $this->cliWrapper,
            $this->logger
        );
    }

    /**
     * Test exportToDsl returns workspace DSL
     */
    public function testExportToDslReturnsWorkspaceDsl(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test description',
            dsl: 'workspace "Test Workspace" "Test description" { model { } }',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Exporting workspace to DSL: ws_123');

        $result = $this->tools->exportToDsl('ws_123');

        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertEquals('Test Workspace', $result['name']);
        $this->assertEquals('workspace "Test Workspace" "Test description" { model { } }', $result['dsl']);
    }

    /**
     * Test exportToDsl throws exception when workspace not found
     */
    public function testExportToDslThrowsExceptionWhenWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->tools->exportToDsl('nonexistent');
    }

    /**
     * Test exportToPlantUml without view key exports full workspace
     */
    public function testExportToPlantUmlWithoutViewKey(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test description',
            dsl: 'workspace "Test" { model { } }',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $plantUmlContent = '@startuml
skinparam componentStyle rectangle
@enduml';

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->cliWrapper
            ->expects($this->once())
            ->method('export')
            ->with(
                $this->stringContains('ws_export_'),
                'plantuml'
            )
            ->willReturn($plantUmlContent);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Exporting workspace to PlantUML: ws_123', ['viewKey' => null]);

        $result = $this->tools->exportToPlantUml('ws_123');

        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertEquals('Test Workspace', $result['name']);
        $this->assertNull($result['viewKey']);
        $this->assertEquals('plantuml', $result['format']);
        $this->assertEquals($plantUmlContent, $result['content']);
    }

    /**
     * Test exportToPlantUml with view key exports specific view
     */
    public function testExportToPlantUmlWithViewKey(): void
    {
        $workspace = new Workspace(
            id: 'ws_456',
            name: 'Multi-View Workspace',
            description: '',
            dsl: 'workspace "Test" { model { } views { systemContext sys "SystemContext" { } } }',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $plantUmlContent = '@startuml SystemContext
@enduml';

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_456')
            ->willReturn($workspace);

        $this->cliWrapper
            ->expects($this->once())
            ->method('export')
            ->with(
                $this->stringContains('ws_export_'),
                'plantuml'
            )
            ->willReturn($plantUmlContent);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Exporting workspace to PlantUML: ws_456', ['viewKey' => 'SystemContext']);

        $result = $this->tools->exportToPlantUml('ws_456', 'SystemContext');

        $this->assertEquals('ws_456', $result['workspaceId']);
        $this->assertEquals('Multi-View Workspace', $result['name']);
        $this->assertEquals('SystemContext', $result['viewKey']);
        $this->assertEquals('plantuml', $result['format']);
        $this->assertStringContainsString('SystemContext', $result['content']);
    }

    /**
     * Test exportToPlantUml throws exception when workspace not found
     */
    public function testExportToPlantUmlThrowsExceptionWhenWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->tools->exportToPlantUml('nonexistent');
    }

    /**
     * Test exportToMermaid without view key exports full workspace
     */
    public function testExportToMermaidWithoutViewKey(): void
    {
        $workspace = new Workspace(
            id: 'ws_789',
            name: 'Mermaid Workspace',
            description: 'Testing Mermaid export',
            dsl: 'workspace "Mermaid Test" { model { } }',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $mermaidContent = 'graph TB
    User[User]
    System[System]
    User-->System';

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_789')
            ->willReturn($workspace);

        $this->cliWrapper
            ->expects($this->once())
            ->method('export')
            ->with(
                $this->stringContains('ws_export_'),
                'mermaid'
            )
            ->willReturn($mermaidContent);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Exporting workspace to Mermaid: ws_789', ['viewKey' => null]);

        $result = $this->tools->exportToMermaid('ws_789');

        $this->assertEquals('ws_789', $result['workspaceId']);
        $this->assertEquals('Mermaid Workspace', $result['name']);
        $this->assertNull($result['viewKey']);
        $this->assertEquals('mermaid', $result['format']);
        $this->assertEquals($mermaidContent, $result['content']);
    }

    /**
     * Test exportToMermaid with view key exports specific view
     */
    public function testExportToMermaidWithViewKey(): void
    {
        $workspace = new Workspace(
            id: 'ws_999',
            name: 'Specific View Workspace',
            description: '',
            dsl: 'workspace "Test" { model { } views { container sys "Containers" { } } }',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $mermaidContent = 'graph TB
    subgraph Containers
        WebApp[Web Application]
        Database[Database]
    end';

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_999')
            ->willReturn($workspace);

        $this->cliWrapper
            ->expects($this->once())
            ->method('export')
            ->with(
                $this->stringContains('ws_export_'),
                'mermaid'
            )
            ->willReturn($mermaidContent);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Exporting workspace to Mermaid: ws_999', ['viewKey' => 'Containers']);

        $result = $this->tools->exportToMermaid('ws_999', 'Containers');

        $this->assertEquals('ws_999', $result['workspaceId']);
        $this->assertEquals('Specific View Workspace', $result['name']);
        $this->assertEquals('Containers', $result['viewKey']);
        $this->assertEquals('mermaid', $result['format']);
        $this->assertStringContainsString('Containers', $result['content']);
    }

    /**
     * Test exportToMermaid throws exception when workspace not found
     */
    public function testExportToMermaidThrowsExceptionWhenWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('missing')
            ->willThrowException(new WorkspaceNotFoundException('missing'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found: missing');

        $this->tools->exportToMermaid('missing');
    }

    /**
     * Test importFromDsl creates workspace from DSL content
     */
    public function testImportFromDslCreatesWorkspace(): void
    {
        $dslContent = 'workspace "Imported Workspace" "This is an imported workspace" {
    model {
        user = person "User"
        system = softwareSystem "System"
        user -> system "Uses"
    }
    views {
        systemContext system "SystemContext" {
            include *
        }
    }
}';

        $workspace = new Workspace(
            id: 'ws_imported',
            name: 'Imported Workspace',
            description: 'This is an imported workspace',
            dsl: $dslContent,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->with('Imported Workspace', 'This is an imported workspace')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('updateDsl')
            ->with('ws_imported', $dslContent)
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Importing workspace from DSL');

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Extracted workspace metadata from DSL', [
                'name' => 'Imported Workspace',
                'description' => 'This is an imported workspace',
            ]);

        $result = $this->tools->importFromDsl($dslContent);

        $this->assertEquals('ws_imported', $result['workspaceId']);
        $this->assertEquals('Imported Workspace', $result['name']);
        $this->assertEquals('This is an imported workspace', $result['description']);
        $this->assertEquals($dslContent, $result['dsl']);
    }

    /**
     * Test importFromDsl validates DSL is not empty
     */
    public function testImportFromDslValidatesNotEmpty(): void
    {
        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('DSL content cannot be empty');

        $this->tools->importFromDsl('');
    }

    /**
     * Test importFromDsl extracts workspace name correctly
     */
    public function testImportFromDslExtractsWorkspaceName(): void
    {
        $dslContent = 'workspace "My Architecture" "Description here" { model { } }';

        $workspace = new Workspace(
            id: 'ws_new',
            name: 'My Architecture',
            description: 'Description here',
            dsl: $dslContent,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->with('My Architecture', 'Description here')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('updateDsl')
            ->willReturn($workspace);

        $result = $this->tools->importFromDsl($dslContent);

        $this->assertEquals('My Architecture', $result['name']);
        $this->assertEquals('Description here', $result['description']);
    }

    /**
     * Test importFromDsl extracts workspace description correctly
     */
    public function testImportFromDslExtractsWorkspaceDescription(): void
    {
        $dslContent = 'workspace "System" "A comprehensive system architecture" { model { } }';

        $workspace = new Workspace(
            id: 'ws_desc',
            name: 'System',
            description: 'A comprehensive system architecture',
            dsl: $dslContent,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->with('System', 'A comprehensive system architecture')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('updateDsl')
            ->willReturn($workspace);

        $result = $this->tools->importFromDsl($dslContent);

        $this->assertEquals('A comprehensive system architecture', $result['description']);
    }

    /**
     * Test importFromDsl uses default name when not found in DSL
     */
    public function testImportFromDslUsesDefaultName(): void
    {
        $dslContent = 'model { person "User" }';

        $workspace = new Workspace(
            id: 'ws_default',
            name: 'Imported Workspace',
            description: '',
            dsl: $dslContent,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->with('Imported Workspace', '')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('updateDsl')
            ->willReturn($workspace);

        $result = $this->tools->importFromDsl($dslContent);

        $this->assertEquals('Imported Workspace', $result['name']);
    }

    /**
     * Test importFromDsl handles DSL with name but no description
     */
    public function testImportFromDslHandlesNoDescription(): void
    {
        $dslContent = 'workspace "Simple Workspace" { model { } }';

        $workspace = new Workspace(
            id: 'ws_nodesc',
            name: 'Simple Workspace',
            description: '',
            dsl: $dslContent,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->with('Simple Workspace', '')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('updateDsl')
            ->willReturn($workspace);

        $result = $this->tools->importFromDsl($dslContent);

        $this->assertEquals('Simple Workspace', $result['name']);
        $this->assertEquals('', $result['description']);
    }

    /**
     * Test importFromDsl wraps InvalidDslException in ToolCallException
     */
    public function testImportFromDslRethrowsInvalidDslException(): void
    {
        $dslContent = 'workspace "Test" { model { } }';

        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test',
            description: '',
            dsl: '',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('updateDsl')
            ->willThrowException(new InvalidDslException('Invalid DSL syntax'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Invalid DSL: Invalid DSL syntax');

        $this->tools->importFromDsl($dslContent);
    }

    /**
     * Test importFromDsl wraps generic exceptions as ToolCallException
     */
    public function testImportFromDslWrapsGenericExceptions(): void
    {
        $dslContent = 'workspace "Test" { model { } }';

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new \RuntimeException('Database error'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Failed to import workspace from DSL: Database error');

        $this->tools->importFromDsl($dslContent);
    }

    /**
     * Test exportToDsl wraps generic exceptions
     */
    public function testExportToDslWrapsGenericExceptions(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_error')
            ->willThrowException(new \RuntimeException('File system error'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Failed to export workspace to DSL: File system error');

        $this->tools->exportToDsl('ws_error');
    }

    /**
     * Test exportToPlantUml wraps generic exceptions
     */
    public function testExportToPlantUmlWrapsGenericExceptions(): void
    {
        $workspace = new Workspace(
            id: 'ws_error',
            name: 'Error Workspace',
            description: '',
            dsl: 'workspace "Test" {}',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->willReturn($workspace);

        $this->cliWrapper
            ->expects($this->once())
            ->method('export')
            ->willThrowException(new \RuntimeException('CLI execution failed'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Failed to export workspace to PlantUML: CLI execution failed');

        $this->tools->exportToPlantUml('ws_error');
    }

    /**
     * Test exportToMermaid wraps generic exceptions
     */
    public function testExportToMermaidWrapsGenericExceptions(): void
    {
        $workspace = new Workspace(
            id: 'ws_error',
            name: 'Error Workspace',
            description: '',
            dsl: 'workspace "Test" {}',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->willReturn($workspace);

        $this->cliWrapper
            ->expects($this->once())
            ->method('export')
            ->willThrowException(new \RuntimeException('Export process crashed'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Failed to export workspace to Mermaid: Export process crashed');

        $this->tools->exportToMermaid('ws_error');
    }

    /**
     * Data provider for DSL content with various formats
     */
    public static function dslContentProvider(): array
    {
        return [
            'standard format' => [
                'workspace "Standard" "Description" { model { } }',
                'Standard',
                'Description',
            ],
            'name only' => [
                'workspace "Name Only" { model { } }',
                'Name Only',
                '',
            ],
            'multiline format' => [
                'workspace "Multi Line"
                "Multi line description" {
                    model {
                        person "User"
                    }
                }',
                'Multi Line',
                'Multi line description',
            ],
            'no workspace directive' => [
                'model { person "User" }',
                'Imported Workspace',
                '',
            ],
        ];
    }

    /**
     * @dataProvider dslContentProvider
     */
    public function testImportFromDslWithVariousDslFormats(
        string $dslContent,
        string $expectedName,
        string $expectedDescription
    ): void {
        $workspace = new Workspace(
            id: 'ws_varied',
            name: $expectedName,
            description: $expectedDescription,
            dsl: $dslContent,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->with($expectedName, $expectedDescription)
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('updateDsl')
            ->willReturn($workspace);

        $result = $this->tools->importFromDsl($dslContent);

        $this->assertEquals($expectedName, $result['name']);
        $this->assertEquals($expectedDescription, $result['description']);
    }

    /**
     * Test that tools properly use the logger for all operations
     */
    public function testToolsUseLoggerForAllOperations(): void
    {
        $workspace = new Workspace(
            id: 'ws_log',
            name: 'Log Test',
            description: '',
            dsl: 'workspace "Test" {}',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        // Test exportToDsl logs debug
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->logger->expects($this->once())->method('debug')
            ->with('Exporting workspace to DSL: ws_log');
        $this->tools->exportToDsl('ws_log');

        // Reset for next test
        $this->setUp();

        // Test exportToPlantUml logs debug
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->cliWrapper->method('export')->willReturn('');
        $this->logger->expects($this->once())->method('debug')
            ->with('Exporting workspace to PlantUML: ws_log', ['viewKey' => null]);
        $this->tools->exportToPlantUml('ws_log');

        // Reset for next test
        $this->setUp();

        // Test exportToMermaid logs debug
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->cliWrapper->method('export')->willReturn('');
        $this->logger->expects($this->once())->method('debug')
            ->with('Exporting workspace to Mermaid: ws_log', ['viewKey' => null]);
        $this->tools->exportToMermaid('ws_log');

        // Reset for next test
        $this->setUp();

        // Test importFromDsl logs info
        $this->workspaceManager->method('create')->willReturn($workspace);
        $this->workspaceManager->method('updateDsl')->willReturn($workspace);
        $this->logger->expects($this->once())->method('info')
            ->with('Importing workspace from DSL');
        $this->tools->importFromDsl('workspace "Test" {}');
    }
}
