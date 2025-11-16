<?php

declare(strict_types=1);

namespace StructurizrMcp\Exception;

/**
 * Thrown when a workspace cannot be found
 */
class WorkspaceNotFoundException extends StructurizrException
{
    /**
     * Constructor
     *
     * @param string $workspaceId The ID of the workspace that was not found
     * @param \Throwable|null $previous Previous exception for exception chaining
     */
    public function __construct(string $workspaceId, ?\Throwable $previous = null)
    {
        parent::__construct(
            "Workspace not found: {$workspaceId}",
            404,
            $previous,
        );
    }
}
