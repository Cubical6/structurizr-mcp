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
 * MCP Resource for accessing individual elements in a workspace
 *
 * Provides dynamic resource for retrieving specific architectural elements
 * (persons, software systems, containers, components) from a workspace model.
 */
class ElementResource
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
     * Get a specific element from workspace model
     *
     * Retrieves detailed information about a specific architectural element
     * including its properties, tags, and relationships.
     *
     * @param string $workspaceId The workspace identifier
     * @param string $elementId The element identifier
     * @return array<string, mixed> Element data including properties and relationships
     * @throws ResourceNotFoundException If workspace or element is not found
     * @throws ResourceReadException If element cannot be loaded
     */
    #[McpResourceTemplate(
        uriTemplate: 'structurizr://workspace/{workspaceId}/element/{elementId}',
        name: 'workspace_element',
        description: 'Specific architectural element data from workspace model',
        mimeType: 'application/json'
    )]
    public function getElement(string $workspaceId, string $elementId): array
    {
        $this->logger->debug("Retrieving element: {$elementId} from workspace: {$workspaceId}");

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            // Search for element in model
            $element = $this->findElement($workspace->model, $elementId);

            if ($element === null) {
                throw new ResourceNotFoundException("Element '{$elementId}' not found in workspace '{$workspaceId}'");
            }

            return [
                'workspaceId' => $workspace->id,
                'element' => $element,
            ];
        } catch (WorkspaceNotFoundException $e) {
            throw new ResourceNotFoundException("Workspace not found: {$workspaceId}");
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ResourceReadException("Failed to retrieve element '{$elementId}': " . $e->getMessage());
        }
    }

    /**
     * Find an element in the workspace model by ID
     *
     * Searches through the model structure to find an element with the given ID.
     * Searches in people, software systems, containers, and components.
     *
     * @param array<string, mixed> $model The workspace model
     * @param string $elementId The element ID to find
     * @return array<string, mixed>|null The element data or null if not found
     */
    private function findElement(array $model, string $elementId): ?array
    {
        // Search in people
        if (isset($model['people']) && is_array($model['people'])) {
            foreach ($model['people'] as $person) {
                if (is_array($person) && isset($person['id']) && $person['id'] === $elementId) {
                    return $person;
                }
            }
        }

        // Search in software systems
        if (isset($model['softwareSystems']) && is_array($model['softwareSystems'])) {
            foreach ($model['softwareSystems'] as $system) {
                if (!is_array($system)) {
                    continue;
                }

                if (isset($system['id']) && $system['id'] === $elementId) {
                    return $system;
                }

                // Search in containers
                if (isset($system['containers']) && is_array($system['containers'])) {
                    foreach ($system['containers'] as $container) {
                        if (!is_array($container)) {
                            continue;
                        }

                        if (isset($container['id']) && $container['id'] === $elementId) {
                            return $container;
                        }

                        // Search in components
                        if (isset($container['components']) && is_array($container['components'])) {
                            foreach ($container['components'] as $component) {
                                if (is_array($component) && isset($component['id']) && $component['id'] === $elementId) {
                                    return $component;
                                }
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}
