<?php

declare(strict_types=1);

namespace StructurizrMcp\Exception;

/**
 * Thrown when API authentication fails
 */
class ApiAuthenticationException extends StructurizrException
{
    public function __construct(string $message = 'Authentication failed', ?\Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}
