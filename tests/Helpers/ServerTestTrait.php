<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Helpers;

use Mcp\Server;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Test trait providing helper methods for MCP server testing
 *
 * This trait provides utilities for building and testing MCP servers, including
 * server creation, capability inspection, and temporary directory management.
 *
 * Usage:
 * ```php
 * class ServerTest extends TestCase
 * {
 *     use ServerTestTrait;
 *
 *     public function testServerCapabilities(): void
 *     {
 *         $server = $this->buildTestServer();
 *         $capabilities = $this->getServerCapabilities($server);
 *         $this->assertArrayHasKey('tools', $capabilities);
 *     }
 *
 *     protected function tearDown(): void
 *     {
 *         if (isset($this->tempDir)) {
 *             $this->cleanupTempWorkspaceDir($this->tempDir);
 *         }
 *     }
 * }
 * ```
 */
trait ServerTestTrait
{
    /**
     * Build a test MCP server instance
     *
     * Creates a fully configured MCP server for testing with auto-discovery enabled.
     * Accepts custom configuration to override defaults.
     *
     * Configuration options:
     * - name: Server name (default: 'test-server')
     * - version: Server version (default: '1.0.0-test')
     * - description: Server description
     * - instructions: Server instructions
     * - logger: PSR-3 logger instance
     * - cache: PSR-16 cache instance
     * - basePath: Base path for discovery (default: project root)
     * - scanDirs: Directories to scan for capabilities (default: ['src'])
     * - excludeDirs: Directories to exclude from scanning
     *
     * @param array<string, mixed> $config Server configuration overrides
     * @return Server Configured test server instance
     */
    protected function buildTestServer(array $config = []): Server
    {
        // Default configuration
        $defaults = [
            'name' => 'test-server',
            'version' => '1.0.0-test',
            'description' => 'Test MCP server for Structurizr',
            'instructions' => 'Test server for automated testing',
            'logger' => new NullLogger(),
            'cache' => $this->createInMemoryCache(),
            'basePath' => dirname(__DIR__, 2),
            'scanDirs' => ['src'],
            'excludeDirs' => ['vendor', 'tests', 'cache', 'sessions', 'workspaces', 'docs', 'examples'],
        ];

        // Merge configuration
        $config = array_merge($defaults, $config);

        // Ensure logger is LoggerInterface
        $logger = $config['logger'] instanceof LoggerInterface
            ? $config['logger']
            : new NullLogger();

        // Ensure cache is CacheInterface
        $cache = $config['cache'] instanceof CacheInterface
            ? $config['cache']
            : $this->createInMemoryCache();

        // Build server
        $builder = Server::builder()
            ->setServerInfo(
                name: $config['name'],
                version: $config['version'],
                description: $config['description']
            )
            ->setInstructions($config['instructions'])
            ->setLogger($logger);

        // Add discovery if base path is provided
        if (isset($config['basePath'])) {
            $builder->setDiscovery(
                basePath: $config['basePath'],
                scanDirs: $config['scanDirs'],
                excludeDirs: $config['excludeDirs'],
                cache: $cache
            );
        }

        return $builder->build();
    }

    /**
     * Get server capabilities
     *
     * Extracts and returns the capabilities registered with the server.
     * This method uses reflection to access the server's internal state.
     *
     * Returns an array with the following structure:
     * ```php
     * [
     *     'tools' => [...],      // Registered tools
     *     'resources' => [...],  // Registered resources
     *     'prompts' => [...],    // Registered prompts
     * ]
     * ```
     *
     * @param Server $server MCP server instance
     * @return array<string, array<mixed>> Server capabilities
     */
    protected function getServerCapabilities(Server $server): array
    {
        $reflection = new \ReflectionClass($server);

        $capabilities = [
            'tools' => [],
            'resources' => [],
            'prompts' => [],
        ];

        // Try to get tools via reflection
        if ($reflection->hasProperty('tools')) {
            $toolsProperty = $reflection->getProperty('tools');
            $toolsProperty->setAccessible(true);
            $capabilities['tools'] = $toolsProperty->getValue($server);
        }

        // Try to get resources via reflection
        if ($reflection->hasProperty('resources')) {
            $resourcesProperty = $reflection->getProperty('resources');
            $resourcesProperty->setAccessible(true);
            $capabilities['resources'] = $resourcesProperty->getValue($server);
        }

        // Try to get prompts via reflection
        if ($reflection->hasProperty('prompts')) {
            $promptsProperty = $reflection->getProperty('prompts');
            $promptsProperty->setAccessible(true);
            $capabilities['prompts'] = $promptsProperty->getValue($server);
        }

        return $capabilities;
    }

    /**
     * Create a temporary workspace directory
     *
     * Creates a unique temporary directory for storing test workspaces.
     * The directory is created in the system temp directory with a unique identifier.
     *
     * Important: The caller is responsible for cleaning up the directory after use.
     * Use cleanupTempWorkspaceDir() in tearDown() to ensure cleanup.
     *
     * @param string|null $prefix Optional prefix for the directory name
     * @return string Absolute path to the created directory
     */
    protected function createTempWorkspaceDir(?string $prefix = null): string
    {
        $prefix = $prefix ?? 'structurizr-test-';
        $tempDir = sys_get_temp_dir() . '/' . $prefix . uniqid();

        $filesystem = new Filesystem();
        $filesystem->mkdir($tempDir, 0o755);

        return $tempDir;
    }

    /**
     * Clean up a temporary workspace directory
     *
     * Recursively removes a temporary directory and all its contents.
     * Safe to call even if the directory doesn't exist.
     *
     * @param string $path Path to the directory to remove
     * @return void
     */
    protected function cleanupTempWorkspaceDir(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $filesystem = new Filesystem();
        $filesystem->remove($path);
    }

    /**
     * Create an in-memory cache for testing
     *
     * Creates a PSR-16 cache backed by ArrayAdapter for fast, isolated testing.
     * Cache data exists only in memory and doesn't persist between tests.
     *
     * @param int $defaultLifetime Default cache lifetime in seconds
     * @return CacheInterface PSR-16 cache instance
     */
    protected function createInMemoryCache(int $defaultLifetime = 3600): CacheInterface
    {
        $adapter = new ArrayAdapter($defaultLifetime, false);
        return new Psr16Cache($adapter);
    }

    /**
     * Assert server has tool
     *
     * Verifies that a server has a specific tool registered.
     *
     * @param Server $server MCP server instance
     * @param string $toolName Name of the tool to check
     * @return void
     */
    protected function assertServerHasTool(Server $server, string $toolName): void
    {
        $capabilities = $this->getServerCapabilities($server);
        $toolNames = array_column($capabilities['tools'] ?? [], 'name');

        $this->assertContains(
            $toolName,
            $toolNames,
            "Server does not have tool: {$toolName}"
        );
    }

    /**
     * Assert server has resource
     *
     * Verifies that a server has a specific resource registered.
     *
     * @param Server $server MCP server instance
     * @param string $resourceUri URI or pattern of the resource to check
     * @return void
     */
    protected function assertServerHasResource(Server $server, string $resourceUri): void
    {
        $capabilities = $this->getServerCapabilities($server);
        $resourceUris = array_column($capabilities['resources'] ?? [], 'uri');

        $this->assertContains(
            $resourceUri,
            $resourceUris,
            "Server does not have resource: {$resourceUri}"
        );
    }

    /**
     * Assert server has prompt
     *
     * Verifies that a server has a specific prompt registered.
     *
     * @param Server $server MCP server instance
     * @param string $promptName Name of the prompt to check
     * @return void
     */
    protected function assertServerHasPrompt(Server $server, string $promptName): void
    {
        $capabilities = $this->getServerCapabilities($server);
        $promptNames = array_column($capabilities['prompts'] ?? [], 'name');

        $this->assertContains(
            $promptName,
            $promptNames,
            "Server does not have prompt: {$promptName}"
        );
    }

    /**
     * Get test base path
     *
     * Returns the project root directory path. Useful for locating test fixtures
     * or setting up discovery paths.
     *
     * @return string Absolute path to project root
     */
    protected function getTestBasePath(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * Create test workspace files
     *
     * Creates test workspace JSON files in a temporary directory.
     * Useful for testing workspace loading and listing functionality.
     *
     * @param string $storagePath Directory to create files in
     * @param int $count Number of test workspaces to create
     * @return array<string> Array of created workspace IDs
     */
    protected function createTestWorkspaceFiles(string $storagePath, int $count = 3): array
    {
        $filesystem = new Filesystem();
        $workspaceIds = [];

        for ($i = 1; $i <= $count; $i++) {
            $workspaceId = "ws_test_{$i}_" . uniqid();
            $workspaceIds[] = $workspaceId;

            $workspaceData = [
                'id' => $workspaceId,
                'name' => "Test Workspace {$i}",
                'description' => "Test workspace number {$i}",
                'model' => [],
                'views' => [],
                'dsl' => '',
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'updatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ];

            $filepath = $storagePath . '/' . $workspaceId . '.json';
            $filesystem->dumpFile($filepath, json_encode($workspaceData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        }

        return $workspaceIds;
    }

    /**
     * Assert method exists on object
     *
     * Verifies that an object has a specific method defined.
     *
     * @param object $object Object to check
     * @param string $methodName Method name to verify
     * @return void
     */
    protected function assertMethodExists(object $object, string $methodName): void
    {
        $this->assertTrue(
            method_exists($object, $methodName),
            sprintf(
                'Method %s does not exist on %s',
                $methodName,
                get_class($object)
            )
        );
    }
}
