<?php

declare(strict_types=1);

namespace StructurizrMcp\Resources;

use Mcp\Capability\Attribute\McpResourceTemplate;
use Mcp\Exception\ResourceNotFoundException;
use Mcp\Exception\ResourceReadException;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * MCP Resource for accessing individual views in a workspace
 *
 * Provides dynamic resource for retrieving specific views
 * (system context, container, component, dynamic, deployment) from a workspace.
 */
class ViewResource
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
     * Get a specific view from workspace
     *
     * Retrieves detailed information about a specific view including its
     * configuration, elements, relationships, and layout settings.
     *
     * @param string $workspaceId The workspace identifier
     * @param string $viewKey The view key/identifier
     * @return array<string, mixed> View data including configuration and elements
     * @throws ResourceNotFoundException If workspace or view is not found
     * @throws ResourceReadException If view cannot be loaded
     */
    #[McpResourceTemplate(
        uriTemplate: 'structurizr://workspace/{workspaceId}/view/{viewKey}',
        name: 'workspace_view',
        description: 'Specific view data from workspace',
        mimeType: 'application/json'
    )]
    public function getView(string $workspaceId, string $viewKey): array
    {
        $this->logger->debug("Retrieving view: {$viewKey} from workspace: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            // Search for view in views
            $view = $this->findView($workspace->views, $viewKey);

            if ($view === null) {
                throw new ResourceNotFoundException("View '{$viewKey}' not found in workspace '{$workspaceId}'");
            }

            return [
                'workspaceId' => $workspace->id,
                'view' => $view,
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ResourceNotFoundException("Workspace not found: {$workspaceId}");
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ResourceReadException("Failed to retrieve view '{$viewKey}': " . $e->getMessage());
        }
    }

    /**
     * Find a view in the workspace by key
     *
     * Searches through all view types to find a view with the given key.
     * Searches in: systemLandscape, systemContext, container, component, dynamic, deployment.
     *
     * @param array<string, mixed> $views The workspace views
     * @param string $viewKey The view key to find
     * @return array<string, mixed>|null The view data or null if not found
     */
    private function findView(array $views, string $viewKey): ?array
    {
        // Define all possible view types
        $viewTypes = [
            'systemLandscapeViews',
            'systemContextViews',
            'containerViews',
            'componentViews',
            'dynamicViews',
            'deploymentViews',
        ];

        // Search through each view type
        foreach ($viewTypes as $viewType) {
            if (!isset($views[$viewType]) || !is_array($views[$viewType])) {
                continue;
            }

            foreach ($views[$viewType] as $view) {
                if (!is_array($view)) {
                    continue;
                }

                if (isset($view['key']) && $view['key'] === $viewKey) {
                    // Add view type to the result for clarity
                    return array_merge($view, ['type' => $viewType]);
                }
            }
        }

        return null;
    }
}
