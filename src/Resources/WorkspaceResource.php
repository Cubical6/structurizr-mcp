<?php

declare(strict_types=1);

namespace StructurizrMcp\Resources;

use Mcp\Capability\Attribute\McpResourceTemplate;
use Mcp\Exception\ResourceNotFoundException;
use Mcp\Exception\ResourceReadException;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\DslBuilder;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * MCP Resource for workspace data access
 *
 * Provides dynamic resources for accessing workspace data through URI templates.
 * Supports full workspace retrieval, model-only access, views-only access, and DSL export.
 */
class WorkspaceResource
{
    /**
     * Constructor
     *
     * @param WorkspaceManager $workspaceManager Manager for workspace operations
     * @param LoggerInterface $logger Logger for debugging and info messages
     */
    public function __construct(
        private readonly WorkspaceManager $workspaceManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Get complete workspace data
     *
     * Retrieves the full workspace including model, views, DSL, and metadata.
     *
     * @param string $workspaceId The workspace identifier
     * @return array<string, mixed> Complete workspace data
     * @throws ResourceNotFoundException If workspace is not found
     * @throws ResourceReadException If workspace cannot be loaded
     */
    #[McpResourceTemplate(
        uriTemplate: 'structurizr://workspace/{workspaceId}',
        name: 'workspace_full',
        description: 'Complete workspace data including model, views, and DSL',
        mimeType: 'application/json'
    )]
    public function getWorkspace(string $workspaceId): array
    {
        $this->logger->debug("Retrieving workspace resource: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            return $workspace->toArray();
        } catch (WorkspaceNotFoundException $e) {
            throw new ResourceNotFoundException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ResourceReadException("Failed to retrieve workspace '{$workspaceId}': " . $e->getMessage());
        }
    }

    /**
     * Get workspace model data only
     *
     * Retrieves only the model portion of the workspace, including elements and relationships.
     *
     * @param string $workspaceId The workspace identifier
     * @return array<string, mixed> Workspace model data
     * @throws ResourceNotFoundException If workspace is not found
     * @throws ResourceReadException If workspace cannot be loaded
     */
    #[McpResourceTemplate(
        uriTemplate: 'structurizr://workspace/{workspaceId}/model',
        name: 'workspace_model',
        description: 'Workspace model data (elements and relationships)',
        mimeType: 'application/json'
    )]
    public function getModel(string $workspaceId): array
    {
        $this->logger->debug("Retrieving workspace model: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'model' => $workspace->model,
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ResourceNotFoundException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ResourceReadException("Failed to retrieve workspace model '{$workspaceId}': " . $e->getMessage());
        }
    }

    /**
     * Get workspace views data only
     *
     * Retrieves only the views portion of the workspace.
     *
     * @param string $workspaceId The workspace identifier
     * @return array<string, mixed> Workspace views data
     * @throws ResourceNotFoundException If workspace is not found
     * @throws ResourceReadException If workspace cannot be loaded
     */
    #[McpResourceTemplate(
        uriTemplate: 'structurizr://workspace/{workspaceId}/views',
        name: 'workspace_views',
        description: 'Workspace views data',
        mimeType: 'application/json'
    )]
    public function getViews(string $workspaceId): array
    {
        $this->logger->debug("Retrieving workspace views: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'views' => $workspace->views,
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ResourceNotFoundException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ResourceReadException("Failed to retrieve workspace views '{$workspaceId}': " . $e->getMessage());
        }
    }

    /**
     * Get workspace DSL content
     *
     * Retrieves the DSL (Domain Specific Language) representation of the workspace.
     * If the workspace doesn't have DSL content, generates it from the model.
     *
     * @param string $workspaceId The workspace identifier
     * @return string Workspace DSL content
     * @throws ResourceNotFoundException If workspace is not found
     * @throws ResourceReadException If workspace cannot be loaded
     */
    #[McpResourceTemplate(
        uriTemplate: 'structurizr://workspace/{workspaceId}/dsl',
        name: 'workspace_dsl',
        description: 'Workspace DSL representation',
        mimeType: 'text/plain'
    )]
    public function getDsl(string $workspaceId): string
    {
        $this->logger->debug("Retrieving workspace DSL: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            // If workspace has DSL, return it
            if (!empty($workspace->dsl)) {
                return $workspace->dsl;
            }

            // Otherwise, generate basic DSL structure from workspace metadata
            $builder = new DslBuilder();
            $builder->workspace($workspace->name, $workspace->description);

            return $builder->toDsl();
        } catch (WorkspaceNotFoundException $e) {
            throw new ResourceNotFoundException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ResourceReadException("Failed to retrieve workspace DSL '{$workspaceId}': " . $e->getMessage());
        }
    }
}
