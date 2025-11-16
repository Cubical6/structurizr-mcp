<?php

declare(strict_types=1);

namespace StructurizrMcp\Exception;

/**
 * Thrown when CLI command execution fails
 */
class CliExecutionException extends StructurizrException
{
    public function __construct(string $command, string $error, ?\Throwable $previous = null)
    {
        parent::__construct(
            "CLI execution failed for command '{$command}': {$error}",
            500,
            $previous,
        );
    }
}
