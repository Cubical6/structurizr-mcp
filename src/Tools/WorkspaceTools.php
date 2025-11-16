<?php

declare(strict_types=1);

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * MCP Tools for Structurizr workspace management
 */
class WorkspaceTools
{
    /**
     * Constructor
     *
     * @param WorkspaceManager $workspaceManager Manager for workspace operations
     * @param LoggerInterface $logger Logger for debugging and info messages
     */
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
        #[Schema(description: 'Workspace name', minLength: 1, maxLength: 100)]
        string $name,
        #[Schema(description: 'Workspace description', maxLength: 500)]
        string $description = ''
    ): array {
        $this->logger->info("Creating workspace: {$name}");

        // Manual validation (Schema handles this in MCP, but needed for direct calls)
        $trimmedName = trim($name);
        if (empty($trimmedName)) {
            throw new ToolCallException('Workspace name cannot be empty');
        }
        if (strlen($name) > 100) {
            throw new ToolCallException('Workspace name must be 100 characters or less');
        }

        try {
            $workspace = $this->workspaceManager->create($name, $description);

            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'description' => $workspace->description,
                'dsl' => $workspace->dsl,
                'createdAt' => $workspace->createdAt?->format('c'),
            ];
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to create workspace '{$name}': " . $e->getMessage());
        }
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
        #[Schema(description: 'Workspace ID to retrieve', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Output format', enum: ['json', 'dsl'])]
        string $format = 'json'
    ): array {
        $this->logger->debug("Getting workspace: {$workspaceId} in format: {$format}");

        // Manual validation (Schema handles this in MCP, but needed for direct calls)
        if (!in_array($format, ['json', 'dsl'], true)) {
            throw new ToolCallException("Invalid format: {$format}. Must be 'json' or 'dsl'");
        }

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            if ($format === 'dsl') {
                return [
                    'workspaceId' => $workspace->id,
                    'name' => $workspace->name,
                    'dsl' => $workspace->dsl,
                ];
            }

            return $workspace->toArray();
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to get workspace '{$workspaceId}': " . $e->getMessage());
        }
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

        try {
            $workspaces = $this->workspaceManager->list();

            return [
                'workspaces' => $workspaces,
                'count' => count($workspaces),
            ];
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to list workspaces: " . $e->getMessage());
        }
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
    public function deleteWorkspace(
        #[Schema(description: 'Workspace ID to delete', minLength: 1)]
        string $workspaceId
    ): array {
        $this->logger->info("Deleting workspace: {$workspaceId}");

        try {
            $this->workspaceManager->delete($workspaceId);

            return [
                'success' => true,
                'message' => "Workspace {$workspaceId} deleted successfully",
                'workspaceId' => $workspaceId,
            ];
        } catch (ToolCallException $e) {
            // Re-throw ToolCallException as-is
            throw $e;
        } catch (WorkspaceNotFoundException $e) {
            // Return failure instead of throwing for workspace not found
            return [
                'success' => false,
                'message' => "Workspace not found: {$workspaceId}",
                'workspaceId' => $workspaceId,
            ];
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to delete workspace '{$workspaceId}': " . $e->getMessage());
        }
    }

}
