<?php

declare(strict_types=1);

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Structurizr\DslBuilder;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * MCP Tools for C4 model building (adding elements and relationships)
 */
class ModelTools
{
    public function __construct(
        private readonly WorkspaceManager $workspaceManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Add a person to the workspace
     *
     * Adds a new person (user, actor) to the C4 model. People represent human users
     * of the system being modeled.
     *
     * @param string $workspaceId The workspace ID
     * @param string $name The name of the person
     * @param string $description Description of the person's role
     * @param array $tags Optional tags for styling and filtering
     * @return array Element details including ID and name
     */
    #[McpTool(name: 'add_person', description: 'Add a person to the C4 model')]
    public function addPerson(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Name of the person/user', minLength: 1, maxLength: 100)]
        string $name,
        #[Schema(description: 'Description of the person', maxLength: 500)]
        string $description = '',
        #[Schema(description: 'Tags for styling', type: 'array')]
        array $tags = [],
    ): array {
        $this->logger->info("Adding person '{$name}' to workspace: {$workspaceId}");

        $workspace = $this->workspaceManager->load($workspaceId);

        // Build/update DSL
        $builder = $this->createBuilderFromWorkspace($workspace);
        $elementId = $builder->addPerson($name, $description, $tags);
        $dsl = $builder->toDsl();

        // Save updated workspace
        $updated = $workspace->withDsl($dsl);
        $this->workspaceManager->save($updated);

        return [
            'workspaceId' => $workspaceId,
            'elementId' => $elementId,
            'name' => $name,
            'type' => 'person',
            'description' => $description,
        ];
    }

    /**
     * Add a software system to the workspace
     *
     * Adds a software system to the C4 model. Systems are the highest level of
     * abstraction and represent applications or services.
     *
     * @param string $workspaceId The workspace ID
     * @param string $name The name of the software system
     * @param string $description Description of what the system does
     * @param string $location 'Internal' or 'External' (default: Internal)
     * @param array $tags Optional tags for styling
     * @return array Element details including ID and name
     */
    #[McpTool(name: 'add_software_system', description: 'Add a software system to the C4 model')]
    public function addSoftwareSystem(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'System name', minLength: 1, maxLength: 100)]
        string $name,
        #[Schema(description: 'System description', maxLength: 500)]
        string $description = '',
        #[Schema(description: 'System location', enum: ['Internal', 'External'])]
        string $location = 'Internal',
        #[Schema(description: 'Tags for styling', type: 'array')]
        array $tags = [],
    ): array {
        $this->logger->info("Adding software system '{$name}' to workspace: {$workspaceId}");

        if (!in_array($location, ['Internal', 'External'], true)) {
            throw new \InvalidArgumentException("Location must be 'Internal' or 'External'");
        }

        $workspace = $this->workspaceManager->load($workspaceId);

        $builder = $this->createBuilderFromWorkspace($workspace);
        $elementId = $builder->addSoftwareSystem($name, $description, $location, $tags);
        $dsl = $builder->toDsl();

        $updated = $workspace->withDsl($dsl);
        $this->workspaceManager->save($updated);

        return [
            'workspaceId' => $workspaceId,
            'elementId' => $elementId,
            'name' => $name,
            'type' => 'softwareSystem',
            'location' => $location,
            'description' => $description,
        ];
    }

    /**
     * Add a container to a software system
     *
     * Adds a container (application, database, microservice, etc.) to a software system.
     * Containers represent deployable/executable units.
     *
     * @param string $workspaceId The workspace ID
     * @param string $systemId The ID of the parent software system
     * @param string $name The name of the container
     * @param string $description Description of the container's purpose
     * @param string $technology Technology/platform (e.g., "Java Spring Boot", "PostgreSQL")
     * @param array $tags Optional tags for styling
     * @return array Container details including ID and name
     */
    #[McpTool(name: 'add_container', description: 'Add a container to a software system')]
    public function addContainer(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Parent system ID', minLength: 1)]
        string $systemId,
        #[Schema(description: 'Container name', minLength: 1, maxLength: 100)]
        string $name,
        #[Schema(description: 'Container description', maxLength: 500)]
        string $description = '',
        #[Schema(description: 'Technology/platform', maxLength: 200)]
        string $technology = '',
        #[Schema(description: 'Tags for styling', type: 'array')]
        array $tags = [],
    ): array {
        $this->logger->info("Adding container '{$name}' to system '{$systemId}' in workspace: {$workspaceId}");

        $workspace = $this->workspaceManager->load($workspaceId);

        $builder = $this->createBuilderFromWorkspace($workspace);
        $elementId = $builder->addContainer($systemId, $name, $description, $technology, $tags);
        $dsl = $builder->toDsl();

        $updated = $workspace->withDsl($dsl);
        $this->workspaceManager->save($updated);

        return [
            'workspaceId' => $workspaceId,
            'elementId' => $elementId,
            'systemId' => $systemId,
            'name' => $name,
            'type' => 'container',
            'technology' => $technology,
            'description' => $description,
        ];
    }

    /**
     * Add a component to a container
     *
     * Adds a component to a container. Components represent logical groupings of
     * functionality within a container.
     *
     * @param string $workspaceId The workspace ID
     * @param string $containerId The ID of the parent container
     * @param string $name The name of the component
     * @param string $description Description of the component's responsibility
     * @param string $technology Technology/framework used
     * @param array $tags Optional tags for styling
     * @return array Component details including ID and name
     */
    #[McpTool(name: 'add_component', description: 'Add a component to a container')]
    public function addComponent(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Parent container ID', minLength: 1)]
        string $containerId,
        #[Schema(description: 'Component name', minLength: 1, maxLength: 100)]
        string $name,
        #[Schema(description: 'Component description', maxLength: 500)]
        string $description = '',
        #[Schema(description: 'Technology/framework', maxLength: 200)]
        string $technology = '',
        #[Schema(description: 'Tags for styling', type: 'array')]
        array $tags = [],
    ): array {
        $this->logger->info("Adding component '{$name}' to container '{$containerId}' in workspace: {$workspaceId}");

        $workspace = $this->workspaceManager->load($workspaceId);

        $builder = $this->createBuilderFromWorkspace($workspace);
        $elementId = $builder->addComponent($containerId, $name, $description, $technology, $tags);
        $dsl = $builder->toDsl();

        $updated = $workspace->withDsl($dsl);
        $this->workspaceManager->save($updated);

        return [
            'workspaceId' => $workspaceId,
            'elementId' => $elementId,
            'containerId' => $containerId,
            'name' => $name,
            'type' => 'component',
            'technology' => $technology,
            'description' => $description,
        ];
    }

    /**
     * Add a relationship between elements
     *
     * Creates a relationship (interaction) between two elements in the model.
     * Relationships show how elements communicate or depend on each other.
     *
     * @param string $workspaceId The workspace ID
     * @param string $sourceId ID of the source element
     * @param string $destinationId ID of the destination element
     * @param string $description Description of the relationship (e.g., "Sends data to", "Uses")
     * @param string $technology Technology/protocol used (e.g., "HTTPS", "gRPC", "SQL")
     * @param array $tags Optional tags for styling
     * @return array Relationship details including ID and description
     */
    #[McpTool(name: 'add_relationship', description: 'Add a relationship between two elements')]
    public function addRelationship(
        #[Schema(description: 'Workspace ID', minLength: 1)]
        string $workspaceId,
        #[Schema(description: 'Source element ID', minLength: 1)]
        string $sourceId,
        #[Schema(description: 'Destination element ID', minLength: 1)]
        string $destinationId,
        #[Schema(description: 'Relationship description', minLength: 1, maxLength: 200)]
        string $description,
        #[Schema(description: 'Technology/protocol', maxLength: 200)]
        string $technology = '',
        #[Schema(description: 'Tags for styling', type: 'array')]
        array $tags = [],
    ): array {
        $this->logger->info("Adding relationship from '{$sourceId}' to '{$destinationId}' in workspace: {$workspaceId}");

        $workspace = $this->workspaceManager->load($workspaceId);

        $builder = $this->createBuilderFromWorkspace($workspace);
        $relationshipId = $builder->addRelationship($sourceId, $destinationId, $description, $technology, $tags);
        $dsl = $builder->toDsl();

        $updated = $workspace->withDsl($dsl);
        $this->workspaceManager->save($updated);

        return [
            'workspaceId' => $workspaceId,
            'relationshipId' => $relationshipId,
            'sourceId' => $sourceId,
            'destinationId' => $destinationId,
            'description' => $description,
            'technology' => $technology,
        ];
    }

    /**
     * Create DSL builder from existing workspace
     */
    private function createBuilderFromWorkspace(Workspace $workspace): DslBuilder
    {
        $builder = new DslBuilder();

        // If workspace already has a model, we need to reconstruct the builder
        // For now, start fresh with workspace name and description
        $builder->workspace($workspace->name, $workspace->description);

        // TODO: If we need to support editing existing workspaces,
        // we would parse the existing DSL here to rebuild the builder state

        return $builder;
    }
}
