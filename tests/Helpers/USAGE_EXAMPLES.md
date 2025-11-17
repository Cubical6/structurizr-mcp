# Test Helper Traits Usage Examples

This document provides comprehensive examples of using the `ContainerTestTrait` and `ServerTestTrait` helper traits in your test classes.

## ContainerTestTrait Examples

### Example 1: Basic Usage for Unit Tests

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;
use StructurizrMcp\Tools\WorkspaceTools;

class MyToolTest extends TestCase
{
    use ContainerTestTrait;

    private WorkspaceTools $tools;

    protected function setUp(): void
    {
        // Create test dependencies using the trait
        $logger = $this->createTestLogger();
        $manager = $this->createTestWorkspaceManager($logger);

        // Initialize the tool being tested
        $this->tools = new WorkspaceTools($manager, $logger);
    }

    protected function tearDown(): void
    {
        // Clean up environment variables
        $this->resetTestEnvironment();
    }

    public function testCreateWorkspace(): void
    {
        // Test implementation
        $result = $this->tools->createWorkspace('Test', 'Description');
        $this->assertArrayHasKey('workspaceId', $result);
    }
}
```

### Example 2: Testing with Log Verification

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;
use StructurizrMcp\Structurizr\WorkspaceManager;

class WorkspaceManagerTest extends TestCase
{
    use ContainerTestTrait;

    public function testWorkspaceCreationLogsMessage(): void
    {
        // Create logger with handler to capture log messages
        [$logger, $handler] = $this->createTestLoggerWithHandler();

        // Create workspace manager with the test logger
        $storagePath = $this->createTempWorkspaceDir();
        $manager = new WorkspaceManager($storagePath, $logger);

        // Perform operation
        $workspace = $manager->create('Test Workspace', 'Description');

        // Verify log messages
        $this->assertTrue($handler->hasInfoRecords());
        $this->assertTrue($handler->hasInfoThatContains('Created workspace'));

        // Cleanup
        $this->cleanupTempWorkspaceDir($storagePath);
    }
}
```

### Example 3: Custom Configuration for Integration Tests

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Integration;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;

class CustomConfigTest extends TestCase
{
    use ContainerTestTrait;

    public function testWithCustomConfiguration(): void
    {
        // Create configuration with custom settings
        $config = $this->createTestConfiguration([
            'STRUCTURIZR_API_URL' => 'https://custom.api.example.com',
            'STRUCTURIZR_CLI_PATH' => '/custom/path/to/cli',
            'WORKSPACE_STORAGE_PATH' => '/custom/storage/path',
        ]);

        // Verify custom settings
        $this->assertEquals(
            'https://custom.api.example.com',
            $config->getStructurizrApiUrl()
        );
        $this->assertEquals(
            '/custom/path/to/cli',
            $config->getStructurizrCliPath()
        );
    }
}
```

### Example 4: Testing with Mock Data

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;

class DataProcessingTest extends TestCase
{
    use ContainerTestTrait;

    public function testProcessWorkspaceData(): void
    {
        // Create test workspace data without needing real instances
        $workspaceData = $this->createTestWorkspaceData(
            id: 'ws_123',
            name: 'Test Workspace',
            description: 'Test description'
        );

        // Create test element data
        $elementData = $this->createTestElementData(
            type: 'softwareSystem',
            id: 'system_1',
            name: 'My System',
            description: 'Test system'
        );

        // Test data processing logic
        $this->assertEquals('ws_123', $workspaceData['id']);
        $this->assertEquals('softwareSystem', $elementData['type']);
    }
}
```

### Example 5: Using Multiple Traits Together

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Integration;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;
use StructurizrMcp\Tests\Helpers\ServerTestTrait;

class FullStackTest extends TestCase
{
    use ContainerTestTrait;
    use ServerTestTrait;

    private string $tempDir;

    protected function setUp(): void
    {
        // Create temporary workspace directory
        $this->tempDir = $this->createTempWorkspaceDir('fullstack-test-');
    }

    protected function tearDown(): void
    {
        // Clean up
        $this->cleanupTempWorkspaceDir($this->tempDir);
        $this->resetTestEnvironment();
    }

    public function testCompleteWorkflow(): void
    {
        // Create dependencies
        $logger = $this->createTestLogger();
        $manager = $this->createTestWorkspaceManager($logger, $this->tempDir);
        $cache = $this->createTestCache();

        // Build server
        $server = $this->buildTestServer([
            'logger' => $logger,
            'cache' => $cache,
        ]);

        // Test server capabilities
        $capabilities = $this->getServerCapabilities($server);
        $this->assertNotEmpty($capabilities['tools']);
    }
}
```

## ServerTestTrait Examples

### Example 6: Testing Server Capabilities

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Integration;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ServerTestTrait;

class ServerCapabilitiesTest extends TestCase
{
    use ServerTestTrait;

    public function testServerHasAllRequiredTools(): void
    {
        // Build test server
        $server = $this->buildTestServer();

        // Assert specific tools are registered
        $this->assertServerHasTool($server, 'create_workspace');
        $this->assertServerHasTool($server, 'add_software_system');
        $this->assertServerHasTool($server, 'add_person');

        // Get all capabilities for detailed inspection
        $capabilities = $this->getServerCapabilities($server);
        $this->assertGreaterThan(20, count($capabilities['tools']));
    }

    public function testServerHasAllRequiredResources(): void
    {
        $server = $this->buildTestServer();

        // Assert specific resources are registered
        $this->assertServerHasResource($server, 'structurizr://config');
        $this->assertServerHasResource($server, 'structurizr://workspace/{workspaceId}');
    }

    public function testServerHasAllRequiredPrompts(): void
    {
        $server = $this->buildTestServer();

        // Assert specific prompts are registered
        $this->assertServerHasPrompt($server, 'analyze_architecture');
        $this->assertServerHasPrompt($server, 'review_security');
    }
}
```

### Example 7: Custom Server Configuration

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Integration;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ServerTestTrait;

class CustomServerTest extends TestCase
{
    use ServerTestTrait;

    public function testServerWithCustomLogger(): void
    {
        // Create custom logger
        $handler = new TestHandler();
        $logger = new Logger('custom-test-logger');
        $logger->pushHandler($handler);

        // Build server with custom configuration
        $server = $this->buildTestServer([
            'name' => 'custom-server',
            'version' => '2.0.0',
            'description' => 'Custom test server',
            'logger' => $logger,
        ]);

        // Server should be configured with custom settings
        $this->assertInstanceOf(\Mcp\Server::class, $server);
    }

    public function testServerWithoutDiscovery(): void
    {
        // Build server without auto-discovery
        $server = $this->buildTestServer([
            'basePath' => null,  // Disable discovery
        ]);

        $capabilities = $this->getServerCapabilities($server);
        // Without discovery, manual tool registration would be needed
        $this->assertIsArray($capabilities);
    }
}
```

### Example 8: Testing with Temporary Workspaces

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Integration;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;
use StructurizrMcp\Tests\Helpers\ServerTestTrait;

class WorkspaceFileManagementTest extends TestCase
{
    use ContainerTestTrait;
    use ServerTestTrait;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = $this->createTempWorkspaceDir();
    }

    protected function tearDown(): void
    {
        $this->cleanupTempWorkspaceDir($this->tempDir);
    }

    public function testListingMultipleWorkspaces(): void
    {
        // Create test workspace files
        $workspaceIds = $this->createTestWorkspaceFiles($this->tempDir, 5);

        // Verify files were created
        $this->assertCount(5, $workspaceIds);

        // Create manager and verify it can load the workspaces
        $manager = $this->createTestWorkspaceManager(
            storagePath: $this->tempDir
        );

        $workspaces = $manager->listAll();
        $this->assertCount(5, $workspaces);
    }
}
```

### Example 9: Testing Method Existence

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;
use StructurizrMcp\Tests\Helpers\ServerTestTrait;
use StructurizrMcp\Tools\WorkspaceTools;

class ToolInterfaceTest extends TestCase
{
    use ContainerTestTrait;
    use ServerTestTrait;

    public function testWorkspaceToolsHasRequiredMethods(): void
    {
        $manager = $this->createTestWorkspaceManager();
        $logger = $this->createTestLogger();
        $tools = new WorkspaceTools($manager, $logger);

        // Verify required methods exist
        $this->assertMethodExists($tools, 'createWorkspace');
        $this->assertMethodExists($tools, 'getWorkspace');
        $this->assertMethodExists($tools, 'listWorkspaces');
        $this->assertMethodExists($tools, 'deleteWorkspace');
    }
}
```

### Example 10: Complex Integration Test

```php
<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Integration;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;
use StructurizrMcp\Tests\Helpers\ServerTestTrait;
use StructurizrMcp\Tools\WorkspaceTools;
use StructurizrMcp\Tools\ModelTools;
use StructurizrMcp\Tools\ViewTools;

class EndToEndWorkflowTest extends TestCase
{
    use ContainerTestTrait;
    use ServerTestTrait;

    private string $tempDir;
    private WorkspaceTools $workspaceTools;
    private ModelTools $modelTools;
    private ViewTools $viewTools;

    protected function setUp(): void
    {
        // Create temporary directory
        $this->tempDir = $this->createTempWorkspaceDir('e2e-test-');

        // Create dependencies
        $logger = $this->createTestLogger();
        $manager = $this->createTestWorkspaceManager($logger, $this->tempDir);

        // Initialize tools
        $this->workspaceTools = new WorkspaceTools($manager, $logger);
        $this->modelTools = new ModelTools($manager, $logger);
        $this->viewTools = new ViewTools($manager, $logger);
    }

    protected function tearDown(): void
    {
        $this->cleanupTempWorkspaceDir($this->tempDir);
        $this->resetTestEnvironment();
    }

    public function testCompleteArchitectureModeling(): void
    {
        // Create workspace
        $workspace = $this->workspaceTools->createWorkspace(
            'E2E Test System',
            'Complete end-to-end test'
        );
        $workspaceId = $workspace['workspaceId'];

        // Add model elements
        $user = $this->modelTools->addPerson($workspaceId, 'User');
        $system = $this->modelTools->addSoftwareSystem($workspaceId, 'System');
        $container = $this->modelTools->addContainer(
            $workspaceId,
            $system['elementId'],
            'Web App'
        );

        // Add relationships
        $this->modelTools->addRelationship(
            $workspaceId,
            $user['elementId'],
            $system['elementId'],
            'Uses'
        );

        // Create views
        $view = $this->viewTools->createSystemContextView(
            $workspaceId,
            $system['elementId'],
            'SystemContext'
        );

        // Verify complete workflow
        $this->assertEquals('SystemContext', $view['viewKey']);

        // List workspaces to verify persistence
        $list = $this->workspaceTools->listWorkspaces();
        $this->assertEquals(1, $list['count']);
        $this->assertEquals($workspaceId, $list['workspaces'][0]['id']);
    }
}
```

## Best Practices

### 1. Trait Composition
- Use `ContainerTestTrait` for unit tests that need mocked dependencies
- Use `ServerTestTrait` for integration tests involving the MCP server
- Combine both traits when testing full server workflows

### 2. Cleanup
- Always clean up temporary directories in `tearDown()`
- Reset environment variables after tests that modify configuration
- Use unique identifiers for test workspaces to avoid conflicts

### 3. Test Isolation
- Each test should create its own temporary directory
- Don't share state between tests
- Use `createTestLogger()` by default, `createTestLoggerWithHandler()` only when needed

### 4. Performance
- Use `NullLogger` for tests that don't verify logging
- Use in-memory cache (`createInMemoryCache()`) for fast testing
- Create minimal test data structures when possible

### 5. Readability
- Use descriptive test names that explain what is being tested
- Group related assertions together
- Add comments for complex test setups

## Common Patterns

### Pattern 1: Testing Tools in Isolation
```php
use ContainerTestTrait;

$logger = $this->createTestLogger();
$manager = $this->createTestWorkspaceManager($logger);
$tool = new MyTool($manager, $logger);
```

### Pattern 2: Testing Server Capabilities
```php
use ServerTestTrait;

$server = $this->buildTestServer();
$this->assertServerHasTool($server, 'my_tool');
```

### Pattern 3: Integration Tests with Real Components
```php
use ContainerTestTrait;
use ServerTestTrait;

$tempDir = $this->createTempWorkspaceDir();
$logger = $this->createTestLogger();
$manager = $this->createTestWorkspaceManager($logger, $tempDir);
// ... perform tests
$this->cleanupTempWorkspaceDir($tempDir);
```

### Pattern 4: Testing with Log Verification
```php
use ContainerTestTrait;

[$logger, $handler] = $this->createTestLoggerWithHandler();
// ... perform operations
$this->assertTrue($handler->hasInfoThatContains('expected message'));
```

### Pattern 5: Testing with Multiple Workspaces
```php
use ServerTestTrait;

$tempDir = $this->createTempWorkspaceDir();
$workspaceIds = $this->createTestWorkspaceFiles($tempDir, 10);
// ... test workspace listing/loading
$this->cleanupTempWorkspaceDir($tempDir);
```
