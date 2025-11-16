<?php

declare(strict_types=1);

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * MCP Tools for workspace analysis and validation
 *
 * Provides tools for analyzing dependencies between elements,
 * searching for elements, and validating workspace structure.
 */
class AnalysisTools
{
    /**
     * Constructor
     *
     * @param WorkspaceManager $workspaceManager Manager for workspace operations
     * @param CliWrapper $cliWrapper Wrapper for Structurizr CLI operations
     * @param LoggerInterface $logger Logger for debugging and info messages
     */
    public function __construct(
        private readonly WorkspaceManager $workspaceManager,
        private readonly CliWrapper $cliWrapper,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Analyze dependencies between elements in the workspace
     *
     * Analyzes the relationships between elements in the workspace.
     * If elementId is provided, returns only dependencies for that specific element.
     * Otherwise, returns a complete dependency graph for the entire workspace.
     *
     * @param string $workspaceId The ID of the workspace to analyze
     * @param string|null $elementId Optional specific element ID to analyze
     * @return array<string, mixed> Dependency analysis including inbound/outbound relationships
     */
    #[McpTool(
        name: 'analyze_dependencies',
        description: 'Analyzes dependencies between elements in the workspace',
    )]
    public function analyzeDependencies(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Specific element ID to analyze (optional)')]
        ?string $elementId = null,
    ): array {
        $this->logger->info("Analyzing dependencies for workspace: {$workspaceId}", [
            'elementId' => $elementId,
        ]);

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            // Parse DSL to extract elements and relationships
            $elements = $this->parseElements($workspace->dsl);
            $relationships = $this->parseRelationships($workspace->dsl);

            if ($elementId !== null) {
                // Analyze specific element
                if (!isset($elements[$elementId])) {
                    throw new ToolCallException("Element not found: {$elementId}");
                }

                $inbound = array_filter(
                    $relationships,
                    fn ($rel) => $rel['destination'] === $elementId,
                );

                $outbound = array_filter(
                    $relationships,
                    fn ($rel) => $rel['source'] === $elementId,
                );

                return [
                    'workspaceId' => $workspaceId,
                    'elementId' => $elementId,
                    'element' => $elements[$elementId],
                    'inboundDependencies' => array_values($inbound),
                    'outboundDependencies' => array_values($outbound),
                    'totalInbound' => count($inbound),
                    'totalOutbound' => count($outbound),
                ];
            }

            // Analyze entire workspace
            $dependencyGraph = [];
            foreach ($elements as $id => $element) {
                $inbound = array_filter(
                    $relationships,
                    fn ($rel) => $rel['destination'] === $id,
                );
                $outbound = array_filter(
                    $relationships,
                    fn ($rel) => $rel['source'] === $id,
                );

                $dependencyGraph[$id] = [
                    'element' => $element,
                    'inboundCount' => count($inbound),
                    'outboundCount' => count($outbound),
                    'totalDependencies' => count($inbound) + count($outbound),
                ];
            }

            // Sort by total dependencies (most connected first)
            uasort($dependencyGraph, fn ($a, $b) => $b['totalDependencies'] <=> $a['totalDependencies']);

            return [
                'workspaceId' => $workspaceId,
                'totalElements' => count($elements),
                'totalRelationships' => count($relationships),
                'dependencyGraph' => $dependencyGraph,
                'relationships' => $relationships,
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (ToolCallException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to analyze dependencies: " . $e->getMessage());
        }
    }

    /**
     * Search for elements by name in the workspace
     *
     * Searches for elements (persons, systems, containers, components) by name.
     * Supports partial and case-insensitive matching.
     *
     * @param string $workspaceId The ID of the workspace to search
     * @param string $name Element name to search for
     * @return array<string, mixed> Array of matching elements with their details
     */
    #[McpTool(
        name: 'find_element',
        description: 'Searches for elements by name in the workspace',
    )]
    public function findElement(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Element name to search for', minLength: 1)]
        string $name,
    ): array {
        $this->logger->info("Finding elements in workspace: {$workspaceId}", [
            'searchName' => $name,
        ]);

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            // Parse DSL to extract elements
            $elements = $this->parseElements($workspace->dsl);

            // Search for matching elements (case-insensitive, partial match)
            $searchTerm = strtolower($name);
            $matches = [];

            foreach ($elements as $id => $element) {
                $elementName = strtolower($element['name']);

                if (str_contains($elementName, $searchTerm)) {
                    $matches[] = [
                        'id' => $id,
                        'name' => $element['name'],
                        'type' => $element['type'],
                        'description' => $element['description'] ?? '',
                        'technology' => $element['technology'] ?? null,
                    ];
                }
            }

            return [
                'workspaceId' => $workspaceId,
                'searchTerm' => $name,
                'matches' => $matches,
                'count' => count($matches),
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to find elements: " . $e->getMessage());
        }
    }

    /**
     * Validate workspace DSL syntax and structure
     *
     * Validates the workspace DSL using the Structurizr CLI validator.
     * Returns validation results including any errors and warnings.
     *
     * @param string $workspaceId The ID of the workspace to validate
     * @return array<string, mixed> Validation results with isValid, errors, and warnings
     */
    #[McpTool(
        name: 'validate_workspace',
        description: 'Validates workspace DSL syntax and structure',
    )]
    public function validateWorkspace(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
    ): array {
        $this->logger->info("Validating workspace: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            // If DSL is empty, return invalid result
            if (empty(trim($workspace->dsl))) {
                return [
                    'workspaceId' => $workspaceId,
                    'isValid' => false,
                    'errors' => ['Workspace DSL is empty'],
                    'warnings' => [],
                    'summary' => 'Validation failed: Workspace DSL is empty',
                ];
            }

            // Create temporary file for validation
            $tempFile = tempnam(sys_get_temp_dir(), 'ws_validate_');
            if ($tempFile === false) {
                throw new \RuntimeException('Failed to create temporary file for validation');
            }

            try {
                // Write DSL to temp file
                file_put_contents($tempFile, $workspace->dsl);

                // Validate using CLI
                $validationResult = $this->cliWrapper->validate($tempFile);

                return [
                    'workspaceId' => $workspaceId,
                    'isValid' => $validationResult->isValid(),
                    'errors' => $validationResult->getErrors(),
                    'warnings' => $validationResult->getWarnings(),
                    'summary' => $validationResult->getSummary(),
                    'errorCount' => $validationResult->getErrorCount(),
                    'warningCount' => $validationResult->getWarningCount(),
                ];
            } finally {
                // Clean up temp file
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
        } catch (WorkspaceNotFoundException $e) {
            throw new ToolCallException("Workspace not found: {$workspaceId}");
        } catch (\Exception $e) {
            throw new ToolCallException("Failed to validate workspace: " . $e->getMessage());
        }
    }

    /**
     * Parse elements from DSL
     *
     * Extracts all elements (person, softwareSystem, container, component)
     * from the workspace DSL.
     *
     * @param string $dsl The DSL content to parse
     * @return array<string, array<string, mixed>> Associative array of elements indexed by ID
     */
    private function parseElements(string $dsl): array
    {
        $elements = [];
        $lines = explode("\n", $dsl);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines and comments
            if (empty($trimmed) || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '//')) {
                continue;
            }

            // Parse person: id = person "Name" "Description"
            if (preg_match('/^(\w+)\s*=\s*person\s+"([^"]+)"\s+"([^"]*)"/', $trimmed, $matches)) {
                $elements[$matches[1]] = [
                    'name' => $matches[2],
                    'type' => 'person',
                    'description' => $matches[3],
                ];
            }

            // Parse softwareSystem: id = softwareSystem "Name" "Description"
            if (preg_match('/^(\w+)\s*=\s*softwareSystem\s+"([^"]+)"\s+"([^"]*)"/', $trimmed, $matches)) {
                $elements[$matches[1]] = [
                    'name' => $matches[2],
                    'type' => 'softwareSystem',
                    'description' => $matches[3],
                ];
            }

            // Parse container: id = container "Name" "Description" "Technology"
            if (preg_match('/^(\w+)\s*=\s*container\s+"([^"]+)"\s+"([^"]*)"\s+"([^"]*)"/', $trimmed, $matches)) {
                $elements[$matches[1]] = [
                    'name' => $matches[2],
                    'type' => 'container',
                    'description' => $matches[3],
                    'technology' => $matches[4],
                ];
            }

            // Parse component: id = component "Name" "Description" "Technology"
            if (preg_match('/^(\w+)\s*=\s*component\s+"([^"]+)"\s+"([^"]*)"\s+"([^"]*)"/', $trimmed, $matches)) {
                $elements[$matches[1]] = [
                    'name' => $matches[2],
                    'type' => 'component',
                    'description' => $matches[3],
                    'technology' => $matches[4],
                ];
            }
        }

        return $elements;
    }

    /**
     * Parse relationships from DSL
     *
     * Extracts all relationships (source -> destination) from the workspace DSL.
     *
     * @param string $dsl The DSL content to parse
     * @return array<int, array<string, mixed>> Array of relationships with source, destination, and description
     */
    private function parseRelationships(string $dsl): array
    {
        $relationships = [];
        $lines = explode("\n", $dsl);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines and comments
            if (empty($trimmed) || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '//')) {
                continue;
            }

            // Parse relationship: source -> destination "Description" "Technology"
            if (preg_match('/^(\w+)\s*->\s*(\w+)\s+"([^"]*)"\s*(?:"([^"]*)")?/', $trimmed, $matches)) {
                $relationships[] = [
                    'source' => $matches[1],
                    'destination' => $matches[2],
                    'description' => $matches[3],
                    'technology' => $matches[4] ?? null,
                ];
            }
        }

        return $relationships;
    }
}
