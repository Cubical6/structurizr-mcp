<?php

declare(strict_types=1);

namespace StructurizrMcp\Exception;

/**
 * Thrown when API authentication fails
 */
class ApiAuthenticationException extends StructurizrException
{
    /**
     * Constructor
     *
     * @param string $message Error message describing the authentication failure
     * @param \Throwable|null $previous Previous exception for exception chaining
     */
    public function __construct(string $message = 'Authentication failed', ?\Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}
