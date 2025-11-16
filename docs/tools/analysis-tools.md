# Analysis Tools

- [Introduction](#introduction)
- [Workspace Validation](#workspace-validation)
    - [Basic Validation](#basic-validation)
    - [Understanding Validation Results](#understanding-validation-results)
    - [Common Validation Errors](#common-validation-errors)
- [Element Search](#element-search)
    - [Basic Search](#basic-search)
    - [Search Patterns](#search-patterns)
    - [Working with Results](#working-with-results)
- [Dependency Analysis](#dependency-analysis)
    - [Complete Workspace Analysis](#complete-workspace-analysis)
    - [Element-Specific Analysis](#element-specific-analysis)
    - [Dependency Visualization](#dependency-visualization)
- [Debugging & Troubleshooting](#debugging--troubleshooting)
    - [Debugging with validate_workspace](#debugging-with-validate_workspace)
    - [Debugging with find_element](#debugging-with-find_element)
    - [Debugging with analyze_dependencies](#debugging-with-analyze_dependencies)
- [Use Cases](#use-cases)
    - [Quality Assurance](#quality-assurance)
    - [Refactoring Support](#refactoring-support)
    - [Architecture Review](#architecture-review)
- [Advanced Techniques](#advanced-techniques)
    - [Identifying Architectural Smells](#identifying-architectural-smells)
    - [Impact Analysis](#impact-analysis)
    - [Automated Validation](#automated-validation)

## Introduction

The Analysis Tools provide powerful capabilities for inspecting, validating, and understanding your Structurizr workspaces. These tools help ensure workspace quality, discover elements, and analyze architectural dependencies.

The three analysis tools are:

- **validate_workspace**: Validates DSL syntax and structure using Structurizr CLI
- **find_element**: Searches for elements by name with flexible pattern matching
- **analyze_dependencies**: Analyzes relationships between architectural elements

These tools are essential for:
- **Quality Assurance**: Ensuring workspace correctness before sharing or deployment
- **Architecture Understanding**: Discovering element relationships and dependencies
- **Debugging**: Identifying issues in workspace structure or DSL syntax
- **Refactoring**: Understanding impact of changes before making them

## Workspace Validation

### Basic Validation

The `validate_workspace` tool validates your workspace DSL using the official Structurizr CLI validator. This ensures your workspace syntax is correct and can be rendered properly.

```php
// Validate a workspace
$result = $client->callTool('validate_workspace', [
    'workspaceId' => 'my-workspace'
]);
```

**Response Structure:**

```json
{
    "workspaceId": "my-workspace",
    "isValid": true,
    "errors": [],
    "warnings": [],
    "summary": "Validation successful",
    "errorCount": 0,
    "warningCount": 0
}
```

### Understanding Validation Results

The validation result provides comprehensive feedback about your workspace:

**Valid Workspace:**

```json
{
    "workspaceId": "e-commerce-system",
    "isValid": true,
    "errors": [],
    "warnings": [
        "Container 'database' has no components defined"
    ],
    "summary": "Validation successful with 1 warning",
    "errorCount": 0,
    "warningCount": 1
}
```

In this case, the workspace is syntactically valid but has a warning about an empty container.

**Invalid Workspace:**

```json
{
    "workspaceId": "broken-workspace",
    "isValid": false,
    "errors": [
        "Line 15: Missing closing brace for softwareSystem block",
        "Line 23: Unknown element type 'service'"
    ],
    "warnings": [],
    "summary": "Validation failed with 2 errors",
    "errorCount": 2,
    "warningCount": 0
}
```

**Empty Workspace:**

```json
{
    "workspaceId": "empty-workspace",
    "isValid": false,
    "errors": ["Workspace DSL is empty"],
    "warnings": [],
    "summary": "Validation failed: Workspace DSL is empty",
    "errorCount": 1,
    "warningCount": 0
}
```

### Common Validation Errors

#### Syntax Errors

**Missing Closing Braces:**

```dsl
workspace "Example" {
    model {
        system = softwareSystem "System" {
            container "Web" "Description"
        # Missing closing brace here
    }
}
```

**Error:** `Missing closing brace for softwareSystem block`

**Solution:** Ensure all blocks have matching opening and closing braces.

#### Structural Errors

**Undefined Element References:**

```dsl
workspace "Example" {
    model {
        user = person "User"
        # 'system' is not defined
        user -> system "Uses"
    }
}
```

**Error:** `Undefined element reference 'system'`

**Solution:** Define all elements before creating relationships to them.

#### Invalid Element Types

```dsl
workspace "Example" {
    model {
        # 'service' is not a valid element type
        api = service "API Service"
    }
}
```

**Error:** `Unknown element type 'service'`

**Solution:** Use valid element types: `person`, `softwareSystem`, `container`, `component`.

## Element Search

### Basic Search

The `find_element` tool searches for elements by name using case-insensitive partial matching:

```php
// Search for elements containing "api"
$result = $client->callTool('find_element', [
    'workspaceId' => 'my-workspace',
    'name' => 'api'
]);
```

**Response Structure:**

```json
{
    "workspaceId": "my-workspace",
    "searchTerm": "api",
    "matches": [
        {
            "id": "apiGateway",
            "name": "API Gateway",
            "type": "container",
            "description": "Routes requests to microservices",
            "technology": "Spring Cloud Gateway"
        },
        {
            "id": "paymentApi",
            "name": "Payment API",
            "type": "softwareSystem",
            "description": "External payment processing",
            "technology": null
        }
    ],
    "count": 2
}
```

### Search Patterns

#### Exact Name Search

Search for an exact element name (case-insensitive):

```php
// Find "User" - matches "User", "user", "USER"
$result = $client->callTool('find_element', [
    'workspaceId' => 'workspace-id',
    'name' => 'User'
]);
```

#### Partial Match Search

Search finds all elements containing the search term:

```php
// Find "service" - matches "Auth Service", "User Service", "Microservices"
$result = $client->callTool('find_element', [
    'workspaceId' => 'workspace-id',
    'name' => 'service'
]);
```

**Example Results:**

```json
{
    "searchTerm": "service",
    "matches": [
        {
            "id": "authService",
            "name": "Auth Service",
            "type": "container"
        },
        {
            "id": "userService",
            "name": "User Service",
            "type": "container"
        },
        {
            "id": "microservicesPlatform",
            "name": "Microservices Platform",
            "type": "softwareSystem"
        }
    ],
    "count": 3
}
```

#### Technology-Agnostic Search

The search focuses on element names, not technologies:

```php
// Searches only in the 'name' field
$result = $client->callTool('find_element', [
    'workspaceId' => 'workspace-id',
    'name' => 'spring'  // Won't match technology="Spring Boot"
]);
```

### Working with Results

#### No Matches Found

```json
{
    "workspaceId": "my-workspace",
    "searchTerm": "nonexistent",
    "matches": [],
    "count": 0
}
```

#### Filtering by Type

While the tool doesn't filter by type, you can do so in your application:

```php
$result = $client->callTool('find_element', [
    'workspaceId' => 'workspace-id',
    'name' => 'database'
]);

// Filter for containers only
$containers = array_filter($result['matches'], function($element) {
    return $element['type'] === 'container';
});
```

#### Getting Element IDs

Use search results to obtain element IDs for other operations:

```php
$searchResult = $client->callTool('find_element', [
    'workspaceId' => 'workspace-id',
    'name' => 'API Gateway'
]);

if ($searchResult['count'] > 0) {
    $elementId = $searchResult['matches'][0]['id'];

    // Use the ID for dependency analysis
    $dependencies = $client->callTool('analyze_dependencies', [
        'workspaceId' => 'workspace-id',
        'elementId' => $elementId
    ]);
}
```

## Dependency Analysis

### Complete Workspace Analysis

Analyze all dependencies in a workspace without specifying an element:

```php
$result = $client->callTool('analyze_dependencies', [
    'workspaceId' => 'my-workspace'
]);
```

**Response Structure:**

```json
{
    "workspaceId": "my-workspace",
    "totalElements": 12,
    "totalRelationships": 18,
    "dependencyGraph": {
        "apiGateway": {
            "element": {
                "name": "API Gateway",
                "type": "container",
                "description": "Routes requests"
            },
            "inboundCount": 5,
            "outboundCount": 8,
            "totalDependencies": 13
        },
        "database": {
            "element": {
                "name": "Database",
                "type": "container",
                "description": "Stores data"
            },
            "inboundCount": 6,
            "outboundCount": 0,
            "totalDependencies": 6
        }
    },
    "relationships": [
        {
            "source": "webApp",
            "destination": "apiGateway",
            "description": "Makes API calls to",
            "technology": "HTTPS/REST"
        }
    ]
}
```

**Key Features:**

- **Sorted by Impact**: Elements sorted by total dependencies (most connected first)
- **Complete Graph**: All elements and their connection counts
- **Full Relationships**: Detailed list of all relationships

### Element-Specific Analysis

Analyze dependencies for a specific element:

```php
$result = $client->callTool('analyze_dependencies', [
    'workspaceId' => 'my-workspace',
    'elementId' => 'apiGateway'
]);
```

**Response Structure:**

```json
{
    "workspaceId": "my-workspace",
    "elementId": "apiGateway",
    "element": {
        "name": "API Gateway",
        "type": "container",
        "description": "Routes requests to microservices"
    },
    "inboundDependencies": [
        {
            "source": "webApp",
            "destination": "apiGateway",
            "description": "Makes API calls to",
            "technology": "HTTPS/REST"
        },
        {
            "source": "mobileApp",
            "destination": "apiGateway",
            "description": "Sends requests to",
            "technology": "HTTPS/REST"
        }
    ],
    "outboundDependencies": [
        {
            "source": "apiGateway",
            "destination": "authService",
            "description": "Authenticates via",
            "technology": "OAuth 2.0"
        },
        {
            "source": "apiGateway",
            "destination": "userService",
            "description": "Routes to",
            "technology": "HTTP"
        }
    ],
    "totalInbound": 2,
    "totalOutbound": 2
}
```

**Understanding the Results:**

- **Inbound Dependencies**: Who depends on this element (consumers)
- **Outbound Dependencies**: What this element depends on (dependencies)
- **Total Counts**: Quick metrics for understanding coupling

### Dependency Visualization

#### Identifying Highly Coupled Elements

Elements with high dependency counts indicate architectural hotspots:

```php
$analysis = $client->callTool('analyze_dependencies', [
    'workspaceId' => 'workspace-id'
]);

// Elements are already sorted by totalDependencies
$mostCoupled = array_slice($analysis['dependencyGraph'], 0, 5);

foreach ($mostCoupled as $id => $data) {
    echo "{$data['element']['name']}: {$data['totalDependencies']} dependencies\n";
}
```

**Output:**

```
API Gateway: 13 dependencies
Database: 6 dependencies
Auth Service: 5 dependencies
```

#### Finding Dependency Bottlenecks

Elements with many inbound dependencies are potential bottlenecks:

```php
$sorted = $analysis['dependencyGraph'];
uasort($sorted, function($a, $b) {
    return $b['inboundCount'] <=> $a['inboundCount'];
});

$bottlenecks = array_slice($sorted, 0, 3);
```

#### Discovering Isolated Elements

Elements with zero dependencies might be unused:

```php
$isolated = array_filter($analysis['dependencyGraph'], function($data) {
    return $data['totalDependencies'] === 0;
});
```

## Debugging & Troubleshooting

### Debugging with validate_workspace

#### Scenario: Can't Export Workspace

**Problem:** Export operations fail with unclear errors.

**Solution:** Validate first to identify issues:

```php
// Step 1: Validate
$validation = $client->callTool('validate_workspace', [
    'workspaceId' => 'problematic-workspace'
]);

if (!$validation['isValid']) {
    echo "Validation Errors:\n";
    foreach ($validation['errors'] as $error) {
        echo "  - {$error}\n";
    }
}

// Step 2: Fix errors in DSL
// Step 3: Re-validate before export
```

#### Scenario: Workspace Won't Load in Structurizr

**Problem:** Workspace loads locally but fails in Structurizr cloud/lite.

**Solution:** Check for warnings that might cause rendering issues:

```php
$validation = $client->callTool('validate_workspace', [
    'workspaceId' => 'workspace-id'
]);

if ($validation['warningCount'] > 0) {
    echo "Warnings that might affect rendering:\n";
    foreach ($validation['warnings'] as $warning) {
        echo "  - {$warning}\n";
    }
}
```

#### Scenario: Pre-Deployment Validation

**Best Practice:** Always validate before pushing to production:

```php
function deployWorkspace($workspaceId) {
    // Validate first
    $validation = $client->callTool('validate_workspace', [
        'workspaceId' => $workspaceId
    ]);

    if (!$validation['isValid']) {
        throw new Exception("Cannot deploy invalid workspace: " .
            implode(', ', $validation['errors']));
    }

    if ($validation['warningCount'] > 0) {
        // Log warnings but continue
        error_log("Workspace has {$validation['warningCount']} warnings");
    }

    // Proceed with deployment
    // ...
}
```

### Debugging with find_element

#### Scenario: Can't Find Element ID for Relationship

**Problem:** Need to create a relationship but don't know the element ID.

**Solution:** Search by name to find the ID:

```php
// Step 1: Search for source element
$sourceResult = $client->callTool('find_element', [
    'workspaceId' => 'workspace-id',
    'name' => 'Web Application'
]);

$sourceId = $sourceResult['matches'][0]['id'];

// Step 2: Search for destination element
$destResult = $client->callTool('find_element', [
    'workspaceId' => 'workspace-id',
    'name' => 'Database'
]);

$destId = $destResult['matches'][0]['id'];

// Step 3: Create relationship
$client->callTool('add_relationship', [
    'workspaceId' => 'workspace-id',
    'sourceId' => $sourceId,
    'destinationId' => $destId,
    'description' => 'Reads from and writes to',
    'technology' => 'JDBC'
]);
```

#### Scenario: Verify Element Exists

**Problem:** Need to check if an element exists before operating on it.

**Solution:** Search and check count:

```php
function elementExists($workspaceId, $elementName) {
    $result = $client->callTool('find_element', [
        'workspaceId' => $workspaceId,
        'name' => $elementName
    ]);

    return $result['count'] > 0;
}

if (!elementExists('workspace-id', 'Payment Gateway')) {
    // Create the element
    $client->callTool('add_container', [
        'workspaceId' => 'workspace-id',
        'systemId' => 'ecommerce',
        'name' => 'Payment Gateway',
        'description' => 'Handles payments',
        'technology' => 'Stripe API'
    ]);
}
```

#### Scenario: Finding All Databases

**Problem:** Need to list all database containers.

**Solution:** Search for "database" and filter by type:

```php
$result = $client->callTool('find_element', [
    'workspaceId' => 'workspace-id',
    'name' => 'database'
]);

$databases = array_filter($result['matches'], function($element) {
    return $element['type'] === 'container' &&
           stripos($element['name'], 'database') !== false;
});

foreach ($databases as $db) {
    echo "Database: {$db['name']} ({$db['technology']})\n";
}
```

### Debugging with analyze_dependencies

#### Scenario: Understanding Breaking Changes

**Problem:** Need to know what will be affected by removing an element.

**Solution:** Analyze dependencies first:

```php
// Step 1: Find element to remove
$searchResult = $client->callTool('find_element', [
    'workspaceId' => 'workspace-id',
    'name' => 'Legacy Service'
]);

$elementId = $searchResult['matches'][0]['id'];

// Step 2: Analyze dependencies
$deps = $client->callTool('analyze_dependencies', [
    'workspaceId' => 'workspace-id',
    'elementId' => $elementId
]);

// Step 3: Check impact
if ($deps['totalInbound'] > 0) {
    echo "WARNING: {$deps['totalInbound']} elements depend on this:\n";
    foreach ($deps['inboundDependencies'] as $dep) {
        echo "  - {$dep['source']} -> {$dep['description']}\n";
    }
    echo "These relationships must be handled before removal.\n";
}
```

#### Scenario: Circular Dependencies

**Problem:** Need to identify circular dependencies in architecture.

**Solution:** Analyze mutual dependencies:

```php
function findCircularDependencies($workspaceId) {
    $analysis = $client->callTool('analyze_dependencies', [
        'workspaceId' => $workspaceId
    ]);

    $circular = [];

    foreach ($analysis['relationships'] as $rel) {
        $source = $rel['source'];
        $dest = $rel['destination'];

        // Check if reverse relationship exists
        $reverse = array_filter($analysis['relationships'], function($r) use ($source, $dest) {
            return $r['source'] === $dest && $r['destination'] === $source;
        });

        if (!empty($reverse)) {
            $circular[] = "{$source} <-> {$dest}";
        }
    }

    return array_unique($circular);
}
```

#### Scenario: Dead Code Detection

**Problem:** Finding unused elements in the architecture.

**Solution:** Look for elements with zero dependencies:

```php
$analysis = $client->callTool('analyze_dependencies', [
    'workspaceId' => 'workspace-id'
]);

$unused = array_filter($analysis['dependencyGraph'], function($data) {
    return $data['totalDependencies'] === 0;
});

if (!empty($unused)) {
    echo "Potentially unused elements:\n";
    foreach ($unused as $id => $data) {
        echo "  - {$data['element']['name']} ({$data['element']['type']})\n";
    }
}
```

## Use Cases

### Quality Assurance

#### Pre-Commit Validation

Validate workspaces before committing to version control:

```bash
#!/bin/bash
# validate-workspace.sh

WORKSPACE_ID=$1

php << 'EOF'
<?php
require 'vendor/autoload.php';

$client = new McpClient();
$result = $client->callTool('validate_workspace', [
    'workspaceId' => $argv[1]
]);

if (!$result['isValid']) {
    echo "ERROR: Workspace validation failed\n";
    foreach ($result['errors'] as $error) {
        echo "  $error\n";
    }
    exit(1);
}

exit(0);
EOF
```

#### Continuous Integration

Integrate validation into CI/CD pipeline:

```yaml
# .github/workflows/validate.yml
name: Validate Workspaces

on: [push, pull_request]

jobs:
  validate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'

      - name: Validate all workspaces
        run: |
          for workspace in workspaces/*.dsl; do
            workspace_id=$(basename $workspace .dsl)
            php validate.php $workspace_id || exit 1
          done
```

### Refactoring Support

#### Safe Element Renaming

Check dependencies before renaming an element:

```php
function safeRenameElement($workspaceId, $oldName, $newName) {
    // Step 1: Find the element
    $search = $client->callTool('find_element', [
        'workspaceId' => $workspaceId,
        'name' => $oldName
    ]);

    if ($search['count'] === 0) {
        throw new Exception("Element not found: {$oldName}");
    }

    $elementId = $search['matches'][0]['id'];

    // Step 2: Analyze dependencies
    $deps = $client->callTool('analyze_dependencies', [
        'workspaceId' => $workspaceId,
        'elementId' => $elementId
    ]);

    // Step 3: Report impact
    echo "Renaming '{$oldName}' to '{$newName}'\n";
    echo "This will affect:\n";
    echo "  - {$deps['totalInbound']} incoming relationships\n";
    echo "  - {$deps['totalOutbound']} outgoing relationships\n";

    // Step 4: Proceed with rename (manual DSL edit required)
    // ...
}
```

#### Merging Duplicate Elements

Find and merge duplicate elements:

```php
function findDuplicates($workspaceId, $searchTerm) {
    $result = $client->callTool('find_element', [
        'workspaceId' => $workspaceId,
        'name' => $searchTerm
    ]);

    if ($result['count'] > 1) {
        echo "Found {$result['count']} potential duplicates:\n";

        foreach ($result['matches'] as $match) {
            $deps = $client->callTool('analyze_dependencies', [
                'workspaceId' => $workspaceId,
                'elementId' => $match['id']
            ]);

            echo "  - {$match['name']} ({$match['id']}): ";
            echo "{$deps['totalInbound']} in, {$deps['totalOutbound']} out\n";
        }
    }
}
```

### Architecture Review

#### Dependency Metrics Report

Generate architectural metrics for review:

```php
function generateMetricsReport($workspaceId) {
    $analysis = $client->callTool('analyze_dependencies', [
        'workspaceId' => $workspaceId
    ]);

    echo "=== Architecture Metrics ===\n\n";

    echo "Total Elements: {$analysis['totalElements']}\n";
    echo "Total Relationships: {$analysis['totalRelationships']}\n";

    $avgDeps = $analysis['totalRelationships'] / $analysis['totalElements'];
    echo "Average Dependencies per Element: " . round($avgDeps, 2) . "\n\n";

    echo "=== Top 5 Most Connected Elements ===\n";
    $top5 = array_slice($analysis['dependencyGraph'], 0, 5);
    foreach ($top5 as $id => $data) {
        echo "{$data['element']['name']}: {$data['totalDependencies']} total ";
        echo "({$data['inboundCount']} in, {$data['outboundCount']} out)\n";
    }

    // Calculate coupling metrics
    $inboundCounts = array_column($analysis['dependencyGraph'], 'inboundCount');
    $outboundCounts = array_column($analysis['dependencyGraph'], 'outboundCount');

    echo "\n=== Coupling Metrics ===\n";
    echo "Max Inbound (Afferent Coupling): " . max($inboundCounts) . "\n";
    echo "Max Outbound (Efferent Coupling): " . max($outboundCounts) . "\n";
}
```

#### Completeness Check

Verify workspace completeness:

```php
function checkCompleteness($workspaceId) {
    $validation = $client->callTool('validate_workspace', [
        'workspaceId' => $workspaceId
    ]);

    $analysis = $client->callTool('analyze_dependencies', [
        'workspaceId' => $workspaceId
    ]);

    $issues = [];

    // Check validation
    if (!$validation['isValid']) {
        $issues[] = "Workspace has validation errors";
    }

    // Check for isolated elements
    $isolated = array_filter($analysis['dependencyGraph'], function($data) {
        return $data['totalDependencies'] === 0;
    });

    if (!empty($isolated)) {
        $issues[] = count($isolated) . " elements have no dependencies";
    }

    // Check for elements without descriptions
    foreach ($analysis['dependencyGraph'] as $id => $data) {
        if (empty($data['element']['description'])) {
            $issues[] = "'{$data['element']['name']}' has no description";
        }
    }

    return $issues;
}
```

## Advanced Techniques

### Identifying Architectural Smells

#### God Object Detection

Find elements that are too highly coupled:

```php
function detectGodObjects($workspaceId, $threshold = 10) {
    $analysis = $client->callTool('analyze_dependencies', [
        'workspaceId' => $workspaceId
    ]);

    $godObjects = array_filter($analysis['dependencyGraph'], function($data) use ($threshold) {
        return $data['totalDependencies'] >= $threshold;
    });

    if (!empty($godObjects)) {
        echo "WARNING: Potential God Objects detected:\n";
        foreach ($godObjects as $id => $data) {
            echo "  - {$data['element']['name']}: {$data['totalDependencies']} dependencies\n";
            echo "    Consider breaking this into smaller, more focused elements.\n";
        }
    }

    return $godObjects;
}
```

#### Feature Envy Detection

Find elements that depend too heavily on others:

```php
function detectFeatureEnvy($workspaceId, $threshold = 5) {
    $analysis = $client->callTool('analyze_dependencies', [
        'workspaceId' => $workspaceId
    ]);

    $envious = array_filter($analysis['dependencyGraph'], function($data) use ($threshold) {
        return $data['outboundCount'] >= $threshold &&
               $data['inboundCount'] < 2;
    });

    if (!empty($envious)) {
        echo "WARNING: Potential Feature Envy detected:\n";
        foreach ($envious as $id => $data) {
            echo "  - {$data['element']['name']}: depends on {$data['outboundCount']} elements\n";
            echo "    Consider moving functionality closer to dependencies.\n";
        }
    }

    return $envious;
}
```

### Impact Analysis

#### Change Impact Assessment

Assess the impact of potential changes:

```php
function assessChangeImpact($workspaceId, $elementName) {
    // Find element
    $search = $client->callTool('find_element', [
        'workspaceId' => $workspaceId,
        'name' => $elementName
    ]);

    if ($search['count'] === 0) {
        return "Element not found";
    }

    $elementId = $search['matches'][0]['id'];

    // Analyze dependencies
    $deps = $client->callTool('analyze_dependencies', [
        'workspaceId' => $workspaceId,
        'elementId' => $elementId
    ]);

    echo "=== Change Impact Analysis ===\n";
    echo "Element: {$deps['element']['name']}\n\n";

    echo "Direct Impact:\n";
    echo "  - {$deps['totalInbound']} elements directly depend on this\n";
    echo "  - Changes will require updates in these consumers\n\n";

    echo "Dependencies:\n";
    echo "  - This element depends on {$deps['totalOutbound']} other elements\n";
    echo "  - Changes to dependencies may affect this element\n\n";

    // Calculate risk level
    $riskScore = ($deps['totalInbound'] * 2) + $deps['totalOutbound'];
    $risk = 'LOW';
    if ($riskScore > 10) $risk = 'HIGH';
    elseif ($riskScore > 5) $risk = 'MEDIUM';

    echo "Risk Level: {$risk} (score: {$riskScore})\n";

    return [
        'element' => $deps['element'],
        'directImpact' => $deps['totalInbound'],
        'dependencies' => $deps['totalOutbound'],
        'riskLevel' => $risk,
        'riskScore' => $riskScore
    ];
}
```

### Automated Validation

#### Validation Rules Engine

Create custom validation rules:

```php
class WorkspaceValidator {
    private $client;
    private $rules = [];

    public function addRule($name, callable $rule) {
        $this->rules[$name] = $rule;
    }

    public function validate($workspaceId) {
        $results = [
            'passed' => [],
            'failed' => []
        ];

        // Run standard validation
        $validation = $this->client->callTool('validate_workspace', [
            'workspaceId' => $workspaceId
        ]);

        if (!$validation['isValid']) {
            $results['failed']['syntax'] = $validation['errors'];
        } else {
            $results['passed'][] = 'syntax';
        }

        // Run custom rules
        $analysis = $this->client->callTool('analyze_dependencies', [
            'workspaceId' => $workspaceId
        ]);

        foreach ($this->rules as $name => $rule) {
            $result = $rule($analysis);
            if ($result === true) {
                $results['passed'][] = $name;
            } else {
                $results['failed'][$name] = $result;
            }
        }

        return $results;
    }
}

// Usage
$validator = new WorkspaceValidator($client);

// Rule: No element should have more than 10 dependencies
$validator->addRule('max_dependencies', function($analysis) {
    $violations = array_filter($analysis['dependencyGraph'], function($data) {
        return $data['totalDependencies'] > 10;
    });

    if (empty($violations)) {
        return true;
    }

    return "Elements with too many dependencies: " .
           implode(', ', array_column($violations, 'element', 'name'));
});

// Rule: All elements must have descriptions
$validator->addRule('has_descriptions', function($analysis) {
    $missing = array_filter($analysis['dependencyGraph'], function($data) {
        return empty($data['element']['description']);
    });

    if (empty($missing)) {
        return true;
    }

    return "Elements missing descriptions: " .
           implode(', ', array_column($missing, 'element', 'name'));
});

$results = $validator->validate('my-workspace');
```

---

These analysis tools provide comprehensive capabilities for understanding, validating, and improving your Structurizr workspaces. Use them regularly as part of your architecture documentation workflow to ensure quality and maintainability.
