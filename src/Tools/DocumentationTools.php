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
 * MCP Tools for managing workspace documentation
 *
 * Provides tools to add documentation sections and Architecture Decision Records (ADRs)
 * to Structurizr workspaces. Documentation is stored as metadata in the workspace model.
 */
class DocumentationTools extends AbstractWorkspaceTool
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
     * Add a documentation section to the workspace
     *
     * Creates a new documentation section with the specified title and content.
     * Documentation sections are stored as metadata in the workspace model and can
     * be used to provide additional context, explanations, or instructions about
     * the architecture.
     *
     * @param string $workspaceId The ID of the workspace to add documentation to
     * @param string $title The title of the documentation section
     * @param string $content The content of the documentation section (Markdown supported)
     * @return array Confirmation with section details
     * @throws ToolCallException If workspace not found or operation fails
     */
    #[McpTool(
        name: 'add_documentation_section',
        description: 'Adds a documentation section to the workspace',
    )]
    public function addDocumentationSection(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Section title', minLength: 1, maxLength: 200)]
        string $title,
        #[Schema(description: 'Section content (Markdown supported)', minLength: 1)]
        string $content,
    ): array {
        $this->logger->info("Adding documentation section '{$title}' to workspace: {$workspaceId}");

        try {
            // Load the workspace
            $workspace = $this->workspaceManager->load($workspaceId);

            // Get current model data
            $model = $workspace->model;

            // Initialize documentation array if not exists
            if (!isset($model['documentation'])) {
                $model['documentation'] = [];
            }

            // Create documentation section
            $section = [
                'id' => 'doc_' . bin2hex(random_bytes(8)),
                'title' => $title,
                'content' => $content,
                'format' => 'Markdown',
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s.uP'),
            ];

            // Add section to documentation
            $model['documentation'][] = $section;

            // Update workspace with new model
            $updatedWorkspace = $workspace->withModel($model);

            // Save the workspace
            $this->workspaceManager->save($updatedWorkspace);

            $this->logger->info("Successfully added documentation section '{$title}' to workspace: {$workspaceId}");

            return [
                'success' => true,
                'workspaceId' => $workspaceId,
                'section' => [
                    'id' => $section['id'],
                    'title' => $section['title'],
                    'format' => $section['format'],
                    'createdAt' => $section['createdAt'],
                ],
                'message' => "Documentation section '{$title}' added successfully",
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException(
                "Failed to add documentation section to workspace '{$workspaceId}': " . $e->getMessage(),
            );
        }
    }

    /**
     * Add an Architecture Decision Record (ADR) to the workspace
     *
     * Creates a new ADR documenting an important architectural decision.
     * ADRs help track the context, decisions, and consequences of architectural
     * choices over time. They are stored as metadata in the workspace model.
     *
     * @param string $workspaceId The ID of the workspace to add the ADR to
     * @param string $id The ADR ID/number (e.g., "001", "042")
     * @param string $date The decision date in YYYY-MM-DD format
     * @param string $title The ADR title describing the decision
     * @param string $status The ADR status (Proposed, Accepted, Rejected, Deprecated, Superseded)
     * @param string $content The ADR content including context, decision, and consequences (Markdown supported)
     * @return array Confirmation with ADR details
     * @throws ToolCallException If workspace not found or operation fails
     */
    #[McpTool(
        name: 'add_adr',
        description: 'Adds an Architecture Decision Record (ADR) to the workspace',
    )]
    public function addAdr(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'ADR ID/number', pattern: '^\d+$')]
        string $id,
        #[Schema(description: 'Decision date (YYYY-MM-DD)', pattern: '^\d{4}-\d{2}-\d{2}$')]
        string $date,
        #[Schema(description: 'ADR title', minLength: 1, maxLength: 200)]
        string $title,
        #[Schema(description: 'ADR status', enum: ['Proposed', 'Accepted', 'Rejected', 'Deprecated', 'Superseded'])]
        string $status,
        #[Schema(description: 'ADR content (Markdown supported)', minLength: 1)]
        string $content,
    ): array {
        $this->logger->info("Adding ADR {$id} '{$title}' to workspace: {$workspaceId}");

        try {
            // Load the workspace
            $workspace = $this->workspaceManager->load($workspaceId);

            // Get current model data
            $model = $workspace->model;

            // Initialize ADRs array if not exists
            if (!isset($model['adrs'])) {
                $model['adrs'] = [];
            }

            // Check if ADR with this ID already exists
            foreach ($model['adrs'] as $existingAdr) {
                if ($existingAdr['id'] === $id) {
                    throw new ToolCallException("ADR with ID '{$id}' already exists in workspace '{$workspaceId}'");
                }
            }

            // Create ADR record
            $adr = [
                'id' => $id,
                'date' => $date,
                'title' => $title,
                'status' => $status,
                'content' => $content,
                'format' => 'Markdown',
                'createdAt' => (new \DateTimeImmutable())->format('Y-m-d\TH:i:s.uP'),
            ];

            // Add ADR to collection
            $model['adrs'][] = $adr;

            // Sort ADRs by ID
            usort($model['adrs'], function ($a, $b) {
                return (int) $a['id'] <=> (int) $b['id'];
            });

            // Update workspace with new model
            $updatedWorkspace = $workspace->withModel($model);

            // Save the workspace
            $this->workspaceManager->save($updatedWorkspace);

            $this->logger->info("Successfully added ADR {$id} '{$title}' to workspace: {$workspaceId}");

            return [
                'success' => true,
                'workspaceId' => $workspaceId,
                'adr' => [
                    'id' => $adr['id'],
                    'date' => $adr['date'],
                    'title' => $adr['title'],
                    'status' => $adr['status'],
                    'format' => $adr['format'],
                    'createdAt' => $adr['createdAt'],
                ],
                'message' => "ADR {$id} '{$title}' added successfully with status '{$status}'",
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (ToolCallException $e) {
            // Re-throw ToolCallException as-is
            throw $e;
        } catch (\Exception $e) {
            throw new ToolCallException(
                "Failed to add ADR to workspace '{$workspaceId}': " . $e->getMessage(),
            );
        }
    }
}
