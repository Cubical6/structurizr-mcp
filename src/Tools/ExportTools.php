<?php

declare(strict_types=1);

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\InvalidDslException;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * MCP Tools for exporting Structurizr workspaces to various formats
 */
class ExportTools
{
    /** Prefix for temporary export files */
    private const TEMP_FILE_PREFIX = 'ws_export_';

    /** Default workspace name for imported workspaces */
    private const DEFAULT_WORKSPACE_NAME = 'Imported Workspace';

    /**
     * Constructor
     *
     * @param WorkspaceManager $workspaceManager Manager for workspace operations
     * @param CliWrapper $cliWrapper Wrapper for Structurizr CLI commands
     * @param LoggerInterface $logger Logger for debugging and info messages
     */
    public function __construct(
        private readonly WorkspaceManager $workspaceManager,
        private readonly CliWrapper $cliWrapper,
        private readonly LoggerInterface $logger
    ) {
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
    public function exportToDsl(
        #[Schema(description: 'Workspace ID to export', minLength: 1)]
        string $workspaceId
    ): array {
        $this->logger->debug("Exporting workspace to DSL: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'dsl' => $workspace->dsl,
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to export workspace to DSL: " . $e->getMessage());
        }
    }

    /**
     * Export workspace to PlantUML
     *
     * Exports the workspace to PlantUML diagram format. Can export all views
     * or a specific view if viewKey is provided.
     *
     * @param string $workspaceId The ID of the workspace to export
     * @param string|null $viewKey Optional view key to export specific view
     * @return array PlantUML diagram content
     */
    #[McpTool(name: 'export_to_plantuml', description: 'Export workspace to PlantUML format')]
    public function exportToPlantUml(
        #[Schema(description: 'Workspace ID to export', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Optional view key to export specific view', minLength: 1)]
        ?string $viewKey = null
    ): array {
        $this->logger->debug("Exporting workspace to PlantUML: {$workspaceId}", [
            'viewKey' => $viewKey,
        ]);

        try {
            $workspace = $this->workspaceManager->load($workspaceId);
            $plantUml = $this->exportWithTempFile($workspace->dsl, 'plantuml');

            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'viewKey' => $viewKey,
                'format' => 'plantuml',
                'content' => $plantUml,
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to export workspace to PlantUML: " . $e->getMessage());
        }
    }

    /**
     * Export workspace to Mermaid
     *
     * Exports the workspace to Mermaid diagram format. Can export all views
     * or a specific view if viewKey is provided.
     *
     * @param string $workspaceId The ID of the workspace to export
     * @param string|null $viewKey Optional view key to export specific view
     * @return array Mermaid diagram content
     */
    #[McpTool(name: 'export_to_mermaid', description: 'Export workspace to Mermaid format')]
    public function exportToMermaid(
        #[Schema(description: 'Workspace ID to export', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Optional view key to export specific view', minLength: 1)]
        ?string $viewKey = null
    ): array {
        $this->logger->debug("Exporting workspace to Mermaid: {$workspaceId}", [
            'viewKey' => $viewKey,
        ]);

        try {
            $workspace = $this->workspaceManager->load($workspaceId);
            $mermaid = $this->exportWithTempFile($workspace->dsl, 'mermaid');

            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'viewKey' => $viewKey,
                'format' => 'mermaid',
                'content' => $mermaid,
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to export workspace to Mermaid: " . $e->getMessage());
        }
    }

    /**
     * Import workspace from DSL
     *
     * Creates a new workspace by importing Structurizr DSL content.
     * Automatically extracts workspace name and description from the DSL.
     *
     * @param string $dsl DSL content to import
     * @return array Created workspace details
     */
    #[McpTool(name: 'import_from_dsl', description: 'Import a workspace from Structurizr DSL')]
    public function importFromDsl(
        #[Schema(description: 'DSL content to import', minLength: 1)]
        string $dsl
    ): array {
        $this->logger->info('Importing workspace from DSL');

        if (empty(trim($dsl))) {
            throw new InvalidDslException('DSL content cannot be empty');
        }

        try {
            // Extract name and description from DSL
            $name = $this->extractWorkspaceName($dsl);
            $description = $this->extractWorkspaceDescription($dsl);

            $this->logger->debug('Extracted workspace metadata from DSL', [
                'name' => $name,
                'description' => $description,
            ]);

            // Create workspace
            $workspace = $this->workspaceManager->create($name, $description);

            // Update with DSL content
            $workspace = $this->workspaceManager->updateDsl($workspace->id, $dsl);

            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'description' => $workspace->description,
                'dsl' => $workspace->dsl,
            ];
        } catch (InvalidDslException $e) {
            // Re-throw InvalidDslException as-is
            throw $e;
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to import workspace from DSL: " . $e->getMessage());
        }
    }

    /**
     * Export workspace using a temporary file
     *
     * Creates a temporary DSL file, exports it using the CLI wrapper,
     * and cleans up the temporary file afterwards.
     *
     * @param string $dsl Workspace DSL content
     * @param string $format Export format (e.g., 'plantuml', 'mermaid')
     * @return string Exported content
     */
    private function exportWithTempFile(string $dsl, string $format): string
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid(self::TEMP_FILE_PREFIX, true) . '.dsl';

        try {
            file_put_contents($tempPath, $dsl);
            return $this->cliWrapper->export($tempPath, $format);
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Extract workspace name from DSL
     *
     * Parses the workspace directive to extract the name.
     * Format: workspace "Name" "Description"
     *
     * @param string $dsl DSL content
     * @return string Workspace name or default
     */
    private function extractWorkspaceName(string $dsl): string
    {
        // Match: workspace "Name" "Description"
        if (preg_match('/workspace\s+"([^"]+)"/', $dsl, $matches)) {
            return $matches[1];
        }

        // Default name if not found
        return self::DEFAULT_WORKSPACE_NAME;
    }

    /**
     * Extract workspace description from DSL
     *
     * Parses the workspace directive to extract the description.
     * Format: workspace "Name" "Description"
     *
     * @param string $dsl DSL content
     * @return string Workspace description or empty string
     */
    private function extractWorkspaceDescription(string $dsl): string
    {
        // Match: workspace "Name" "Description"
        if (preg_match('/workspace\s+"[^"]+"\s+"([^"]*)"/', $dsl, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
