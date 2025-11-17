<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Helpers;

use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use StructurizrMcp\Configuration;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Structurizr\WorkspaceManager;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Test trait providing helper methods for creating test dependencies
 *
 * This trait provides reusable factory methods for creating common test dependencies
 * such as Configuration, Logger, WorkspaceManager, and Cache instances configured
 * for testing environments.
 *
 * Usage:
 * ```php
 * class MyTest extends TestCase
 * {
 *     use ContainerTestTrait;
 *
 *     public function testSomething(): void
 *     {
 *         $config = $this->createTestConfiguration();
 *         $logger = $this->createTestLogger();
 *         $manager = $this->createTestWorkspaceManager($logger);
 *         // ... use test dependencies
 *     }
 * }
 * ```
 */
trait ContainerTestTrait
{
    /**
     * Create a test configuration instance with default or custom settings
     *
     * This method creates a Configuration instance suitable for testing by setting
     * up environment variables with test-friendly defaults. Custom values can be
     * provided to override defaults.
     *
     * @param array<string, string> $overrides Custom environment variable values
     * @return Configuration Configured instance for testing
     */
    protected function createTestConfiguration(array $overrides = []): Configuration
    {
        // Set default test environment variables
        $defaults = [
            'STRUCTURIZR_API_KEY' => 'test-api-key',
            'STRUCTURIZR_API_SECRET' => 'test-api-secret',
            'STRUCTURIZR_API_URL' => 'https://api.test.structurizr.com',
            'STRUCTURIZR_WORKSPACE_ID' => 'test-workspace-123',
            'STRUCTURIZR_CLI_PATH' => '/usr/local/bin/structurizr-cli',
            'WORKSPACE_STORAGE_PATH' => sys_get_temp_dir() . '/structurizr-test-' . uniqid(),
            'LOG_LEVEL' => 'DEBUG',
            'LOG_PATH' => 'php://memory',
            'SERVER_NAME' => 'structurizr-test-server',
            'SERVER_VERSION' => '1.0.0-test',
        ];

        // Apply overrides
        $settings = array_merge($defaults, $overrides);

        // Set environment variables
        foreach ($settings as $key => $value) {
            $_ENV[$key] = $value;
        }

        return new Configuration();
    }

    /**
     * Create a test logger instance
     *
     * Returns a NullLogger by default for tests that don't need to inspect log output.
     * Use createTestLoggerWithHandler() if you need to verify log messages.
     *
     * @return LoggerInterface Test logger instance
     */
    protected function createTestLogger(): LoggerInterface
    {
        return new NullLogger();
    }

    /**
     * Create a test logger with a TestHandler for verifying log messages
     *
     * The TestHandler allows you to inspect logged messages during tests.
     * Useful for testing that specific log messages are generated.
     *
     * Example:
     * ```php
     * [$logger, $handler] = $this->createTestLoggerWithHandler();
     * // ... perform operations
     * $this->assertTrue($handler->hasInfoRecords());
     * $this->assertTrue($handler->hasInfoThatContains('Workspace created'));
     * ```
     *
     * @param int $level Minimum log level (default: Logger::DEBUG)
     * @return array{0: Logger, 1: TestHandler} Array containing logger and handler
     */
    protected function createTestLoggerWithHandler(int $level = Logger::DEBUG): array
    {
        $handler = new TestHandler(Level::from($level));
        $logger = new Logger('test-logger');
        $logger->pushHandler($handler);

        return [$logger, $handler];
    }

    /**
     * Create a test WorkspaceManager instance
     *
     * Creates a WorkspaceManager configured for testing with a temporary storage
     * directory. The storage path is created in the system temp directory with a
     * unique identifier to avoid conflicts between tests.
     *
     * Note: Callers are responsible for cleaning up the temporary directory after
     * tests complete. Consider using ServerTestTrait::cleanupTempWorkspaceDir().
     *
     * @param LoggerInterface|null $logger Optional logger (defaults to NullLogger)
     * @param string|null $storagePath Optional custom storage path
     * @return WorkspaceManager Configured workspace manager for testing
     */
    protected function createTestWorkspaceManager(
        ?LoggerInterface $logger = null,
        ?string $storagePath = null
    ): WorkspaceManager {
        $logger = $logger ?? $this->createTestLogger();
        $storagePath = $storagePath ?? sys_get_temp_dir() . '/structurizr-test-' . uniqid();

        return new WorkspaceManager($storagePath, $logger);
    }

    /**
     * Create a test CliWrapper instance
     *
     * Creates a CliWrapper for testing. By default, uses a test CLI path.
     * You may want to mock this for unit tests to avoid external dependencies.
     *
     * @param string|null $cliPath Optional custom CLI path
     * @param LoggerInterface|null $logger Optional logger (defaults to NullLogger)
     * @return CliWrapper Configured CLI wrapper for testing
     */
    protected function createTestCliWrapper(
        ?string $cliPath = null,
        ?LoggerInterface $logger = null
    ): CliWrapper {
        $cliPath = $cliPath ?? '/usr/local/bin/structurizr-cli';
        $logger = $logger ?? $this->createTestLogger();

        return new CliWrapper($cliPath, $logger);
    }

    /**
     * Create a test cache instance
     *
     * Creates an in-memory PSR-16 cache suitable for testing. Uses ArrayAdapter
     * for fast, isolated caching without filesystem dependencies.
     *
     * @return CacheInterface PSR-16 cache instance for testing
     */
    protected function createTestCache(): CacheInterface
    {
        $adapter = new ArrayAdapter(
            defaultLifetime: 3600,
            storeSerialized: false
        );

        return new Psr16Cache($adapter);
    }

    /**
     * Reset test environment variables
     *
     * Clears all test-related environment variables. Useful in tearDown()
     * to ensure clean state between tests.
     *
     * @return void
     */
    protected function resetTestEnvironment(): void
    {
        $testEnvVars = [
            'STRUCTURIZR_API_KEY',
            'STRUCTURIZR_API_SECRET',
            'STRUCTURIZR_API_URL',
            'STRUCTURIZR_WORKSPACE_ID',
            'STRUCTURIZR_CLI_PATH',
            'WORKSPACE_STORAGE_PATH',
            'LOG_LEVEL',
            'LOG_PATH',
            'SERVER_NAME',
            'SERVER_VERSION',
        ];

        foreach ($testEnvVars as $var) {
            unset($_ENV[$var]);
        }
    }

    /**
     * Create a minimal workspace data array for testing
     *
     * Provides a basic workspace structure that can be used in tests.
     * Useful for creating test fixtures without needing real workspace instances.
     *
     * @param string $id Workspace ID
     * @param string $name Workspace name
     * @param string $description Workspace description
     * @return array<string, mixed> Workspace data array
     */
    protected function createTestWorkspaceData(
        string $id = 'ws_test_123',
        string $name = 'Test Workspace',
        string $description = 'Test workspace description'
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'model' => [],
            'views' => [],
            'dsl' => '',
            'createdAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'updatedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Create a test element data array
     *
     * Provides a basic element structure for testing without creating real elements.
     *
     * @param string $type Element type (person, softwareSystem, container, component)
     * @param string $id Element ID
     * @param string $name Element name
     * @param string $description Element description
     * @return array<string, mixed> Element data array
     */
    protected function createTestElementData(
        string $type = 'person',
        string $id = 'element_1',
        string $name = 'Test Element',
        string $description = 'Test element description'
    ): array {
        return [
            'type' => $type,
            'elementId' => $id,
            'name' => $name,
            'description' => $description,
            'tags' => [],
            'relationships' => [],
        ];
    }
}
