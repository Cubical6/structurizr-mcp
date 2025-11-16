<?php

declare(strict_types=1);

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Structurizr\DslBuilder;
use StructurizrMcp\Structurizr\Workspace;
use Psr\Log\LoggerInterface;
use Mcp\Exception\ToolCallException;
use StructurizrMcp\Exception\WorkspaceNotFoundException;

/**
 * MCP Tools for C4 view operations (creating and configuring diagrams)
 */
class ViewTools extends AbstractWorkspaceTool
{
    public function __construct(
        private readonly WorkspaceManager $workspaceManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Create a system context view
     *
     * Creates a system context diagram view showing a system and its relationships
     * with users and other systems. This is the highest level C4 diagram showing
     * the big picture of how the system fits into its environment.
     *
     * @param string $workspaceId The workspace ID
     * @param string $systemId The software system to focus on
     * @param string $key Unique key for the view (alphanumeric, hyphens, underscores)
     * @param string $description Optional description of the view
     * @return array View details including key and type
     */
    #[McpTool(name: 'create_system_context_view', description: 'Create a system context diagram view')]
    public function createSystemContextView(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'System ID to visualize', minLength: 1)]
        string $systemId,
        #[Schema(description: 'Unique view key', minLength: 1, maxLength: 50, pattern: '^[a-zA-Z0-9_-]+$')]
        string $key,
        #[Schema(description: 'View description', maxLength: 500)]
        string $description = ''
    ): array {
        $this->logger->info("Creating system context view '{$key}' for system '{$systemId}' in workspace: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            $builder = $this->createBuilderFromWorkspace($workspace);
            $viewKey = $builder->addSystemContextView($systemId, $key, $description);
            $dsl = $builder->toDsl();

            $updated = $workspace->withDsl($dsl);
            $this->workspaceManager->save($updated);


        return [
            'workspaceId' => $workspaceId,
            'viewKey' => $viewKey,
            'systemId' => $systemId,
            'type' => 'systemContext',
            'description' => $description,
        ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to create system context view '{$key}': " . $e->getMessage());
        }
    }

    /**
     * Create a container view
     *
     * Creates a container diagram view showing the containers (applications, data stores,
     * microservices, etc.) within a software system. This is the second level C4 diagram
     * showing the high-level technology choices and deployment architecture.
     *
     * @param string $workspaceId The workspace ID
     * @param string $systemId The software system to visualize
     * @param string $key Unique key for the view (alphanumeric, hyphens, underscores)
     * @param string $description Optional description of the view
     * @return array View details including key and type
     */
    #[McpTool(name: 'create_container_view', description: 'Create a container diagram view')]
    public function createContainerView(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'System ID to visualize', minLength: 1)]
        string $systemId,
        #[Schema(description: 'Unique view key', minLength: 1, maxLength: 50, pattern: '^[a-zA-Z0-9_-]+$')]
        string $key,
        #[Schema(description: 'View description', maxLength: 500)]
        string $description = ''
    ): array {
        $this->logger->info("Creating container view '{$key}' for system '{$systemId}' in workspace: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            $builder = $this->createBuilderFromWorkspace($workspace);
            $viewKey = $builder->addContainerView($systemId, $key, $description);
            $dsl = $builder->toDsl();

            $updated = $workspace->withDsl($dsl);
            $this->workspaceManager->save($updated);


        return [
            'workspaceId' => $workspaceId,
            'viewKey' => $viewKey,
            'systemId' => $systemId,
            'type' => 'container',
            'description' => $description,
        ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to create container view '{$key}': " . $e->getMessage());
        }
    }

    /**
     * Create a component view
     *
     * Creates a component diagram view showing the components within a container.
     * This is the third level C4 diagram showing the internal structure and
     * organization of a single container.
     *
     * @param string $workspaceId The workspace ID
     * @param string $containerId The container to visualize
     * @param string $key Unique key for the view (alphanumeric, hyphens, underscores)
     * @param string $description Optional description of the view
     * @return array View details including key and type
     */
    #[McpTool(name: 'create_component_view', description: 'Create a component diagram view')]
    public function createComponentView(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Container ID to visualize', minLength: 1)]
        string $containerId,
        #[Schema(description: 'Unique view key', minLength: 1, maxLength: 50, pattern: '^[a-zA-Z0-9_-]+$')]
        string $key,
        #[Schema(description: 'View description', maxLength: 500)]
        string $description = ''
    ): array {
        $this->logger->info("Creating component view '{$key}' for container '{$containerId}' in workspace: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            $builder = $this->createBuilderFromWorkspace($workspace);
            $viewKey = $builder->addComponentView($containerId, $key, $description);
            $dsl = $builder->toDsl();

            $updated = $workspace->withDsl($dsl);
            $this->workspaceManager->save($updated);


        return [
            'workspaceId' => $workspaceId,
            'viewKey' => $viewKey,
            'containerId' => $containerId,
            'type' => 'component',
            'description' => $description,
        ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to create component view '{$key}': " . $e->getMessage());
        }
    }

    /**
     * Apply auto-layout to a view
     *
     * Configures the automatic layout direction for a view. Auto-layout automatically
     * positions elements in the diagram according to the specified direction.
     *
     * @param string $workspaceId The workspace ID
     * @param string $viewKey The unique key of the view to modify
     * @param string $direction Layout direction: 'tb' (top-to-bottom), 'bt' (bottom-to-top), 'lr' (left-to-right), 'rl' (right-to-left)
     * @return array Updated view details
     */
    #[McpTool(name: 'apply_auto_layout', description: 'Apply auto-layout configuration to a view')]
    public function applyAutoLayout(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'View key to modify', minLength: 1, maxLength: 50, pattern: '^[a-zA-Z0-9_-]+$')]
        string $viewKey,
        #[Schema(description: 'Layout direction', enum: ['tb', 'bt', 'lr', 'rl'])]
        string $direction = 'lr'
    ): array {
        $this->logger->info("Applying auto-layout '{$direction}' to view '{$viewKey}' in workspace: {$workspaceId}");

        try {
            if (!in_array($direction, ['tb', 'bt', 'lr', 'rl'], true)) {
                throw new \InvalidArgumentException("Direction must be one of: tb, bt, lr, rl");
            }

            $workspace = $this->workspaceManager->load($workspaceId);

            $builder = $this->createBuilderFromWorkspace($workspace);
            $builder->setViewAutoLayout($viewKey, $direction);
            $dsl = $builder->toDsl();

            $updated = $workspace->withDsl($dsl);
            $this->workspaceManager->save($updated);


        return [
            'workspaceId' => $workspaceId,
            'viewKey' => $viewKey,
            'autoLayout' => $direction,
            'message' => "Auto-layout '{$direction}' applied to view '{$viewKey}'",
        ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to apply auto-layout to view '{$viewKey}': " . $e->getMessage());
        }
    }
}
