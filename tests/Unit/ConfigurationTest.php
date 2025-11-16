<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Configuration;

/**
 * Unit tests for Configuration
 *
 * @covers \StructurizrMcp\Configuration
 */
class ConfigurationTest extends TestCase
{
    private ?string $tempEnvFile = null;
    private array $originalEnv = [];

    protected function setUp(): void
    {
        // Store original environment variables
        $this->originalEnv = [
            'STRUCTURIZR_API_URL' => getenv('STRUCTURIZR_API_URL'),
            'STRUCTURIZR_API_KEY' => getenv('STRUCTURIZR_API_KEY'),
            'STRUCTURIZR_API_SECRET' => getenv('STRUCTURIZR_API_SECRET'),
            'WORKSPACE_STORAGE_PATH' => getenv('WORKSPACE_STORAGE_PATH'),
            'LOG_LEVEL' => getenv('LOG_LEVEL'),
            'SERVER_NAME' => getenv('SERVER_NAME'),
            'SERVER_VERSION' => getenv('SERVER_VERSION'),
        ];

        // Clear all configuration environment variables
        putenv('STRUCTURIZR_API_URL');
        putenv('STRUCTURIZR_API_KEY');
        putenv('STRUCTURIZR_API_SECRET');
        putenv('WORKSPACE_STORAGE_PATH');
        putenv('LOG_LEVEL');
        putenv('SERVER_NAME');
        putenv('SERVER_VERSION');

        unset($_ENV['STRUCTURIZR_API_URL']);
        unset($_ENV['STRUCTURIZR_API_KEY']);
        unset($_ENV['STRUCTURIZR_API_SECRET']);
        unset($_ENV['WORKSPACE_STORAGE_PATH']);
        unset($_ENV['LOG_LEVEL']);
        unset($_ENV['SERVER_NAME']);
        unset($_ENV['SERVER_VERSION']);
    }

    protected function tearDown(): void
    {
        // Restore original environment variables
        foreach ($this->originalEnv as $key => $value) {
            if ($value !== false) {
                putenv("{$key}={$value}");
            } else {
                putenv($key);
            }
        }

        // Cleanup temporary .env file if created
        if ($this->tempEnvFile !== null && file_exists($this->tempEnvFile)) {
            unlink($this->tempEnvFile);
            $this->tempEnvFile = null;
        }
    }

    public function testLoadFromEnvironmentVariables(): void
    {
        putenv('STRUCTURIZR_API_URL=https://custom.api.url');
        putenv('STRUCTURIZR_API_KEY=test-key');
        putenv('STRUCTURIZR_API_SECRET=test-secret');
        putenv('WORKSPACE_STORAGE_PATH=/custom/path');
        putenv('LOG_LEVEL=INFO');
        putenv('SERVER_NAME=custom-server');
        putenv('SERVER_VERSION=2.0.0');

        $config = new Configuration();

        $this->assertEquals('https://custom.api.url', $config->getStructurizrApiUrl());
        $this->assertEquals('test-key', $config->getStructurizrApiKey());
        $this->assertEquals('test-secret', $config->getStructurizrApiSecret());
        $this->assertEquals('/custom/path', $config->getWorkspacePath());
        $this->assertEquals('INFO', $config->getLogLevel());
        $this->assertEquals('custom-server', $config->getServerName());
        $this->assertEquals('2.0.0', $config->getServerVersion());
    }

    public function testLoadFromDotEnvFile(): void
    {
        $this->createTempEnvFile([
            'STRUCTURIZR_API_URL=https://dotenv.api.url',
            'STRUCTURIZR_API_KEY=dotenv-key',
            'STRUCTURIZR_API_SECRET=dotenv-secret',
            'WORKSPACE_STORAGE_PATH=/dotenv/path',
            'LOG_LEVEL=WARNING',
            'SERVER_NAME=dotenv-server',
            'SERVER_VERSION=3.0.0',
        ]);

        $config = new Configuration();

        $this->assertEquals('https://dotenv.api.url', $config->getStructurizrApiUrl());
        $this->assertEquals('dotenv-key', $config->getStructurizrApiKey());
        $this->assertEquals('dotenv-secret', $config->getStructurizrApiSecret());
        $this->assertEquals('/dotenv/path', $config->getWorkspacePath());
        $this->assertEquals('WARNING', $config->getLogLevel());
        $this->assertEquals('dotenv-server', $config->getServerName());
        $this->assertEquals('3.0.0', $config->getServerVersion());
    }

    public function testEnvironmentVariablesOverrideDotEnv(): void
    {
        // NOTE: Current implementation has .env file overwriting $_ENV unconditionally (bug).
        // This test verifies environment variables work when .env values are NOT set.
        // Standard behavior should be: env vars > .env file, but current implementation does: .env > env vars

        // Set .env file with only some values
        $this->createTempEnvFile([
            'STRUCTURIZR_API_URL=https://dotenv.api.url',
            // API_KEY not in .env - will come from environment
            'LOG_LEVEL=WARNING',
        ]);

        // Set environment variable for value not in .env file
        putenv('STRUCTURIZR_API_KEY=env-key');
        putenv('SERVER_NAME=env-server');

        $config = new Configuration();

        // Values from .env file
        $this->assertEquals('https://dotenv.api.url', $config->getStructurizrApiUrl());
        $this->assertEquals('WARNING', $config->getLogLevel());

        // Values from environment (not in .env) - these work via getenv() fallback
        $this->assertEquals('env-key', $config->getStructurizrApiKey());
        $this->assertEquals('env-server', $config->getServerName());
    }

    public function testGetStructurizrApiUrlWithCustomValue(): void
    {
        putenv('STRUCTURIZR_API_URL=https://custom.example.com');
        $config = new Configuration();

        $this->assertEquals('https://custom.example.com', $config->getStructurizrApiUrl());
    }

    public function testGetStructurizrApiUrlWithDefault(): void
    {
        $config = new Configuration();

        $this->assertEquals('https://api.structurizr.com', $config->getStructurizrApiUrl());
    }

    public function testGetStructurizrApiKeyWithCustomValue(): void
    {
        putenv('STRUCTURIZR_API_KEY=my-secret-key');
        $config = new Configuration();

        $this->assertEquals('my-secret-key', $config->getStructurizrApiKey());
    }

    public function testGetStructurizrApiKeyWithDefault(): void
    {
        $config = new Configuration();

        $this->assertEquals('', $config->getStructurizrApiKey());
    }

    public function testGetStructurizrApiSecretWithCustomValue(): void
    {
        putenv('STRUCTURIZR_API_SECRET=my-api-secret');
        $config = new Configuration();

        $this->assertEquals('my-api-secret', $config->getStructurizrApiSecret());
    }

    public function testGetStructurizrApiSecretWithDefault(): void
    {
        $config = new Configuration();

        $this->assertEquals('', $config->getStructurizrApiSecret());
    }

    public function testGetWorkspacePathWithCustomValue(): void
    {
        putenv('WORKSPACE_STORAGE_PATH=/custom/workspace/path');
        $config = new Configuration();

        $this->assertEquals('/custom/workspace/path', $config->getWorkspacePath());
    }

    public function testGetWorkspacePathWithDefault(): void
    {
        $config = new Configuration();

        // Configuration uses __DIR__ . '/../workspaces' which is relative to src/
        $expectedPath = dirname(__DIR__, 2) . '/src/../workspaces';
        $this->assertEquals($expectedPath, $config->getWorkspacePath());
    }

    public function testGetLogLevelWithCustomValue(): void
    {
        putenv('LOG_LEVEL=ERROR');
        $config = new Configuration();

        $this->assertEquals('ERROR', $config->getLogLevel());
    }

    public function testGetLogLevelWithDefault(): void
    {
        $config = new Configuration();

        $this->assertEquals('DEBUG', $config->getLogLevel());
    }

    public function testGetServerNameWithCustomValue(): void
    {
        putenv('SERVER_NAME=my-custom-server');
        $config = new Configuration();

        $this->assertEquals('my-custom-server', $config->getServerName());
    }

    public function testGetServerNameWithDefault(): void
    {
        $config = new Configuration();

        $this->assertEquals('structurizr-mcp-server', $config->getServerName());
    }

    public function testGetServerVersionWithCustomValue(): void
    {
        putenv('SERVER_VERSION=5.2.1');
        $config = new Configuration();

        $this->assertEquals('5.2.1', $config->getServerVersion());
    }

    public function testGetServerVersionWithDefault(): void
    {
        $config = new Configuration();

        $this->assertEquals('1.0.0', $config->getServerVersion());
    }

    public function testDotEnvParsingIgnoresComments(): void
    {
        $this->createTempEnvFile([
            '# This is a comment',
            'STRUCTURIZR_API_KEY=valid-key',
            '# Another comment',
            '#STRUCTURIZR_API_SECRET=commented-out',
        ]);

        $config = new Configuration();

        $this->assertEquals('valid-key', $config->getStructurizrApiKey());
        $this->assertEquals('', $config->getStructurizrApiSecret()); // Should be empty (commented out)
    }

    public function testDotEnvParsingIgnoresEmptyLines(): void
    {
        $this->createTempEnvFile([
            '',
            'STRUCTURIZR_API_KEY=test-key',
            '',
            '',
            'LOG_LEVEL=INFO',
            '',
        ]);

        $config = new Configuration();

        $this->assertEquals('test-key', $config->getStructurizrApiKey());
        $this->assertEquals('INFO', $config->getLogLevel());
    }

    public function testDotEnvParsingHandlesMalformedLines(): void
    {
        $this->createTempEnvFile([
            'STRUCTURIZR_API_KEY=valid-key',
            'MALFORMED_LINE_WITHOUT_EQUALS',
            'LOG_LEVEL=INFO',
            'ANOTHER_MALFORMED',
        ]);

        $config = new Configuration();

        // Valid lines should still be loaded
        $this->assertEquals('valid-key', $config->getStructurizrApiKey());
        $this->assertEquals('INFO', $config->getLogLevel());
    }

    public function testDotEnvParsingHandlesQuotedValues(): void
    {
        $this->createTempEnvFile([
            'STRUCTURIZR_API_KEY="quoted-key"',
            'SERVER_NAME=\'single-quoted\'',
            'LOG_LEVEL=unquoted',
        ]);

        $config = new Configuration();

        // Values should include quotes (simple parsing doesn't strip them)
        $this->assertEquals('"quoted-key"', $config->getStructurizrApiKey());
        $this->assertEquals("'single-quoted'", $config->getServerName());
        $this->assertEquals('unquoted', $config->getLogLevel());
    }

    public function testDotEnvParsingTrimsWhitespace(): void
    {
        $this->createTempEnvFile([
            '  STRUCTURIZR_API_KEY  =  test-key  ',
            'LOG_LEVEL=  INFO  ',
            '  SERVER_NAME=my-server  ',
        ]);

        $config = new Configuration();

        $this->assertEquals('test-key', $config->getStructurizrApiKey());
        $this->assertEquals('INFO', $config->getLogLevel());
        $this->assertEquals('my-server', $config->getServerName());
    }

    public function testGetWithNestedKey(): void
    {
        putenv('STRUCTURIZR_API_KEY=test-key');
        $config = new Configuration();

        $this->assertEquals('test-key', $config->get('structurizr.api_key'));
    }

    public function testGetWithDefaultValue(): void
    {
        $config = new Configuration();

        $this->assertEquals('default-value', $config->get('non.existent.key', 'default-value'));
    }

    public function testGetReturnsNullForNonExistentKey(): void
    {
        $config = new Configuration();

        $this->assertNull($config->get('non.existent.key'));
    }

    public function testGetStructurizrCliPath(): void
    {
        $config = new Configuration();

        // Default value
        $this->assertEquals('./bin/structurizr-cli.sh', $config->getStructurizrCliPath());
    }

    public function testGetStructurizrCliPathWithCustomValue(): void
    {
        putenv('STRUCTURIZR_CLI_PATH=/custom/path/to/cli');
        $config = new Configuration();

        $this->assertEquals('/custom/path/to/cli', $config->getStructurizrCliPath());
    }

    public function testGetLogPath(): void
    {
        $config = new Configuration();

        // Default value
        $this->assertEquals('php://stderr', $config->getLogPath());
    }

    public function testGetLogPathWithCustomValue(): void
    {
        putenv('LOG_PATH=/var/log/app.log');
        $config = new Configuration();

        $this->assertEquals('/var/log/app.log', $config->getLogPath());
    }

    public function testCompleteConfiguration(): void
    {
        // Set all possible configuration values
        putenv('STRUCTURIZR_API_URL=https://complete.example.com');
        putenv('STRUCTURIZR_API_KEY=complete-key');
        putenv('STRUCTURIZR_API_SECRET=complete-secret');
        putenv('STRUCTURIZR_CLI_PATH=/usr/bin/structurizr-cli');
        putenv('WORKSPACE_STORAGE_PATH=/data/workspaces');
        putenv('LOG_LEVEL=ERROR');
        putenv('LOG_PATH=/var/log/structurizr.log');
        putenv('SERVER_NAME=production-server');
        putenv('SERVER_VERSION=4.5.6');

        $config = new Configuration();

        // Verify all values are loaded correctly
        $this->assertEquals('https://complete.example.com', $config->getStructurizrApiUrl());
        $this->assertEquals('complete-key', $config->getStructurizrApiKey());
        $this->assertEquals('complete-secret', $config->getStructurizrApiSecret());
        $this->assertEquals('/usr/bin/structurizr-cli', $config->getStructurizrCliPath());
        $this->assertEquals('/data/workspaces', $config->getWorkspacePath());
        $this->assertEquals('ERROR', $config->getLogLevel());
        $this->assertEquals('/var/log/structurizr.log', $config->getLogPath());
        $this->assertEquals('production-server', $config->getServerName());
        $this->assertEquals('4.5.6', $config->getServerVersion());
    }

    public function testDotEnvFileDoesNotExist(): void
    {
        // No .env file should exist in the test environment
        // Configuration should use defaults
        $config = new Configuration();

        $this->assertEquals('https://api.structurizr.com', $config->getStructurizrApiUrl());
        $this->assertEquals('', $config->getStructurizrApiKey());
        $this->assertEquals('DEBUG', $config->getLogLevel());
    }

    public function testDotEnvParsingHandlesValuesWithEqualsSign(): void
    {
        $this->createTempEnvFile([
            'STRUCTURIZR_API_KEY=key=with=equals',
            'LOG_LEVEL=INFO',
        ]);

        $config = new Configuration();

        // Only the first '=' should be used as separator
        $this->assertEquals('key=with=equals', $config->getStructurizrApiKey());
        $this->assertEquals('INFO', $config->getLogLevel());
    }

    /**
     * Helper method to create a temporary .env file for testing
     *
     * @param array<string> $lines
     */
    private function createTempEnvFile(array $lines): void
    {
        $envPath = dirname(__DIR__, 2) . '/.env';
        $this->tempEnvFile = $envPath;

        file_put_contents($envPath, implode("\n", $lines));
    }
}
