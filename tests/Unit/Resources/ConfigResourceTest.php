<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Resources;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Configuration;
use StructurizrMcp\Resources\ConfigResource;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * Unit tests for ConfigResource
 *
 * @covers \StructurizrMcp\Resources\ConfigResource
 */
class ConfigResourceTest extends TestCase
{
    private Configuration&MockObject $config;
    private WorkspaceManager&MockObject $workspaceManager;
    private LoggerInterface&MockObject $logger;
    private ConfigResource $resource;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Configuration::class);
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resource = new ConfigResource($this->config, $this->workspaceManager, $this->logger);
    }

    public function testGetConfig(): void
    {
        // Configure mock expectations
        $this->config->method('getServerName')->willReturn('test-server');
        $this->config->method('getServerVersion')->willReturn('1.0.0');
        $this->config->method('getStructurizrCliPath')->willReturn('/path/to/cli');
        $this->config->method('getStructurizrApiUrl')->willReturn('https://api.structurizr.com');
        $this->config->method('getWorkspacePath')->willReturn('/path/to/workspaces');
        $this->config->method('getLogLevel')->willReturn('DEBUG');
        $this->config->method('getLogPath')->willReturn('php://stderr');

        $this->workspaceManager
            ->expects($this->once())
            ->method('list')
            ->willReturn([
                ['id' => 'ws_1', 'name' => 'Workspace 1'],
                ['id' => 'ws_2', 'name' => 'Workspace 2'],
            ]);

        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with('Retrieving server configuration');

        $result = $this->resource->getConfig();

        // Assert structure and values
        $this->assertIsArray($result);
        $this->assertArrayHasKey('server', $result);
        $this->assertArrayHasKey('structurizr', $result);
        $this->assertArrayHasKey('storage', $result);
        $this->assertArrayHasKey('logging', $result);

        $this->assertEquals('test-server', $result['server']['name']);
        $this->assertEquals('1.0.0', $result['server']['version']);
        $this->assertEquals('/path/to/cli', $result['structurizr']['cliPath']);
        $this->assertEquals('https://api.structurizr.com', $result['structurizr']['apiUrl']);
        $this->assertEquals('/path/to/workspaces', $result['storage']['workspacePath']);
        $this->assertEquals(2, $result['storage']['workspaceCount']);
        $this->assertEquals('DEBUG', $result['logging']['level']);
        $this->assertEquals('php://stderr', $result['logging']['path']);
    }

    public function testGetConfigWithNoWorkspaces(): void
    {
        // Configure mock expectations
        $this->config->method('getServerName')->willReturn('test-server');
        $this->config->method('getServerVersion')->willReturn('1.0.0');
        $this->config->method('getStructurizrCliPath')->willReturn('/path/to/cli');
        $this->config->method('getStructurizrApiUrl')->willReturn('https://api.structurizr.com');
        $this->config->method('getWorkspacePath')->willReturn('/path/to/workspaces');
        $this->config->method('getLogLevel')->willReturn('INFO');
        $this->config->method('getLogPath')->willReturn('/var/log/app.log');

        $this->workspaceManager
            ->expects($this->once())
            ->method('list')
            ->willReturn([]);

        $result = $this->resource->getConfig();

        // Assert workspace count is 0
        $this->assertEquals(0, $result['storage']['workspaceCount']);
    }

    public function testGetConfigIncludesAllRequiredFields(): void
    {
        // Configure mock expectations
        $this->config->method('getServerName')->willReturn('server');
        $this->config->method('getServerVersion')->willReturn('1.0');
        $this->config->method('getStructurizrCliPath')->willReturn('/cli');
        $this->config->method('getStructurizrApiUrl')->willReturn('https://api.test');
        $this->config->method('getWorkspacePath')->willReturn('/workspaces');
        $this->config->method('getLogLevel')->willReturn('ERROR');
        $this->config->method('getLogPath')->willReturn('/log');

        $this->workspaceManager->method('list')->willReturn([]);

        $result = $this->resource->getConfig();

        // Verify all required fields are present
        $requiredFields = [
            'server' => ['name', 'version'],
            'structurizr' => ['cliPath', 'apiUrl'],
            'storage' => ['workspacePath', 'workspaceCount'],
            'logging' => ['level', 'path'],
        ];

        foreach ($requiredFields as $section => $fields) {
            $this->assertArrayHasKey($section, $result);
            foreach ($fields as $field) {
                $this->assertArrayHasKey($field, $result[$section], "Missing field: {$section}.{$field}");
            }
        }
    }
}
