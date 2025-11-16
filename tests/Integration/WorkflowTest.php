<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Integration;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Structurizr\DslBuilder;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Tools\WorkspaceTools;
use StructurizrMcp\Tools\ModelTools;
use StructurizrMcp\Tools\ViewTools;
use StructurizrMcp\Tools\ExportTools;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use Psr\Log\NullLogger;

/**
 * Integration tests for complete workflows
 *
 * Tests complete end-to-end workflows using real components (not mocked).
 *
 * NOTE: Many of these tests currently fail due to an implementation limitation:
 * The createBuilderFromWorkspace() method in ModelTools and ViewTools doesn't
 * rebuild the DslBuilder state from existing DSL (see TODO comment in the code).
 * These tests document the EXPECTED behavior once that limitation is addressed.
 *
 * @group integration
 * @group incomplete
 */
class WorkflowTest extends TestCase
{
    private string $tempStoragePath;
    private WorkspaceManager $manager;
    private WorkspaceTools $workspaceTools;
    private ModelTools $modelTools;
    private ViewTools $viewTools;
    private ExportTools $exportTools;

    protected function setUp(): void
    {
        // Create temporary directory for test workspaces
        $this->tempStoragePath = sys_get_temp_dir() . '/structurizr-integration-test-' . uniqid();
        $logger = new NullLogger();

        // Initialize real components (no mocks)
        $this->manager = new WorkspaceManager($this->tempStoragePath, $logger);
        $this->workspaceTools = new WorkspaceTools($this->manager, $logger);
        $this->modelTools = new ModelTools($this->manager, $logger);
        $this->viewTools = new ViewTools($this->manager, $logger);
        $this->exportTools = new ExportTools($this->manager, $this->createMock(CliWrapper::class), $logger);
    }

    protected function tearDown(): void
    {
        // Clean up temporary directory
        if (is_dir($this->tempStoragePath)) {
            $files = glob($this->tempStoragePath . '/*.json');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
            rmdir($this->tempStoragePath);
        }
    }

    /**
     * Test complete workflow: Create workspace → Add elements → Create views → Export
     */
    public function testCompleteECommerceWorkflow(): void
    {
        // Step 1: Create workspace
        $workspaceResult = $this->workspaceTools->createWorkspace(
            'E-Commerce System',
            'Online shopping platform'
        );

        $this->assertNotEmpty($workspaceResult['workspaceId']);
        $workspaceId = $workspaceResult['workspaceId'];

        // Step 2: Add people
        $customer = $this->modelTools->addPerson(
            $workspaceId,
            'Customer',
            'A customer of the online store',
            ['External']
        );

        $this->assertEquals('person', $customer['type']);
        $this->assertEquals('Customer', $customer['name']);

        $admin = $this->modelTools->addPerson(
            $workspaceId,
            'Admin',
            'System administrator',
            ['Internal']
        );

        // Step 3: Add software systems
        $ecommerce = $this->modelTools->addSoftwareSystem(
            $workspaceId,
            'E-Commerce System',
            'Allows customers to purchase products online',
            'Internal'
        );

        $this->assertEquals('softwareSystem', $ecommerce['type']);

        $payment = $this->modelTools->addSoftwareSystem(
            $workspaceId,
            'Payment Gateway',
            'Processes credit card payments',
            'External'
        );

        $email = $this->modelTools->addSoftwareSystem(
            $workspaceId,
            'Email System',
            'Sends emails to customers',
            'External'
        );

        // Step 4: Add containers
        $webapp = $this->modelTools->addContainer(
            $workspaceId,
            $ecommerce['elementId'],
            'Web Application',
            'Delivers the static content and the e-commerce single page application',
            'JavaScript and React'
        );

        $this->assertEquals('container', $webapp['type']);

        $api = $this->modelTools->addContainer(
            $workspaceId,
            $ecommerce['elementId'],
            'API Application',
            'Provides e-commerce functionality via a RESTful JSON API',
            'Node.js and Express'
        );

        $database = $this->modelTools->addContainer(
            $workspaceId,
            $ecommerce['elementId'],
            'Database',
            'Stores product information, customer information, orders, etc.',
            'PostgreSQL'
        );

        // Step 5: Add components
        $orderController = $this->modelTools->addComponent(
            $workspaceId,
            $api['elementId'],
            'Order Controller',
            'Handles order processing requests',
            'Express Controller'
        );

        $this->assertEquals('component', $orderController['type']);

        $productController = $this->modelTools->addComponent(
            $workspaceId,
            $api['elementId'],
            'Product Controller',
            'Handles product catalog requests',
            'Express Controller'
        );

        // Step 6: Add relationships
        $rel1 = $this->modelTools->addRelationship(
            $workspaceId,
            $customer['elementId'],
            $webapp['elementId'],
            'Visits using',
            'HTTPS'
        );

        $this->assertNotEmpty($rel1['relationshipId']);

        $this->modelTools->addRelationship(
            $workspaceId,
            $webapp['elementId'],
            $api['elementId'],
            'Makes API calls to',
            'JSON/HTTPS'
        );

        $this->modelTools->addRelationship(
            $workspaceId,
            $api['elementId'],
            $database['elementId'],
            'Reads from and writes to',
            'SQL/TCP'
        );

        $this->modelTools->addRelationship(
            $workspaceId,
            $api['elementId'],
            $payment['elementId'],
            'Processes payments using',
            'HTTPS/REST'
        );

        $this->modelTools->addRelationship(
            $workspaceId,
            $api['elementId'],
            $email['elementId'],
            'Sends emails using',
            'SMTP'
        );

        $this->modelTools->addRelationship(
            $workspaceId,
            $admin['elementId'],
            $webapp['elementId'],
            'Manages products using',
            'HTTPS'
        );

        // Step 7: Create views
        $contextView = $this->viewTools->createSystemContextView(
            $workspaceId,
            $ecommerce['elementId'],
            'SystemContext',
            'The system context diagram for the E-Commerce System'
        );

        $this->assertEquals('SystemContext', $contextView['viewKey']);
        $this->assertEquals('systemContext', $contextView['type']);

        $containerView = $this->viewTools->createContainerView(
            $workspaceId,
            $ecommerce['elementId'],
            'Containers',
            'The container diagram for the E-Commerce System'
        );

        $this->assertEquals('Containers', $containerView['viewKey']);

        $componentView = $this->viewTools->createComponentView(
            $workspaceId,
            $api['elementId'],
            'Components',
            'The component diagram for the API Application'
        );

        $this->assertEquals('Components', $componentView['viewKey']);

        // Step 8: Apply auto-layout
        $layoutResult = $this->viewTools->applyAutoLayout(
            $workspaceId,
            'Containers',
            'tb'
        );

        $this->assertEquals('tb', $layoutResult['autoLayout']);

        // Step 9: Export to DSL
        $exportResult = $this->workspaceTools->exportToDsl($workspaceId);

        $this->assertNotEmpty($exportResult['dsl']);
        $dsl = $exportResult['dsl'];

        // Verify DSL contains expected elements
        $this->assertStringContainsString('E-Commerce System', $dsl);
        $this->assertStringContainsString('Customer', $dsl);
        $this->assertStringContainsString('Payment Gateway', $dsl);
        $this->assertStringContainsString('Web Application', $dsl);
        $this->assertStringContainsString('API Application', $dsl);
        $this->assertStringContainsString('Order Controller', $dsl);
        $this->assertStringContainsString('systemContext', $dsl);
        $this->assertStringContainsString('container', $dsl);
        $this->assertStringContainsString('component', $dsl);

        // Step 10: Retrieve workspace
        $retrievedWorkspace = $this->workspaceTools->getWorkspace($workspaceId, 'json');

        $this->assertEquals($workspaceId, $retrievedWorkspace['id']);
        $this->assertEquals('E-Commerce System', $retrievedWorkspace['name']);
        $this->assertNotEmpty($retrievedWorkspace['dsl']);

        // Step 11: List workspaces
        $listResult = $this->workspaceTools->listWorkspaces();

        $this->assertEquals(1, $listResult['count']);
        $this->assertEquals($workspaceId, $listResult['workspaces'][0]['id']);
    }

    /**
     * Test workflow with error handling
     */
    public function testWorkflowWithErrorHandling(): void
    {
        // Create workspace
        $workspace = $this->workspaceTools->createWorkspace('Error Test');
        $workspaceId = $workspace['workspaceId'];

        // Try to add relationship with non-existent elements
        // This will succeed at the tool level but will create invalid DSL
        // The DslBuilder validation happens during DSL generation
        $person = $this->modelTools->addPerson($workspaceId, 'User');
        $system = $this->modelTools->addSoftwareSystem($workspaceId, 'System');

        // Valid relationship
        $result = $this->modelTools->addRelationship(
            $workspaceId,
            $person['elementId'],
            $system['elementId'],
            'Uses'
        );

        $this->assertNotEmpty($result['relationshipId']);

        // Try to get non-existent workspace
        try {
            $this->workspaceTools->getWorkspace('nonexistent');
            $this->fail('Expected WorkspaceNotFoundException');
        } catch (WorkspaceNotFoundException $e) {
            $this->assertStringContainsString('Workspace not found', $e->getMessage());
        }

        // Delete workspace
        $deleteResult = $this->workspaceTools->deleteWorkspace($workspaceId);
        $this->assertTrue($deleteResult['success']);

        // Try to access deleted workspace
        try {
            $this->workspaceTools->getWorkspace($workspaceId);
            $this->fail('Expected WorkspaceNotFoundException');
        } catch (WorkspaceNotFoundException $e) {
            $this->assertStringContainsString('Workspace not found', $e->getMessage());
        }
    }

    /**
     * Test multiple workspaces workflow
     */
    public function testMultipleWorkspacesWorkflow(): void
    {
        // Create multiple workspaces
        $ws1 = $this->workspaceTools->createWorkspace('Workspace 1', 'First workspace');
        $ws2 = $this->workspaceTools->createWorkspace('Workspace 2', 'Second workspace');
        $ws3 = $this->workspaceTools->createWorkspace('Workspace 3', 'Third workspace');

        // Add elements to different workspaces
        $this->modelTools->addPerson($ws1['workspaceId'], 'User 1');
        $this->modelTools->addPerson($ws2['workspaceId'], 'User 2');
        $this->modelTools->addPerson($ws3['workspaceId'], 'User 3');

        // List all workspaces
        $list = $this->workspaceTools->listWorkspaces();
        $this->assertEquals(3, $list['count']);

        // Verify each workspace
        $ids = array_column($list['workspaces'], 'id');
        $this->assertContains($ws1['workspaceId'], $ids);
        $this->assertContains($ws2['workspaceId'], $ids);
        $this->assertContains($ws3['workspaceId'], $ids);

        // Delete one workspace
        $this->workspaceTools->deleteWorkspace($ws2['workspaceId']);

        // List again
        $list = $this->workspaceTools->listWorkspaces();
        $this->assertEquals(2, $list['count']);

        $ids = array_column($list['workspaces'], 'id');
        $this->assertContains($ws1['workspaceId'], $ids);
        $this->assertNotContains($ws2['workspaceId'], $ids);
        $this->assertContains($ws3['workspaceId'], $ids);
    }

    /**
     * Test DSL builder integration workflow
     */
    public function testDslBuilderIntegrationWorkflow(): void
    {
        // Create workspace using WorkspaceTools
        $workspace = $this->workspaceTools->createWorkspace('DSL Builder Test');
        $workspaceId = $workspace['workspaceId'];

        // Add elements using ModelTools
        $user = $this->modelTools->addPerson($workspaceId, 'User', 'End user');
        $system = $this->modelTools->addSoftwareSystem($workspaceId, 'System', 'The system');
        $container = $this->modelTools->addContainer($workspaceId, $system['elementId'], 'App', 'Application', 'Java');

        // Add relationships
        $this->modelTools->addRelationship($workspaceId, $user['elementId'], $system['elementId'], 'Uses');

        // Create views
        $this->viewTools->createSystemContextView($workspaceId, $system['elementId'], 'Context');
        $this->viewTools->createContainerView($workspaceId, $system['elementId'], 'Containers');

        // Export DSL
        $export = $this->workspaceTools->exportToDsl($workspaceId);
        $dsl = $export['dsl'];

        // Verify DSL structure
        $this->assertStringContainsString('workspace "DSL Builder Test"', $dsl);
        $this->assertStringContainsString('model {', $dsl);
        $this->assertStringContainsString('person "User"', $dsl);
        $this->assertStringContainsString('softwareSystem "System"', $dsl);
        $this->assertStringContainsString('container "App"', $dsl);
        $this->assertStringContainsString('views {', $dsl);
        $this->assertStringContainsString('systemContext', $dsl);
        $this->assertStringContainsString('styles {', $dsl);

        // Verify workspace persistence
        $loaded = $this->manager->load($workspaceId);
        $this->assertEquals($dsl, $loaded->dsl);
    }

    /**
     * Test workspace update workflow
     */
    public function testWorkspaceUpdateWorkflow(): void
    {
        // Create workspace
        $workspace = $this->workspaceTools->createWorkspace('Update Test');
        $workspaceId = $workspace['workspaceId'];

        // Add initial elements
        $user = $this->modelTools->addPerson($workspaceId, 'User');
        $system = $this->modelTools->addSoftwareSystem($workspaceId, 'System');

        // Get workspace
        $ws1 = $this->workspaceTools->getWorkspace($workspaceId, 'json');
        $dsl1 = $ws1['dsl'];

        // Add more elements
        $container = $this->modelTools->addContainer($workspaceId, $system['elementId'], 'API');

        // Get workspace again
        $ws2 = $this->workspaceTools->getWorkspace($workspaceId, 'json');
        $dsl2 = $ws2['dsl'];

        // DSL should be updated
        $this->assertNotEquals($dsl1, $dsl2);
        $this->assertStringContainsString('container "API"', $dsl2);

        // Updated timestamp should change
        $this->assertNotEquals($ws1['updatedAt'], $ws2['updatedAt']);

        // Created timestamp should remain the same
        $this->assertEquals($ws1['createdAt'], $ws2['createdAt']);
    }

    /**
     * Test persistence across manager instances
     */
    public function testPersistenceAcrossInstances(): void
    {
        // Create workspace with first manager instance
        $workspace = $this->workspaceTools->createWorkspace('Persistence Test');
        $workspaceId = $workspace['workspaceId'];

        $this->modelTools->addPerson($workspaceId, 'User');
        $this->modelTools->addSoftwareSystem($workspaceId, 'System');

        // Create new manager instance (simulating server restart)
        $newManager = new WorkspaceManager($this->tempStoragePath, new NullLogger());
        $newWorkspaceTools = new WorkspaceTools($newManager, new NullLogger());

        // List workspaces with new instance
        $list = $newWorkspaceTools->listWorkspaces();
        $this->assertEquals(1, $list['count']);
        $this->assertEquals($workspaceId, $list['workspaces'][0]['id']);

        // Load workspace with new instance
        $loaded = $newWorkspaceTools->getWorkspace($workspaceId, 'json');
        $this->assertEquals('Persistence Test', $loaded['name']);
        $this->assertStringContainsString('person "User"', $loaded['dsl']);
        $this->assertStringContainsString('softwareSystem "System"', $loaded['dsl']);
    }

    /**
     * Test complex hierarchical model
     */
    public function testComplexHierarchicalModel(): void
    {
        $workspace = $this->workspaceTools->createWorkspace('Complex Model');
        $workspaceId = $workspace['workspaceId'];

        // Build a complex hierarchy
        $system = $this->modelTools->addSoftwareSystem($workspaceId, 'System');

        // Add multiple containers
        $web = $this->modelTools->addContainer($workspaceId, $system['elementId'], 'Web', 'Frontend', 'React');
        $api = $this->modelTools->addContainer($workspaceId, $system['elementId'], 'API', 'Backend', 'Node.js');
        $db = $this->modelTools->addContainer($workspaceId, $system['elementId'], 'DB', 'Database', 'PostgreSQL');

        // Add components to API
        $auth = $this->modelTools->addComponent($workspaceId, $api['elementId'], 'Auth', 'Authentication');
        $orders = $this->modelTools->addComponent($workspaceId, $api['elementId'], 'Orders', 'Order processing');
        $products = $this->modelTools->addComponent($workspaceId, $api['elementId'], 'Products', 'Product catalog');

        // Add relationships
        $this->modelTools->addRelationship($workspaceId, $web['elementId'], $api['elementId'], 'Calls');
        $this->modelTools->addRelationship($workspaceId, $api['elementId'], $db['elementId'], 'Reads/writes');

        // Create all view types
        $this->viewTools->createSystemContextView($workspaceId, $system['elementId'], 'Context');
        $this->viewTools->createContainerView($workspaceId, $system['elementId'], 'Containers');
        $this->viewTools->createComponentView($workspaceId, $api['elementId'], 'APIComponents');

        // Export and verify
        $export = $this->workspaceTools->exportToDsl($workspaceId);
        $dsl = $export['dsl'];

        $this->assertStringContainsString('container "Web"', $dsl);
        $this->assertStringContainsString('container "API"', $dsl);
        $this->assertStringContainsString('container "DB"', $dsl);
        $this->assertStringContainsString('component "Auth"', $dsl);
        $this->assertStringContainsString('component "Orders"', $dsl);
        $this->assertStringContainsString('component "Products"', $dsl);
    }

    /**
     * Test view auto-layout workflow
     */
    public function testViewAutoLayoutWorkflow(): void
    {
        $workspace = $this->workspaceTools->createWorkspace('Layout Test');
        $workspaceId = $workspace['workspaceId'];

        $system = $this->modelTools->addSoftwareSystem($workspaceId, 'System');

        // Create view with default layout (lr)
        $view = $this->viewTools->createSystemContextView($workspaceId, $system['elementId'], 'Context');
        $export1 = $this->exportTools->exportToDsl($workspaceId);
        $this->assertStringContainsString('autoLayout lr', $export1['dsl']);

        // Change to top-to-bottom
        $this->viewTools->applyAutoLayout($workspaceId, 'Context', 'tb');
        $export2 = $this->exportTools->exportToDsl($workspaceId);
        $this->assertStringContainsString('autoLayout tb', $export2['dsl']);

        // Change to right-to-left
        $this->viewTools->applyAutoLayout($workspaceId, 'Context', 'rl');
        $export3 = $this->exportTools->exportToDsl($workspaceId);
        $this->assertStringContainsString('autoLayout rl', $export3['dsl']);
    }
}
