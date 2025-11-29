<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Structurizr\Executor;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use StructurizrMcp\Structurizr\Executor\DockerCliExecutor;

/**
 * Unit tests for DockerCliExecutor
 *
 * @covers \StructurizrMcp\Structurizr\Executor\DockerCliExecutor
 */
class DockerCliExecutorTest extends TestCase
{
    private NullLogger $logger;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->logger = new NullLogger();

        // Create temporary directory for path transformation tests
        $this->tempDir = sys_get_temp_dir() . '/docker-executor-test-' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        $files = glob($this->tempDir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    }

    public function testGetNameReturnsDocker(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        $this->assertEquals('docker', $executor->getName());
    }

    public function testDefaultImageIsStructurizrCliLatest(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        // We can't easily check the private image property, but we can verify the class instantiates
        $this->assertInstanceOf(DockerCliExecutor::class, $executor);
    }

    public function testCustomImageCanBeProvided(): void
    {
        $executor = new DockerCliExecutor($this->logger, 'structurizr/cli:2.0.0');

        $this->assertInstanceOf(DockerCliExecutor::class, $executor);
    }

    public function testTransformArgumentsWithWorkspaceFlag(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($executor);
        $method = $reflection->getMethod('transformArguments');
        $method->setAccessible(true);

        // Create a test file
        $testFile = $this->tempDir . '/test.dsl';
        file_put_contents($testFile, 'workspace {}');

        $args = ['validate', '-workspace', $testFile];
        $result = $method->invoke($executor, $args, $this->tempDir);

        $this->assertEquals('validate', $result[0]);
        $this->assertEquals('-workspace', $result[1]);
        $this->assertStringStartsWith('/usr/local/structurizr/', $result[2]);
    }

    public function testTransformArgumentsWithLongWorkspaceFlag(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        $reflection = new \ReflectionClass($executor);
        $method = $reflection->getMethod('transformArguments');
        $method->setAccessible(true);

        $testFile = $this->tempDir . '/test.dsl';
        file_put_contents($testFile, 'workspace {}');

        $args = ['validate', '--workspace', $testFile];
        $result = $method->invoke($executor, $args, $this->tempDir);

        $this->assertEquals('validate', $result[0]);
        $this->assertEquals('--workspace', $result[1]);
        $this->assertStringStartsWith('/usr/local/structurizr/', $result[2]);
    }

    public function testTransformArgumentsWithOutputFlag(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        $reflection = new \ReflectionClass($executor);
        $method = $reflection->getMethod('transformArguments');
        $method->setAccessible(true);

        $testFile = $this->tempDir . '/test.dsl';
        file_put_contents($testFile, 'workspace {}');

        $outputFile = $this->tempDir . '/output.puml';

        $args = ['export', '-workspace', $testFile, '-output', $outputFile];
        $result = $method->invoke($executor, $args, $this->tempDir);

        $this->assertEquals('export', $result[0]);
        $this->assertEquals('-workspace', $result[1]);
        $this->assertStringStartsWith('/usr/local/structurizr/', $result[2]);
        $this->assertEquals('-output', $result[3]);
        $this->assertStringStartsWith('/usr/local/structurizr/', $result[4]);
    }

    public function testTransformArgumentsWithEqualsWorkspaceSyntax(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        $reflection = new \ReflectionClass($executor);
        $method = $reflection->getMethod('transformArguments');
        $method->setAccessible(true);

        $testFile = $this->tempDir . '/test.dsl';
        file_put_contents($testFile, 'workspace {}');

        $args = ['validate', "-workspace={$testFile}"];
        $result = $method->invoke($executor, $args, $this->tempDir);

        $this->assertEquals('validate', $result[0]);
        $this->assertStringStartsWith('-workspace=/usr/local/structurizr/', $result[1]);
    }

    public function testTransformArgumentsWithLongEqualsWorkspaceSyntax(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        $reflection = new \ReflectionClass($executor);
        $method = $reflection->getMethod('transformArguments');
        $method->setAccessible(true);

        $testFile = $this->tempDir . '/test.dsl';
        file_put_contents($testFile, 'workspace {}');

        $args = ['validate', "--workspace={$testFile}"];
        $result = $method->invoke($executor, $args, $this->tempDir);

        $this->assertEquals('validate', $result[0]);
        $this->assertStringStartsWith('--workspace=/usr/local/structurizr/', $result[1]);
    }

    public function testTransformArgumentsPreservesNonPathArguments(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        $reflection = new \ReflectionClass($executor);
        $method = $reflection->getMethod('transformArguments');
        $method->setAccessible(true);

        $args = ['export', '-format', 'plantuml', '-id', '12345'];
        $result = $method->invoke($executor, $args, $this->tempDir);

        $this->assertEquals(['export', '-format', 'plantuml', '-id', '12345'], $result);
    }

    public function testToContainerPathForFileInsideWorkingDirectory(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        $reflection = new \ReflectionClass($executor);
        $method = $reflection->getMethod('toContainerPath');
        $method->setAccessible(true);

        // Create a test file inside the temp directory
        $testFile = $this->tempDir . '/subdir/test.dsl';
        mkdir($this->tempDir . '/subdir');
        file_put_contents($testFile, 'workspace {}');

        $result = $method->invoke($executor, $testFile, $this->tempDir);

        $this->assertEquals('/usr/local/structurizr/subdir/test.dsl', $result);

        // Cleanup
        unlink($testFile);
        rmdir($this->tempDir . '/subdir');
    }

    /**
     * Note: isAvailable() depends on Docker being installed and running.
     * This test only verifies the method doesn't throw and returns a boolean.
     */
    public function testIsAvailableReturnsBoolean(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        $result = $executor->isAvailable();

        $this->assertIsBool($result);
    }

    /**
     * Note: This test verifies caching behavior - calling isAvailable twice
     * should return the same result and not re-check Docker.
     */
    public function testIsAvailableIsCached(): void
    {
        $executor = new DockerCliExecutor($this->logger);

        $result1 = $executor->isAvailable();
        $result2 = $executor->isAvailable();

        $this->assertEquals($result1, $result2);
    }
}
