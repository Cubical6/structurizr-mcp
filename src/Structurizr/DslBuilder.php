<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr;

/**
 * Builder for generating Structurizr DSL
 */
class DslBuilder
{
    private string $workspaceName = '';
    private string $workspaceDescription = '';
    private array $elements = [];
    private array $relationships = [];
    private array $views = [];
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
            $dsl .= "            element \"Software System\" {\n";
            $dsl .= "                background #1168bd\n";
            $dsl .= "                color #ffffff\n";
            $dsl .= "            }\n";
            $dsl .= "            element \"Person\" {\n";
            $dsl .= "                background #08427b\n";
            $dsl .= "                color #ffffff\n";
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
        $tags = !empty($element['tags']) ? ' "' . implode(',', $element['tags']) . '"' : '';
        return "        {$element['id']} = person \"{$element['name']}\" \"{$element['description']}\"{$tags}\n";
    }

    private function generateSystemDsl(array $element): string
    {
        $tags = !empty($element['tags']) ? ' "' . implode(',', $element['tags']) . '"' : '';
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
        $tags = !empty($element['tags']) ? ' "' . implode(',', $element['tags']) . '"' : '';
        $tech = $element['technology'] ? " \"{$element['technology']}\"" : '';
        $dsl = "            {$element['id']} = container \"{$element['name']}\" \"{$element['description']}\"{$tech}{$tags}";

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
        $tags = !empty($element['tags']) ? ' "' . implode(',', $element['tags']) . '"' : '';
        $tech = $element['technology'] ? " \"{$element['technology']}\"" : '';
        return "                {$element['id']} = component \"{$element['name']}\" \"{$element['description']}\"{$tech}{$tags}\n";
    }

    private function generateRelationshipDsl(array $rel): string
    {
        $tech = $rel['technology'] ? " \"{$rel['technology']}\"" : '';
        $tags = !empty($rel['tags']) ? ' "' . implode(',', $rel['tags']) . '"' : '';
        return "        {$rel['sourceId']} -> {$rel['destinationId']} \"{$rel['description']}\"{$tech}{$tags}\n";
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
}
