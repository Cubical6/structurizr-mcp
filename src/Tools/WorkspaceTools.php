<?php

declare(strict_types=1);

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use Psr\Log\LoggerInterface;

/**
 * MCP Tools for Structurizr workspace management
 */
class WorkspaceTools
{
    public function __construct(
        private readonly WorkspaceManager $workspaceManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Create a new Structurizr workspace
     *
     * Creates a new workspace with the specified name and description.
     * Returns the workspace ID which can be used for subsequent operations.
     *
     * @param string $name The name of the workspace (1-100 characters)
     * @param string $description Optional description of the workspace purpose
     * @return array Workspace details including ID, name, and DSL
     */
    #[McpTool(name: 'create_workspace', description: 'Create a new Structurizr workspace')]
    public function createWorkspace(
        string $name,
        string $description = ''
    ): array {
        $this->logger->info("Creating workspace: {$name}");

        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Workspace name cannot be empty');
        }

        if (strlen($name) > 100) {
            throw new \InvalidArgumentException('Workspace name must be 100 characters or less');
        }

        $workspace = $this->workspaceManager->create($name, $description);

        return [
            'workspaceId' => $workspace->id,
            'name' => $workspace->name,
            'description' => $workspace->description,
            'dsl' => $workspace->dsl,
            'createdAt' => $workspace->createdAt?->format('c'),
        ];
    }

    /**
     * Get workspace details
     *
     * Retrieves the complete details of a workspace including its model, views, and DSL.
     *
     * @param string $workspaceId The ID of the workspace to retrieve
     * @param string $format Output format: 'json' or 'dsl' (default: 'json')
     * @return array Workspace data in the requested format
     */
    #[McpTool(name: 'get_workspace', description: 'Get workspace details by ID')]
    public function getWorkspace(
        string $workspaceId,
        string $format = 'json'
    ): array {
        $this->logger->debug("Getting workspace: {$workspaceId} in format: {$format}");

        if (!in_array($format, ['json', 'dsl'], true)) {
            throw new \InvalidArgumentException("Invalid format: {$format}. Must be 'json' or 'dsl'");
        }

        $workspace = $this->workspaceManager->load($workspaceId);

        if ($format === 'dsl') {
            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'dsl' => $workspace->dsl,
            ];
        }

        return $workspace->toArray();
    }

    /**
     * List all workspaces
     *
     * Returns a list of all available workspaces with their metadata.
     *
     * @return array List of workspaces with ID, name, description, and timestamps
     */
    #[McpTool(name: 'list_workspaces', description: 'List all available workspaces')]
    public function listWorkspaces(): array
    {
        $this->logger->debug('Listing all workspaces');

        $workspaces = $this->workspaceManager->list();

        return [
            'workspaces' => $workspaces,
            'count' => count($workspaces),
        ];
    }

    /**
     * Delete a workspace
     *
     * Permanently deletes a workspace and all its data.
     *
     * @param string $workspaceId The ID of the workspace to delete
     * @return array Deletion confirmation with success status and message
     */
    #[McpTool(name: 'delete_workspace', description: 'Delete a workspace by ID')]
    public function deleteWorkspace(string $workspaceId): array
    {
        $this->logger->info("Deleting workspace: {$workspaceId}");

        try {
            $this->workspaceManager->delete($workspaceId);

            return [
                'success' => true,
                'message' => "Workspace {$workspaceId} deleted successfully",
                'workspaceId' => $workspaceId,
            ];
        } catch (WorkspaceNotFoundException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'workspaceId' => $workspaceId,
            ];
        }
    }

    /**
     * Export workspace to DSL
     *
     * Exports the workspace definition to Structurizr DSL format.
     *
     * @param string $workspaceId The ID of the workspace to export
     * @return array Workspace DSL string
     */
    #[McpTool(name: 'export_to_dsl', description: 'Export workspace to Structurizr DSL format')]
    public function exportToDsl(string $workspaceId): array
    {
        $this->logger->debug("Exporting workspace to DSL: {$workspaceId}");

        $workspace = $this->workspaceManager->load($workspaceId);

        return [
            'workspaceId' => $workspace->id,
            'name' => $workspace->name,
            'dsl' => $workspace->dsl,
        ];
    }
}
