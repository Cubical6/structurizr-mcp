<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Configuration;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * Example test demonstrating usage of ContainerTestTrait and ServerTestTrait
 *
 * This test class serves as a reference implementation showing how to use
 * both helper traits together in a real test scenario.
 *
 * @group helpers
 * @group example
 */
class ExampleTraitUsageTest extends TestCase
{
    use ContainerTestTrait;
    use ServerTestTrait;

    private string $tempDir;

    protected function setUp(): void
    {
        // Create a temporary directory for test workspaces
        $this->tempDir = $this->createTempWorkspaceDir('example-test-');
    }

    protected function tearDown(): void
    {
        // Clean up temporary directory and environment
        $this->cleanupTempWorkspaceDir($this->tempDir);
        $this->resetTestEnvironment();
    }

    /**
     * Example 1: Using ContainerTestTrait to create test dependencies
     */
    public function testCreateTestConfiguration(): void
    {
        $config = $this->createTestConfiguration([
            'SERVER_NAME' => 'example-test-server',
        ]);

        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertEquals('example-test-server', $config->getServerName());
    }

    /**
     * Example 2: Using ContainerTestTrait to create a test logger
     */
    public function testCreateTestLogger(): void
    {
        $logger = $this->createTestLogger();
        $this->assertInstanceOf(\Psr\Log\LoggerInterface::class, $logger);

        // Logger should accept log calls without errors
        $logger->info('Test log message');
        $this->assertTrue(true); // If we get here, logging worked
    }

    /**
     * Example 3: Using ContainerTestTrait with log handler for verification
     */
    public function testCreateTestLoggerWithHandler(): void
    {
        [$logger, $handler] = $this->createTestLoggerWithHandler();

        $logger->info('Test message 1');
        $logger->debug('Test message 2');
        $logger->error('Test error message');

        // Verify log messages were captured
        $this->assertTrue($handler->hasInfoRecords());
        $this->assertTrue($handler->hasDebugRecords());
        $this->assertTrue($handler->hasErrorRecords());
        $this->assertTrue($handler->hasInfoThatContains('Test message 1'));
    }

    /**
     * Example 4: Using ContainerTestTrait to create WorkspaceManager
     */
    public function testCreateTestWorkspaceManager(): void
    {
        $logger = $this->createTestLogger();
        $manager = $this->createTestWorkspaceManager($logger, $this->tempDir);

        $this->assertInstanceOf(WorkspaceManager::class, $manager);

        // Manager should be able to create a workspace
        $workspace = $manager->create('Example Workspace', 'Test description');
        $this->assertEquals('Example Workspace', $workspace->name);
    }

    /**
     * Example 5: Using ContainerTestTrait to create test data
     */
    public function testCreateTestWorkspaceData(): void
    {
        $workspaceData = $this->createTestWorkspaceData(
            id: 'ws_example_123',
            name: 'Example Workspace',
            description: 'Example description'
        );

        $this->assertIsArray($workspaceData);
        $this->assertEquals('ws_example_123', $workspaceData['id']);
        $this->assertEquals('Example Workspace', $workspaceData['name']);
        $this->assertArrayHasKey('model', $workspaceData);
        $this->assertArrayHasKey('views', $workspaceData);
    }

    /**
     * Example 6: Using ContainerTestTrait to create test element data
     */
    public function testCreateTestElementData(): void
    {
        $elementData = $this->createTestElementData(
            type: 'softwareSystem',
            id: 'system_1',
            name: 'Example System',
            description: 'Example system description'
        );

        $this->assertIsArray($elementData);
        $this->assertEquals('softwareSystem', $elementData['type']);
        $this->assertEquals('system_1', $elementData['elementId']);
        $this->assertEquals('Example System', $elementData['name']);
    }

    /**
     * Example 7: Using ServerTestTrait to build a test server
     */
    public function testBuildTestServer(): void
    {
        $server = $this->buildTestServer([
            'name' => 'example-server',
            'version' => '1.0.0-example',
        ]);

        $this->assertInstanceOf(\Mcp\Server::class, $server);
    }

    /**
     * Example 8: Using ServerTestTrait to inspect server capabilities
     */
    public function testGetServerCapabilities(): void
    {
        $server = $this->buildTestServer();
        $capabilities = $this->getServerCapabilities($server);

        $this->assertIsArray($capabilities);
        $this->assertArrayHasKey('tools', $capabilities);
        $this->assertArrayHasKey('resources', $capabilities);
        $this->assertArrayHasKey('prompts', $capabilities);
    }

    /**
     * Example 9: Using ServerTestTrait to verify server has specific tools
     */
    public function testAssertServerHasTool(): void
    {
        $server = $this->buildTestServer();

        // These assertions will pass if auto-discovery finds the tools
        $this->assertServerHasTool($server, 'create_workspace');
        $this->assertServerHasTool($server, 'list_workspaces');
    }

    /**
     * Example 10: Using ServerTestTrait to create test workspace files
     */
    public function testCreateTestWorkspaceFiles(): void
    {
        $workspaceIds = $this->createTestWorkspaceFiles($this->tempDir, 3);

        $this->assertCount(3, $workspaceIds);

        // Verify files were actually created
        foreach ($workspaceIds as $id) {
            $filepath = $this->tempDir . '/' . $id . '.json';
            $this->assertFileExists($filepath);

            $content = file_get_contents($filepath);
            $this->assertNotFalse($content);

            $data = json_decode($content, true);
            $this->assertIsArray($data);
            $this->assertEquals($id, $data['id']);
        }
    }

    /**
     * Example 11: Using both traits together for integration testing
     */
    public function testCombinedTraitUsage(): void
    {
        // Use ContainerTestTrait to create dependencies
        $logger = $this->createTestLogger();
        $cache = $this->createTestCache();
        $manager = $this->createTestWorkspaceManager($logger, $this->tempDir);

        // Use ServerTestTrait to build server
        $server = $this->buildTestServer([
            'logger' => $logger,
            'cache' => $cache,
        ]);

        // Use ServerTestTrait to verify capabilities
        $capabilities = $this->getServerCapabilities($server);
        $this->assertNotEmpty($capabilities);

        // Create a workspace using the manager
        $workspace = $manager->create('Integration Test', 'Testing both traits');
        $this->assertEquals('Integration Test', $workspace->name);

        // Verify the workspace file exists
        $filepath = $this->tempDir . '/' . $workspace->id . '.json';
        $this->assertFileExists($filepath);
    }

    /**
     * Example 12: Using ServerTestTrait helper methods
     */
    public function testServerTraitHelperMethods(): void
    {
        // Get base path
        $basePath = $this->getTestBasePath();
        $this->assertDirectoryExists($basePath);
        $this->assertStringEndsWith('structurizr-mcp', $basePath);

        // Create in-memory cache
        $cache = $this->createInMemoryCache();
        $this->assertInstanceOf(\Psr\SimpleCache\CacheInterface::class, $cache);

        // Test cache operations
        $cache->set('test_key', 'test_value');
        $this->assertEquals('test_value', $cache->get('test_key'));
    }

    /**
     * Example 13: Testing method existence
     */
    public function testAssertMethodExists(): void
    {
        $manager = $this->createTestWorkspaceManager();

        $this->assertMethodExists($manager, 'create');
        $this->assertMethodExists($manager, 'load');
        $this->assertMethodExists($manager, 'save');
        $this->assertMethodExists($manager, 'delete');
        $this->assertMethodExists($manager, 'listAll');
    }

    /**
     * Example 14: Using ContainerTestTrait to create CLI wrapper
     */
    public function testCreateTestCliWrapper(): void
    {
        $logger = $this->createTestLogger();
        $cliWrapper = $this->createTestCliWrapper('/usr/bin/structurizr', $logger);

        $this->assertInstanceOf(\StructurizrMcp\Structurizr\CliWrapper::class, $cliWrapper);
    }

    /**
     * Example 15: Environment cleanup
     */
    public function testResetTestEnvironment(): void
    {
        // Set some environment variables
        $_ENV['STRUCTURIZR_API_KEY'] = 'test-key';
        $_ENV['SERVER_NAME'] = 'test-server';

        // Reset environment
        $this->resetTestEnvironment();

        // Variables should be unset
        $this->assertArrayNotHasKey('STRUCTURIZR_API_KEY', $_ENV);
        $this->assertArrayNotHasKey('SERVER_NAME', $_ENV);
    }
}
