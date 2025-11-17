# Batch Operations

- [Introduction](#introduction)
- [Bulk Element Creation](#bulk-element-creation)
- [Template-Based Generation](#template-based-generation)
- [Import from Other Formats](#import-from-other-formats)
- [Batch Validation](#batch-validation)
- [Performance Considerations](#performance-considerations)
- [Error Handling](#error-handling)

---

## Introduction

> **Status:** This feature is planned for a future release. This documentation describes the intended design and architecture for batch operations.

Batch operations enable efficient creation and manipulation of multiple workspace elements in a single operation, significantly improving performance for large-scale architecture modeling.

> **Benefits:**
> - **Performance** - Single operation instead of many individual calls
> - **Atomicity** - All changes succeed or fail together
> - **Consistency** - Automatic relationship validation across elements
> - **Convenience** - Less code for complex operations

---

## Bulk Element Creation

Create multiple elements in a single batch operation.

### Batch Add Elements

Add multiple elements of different types efficiently:

```php
#[McpTool(
    name: 'batch_add_elements',
    description: 'Adds multiple elements to a workspace in a single operation'
)]
public function batchAddElements(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId,

    #[Schema(description: 'Array of element definitions')]
    array $elements,

    #[Schema(description: 'Validate relationships after creation')]
    bool $validateRelationships = true
): array {
    $workspace = $this->getWorkspace($workspaceId);

    $created = [];
    $errors = [];

    // Start transaction (for atomic operations)
    $this->workspaceManager->beginTransaction($workspace);

    try {
        foreach ($elements as $index => $elementDef) {
            try {
                $element = $this->createElement($workspace, $elementDef);
                $created[] = [
                    'index' => $index,
                    'id' => $element->getId(),
                    'type' => $element->getType(),
                    'name' => $element->getName()
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'element' => $elementDef,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Validate if requested
        if ($validateRelationships && !empty($created)) {
            $validation = $this->validateWorkspace($workspaceId);

            if (!$validation['valid']) {
                throw new ValidationException(
                    'Batch validation failed: ' . json_encode($validation['errors'])
                );
            }
        }

        // Commit transaction
        $this->workspaceManager->commit($workspace);

        return [
            'success' => empty($errors),
            'created' => count($created),
            'failed' => count($errors),
            'elements' => $created,
            'errors' => $errors,
            'message' => sprintf(
                'Created %d elements, %d errors',
                count($created),
                count($errors)
            )
        ];
    } catch (\Exception $e) {
        // Rollback on error
        $this->workspaceManager->rollback($workspace);

        throw new ToolCallException(
            "Batch operation failed: " . $e->getMessage(),
            previous: $e
        );
    }
}
```

### Element Definition Format

Standard format for batch element definitions:

```json
{
  "elements": [
    {
      "type": "person",
      "id": "user",
      "name": "User",
      "description": "A user of the system",
      "tags": ["External"]
    },
    {
      "type": "softwareSystem",
      "id": "webapp",
      "name": "Web Application",
      "description": "Main application",
      "tags": ["Internal"],
      "containers": [
        {
          "id": "api",
          "name": "API",
          "technology": "Node.js",
          "description": "REST API"
        },
        {
          "id": "database",
          "name": "Database",
          "technology": "PostgreSQL",
          "description": "Primary data store"
        }
      ]
    }
  ],
  "relationships": [
    {
      "source": "user",
      "destination": "webapp",
      "description": "Uses",
      "technology": "HTTPS"
    },
    {
      "source": "api",
      "destination": "database",
      "description": "Reads from and writes to",
      "technology": "JDBC"
    }
  ]
}
```

### Batch with Relationships

Create elements and relationships together:

```php
#[McpTool(
    name: 'batch_create_model',
    description: 'Creates a complete model with elements and relationships'
)]
public function batchCreateModel(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId,

    #[Schema(description: 'Model definition with elements and relationships')]
    array $modelDefinition
): array {
    $workspace = $this->getWorkspace($workspaceId);

    $this->workspaceManager->beginTransaction($workspace);

    try {
        // Phase 1: Create all elements
        $elementMap = [];

        foreach ($modelDefinition['elements'] ?? [] as $elementDef) {
            $element = $this->createElementRecursive($workspace, $elementDef);
            $elementMap[$elementDef['id']] = $element->getId();
        }

        // Phase 2: Create all relationships
        $relationships = [];

        foreach ($modelDefinition['relationships'] ?? [] as $relDef) {
            $sourceId = $elementMap[$relDef['source']] ?? $relDef['source'];
            $destId = $elementMap[$relDef['destination']] ?? $relDef['destination'];

            $relationship = $this->addRelationship(
                $workspace,
                $sourceId,
                $destId,
                $relDef['description'] ?? '',
                $relDef['technology'] ?? ''
            );

            $relationships[] = $relationship->getId();
        }

        $this->workspaceManager->commit($workspace);

        return [
            'success' => true,
            'elementsCreated' => count($elementMap),
            'relationshipsCreated' => count($relationships),
            'elementMap' => $elementMap
        ];
    } catch (\Exception $e) {
        $this->workspaceManager->rollback($workspace);

        throw new ToolCallException(
            "Batch model creation failed: " . $e->getMessage(),
            previous: $e
        );
    }
}

private function createElementRecursive(
    Workspace $workspace,
    array $elementDef
): Element {
    // Create main element
    $element = $this->createElement($workspace, $elementDef);

    // Create child elements (containers, components)
    if (isset($elementDef['containers'])) {
        foreach ($elementDef['containers'] as $containerDef) {
            $containerDef['parent'] = $element->getId();
            $this->createElementRecursive($workspace, $containerDef);
        }
    }

    if (isset($elementDef['components'])) {
        foreach ($elementDef['components'] as $componentDef) {
            $componentDef['parent'] = $element->getId();
            $this->createElementRecursive($workspace, $componentDef);
        }
    }

    return $element;
}
```

---

## Template-Based Generation

Generate architecture models from predefined templates.

### Template Structure

Define reusable architecture templates:

```php
class ArchitectureTemplate
{
    public function __construct(
        private readonly string $name,
        private readonly string $description,
        private readonly array $parameters,
        private readonly callable $generator
    ) {}

    public function generate(array $params): array
    {
        // Validate parameters
        $this->validateParameters($params);

        // Generate model
        return ($this->generator)($params);
    }

    private function validateParameters(array $params): void
    {
        foreach ($this->parameters as $param => $config) {
            if ($config['required'] && !isset($params[$param])) {
                throw new \InvalidArgumentException(
                    "Required parameter '{$param}' is missing"
                );
            }
        }
    }
}
```

### Microservices Template

Template for microservices architecture:

```php
class MicroservicesTemplate extends ArchitectureTemplate
{
    public function __construct()
    {
        parent::__construct(
            name: 'microservices',
            description: 'Generates a microservices architecture',
            parameters: [
                'serviceName' => ['type' => 'string', 'required' => true],
                'services' => ['type' => 'array', 'required' => true],
                'includeGateway' => ['type' => 'boolean', 'required' => false],
                'includeServiceDiscovery' => ['type' => 'boolean', 'required' => false],
            ],
            generator: $this->generateMicroservices(...)
        );
    }

    private function generateMicroservices(array $params): array
    {
        $elements = [];
        $relationships = [];

        // API Gateway
        if ($params['includeGateway'] ?? true) {
            $elements[] = [
                'type' => 'container',
                'id' => 'api-gateway',
                'name' => 'API Gateway',
                'technology' => 'Kong / AWS API Gateway',
                'description' => 'Routes requests to microservices'
            ];
        }

        // Service Discovery
        if ($params['includeServiceDiscovery'] ?? true) {
            $elements[] = [
                'type' => 'container',
                'id' => 'service-discovery',
                'name' => 'Service Discovery',
                'technology' => 'Consul / Eureka',
                'description' => 'Service registry and discovery'
            ];
        }

        // Generate each microservice
        foreach ($params['services'] as $service) {
            $serviceId = $this->slugify($service['name']);

            // Service container
            $elements[] = [
                'type' => 'container',
                'id' => $serviceId,
                'name' => $service['name'],
                'technology' => $service['technology'] ?? 'Node.js',
                'description' => $service['description'] ?? ''
            ];

            // Service database
            if ($service['hasDatabase'] ?? true) {
                $dbId = "{$serviceId}-db";

                $elements[] = [
                    'type' => 'container',
                    'id' => $dbId,
                    'name' => "{$service['name']} Database",
                    'technology' => $service['databaseType'] ?? 'PostgreSQL',
                    'description' => "Data store for {$service['name']}"
                ];

                $relationships[] = [
                    'source' => $serviceId,
                    'destination' => $dbId,
                    'description' => 'Reads from and writes to',
                    'technology' => 'JDBC / ORM'
                ];
            }

            // Gateway relationships
            if ($params['includeGateway'] ?? true) {
                $relationships[] = [
                    'source' => 'api-gateway',
                    'destination' => $serviceId,
                    'description' => 'Routes requests to',
                    'technology' => 'HTTP/REST'
                ];
            }

            // Service discovery relationships
            if ($params['includeServiceDiscovery'] ?? true) {
                $relationships[] = [
                    'source' => $serviceId,
                    'destination' => 'service-discovery',
                    'description' => 'Registers with',
                    'technology' => 'HTTP'
                ];
            }
        }

        // Inter-service relationships
        foreach ($params['services'] as $service) {
            if (empty($service['dependencies'])) {
                continue;
            }

            $serviceId = $this->slugify($service['name']);

            foreach ($service['dependencies'] as $dependency) {
                $dependencyId = $this->slugify($dependency);

                $relationships[] = [
                    'source' => $serviceId,
                    'destination' => $dependencyId,
                    'description' => 'Calls',
                    'technology' => 'HTTP/REST'
                ];
            }
        }

        return [
            'elements' => $elements,
            'relationships' => $relationships
        ];
    }

    private function slugify(string $text): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $text));
    }
}
```

### Template Application

Apply templates to generate workspaces:

```php
#[McpTool(
    name: 'generate_from_template',
    description: 'Generates workspace from a predefined template'
)]
public function generateFromTemplate(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId,

    #[Schema(description: 'Template name')]
    string $templateName,

    #[Schema(description: 'Template parameters')]
    array $parameters
): array {
    $workspace = $this->getWorkspace($workspaceId);

    // Load template
    $template = $this->templateRegistry->get($templateName);

    if (!$template) {
        throw new ToolCallException("Template '{$templateName}' not found");
    }

    // Generate model from template
    $model = $template->generate($parameters);

    // Apply to workspace
    $result = $this->batchCreateModel($workspaceId, $model);

    return [
        'success' => true,
        'template' => $templateName,
        'elementsCreated' => $result['elementsCreated'],
        'relationshipsCreated' => $result['relationshipsCreated']
    ];
}
```

### Built-in Templates

Provide common architecture templates:

- **Microservices** - Service-oriented architecture
- **Three-tier** - Traditional web application
- **Event-driven** - Event sourcing and CQRS
- **Serverless** - Function-as-a-service architecture
- **Monolith** - Layered monolithic application
- **Clean Architecture** - Ports and adapters pattern

---

## Import from Other Formats

Import architecture definitions from various sources.

### JSON Import

Import from generic JSON format:

```php
#[McpTool(
    name: 'import_from_json',
    description: 'Imports workspace from JSON architecture definition'
)]
public function importFromJson(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId,

    #[Schema(description: 'JSON content')]
    string $jsonContent
): array {
    $data = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new ToolCallException(
            'Invalid JSON: ' . json_last_error_msg()
        );
    }

    // Transform to internal format
    $model = $this->transformJsonToModel($data);

    // Apply to workspace
    return $this->batchCreateModel($workspaceId, $model);
}
```

### PlantUML Import

Import from PlantUML C4 diagrams:

```php
#[McpTool(
    name: 'import_from_plantuml',
    description: 'Imports workspace from PlantUML C4 diagram'
)]
public function importFromPlantUml(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId,

    #[Schema(description: 'PlantUML content')]
    string $plantUmlContent
): array {
    $parser = new PlantUmlParser();
    $model = $parser->parse($plantUmlContent);

    return $this->batchCreateModel($workspaceId, $model);
}

class PlantUmlParser
{
    public function parse(string $content): array
    {
        $elements = [];
        $relationships = [];

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);

            // Parse Person
            if (preg_match('/Person\((\w+),\s*"([^"]+)"\)/', $line, $matches)) {
                $elements[] = [
                    'type' => 'person',
                    'id' => $matches[1],
                    'name' => $matches[2]
                ];
            }

            // Parse System
            if (preg_match('/System\((\w+),\s*"([^"]+)"(?:,\s*"([^"]+)")?\)/', $line, $matches)) {
                $elements[] = [
                    'type' => 'softwareSystem',
                    'id' => $matches[1],
                    'name' => $matches[2],
                    'description' => $matches[3] ?? ''
                ];
            }

            // Parse Container
            if (preg_match('/Container\((\w+),\s*"([^"]+)",\s*"([^"]+)"(?:,\s*"([^"]+)")?\)/', $line, $matches)) {
                $elements[] = [
                    'type' => 'container',
                    'id' => $matches[1],
                    'name' => $matches[2],
                    'technology' => $matches[3],
                    'description' => $matches[4] ?? ''
                ];
            }

            // Parse Relationship
            if (preg_match('/Rel\((\w+),\s*(\w+),\s*"([^"]+)"(?:,\s*"([^"]+)")?\)/', $line, $matches)) {
                $relationships[] = [
                    'source' => $matches[1],
                    'destination' => $matches[2],
                    'description' => $matches[3],
                    'technology' => $matches[4] ?? ''
                ];
            }
        }

        return [
            'elements' => $elements,
            'relationships' => $relationships
        ];
    }
}
```

### ArchiMate Import

Import from ArchiMate models:

```php
#[McpTool(
    name: 'import_from_archimate',
    description: 'Imports workspace from ArchiMate XML'
)]
public function importFromArchiMate(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId,

    #[Schema(description: 'ArchiMate XML content')]
    string $xmlContent
): array {
    $parser = new ArchiMateParser();
    $model = $parser->parse($xmlContent);

    return $this->batchCreateModel($workspaceId, $model);
}
```

### AWS Architecture Import

Import from AWS CloudFormation or CDK:

```php
#[McpTool(
    name: 'import_from_aws',
    description: 'Imports workspace from AWS CloudFormation template'
)]
public function importFromAws(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId,

    #[Schema(description: 'CloudFormation template')]
    string $templateContent
): array {
    $template = json_decode($templateContent, true);

    $elements = [];
    $relationships = [];

    // Parse AWS resources
    foreach ($template['Resources'] ?? [] as $logicalId => $resource) {
        $elements[] = $this->mapAwsResource($logicalId, $resource);
    }

    // Parse dependencies
    foreach ($template['Resources'] ?? [] as $logicalId => $resource) {
        if (isset($resource['DependsOn'])) {
            $dependencies = is_array($resource['DependsOn'])
                ? $resource['DependsOn']
                : [$resource['DependsOn']];

            foreach ($dependencies as $dependency) {
                $relationships[] = [
                    'source' => $logicalId,
                    'destination' => $dependency,
                    'description' => 'Depends on'
                ];
            }
        }
    }

    return $this->batchCreateModel($workspaceId, [
        'elements' => $elements,
        'relationships' => $relationships
    ]);
}
```

---

## Batch Validation

Validate multiple workspaces or elements efficiently.

### Batch Workspace Validation

```php
#[McpTool(
    name: 'batch_validate_workspaces',
    description: 'Validates multiple workspaces in a single operation'
)]
public function batchValidateWorkspaces(
    #[Schema(description: 'Array of workspace IDs')]
    array $workspaceIds
): array {
    $results = [];

    foreach ($workspaceIds as $workspaceId) {
        try {
            $validation = $this->validateWorkspace($workspaceId);

            $results[] = [
                'workspaceId' => $workspaceId,
                'valid' => $validation['valid'],
                'errors' => $validation['errors'] ?? [],
                'warnings' => $validation['warnings'] ?? []
            ];
        } catch (\Exception $e) {
            $results[] = [
                'workspaceId' => $workspaceId,
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    $totalValid = count(array_filter($results, fn($r) => $r['valid']));

    return [
        'total' => count($results),
        'valid' => $totalValid,
        'invalid' => count($results) - $totalValid,
        'results' => $results
    ];
}
```

### Parallel Validation

Execute validations in parallel for better performance:

```php
class ParallelValidator
{
    public function validateWorkspaces(array $workspaceIds): array
    {
        $processes = [];

        // Start validation processes
        foreach ($workspaceIds as $workspaceId) {
            $process = new Process([
                'php',
                'bin/console',
                'validate',
                $workspaceId
            ]);

            $process->start();
            $processes[$workspaceId] = $process;
        }

        // Collect results
        $results = [];

        foreach ($processes as $workspaceId => $process) {
            $process->wait();

            $results[] = [
                'workspaceId' => $workspaceId,
                'exitCode' => $process->getExitCode(),
                'output' => $process->getOutput()
            ];
        }

        return $results;
    }
}
```

---

## Performance Considerations

Optimize batch operations for maximum performance.

### Chunking

Process large batches in chunks:

```php
class BatchProcessor
{
    private const CHUNK_SIZE = 100;

    public function processLargeBatch(array $elements): array
    {
        $chunks = array_chunk($elements, self::CHUNK_SIZE);
        $results = [];

        foreach ($chunks as $index => $chunk) {
            $chunkResult = $this->processChunk($chunk);
            $results = array_merge($results, $chunkResult);

            // Progress reporting
            $progress = (($index + 1) / count($chunks)) * 100;
            $this->logger->info("Batch progress: {$progress}%");

            // Periodic garbage collection
            if ($index % 10 === 0) {
                gc_collect_cycles();
            }
        }

        return $results;
    }
}
```

### Caching

Cache intermediate results:

```php
class CachedBatchProcessor extends BatchProcessor
{
    public function __construct(
        private readonly CacheInterface $cache
    ) {}

    public function processWithCache(array $elements): array
    {
        $cacheKey = 'batch:' . md5(json_encode($elements));
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            $this->logger->info('Using cached batch result');
            return $cached;
        }

        $result = $this->processLargeBatch($elements);

        $this->cache->set($cacheKey, $result, 3600);

        return $result;
    }
}
```

### Progress Reporting

Report progress for long-running batch operations:

```php
#[McpTool(
    name: 'batch_import_with_progress',
    description: 'Imports large dataset with progress reporting'
)]
public function batchImportWithProgress(
    string $workspaceId,
    array $data,
    callable $progressCallback
): array {
    $total = count($data);
    $processed = 0;

    foreach ($data as $item) {
        $this->processItem($workspaceId, $item);
        $processed++;

        // Report progress
        $progressCallback([
            'processed' => $processed,
            'total' => $total,
            'percentage' => ($processed / $total) * 100
        ]);
    }

    return ['success' => true, 'processed' => $processed];
}
```

---

## Error Handling

Handle errors gracefully in batch operations.

### Partial Success

Allow batch to continue even if some items fail:

```php
public function batchWithPartialSuccess(array $items): array
{
    $succeeded = [];
    $failed = [];

    foreach ($items as $index => $item) {
        try {
            $result = $this->processItem($item);
            $succeeded[] = ['index' => $index, 'result' => $result];
        } catch (\Exception $e) {
            $failed[] = [
                'index' => $index,
                'item' => $item,
                'error' => $e->getMessage()
            ];
        }
    }

    return [
        'succeeded' => $succeeded,
        'failed' => $failed,
        'successCount' => count($succeeded),
        'failureCount' => count($failed)
    ];
}
```

### Rollback on Error

Rollback all changes if any item fails:

```php
public function batchWithRollback(array $items): array
{
    $this->beginTransaction();

    try {
        foreach ($items as $item) {
            $this->processItem($item);
        }

        $this->commit();

        return ['success' => true, 'processed' => count($items)];
    } catch (\Exception $e) {
        $this->rollback();

        throw new ToolCallException(
            "Batch operation failed, all changes rolled back: " . $e->getMessage()
        );
    }
}
```

### Error Recovery

Implement retry logic for transient failures:

```php
class RetryableBatchProcessor
{
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_MS = 100;

    public function processWithRetry(array $items): array
    {
        $failed = $items;
        $succeeded = [];
        $attempts = 0;

        while (!empty($failed) && $attempts < self::MAX_RETRIES) {
            $attempts++;
            $this->logger->info("Batch attempt {$attempts}");

            $retryResults = $this->batchWithPartialSuccess($failed);

            $succeeded = array_merge($succeeded, $retryResults['succeeded']);
            $failed = array_column($retryResults['failed'], 'item');

            if (!empty($failed)) {
                usleep(self::RETRY_DELAY_MS * 1000 * $attempts); // Exponential backoff
            }
        }

        return [
            'succeeded' => $succeeded,
            'failed' => $failed,
            'attempts' => $attempts
        ];
    }
}
```

---

<p align="right">
  <strong>Back:</strong> <a href="cloud-integration.md">← Cloud Integration</a> |
  <strong>Up:</strong> <a href="../README.md">Documentation Index</a>
</p>
