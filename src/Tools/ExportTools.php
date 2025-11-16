<?php

declare(strict_types=1);

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Exception\InvalidDslException;
use Psr\Log\LoggerInterface;

/**
 * MCP Tools for exporting Structurizr workspaces to various formats
 */
class ExportTools
{
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
    ): array
    {
        $this->logger->debug("Exporting workspace to DSL: {$workspaceId}");

        $workspace = $this->workspaceManager->load($workspaceId);

        return [
            'workspaceId' => $workspace->id,
            'name' => $workspace->name,
            'dsl' => $workspace->dsl,
        ];
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
    ): array
    {
        $this->logger->debug("Exporting workspace to PlantUML: {$workspaceId}", [
            'viewKey' => $viewKey,
        ]);

        $workspace = $this->workspaceManager->load($workspaceId);

        // Create temp DSL file
        $tempPath = sys_get_temp_dir() . '/' . uniqid('ws_export_', true) . '.dsl';

        try {
            file_put_contents($tempPath, $workspace->dsl);

            // Export using CLI
            $plantUml = $this->cliWrapper->export($tempPath, 'plantuml');

            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'viewKey' => $viewKey,
                'format' => 'plantuml',
                'content' => $plantUml,
            ];
        } finally {
            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
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
    ): array
    {
        $this->logger->debug("Exporting workspace to Mermaid: {$workspaceId}", [
            'viewKey' => $viewKey,
        ]);

        $workspace = $this->workspaceManager->load($workspaceId);

        // Create temp DSL file
        $tempPath = sys_get_temp_dir() . '/' . uniqid('ws_export_', true) . '.dsl';

        try {
            file_put_contents($tempPath, $workspace->dsl);

            // Export using CLI
            $mermaid = $this->cliWrapper->export($tempPath, 'mermaid');

            return [
                'workspaceId' => $workspace->id,
                'name' => $workspace->name,
                'viewKey' => $viewKey,
                'format' => 'mermaid',
                'content' => $mermaid,
            ];
        } finally {
            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
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
    ): array
    {
        $this->logger->info('Importing workspace from DSL');

        if (empty(trim($dsl))) {
            throw new InvalidDslException('DSL content cannot be empty');
        }

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
        return 'Imported Workspace';
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
