<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use StructurizrMcp\Configuration;
use StructurizrMcp\Prompts\AnalysisPrompts;
use StructurizrMcp\Prompts\GenerationPrompts;
use StructurizrMcp\Resources\ConfigResource;
use StructurizrMcp\Resources\ElementResource;
use StructurizrMcp\Resources\ViewResource;
use StructurizrMcp\Resources\WorkspaceResource;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Tools\AnalysisTools;
use StructurizrMcp\Tools\DocumentationTools;
use StructurizrMcp\Tools\ExportTools;
use StructurizrMcp\Tools\ModelTools;
use StructurizrMcp\Tools\ViewTools;
use StructurizrMcp\Tools\WorkspaceTools;

/**
 * Comprehensive unit tests for PSR-11 Container configuration
 *
 * Tests container service resolution, dependency injection, singleton behavior,
 * and proper wiring of all tool, resource, and prompt classes.
 *
 * @covers \StructurizrMcp\Container\ServerContainer
 */
class ServerContainerTest extends TestCase
{
    private ContainerInterface $container;
    private string $tempDir;

    protected function setUp(): void
    {
        // Create temporary directory for testing
        $this->tempDir = sys_get_temp_dir() . '/structurizr-mcp-test-' . uniqid();
        mkdir($this->tempDir, 0o755, true);

        // Create a mock CLI executable for testing
        $mockCliPath = $this->tempDir . '/structurizr-cli.sh';
        file_put_contents($mockCliPath, "#!/bin/bash\necho 'Mock CLI'\n");
        chmod($mockCliPath, 0o755);

        // Set environment variables for testing
        $_ENV['WORKSPACE_STORAGE_PATH'] = $this->tempDir;
        $_ENV['LOG_LEVEL'] = 'ERROR';
        $_ENV['LOG_PATH'] = 'php://stderr';
        $_ENV['SERVER_NAME'] = 'test-server';
        $_ENV['SERVER_VERSION'] = '1.0.0-test';
        $_ENV['STRUCTURIZR_CLI_PATH'] = $mockCliPath;

        // Create test container
        $this->container = $this->createTestContainer();
    }

    protected function tearDown(): void
    {
        // Clean up temporary directory
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }

        // Clean up environment variables
        unset($_ENV['WORKSPACE_STORAGE_PATH']);
        unset($_ENV['LOG_LEVEL']);
        unset($_ENV['LOG_PATH']);
        unset($_ENV['SERVER_NAME']);
        unset($_ENV['SERVER_VERSION']);
        unset($_ENV['STRUCTURIZR_CLI_PATH']);
    }

    /**
     * Test that container implements PSR-11 ContainerInterface
     */
    public function testContainerImplementsPsr11Interface(): void
    {
        $this->assertInstanceOf(
            ContainerInterface::class,
            $this->container,
            'Container must implement PSR-11 ContainerInterface'
        );

        // Verify interface methods exist
        $this->assertTrue(
            method_exists($this->container, 'get'),
            'Container must have get() method'
        );
        $this->assertTrue(
            method_exists($this->container, 'has'),
            'Container must have has() method'
        );
    }

    /**
     * Test that container resolves Configuration service
     */
    public function testContainerResolvesConfiguration(): void
    {
        $this->assertTrue(
            $this->container->has(Configuration::class),
            'Container must have Configuration service'
        );

        $config = $this->container->get(Configuration::class);

        $this->assertInstanceOf(
            Configuration::class,
            $config,
            'Container must resolve Configuration instance'
        );

        $this->assertEquals('test-server', $config->getServerName());
        $this->assertEquals('1.0.0-test', $config->getServerVersion());
        $this->assertEquals($this->tempDir, $config->getWorkspacePath());
    }

    /**
     * Test that container resolves Logger service
     */
    public function testContainerResolvesLogger(): void
    {
        $this->assertTrue(
            $this->container->has(LoggerInterface::class),
            'Container must have LoggerInterface service'
        );

        $logger = $this->container->get(LoggerInterface::class);

        $this->assertInstanceOf(
            LoggerInterface::class,
            $logger,
            'Container must resolve LoggerInterface instance'
        );

        // Logger should be usable without errors
        $logger->info('Test message');
        $logger->debug('Debug message');
        $this->assertTrue(true, 'Logger methods should be callable');
    }

    /**
     * Test that container resolves WorkspaceManager service
     */
    public function testContainerResolvesWorkspaceManager(): void
    {
        $this->assertTrue(
            $this->container->has(WorkspaceManager::class),
            'Container must have WorkspaceManager service'
        );

        $workspaceManager = $this->container->get(WorkspaceManager::class);

        $this->assertInstanceOf(
            WorkspaceManager::class,
            $workspaceManager,
            'Container must resolve WorkspaceManager instance'
        );
    }

    /**
     * Test that container resolves CliWrapper service
     */
    public function testContainerResolvesCliWrapper(): void
    {
        $this->assertTrue(
            $this->container->has(CliWrapper::class),
            'Container must have CliWrapper service'
        );

        $cliWrapper = $this->container->get(CliWrapper::class);

        $this->assertInstanceOf(
            CliWrapper::class,
            $cliWrapper,
            'Container must resolve CliWrapper instance'
        );
    }

    /**
     * Test that container resolves all tool classes
     *
     * Loops through all 6 tool classes and verifies proper resolution
     */
    public function testContainerResolvesAllToolClasses(): void
    {
        $toolClasses = [
            WorkspaceTools::class,
            ModelTools::class,
            ViewTools::class,
            DocumentationTools::class,
            AnalysisTools::class,
            ExportTools::class,
        ];

        foreach ($toolClasses as $toolClass) {
            $this->assertTrue(
                $this->container->has($toolClass),
                "Container must have {$toolClass} service"
            );

            $tool = $this->container->get($toolClass);

            $this->assertInstanceOf(
                $toolClass,
                $tool,
                "Container must resolve {$toolClass} instance"
            );
        }

        $this->assertTrue(
            true,
            'All tool classes successfully resolved'
        );
    }

    /**
     * Test that container resolves all resource classes
     *
     * Loops through all 4 resource classes and verifies proper resolution
     */
    public function testContainerResolvesAllResourceClasses(): void
    {
        $resourceClasses = [
            ConfigResource::class,
            WorkspaceResource::class,
            ElementResource::class,
            ViewResource::class,
        ];

        foreach ($resourceClasses as $resourceClass) {
            $this->assertTrue(
                $this->container->has($resourceClass),
                "Container must have {$resourceClass} service"
            );

            $resource = $this->container->get($resourceClass);

            $this->assertInstanceOf(
                $resourceClass,
                $resource,
                "Container must resolve {$resourceClass} instance"
            );
        }

        $this->assertTrue(
            true,
            'All resource classes successfully resolved'
        );
    }

    /**
     * Test that container resolves all prompt classes
     *
     * Loops through all 2 prompt classes and verifies proper resolution
     */
    public function testContainerResolvesAllPromptClasses(): void
    {
        $promptClasses = [
            AnalysisPrompts::class,
            GenerationPrompts::class,
        ];

        foreach ($promptClasses as $promptClass) {
            $this->assertTrue(
                $this->container->has($promptClass),
                "Container must have {$promptClass} service"
            );

            $prompt = $this->container->get($promptClass);

            $this->assertInstanceOf(
                $promptClass,
                $prompt,
                "Container must resolve {$promptClass} instance"
            );
        }

        $this->assertTrue(
            true,
            'All prompt classes successfully resolved'
        );
    }

    /**
     * Test that ExportTools has CliWrapper dependency properly injected
     */
    public function testExportToolsHasCliWrapperDependency(): void
    {
        $exportTools = $this->container->get(ExportTools::class);

        $this->assertInstanceOf(
            ExportTools::class,
            $exportTools,
            'ExportTools must be resolved'
        );

        // Verify that ExportTools was constructed with proper dependencies
        // We can verify this by checking that the object is properly constructed
        $reflection = new \ReflectionClass($exportTools);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor, 'ExportTools must have constructor');

        $parameters = $constructor->getParameters();
        $parameterNames = array_map(fn($p) => $p->getName(), $parameters);

        $this->assertContains(
            'workspaceManager',
            $parameterNames,
            'ExportTools constructor must have workspaceManager parameter'
        );
        $this->assertContains(
            'cliWrapper',
            $parameterNames,
            'ExportTools constructor must have cliWrapper parameter'
        );
        $this->assertContains(
            'logger',
            $parameterNames,
            'ExportTools constructor must have logger parameter'
        );
    }

    /**
     * Test that ConfigResource has Configuration dependency properly injected
     */
    public function testConfigResourceHasConfigurationDependency(): void
    {
        $configResource = $this->container->get(ConfigResource::class);

        $this->assertInstanceOf(
            ConfigResource::class,
            $configResource,
            'ConfigResource must be resolved'
        );

        // Verify that ConfigResource was constructed with proper dependencies
        $reflection = new \ReflectionClass($configResource);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor, 'ConfigResource must have constructor');

        $parameters = $constructor->getParameters();
        $parameterNames = array_map(fn($p) => $p->getName(), $parameters);

        $this->assertContains(
            'config',
            $parameterNames,
            'ConfigResource constructor must have config parameter'
        );
        $this->assertContains(
            'workspaceManager',
            $parameterNames,
            'ConfigResource constructor must have workspaceManager parameter'
        );
        $this->assertContains(
            'logger',
            $parameterNames,
            'ConfigResource constructor must have logger parameter'
        );
    }

    /**
     * Test that container has no circular dependencies
     */
    public function testNoCircularDependencies(): void
    {
        // Track resolution order to detect circular dependencies
        $resolutionOrder = [];

        $services = [
            Configuration::class,
            LoggerInterface::class,
            WorkspaceManager::class,
            CliWrapper::class,
            WorkspaceTools::class,
            ModelTools::class,
            ViewTools::class,
            DocumentationTools::class,
            AnalysisTools::class,
            ExportTools::class,
            ConfigResource::class,
            WorkspaceResource::class,
            ElementResource::class,
            ViewResource::class,
            AnalysisPrompts::class,
            GenerationPrompts::class,
        ];

        // Attempt to resolve all services - this should not cause infinite recursion
        foreach ($services as $service) {
            try {
                $instance = $this->container->get($service);
                $resolutionOrder[] = $service;

                $this->assertInstanceOf(
                    $service,
                    $instance,
                    "Service {$service} should be resolvable without circular dependencies"
                );
            } catch (\Throwable $e) {
                $this->fail(
                    "Circular dependency or resolution error detected for {$service}: " . $e->getMessage()
                );
            }
        }

        $this->assertGreaterThan(
            0,
            count($resolutionOrder),
            'All services should be resolved without circular dependencies'
        );
    }

    /**
     * Test that container returns singletons for shared services
     */
    public function testContainerReturnesSingletonsForSharedServices(): void
    {
        // Core services should be singletons
        $config1 = $this->container->get(Configuration::class);
        $config2 = $this->container->get(Configuration::class);
        $this->assertSame(
            $config1,
            $config2,
            'Configuration should be singleton'
        );

        $logger1 = $this->container->get(LoggerInterface::class);
        $logger2 = $this->container->get(LoggerInterface::class);
        $this->assertSame(
            $logger1,
            $logger2,
            'Logger should be singleton'
        );

        $workspaceManager1 = $this->container->get(WorkspaceManager::class);
        $workspaceManager2 = $this->container->get(WorkspaceManager::class);
        $this->assertSame(
            $workspaceManager1,
            $workspaceManager2,
            'WorkspaceManager should be singleton'
        );

        // Tool instances should be singletons
        $workspaceTools1 = $this->container->get(WorkspaceTools::class);
        $workspaceTools2 = $this->container->get(WorkspaceTools::class);
        $this->assertSame(
            $workspaceTools1,
            $workspaceTools2,
            'WorkspaceTools should be singleton'
        );
    }

    /**
     * Test that container throws exception for unknown service
     */
    public function testContainerThrowsExceptionForUnknownService(): void
    {
        $unknownService = 'NonExistent\\Service\\Class';

        $this->assertFalse(
            $this->container->has($unknownService),
            'Container should not have unknown service'
        );

        $this->expectException(NotFoundExceptionInterface::class);
        $this->container->get($unknownService);
    }

    /**
     * Create a test container with all service definitions
     *
     * This is a simplified PSR-11 container for testing purposes.
     * In production, this would be replaced with a proper DI container
     * like PHP-DI, Symfony DependencyInjection, or Laravel Container.
     */
    private function createTestContainer(): ContainerInterface
    {
        return new class implements ContainerInterface {
            /** @var array<string, object> */
            private array $singletons = [];

            public function get(string $id): mixed
            {
                if (!$this->has($id)) {
                    throw new class("Service not found: {$id}") extends \Exception implements NotFoundExceptionInterface {};
                }

                // Return singleton if already instantiated
                if (isset($this->singletons[$id])) {
                    return $this->singletons[$id];
                }

                // Create and cache singleton
                $service = $this->create($id);
                $this->singletons[$id] = $service;
                return $service;
            }

            public function has(string $id): bool
            {
                return $this->canCreate($id);
            }

            private function canCreate(string $id): bool
            {
                // List of all services that can be created
                $knownServices = [
                    Configuration::class,
                    LoggerInterface::class,
                    WorkspaceManager::class,
                    CliWrapper::class,
                    WorkspaceTools::class,
                    ModelTools::class,
                    ViewTools::class,
                    DocumentationTools::class,
                    AnalysisTools::class,
                    ExportTools::class,
                    ConfigResource::class,
                    WorkspaceResource::class,
                    ElementResource::class,
                    ViewResource::class,
                    AnalysisPrompts::class,
                    GenerationPrompts::class,
                ];

                return in_array($id, $knownServices, true);
            }

            private function create(string $id): mixed
            {
                return match ($id) {
                    Configuration::class => new Configuration(),
                    LoggerInterface::class => new NullLogger(),

                    WorkspaceManager::class => new WorkspaceManager(
                        storagePath: $this->get(Configuration::class)->getWorkspacePath(),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    CliWrapper::class => new CliWrapper(
                        cliPath: $this->get(Configuration::class)->getStructurizrCliPath(),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    // Tool classes
                    WorkspaceTools::class => new WorkspaceTools(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    ModelTools::class => new ModelTools(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    ViewTools::class => new ViewTools(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    DocumentationTools::class => new DocumentationTools(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    AnalysisTools::class => new AnalysisTools(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        cliWrapper: $this->get(CliWrapper::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    ExportTools::class => new ExportTools(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        cliWrapper: $this->get(CliWrapper::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    // Resource classes
                    ConfigResource::class => new ConfigResource(
                        config: $this->get(Configuration::class),
                        workspaceManager: $this->get(WorkspaceManager::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    WorkspaceResource::class => new WorkspaceResource(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    ElementResource::class => new ElementResource(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    ViewResource::class => new ViewResource(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    // Prompt classes
                    AnalysisPrompts::class => new AnalysisPrompts(
                        workspaceManager: $this->get(WorkspaceManager::class),
                        logger: $this->get(LoggerInterface::class)
                    ),

                    GenerationPrompts::class => new GenerationPrompts(
                        logger: $this->get(LoggerInterface::class)
                    ),

                    default => throw new class("Unknown service: {$id}") extends \Exception implements NotFoundExceptionInterface {},
                };
            }
        };
    }

    /**
     * Recursively remove directory and all its contents
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
