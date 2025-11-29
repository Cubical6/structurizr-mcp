<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Structurizr\Executor;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use StructurizrMcp\Structurizr\Executor\LocalCliExecutor;
use StructurizrMcp\Structurizr\ProcessResult;

/**
 * Unit tests for LocalCliExecutor
 *
 * @covers \StructurizrMcp\Structurizr\Executor\LocalCliExecutor
 */
class LocalCliExecutorTest extends TestCase
{
    private string $tempCliPath;
    private string $tempOutputDir;
    private NullLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new NullLogger();

        // Create temporary output directory
        $this->tempOutputDir = sys_get_temp_dir() . '/executor-test-' . uniqid();
        mkdir($this->tempOutputDir);

        // Create temporary CLI executable for testing
        $this->tempCliPath = $this->tempOutputDir . '/structurizr-cli';
        file_put_contents($this->tempCliPath, "#!/bin/sh\necho 'test'\nexit 0");
        chmod($this->tempCliPath, 0o755);
    }

    protected function tearDown(): void
    {
        // Clean up temporary files
        if (file_exists($this->tempCliPath)) {
            unlink($this->tempCliPath);
        }

        $files = glob($this->tempOutputDir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        if (is_dir($this->tempOutputDir)) {
            rmdir($this->tempOutputDir);
        }
    }

    public function testIsAvailableReturnsFalseWhenPathIsNull(): void
    {
        $executor = new LocalCliExecutor(null, $this->logger);

        $this->assertFalse($executor->isAvailable());
    }

    public function testIsAvailableReturnsFalseWhenPathIsEmpty(): void
    {
        $executor = new LocalCliExecutor('', $this->logger);

        $this->assertFalse($executor->isAvailable());
    }

    public function testIsAvailableReturnsFalseWhenPathDoesNotExist(): void
    {
        $executor = new LocalCliExecutor('/nonexistent/path/to/cli', $this->logger);

        $this->assertFalse($executor->isAvailable());
    }

    public function testIsAvailableReturnsFalseWhenPathIsNotExecutable(): void
    {
        // Create a non-executable file
        $nonExecutable = $this->tempOutputDir . '/non-executable';
        file_put_contents($nonExecutable, 'not executable');
        chmod($nonExecutable, 0o644);

        $executor = new LocalCliExecutor($nonExecutable, $this->logger);

        $this->assertFalse($executor->isAvailable());

        unlink($nonExecutable);
    }

    public function testIsAvailableReturnsTrueWhenExecutableExists(): void
    {
        $executor = new LocalCliExecutor($this->tempCliPath, $this->logger);

        $this->assertTrue($executor->isAvailable());
    }

    public function testGetNameReturnsLocal(): void
    {
        $executor = new LocalCliExecutor($this->tempCliPath, $this->logger);

        $this->assertEquals('local', $executor->getName());
    }

    public function testExecuteReturnsProcessResult(): void
    {
        $executor = new LocalCliExecutor($this->tempCliPath, $this->logger);

        // Must call isAvailable first to resolve the path
        $this->assertTrue($executor->isAvailable());

        $result = $executor->execute(['version'], 30);

        $this->assertInstanceOf(ProcessResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(0, $result->getExitCode());
        $this->assertStringContainsString('test', $result->getStdout());
    }

    public function testExecuteWithWorkingDirectory(): void
    {
        $executor = new LocalCliExecutor($this->tempCliPath, $this->logger);
        $this->assertTrue($executor->isAvailable());

        $result = $executor->execute(['version'], 30, $this->tempOutputDir);

        $this->assertInstanceOf(ProcessResult::class, $result);
        $this->assertTrue($result->isSuccess());
    }

    public function testExecuteThrowsWhenNotAvailable(): void
    {
        $executor = new LocalCliExecutor('/nonexistent/path', $this->logger);

        // Don't call isAvailable - go straight to execute
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LocalCliExecutor is not available');

        $executor->execute(['version'], 30);
    }

    public function testExecuteWithFailingCommand(): void
    {
        // Create a script that fails
        $failingCli = $this->tempOutputDir . '/failing-cli';
        file_put_contents($failingCli, "#!/bin/sh\necho 'error' >&2\nexit 1");
        chmod($failingCli, 0o755);

        $executor = new LocalCliExecutor($failingCli, $this->logger);
        $this->assertTrue($executor->isAvailable());

        $result = $executor->execute(['version'], 30);

        $this->assertFalse($result->isSuccess());
        $this->assertEquals(1, $result->getExitCode());
        $this->assertStringContainsString('error', $result->getStderr());

        unlink($failingCli);
    }
}
