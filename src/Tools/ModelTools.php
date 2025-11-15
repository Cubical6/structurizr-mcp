<?php

declare(strict_types=1);

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Structurizr\DslBuilder;
use Psr\Log\LoggerInterface;

/**
 * MCP Tools for C4 model building (adding elements and relationships)
 */
class ModelTools
{
    public function __construct(
        private readonly WorkspaceManager $workspaceManager,
        private readonly LoggerInterface $logger
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
        string $workspaceId,
        string $name,
        string $description = '',
        array $tags = []
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
        string $workspaceId,
        string $name,
        string $description = '',
        string $location = 'Internal',
        array $tags = []
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
        string $workspaceId,
        string $systemId,
        string $name,
        string $description = '',
        string $technology = '',
        array $tags = []
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
        string $workspaceId,
        string $containerId,
        string $name,
        string $description = '',
        string $technology = '',
        array $tags = []
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
        string $workspaceId,
        string $sourceId,
        string $destinationId,
        string $description,
        string $technology = '',
        array $tags = []
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
     * Create a system context view
     *
     * Creates a system context diagram view showing a system and its relationships
     * with users and other systems.
     *
     * @param string $workspaceId The workspace ID
     * @param string $systemId The software system to focus on
     * @param string $key Unique key for the view
     * @param string $description Optional description of the view
     * @return array View details including key and type
     */
    #[McpTool(name: 'create_system_context_view', description: 'Create a system context diagram view')]
    public function createSystemContextView(
        string $workspaceId,
        string $systemId,
        string $key,
        string $description = ''
    ): array {
        $this->logger->info("Creating system context view '{$key}' for system '{$systemId}' in workspace: {$workspaceId}");

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
    }

    /**
     * Create DSL builder from existing workspace
     */
    private function createBuilderFromWorkspace($workspace): DslBuilder
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
