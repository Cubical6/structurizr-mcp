# Performance Optimization

- [Introduction](#introduction)
- [Caching Strategies](#caching-strategies)
- [Discovery Cache](#discovery-cache)
- [Large Workspace Handling](#large-workspace-handling)
- [Memory Management](#memory-management)
- [Process Optimization](#process-optimization)
- [Performance Monitoring](#performance-monitoring)
- [Benchmarking](#benchmarking)

---

## Introduction

Structurizr MCP Server is designed for high performance, but optimal configuration and usage patterns can significantly improve response times and resource efficiency. This guide covers performance optimization techniques for various deployment scenarios.

> **Performance Goal:** The server targets sub-100ms response times for most operations and supports workspaces with thousands of elements.

---

## Caching Strategies

Effective caching is critical for minimizing redundant computations and I/O operations.

### Cache Architecture

The server implements a multi-layer caching strategy:

```
┌─────────────────────────────────────┐
│      MCP Request                    │
└──────────────┬──────────────────────┘
               │
       ┌───────▼──────────┐
       │  Discovery Cache  │ ← Tool/Resource/Prompt definitions
       └───────┬──────────┘
               │
       ┌───────▼──────────┐
       │  Workspace Cache  │ ← Workspace objects and metadata
       └───────┬──────────┘
               │
       ┌───────▼──────────┐
       │  Process Cache    │ ← CLI command results
       └───────┬──────────┘
               │
       ┌───────▼──────────┐
       │  File System      │ ← Persistent storage
       └──────────────────┘
```

### PSR-16 Simple Cache

The server uses PSR-16 compatible caching:

```php
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Create cache instance
$cache = new Psr16Cache(
    new PhpFilesAdapter(
        namespace: 'structurizr',
        defaultLifetime: 3600,
        directory: __DIR__ . '/cache'
    )
);

// Cache workspace data
$workspaceId = 'my-workspace';
$workspace = $cache->get($workspaceId);

if ($workspace === null) {
    $workspace = $this->loadWorkspaceFromDisk($workspaceId);
    $cache->set($workspaceId, $workspace, 3600); // 1 hour TTL
}
```

### Cache Invalidation

Proper cache invalidation ensures data consistency:

```php
class WorkspaceManager
{
    public function updateWorkspace(Workspace $workspace): void
    {
        // Update persistent storage
        $this->saveWorkspaceToDisk($workspace);

        // Invalidate cache
        $this->cache->delete($workspace->getId());

        // Invalidate related caches
        $this->cache->delete("workspace:{$workspace->getId()}:model");
        $this->cache->delete("workspace:{$workspace->getId()}:views");
    }

    public function clearWorkspaceCache(string $workspaceId): void
    {
        // Clear all cache entries for a workspace
        $patterns = [
            $workspaceId,
            "workspace:{$workspaceId}:*",
            "element:{$workspaceId}:*",
            "view:{$workspaceId}:*",
        ];

        foreach ($patterns as $pattern) {
            $this->cache->delete($pattern);
        }
    }
}
```

> **Best Practice:** Invalidate caches immediately after data changes to prevent stale reads.

### Cache Warming

Pre-populate caches for frequently accessed data:

```php
public function warmCache(): void
{
    // Load all workspaces into cache
    $workspaceIds = $this->listWorkspaceIds();

    foreach ($workspaceIds as $workspaceId) {
        $workspace = $this->loadWorkspace($workspaceId);
        $this->cache->set($workspaceId, $workspace, 3600);
    }

    $this->logger->info('Cache warmed with ' . count($workspaceIds) . ' workspaces');
}
```

Run cache warming after server startup or during low-traffic periods.

---

## Discovery Cache

The MCP discovery cache significantly improves server startup time.

### How Discovery Works

Discovery scans PHP files to find MCP capabilities:

```php
$server = Server::builder()
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['src'],
        excludeDirs: ['vendor', 'tests', 'cache'],
        cache: $cache // PSR-16 cache instance
    )
    ->build();
```

### Performance Impact

| Scenario | Without Cache | With Cache |
|----------|---------------|------------|
| First startup | 500-800ms | 500-800ms |
| Subsequent startups | 500-800ms | 10-50ms |
| After code change | 500-800ms | 500-800ms |

### Cache Invalidation

The discovery cache automatically invalidates when:
- PHP files are modified (based on file modification time)
- Server version changes
- Cache manually cleared

Manual cache clearing:

```bash
# Clear all caches
rm -rf cache/*

# Clear only discovery cache
rm -rf cache/structurizr.discovery.*
```

### Optimizing Discovery

For large codebases, optimize discovery performance:

```php
$server = Server::builder()
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['src'], // Only scan necessary directories
        excludeDirs: [
            'vendor',
            'tests',
            'cache',
            'sessions',
            'workspaces',
            'docs',
            'examples',
        ],
        cache: $cache
    )
    ->build();
```

> **Performance Tip:** Exclude as many directories as possible from discovery scanning.

---

## Large Workspace Handling

Efficiently handle workspaces with thousands of elements and complex relationships.

### Lazy Loading

Load workspace components on demand:

```php
class Workspace
{
    private ?array $elements = null;
    private ?array $relationships = null;

    public function getElements(): array
    {
        if ($this->elements === null) {
            $this->elements = $this->loadElementsFromData();
        }

        return $this->elements;
    }

    public function findElement(string $id): ?Element
    {
        // Only load specific element, not entire collection
        return $this->loadElementById($id);
    }
}
```

### Pagination

Paginate large result sets:

```php
#[McpTool(
    name: 'list_elements',
    description: 'Lists elements in a workspace with pagination'
)]
public function listElements(
    string $workspaceId,
    #[Schema(description: 'Page number (0-indexed)', minimum: 0)]
    int $page = 0,
    #[Schema(description: 'Items per page', minimum: 1, maximum: 100)]
    int $pageSize = 50
): array {
    $workspace = $this->getWorkspace($workspaceId);
    $elements = $workspace->getElements();

    $offset = $page * $pageSize;
    $pagedElements = array_slice($elements, $offset, $pageSize);

    return [
        'elements' => $pagedElements,
        'page' => $page,
        'pageSize' => $pageSize,
        'total' => count($elements),
        'hasMore' => ($offset + $pageSize) < count($elements),
    ];
}
```

### Streaming Export

Stream large exports instead of loading into memory:

```php
public function exportToDslStreaming(string $workspaceId): void
{
    $workspace = $this->getWorkspace($workspaceId);

    // Stream header
    echo "workspace \"{$workspace->getName()}\" {\n";

    // Stream model elements in chunks
    foreach ($workspace->streamElements(chunkSize: 100) as $chunk) {
        echo $this->dslBuilder->buildElements($chunk);
        flush();
    }

    // Stream views
    foreach ($workspace->streamViews(chunkSize: 10) as $chunk) {
        echo $this->dslBuilder->buildViews($chunk);
        flush();
    }

    echo "}\n";
}
```

### Indexing

Build indexes for fast lookups:

```php
class WorkspaceIndex
{
    private array $elementsByName = [];
    private array $elementsByTag = [];
    private array $relationshipsBySource = [];

    public function buildIndex(Workspace $workspace): void
    {
        foreach ($workspace->getElements() as $element) {
            // Index by name
            $this->elementsByName[$element->getName()] = $element;

            // Index by tags
            foreach ($element->getTags() as $tag) {
                $this->elementsByTag[$tag][] = $element;
            }
        }

        foreach ($workspace->getRelationships() as $relationship) {
            // Index by source
            $this->relationshipsBySource[$relationship->getSourceId()][] = $relationship;
        }
    }

    public function findByTag(string $tag): array
    {
        return $this->elementsByTag[$tag] ?? [];
    }
}
```

> **Performance Tip:** Build indexes once at workspace load time for O(1) lookups instead of O(n) scans.

---

## Memory Management

Optimize memory usage for long-running server processes.

### Memory Limits

Configure PHP memory limits appropriately:

```ini
; php.ini
memory_limit = 256M  ; For small workspaces
memory_limit = 512M  ; For medium workspaces
memory_limit = 1G    ; For large workspaces
```

Check memory usage:

```php
public function getMemoryUsage(): array
{
    return [
        'current' => memory_get_usage(true),
        'peak' => memory_get_peak_usage(true),
        'limit' => ini_get('memory_limit'),
        'formatted' => [
            'current' => $this->formatBytes(memory_get_usage(true)),
            'peak' => $this->formatBytes(memory_get_peak_usage(true)),
        ],
    ];
}
```

### Object Pooling

Reuse objects instead of creating new instances:

```php
class ElementFactory
{
    private array $pool = [];

    public function createElement(string $type): Element
    {
        if (isset($this->pool[$type])) {
            $element = $this->pool[$type];
            $element->reset();
            return $element;
        }

        $element = match($type) {
            'person' => new Person(),
            'softwareSystem' => new SoftwareSystem(),
            'container' => new Container(),
            'component' => new Component(),
        };

        $this->pool[$type] = $element;
        return $element;
    }
}
```

### Garbage Collection

Manually trigger garbage collection for long-running processes:

```php
public function processLargeWorkspace(string $workspaceId): void
{
    $workspace = $this->loadWorkspace($workspaceId);

    foreach ($workspace->getElements() as $index => $element) {
        $this->processElement($element);

        // Trigger GC every 1000 elements
        if ($index % 1000 === 0) {
            gc_collect_cycles();
        }
    }

    // Final cleanup
    unset($workspace);
    gc_collect_cycles();
}
```

### Memory Profiling

Profile memory usage to identify leaks:

```php
public function profileMemory(callable $operation): array
{
    $startMemory = memory_get_usage(true);
    $startPeak = memory_get_peak_usage(true);

    $result = $operation();

    $endMemory = memory_get_usage(true);
    $endPeak = memory_get_peak_usage(true);

    return [
        'result' => $result,
        'memory' => [
            'start' => $startMemory,
            'end' => $endMemory,
            'delta' => $endMemory - $startMemory,
            'peak' => $endPeak - $startPeak,
        ],
    ];
}
```

---

## Process Optimization

Optimize external process execution for better performance.

### Process Pooling

Reuse CLI processes when possible:

```php
class CliProcessPool
{
    private array $processes = [];
    private int $maxProcesses = 5;

    public function getProcess(): Process
    {
        // Return idle process if available
        foreach ($this->processes as $process) {
            if (!$process->isRunning()) {
                return $process;
            }
        }

        // Create new process if under limit
        if (count($this->processes) < $this->maxProcesses) {
            $process = new Process([$this->cliPath]);
            $this->processes[] = $process;
            return $process;
        }

        // Wait for a process to become available
        while (true) {
            foreach ($this->processes as $process) {
                if (!$process->isRunning()) {
                    return $process;
                }
            }
            usleep(10000); // 10ms
        }
    }
}
```

### Parallel Execution

Execute independent operations in parallel:

```php
use Symfony\Component\Process\Process;

public function exportMultipleViews(array $viewKeys): array
{
    $processes = [];

    // Start all export processes
    foreach ($viewKeys as $viewKey) {
        $process = new Process([
            $this->cliPath,
            'export',
            '-workspace', $this->workspaceId,
            '-view', $viewKey,
        ]);
        $process->start();
        $processes[$viewKey] = $process;
    }

    // Collect results
    $results = [];
    foreach ($processes as $viewKey => $process) {
        $process->wait();
        $results[$viewKey] = $process->getOutput();
    }

    return $results;
}
```

### Command Batching

Batch multiple operations into single CLI invocations:

```php
public function validateMultipleWorkspaces(array $workspaceIds): array
{
    // Instead of running CLI once per workspace,
    // create a batch validation script
    $batchScript = $this->createBatchValidationScript($workspaceIds);

    $process = new Process([$this->cliPath, 'validate', '-batch', $batchScript]);
    $process->run();

    return $this->parseBatchResults($process->getOutput());
}
```

---

## Performance Monitoring

Track performance metrics to identify bottlenecks.

### Request Timing

Measure operation execution time:

```php
class PerformanceMonitor
{
    private array $timings = [];

    public function startTimer(string $operation): void
    {
        $this->timings[$operation] = [
            'start' => microtime(true),
        ];
    }

    public function stopTimer(string $operation): float
    {
        if (!isset($this->timings[$operation])) {
            return 0.0;
        }

        $duration = microtime(true) - $this->timings[$operation]['start'];
        $this->timings[$operation]['duration'] = $duration;

        $this->logger->debug("Operation '{$operation}' took {$duration}s");

        return $duration;
    }

    public function getMetrics(): array
    {
        return array_map(
            fn($timing) => $timing['duration'] ?? 0,
            $this->timings
        );
    }
}
```

Usage:

```php
$monitor->startTimer('create_workspace');
$workspace = $this->workspaceManager->createWorkspace($name, $description);
$duration = $monitor->stopTimer('create_workspace');
```

### Resource Monitoring

Track system resource usage:

```php
public function getResourceMetrics(): array
{
    return [
        'memory' => [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
        ],
        'cpu' => [
            'user' => getrusage()['ru_utime.tv_sec'] ?? 0,
            'system' => getrusage()['ru_stime.tv_sec'] ?? 0,
        ],
        'io' => [
            'reads' => getrusage()['ru_inblock'] ?? 0,
            'writes' => getrusage()['ru_oublock'] ?? 0,
        ],
    ];
}
```

### Slow Query Logging

Log operations that exceed thresholds:

```php
public function executeWithLogging(string $operation, callable $callback): mixed
{
    $start = microtime(true);
    $result = $callback();
    $duration = microtime(true) - $start;

    if ($duration > 1.0) { // Log if over 1 second
        $this->logger->warning("Slow operation: {$operation} took {$duration}s");
    }

    return $result;
}
```

---

## Benchmarking

Establish performance baselines and track improvements.

### Benchmark Suite

Create a comprehensive benchmark suite:

```php
class PerformanceBenchmark
{
    public function runBenchmarks(): array
    {
        return [
            'workspace_creation' => $this->benchmarkWorkspaceCreation(),
            'element_addition' => $this->benchmarkElementAddition(),
            'view_creation' => $this->benchmarkViewCreation(),
            'dsl_export' => $this->benchmarkDslExport(),
            'large_workspace' => $this->benchmarkLargeWorkspace(),
        ];
    }

    private function benchmarkWorkspaceCreation(): array
    {
        $iterations = 100;
        $times = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $this->workspaceManager->createWorkspace("Benchmark $i", "Test");
            $times[] = microtime(true) - $start;
        }

        return $this->calculateStats($times);
    }

    private function calculateStats(array $times): array
    {
        sort($times);
        $count = count($times);

        return [
            'min' => min($times),
            'max' => max($times),
            'avg' => array_sum($times) / $count,
            'median' => $times[intval($count / 2)],
            'p95' => $times[intval($count * 0.95)],
            'p99' => $times[intval($count * 0.99)],
        ];
    }
}
```

### Performance Targets

| Operation | Target | Baseline |
|-----------|--------|----------|
| Create workspace | < 50ms | 20ms |
| Add element | < 10ms | 5ms |
| Create view | < 20ms | 10ms |
| Export to DSL | < 100ms | 50ms |
| List workspaces | < 30ms | 15ms |
| Large workspace (1000 elements) | < 500ms | 250ms |

### Continuous Performance Testing

Run benchmarks in CI/CD:

```bash
#!/bin/bash
# benchmark.sh

echo "Running performance benchmarks..."
php tests/Performance/benchmark.php > benchmark-results.json

# Compare with baseline
php tests/Performance/compare.php \
    benchmark-results.json \
    baseline-results.json

# Fail if performance degraded by >10%
exit $?
```

---

## Performance Tips Summary

### Do's
- ✅ Use caching aggressively with proper invalidation
- ✅ Paginate large result sets
- ✅ Build indexes for frequent lookups
- ✅ Profile memory usage and fix leaks
- ✅ Execute independent operations in parallel
- ✅ Monitor slow operations and optimize
- ✅ Benchmark regularly and track trends

### Don'ts
- ❌ Load entire large workspaces into memory
- ❌ Scan collections repeatedly (O(n) per request)
- ❌ Execute CLI commands synchronously in loops
- ❌ Keep stale caches without invalidation
- ❌ Ignore memory limits and leak warnings
- ❌ Skip performance testing in CI/CD

---

<p align="right">
  <strong>Next:</strong> <a href="extending.md">Extending the Server →</a>
</p>
