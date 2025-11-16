<?php

declare(strict_types=1);

namespace StructurizrMcp\Exception;

/**
 * Thrown when CLI command execution fails
 */
class CliExecutionException extends StructurizrException
{
    /**
     * Constructor
     *
     * @param string $command The CLI command that failed
     * @param string $error The error message from the command
     * @param \Throwable|null $previous Previous exception for exception chaining
     */
    public function __construct(string $command, string $error, ?\Throwable $previous = null)
    {
        parent::__construct(
            "CLI execution failed for command '{$command}': {$error}",
            500,
            $previous
        );
    }
}
