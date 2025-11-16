# Workspace Management

## Introduction

The Structurizr MCP server uses a **file-based storage system** to manage workspaces locally. This approach provides simplicity, version control integration, and eliminates external dependencies while maintaining full workspace functionality.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                   WorkspaceManager                       │
│  ┌──────────────────────────────────────────────────┐   │
│  │  create() │ load() │ save() │ delete() │ list() │   │
│  └──────────────────────────────────────────────────┘   │
└─────────────────────┬───────────────────────────────────┘
                      │
                      │ File Operations
                      │
┌─────────────────────▼───────────────────────────────────┐
│              Filesystem Storage Layer                    │
│  ┌──────────────────────────────────────────────────┐   │
│  │     workspaces/                                  │   │
│  │     ├── ws_abc123.json                           │   │
│  │     ├── ws_def456.json                           │   │
│  │     └── ws_ghi789.json                           │   │
│  └──────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────┘
                      │
                      │ DSL Generation
                      │
┌─────────────────────▼───────────────────────────────────┐
│                   DslBuilder                             │
│  - Converts workspace data to DSL format                 │
│  - Generates element identifiers                         │
│  - Builds hierarchical structure                         │
└──────────────────────────────────────────────────────────┘
```

## Workspace Data Model

### Workspace Class

The `Workspace` class is an immutable value object representing a complete workspace:

```php
readonly class Workspace
{
    public function __construct(
        public string $id,              // Unique identifier
        public string $name,            // Workspace name
        public string $description,     // Description
        public array $model,            // Architecture elements
        public array $views,            // View definitions
        public string $dsl,             // Generated DSL
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {}
}
```

**Key Characteristics:**

1. **Immutable** - Once created, cannot be modified
2. **Value Object** - Represents a complete state
3. **Self-Contained** - All data needed for serialization
4. **Type-Safe** - Strong typing with PHP 8.1+ features

### Workspace Model Structure

The `model` array contains all architecture elements:

```php
[
    'elements' => [
        'person_1' => [
            'type' => 'person',
            'id' => 'person_1',
            'name' => 'User',
            'description' => 'A user of the system',
            'tags' => ['External']
        ],
        'system_1' => [
            'type' => 'softwareSystem',
            'id' => 'system_1',
            'name' => 'E-commerce System',
            'description' => 'Handles online orders',
            'location' => 'Internal',
            'tags' => [],
            'containers' => ['container_1', 'container_2']
        ],
        'container_1' => [
            'type' => 'container',
            'id' => 'container_1',
            'name' => 'Web Application',
            'description' => 'Frontend application',
            'technology' => 'React',
            'systemId' => 'system_1',
            'tags' => [],
            'components' => []
        ]
    ],
    'relationships' => [
        'relationship_1' => [
            'id' => 'relationship_1',
            'sourceId' => 'person_1',
            'destinationId' => 'system_1',
            'description' => 'Uses',
            'technology' => 'HTTPS',
            'tags' => []
        ]
    ]
]
```

### Workspace Views Structure

The `views` array contains view definitions:

```php
[
    [
        'type' => 'systemContext',
        'systemId' => 'system_1',
        'key' => 'SystemContext',
        'description' => 'System context diagram',
        'autoLayout' => 'lr'
    ],
    [
        'type' => 'container',
        'systemId' => 'system_1',
        'key' => 'Containers',
        'description' => 'Container diagram',
        'autoLayout' => 'tb'
    ]
]
```

## File-Based Storage

### Storage Structure

```
workspaces/
├── ws_a1b2c3d4e5f6g7h8.json      # Workspace file
├── ws_1a2b3c4d5e6f7g8h.json      # Another workspace
└── ws_9z8y7x6w5v4u3t2s.json      # Yet another workspace
```

**Characteristics:**

1. **Flat Directory** - All workspaces in one directory
2. **JSON Format** - Human-readable and parseable
3. **Unique IDs** - Generated with `ws_` prefix + random hex
4. **Git-Friendly** - Can be version controlled

### Workspace File Format

**Complete JSON structure:**

```json
{
  "id": "ws_a1b2c3d4e5f6g7h8",
  "name": "E-commerce Platform",
  "description": "Architecture for our e-commerce system",
  "model": {
    "elements": {
      "person_1": {
        "type": "person",
        "id": "person_1",
        "name": "Customer",
        "description": "A customer of the platform",
        "tags": ["External User"]
      },
      "system_1": {
        "type": "softwareSystem",
        "id": "system_1",
        "name": "E-commerce System",
        "description": "Online shopping platform",
        "location": "Internal",
        "tags": [],
        "containers": ["container_1", "container_2"]
      },
      "container_1": {
        "type": "container",
        "id": "container_1",
        "name": "Web Application",
        "description": "Customer-facing web application",
        "technology": "React, TypeScript",
        "systemId": "system_1",
        "tags": [],
        "components": []
      },
      "container_2": {
        "type": "container",
        "id": "container_2",
        "name": "Database",
        "description": "Stores product and order data",
        "technology": "PostgreSQL",
        "systemId": "system_1",
        "tags": ["Database"],
        "components": []
      }
    },
    "relationships": {
      "relationship_1": {
        "id": "relationship_1",
        "sourceId": "person_1",
        "destinationId": "system_1",
        "description": "Browse products, place orders",
        "technology": "HTTPS",
        "tags": []
      },
      "relationship_2": {
        "id": "relationship_2",
        "sourceId": "container_1",
        "destinationId": "container_2",
        "description": "Reads from and writes to",
        "technology": "SQL/TCP",
        "tags": []
      }
    }
  },
  "views": [
    {
      "type": "systemContext",
      "systemId": "system_1",
      "key": "SystemContext",
      "description": "System context for E-commerce System",
      "autoLayout": "lr"
    },
    {
      "type": "container",
      "systemId": "system_1",
      "key": "Containers",
      "description": "Container view for E-commerce System",
      "autoLayout": "tb"
    }
  ],
  "dsl": "workspace \"E-commerce Platform\" \"Architecture for our e-commerce system\" {\n\n    model {\n        person_1 = person \"Customer\" \"A customer of the platform\"\n        ...\n    }\n\n    views {\n        systemContext system_1 \"SystemContext\" {\n            include *\n            autoLayout lr\n        }\n        ...\n    }\n}",
  "createdAt": "2024-01-15T10:30:00+00:00",
  "updatedAt": "2024-01-15T14:20:00+00:00"
}
```

## WorkspaceManager Implementation

### Initialization

```php
class WorkspaceManager
{
    private Filesystem $filesystem;

    public function __construct(
        private readonly string $storagePath,
        private readonly LoggerInterface $logger,
    ) {
        $this->filesystem = new Filesystem();

        // Ensure storage directory exists
        if (!is_dir($this->storagePath)) {
            $this->filesystem->mkdir($this->storagePath, 0755);
            $this->logger->info("Created workspace storage directory");
        }
    }
}
```

**Storage Path Configuration:**

Set via environment variable:
```bash
WORKSPACE_STORAGE_PATH=/path/to/workspaces
```

Or in Claude Desktop config:
```json
{
  "env": {
    "WORKSPACE_STORAGE_PATH": "./workspaces"
  }
}
```

### Creating Workspaces

```php
public function create(string $name, string $description = ''): Workspace
{
    // Generate unique ID
    $id = $this->generateWorkspaceId();
    $now = new DateTimeImmutable();

    // Create empty workspace
    $workspace = new Workspace(
        id: $id,
        name: $name,
        description: $description,
        model: [],
        views: [],
        dsl: '',
        createdAt: $now,
        updatedAt: $now,
    );

    // Save to disk
    $this->save($workspace);
    $this->logger->info("Created workspace: {$id} - {$name}");

    return $workspace;
}

private function generateWorkspaceId(): string
{
    do {
        // Generate: ws_<16 hex characters>
        $id = 'ws_' . bin2hex(random_bytes(8));
    } while ($this->exists($id));

    return $id;
}
```

**ID Generation:**
- Prefix: `ws_` (identifies as workspace)
- Random: 16 hex characters (8 bytes)
- Uniqueness: Checked against existing files
- Collision-resistant: ~18 quintillion possibilities

### Loading Workspaces

```php
public function load(string $id): Workspace
{
    $filepath = $this->getWorkspacePath($id);

    // Check existence
    if (!file_exists($filepath)) {
        throw new WorkspaceNotFoundException($id);
    }

    // Read file
    $content = file_get_contents($filepath);
    if ($content === false) {
        throw new RuntimeException("Failed to read workspace file: {$id}");
    }

    // Parse JSON
    $data = json_decode($content, true);
    if ($data === null) {
        throw new RuntimeException("Failed to parse workspace JSON: {$id}");
    }

    $this->logger->debug("Loaded workspace: {$id}");

    // Reconstruct object
    return Workspace::fromArray($data);
}
```

**Error Handling:**

1. **File Not Found** - `WorkspaceNotFoundException`
2. **Read Error** - `RuntimeException`
3. **Parse Error** - `RuntimeException`
4. **Validation Error** - Exception from `fromArray()`

### Saving Workspaces

```php
public function save(Workspace $workspace): void
{
    $filepath = $this->getWorkspacePath($workspace->id);

    // Convert to array
    $data = $workspace->toArray();

    // Encode as JSON with pretty printing
    $json = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        throw new RuntimeException(
            "Failed to encode workspace: {$workspace->id}"
        );
    }

    // Write atomically
    file_put_contents($filepath, $json);
    $this->logger->debug("Saved workspace: {$workspace->id}");
}
```

**JSON Encoding Options:**

- `JSON_PRETTY_PRINT` - Human-readable formatting
- `JSON_UNESCAPED_SLASHES` - Don't escape forward slashes
- `JSON_UNESCAPED_UNICODE` - Preserve Unicode characters (optional)

### Deleting Workspaces

```php
public function delete(string $id): void
{
    $filepath = $this->getWorkspacePath($id);

    if (!file_exists($filepath)) {
        throw new WorkspaceNotFoundException($id);
    }

    unlink($filepath);
    $this->logger->info("Deleted workspace: {$id}");
}
```

### Listing Workspaces

```php
public function list(): array
{
    $workspaces = [];
    $files = glob($this->storagePath . '/*.json');

    if ($files === false) {
        return [];
    }

    foreach ($files as $file) {
        try {
            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $data = json_decode($content, true);
            if ($data !== null && isset($data['id'])) {
                $workspaces[] = [
                    'id' => $data['id'],
                    'name' => $data['name'] ?? '',
                    'description' => $data['description'] ?? '',
                    'createdAt' => $data['createdAt'] ?? null,
                    'updatedAt' => $data['updatedAt'] ?? null,
                ];
            }
        } catch (Exception $e) {
            $this->logger->warning(
                "Failed to read workspace file: {$file}",
                ['error' => $e->getMessage()]
            );
        }
    }

    return $workspaces;
}
```

**List Operation:**

- Returns **summary** data only (not full workspace)
- Skips corrupted files (with warning log)
- No guaranteed order (filesystem dependent)
- Efficient for large workspace collections

## Workspace Modifications

### Immutability Pattern

Workspaces are **immutable** - modifications create new instances:

```php
// Original workspace
$workspace = $manager->load($id);

// Create modified copy
$updated = $workspace->withName('New Name');

// Save modified workspace
$manager->save($updated);
```

**Benefits:**

1. **Thread Safety** - No concurrent modification issues
2. **Predictability** - No hidden state changes
3. **History Tracking** - Easy to implement undo/redo
4. **Debugging** - Clear data flow

### Common Modification Methods

```php
class Workspace
{
    // Update metadata
    public function withName(string $name): self
    {
        return new self(
            id: $this->id,
            name: $name,
            description: $this->description,
            model: $this->model,
            views: $this->views,
            dsl: $this->dsl,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function withDescription(string $description): self
    {
        return new self(/* ... */);
    }

    // Update model
    public function withModel(array $model): self
    {
        return new self(
            /* ... */
            model: $model,
            updatedAt: new DateTimeImmutable(),
        );
    }

    // Update DSL
    public function withDsl(string $dsl): self
    {
        return new self(
            /* ... */
            dsl: $dsl,
            updatedAt: new DateTimeImmutable(),
        );
    }
}
```

### Model Updates via Tools

Tools modify the workspace by:

1. Loading workspace
2. Modifying model array
3. Regenerating DSL
4. Creating new workspace instance
5. Saving

**Example:**

```php
public function addSoftwareSystem(
    string $workspaceId,
    string $name,
    string $description,
    string $tags
): array {
    // Load workspace
    $workspace = $this->manager->load($workspaceId);

    // Parse existing DSL to DslBuilder
    $builder = DslBuilder::fromDsl($workspace->dsl);

    // Add new system
    $systemId = $builder->addSoftwareSystem(
        name: $name,
        description: $description,
        tags: explode(',', $tags)
    );

    // Generate new DSL
    $newDsl = $builder->toDsl();

    // Update workspace
    $updated = $workspace
        ->withModel($builder->toArray())
        ->withDsl($newDsl);

    // Save
    $this->manager->save($updated);

    return ['success' => true, 'systemId' => $systemId];
}
```

## DSL Generation

### Why DSL?

The **Structurizr DSL** is the canonical format for:

1. **CLI Validation** - Structurizr CLI validates DSL
2. **Export** - CLI exports DSL to PlantUML, Mermaid, etc.
3. **Visualization** - Structurizr renders DSL
4. **Human Readability** - Text format for review
5. **Version Control** - Diff-friendly

### Generation Process

```
Workspace Model (JSON)
        │
        │ DslBuilder::fromDsl()
        ▼
   DslBuilder State
        │
        │ Modifications
        ▼
   Updated Builder
        │
        │ toDsl()
        ▼
  Structurizr DSL (String)
        │
        │ Save to workspace
        ▼
   Updated Workspace
```

### Example DSL Output

From the workspace model above:

```dsl
workspace "E-commerce Platform" "Architecture for our e-commerce system" {

    model {
        person_1 = person "Customer" "A customer of the platform" "External User"
        system_1 = softwareSystem "E-commerce System" "Online shopping platform" {
            container_1 = container "Web Application" "Customer-facing web application" "React, TypeScript"
            container_2 = container "Database" "Stores product and order data" "PostgreSQL" "Database"
        }

        person_1 -> system_1 "Browse products, place orders" "HTTPS"
        container_1 -> container_2 "Reads from and writes to" "SQL/TCP"
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

See [DSL Builder](/docs/architecture/dsl-builder.md) for detailed DSL generation documentation.

## Security

### Path Traversal Prevention

```php
private function getWorkspacePath(string $id): string
{
    // Sanitize ID to prevent directory traversal
    $sanitizedId = preg_replace('/[^a-zA-Z0-9_-]/', '', $id);

    return $this->storagePath . '/' . $sanitizedId . '.json';
}
```

**Protection:**

- Removes all special characters except `_` and `-`
- Prevents `../` attacks
- Ensures ID stays within workspace directory

**Additional Validation:**

```php
// Validate resolved path is within storage directory
$resolvedPath = realpath($path);
if (strpos($resolvedPath, $this->storagePath) !== 0) {
    throw new SecurityException('Invalid workspace path');
}
```

### File Permissions

**Directory:** `0755` (rwxr-xr-x)
- Owner: read, write, execute
- Group: read, execute
- Other: read, execute

**Files:** Default umask (typically `0644`)
- Owner: read, write
- Group: read
- Other: read

**Recommendation:**

For sensitive environments, restrict permissions:

```php
// Private workspace directory
$this->filesystem->mkdir($this->storagePath, 0700);

// Private workspace files
$this->filesystem->chmod($filepath, 0600);
```

## Backup and Version Control

### Git Integration

Workspaces can be version controlled with Git:

```bash
# Initialize repository
cd workspaces
git init

# Add .gitignore
echo "*.tmp" >> .gitignore
echo ".DS_Store" >> .gitignore

# Commit workspaces
git add *.json
git commit -m "Add architecture workspaces"

# Track changes
git log --oneline -- ws_abc123.json
git diff ws_abc123.json
```

**Benefits:**

- **History** - Track all changes
- **Collaboration** - Team can review/merge
- **Backup** - Remote repository backup
- **Branching** - Experiment with alternatives

### Backup Strategy

**Simple File Backup:**

```bash
# Daily backup
tar czf workspaces-$(date +%Y%m%d).tar.gz workspaces/

# Restore
tar xzf workspaces-20240115.tar.gz
```

**Automated Backup:**

```bash
#!/bin/bash
# backup-workspaces.sh

BACKUP_DIR="/backups/structurizr"
WORKSPACE_DIR="/path/to/workspaces"
DATE=$(date +%Y%m%d-%H%M%S)

mkdir -p "$BACKUP_DIR"
tar czf "$BACKUP_DIR/workspaces-$DATE.tar.gz" "$WORKSPACE_DIR"

# Keep only last 30 days
find "$BACKUP_DIR" -name "workspaces-*.tar.gz" -mtime +30 -delete
```

## Performance Considerations

### File I/O Optimization

**1. Lazy Loading**

Load workspaces only when needed:

```php
// Good: Load when accessed
$workspace = $manager->load($id);

// Bad: Load all workspaces upfront
foreach ($allIds as $id) {
    $workspaces[] = $manager->load($id);
}
```

**2. List vs. Load**

Use `list()` for summaries, `load()` for full data:

```php
// List all workspaces (lightweight)
$summaries = $manager->list();

// Load specific workspace (full data)
$workspace = $manager->load($summaries[0]['id']);
```

**3. Caching**

For read-heavy workloads, cache in memory:

```php
class CachingWorkspaceManager
{
    private array $cache = [];

    public function load(string $id): Workspace
    {
        if (!isset($this->cache[$id])) {
            $this->cache[$id] = parent::load($id);
        }
        return $this->cache[$id];
    }
}
```

### Scaling Considerations

**Current Limits:**

- **File System** - Thousands of workspaces
- **Directory Listing** - `glob()` slows with many files
- **Concurrent Access** - No locking mechanism

**Future Enhancements:**

1. **Database Backend** - For large-scale deployments
2. **File Locking** - For concurrent access
3. **Indexing** - For fast searches
4. **Sharding** - For massive workspace counts

## Best Practices

### Workspace Naming

**Clear, Descriptive Names:**

```php
// Good
"E-commerce Platform Architecture"
"User Authentication Service"
"Data Pipeline - ETL Process"

// Bad
"System1"
"Test"
"New Workspace"
```

### Descriptions

**Provide Context:**

```php
// Good
"Architecture for the customer-facing e-commerce platform, including web app, mobile API, and backend services"

// Bad
"E-commerce stuff"
""
```

### Model Organization

**Consistent Naming:**

- Use `person_N` for people
- Use `system_N` for systems
- Use `container_N` for containers
- Use `component_N` for components
- Use `relationship_N` for relationships

**Hierarchical Structure:**

Keep containers within systems, components within containers:

```php
$model = [
    'elements' => [
        'system_1' => [
            'containers' => ['container_1', 'container_2']
        ],
        'container_1' => [
            'systemId' => 'system_1',
            'components' => ['component_1', 'component_2']
        ]
    ]
];
```

### Error Handling

**Specific Exceptions:**

```php
try {
    $workspace = $manager->load($id);
} catch (WorkspaceNotFoundException $e) {
    // Handle missing workspace
} catch (RuntimeException $e) {
    // Handle file/parsing errors
}
```

**Logging:**

Log operations for debugging:

```php
$this->logger->info("Creating workspace: {$name}");
$this->logger->debug("Loading workspace: {$id}");
$this->logger->warning("Corrupted workspace file: {$file}");
$this->logger->error("Failed to save workspace: {$id}");
```

## Testing

### Unit Tests

Test WorkspaceManager operations:

```php
class WorkspaceManagerTest extends TestCase
{
    private string $tempDir;
    private WorkspaceManager $manager;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/test_' . uniqid();
        mkdir($this->tempDir);

        $logger = new NullLogger();
        $this->manager = new WorkspaceManager($this->tempDir, $logger);
    }

    public function testCreateWorkspace(): void
    {
        $workspace = $this->manager->create('Test', 'Description');

        $this->assertNotEmpty($workspace->id);
        $this->assertEquals('Test', $workspace->name);
        $this->assertEquals('Description', $workspace->description);
        $this->assertTrue($this->manager->exists($workspace->id));
    }

    protected function tearDown(): void
    {
        // Clean up
        $files = glob($this->tempDir . '/*');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($this->tempDir);
    }
}
```

## Resources

### Related Documentation
- [MCP Overview](/docs/architecture/mcp-overview.md)
- [DSL Builder](/docs/architecture/dsl-builder.md)
- [CLI Integration](/docs/architecture/cli-integration.md)

### Code Reference
- [`src/Structurizr/WorkspaceManager.php`](/src/Structurizr/WorkspaceManager.php)
- [`src/Structurizr/Workspace.php`](/src/Structurizr/Workspace.php)
- [`tests/Unit/Structurizr/WorkspaceManagerTest.php`](/tests/Unit/Structurizr/WorkspaceManagerTest.php)
