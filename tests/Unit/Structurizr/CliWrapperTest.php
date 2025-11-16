<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Structurizr;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Structurizr\ProcessResult;
use StructurizrMcp\Structurizr\ValidationResult;
use StructurizrMcp\Exception\CliExecutionException;

/**
 * Unit tests for CliWrapper
 *
 * @covers \StructurizrMcp\Structurizr\CliWrapper
 */
class CliWrapperTest extends TestCase
{
    private string $tempCliPath;
    private string $tempDslPath;
    private string $tempWorkspacePath;
    private string $tempOutputDir;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        // Create temporary CLI executable for testing
        $this->tempCliPath = sys_get_temp_dir() . '/structurizr-cli-' . uniqid();
        file_put_contents($this->tempCliPath, '#!/bin/sh' . PHP_EOL . 'echo "test"');
        chmod($this->tempCliPath, 0755);

        // Create temporary DSL file
        $this->tempDslPath = sys_get_temp_dir() . '/test-workspace-' . uniqid() . '.dsl';
        file_put_contents($this->tempDslPath, 'workspace "Test" { }');

        // Create temporary workspace JSON file
        $this->tempWorkspacePath = sys_get_temp_dir() . '/test-workspace-' . uniqid() . '.json';
        file_put_contents($this->tempWorkspacePath, json_encode(['name' => 'Test']));

        // Create temporary output directory
        $this->tempOutputDir = sys_get_temp_dir() . '/structurizr-output-' . uniqid();
        mkdir($this->tempOutputDir);

        $this->logger = new NullLogger();
    }

    protected function tearDown(): void
    {
        // Clean up temporary files
        if (file_exists($this->tempCliPath)) {
            unlink($this->tempCliPath);
        }
        if (file_exists($this->tempDslPath)) {
            unlink($this->tempDslPath);
        }
        if (file_exists($this->tempWorkspacePath)) {
            unlink($this->tempWorkspacePath);
        }
        if (is_dir($this->tempOutputDir)) {
            $files = glob($this->tempOutputDir . '/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            rmdir($this->tempOutputDir);
        }
    }

    // ========================================
    // Constructor Tests
    // ========================================

    public function testConstructorValidatesCliPath(): void
    {
        $this->expectException(CliExecutionException::class);
        $this->expectExceptionMessage('CLI executable not found at path: /nonexistent/path/to/cli');

        new CliWrapper('/nonexistent/path/to/cli', $this->logger);
    }

    public function testConstructorValidatesExecutable(): void
    {
        // Create a non-executable file
        $nonExecutable = sys_get_temp_dir() . '/non-executable-' . uniqid();
        file_put_contents($nonExecutable, 'not executable');
        chmod($nonExecutable, 0644);

        try {
            $this->expectException(CliExecutionException::class);
            $this->expectExceptionMessage('CLI path is not executable:');

            new CliWrapper($nonExecutable, $this->logger);
        } finally {
            if (file_exists($nonExecutable)) {
                unlink($nonExecutable);
            }
        }
    }

    public function testConstructorAcceptsValidCliPath(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        $this->assertInstanceOf(CliWrapper::class, $wrapper);
    }

    // ========================================
    // Execute Command Tests
    // ========================================

    public function testExecuteCommandSuccess(): void
    {
        // Create a script that exits successfully
        $tempCli = $this->tempOutputDir . '/success-cli';
        file_put_contents($tempCli, "#!/bin/sh\necho 'Success output'\nexit 0");
        chmod($tempCli, 0755);

        $wrapper = new CliWrapper($tempCli, $this->logger);
        $result = $wrapper->executeCommand(['version'], 30);

        $this->assertInstanceOf(ProcessResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(0, $result->getExitCode());
        $this->assertStringContainsString('Success output', $result->getStdout());
    }

    public function testExecuteCommandFailure(): void
    {
        // Create a script that fails
        $tempCli = $this->tempOutputDir . '/fail-cli';
        file_put_contents($tempCli, "#!/bin/sh\necho 'Error message' >&2\nexit 1");
        chmod($tempCli, 0755);

        $wrapper = new CliWrapper($tempCli, $this->logger);
        $result = $wrapper->executeCommand(['invalid'], 30);

        $this->assertFalse($result->isSuccess());
        $this->assertEquals(1, $result->getExitCode());
        $this->assertStringContainsString('Error message', $result->getStderr());
    }

    public function testExecuteCommandTimeout(): void
    {
        // Create a script that sleeps longer than the timeout
        $tempCli = $this->tempOutputDir . '/slow-cli';
        file_put_contents($tempCli, "#!/bin/sh\nsleep 10\necho 'Done'\nexit 0");
        chmod($tempCli, 0755);

        $wrapper = new CliWrapper($tempCli, $this->logger);

        $this->expectException(CliExecutionException::class);

        // Set a very short timeout
        $wrapper->executeCommand(['test'], 1);
    }

    // ========================================
    // Validate Tests
    // ========================================

    public function testValidateCommandSuccess(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 0,
            stdout: 'Workspace is valid',
            stderr: '',
            success: true
        );

        $wrapper->setMockedResult($result);

        $validationResult = $wrapper->validate($this->tempDslPath);

        $this->assertInstanceOf(ValidationResult::class, $validationResult);
        $this->assertTrue($validationResult->isValid());
        $this->assertEmpty($validationResult->getErrors());
        $this->assertEmpty($validationResult->getWarnings());
    }

    public function testValidateCommandWithErrors(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 1,
            stdout: "ERROR: Syntax error on line 5\nERROR: Missing closing brace",
            stderr: '',
            success: false
        );

        $wrapper->setMockedResult($result);

        $validationResult = $wrapper->validate($this->tempDslPath);

        $this->assertFalse($validationResult->isValid());
        $this->assertCount(2, $validationResult->getErrors());
        $this->assertContains('Syntax error on line 5', $validationResult->getErrors());
        $this->assertContains('Missing closing brace', $validationResult->getErrors());
    }

    public function testValidateCommandWithWarnings(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 0,
            stdout: "WARNING: Unused element detected\nWARNING: Missing description for component",
            stderr: '',
            success: true
        );

        $wrapper->setMockedResult($result);

        $validationResult = $wrapper->validate($this->tempDslPath);

        $this->assertTrue($validationResult->isValid());
        $this->assertEmpty($validationResult->getErrors());
        $this->assertCount(2, $validationResult->getWarnings());
        $this->assertContains('Unused element detected', $validationResult->getWarnings());
        $this->assertContains('Missing description for component', $validationResult->getWarnings());
    }

    public function testValidateCommandFileNotFound(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        $this->expectException(CliExecutionException::class);
        $this->expectExceptionMessage('DSL file not found:');

        $wrapper->validate('/nonexistent/file.dsl');
    }

    // ========================================
    // Export Tests
    // ========================================

    public function testExportCommandSuccess(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $expectedOutput = '@startuml' . PHP_EOL . 'Test PlantUML' . PHP_EOL . '@enduml';
        $result = new ProcessResult(
            exitCode: 0,
            stdout: $expectedOutput,
            stderr: '',
            success: true
        );

        $wrapper->setMockedResult($result);

        $output = $wrapper->export($this->tempWorkspacePath, 'plantuml');

        $this->assertEquals($expectedOutput, $output);
    }

    public function testExportCommandWithOutputPath(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $outputPath = $this->tempOutputDir . '/output.puml';
        $result = new ProcessResult(
            exitCode: 0,
            stdout: '',
            stderr: '',
            success: true
        );

        $wrapper->setMockedResult($result);

        $returnedPath = $wrapper->export($this->tempWorkspacePath, 'plantuml', $outputPath);

        $this->assertEquals($outputPath, $returnedPath);
    }

    public function testExportCommandFailure(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 1,
            stdout: '',
            stderr: 'Export failed: Invalid format',
            success: false
        );

        $wrapper->setMockedResult($result);

        $this->expectException(CliExecutionException::class);
        $this->expectExceptionMessage('Export failed: Export failed: Invalid format');

        $wrapper->export($this->tempWorkspacePath, 'invalid-format');
    }

    public function testExportCommandWithInvalidOutputDirectory(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        $this->expectException(CliExecutionException::class);
        $this->expectExceptionMessage('Output directory does not exist:');

        $wrapper->export($this->tempWorkspacePath, 'plantuml', '/nonexistent/dir/output.puml');
    }

    // ========================================
    // Push Tests
    // ========================================

    public function testPushCommandSuccess(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 0,
            stdout: 'Workspace pushed successfully',
            stderr: '',
            success: true
        );

        $wrapper->setMockedResult($result);

        $pushResult = $wrapper->push(
            $this->tempWorkspacePath,
            12345,
            'test-key',
            'test-secret'
        );

        $this->assertTrue($pushResult->isSuccess());
        $this->assertEquals('Workspace pushed successfully', $pushResult->getStdout());

        // Verify the command was called with correct arguments
        $lastArgs = $wrapper->getLastCommandArgs();
        $this->assertContains('push', $lastArgs);
        $this->assertContains('-id', $lastArgs);
        $this->assertContains('12345', $lastArgs);
        $this->assertContains('-key', $lastArgs);
        $this->assertContains('-secret', $lastArgs);
    }

    public function testPushCommandWithCustomApiUrl(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 0,
            stdout: 'Workspace pushed successfully',
            stderr: '',
            success: true
        );

        $wrapper->setMockedResult($result);

        $wrapper->push(
            $this->tempWorkspacePath,
            12345,
            'test-key',
            'test-secret',
            'https://custom.structurizr.com'
        );

        $lastArgs = $wrapper->getLastCommandArgs();
        $this->assertContains('-url', $lastArgs);
        $this->assertContains('https://custom.structurizr.com', $lastArgs);
    }

    public function testPushCommandFailure(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 1,
            stdout: '',
            stderr: 'Authentication failed',
            success: false
        );

        $wrapper->setMockedResult($result);

        $this->expectException(CliExecutionException::class);
        $this->expectExceptionMessage('Push failed: Authentication failed');

        $wrapper->push(
            $this->tempWorkspacePath,
            12345,
            'invalid-key',
            'invalid-secret'
        );
    }

    // ========================================
    // Pull Tests
    // ========================================

    public function testPullCommandSuccess(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $outputPath = $this->tempOutputDir . '/pulled-workspace.json';
        $result = new ProcessResult(
            exitCode: 0,
            stdout: 'Workspace pulled successfully',
            stderr: '',
            success: true
        );

        $wrapper->setMockedResult($result);

        $pullResult = $wrapper->pull(
            12345,
            'test-key',
            'test-secret',
            $outputPath
        );

        $this->assertTrue($pullResult->isSuccess());
        $this->assertEquals('Workspace pulled successfully', $pullResult->getStdout());

        $lastArgs = $wrapper->getLastCommandArgs();
        $this->assertContains('pull', $lastArgs);
        $this->assertContains('-id', $lastArgs);
        $this->assertContains('12345', $lastArgs);
    }

    public function testPullCommandWithCustomApiUrl(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $outputPath = $this->tempOutputDir . '/pulled-workspace.json';
        $result = new ProcessResult(
            exitCode: 0,
            stdout: 'Workspace pulled successfully',
            stderr: '',
            success: true
        );

        $wrapper->setMockedResult($result);

        $wrapper->pull(
            12345,
            'test-key',
            'test-secret',
            $outputPath,
            'https://custom.structurizr.com'
        );

        $lastArgs = $wrapper->getLastCommandArgs();
        $this->assertContains('-url', $lastArgs);
        $this->assertContains('https://custom.structurizr.com', $lastArgs);
    }

    public function testPullCommandFailure(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $outputPath = $this->tempOutputDir . '/pulled-workspace.json';
        $result = new ProcessResult(
            exitCode: 1,
            stdout: '',
            stderr: 'Workspace not found',
            success: false
        );

        $wrapper->setMockedResult($result);

        $this->expectException(CliExecutionException::class);
        $this->expectExceptionMessage('Pull failed: Workspace not found');

        $wrapper->pull(
            99999,
            'test-key',
            'test-secret',
            $outputPath
        );
    }

    public function testPullCommandWithInvalidOutputDirectory(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        $this->expectException(CliExecutionException::class);
        $this->expectExceptionMessage('Output directory does not exist:');

        $wrapper->pull(
            12345,
            'test-key',
            'test-secret',
            '/nonexistent/dir/workspace.json'
        );
    }

    // ========================================
    // Version Tests
    // ========================================

    public function testGetVersionSuccess(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 0,
            stdout: 'Structurizr CLI v1.30.0',
            stderr: '',
            success: true
        );

        $wrapper->setMockedResult($result);

        $version = $wrapper->getVersion();

        $this->assertEquals('Structurizr CLI v1.30.0', $version);
    }

    public function testGetVersionFailure(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 1,
            stdout: '',
            stderr: 'Command not found',
            success: false
        );

        $wrapper->setMockedResult($result);

        $version = $wrapper->getVersion();

        $this->assertEquals('unknown', $version);
    }

    // ========================================
    // Logging and Security Tests
    // ========================================

    public function testCommandSanitizationForLogging(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        // Use reflection to access private method
        $reflection = new \ReflectionClass($wrapper);
        $method = $reflection->getMethod('sanitizeCommandForLogging');
        $method->setAccessible(true);

        $command = [
            '/path/to/cli',
            'push',
            '-key', 'my-secret-key',
            '-secret', 'my-secret-secret',
            '-workspace', '/path/to/workspace.dsl'
        ];

        $sanitized = $method->invoke($wrapper, $command);

        // Verify credentials are redacted
        $this->assertContains('-key', $sanitized);
        $this->assertContains('[REDACTED]', $sanitized);
        $this->assertNotContains('my-secret-key', $sanitized);
        $this->assertNotContains('my-secret-secret', $sanitized);
        $this->assertContains('/path/to/workspace.dsl', $sanitized);

        // Verify that the redacted values are in the correct positions
        $keyIndex = array_search('-key', $sanitized);
        $this->assertEquals('[REDACTED]', $sanitized[$keyIndex + 1]);

        $secretIndex = array_search('-secret', $sanitized);
        $this->assertEquals('[REDACTED]', $sanitized[$secretIndex + 1]);
    }

    public function testApiKeyRedactionInLogs(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        $reflection = new \ReflectionClass($wrapper);
        $method = $reflection->getMethod('sanitizeCommandForLogging');
        $method->setAccessible(true);

        $command = [
            '/path/to/cli',
            'push',
            '-apiKey', 'my-api-key',
            '-workspace', '/path/to/workspace.dsl'
        ];

        $sanitized = $method->invoke($wrapper, $command);

        $this->assertContains('-apiKey', $sanitized);
        $this->assertContains('[REDACTED]', $sanitized);
        $this->assertNotContains('my-api-key', $sanitized);
        $this->assertContains('/path/to/workspace.dsl', $sanitized);
    }

    public function testApiSecretRedactionInLogs(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        $reflection = new \ReflectionClass($wrapper);
        $method = $reflection->getMethod('sanitizeCommandForLogging');
        $method->setAccessible(true);

        $command = [
            '/path/to/cli',
            'push',
            '-apiSecret', 'my-api-secret',
            '-id', '12345'
        ];

        $sanitized = $method->invoke($wrapper, $command);

        $this->assertContains('-apiSecret', $sanitized);
        $this->assertContains('[REDACTED]', $sanitized);
        $this->assertNotContains('my-api-secret', $sanitized);
        $this->assertContains('12345', $sanitized);
    }

    public function testMultipleCredentialsRedaction(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        $reflection = new \ReflectionClass($wrapper);
        $method = $reflection->getMethod('sanitizeCommandForLogging');
        $method->setAccessible(true);

        $command = [
            '/path/to/cli',
            'push',
            '-key', 'secret-key',
            '-secret', 'secret-secret',
            '-apiKey', 'api-key-value',
            '-apiSecret', 'api-secret-value',
            '-workspace', '/path/to/workspace.dsl'
        ];

        $sanitized = $method->invoke($wrapper, $command);

        // Verify all credentials are redacted
        $this->assertNotContains('secret-key', $sanitized);
        $this->assertNotContains('secret-secret', $sanitized);
        $this->assertNotContains('api-key-value', $sanitized);
        $this->assertNotContains('api-secret-value', $sanitized);

        // Count [REDACTED] occurrences
        $redactedCount = 0;
        foreach ($sanitized as $arg) {
            if ($arg === '[REDACTED]') {
                $redactedCount++;
            }
        }
        $this->assertEquals(4, $redactedCount);
    }

    // ========================================
    // Validation Result Parsing Tests
    // ========================================

    public function testParseValidationResultWithMixedMessages(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $output = <<<OUTPUT
[INFO] Starting validation
ERROR: Undefined element 'database'
WARNING: Element 'api' has no description
[ERROR] Relationship missing source
WARNING: View 'SystemContext' is empty
OUTPUT;

        $result = new ProcessResult(
            exitCode: 1,
            stdout: $output,
            stderr: '',
            success: false
        );

        $wrapper->setMockedResult($result);

        $validationResult = $wrapper->validate($this->tempDslPath);

        $this->assertFalse($validationResult->isValid());
        $this->assertCount(2, $validationResult->getErrors());
        $this->assertCount(2, $validationResult->getWarnings());
    }

    public function testParseValidationResultWithStderrFallback(): void
    {
        $wrapper = new TestableCliWrapper($this->tempCliPath, $this->logger);

        $result = new ProcessResult(
            exitCode: 1,
            stdout: '',
            stderr: 'Fatal error: Could not parse DSL file',
            success: false
        );

        $wrapper->setMockedResult($result);

        $validationResult = $wrapper->validate($this->tempDslPath);

        $this->assertFalse($validationResult->isValid());
        $this->assertCount(1, $validationResult->getErrors());
        $this->assertEquals('Fatal error: Could not parse DSL file', $validationResult->getErrors()[0]);
    }

    // ========================================
    // File Path Validation Tests
    // ========================================

    public function testValidateFilePathWithNonexistentFile(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        $this->expectException(CliExecutionException::class);
        $this->expectExceptionMessage('not found:');

        $wrapper->validate('/nonexistent/file.dsl');
    }

    public function testValidateFilePathWithDirectory(): void
    {
        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        $this->expectException(CliExecutionException::class);
        $this->expectExceptionMessage('is not a file:');

        $wrapper->validate($this->tempOutputDir);
    }

    public function testExportWithNonReadableFile(): void
    {
        // Skip this test if running as root (root can read all files)
        if (posix_getuid() === 0) {
            $this->markTestSkipped('Test cannot run as root user');
        }

        // Create a file and make it non-readable
        $nonReadable = $this->tempOutputDir . '/non-readable.json';
        file_put_contents($nonReadable, '{}');
        chmod($nonReadable, 0000);

        $wrapper = new CliWrapper($this->tempCliPath, $this->logger);

        try {
            $this->expectException(CliExecutionException::class);
            $this->expectExceptionMessage('is not readable:');

            $wrapper->export($nonReadable, 'plantuml');
        } finally {
            // Restore permissions for cleanup
            chmod($nonReadable, 0644);
        }
    }
}

/**
 * Testable version of CliWrapper that allows mocking of executeCommand
 */
class TestableCliWrapper extends CliWrapper
{
    private ?ProcessResult $mockedResult = null;
    private array $lastCommandArgs = [];

    public function setMockedResult(ProcessResult $result): void
    {
        $this->mockedResult = $result;
    }

    public function getLastCommandArgs(): array
    {
        return $this->lastCommandArgs;
    }

    public function executeCommand(array $args, int $timeout = 30): ProcessResult
    {
        $this->lastCommandArgs = $args;

        if ($this->mockedResult !== null) {
            return $this->mockedResult;
        }

        return parent::executeCommand($args, $timeout);
    }
}
