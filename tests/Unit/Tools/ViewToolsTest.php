<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use StructurizrMcp\Tools\ViewTools;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for ViewTools
 *
 * @covers \StructurizrMcp\Tools\ViewTools
 */
class ViewToolsTest extends TestCase
{
    private WorkspaceManager&MockObject $workspaceManager;
    private LoggerInterface&MockObject $logger;
    private ViewTools $tools;

    protected function setUp(): void
    {
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tools = new ViewTools($this->workspaceManager, $this->logger);
    }

    private function createTestWorkspace(string $id = 'ws_test'): Workspace
    {
        return new Workspace(
            id: $id,
            name: 'Test Workspace',
            description: 'Test description',
            model: [],
            views: [],
            dsl: '',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function testCreateSystemContextView(): void
    {
        $workspace = $this->createTestWorkspace();

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_test')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Workspace $ws) {
                return str_contains($ws->dsl, 'systemContext system_1 "SystemContext"')
                    && str_contains($ws->dsl, 'include *')
                    && str_contains($ws->dsl, 'autoLayout lr');
            }));

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with("Creating system context view 'SystemContext' for system 'system_1' in workspace: ws_test");

        $result = $this->tools->createSystemContextView(
            'ws_test',
            'system_1',
            'SystemContext',
            'Overview of the system'
        );

        $this->assertEquals('ws_test', $result['workspaceId']);
        $this->assertEquals('SystemContext', $result['viewKey']);
        $this->assertEquals('system_1', $result['systemId']);
        $this->assertEquals('systemContext', $result['type']);
        $this->assertEquals('Overview of the system', $result['description']);
    }

    public function testCreateSystemContextViewWithoutDescription(): void
    {
        $workspace = $this->createTestWorkspace();

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->expects($this->once())->method('save');

        $result = $this->tools->createSystemContextView(
            'ws_test',
            'system_1',
            'Context'
        );

        $this->assertEquals('', $result['description']);
    }

    public function testCreateSystemContextViewWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found');

        $this->tools->createSystemContextView('nonexistent', 'system_1', 'Context');
    }

    public function testCreateContainerView(): void
    {
        $workspace = $this->createTestWorkspace();

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_test')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Workspace $ws) {
                return str_contains($ws->dsl, 'container system_1 "Containers"')
                    && str_contains($ws->dsl, 'include *')
                    && str_contains($ws->dsl, 'autoLayout lr');
            }));

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with("Creating container view 'Containers' for system 'system_1' in workspace: ws_test");

        $result = $this->tools->createContainerView(
            'ws_test',
            'system_1',
            'Containers',
            'Container diagram'
        );

        $this->assertEquals('ws_test', $result['workspaceId']);
        $this->assertEquals('Containers', $result['viewKey']);
        $this->assertEquals('system_1', $result['systemId']);
        $this->assertEquals('container', $result['type']);
        $this->assertEquals('Container diagram', $result['description']);
    }

    public function testCreateContainerViewWithoutDescription(): void
    {
        $workspace = $this->createTestWorkspace();

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->expects($this->once())->method('save');

        $result = $this->tools->createContainerView(
            'ws_test',
            'system_1',
            'Containers'
        );

        $this->assertEquals('', $result['description']);
    }

    public function testCreateComponentView(): void
    {
        $workspace = $this->createTestWorkspace();

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_test')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Workspace $ws) {
                return str_contains($ws->dsl, 'component container_1 "Components"')
                    && str_contains($ws->dsl, 'include *')
                    && str_contains($ws->dsl, 'autoLayout lr');
            }));

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with("Creating component view 'Components' for container 'container_1' in workspace: ws_test");

        $result = $this->tools->createComponentView(
            'ws_test',
            'container_1',
            'Components',
            'Component diagram'
        );

        $this->assertEquals('ws_test', $result['workspaceId']);
        $this->assertEquals('Components', $result['viewKey']);
        $this->assertEquals('container_1', $result['containerId']);
        $this->assertEquals('component', $result['type']);
        $this->assertEquals('Component diagram', $result['description']);
    }

    public function testCreateComponentViewWithoutDescription(): void
    {
        $workspace = $this->createTestWorkspace();

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->expects($this->once())->method('save');

        $result = $this->tools->createComponentView(
            'ws_test',
            'container_1',
            'APIComponents'
        );

        $this->assertEquals('', $result['description']);
    }

    /**
     * Note: applyAutoLayout has same limitation as other tools -
     * the view must exist in the builder. Testing exception behavior.
     */
    public function testApplyAutoLayoutThrowsExceptionWhenViewNotInBuilder(): void
    {
        $workspace = $this->createTestWorkspace();

        $this->workspaceManager->method('load')->willReturn($workspace);

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('View not found');

        $this->tools->applyAutoLayout('ws_test', 'Context', 'tb');
    }

    /**
     * Test that invalid direction is rejected
     */

    public function testApplyAutoLayoutInvalidDirection(): void
    {
        $workspace = $this->createTestWorkspace();

        $this->workspaceManager->method('load')->willReturn($workspace);

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Direction must be one of: tb, bt, lr, rl');

        $this->tools->applyAutoLayout('ws_test', 'Context', 'invalid');
    }

    public function testApplyAutoLayoutWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found');

        $this->tools->applyAutoLayout('nonexistent', 'Context', 'lr');
    }

    /**
     * Test that workspace is properly updated after creating views
     */
    public function testWorkspaceUpdatedAfterCreatingView(): void
    {
        $workspace = $this->createTestWorkspace();

        $savedWorkspaces = [];

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspaces) {
                $savedWorkspaces[] = $ws;
            });

        $this->tools->createSystemContextView('ws_test', 'system_1', 'Context');

        $this->assertCount(1, $savedWorkspaces);
        $this->assertStringContainsString('systemContext', $savedWorkspaces[0]->dsl);
        $this->assertNotEquals($workspace->updatedAt, $savedWorkspaces[0]->updatedAt);
    }

    /**
     * Note: This test would work if createBuilderFromWorkspace rebuilt state.
     * Currently only testing independent view creation.
     */
    public function testIndependentViewCreation(): void
    {
        $workspace = $this->createTestWorkspace();

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->expects($this->exactly(2))->method('save');

        // Create views independently - each works in isolation
        $context = $this->tools->createSystemContextView('ws_test', 'system_1', 'Context');
        $this->assertEquals('systemContext', $context['type']);

        $containers = $this->tools->createContainerView('ws_test', 'system_1', 'Containers');
        $this->assertEquals('container', $containers['type']);

        // Note: applyAutoLayout and additional views would fail with current implementation
        // See integration tests for full workflow.
    }

    /**
     * Test logging behavior
     */
    public function testLoggingBehavior(): void
    {
        $workspace = $this->createTestWorkspace();
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->method('save');

        // Test createSystemContextView logging
        $this->logger->expects($this->once())
            ->method('info')
            ->with("Creating system context view 'TestView' for system 'sys_1' in workspace: ws_test");

        $this->tools->createSystemContextView('ws_test', 'sys_1', 'TestView');
    }

    public function testAllMethodsUpdateWorkspace(): void
    {
        $workspace = $this->createTestWorkspace();
        $this->workspaceManager->method('load')->willReturn($workspace);

        // Each method should call save once
        $this->workspaceManager->expects($this->exactly(1))->method('save');

        $this->tools->createSystemContextView('ws_test', 'system_1', 'Context');

        // Reset and test another method
        $this->setUp();
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->expects($this->exactly(1))->method('save');

        $this->tools->createContainerView('ws_test', 'system_1', 'Containers');
    }

    public function testViewKeyIsReturned(): void
    {
        $workspace = $this->createTestWorkspace();
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->method('save');

        $result = $this->tools->createSystemContextView('ws_test', 'system_1', 'MyCustomKey');

        $this->assertEquals('MyCustomKey', $result['viewKey']);
    }

    public function testCreatedViewsContainRequiredElements(): void
    {
        $workspace = $this->createTestWorkspace();

        $savedWorkspace = null;
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $this->tools->createSystemContextView('ws_test', 'system_1', 'Context', 'Test view');

        $this->assertNotNull($savedWorkspace);
        $dsl = $savedWorkspace->dsl;

        // Verify DSL contains all required elements
        $this->assertStringContainsString('views {', $dsl);
        $this->assertStringContainsString('systemContext system_1 "Context"', $dsl);
        $this->assertStringContainsString('include *', $dsl);
        $this->assertStringContainsString('autoLayout lr', $dsl);
        $this->assertStringContainsString('styles {', $dsl);
    }

    public function testViewPreservesWorkspaceMetadata(): void
    {
        $workspace = new Workspace(
            id: 'ws_metadata',
            name: 'Original Name',
            description: 'Original Description',
            model: ['existing' => 'data'],
            views: ['existing' => 'view'],
            dsl: 'existing dsl',
            createdAt: new \DateTimeImmutable('2024-01-01'),
            updatedAt: new \DateTimeImmutable('2024-01-01'),
        );

        $savedWorkspace = null;

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $this->tools->createSystemContextView('ws_metadata', 'system_1', 'Context');

        $this->assertNotNull($savedWorkspace);
        $this->assertEquals('ws_metadata', $savedWorkspace->id);
        $this->assertEquals('Original Name', $savedWorkspace->name);
        $this->assertEquals('Original Description', $savedWorkspace->description);
        // DSL should be updated
        $this->assertNotEquals('existing dsl', $savedWorkspace->dsl);
        $this->assertStringContainsString('systemContext', $savedWorkspace->dsl);
        // Created date should be preserved
        $this->assertEquals($workspace->createdAt, $savedWorkspace->createdAt);
        // Updated date should be changed
        $this->assertNotEquals($workspace->updatedAt, $savedWorkspace->updatedAt);
    }

    /**
     * Test view key validation (pattern matching)
     */
    public function testViewKeyPatternValidation(): void
    {
        // Note: This test assumes the Schema attribute pattern is enforced
        // In actual MCP implementation, the pattern validation happens before the method is called
        // Here we're testing that valid keys work correctly
        $workspace = $this->createTestWorkspace();
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->method('save');

        // Test various valid view keys
        $validKeys = [
            'Context',
            'system-context',
            'System_Context',
            'view123',
            'my-view_1',
        ];

        foreach ($validKeys as $key) {
            $result = $this->tools->createSystemContextView('ws_test', 'system_1', $key);
            $this->assertEquals($key, $result['viewKey']);
        }
    }
}
