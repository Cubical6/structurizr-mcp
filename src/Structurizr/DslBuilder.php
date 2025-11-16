<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr;

/**
 * Builder for generating Structurizr DSL
 */
class DslBuilder
{
    /** Background color for Software System elements */
    private const COLOR_SOFTWARE_SYSTEM_BG = '#1168bd';

    /** Foreground color for Software System elements */
    private const COLOR_SOFTWARE_SYSTEM_FG = '#ffffff';

    /** Background color for Person elements */
    private const COLOR_PERSON_BG = '#08427b';

    /** Foreground color for Person elements */
    private const COLOR_PERSON_FG = '#ffffff';

    /** Element type name for Software System */
    private const ELEMENT_TYPE_SOFTWARE_SYSTEM = 'Software System';

    /** Element type name for Person */
    private const ELEMENT_TYPE_PERSON = 'Person';

    /**
     * Name of the workspace
     *
     * @var string
     */
    private string $workspaceName = '';

    /**
     * Description of the workspace
     *
     * @var string
     */
    private string $workspaceDescription = '';

    /**
     * Collection of elements (people, systems, containers, components)
     *
     * @var array<string, array<string, mixed>>
     */
    private array $elements = [];

    /**
     * Collection of relationships between elements
     *
     * @var array<string, array<string, mixed>>
     */
    private array $relationships = [];

    /**
     * Collection of views (system context, container, component)
     *
     * @var array<int, array<string, mixed>>
     */
    private array $views = [];

    /**
     * Counter for generating unique element IDs
     *
     * @var int
     */
    private int $elementCounter = 0;

    public function workspace(string $name, string $description = ''): self
    {
        $this->workspaceName = $name;
        $this->workspaceDescription = $description;
        return $this;
    }

    public function addPerson(string $name, string $description = '', array $tags = []): string
    {
        $id = $this->generateId('person');
        $this->elements[$id] = [
            'type' => 'person',
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'tags' => $tags,
        ];
        return $id;
    }

    public function addSoftwareSystem(string $name, string $description = '', string $location = 'Internal', array $tags = []): string
    {
        $id = $this->generateId('system');
        $this->elements[$id] = [
            'type' => 'softwareSystem',
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'location' => $location,
            'tags' => $tags,
            'containers' => [],
        ];
        return $id;
    }

    public function addContainer(string $systemId, string $name, string $description = '', string $technology = '', array $tags = []): string
    {
        if (!isset($this->elements[$systemId]) || $this->elements[$systemId]['type'] !== 'softwareSystem') {
            throw new \InvalidArgumentException("System not found: {$systemId}");
        }

        $id = $this->generateId('container');
        $container = [
            'type' => 'container',
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'technology' => $technology,
            'tags' => $tags,
            'systemId' => $systemId,
            'components' => [],
        ];

        $this->elements[$id] = $container;
        $this->elements[$systemId]['containers'][] = $id;

        return $id;
    }

    public function addComponent(string $containerId, string $name, string $description = '', string $technology = '', array $tags = []): string
    {
        if (!isset($this->elements[$containerId]) || $this->elements[$containerId]['type'] !== 'container') {
            throw new \InvalidArgumentException("Container not found: {$containerId}");
        }

        $id = $this->generateId('component');
        $component = [
            'type' => 'component',
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'technology' => $technology,
            'tags' => $tags,
            'containerId' => $containerId,
        ];

        $this->elements[$id] = $component;
        $this->elements[$containerId]['components'][] = $id;

        return $id;
    }

    public function addRelationship(string $sourceId, string $destinationId, string $description, string $technology = '', array $tags = []): string
    {
        if (!isset($this->elements[$sourceId])) {
            throw new \InvalidArgumentException("Source element not found: {$sourceId}");
        }
        if (!isset($this->elements[$destinationId])) {
            throw new \InvalidArgumentException("Destination element not found: {$destinationId}");
        }

        $id = $this->generateId('relationship');
        $this->relationships[$id] = [
            'id' => $id,
            'sourceId' => $sourceId,
            'destinationId' => $destinationId,
            'description' => $description,
            'technology' => $technology,
            'tags' => $tags,
        ];

        return $id;
    }

    public function addSystemContextView(string $systemId, string $key, string $description = ''): string
    {
        $this->views[] = [
            'type' => 'systemContext',
            'systemId' => $systemId,
            'key' => $key,
            'description' => $description,
            'autoLayout' => 'lr',
        ];
        return $key;
    }

    public function addContainerView(string $systemId, string $key, string $description = ''): string
    {
        $this->views[] = [
            'type' => 'container',
            'systemId' => $systemId,
            'key' => $key,
            'description' => $description,
            'autoLayout' => 'lr',
        ];
        return $key;
    }

    public function addComponentView(string $containerId, string $key, string $description = ''): string
    {
        $this->views[] = [
            'type' => 'component',
            'containerId' => $containerId,
            'key' => $key,
            'description' => $description,
            'autoLayout' => 'lr',
        ];
        return $key;
    }

    public function dynamicView(string $elementId, string $key, string $description = ''): string
    {
        $this->views[] = [
            'type' => 'dynamic',
            'elementId' => $elementId,
            'key' => $key,
            'description' => $description,
            'autoLayout' => 'lr',
        ];
        return $key;
    }

    public function setViewAutoLayout(string $viewKey, string $direction): void
    {
        foreach ($this->views as &$view) {
            if ($view['key'] === $viewKey) {
                $view['autoLayout'] = $direction;
                return;
            }
        }
        throw new \InvalidArgumentException("View not found: {$viewKey}");
    }

    public function toDsl(): string
    {
        $dsl = "workspace \"{$this->workspaceName}\" \"{$this->workspaceDescription}\" {\n\n";
        $dsl .= "    model {\n";

        // Add people and systems
        foreach ($this->elements as $element) {
            if ($element['type'] === 'person') {
                $dsl .= $this->generatePersonDsl($element);
            } elseif ($element['type'] === 'softwareSystem') {
                $dsl .= $this->generateSystemDsl($element);
            }
        }

        // Add relationships
        foreach ($this->relationships as $rel) {
            $dsl .= $this->generateRelationshipDsl($rel);
        }

        $dsl .= "    }\n\n";

        // Add views
        if (!empty($this->views)) {
            $dsl .= "    views {\n";
            foreach ($this->views as $view) {
                $dsl .= $this->generateViewDsl($view);
            }
            $dsl .= "\n        styles {\n";
            $dsl .= "            element \"" . self::ELEMENT_TYPE_SOFTWARE_SYSTEM . "\" {\n";
            $dsl .= "                background " . self::COLOR_SOFTWARE_SYSTEM_BG . "\n";
            $dsl .= "                color " . self::COLOR_SOFTWARE_SYSTEM_FG . "\n";
            $dsl .= "            }\n";
            $dsl .= "            element \"" . self::ELEMENT_TYPE_PERSON . "\" {\n";
            $dsl .= "                background " . self::COLOR_PERSON_BG . "\n";
            $dsl .= "                color " . self::COLOR_PERSON_FG . "\n";
            $dsl .= "                shape person\n";
            $dsl .= "            }\n";
            $dsl .= "        }\n";
            $dsl .= "    }\n";
        }

        $dsl .= "}\n";

        return $dsl;
    }

    private function generatePersonDsl(array $element): string
    {
        $tags = $this->formatTags($element['tags']);
        return "        {$element['id']} = person \"{$element['name']}\" \"{$element['description']}\"{$tags}\n";
    }

    private function generateSystemDsl(array $element): string
    {
        $tags = $this->formatTags($element['tags']);
        $dsl = "        {$element['id']} = softwareSystem \"{$element['name']}\" \"{$element['description']}\"{$tags}";

        if (!empty($element['containers'])) {
            $dsl .= " {\n";
            foreach ($element['containers'] as $containerId) {
                if (isset($this->elements[$containerId])) {
                    $dsl .= $this->generateContainerDsl($this->elements[$containerId]);
                }
            }
            $dsl .= "        }\n";
        } else {
            $dsl .= "\n";
        }

        return $dsl;
    }

    private function generateContainerDsl(array $element): string
    {
        $techAndTags = $this->formatTechnologyAndTags(
            $element['technology'] ?? '',
            $element['tags']
        );
        $dsl = "            {$element['id']} = container \"{$element['name']}\" \"{$element['description']}\"{$techAndTags}";

        if (!empty($element['components'])) {
            $dsl .= " {\n";
            foreach ($element['components'] as $componentId) {
                if (isset($this->elements[$componentId])) {
                    $dsl .= $this->generateComponentDsl($this->elements[$componentId]);
                }
            }
            $dsl .= "            }\n";
        } else {
            $dsl .= "\n";
        }

        return $dsl;
    }

    private function generateComponentDsl(array $element): string
    {
        $techAndTags = $this->formatTechnologyAndTags(
            $element['technology'] ?? '',
            $element['tags']
        );
        return "                {$element['id']} = component \"{$element['name']}\" \"{$element['description']}\"{$techAndTags}\n";
    }

    private function generateRelationshipDsl(array $rel): string
    {
        $techAndTags = $this->formatTechnologyAndTags(
            $rel['technology'] ?? '',
            $rel['tags']
        );
        return "        {$rel['sourceId']} -> {$rel['destinationId']} \"{$rel['description']}\"{$techAndTags}\n";
    }

    private function generateViewDsl(array $view): string
    {
        $dsl = '';
        $autoLayout = $view['autoLayout'] ?? 'lr';

        switch ($view['type']) {
            case 'systemContext':
                $dsl .= "        systemContext {$view['systemId']} \"{$view['key']}\" {\n";
                $dsl .= "            include *\n";
                $dsl .= "            autoLayout {$autoLayout}\n";
                $dsl .= "        }\n";
                break;
            case 'container':
                $dsl .= "        container {$view['systemId']} \"{$view['key']}\" {\n";
                $dsl .= "            include *\n";
                $dsl .= "            autoLayout {$autoLayout}\n";
                $dsl .= "        }\n";
                break;
            case 'component':
                $dsl .= "        component {$view['containerId']} \"{$view['key']}\" {\n";
                $dsl .= "            include *\n";
                $dsl .= "            autoLayout {$autoLayout}\n";
                $dsl .= "        }\n";
                break;
            case 'dynamic':
                $description = !empty($view['description']) ? " \"{$view['description']}\"" : '';
                $dsl .= "        dynamic {$view['elementId']} \"{$view['key']}\"{$description} {\n";
                $dsl .= "            autoLayout {$autoLayout}\n";
                $dsl .= "        }\n";
                break;
        }
        return $dsl;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->workspaceName,
            'description' => $this->workspaceDescription,
            'elements' => $this->elements,
            'relationships' => $this->relationships,
            'views' => $this->views,
        ];
    }

    private function generateId(string $prefix): string
    {
        return $prefix . '_' . ++$this->elementCounter;
    }

    /**
     * Format technology and tags for DSL output with correct positional handling
     *
     * In DSL, technology and tags are positionally distinguished. When tags are present
     * but technology is empty, we must output an empty technology string to prevent
     * the parser from misinterpreting tags as technology.
     *
     * This method replaces the previous formatTechnology() and formatTags() helpers
     * to ensure correct positional formatting.
     *
     * @param string $technology Technology string (may be empty)
     * @param array<int, string> $tags Array of tag strings
     * @return string Formatted technology and tags string
     */
    private function formatTechnologyAndTags(string $technology, array $tags): string
    {
        $result = '';

        if (!empty($tags)) {
            // Tags present: always output technology (even if empty) to preserve position
            $result .= " \"{$technology}\"";
            $result .= ' "' . implode(',', $tags) . '"';
        } elseif ($technology !== '') {
            // No tags but technology present
            $result .= " \"{$technology}\"";
        }
        // If both empty, return empty string

        return $result;
    }

    /**
     * Format tags for DSL output
     *
     * Helper method for generating tag strings in Person and SoftwareSystem elements.
     * For containers, components, and relationships, use formatTechnologyAndTags() instead.
     *
     * @param array<int, string> $tags Array of tag strings
     * @return string Formatted tags string or empty string
     */
    private function formatTags(array $tags): string
    {
        return !empty($tags) ? ' "' . implode(',', $tags) . '"' : '';
    }

    public function getElement(string $id): ?array
    {
        return $this->elements[$id] ?? null;
    }

    public function findElement(string $name, ?string $type = null): ?array
    {
        foreach ($this->elements as $element) {
            if ($element['name'] === $name) {
                if ($type === null || $element['type'] === $type) {
                    return $element;
                }
            }
        }
        return null;
    }

    /**
     * Create a DslBuilder from existing DSL string
     *
     * Parses the DSL string to rebuild the builder's internal state,
     * preserving all existing elements, relationships, and views.
     *
     * @param string $dsl The DSL string to parse
     * @return self A new DslBuilder instance with the parsed state
     */
    public static function fromDsl(string $dsl): self
    {
        $builder = new self();

        if (empty(trim($dsl))) {
            return $builder;
        }

        // Parse workspace name and description
        if (preg_match('/workspace\s+"([^"]*)"\s+"([^"]*)"/', $dsl, $matches)) {
            $builder->workspaceName = $matches[1];
            $builder->workspaceDescription = $matches[2];
        }

        // Parse model section
        if (preg_match('/model\s*\{(.*)\n\s*\}/s', $dsl, $modelMatch)) {
            $modelContent = $modelMatch[1];
            $builder->parseModelSection($modelContent);
        }

        // Parse views section
        if (preg_match('/views\s*\{(.*)\n\s*\}/s', $dsl, $viewsMatch)) {
            $viewsContent = $viewsMatch[1];
            $builder->parseViewsSection($viewsContent);
        }

        return $builder;
    }

    /**
     * Parse the model section of DSL
     *
     * @param string $content The model section content
     * @return void
     */
    private function parseModelSection(string $content): void
    {
        $lines = explode("\n", $content);
        $currentSystem = null;
        $currentContainer = null;
        $systemStack = [];
        $containerStack = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                continue;
            }

            // Try each parser in sequence
            if ($element = $this->parsePerson($trimmed)) {
                $this->elements[$element['id']] = $element;
                $this->updateElementCounter($element['id']);
                continue;
            }

            if ($element = $this->parseSystem($trimmed)) {
                $this->elements[$element['id']] = $element;
                $this->updateElementCounter($element['id']);
                $currentSystem = $element['id'];
                $systemStack[] = $element['id'];
                continue;
            }

            if ($element = $this->parseContainer($trimmed, $currentSystem)) {
                if ($element !== null) {
                    $this->elements[$element['id']] = $element;
                    $this->elements[$currentSystem]['containers'][] = $element['id'];
                    $this->updateElementCounter($element['id']);
                    $currentContainer = $element['id'];
                    $containerStack[] = $element['id'];
                }
                continue;
            }

            if ($element = $this->parseComponent($trimmed, $currentContainer)) {
                if ($element !== null) {
                    $this->elements[$element['id']] = $element;
                    $this->elements[$currentContainer]['components'][] = $element['id'];
                    $this->updateElementCounter($element['id']);
                }
                continue;
            }

            if ($relationship = $this->parseRelationship($trimmed)) {
                $id = $this->generateId('relationship');
                $this->relationships[$id] = array_merge(['id' => $id], $relationship);
                continue;
            }

            // Track closing braces
            if ($trimmed === '}') {
                if (!empty($containerStack) && $currentContainer !== null) {
                    array_pop($containerStack);
                    $currentContainer = end($containerStack) ?: null;
                } elseif (!empty($systemStack) && $currentSystem !== null) {
                    array_pop($systemStack);
                    $currentSystem = end($systemStack) ?: null;
                }
            }
        }
    }

    /**
     * Parse a person element from a DSL line
     *
     * Pattern: person_1 = person "Name" "Description" "tags"
     *
     * @param string $line The trimmed DSL line to parse
     * @return array<string, mixed>|null Parsed element data or null if not a person
     */
    private function parsePerson(string $line): ?array
    {
        if (!preg_match('/^(\w+)\s*=\s*person\s+"([^"]*)"\s+"([^"]*)"(?:\s+"([^"]*)")?/', $line, $matches)) {
            return null;
        }

        return [
            'type' => 'person',
            'id' => $matches[1],
            'name' => $matches[2],
            'description' => $matches[3],
            'tags' => isset($matches[4]) ? explode(',', $matches[4]) : [],
        ];
    }

    /**
     * Parse a software system element from a DSL line
     *
     * Pattern: system_1 = softwareSystem "Name" "Description" "tags" {
     *
     * @param string $line The trimmed DSL line to parse
     * @return array<string, mixed>|null Parsed element data or null if not a system
     */
    private function parseSystem(string $line): ?array
    {
        if (!preg_match('/^(\w+)\s*=\s*softwareSystem\s+"([^"]*)"\s+"([^"]*)"(?:\s+"([^"]*)")?\s*\{?/', $line, $matches)) {
            return null;
        }

        return [
            'type' => 'softwareSystem',
            'id' => $matches[1],
            'name' => $matches[2],
            'description' => $matches[3],
            'location' => 'Internal',
            'tags' => isset($matches[4]) ? explode(',', $matches[4]) : [],
            'containers' => [],
        ];
    }

    /**
     * Parse a container element from a DSL line
     *
     * Pattern: container_1 = container "Name" "Description" "Technology" "tags" {
     *
     * @param string $line The trimmed DSL line to parse
     * @param string|null $currentSystem The current parent system ID
     * @return array<string, mixed>|null Parsed element data or null if not a container or no parent
     */
    private function parseContainer(string $line, ?string $currentSystem): ?array
    {
        if (!preg_match('/^(\w+)\s*=\s*container\s+"([^"]*)"\s+"([^"]*)"(?:\s+"([^"]*)")?(?:\s+"([^"]*)")?\s*\{?/', $line, $matches)) {
            return null;
        }

        if ($currentSystem === null) {
            return null; // Skip containers without a parent system
        }

        return [
            'type' => 'container',
            'id' => $matches[1],
            'name' => $matches[2],
            'description' => $matches[3],
            'technology' => $matches[4] ?? '',
            'tags' => isset($matches[5]) ? explode(',', $matches[5]) : [],
            'systemId' => $currentSystem,
            'components' => [],
        ];
    }

    /**
     * Parse a component element from a DSL line
     *
     * Pattern: component_1 = component "Name" "Description" "Technology" "tags"
     *
     * @param string $line The trimmed DSL line to parse
     * @param string|null $currentContainer The current parent container ID
     * @return array<string, mixed>|null Parsed element data or null if not a component or no parent
     */
    private function parseComponent(string $line, ?string $currentContainer): ?array
    {
        if (!preg_match('/^(\w+)\s*=\s*component\s+"([^"]*)"\s+"([^"]*)"(?:\s+"([^"]*)")?(?:\s+"([^"]*)")?/', $line, $matches)) {
            return null;
        }

        if ($currentContainer === null) {
            return null; // Skip components without a parent container
        }

        return [
            'type' => 'component',
            'id' => $matches[1],
            'name' => $matches[2],
            'description' => $matches[3],
            'technology' => $matches[4] ?? '',
            'tags' => isset($matches[5]) ? explode(',', $matches[5]) : [],
            'containerId' => $currentContainer,
        ];
    }

    /**
     * Parse a relationship from a DSL line
     *
     * Pattern: source -> destination "Description" "Technology" "tags"
     *
     * @param string $line The trimmed DSL line to parse
     * @return array<string, mixed>|null Parsed relationship data or null if not a relationship
     */
    private function parseRelationship(string $line): ?array
    {
        if (!preg_match('/^(\w+)\s*->\s*(\w+)\s+"([^"]*)"(?:\s+"([^"]*)")?(?:\s+"([^"]*)")?/', $line, $matches)) {
            return null;
        }

        return [
            'sourceId' => $matches[1],
            'destinationId' => $matches[2],
            'description' => $matches[3],
            'technology' => $matches[4] ?? '',
            'tags' => isset($matches[5]) ? explode(',', $matches[5]) : [],
        ];
    }

    /**
     * Parse the views section of DSL
     *
     * @param string $content The views section content
     * @return void
     */
    private function parseViewsSection(string $content): void
    {
        // Parse systemContext views
        if (preg_match_all('/systemContext\s+(\w+)\s+"([^"]+)"\s*\{[^}]*autoLayout\s+(\w+)/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $this->views[] = [
                    'type' => 'systemContext',
                    'systemId' => $match[1],
                    'key' => $match[2],
                    'description' => '',
                    'autoLayout' => $match[3],
                ];
            }
        }

        // Parse container views
        if (preg_match_all('/container\s+(\w+)\s+"([^"]+)"\s*\{[^}]*autoLayout\s+(\w+)/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $this->views[] = [
                    'type' => 'container',
                    'systemId' => $match[1],
                    'key' => $match[2],
                    'description' => '',
                    'autoLayout' => $match[3],
                ];
            }
        }

        // Parse component views
        if (preg_match_all('/component\s+(\w+)\s+"([^"]+)"\s*\{[^}]*autoLayout\s+(\w+)/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $this->views[] = [
                    'type' => 'component',
                    'containerId' => $match[1],
                    'key' => $match[2],
                    'description' => '',
                    'autoLayout' => $match[3],
                ];
            }
        }

        // Parse dynamic views
        if (preg_match_all('/dynamic\s+(\w+)\s+"([^"]+)"(?:\s+"([^"]*)")?\s*\{[^}]*autoLayout\s+(\w+)/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // Description is optional in DSL, may be empty string if not provided
                $description = array_key_exists(3, $match) ? $match[3] : '';
                $autoLayout = array_key_exists(4, $match) ? $match[4] : 'lr';
                $this->views[] = [
                    'type' => 'dynamic',
                    'elementId' => $match[1],
                    'key' => $match[2],
                    'description' => $description,
                    'autoLayout' => $autoLayout,
                ];
            }
        }
    }

    /**
     * Update the element counter based on parsed element ID
     *
     * @param string $id The element ID to analyze
     * @return void
     */
    private function updateElementCounter(string $id): void
    {
        // Extract numeric suffix from IDs like "person_1", "system_2", etc.
        if (preg_match('/_(\d+)$/', $id, $matches)) {
            $counter = (int) $matches[1];
            if ($counter > $this->elementCounter) {
                $this->elementCounter = $counter;
            }
        }
    }
}
