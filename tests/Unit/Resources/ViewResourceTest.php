<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Resources;

use Mcp\Exception\ResourceNotFoundException;
use Mcp\Exception\ResourceReadException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Resources\ViewResource;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * Unit tests for ViewResource
 *
 * @covers \StructurizrMcp\Resources\ViewResource
 */
class ViewResourceTest extends TestCase
{
    private WorkspaceManager&MockObject $workspaceManager;
    private LoggerInterface&MockObject $logger;
    private ViewResource $resource;

    protected function setUp(): void
    {
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resource = new ViewResource($this->workspaceManager, $this->logger);
    }

    public function testGetViewSystemContext(): void
    {
        $views = [
            'systemContextViews' => [
                [
                    'key' => 'context1',
                    'softwareSystemId' => 'sys1',
                    'description' => 'System Context view',
                ],
            ],
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
            ->with('Retrieving view: context1 from workspace: ws_123');

        $result = $this->resource->getView('ws_123', 'context1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('view', $result);
        $this->assertEquals('context1', $result['view']['key']);
        $this->assertEquals('sys1', $result['view']['softwareSystemId']);
        $this->assertEquals('systemContextViews', $result['view']['type']);
    }

    public function testGetViewContainer(): void
    {
        $views = [
            'containerViews' => [
                [
                    'key' => 'container1',
                    'softwareSystemId' => 'sys1',
                    'description' => 'Container view',
                ],
            ],
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

        $result = $this->resource->getView('ws_123', 'container1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('view', $result);
        $this->assertEquals('container1', $result['view']['key']);
        $this->assertEquals('containerViews', $result['view']['type']);
    }

    public function testGetViewComponent(): void
    {
        $views = [
            'componentViews' => [
                [
                    'key' => 'component1',
                    'containerId' => 'container1',
                    'description' => 'Component view',
                ],
            ],
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

        $result = $this->resource->getView('ws_123', 'component1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('view', $result);
        $this->assertEquals('component1', $result['view']['key']);
        $this->assertEquals('componentViews', $result['view']['type']);
    }

    public function testGetViewDynamic(): void
    {
        $views = [
            'dynamicViews' => [
                [
                    'key' => 'dynamic1',
                    'elementId' => 'sys1',
                    'description' => 'Dynamic view',
                ],
            ],
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

        $result = $this->resource->getView('ws_123', 'dynamic1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('view', $result);
        $this->assertEquals('dynamic1', $result['view']['key']);
        $this->assertEquals('dynamicViews', $result['view']['type']);
    }

    public function testGetViewDeployment(): void
    {
        $views = [
            'deploymentViews' => [
                [
                    'key' => 'deployment1',
                    'environment' => 'Production',
                    'description' => 'Deployment view',
                ],
            ],
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

        $result = $this->resource->getView('ws_123', 'deployment1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('view', $result);
        $this->assertEquals('deployment1', $result['view']['key']);
        $this->assertEquals('deploymentViews', $result['view']['type']);
    }

    public function testGetViewSystemLandscape(): void
    {
        $views = [
            'systemLandscapeViews' => [
                [
                    'key' => 'landscape1',
                    'description' => 'System Landscape view',
                ],
            ],
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

        $result = $this->resource->getView('ws_123', 'landscape1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('view', $result);
        $this->assertEquals('landscape1', $result['view']['key']);
        $this->assertEquals('systemLandscapeViews', $result['view']['type']);
    }

    public function testGetViewNotFound(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test',
            model: [],
            views: ['systemContextViews' => []],
            dsl: '',
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage("View 'nonexistent' not found in workspace 'ws_123'");

        $this->resource->getView('ws_123', 'nonexistent');
    }

    public function testGetViewWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->resource->getView('nonexistent', 'view1');
    }

    public function testGetViewWithMultipleViewTypes(): void
    {
        $views = [
            'systemContextViews' => [
                ['key' => 'context1', 'description' => 'Context'],
            ],
            'containerViews' => [
                ['key' => 'container1', 'description' => 'Container'],
            ],
            'componentViews' => [
                ['key' => 'component1', 'description' => 'Component'],
            ],
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

        // Should find the container view among multiple view types
        $result = $this->resource->getView('ws_123', 'container1');

        $this->assertIsArray($result);
        $this->assertEquals('container1', $result['view']['key']);
        $this->assertEquals('containerViews', $result['view']['type']);
    }

    public function testGetViewGeneralException(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_error')
            ->willThrowException(new \RuntimeException('Database error'));

        $this->expectException(ResourceReadException::class);
        $this->expectExceptionMessage("Failed to retrieve view 'view1': Database error");

        $this->resource->getView('ws_error', 'view1');
    }
}
