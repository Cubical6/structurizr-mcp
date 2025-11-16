# Extending the Server

- [Introduction](#introduction)
- [Creating Custom Tools](#creating-custom-tools)
- [Adding New Resources](#adding-new-resources)
- [Custom Prompts](#custom-prompts)
- [Plugin Architecture](#plugin-architecture)
- [Best Practices](#best-practices)
- [Publishing Extensions](#publishing-extensions)

---

## Introduction

Structurizr MCP Server is designed to be extensible. You can add custom tools, resources, and prompts to enhance functionality for your specific use cases without modifying the core codebase.

> **Architecture Principle:** Extensions follow the Open/Closed Principle - the server is open for extension but closed for modification.

---

## Creating Custom Tools

Custom tools allow you to add new MCP capabilities that integrate seamlessly with the existing server.

### Basic Tool Structure

All tools use PHP attributes for MCP discovery:

```php
<?php

namespace StructurizrMcp\Tools;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;

class CustomTools extends AbstractWorkspaceTool
{
    #[McpTool(
        name: 'my_custom_tool',
        description: 'Description of what this tool does'
    )]
    public function myCustomTool(
        #[Schema(description: 'Parameter description', type: 'string')]
        string $param1,

        #[Schema(description: 'Optional parameter', type: 'integer', minimum: 0)]
        int $param2 = 0
    ): array {
        try {
            // Tool implementation
            $result = $this->performCustomOperation($param1, $param2);

            return [
                'success' => true,
                'result' => $result,
                'message' => 'Operation completed successfully'
            ];
        } catch (\Exception $e) {
            throw new ToolCallException(
                "Custom tool failed: " . $e->getMessage()
            );
        }
    }

    private function performCustomOperation(string $param1, int $param2): mixed
    {
        // Your custom logic here
        return ['data' => 'example'];
    }
}
```

### Extending AbstractWorkspaceTool

Extend the base class to access workspace management:

```php
<?php

namespace StructurizrMcp\Tools;

use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Structurizr\Workspace;

class AdvancedCustomTools extends AbstractWorkspaceTool
{
    public function __construct(
        WorkspaceManager $workspaceManager,
        private readonly CustomService $customService
    ) {
        parent::__construct($workspaceManager);
    }

    #[McpTool(
        name: 'analyze_complexity',
        description: 'Analyzes architectural complexity of a workspace'
    )]
    public function analyzeComplexity(
        #[Schema(description: 'Workspace ID')]
        string $workspaceId
    ): array {
        // Use inherited method to get workspace
        $workspace = $this->getWorkspace($workspaceId);

        // Perform custom analysis
        $metrics = $this->calculateComplexityMetrics($workspace);

        return [
            'workspaceId' => $workspaceId,
            'metrics' => $metrics,
            'recommendations' => $this->generateRecommendations($metrics)
        ];
    }

    private function calculateComplexityMetrics(Workspace $workspace): array
    {
        $model = $workspace->getModel();

        return [
            'totalElements' => count($model['elements'] ?? []),
            'totalRelationships' => count($model['relationships'] ?? []),
            'cyclomaticComplexity' => $this->customService->calculateCyclomatic($model),
            'couplingScore' => $this->customService->calculateCoupling($model),
        ];
    }
}
```

### Schema Validation

Use comprehensive schema attributes for robust validation:

```php
#[McpTool(
    name: 'create_microservice',
    description: 'Creates a microservice architecture pattern'
)]
public function createMicroservice(
    #[Schema(
        description: 'Service name',
        type: 'string',
        minLength: 1,
        maxLength: 50,
        pattern: '^[a-zA-Z][a-zA-Z0-9-]*$'
    )]
    string $serviceName,

    #[Schema(
        description: 'Technology stack',
        type: 'string',
        enum: ['nodejs', 'java', 'python', 'go', 'dotnet']
    )]
    string $technology,

    #[Schema(
        description: 'Port number',
        type: 'integer',
        minimum: 1024,
        maximum: 65535
    )]
    int $port = 8080,

    #[Schema(
        description: 'Database type',
        type: 'string',
        enum: ['postgresql', 'mysql', 'mongodb', 'redis', 'none']
    )]
    string $database = 'none'
): array {
    // All parameters are pre-validated by schema
    return $this->generateMicroserviceArchitecture(
        $serviceName,
        $technology,
        $port,
        $database
    );
}
```

### Dependency Injection

Custom tools support constructor injection:

```php
class IntegrationTools extends AbstractWorkspaceTool
{
    public function __construct(
        WorkspaceManager $workspaceManager,
        private readonly HttpClient $httpClient,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($workspaceManager);
    }

    #[McpTool(
        name: 'import_from_api',
        description: 'Imports architecture from external API'
    )]
    public function importFromApi(
        #[Schema(description: 'API endpoint URL')]
        string $apiUrl,

        #[Schema(description: 'API key for authentication')]
        string $apiKey = ''
    ): array {
        // Check cache first
        $cacheKey = 'import:' . md5($apiUrl);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            $this->logger->info("Using cached import for {$apiUrl}");
            return $cached;
        }

        // Fetch from API
        $response = $this->httpClient->get($apiUrl, [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}"
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $workspace = $this->convertApiDataToWorkspace($data);

        // Cache result
        $this->cache->set($cacheKey, $workspace, 3600);

        return $workspace;
    }
}
```

---

## Adding New Resources

Resources provide read-only access to data via URIs.

### Basic Resource Structure

```php
<?php

namespace StructurizrMcp\Resources;

use Mcp\Capability\Attribute\McpResource;
use Mcp\Exception\ResourceException;

class CustomResource
{
    #[McpResource(
        uri: 'structurizr://custom/{customId}',
        name: 'Custom Resource',
        description: 'Provides access to custom data',
        mimeType: 'application/json'
    )]
    public function getCustomResource(string $customId): array
    {
        try {
            $data = $this->loadCustomData($customId);

            return [
                'contents' => [
                    [
                        'uri' => "structurizr://custom/{$customId}",
                        'mimeType' => 'application/json',
                        'text' => json_encode($data, JSON_PRETTY_PRINT)
                    ]
                ]
            ];
        } catch (\Exception $e) {
            throw new ResourceException(
                "Failed to load custom resource: " . $e->getMessage()
            );
        }
    }

    private function loadCustomData(string $customId): array
    {
        // Load and return your custom data
        return ['id' => $customId, 'data' => []];
    }
}
```

### Dynamic Resource Lists

Provide templated resources with dynamic lists:

```php
class MetricsResource
{
    #[McpResource(
        uri: 'structurizr://metrics/{metricType}/{workspaceId}',
        name: 'Workspace Metrics',
        description: 'Provides various workspace metrics',
        mimeType: 'application/json'
    )]
    public function getMetrics(string $metricType, string $workspaceId): array
    {
        $metrics = match($metricType) {
            'complexity' => $this->getComplexityMetrics($workspaceId),
            'dependencies' => $this->getDependencyMetrics($workspaceId),
            'coverage' => $this->getCoverageMetrics($workspaceId),
            default => throw new ResourceException("Unknown metric type: {$metricType}")
        };

        return [
            'contents' => [
                [
                    'uri' => "structurizr://metrics/{$metricType}/{$workspaceId}",
                    'mimeType' => 'application/json',
                    'text' => json_encode($metrics, JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
}
```

### Multiple Representations

Return different formats for the same resource:

```php
#[McpResource(
    uri: 'structurizr://report/{workspaceId}',
    name: 'Architecture Report',
    description: 'Generates architecture report in multiple formats',
    mimeType: 'text/markdown'
)]
public function getReport(
    string $workspaceId,
    string $format = 'markdown'
): array {
    $workspace = $this->workspaceManager->getWorkspace($workspaceId);

    $content = match($format) {
        'markdown' => $this->generateMarkdownReport($workspace),
        'html' => $this->generateHtmlReport($workspace),
        'json' => $this->generateJsonReport($workspace),
        default => throw new ResourceException("Unsupported format: {$format}")
    };

    $mimeType = match($format) {
        'markdown' => 'text/markdown',
        'html' => 'text/html',
        'json' => 'application/json',
    };

    return [
        'contents' => [
            [
                'uri' => "structurizr://report/{$workspaceId}?format={$format}",
                'mimeType' => $mimeType,
                'text' => $content
            ]
        ]
    ];
}
```

---

## Custom Prompts

Prompts provide reusable templates for common LLM interactions.

### Basic Prompt Structure

```php
<?php

namespace StructurizrMcp\Prompts;

use Mcp\Capability\Attribute\McpPrompt;
use Mcp\Capability\Attribute\Schema;

class CustomPrompts
{
    #[McpPrompt(
        name: 'design_review',
        description: 'Performs a comprehensive architecture design review'
    )]
    public function designReview(
        #[Schema(description: 'Workspace ID to review')]
        string $workspaceId,

        #[Schema(description: 'Focus area (security, performance, scalability)')]
        string $focus = 'general'
    ): array {
        $workspace = $this->loadWorkspace($workspaceId);

        $prompt = $this->buildReviewPrompt($workspace, $focus);

        return [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        'type' => 'text',
                        'text' => $prompt
                    ]
                ]
            ]
        ];
    }

    private function buildReviewPrompt(array $workspace, string $focus): string
    {
        $systemContext = $this->extractSystemContext($workspace);

        return <<<PROMPT
# Architecture Design Review

Please review this architecture design with a focus on: {$focus}

## System Context
{$systemContext}

## Review Criteria
1. Adherence to architectural principles
2. Scalability and performance considerations
3. Security best practices
4. Maintainability and evolution
5. Technology choices and justification

Please provide:
- Key findings (strengths and concerns)
- Risk assessment
- Specific recommendations for improvement
- Priority ranking for suggested changes

PROMPT;
    }
}
```

### Multi-Step Prompts

Create prompts that guide through multiple steps:

```php
#[McpPrompt(
    name: 'create_system_wizard',
    description: 'Interactive wizard for creating a new system architecture'
)]
public function createSystemWizard(
    #[Schema(description: 'System description')]
    string $description,

    #[Schema(description: 'Current step (1-4)', minimum: 1, maximum: 4)]
    int $step = 1
): array {
    $prompts = [
        1 => $this->getSystemContextPrompt($description),
        2 => $this->getContainerPrompt($description),
        3 => $this->getComponentPrompt($description),
        4 => $this->getDeploymentPrompt($description),
    ];

    return [
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    'type' => 'text',
                    'text' => $prompts[$step]
                ]
            ]
        ]
    ];
}
```

### Contextual Prompts

Prompts that adapt based on workspace state:

```php
#[McpPrompt(
    name: 'suggest_next_steps',
    description: 'Suggests next steps based on current workspace state'
)]
public function suggestNextSteps(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId
): array {
    $workspace = $this->loadWorkspace($workspaceId);
    $analysis = $this->analyzeWorkspaceCompleteness($workspace);

    $suggestions = match(true) {
        $analysis['hasNoViews'] =>
            "Your workspace has elements but no views. Let's create views to visualize your architecture.",
        $analysis['hasOnlySystemContext'] =>
            "You have a system context. Let's dive deeper by adding containers.",
        $analysis['missingDocumentation'] =>
            "Your architecture model is complete. Let's add documentation and ADRs.",
        default =>
            "Your workspace looks good! Consider reviewing security or performance aspects."
    };

    return [
        'description' => $suggestions,
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    'type' => 'text',
                    'text' => "# Next Steps for {$workspace['name']}\n\n{$suggestions}\n\n" .
                             "What would you like to focus on?"
                ]
            ]
        ]
    ];
}
```

---

## Plugin Architecture

Create self-contained plugins that bundle tools, resources, and prompts.

### Plugin Interface

```php
<?php

namespace StructurizrMcp\Plugin;

interface PluginInterface
{
    /**
     * Get plugin metadata
     */
    public function getMetadata(): array;

    /**
     * Register plugin capabilities
     */
    public function register(): void;

    /**
     * Initialize plugin
     */
    public function boot(): void;

    /**
     * Get plugin dependencies
     */
    public function getDependencies(): array;
}
```

### Example Plugin

```php
<?php

namespace StructurizrMcp\Plugin;

use Psr\Container\ContainerInterface;

class DiagramGeneratorPlugin implements PluginInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {}

    public function getMetadata(): array
    {
        return [
            'name' => 'diagram-generator',
            'version' => '1.0.0',
            'description' => 'Advanced diagram generation capabilities',
            'author' => 'Your Name',
            'homepage' => 'https://example.com/diagram-plugin',
        ];
    }

    public function register(): void
    {
        // Register plugin services
        $this->container->set(
            DiagramRenderer::class,
            fn() => new DiagramRenderer($this->container->get(Configuration::class))
        );
    }

    public function boot(): void
    {
        // Initialize plugin
        $this->initializeTemplates();
        $this->registerDiagramFormats();
    }

    public function getDependencies(): array
    {
        return [
            'structurizr/core' => '^1.0',
            'intervention/image' => '^2.7',
        ];
    }

    private function initializeTemplates(): void
    {
        // Load diagram templates
    }

    private function registerDiagramFormats(): void
    {
        // Register custom diagram formats
    }
}
```

### Plugin Directory Structure

```
plugins/
└── diagram-generator/
    ├── composer.json
    ├── plugin.php          # Plugin entry point
    ├── src/
    │   ├── Tools/
    │   │   └── DiagramTools.php
    │   ├── Resources/
    │   │   └── DiagramResource.php
    │   └── Services/
    │       └── DiagramRenderer.php
    ├── templates/
    │   └── diagrams/
    └── tests/
        └── DiagramToolsTest.php
```

### Plugin Loading

```php
<?php
// In server.php

use StructurizrMcp\Plugin\PluginLoader;

$pluginLoader = new PluginLoader(__DIR__ . '/plugins');
$plugins = $pluginLoader->loadPlugins();

foreach ($plugins as $plugin) {
    $plugin->register();
    $plugin->boot();
}

// Build server with plugin capabilities included
$server = Server::builder()
    ->setServerInfo(/* ... */)
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['src', 'plugins'], // Include plugins
        /* ... */
    )
    ->build();
```

---

## Best Practices

### Code Organization

```
src/
└── Extensions/          # All custom extensions here
    ├── Tools/
    │   ├── CustomTools.php
    │   └── IntegrationTools.php
    ├── Resources/
    │   ├── CustomResource.php
    │   └── MetricsResource.php
    ├── Prompts/
    │   └── CustomPrompts.php
    └── Services/
        └── CustomService.php
```

### Naming Conventions

- **Tools**: Use descriptive action verbs (e.g., `analyzeComplexity`, `generateReport`)
- **Resources**: Use noun phrases (e.g., `getMetrics`, `getReport`)
- **Prompts**: Use present tense descriptions (e.g., `reviewDesign`, `suggestImprovements`)

### Error Handling

```php
#[McpTool(name: 'custom_operation')]
public function customOperation(string $param): array
{
    try {
        $this->validateInput($param);
        $result = $this->performOperation($param);

        return [
            'success' => true,
            'result' => $result
        ];
    } catch (ValidationException $e) {
        throw new ToolCallException(
            "Invalid input: " . $e->getMessage(),
            previous: $e
        );
    } catch (\Exception $e) {
        $this->logger->error("Operation failed", [
            'param' => $param,
            'error' => $e->getMessage()
        ]);

        throw new ToolCallException(
            "Operation failed: " . $e->getMessage(),
            previous: $e
        );
    }
}
```

### Testing Extensions

```php
<?php

namespace Tests\Extensions;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Extensions\Tools\CustomTools;

class CustomToolsTest extends TestCase
{
    private CustomTools $tools;

    protected function setUp(): void
    {
        $this->tools = new CustomTools(
            $this->createMock(WorkspaceManager::class)
        );
    }

    public function testCustomToolSuccess(): void
    {
        $result = $this->tools->myCustomTool('test', 42);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('result', $result);
    }

    public function testCustomToolValidation(): void
    {
        $this->expectException(ToolCallException::class);
        $this->tools->myCustomTool('', -1);
    }
}
```

### Documentation

Document all custom capabilities:

```php
/**
 * Analyzes architectural complexity of a workspace
 *
 * This tool calculates various complexity metrics including:
 * - Cyclomatic complexity
 * - Coupling scores
 * - Cohesion metrics
 * - Dependency depth
 *
 * @param string $workspaceId The workspace to analyze
 * @return array Complexity metrics and recommendations
 *
 * @throws WorkspaceNotFoundException If workspace doesn't exist
 * @throws ToolCallException If analysis fails
 *
 * @example
 * $result = $tools->analyzeComplexity('my-workspace');
 * // Returns: ['metrics' => [...], 'recommendations' => [...]]
 */
#[McpTool(
    name: 'analyze_complexity',
    description: 'Analyzes architectural complexity of a workspace'
)]
public function analyzeComplexity(string $workspaceId): array
{
    // ...
}
```

---

## Publishing Extensions

### Packaging

Create a Composer package for your extension:

```json
{
    "name": "vendor/structurizr-mcp-extension-name",
    "description": "Custom extension for Structurizr MCP Server",
    "type": "library",
    "require": {
        "php": "^8.1",
        "cubical6/structurizr-mcp": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "Vendor\\StructurizrExtension\\": "src/"
        }
    }
}
```

### Installation Guide

Provide clear installation instructions:

```markdown
# Installation

```bash
composer require vendor/structurizr-mcp-extension-name
```

# Configuration

Add to your server.php:

```php
use Vendor\StructurizrExtension\CustomTools;

$server = Server::builder()
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['src', 'vendor/vendor/structurizr-mcp-extension-name/src']
    )
    ->build();
```
```

### Example Repository

Check out example extensions:
- [structurizr-mcp-aws-extension](https://github.com/example/structurizr-mcp-aws) - AWS architecture integration
- [structurizr-mcp-compliance-extension](https://github.com/example/structurizr-mcp-compliance) - Compliance checking
- [structurizr-mcp-metrics-extension](https://github.com/example/structurizr-mcp-metrics) - Advanced metrics

---

<p align="right">
  <strong>Next:</strong> <a href="cloud-integration.md">Cloud Integration →</a>
</p>
