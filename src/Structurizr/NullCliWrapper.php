<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr;

use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\CliExecutionException;

/**
 * Null Object implementation of CliWrapper
 *
 * Used when Structurizr CLI is not available or configured.
 * Provides clear error messages for operations that require the CLI.
 *
 * This follows the Null Object Pattern to allow graceful degradation
 * when the CLI is not available, while still allowing dependency injection
 * to work correctly.
 */
class NullCliWrapper implements CliWrapperInterface
{
    private const ERROR_MESSAGE = 'Structurizr CLI is not configured. Please set STRUCTURIZR_CLI_PATH environment variable to the path of the Structurizr CLI executable.';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        $this->logger->debug('NullCliWrapper initialized - CLI operations will be unavailable');
    }

    /**
     * @throws CliExecutionException Always throws - validation requires CLI
     */
    public function validate(string $dslPath): ValidationResult
    {
        throw new CliExecutionException(
            'validate',
            'Workspace validation requires Structurizr CLI. ' . self::ERROR_MESSAGE,
        );
    }

    /**
     * @throws CliExecutionException Always throws - export requires CLI
     */
    public function export(string $workspacePath, string $format, ?string $outputPath = null): string
    {
        throw new CliExecutionException(
            'export',
            ucfirst($format) . ' export requires Structurizr CLI. ' . self::ERROR_MESSAGE,
        );
    }

    /**
     * @throws CliExecutionException Always throws - push requires CLI
     */
    public function push(
        string $workspacePath,
        int $workspaceId,
        string $apiKey,
        string $apiSecret,
        ?string $apiUrl = null,
    ): ProcessResult {
        throw new CliExecutionException(
            'push',
            'Pushing to Structurizr Cloud requires Structurizr CLI. ' . self::ERROR_MESSAGE,
        );
    }

    /**
     * @throws CliExecutionException Always throws - pull requires CLI
     */
    public function pull(
        int $workspaceId,
        string $apiKey,
        string $apiSecret,
        string $outputPath,
        ?string $apiUrl = null,
    ): ProcessResult {
        throw new CliExecutionException(
            'pull',
            'Pulling from Structurizr Cloud requires Structurizr CLI. ' . self::ERROR_MESSAGE,
        );
    }

    /**
     * @return string Returns "unavailable" since CLI is not configured
     */
    public function getVersion(): string
    {
        return 'unavailable';
    }
}
