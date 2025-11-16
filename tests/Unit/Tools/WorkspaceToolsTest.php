<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Tools;

use Mcp\Exception\ToolCallException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Tools\WorkspaceTools;

/**
 * Unit tests for WorkspaceTools
 *
 * @covers \StructurizrMcp\Tools\WorkspaceTools
 */
class WorkspaceToolsTest extends TestCase
{
    private WorkspaceManager&MockObject $workspaceManager;
    private LoggerInterface&MockObject $logger;
    private WorkspaceTools $tools;

    protected function setUp(): void
    {
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tools = new WorkspaceTools($this->workspaceManager, $this->logger);
    }

    public function testCreateWorkspace(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test description',
            dsl: '',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            updatedAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->with('Test Workspace', 'Test description')
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Creating workspace: Test Workspace');

        $result = $this->tools->createWorkspace('Test Workspace', 'Test description');

        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertEquals('Test Workspace', $result['name']);
        $this->assertEquals('Test description', $result['description']);
        $this->assertEquals('', $result['dsl']);
        $this->assertEquals('2024-01-01T12:00:00+00:00', $result['createdAt']);
    }

    public function testCreateWorkspaceWithEmptyDescription(): void
    {
        $workspace = new Workspace(
            id: 'ws_456',
            name: 'Minimal Workspace',
            description: '',
            dsl: '',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('create')
            ->with('Minimal Workspace', '')
            ->willReturn($workspace);

        $result = $this->tools->createWorkspace('Minimal Workspace');

        $this->assertEquals('ws_456', $result['workspaceId']);
        $this->assertEquals('', $result['description']);
    }

    public function testGetWorkspaceJson(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Description',
            model: ['elements' => ['person1']],
            views: ['view1'],
            dsl: 'workspace "Test" {}',
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
            ->with('Getting workspace: ws_123 in format: json');

        $result = $this->tools->getWorkspace('ws_123', 'json');

        $this->assertEquals('ws_123', $result['id']);
        $this->assertEquals('Test Workspace', $result['name']);
        $this->assertEquals(['elements' => ['person1']], $result['model']);
        $this->assertEquals(['view1'], $result['views']);
        $this->assertEquals('workspace "Test" {}', $result['dsl']);
    }

    public function testGetWorkspaceDsl(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Description',
            dsl: 'workspace "Test" { model { } }',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $result = $this->tools->getWorkspace('ws_123', 'dsl');

        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertEquals('Test Workspace', $result['name']);
        $this->assertEquals('workspace "Test" { model { } }', $result['dsl']);
        $this->assertArrayNotHasKey('model', $result);
        $this->assertArrayNotHasKey('views', $result);
    }

    public function testGetWorkspaceDefaultFormat(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test',
            description: '',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        // Default format should be 'json'
        $result = $this->tools->getWorkspace('ws_123');

        $this->assertArrayHasKey('model', $result);
        $this->assertArrayHasKey('views', $result);
    }

    public function testGetWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found');

        $this->tools->getWorkspace('nonexistent');
    }

    public function testListWorkspaces(): void
    {
        $workspaces = [
            [
                'id' => 'ws_1',
                'name' => 'Workspace 1',
                'description' => 'First',
                'createdAt' => '2024-01-01T00:00:00+00:00',
                'updatedAt' => '2024-01-01T00:00:00+00:00',
            ],
            [
                'id' => 'ws_2',
                'name' => 'Workspace 2',
                'description' => 'Second',
                'createdAt' => '2024-01-02T00:00:00+00:00',
                'updatedAt' => '2024-01-02T00:00:00+00:00',
            ],
        ];

        $this->workspaceManager
            ->expects($this->once())
            ->method('list')
            ->willReturn($workspaces);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Listing all workspaces');

        $result = $this->tools->listWorkspaces();

        $this->assertArrayHasKey('workspaces', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals($workspaces, $result['workspaces']);
    }

    public function testListWorkspacesEmpty(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('list')
            ->willReturn([]);

        $result = $this->tools->listWorkspaces();

        $this->assertEquals(0, $result['count']);
        $this->assertEmpty($result['workspaces']);
    }

    public function testDeleteWorkspace(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('delete')
            ->with('ws_123');

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Deleting workspace: ws_123');

        $result = $this->tools->deleteWorkspace('ws_123');

        $this->assertTrue($result['success']);
        $this->assertEquals('Workspace ws_123 deleted successfully', $result['message']);
        $this->assertEquals('ws_123', $result['workspaceId']);
    }

    public function testDeleteWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('delete')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $result = $this->tools->deleteWorkspace('nonexistent');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Workspace not found', $result['message']);
        $this->assertEquals('nonexistent', $result['workspaceId']);
    }

    /**
     * Note: Export functionality is in ExportTools, not WorkspaceTools.
     * See ExportToolsTest for export-related tests.
     */


    /**
     * Test that all methods properly use the logger
     */
    public function testToolsUseLogger(): void
    {
        $workspace = new Workspace(
            id: 'ws_log',
            name: 'Log Test',
            description: '',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->workspaceManager->method('create')->willReturn($workspace);
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->method('list')->willReturn([]);

        // Test create logs
        $this->logger->expects($this->exactly(1))->method('info');
        $this->tools->createWorkspace('Log Test');

        // Reset mock
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tools = new WorkspaceTools($this->workspaceManager, $this->logger);

        // Test get logs
        $this->logger->expects($this->exactly(1))->method('debug');
        $this->tools->getWorkspace('ws_log');

        // Reset mock
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tools = new WorkspaceTools($this->workspaceManager, $this->logger);

        // Test list logs
        $this->logger->expects($this->exactly(1))->method('debug');
        $this->tools->listWorkspaces();
    }
}
