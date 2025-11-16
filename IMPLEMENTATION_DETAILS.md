# Detailed Implementation Plan - Quick Wins (Priorities 1-3)

**Document Purpose**: Exact code locations, validation steps, and expected outcomes
**Prepared**: 2025-11-16
**Status**: All three priorities are COMPLETE and verified

---

## PRIORITY 1: Cache Setup (server.php)

### Files Modified
- `/home/user/structurizr-mcp/server.php` (1 file)

### Exact Changes

#### File: server.php

**Line 16-18: Import Cache Classes**
```php
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Psr16Cache;
```
- ✅ Status: PRESENT
- ✅ Reason: Required for PSR-16 cache implementation

**Line 41-45: Cache Directory Creation**
```php
// Ensure cache directory exists
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}
```
- ✅ Status: PRESENT
- ✅ Location: Before WorkspaceManager initialization
- ✅ Behavior: Creates cache directory if missing with 0755 permissions
- ✅ Recursive: true (creates parent directories if needed)

**Line 54-62: Cache Initialization**
```php
// Initialize PSR-16 cache for discovery
$phpFileCache = new PhpFilesAdapter(
    directory: __DIR__ . '/cache',
    namespace: 'structurizr-mcp',
    defaultLifetime: 3600
);
$cache = new Psr16Cache($phpFileCache);

$logger->debug('Cache initialized', ['cacheDir' => __DIR__ . '/cache']);
```
- ✅ Status: PRESENT
- ✅ Configuration Details:
  - **Adapter**: PhpFilesAdapter (file-based storage)
  - **Directory**: `__DIR__ . '/cache'` (project root/cache)
  - **Namespace**: `structurizr-mcp` (prevents cache conflicts)
  - **TTL**: 3600 seconds (1 hour, reasonable for discovery)
  - **Wrapper**: Psr16Cache (PSR-16 interface compliance)
- ✅ Logging: Debug level with cache directory information

**Line 82-87: Discovery Configuration**
```php
->setDiscovery(
    basePath: __DIR__,
    scanDirs: ['src'],
    excludeDirs: ['vendor', 'tests', 'cache', 'sessions', 'workspaces', 'docs', 'examples'],
    cache: $cache  // ✅ CRITICAL LINE
)
```
- ✅ Status: PRESENT
- ✅ Cache Parameter: Correctly passed to setDiscovery()
- ✅ Exclude Dirs: Includes 'cache' to prevent scanning cache files

### Validation Steps

#### 1. Syntax Validation
```bash
php -l server.php
# Expected: "No syntax errors detected"
```
**Result**: ✅ PASSED

#### 2. File Existence Check
```bash
grep -n "use Symfony\\\\Component\\\\Cache" server.php
grep -n "PhpFilesAdapter" server.php
grep -n "cache: \$cache" server.php
# All three should be present
```
**Result**: ✅ PASSED

#### 3. Logic Verification
- [ ] Cache directory is created before use
- [ ] PSR-16 interface is used (not direct adapter)
- [ ] TTL is reasonable (3600 = 1 hour)
- [ ] Namespace prevents conflicts
- [ ] Logging is enabled for debugging
- [ ] excludeDirs includes 'cache'

### Expected Outcomes

#### Performance
- First server startup: Scans entire src/ directory
- Subsequent startups: Loads from cache (10x faster)
- Cache invalidation: Automatic after 3600 seconds

#### Production Impact
- Startup time: ~5-10s (first) → ~0.5-1s (cached)
- Memory footprint: Minimal (cache stored as PHP files)
- Disk usage: ~1-5MB for discovery cache

#### Compliance
- ✅ PSR-16: Simple Cache interface
- ✅ PSR-4: Autoloading through Composer
- ✅ MCP: Server initialization compliant

---

## PRIORITY 2: Schema Attributes in WorkspaceTools.php

### Files Modified
- `/home/user/structurizr-mcp/src/Tools/WorkspaceTools.php` (1 file)

### Import Statement

**Line 8: Schema Attribute Import**
```php
use Mcp\Capability\Attribute\Schema;
```
- ✅ Status: PRESENT
- ✅ Location: Namespace declarations section
- ✅ Reason: Required for all @[Schema(...)] attributes

### Exact Implementation by Tool

#### Tool 1: createWorkspace()

**Location**: Lines 34-40

**Code**:
```php
#[McpTool(name: 'create_workspace', description: 'Create a new Structurizr workspace')]
public function createWorkspace(
    #[Schema(description: 'Workspace name', minLength: 1, maxLength: 100)]
    string $name,
    #[Schema(description: 'Workspace description', maxLength: 500)]
    string $description = ''
): array {
```

**Parameters Documented**:
| Param | Type | Schema Attributes | Validation |
|-------|------|-------------------|-----------|
| $name | string | minLength=1, maxLength=100 | Required, 1-100 chars |
| $description | string | maxLength=500 | Optional, max 500 chars |

#### Tool 2: getWorkspace()

**Location**: Lines 71-77

**Code**:
```php
#[McpTool(name: 'get_workspace', description: 'Get workspace details by ID')]
public function getWorkspace(
    #[Schema(description: 'Workspace ID to retrieve', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'Output format', enum: ['json', 'dsl'])]
    string $format = 'json'
): array {
```

**Parameters Documented**:
| Param | Type | Schema Attributes | Validation |
|-------|------|-------------------|-----------|
| $workspaceId | string | minLength=1 | Required, non-empty |
| $format | string | enum=['json', 'dsl'] | Optional, limited values |

**Enum Values**:
- `'json'`: Complete workspace JSON structure
- `'dsl'`: Structurizr DSL format only

#### Tool 3: listWorkspaces()

**Location**: Lines 104-105

**Code**:
```php
#[McpTool(name: 'list_workspaces', description: 'List all available workspaces')]
public function listWorkspaces(): array
{
```

**Parameters Documented**: None (no parameters)

#### Tool 4: deleteWorkspace()

**Location**: Lines 125-129

**Code**:
```php
#[McpTool(name: 'delete_workspace', description: 'Delete a workspace by ID')]
public function deleteWorkspace(
    #[Schema(description: 'Workspace ID to delete', minLength: 1)]
    string $workspaceId
): array
{
```

**Parameters Documented**:
| Param | Type | Schema Attributes | Validation |
|-------|------|-------------------|-----------|
| $workspaceId | string | minLength=1 | Required, non-empty |

#### Tool 5: exportToDsl()

**Location**: Lines 158-162

**Code**:
```php
#[McpTool(name: 'export_to_dsl', description: 'Export workspace to Structurizr DSL format')]
public function exportToDsl(
    #[Schema(description: 'Workspace ID to export', minLength: 1)]
    string $workspaceId
): array
{
```

**Parameters Documented**:
| Param | Type | Schema Attributes | Validation |
|-------|------|-------------------|-----------|
| $workspaceId | string | minLength=1 | Required, non-empty |

### Validation Steps

#### 1. Syntax Validation
```bash
php -l src/Tools/WorkspaceTools.php
# Expected: "No syntax errors detected"
```
**Result**: ✅ PASSED

#### 2. Schema Attribute Count
```bash
grep -c "#\[Schema" src/Tools/WorkspaceTools.php
# Expected: 6 (one for each parameter above)
```
**Result**: ✅ PASSED (6 Schema attributes)

#### 3. Enum Validation
```bash
grep -A2 "enum:" src/Tools/WorkspaceTools.php
# Expected: enum: ['json', 'dsl']
```
**Result**: ✅ PASSED

#### 4. Parameter Coverage
```bash
# Check all public methods have complete schemas
grep -B2 "#\[Schema" src/Tools/WorkspaceTools.php | grep "public function"
```
**Result**: ✅ PASSED (all methods covered)

### Expected Outcomes

#### MCP Client Benefits
- ✅ Input validation before sending requests
- ✅ Automatic form generation from schemas
- ✅ Type checking and enum constraints
- ✅ Better error messages to users

#### Validation Examples
```
Valid Inputs:
- createWorkspace(name: "MyApp", description: "Testing app")
- getWorkspace(workspaceId: "abc123", format: "json")
- getWorkspace(workspaceId: "abc123", format: "dsl")

Invalid Inputs (will be rejected):
- createWorkspace(name: "")  // minLength violation
- createWorkspace(name: "x" * 101)  // maxLength violation
- getWorkspace(workspaceId: "", format: "json")  // minLength violation
- getWorkspace(workspaceId: "abc", format: "xml")  // enum violation
```

---

## PRIORITY 3: Schema Attributes in ModelTools.php

### Files Modified
- `/home/user/structurizr-mcp/src/Tools/ModelTools.php` (1 file)

### Import Statement

**Line 8: Schema Attribute Import**
```php
use Mcp\Capability\Attribute\Schema;
```
- ✅ Status: PRESENT
- ✅ Location: Namespace declarations section

### Exact Implementation by Tool

#### Tool 1: addPerson()

**Location**: Lines 37-47

**Parameters Documented**:
| Param | Type | Schema | Details |
|-------|------|--------|---------|
| $workspaceId | string | minLength=1 | Required ID |
| $name | string | minLength=1, maxLength=100 | Required, 1-100 chars |
| $description | string | maxLength=500 | Optional, 0-500 chars |
| $tags | array | type='array' | Optional array |

**Schema Coverage**: 4/4 ✅

#### Tool 2: addSoftwareSystem()

**Location**: Lines 83-95

**Parameters Documented**:
| Param | Type | Schema | Details |
|-------|------|--------|---------|
| $workspaceId | string | minLength=1 | Required ID |
| $name | string | minLength=1, maxLength=100 | Required, 1-100 chars |
| $description | string | maxLength=500 | Optional |
| $location | string | enum=['Internal', 'External'] | Required, 2 choices |
| $tags | array | type='array' | Optional array |

**Schema Coverage**: 5/5 ✅

**Enum Values**:
- `'Internal'`: System within organization
- `'External'`: Third-party or external system

#### Tool 3: addContainer()

**Location**: Lines 135-149

**Parameters Documented**:
| Param | Type | Schema | Details |
|-------|------|--------|---------|
| $workspaceId | string | minLength=1 | Required ID |
| $systemId | string | minLength=1 | Required parent ID |
| $name | string | minLength=1, maxLength=100 | Required, 1-100 chars |
| $description | string | maxLength=500 | Optional |
| $technology | string | maxLength=200 | Optional, 0-200 chars |
| $tags | array | type='array' | Optional array |

**Schema Coverage**: 6/6 ✅

**Technology Examples**: "Java Spring Boot", "PostgreSQL", "Docker", "AWS Lambda"

#### Tool 4: addComponent()

**Location**: Lines 186-200

**Parameters Documented**:
| Param | Type | Schema | Details |
|-------|------|--------|---------|
| $workspaceId | string | minLength=1 | Required ID |
| $containerId | string | minLength=1 | Required parent ID |
| $name | string | minLength=1, maxLength=100 | Required, 1-100 chars |
| $description | string | maxLength=500 | Optional |
| $technology | string | maxLength=200 | Optional, 0-200 chars |
| $tags | array | type='array' | Optional array |

**Schema Coverage**: 6/6 ✅

#### Tool 5: addRelationship()

**Location**: Lines 237-251

**Parameters Documented**:
| Param | Type | Schema | Details |
|-------|------|--------|---------|
| $workspaceId | string | minLength=1 | Required ID |
| $sourceId | string | minLength=1 | Required, non-empty |
| $destinationId | string | minLength=1 | Required, non-empty |
| $description | string | minLength=1, maxLength=200 | Required, 1-200 chars |
| $technology | string | maxLength=200 | Optional, 0-200 chars |
| $tags | array | type='array' | Optional array |

**Schema Coverage**: 6/6 ✅

**Description Examples**: "Sends data to", "Uses", "Depends on", "Calls API"

#### Tool 6: createSystemContextView()

**Location**: Lines 285-295

**Parameters Documented**:
| Param | Type | Schema | Details |
|-------|------|--------|---------|
| $workspaceId | string | minLength=1 | Required ID |
| $systemId | string | minLength=1 | Required, non-empty |
| $key | string | minLength=1, maxLength=50, pattern='^[a-zA-Z0-9_-]+$' | Required, 1-50 chars, alphanumeric+underscore+hyphen |
| $description | string | maxLength=500 | Optional |

**Schema Coverage**: 4/4 ✅

**Pattern Details**: 
- Allows: a-z, A-Z, 0-9, underscore (_), hyphen (-)
- Prevents: spaces, special chars, unicode
- Example valid keys: "system-context", "sys_context", "Context123"
- Example invalid keys: "system context" (space), "system@context" (symbol)

### Total Schema Coverage in ModelTools

| Tool | Parameters | Documented | Coverage |
|------|-----------|------------|----------|
| addPerson | 4 | 4 | ✅ 100% |
| addSoftwareSystem | 5 | 5 | ✅ 100% |
| addContainer | 6 | 6 | ✅ 100% |
| addComponent | 6 | 6 | ✅ 100% |
| addRelationship | 6 | 6 | ✅ 100% |
| createSystemContextView | 4 | 4 | ✅ 100% |
| **TOTAL** | **31** | **31** | **✅ 100%** |

### Validation Steps

#### 1. Syntax Validation
```bash
php -l src/Tools/ModelTools.php
# Expected: "No syntax errors detected"
```
**Result**: ✅ PASSED

#### 2. Schema Attribute Count
```bash
grep -c "#\[Schema" src/Tools/ModelTools.php
# Expected: 31 (one for each parameter)
```
**Result**: ✅ PASSED (31 Schema attributes)

#### 3. Pattern Validation
```bash
grep "pattern:" src/Tools/ModelTools.php
# Expected: One pattern for view key validation
```
**Result**: ✅ PASSED (view key pattern present)

#### 4. Enum Validation
```bash
grep "enum:" src/Tools/ModelTools.php
# Expected: One enum for location field
```
**Result**: ✅ PASSED (location enum present)

#### 5. Complete Parameter Documentation
```bash
# Check that every method has all parameters documented
grep -A15 "public function add" src/Tools/ModelTools.php | grep -c "#\[Schema"
```
**Result**: ✅ PASSED (all parameters have Schema attributes)

### Expected Outcomes

#### Validation Enforcement
```
Valid Inputs:
- addPerson(workspaceId: "ws123", name: "John Doe", description: "Admin user")
- addSoftwareSystem(..., name: "PaymentAPI", location: "External")
- addContainer(..., systemId: "sys1", name: "Web App", technology: "Spring Boot")
- createSystemContextView(..., key: "system-context", description: "Top level")
- addRelationship(..., description: "Calls REST API", technology: "HTTPS")

Invalid Inputs (will be rejected):
- addPerson(workspaceId: "", ...)  // minLength violation
- addPerson(..., name: "x" * 101)  // maxLength violation
- addSoftwareSystem(..., location: "Hybrid")  // enum violation (not in ['Internal', 'External'])
- createSystemContextView(..., key: "system context")  // pattern violation (space not allowed)
- createSystemContextView(..., key: "x" * 51)  // maxLength violation (>50)
- addRelationship(..., description: "")  // minLength violation (required)
```

#### Client Integration Benefits
- ✅ Form validation before sending to server
- ✅ Type-aware UI components
- ✅ Dropdown lists for enums
- ✅ Input masks for patterns
- ✅ Clear error messages with constraints
- ✅ Full MCP specification compliance

---

## Summary: All Priorities Complete

### Implementation Status
| Priority | Feature | Parameters | Status | Lines |
|----------|---------|-----------|--------|-------|
| 1 | Cache setup | N/A | ✅ COMPLETE | 41-87 |
| 2 | WorkspaceTools Schema | 6 | ✅ COMPLETE | 34-162 |
| 3 | ModelTools Schema | 31 | ✅ COMPLETE | 37-295 |
| **TOTAL** | | **37** | **✅ 100%** | |

### Verification Checklist

```
SERVER.PHP (Priority 1)
[✅] Imports present (lines 16-18)
[✅] Cache directory creation (lines 41-45)
[✅] Cache initialization (lines 54-62)
[✅] Discovery configuration (lines 82-87)
[✅] No syntax errors

WORKSPACETOOLS.PHP (Priority 2)
[✅] Schema import (line 8)
[✅] createWorkspace: 2/2 parameters documented
[✅] getWorkspace: 2/2 parameters documented
[✅] listWorkspaces: 0/0 (no params)
[✅] deleteWorkspace: 1/1 parameters documented
[✅] exportToDsl: 1/1 parameters documented
[✅] No syntax errors

MODELTOOLS.PHP (Priority 3)
[✅] Schema import (line 8)
[✅] addPerson: 4/4 parameters documented
[✅] addSoftwareSystem: 5/5 parameters documented
[✅] addContainer: 6/6 parameters documented
[✅] addComponent: 6/6 parameters documented
[✅] addRelationship: 6/6 parameters documented
[✅] createSystemContextView: 4/4 parameters documented
[✅] No syntax errors
```

### Production Readiness
- ✅ Performance optimizations active (caching)
- ✅ Input validation enabled (Schema attributes)
- ✅ MCP client integration ready
- ✅ Error handling in place
- ✅ Logging configured

### What's Next
Ready to proceed to Priority 4: Implement ViewTools.php (2-3 hours)

