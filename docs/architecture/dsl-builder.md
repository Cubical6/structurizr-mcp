# DSL Builder

## Introduction

The `DslBuilder` class is a **fluent builder** that generates Structurizr DSL from workspace model data. It provides a programmatic interface for constructing C4 architecture diagrams while maintaining the hierarchical structure required by the Structurizr DSL format.

## Architecture

```
┌──────────────────────────────────────────────────────┐
│                    DslBuilder                         │
│                                                       │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  │
│  │  Elements   │  │Relationships│  │    Views    │  │
│  │   Array     │  │    Array    │  │    Array    │  │
│  └─────────────┘  └─────────────┘  └─────────────┘  │
│         │                 │                 │        │
│         └─────────────────┴─────────────────┘        │
│                         │                            │
│                         │                            │
│                    ┌────▼─────┐                      │
│                    │  toDsl() │                      │
│                    └────┬─────┘                      │
│                         │                            │
└─────────────────────────┼────────────────────────────┘
                          │
                          ▼
                   Structurizr DSL
                     (String)
```

## Core Concepts

### Builder Pattern

The DslBuilder implements the **Builder Pattern** for constructing complex DSL output:

**Benefits:**
1. **Fluent Interface** - Method chaining for readability
2. **Incremental Construction** - Build step by step
3. **Validation** - Check constraints during construction
4. **Immutability** - Final output is read-only

**Example:**

```php
$builder = new DslBuilder();

$builder
    ->workspace('My System', 'System description')
    ->addPerson('User', 'A user of the system')
    ->addSoftwareSystem('System', 'The main system')
    ->addRelationship('person_1', 'system_1', 'Uses')
    ->addSystemContextView('system_1', 'Context');

$dsl = $builder->toDsl();
```

### Internal State

The builder maintains three primary arrays:

**1. Elements Array**

Stores all model elements (people, systems, containers, components):

```php
private array $elements = [
    'person_1' => [
        'type' => 'person',
        'id' => 'person_1',
        'name' => 'User',
        'description' => 'A user',
        'tags' => ['External']
    ],
    'system_1' => [
        'type' => 'softwareSystem',
        'id' => 'system_1',
        'name' => 'System',
        'description' => 'The system',
        'location' => 'Internal',
        'tags' => [],
        'containers' => ['container_1']
    ],
    'container_1' => [
        'type' => 'container',
        'id' => 'container_1',
        'name' => 'Web App',
        'description' => 'Frontend',
        'technology' => 'React',
        'systemId' => 'system_1',
        'tags' => [],
        'components' => []
    ]
];
```

**2. Relationships Array**

Stores connections between elements:

```php
private array $relationships = [
    'relationship_1' => [
        'id' => 'relationship_1',
        'sourceId' => 'person_1',
        'destinationId' => 'system_1',
        'description' => 'Uses',
        'technology' => 'HTTPS',
        'tags' => []
    ]
];
```

**3. Views Array**

Stores view definitions:

```php
private array $views = [
    [
        'type' => 'systemContext',
        'systemId' => 'system_1',
        'key' => 'Context',
        'description' => 'System context view',
        'autoLayout' => 'lr'
    ]
];
```

## Element Management

### ID Generation

The builder automatically generates unique IDs:

```php
private int $elementCounter = 0;

private function generateId(string $prefix): string
{
    return $prefix . '_' . ++$this->elementCounter;
}
```

**ID Format:**
- `person_1`, `person_2`, ...
- `system_1`, `system_2`, ...
- `container_1`, `container_2`, ...
- `component_1`, `component_2`, ...
- `relationship_1`, `relationship_2`, ...

**Auto-increment ensures:**
- Uniqueness across all elements
- Predictable naming
- Easy reference in relationships

### Adding Elements

#### People

```php
public function addPerson(
    string $name,
    string $description = '',
    array $tags = []
): string {
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
```

**Usage:**

```php
$userId = $builder->addPerson(
    name: 'Customer',
    description: 'A customer of the platform',
    tags: ['External']
);
// Returns: 'person_1'
```

#### Software Systems

```php
public function addSoftwareSystem(
    string $name,
    string $description = '',
    string $location = 'Internal',
    array $tags = []
): string {
    $id = $this->generateId('system');

    $this->elements[$id] = [
        'type' => 'softwareSystem',
        'id' => $id,
        'name' => $name,
        'description' => $description,
        'location' => $location,
        'tags' => $tags,
        'containers' => [],  // Child containers
    ];

    return $id;
}
```

**Usage:**

```php
$systemId = $builder->addSoftwareSystem(
    name: 'E-commerce Platform',
    description: 'Online shopping system',
    location: 'Internal'
);
// Returns: 'system_1'
```

#### Containers

```php
public function addContainer(
    string $systemId,
    string $name,
    string $description = '',
    string $technology = '',
    array $tags = []
): string {
    // Validate parent system exists
    if (!isset($this->elements[$systemId]) ||
        $this->elements[$systemId]['type'] !== 'softwareSystem') {
        throw new InvalidArgumentException("System not found: {$systemId}");
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
        'components' => [],  // Child components
    ];

    // Add to elements
    $this->elements[$id] = $container;

    // Register with parent system
    $this->elements[$systemId]['containers'][] = $id;

    return $id;
}
```

**Usage:**

```php
$webAppId = $builder->addContainer(
    systemId: 'system_1',
    name: 'Web Application',
    description: 'Customer-facing UI',
    technology: 'React, TypeScript',
    tags: ['Web']
);
// Returns: 'container_1'
```

#### Components

```php
public function addComponent(
    string $containerId,
    string $name,
    string $description = '',
    string $technology = '',
    array $tags = []
): string {
    // Validate parent container exists
    if (!isset($this->elements[$containerId]) ||
        $this->elements[$containerId]['type'] !== 'container') {
        throw new InvalidArgumentException("Container not found: {$containerId}");
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

    // Add to elements
    $this->elements[$id] = $component;

    // Register with parent container
    $this->elements[$containerId]['components'][] = $id;

    return $id;
}
```

**Usage:**

```php
$controllerId = $builder->addComponent(
    containerId: 'container_1',
    name: 'Product Controller',
    description: 'Handles product requests',
    technology: 'Express Controller'
);
// Returns: 'component_1'
```

### Hierarchical Structure

Elements maintain parent-child relationships:

```
Software System
    └── containers: [container_1, container_2]
        Container (container_1)
            └── components: [component_1, component_2]
                Component (component_1)
            └── systemId: system_1
        Container (container_2)
            └── components: []
            └── systemId: system_1
```

**This hierarchy enables:**
1. **Nested DSL generation** - Proper indentation
2. **Validation** - Ensure valid parent references
3. **Traversal** - Find all children of a system
4. **Deletion** - Cascade delete children

## Relationships

### Adding Relationships

```php
public function addRelationship(
    string $sourceId,
    string $destinationId,
    string $description,
    string $technology = '',
    array $tags = []
): string {
    // Validate source element exists
    if (!isset($this->elements[$sourceId])) {
        throw new InvalidArgumentException("Source element not found: {$sourceId}");
    }

    // Validate destination element exists
    if (!isset($this->elements[$destinationId])) {
        throw new InvalidArgumentException("Destination element not found: {$destinationId}");
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
```

**Usage:**

```php
$relId = $builder->addRelationship(
    sourceId: 'person_1',
    destinationId: 'system_1',
    description: 'Uses',
    technology: 'HTTPS'
);
// Returns: 'relationship_1'
```

### Relationship Validation

The builder validates relationships:

1. **Source exists** - Source element must be defined
2. **Destination exists** - Destination element must be defined
3. **No self-references** - Element cannot reference itself (optional)

**Advanced validation** (not implemented, but recommended):

```php
// Prevent duplicate relationships
private function isDuplicate(string $sourceId, string $destId): bool
{
    foreach ($this->relationships as $rel) {
        if ($rel['sourceId'] === $sourceId &&
            $rel['destinationId'] === $destId) {
            return true;
        }
    }
    return false;
}

// Validate relationship types
private function isValidRelationship(string $sourceType, string $destType): bool
{
    $validPairs = [
        'person' => ['softwareSystem'],
        'softwareSystem' => ['softwareSystem', 'container'],
        'container' => ['container', 'softwareSystem', 'component'],
        'component' => ['component', 'container', 'softwareSystem'],
    ];

    return in_array($destType, $validPairs[$sourceType] ?? []);
}
```

## Views

### View Types

#### System Context View

```php
public function addSystemContextView(
    string $systemId,
    string $key,
    string $description = ''
): string {
    $this->views[] = [
        'type' => 'systemContext',
        'systemId' => $systemId,
        'key' => $key,
        'description' => $description,
        'autoLayout' => 'lr',
    ];

    return $key;
}
```

#### Container View

```php
public function addContainerView(
    string $systemId,
    string $key,
    string $description = ''
): string {
    $this->views[] = [
        'type' => 'container',
        'systemId' => $systemId,
        'key' => $key,
        'description' => $description,
        'autoLayout' => 'lr',
    ];

    return $key;
}
```

#### Component View

```php
public function addComponentView(
    string $containerId,
    string $key,
    string $description = ''
): string {
    $this->views[] = [
        'type' => 'component',
        'containerId' => $containerId,
        'key' => $key,
        'description' => $description,
        'autoLayout' => 'lr',
    ];

    return $key;
}
```

#### Dynamic View

```php
public function dynamicView(
    string $elementId,
    string $key,
    string $description = ''
): string {
    $this->views[] = [
        'type' => 'dynamic',
        'elementId' => $elementId,
        'key' => $key,
        'description' => $description,
        'autoLayout' => 'lr',
    ];

    return $key;
}
```

### Auto-Layout

Set layout direction for views:

```php
public function setViewAutoLayout(string $viewKey, string $direction): void
{
    foreach ($this->views as &$view) {
        if ($view['key'] === $viewKey) {
            $view['autoLayout'] = $direction;
            return;
        }
    }

    throw new InvalidArgumentException("View not found: {$viewKey}");
}
```

**Supported directions:**
- `lr` - Left to right
- `rl` - Right to left
- `tb` - Top to bottom
- `bt` - Bottom to top

## DSL Generation

### Main Generation Method

```php
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
        $dsl .= $this->generateStyles();
        $dsl .= "        }\n";
        $dsl .= "    }\n";
    }

    $dsl .= "}\n";

    return $dsl;
}
```

### Element Generation

#### Person DSL

```php
private function generatePersonDsl(array $element): string
{
    $tags = $this->formatTags($element['tags']);

    return "        {$element['id']} = person \"{$element['name']}\" \"{$element['description']}\"{$tags}\n";
}
```

**Output:**

```dsl
person_1 = person "Customer" "A customer of the platform" "External"
```

#### Software System DSL

```php
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
```

**Output (with containers):**

```dsl
system_1 = softwareSystem "E-commerce Platform" "Online shopping" {
    container_1 = container "Web App" "Frontend" "React"
    container_2 = container "API" "Backend" "Node.js"
}
```

#### Container DSL

```php
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
```

#### Component DSL

```php
private function generateComponentDsl(array $element): string
{
    $techAndTags = $this->formatTechnologyAndTags(
        $element['technology'] ?? '',
        $element['tags']
    );

    return "                {$element['id']} = component \"{$element['name']}\" \"{$element['description']}\"{$techAndTags}\n";
}
```

### Relationship Generation

```php
private function generateRelationshipDsl(array $rel): string
{
    $techAndTags = $this->formatTechnologyAndTags(
        $rel['technology'] ?? '',
        $rel['tags']
    );

    return "        {$rel['sourceId']} -> {$rel['destinationId']} \"{$rel['description']}\"{$techAndTags}\n";
}
```

**Output:**

```dsl
person_1 -> system_1 "Uses" "HTTPS"
container_1 -> container_2 "Calls" "REST/JSON"
```

### View Generation

```php
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
```

### Formatting Helpers

#### Technology and Tags

```php
private function formatTechnologyAndTags(string $technology, array $tags): string
{
    $result = '';

    if (!empty($tags)) {
        // Tags present: always output technology (even if empty)
        $result .= " \"{$technology}\"";
        $result .= ' "' . implode(',', $tags) . '"';
    } elseif ($technology !== '') {
        // No tags but technology present
        $result .= " \"{$technology}\"";
    }
    // If both empty, return empty string

    return $result;
}
```

**Why this matters:**

DSL is positional - tags come after technology:

```dsl
# Correct: technology first, then tags
container "App" "Description" "React" "Web,Frontend"

# Wrong: tags interpreted as technology
container "App" "Description" "Web,Frontend"

# Correct: empty technology, then tags
container "App" "Description" "" "Web,Frontend"
```

#### Tags Only

```php
private function formatTags(array $tags): string
{
    return !empty($tags) ? ' "' . implode(',', $tags) . '"' : '';
}
```

### Style Generation

```php
private function generateStyles(): string
{
    $styles = '';

    $styles .= "            element \"Software System\" {\n";
    $styles .= "                background #1168bd\n";
    $styles .= "                color #ffffff\n";
    $styles .= "            }\n";

    $styles .= "            element \"Person\" {\n";
    $styles .= "                background #08427b\n";
    $styles .= "                color #ffffff\n";
    $styles .= "                shape person\n";
    $styles .= "            }\n";

    return $styles;
}
```

## DSL Parsing (Reverse Operation)

### fromDsl() Method

The builder can parse existing DSL to rebuild state:

```php
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
```

### Parsing Elements

```php
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
```

### Element Counter Update

After parsing, update counter to continue from max ID:

```php
private function updateElementCounter(string $id): void
{
    // Extract numeric suffix from IDs like "person_1", "system_2"
    if (preg_match('/_(\d+)$/', $id, $matches)) {
        $counter = (int) $matches[1];
        if ($counter > $this->elementCounter) {
            $this->elementCounter = $counter;
        }
    }
}
```

**Why this matters:**

Ensures new elements don't conflict with existing IDs:

```php
// Parse DSL with person_1, system_1
$builder = DslBuilder::fromDsl($existingDsl);
// Counter is now 1

// Add new person -> person_2 (not person_1)
$newPersonId = $builder->addPerson('New Person');
// Returns: 'person_2'
```

## Complete Example

```php
// Create new builder
$builder = new DslBuilder();

// Set workspace metadata
$builder->workspace('E-commerce Platform', 'Online shopping system');

// Add people
$customer = $builder->addPerson('Customer', 'Platform user', ['External']);
$admin = $builder->addPerson('Administrator', 'Platform admin');

// Add software system
$system = $builder->addSoftwareSystem(
    'E-commerce Platform',
    'Handles online orders',
    'Internal'
);

// Add containers
$webapp = $builder->addContainer(
    $system,
    'Web Application',
    'Customer UI',
    'React, TypeScript',
    ['Web Browser']
);

$api = $builder->addContainer(
    $system,
    'API',
    'Backend API',
    'Node.js, Express',
    ['API']
);

$database = $builder->addContainer(
    $system,
    'Database',
    'Product and order data',
    'PostgreSQL',
    ['Database']
);

// Add components
$controller = $builder->addComponent(
    $api,
    'Product Controller',
    'Handles product requests',
    'Express Controller'
);

$service = $builder->addComponent(
    $api,
    'Product Service',
    'Business logic',
    'Service'
);

// Add relationships
$builder->addRelationship($customer, $webapp, 'Uses', 'HTTPS');
$builder->addRelationship($webapp, $api, 'Calls', 'REST/JSON');
$builder->addRelationship($api, $database, 'Reads/writes', 'SQL');
$builder->addRelationship($controller, $service, 'Uses');

// Add views
$builder->addSystemContextView($system, 'SystemContext', 'System context');
$builder->addContainerView($system, 'Containers', 'Container view');
$builder->addComponentView($api, 'APIComponents', 'API components');

// Set layout
$builder->setViewAutoLayout('SystemContext', 'lr');
$builder->setViewAutoLayout('Containers', 'tb');

// Generate DSL
$dsl = $builder->toDsl();

echo $dsl;
```

**Output:**

```dsl
workspace "E-commerce Platform" "Online shopping system" {

    model {
        person_1 = person "Customer" "Platform user" "External"
        person_2 = person "Administrator" "Platform admin"
        system_1 = softwareSystem "E-commerce Platform" "Handles online orders" {
            container_1 = container "Web Application" "Customer UI" "React, TypeScript" "Web Browser"
            container_2 = container "API" "Backend API" "Node.js, Express" "API" {
                component_1 = component "Product Controller" "Handles product requests" "Express Controller"
                component_2 = component "Product Service" "Business logic" "Service"
            }
            container_3 = container "Database" "Product and order data" "PostgreSQL" "Database"
        }

        person_1 -> container_1 "Uses" "HTTPS"
        container_1 -> container_2 "Calls" "REST/JSON"
        container_2 -> container_3 "Reads/writes" "SQL"
        component_1 -> component_2 "Uses"
    }

    views {
        systemContext system_1 "SystemContext" {
            include *
            autoLayout lr
        }

        container system_1 "Containers" {
            include *
            autoLayout tb
        }

        component container_2 "APIComponents" {
            include *
            autoLayout lr
        }

        styles {
            element "Software System" {
                background #1168bd
                color #ffffff
            }
            element "Person" {
                background #08427b
                color #ffffff
                shape person
            }
        }
    }
}
```

## Best Practices

### 1. Validate Parent References

Always check parent exists before adding children:

```php
if (!isset($this->elements[$systemId]) ||
    $this->elements[$systemId]['type'] !== 'softwareSystem') {
    throw new InvalidArgumentException("Invalid parent system");
}
```

### 2. Maintain Counter Integrity

Update counter when parsing DSL:

```php
$builder = DslBuilder::fromDsl($existingDsl);
// Counter is now synchronized with existing IDs
```

### 3. Use Return Values

Capture returned IDs for relationships:

```php
$systemId = $builder->addSoftwareSystem('System', 'Description');
$personId = $builder->addPerson('User', 'Description');
$builder->addRelationship($personId, $systemId, 'Uses');
```

### 4. Consistent Tags

Use consistent tag naming:

```php
// Good: Consistent tags
['External', 'Database', 'Web Browser']

// Bad: Inconsistent tags
['external', 'db', 'web_browser']
```

### 5. Technology Specifications

Be specific with technology:

```php
// Good: Specific tech stack
technology: 'React 18, TypeScript, Vite'

// Bad: Vague
technology: 'JavaScript'
```

## Testing

### Unit Tests

```php
class DslBuilderTest extends TestCase
{
    public function testAddPerson(): void
    {
        $builder = new DslBuilder();
        $id = $builder->addPerson('User', 'A user');

        $this->assertEquals('person_1', $id);
        $element = $builder->getElement($id);
        $this->assertEquals('person', $element['type']);
        $this->assertEquals('User', $element['name']);
    }

    public function testGenerateDsl(): void
    {
        $builder = new DslBuilder();
        $builder->workspace('Test', 'Description');
        $personId = $builder->addPerson('User', 'A user');
        $systemId = $builder->addSoftwareSystem('System', 'The system');
        $builder->addRelationship($personId, $systemId, 'Uses');

        $dsl = $builder->toDsl();

        $this->assertStringContainsString('workspace "Test"', $dsl);
        $this->assertStringContainsString('person_1 = person "User"', $dsl);
        $this->assertStringContainsString('system_1 = softwareSystem "System"', $dsl);
        $this->assertStringContainsString('person_1 -> system_1 "Uses"', $dsl);
    }

    public function testFromDsl(): void
    {
        $originalDsl = <<<DSL
        workspace "Test" "Description" {
            model {
                person_1 = person "User" "A user"
                system_1 = softwareSystem "System" "The system"
                person_1 -> system_1 "Uses"
            }
        }
        DSL;

        $builder = DslBuilder::fromDsl($originalDsl);
        $regeneratedDsl = $builder->toDsl();

        // Should produce equivalent DSL
        $this->assertStringContainsString('person_1 = person "User"', $regeneratedDsl);
        $this->assertStringContainsString('system_1 = softwareSystem "System"', $regeneratedDsl);
    }
}
```

## Resources

### Related Documentation
- [Workspace Management](/docs/architecture/workspace-management.md)
- [C4 Model](/docs/architecture/c4-model.md)
- [CLI Integration](/docs/architecture/cli-integration.md)

### Code Reference
- [`src/Structurizr/DslBuilder.php`](/src/Structurizr/DslBuilder.php)
- [`tests/Unit/Structurizr/DslBuilderTest.php`](/tests/Unit/Structurizr/DslBuilderTest.php)

### Structurizr DSL
- [DSL Language Reference](https://github.com/structurizr/dsl/blob/master/docs/language-reference.md)
- [DSL Cookbook](https://github.com/structurizr/dsl/tree/master/docs/cookbook)
