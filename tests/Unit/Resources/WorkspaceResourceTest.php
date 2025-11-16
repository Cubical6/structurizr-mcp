<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Resources;

use Mcp\Exception\ResourceNotFoundException;
use Mcp\Exception\ResourceReadException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Resources\WorkspaceResource;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * Unit tests for WorkspaceResource
 *
 * @covers \StructurizrMcp\Resources\WorkspaceResource
 */
class WorkspaceResourceTest extends TestCase
{
    private WorkspaceManager&MockObject $workspaceManager;
    private LoggerInterface&MockObject $logger;
    private WorkspaceResource $resource;

    protected function setUp(): void
    {
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resource = new WorkspaceResource($this->workspaceManager, $this->logger);
    }

    public function testGetWorkspace(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test description',
            model: ['people' => []],
            views: ['systemContextViews' => []],
            dsl: 'workspace "Test" {}',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            updatedAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Retrieving workspace resource: ws_123');

        $result = $this->resource->getWorkspace('ws_123');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['id']);
        $this->assertEquals('Test Workspace', $result['name']);
        $this->assertEquals('Test description', $result['description']);
        $this->assertArrayHasKey('model', $result);
        $this->assertArrayHasKey('views', $result);
        $this->assertArrayHasKey('dsl', $result);
    }

    public function testGetWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->resource->getWorkspace('nonexistent');
    }

    public function testGetWorkspaceGeneralException(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_error')
            ->willThrowException(new \RuntimeException('Database error'));

        $this->expectException(ResourceReadException::class);
        $this->expectExceptionMessage("Failed to retrieve workspace 'ws_error': Database error");

        $this->resource->getWorkspace('ws_error');
    }

    public function testGetModel(): void
    {
        $model = [
            'people' => [['id' => 'person1', 'name' => 'User']],
            'softwareSystems' => [['id' => 'sys1', 'name' => 'System']],
        ];

        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test',
            model: $model,
            views: [],
            dsl: '',
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Retrieving workspace model: ws_123');

        $result = $this->resource->getModel('ws_123');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertEquals('Test Workspace', $result['name']);
        $this->assertArrayHasKey('model', $result);
        $this->assertEquals($model, $result['model']);
    }

    public function testGetModelWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->resource->getModel('nonexistent');
    }

    public function testGetViews(): void
    {
        $views = [
            'systemContextViews' => [['key' => 'context1']],
            'containerViews' => [['key' => 'container1']],
        ];

        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test',
            model: [],
            views: $views,
            dsl: '',
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Retrieving workspace views: ws_123');

        $result = $this->resource->getViews('ws_123');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertEquals('Test Workspace', $result['name']);
        $this->assertArrayHasKey('views', $result);
        $this->assertEquals($views, $result['views']);
    }

    public function testGetViewsWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->resource->getViews('nonexistent');
    }

    public function testGetDslWithExistingDsl(): void
    {
        $dsl = 'workspace "Test Workspace" {\n    model {\n    }\n}';

        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test',
            model: [],
            views: [],
            dsl: $dsl,
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Retrieving workspace DSL: ws_123');

        $result = $this->resource->getDsl('ws_123');

        $this->assertIsString($result);
        $this->assertEquals($dsl, $result);
    }

    public function testGetDslWithoutExistingDsl(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test description',
            model: [],
            views: [],
            dsl: '',
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $result = $this->resource->getDsl('ws_123');

        $this->assertIsString($result);
        $this->assertStringContainsString('workspace "Test Workspace"', $result);
        $this->assertStringContainsString('Test description', $result);
    }

    public function testGetDslWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->resource->getDsl('nonexistent');
    }
}
