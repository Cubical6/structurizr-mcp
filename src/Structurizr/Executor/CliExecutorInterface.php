<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr\Executor;

use StructurizrMcp\Structurizr\ProcessResult;

/**
 * Interface for CLI execution strategies
 *
 * Allows switching between local CLI and Docker-based execution
 * without changing the CliWrapper API.
 */
interface CliExecutorInterface
{
    /**
     * Execute a Structurizr CLI command
     *
     * @param array<string> $args Command arguments
     * @param int $timeout Timeout in seconds
     * @param string|null $workingDirectory Working directory for file operations
     * @return ProcessResult
     */
    public function execute(array $args, int $timeout, ?string $workingDirectory = null): ProcessResult;

    /**
     * Check if this executor is available
     */
    public function isAvailable(): bool;

    /**
     * Get executor name for logging
     */
    public function getName(): string;
}
