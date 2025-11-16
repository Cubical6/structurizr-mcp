<?php

declare(strict_types=1);

namespace StructurizrMcp\Tools;

use StructurizrMcp\Structurizr\DslBuilder;
use StructurizrMcp\Structurizr\Workspace;

/**
 * Base class for workspace manipulation tools
 *
 * Provides shared functionality for tools that need to work with workspaces
 * and the DSL builder.
 */
abstract class AbstractWorkspaceTool
{
    /**
     * Create DSL builder from existing workspace, preserving existing DSL content if present
     *
     * Creates a new DslBuilder instance and initializes it with the workspace content.
     * If the workspace has existing DSL, it is parsed to rebuild the builder state with
     * all existing elements, relationships, and views. Otherwise, a fresh builder is
     * created with workspace name and description.
     *
     * @param Workspace $workspace The workspace to create builder from
     * @return DslBuilder Builder instance with workspace state
     */
    protected function createBuilderFromWorkspace(Workspace $workspace): DslBuilder
    {
        // If workspace has existing DSL, parse it to preserve content
        if (!empty($workspace->dsl)) {
            return DslBuilder::fromDsl($workspace->dsl);
        }

        // Otherwise, create a fresh builder with workspace metadata
        $builder = new DslBuilder();
        $builder->workspace($workspace->name, $workspace->description);

        return $builder;
    }
}
