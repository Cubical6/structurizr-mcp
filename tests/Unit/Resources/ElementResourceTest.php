<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Resources;

use Mcp\Exception\ResourceNotFoundException;
use Mcp\Exception\ResourceReadException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Resources\ElementResource;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * Unit tests for ElementResource
 *
 * @covers \StructurizrMcp\Resources\ElementResource
 */
class ElementResourceTest extends TestCase
{
    private WorkspaceManager&MockObject $workspaceManager;
    private LoggerInterface&MockObject $logger;
    private ElementResource $resource;

    protected function setUp(): void
    {
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resource = new ElementResource($this->workspaceManager, $this->logger);
    }

    public function testGetElementPerson(): void
    {
        $model = [
            'people' => [
                ['id' => 'person1', 'name' => 'User', 'description' => 'A user of the system'],
                ['id' => 'person2', 'name' => 'Admin', 'description' => 'Administrator'],
            ],
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
            ->with('Retrieving element: person1 from workspace: ws_123');

        $result = $this->resource->getElement('ws_123', 'person1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('element', $result);
        $this->assertEquals('person1', $result['element']['id']);
        $this->assertEquals('User', $result['element']['name']);
    }

    public function testGetElementSoftwareSystem(): void
    {
        $model = [
            'softwareSystems' => [
                ['id' => 'sys1', 'name' => 'System 1', 'description' => 'First system'],
                ['id' => 'sys2', 'name' => 'System 2', 'description' => 'Second system'],
            ],
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

        $result = $this->resource->getElement('ws_123', 'sys1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('element', $result);
        $this->assertEquals('sys1', $result['element']['id']);
        $this->assertEquals('System 1', $result['element']['name']);
    }

    public function testGetElementContainer(): void
    {
        $model = [
            'softwareSystems' => [
                [
                    'id' => 'sys1',
                    'name' => 'System 1',
                    'containers' => [
                        ['id' => 'container1', 'name' => 'Web App', 'technology' => 'Spring Boot'],
                        ['id' => 'container2', 'name' => 'Database', 'technology' => 'PostgreSQL'],
                    ],
                ],
            ],
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

        $result = $this->resource->getElement('ws_123', 'container1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('element', $result);
        $this->assertEquals('container1', $result['element']['id']);
        $this->assertEquals('Web App', $result['element']['name']);
        $this->assertEquals('Spring Boot', $result['element']['technology']);
    }

    public function testGetElementComponent(): void
    {
        $model = [
            'softwareSystems' => [
                [
                    'id' => 'sys1',
                    'name' => 'System 1',
                    'containers' => [
                        [
                            'id' => 'container1',
                            'name' => 'Web App',
                            'components' => [
                                ['id' => 'component1', 'name' => 'Controller', 'technology' => 'Spring MVC'],
                                ['id' => 'component2', 'name' => 'Service', 'technology' => 'Spring'],
                            ],
                        ],
                    ],
                ],
            ],
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

        $result = $this->resource->getElement('ws_123', 'component1');

        $this->assertIsArray($result);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('element', $result);
        $this->assertEquals('component1', $result['element']['id']);
        $this->assertEquals('Controller', $result['element']['name']);
        $this->assertEquals('Spring MVC', $result['element']['technology']);
    }

    public function testGetElementNotFound(): void
    {
        $workspace = new Workspace(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test',
            model: ['people' => []],
            views: [],
            dsl: '',
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage("Element 'nonexistent' not found in workspace 'ws_123'");

        $this->resource->getElement('ws_123', 'nonexistent');
    }

    public function testGetElementWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->resource->getElement('nonexistent', 'element1');
    }

    public function testGetElementWithComplexModel(): void
    {
        $model = [
            'people' => [
                ['id' => 'person1', 'name' => 'User'],
            ],
            'softwareSystems' => [
                [
                    'id' => 'sys1',
                    'name' => 'System 1',
                    'containers' => [
                        [
                            'id' => 'container1',
                            'name' => 'Container 1',
                            'components' => [
                                ['id' => 'component1', 'name' => 'Component 1'],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'sys2',
                    'name' => 'System 2',
                ],
            ],
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

        // Should find the nested component
        $result = $this->resource->getElement('ws_123', 'component1');

        $this->assertIsArray($result);
        $this->assertEquals('component1', $result['element']['id']);
        $this->assertEquals('Component 1', $result['element']['name']);
    }

    public function testGetElementGeneralException(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_error')
            ->willThrowException(new \RuntimeException('Database error'));

        $this->expectException(ResourceReadException::class);
        $this->expectExceptionMessage("Failed to retrieve element 'element1': Database error");

        $this->resource->getElement('ws_error', 'element1');
    }
}
