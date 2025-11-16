<?php

declare(strict_types=1);

namespace StructurizrMcp\Exception;

/**
 * Thrown when DSL validation fails
 */
class InvalidDslException extends StructurizrException
{
    /**
     * Constructor
     *
     * @param string $message Description of the DSL validation error
     * @param \Throwable|null $previous Previous exception for exception chaining
     */
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct(
            "Invalid DSL: {$message}",
            400,
            $previous,
        );
    }
}
