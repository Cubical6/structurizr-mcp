<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Exception\CliNotAvailableException;
use StructurizrMcp\Exception\StructurizrException;

/**
 * Unit tests for CliNotAvailableException
 *
 * @covers \StructurizrMcp\Exception\CliNotAvailableException
 */
class CliNotAvailableExceptionTest extends TestCase
{
    public function testExtendsStructurizrException(): void
    {
        $exception = new CliNotAvailableException();

        $this->assertInstanceOf(StructurizrException::class, $exception);
    }

    public function testMessageContainsInstallationInstructions(): void
    {
        $exception = new CliNotAvailableException();

        $message = $exception->getMessage();

        $this->assertStringContainsString('Structurizr CLI is not available', $message);
        $this->assertStringContainsString('Installation options', $message);
        $this->assertStringContainsString('Docker', $message);
    }

    public function testDefaultInstructionsContainDockerCommand(): void
    {
        $exception = new CliNotAvailableException();

        $instructions = $exception->getInstallationInstructions();

        $this->assertArrayHasKey('Docker (recommended)', $instructions);
        $this->assertStringContainsString('docker pull', $instructions['Docker (recommended)']);
    }

    public function testDefaultInstructionsContainHomebrewCommand(): void
    {
        $exception = new CliNotAvailableException();

        $instructions = $exception->getInstallationInstructions();

        $this->assertArrayHasKey('macOS (Homebrew)', $instructions);
        $this->assertStringContainsString('brew install', $instructions['macOS (Homebrew)']);
    }

    public function testDefaultInstructionsContainScoopCommand(): void
    {
        $exception = new CliNotAvailableException();

        $instructions = $exception->getInstallationInstructions();

        $this->assertArrayHasKey('Windows (Scoop)', $instructions);
        $this->assertStringContainsString('scoop', $instructions['Windows (Scoop)']);
    }

    public function testDefaultInstructionsContainManualDownload(): void
    {
        $exception = new CliNotAvailableException();

        $instructions = $exception->getInstallationInstructions();

        $this->assertArrayHasKey('Manual', $instructions);
        $this->assertStringContainsString('github.com/structurizr/cli', $instructions['Manual']);
    }

    public function testCustomInstructionsCanBeProvided(): void
    {
        $customInstructions = [
            'Custom Method' => 'custom install command',
            'Another Method' => 'another command',
        ];

        $exception = new CliNotAvailableException($customInstructions);

        $instructions = $exception->getInstallationInstructions();

        $this->assertEquals($customInstructions, $instructions);
        $this->assertStringContainsString('Custom Method', $exception->getMessage());
        $this->assertStringContainsString('custom install command', $exception->getMessage());
    }

    public function testMessageIsFormattedCorrectly(): void
    {
        $exception = new CliNotAvailableException();

        $message = $exception->getMessage();

        // Check the message has proper structure
        $this->assertStringContainsString("Installation options:\n", $message);

        // Check that instructions are indented
        $this->assertMatchesRegularExpression('/\s{2}\w+/', $message);
    }
}
