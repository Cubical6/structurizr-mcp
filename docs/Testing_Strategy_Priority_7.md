# Priority 7: Comprehensive Test Suite Strategy

## Current Status
- **Test Coverage**: 0% (no tests directory)
- **PHPUnit**: ✅ Configured (phpunit.xml exists)
- **PHPStan**: ✅ Configured (phpstan.neon at level 8)
- **Dependencies**: ✅ Installed (phpunit/phpunit ^10.0)
- **Autoload**: ✅ Configured (StructurizrMcp\Tests\ → tests/)

## Strategic Test Plan Overview

### Testing Pyramid
```
                    E2E Tests (5%)
                   [MCP Protocol]
                  
            Integration Tests (20%)
           [Tool Workflows, APIs]
           
    Unit Tests (75%)
   [Classes, Methods]
```

### Coverage Goals
- **Minimum**: 75% overall code coverage
- **Core Classes**: >90% (WorkspaceManager, DslBuilder, Tools)
- **Infrastructure**: >85% (Exception handlers, Configuration)
- **Target**: 85-90% across all modules

---

## Part 1: Test Directory Structure

### Directory Layout
```
tests/
├── Unit/
│   ├── Structurizr/
│   │   ├── WorkspaceManagerTest.php        (core storage/retrieval)
│   │   ├── DslBuilderTest.php              (DSL generation)
│   │   └── WorkspaceTest.php               (value object)
│   ├── Tools/
│   │   ├── WorkspaceToolsTest.php          (5 CRUD tools)
│   │   ├── ModelToolsTest.php              (7 model tools)
│   │   └── ViewToolsTest.php               (4 view tools - when implemented)
│   └── Exception/
│       └── ExceptionTest.php               (custom exceptions)
├── Integration/
│   ├── WorkspaceWorkflowTest.php           (Create→Add→View→Export)
│   ├── ToolsIntegrationTest.php            (All tools together)
│   ├── MCP/
│   │   └── ProtocolComplianceTest.php      (MCP spec validation)
│   └── Fixtures/
│       ├── sample-workspace.dsl
│       ├── sample-workspace.json
│       └── example-c4-model.json
└── bootstrap.php                           (test setup)
```

### Create Structure Command
```bash
mkdir -p tests/{Unit,Integration}/{Structurizr,Tools,Exception,Fixtures,MCP}
touch tests/bootstrap.php
```

---

## Part 2: Unit Tests - WorkspaceManager

### Test Class: `tests/Unit/Structurizr/WorkspaceManagerTest.php`

**Methods to Test**: 6 core public methods
1. `create(name, description)` - Create new workspace
2. `load(id)` - Load existing workspace  
3. `save(workspace)` - Persist workspace
4. `delete(id)` - Remove workspace
5. `list()` - List all workspaces
6. `exists(id)` - Check workspace existence

### Unit Test Cases (18 tests)

#### Creation Tests (3 tests)
```php
public function testCreateWorkspaceSuccessfully()
  - Assert: Returns Workspace object with UUID id
  - Assert: File created in storage path
  - Assert: JSON valid and parseable
  
public function testCreateGeneratesUniqueIds()
  - Create 10 workspaces
  - Assert: All IDs are unique (no collision)
  
public function testCreateWithEmptyDescription()
  - Create with description = ''
  - Assert: Loads correctly, description empty
```

#### Loading Tests (4 tests)
```php
public function testLoadExistingWorkspace()
  - Create, save, load
  - Assert: Loaded equals original
  
public function testLoadNonExistentWorkspace()
  - Assert: Throws WorkspaceNotFoundException
  - Assert: Contains workspace ID in message
  
public function testLoadMalformedJson()
  - Corrupt JSON file
  - Assert: Throws exception, doesn't crash
  
public function testLoadWithMissingFields()
  - Missing required fields in JSON
  - Assert: Throws exception or uses defaults
```

#### Persistence Tests (4 tests)
```php
public function testSaveCreatesJsonFile()
  - Assert: File created in storage path
  - Assert: File is valid JSON
  
public function testSaveUpdatesModifiedTime()
  - Create, wait, modify, save
  - Assert: updatedAt changes
  
public function testSaveWithSpecialCharacters()
  - Name with émojis, quotes, etc.
  - Assert: JSON encodes correctly
  
public function testSaveMaintainsDataIntegrity()
  - Save→Load→Verify all fields match
```

#### Deletion Tests (3 tests)
```php
public function testDeleteRemovesFile()
  - Create, delete
  - Assert: File doesn't exist
  
public function testDeleteNonExistentWorkspace()
  - Assert: Throws exception
  - Assert: No side effects
  
public function testDeleteWithDslAndViews()
  - Complex workspace, delete
  - Assert: Completely removed
```

#### Listing Tests (2 tests)
```php
public function testListMultipleWorkspaces()
  - Create 3 workspaces
  - Assert: list() returns all 3
  - Assert: Each has id, name, description
  
public function testListEmptyStorage()
  - With empty storage directory
  - Assert: Returns empty array, no errors
```

#### Edge Cases (2 tests)
```php
public function testStorageDirectoryCreated()
  - Non-existent path on construct
  - Assert: Directory created
  
public function testPathTraversalPrevention()
  - Try ../../../ in workspace ID
  - Assert: Safely handled
```

### Test Fixtures
```php
// tests/Unit/Structurizr/WorkspaceManagerTest.php
private string $tempDir;

protected function setUp(): void {
    $this->tempDir = sys_get_temp_dir() . '/structurizr-tests-' . uniqid();
    mkdir($this->tempDir);
    $this->manager = new WorkspaceManager($this->tempDir, $this->logger);
}

protected function tearDown(): void {
    // Clean up temp files
}
```

---

## Part 3: Unit Tests - DslBuilder

### Test Class: `tests/Unit/Structurizr/DslBuilderTest.php`

**Methods to Test**: 13 core methods
1. `workspace()` - Set workspace name/description
2. `addPerson()` - Add person element
3. `addSoftwareSystem()` - Add system element
4. `addContainer()` - Add container (with validation)
5. `addComponent()` - Add component (with validation)
6. `addRelationship()` - Add relationship (with validation)
7. `addSystemContextView()` - Create view
8. `addContainerView()` - Create view
9. `addComponentView()` - Create view
10. `toDsl()` - Generate DSL string
11. `toArray()` - Export as array
12. `getElement()` - Retrieve element
13. `findElement()` - Search by name

### Unit Test Cases (35 tests)

#### Workspace Setup (2 tests)
```php
public function testWorkspaceConfiguration()
  - workspace('Test', 'Description')
  - Assert: Stored internally
  
public function testWorkspaceGeneratesDsl()
  - workspace() called
  - Assert: toDsl() contains workspace declaration
```

#### Element Creation (6 tests)
```php
public function testAddPersonCreatesElement()
  - addPerson('User', 'desc')
  - Assert: Returns ID, element stored
  
public function testAddPersonWithTags()
  - addPerson() with tags array
  - Assert: Tags preserved in output
  
public function testAddSoftwareSystem()
  - addSoftwareSystem('System', 'desc')
  - Assert: Returns ID, type correct
  
public function testSoftwareSystemLocation()
  - location='External'
  - Assert: Correctly marked in DSL
  
public function testAddContainer()
  - addContainer(systemId, 'Name')
  - Assert: Returns ID, parent tracked
  
public function testAddComponent()
  - addComponent(containerId, 'Name')
  - Assert: Returns ID, parent tracked
```

#### Validation Tests (6 tests)
```php
public function testAddContainerInvalidParent()
  - addContainer('nonexistent', 'name')
  - Assert: Throws InvalidArgumentException
  
public function testAddComponentInvalidParent()
  - addComponent('notacontainer', 'name')
  - Assert: Throws InvalidArgumentException
  
public function testAddRelationshipInvalidSource()
  - addRelationship('nonexistent', id, 'desc')
  - Assert: Throws InvalidArgumentException
  
public function testAddRelationshipInvalidDestination()
  - addRelationship(id, 'nonexistent', 'desc')
  - Assert: Throws exception
  
public function testDuplicateElementIds()
  - Attempt to create elements with same ID
  - Assert: All IDs unique
  
public function testDeepNestingValidation()
  - Create person→system→container→component
  - Assert: All relationships valid
```

#### DSL Generation (8 tests)
```php
public function testGenerateBasicDsl()
  - workspace, person, system
  - Assert: Valid DSL syntax
  
public function testDslIndentation()
  - Check DSL formatting
  - Assert: Proper nesting indentation
  
public function testDslWithAllElements()
  - Add all element types
  - Assert: DSL contains all
  
public function testDslRelationships()
  - addRelationship called
  - Assert: "→" syntax in DSL
  
public function testDslViews()
  - addSystemContextView()
  - Assert: "systemContext" block in DSL
  
public function testDslSpecialCharacterHandling()
  - Names with quotes, newlines
  - Assert: Properly escaped
  
public function testDslEmptyElements()
  - No elements added
  - Assert: Valid but empty workspace DSL
  
public function testDslLargeWorkspace()
  - 100+ elements
  - Assert: DSL generated, valid structure
```

#### Array Export (5 tests)
```php
public function testToArrayStructure()
  - toArray()
  - Assert: Returns proper nested structure
  
public function testArrayContainsAllElements()
  - Assert: All added elements present
  
public function testArrayRelationships()
  - Assert: Relationships array correct
  
public function testArrayViews()
  - Assert: Views section populated
  
public function testArrayIsSerializable()
  - json_encode(toArray())
  - Assert: No errors
```

#### Element Retrieval (4 tests)
```php
public function testGetExistingElement()
  - getElement('id')
  - Assert: Returns element data
  
public function testGetNonExistentElement()
  - getElement('invalid')
  - Assert: Returns null or throws
  
public function testFindElementByName()
  - findElement('Person Name')
  - Assert: Returns matching element
  
public function testFindElementByType()
  - findElement('name', 'person')
  - Assert: Type-specific search works
```

#### Immutability Tests (2 tests)
```php
public function testBuilderNotMutated()
  - Multiple calls to toDsl()
  - Assert: Same output each time
  
public function testArraysNotShared()
  - Modify returned array
  - Assert: Doesn't affect builder state
```

### Test Strategy
```php
class DslBuilderTest extends TestCase {
    private DslBuilder $builder;
    
    protected function setUp(): void {
        $this->builder = new DslBuilder();
        $this->builder->workspace('Test Workspace', 'For testing');
    }
    
    // Use helper methods for common patterns
    private function createTestHierarchy(): array {
        $person = $this->builder->addPerson('User', 'Test user');
        $system = $this->builder->addSoftwareSystem('System', 'Test system');
        $container = $this->builder->addContainer($system, 'App', 'Web app');
        $component = $this->builder->addComponent($container, 'Controller', 'MVC');
        return compact('person', 'system', 'container', 'component');
    }
}
```

---

## Part 4: Unit Tests - Tools

### Test Structure

#### WorkspaceTools Tests (5 tests per method × 5 methods = 25 tests)

**File**: `tests/Unit/Tools/WorkspaceToolsTest.php`

Methods:
1. `createWorkspace($name, $description)` - Create tool
2. `getWorkspace($id, $format)` - Retrieve tool
3. `listWorkspaces()` - List tool
4. `deleteWorkspace($id)` - Delete tool
5. `exportToDsl($id)` - Export tool

Test Cases for Each:
```php
public function testCreateValid()        // Happy path
public function testCreateEmptyName()    // Validation
public function testCreateNameTooLong()  // Boundary
public function testCreateError()        // Exception
public function testCreateLogging()      // Side effects

// Similar 5 tests for each method
```

#### ModelTools Tests (6 tests per method × 7 methods = 42 tests)

**File**: `tests/Unit/Tools/ModelToolsTest.php`

Methods:
1. `addPerson()` - Add person
2. `addSoftwareSystem()` - Add system
3. `addContainer()` - Add container
4. `addComponent()` - Add component
5. `addRelationship()` - Add relationship
6. `createSystemContextView()` - Create view
7. Additional helpers

Each with: valid input, missing workspace, invalid parent, validation errors, success cases

#### ViewTools Tests (Expected, for when implemented)

**File**: `tests/Unit/Tools/ViewToolsTest.php`

- 3 view creation tools
- Apply auto layout
- Schema validation
- 20-25 tests total

### Schema Attribute Validation Strategy

#### Testing Schema Attributes

```php
class SchemaValidationTest extends TestCase {
    /**
     * Test that all tool methods have Schema attributes
     */
    public function testAllToolParametersHaveSchema(): void {
        $reflection = new ReflectionClass(WorkspaceTools::class);
        foreach ($reflection->getMethods() as $method) {
            if (!str_starts_with($method->getName(), 'create') 
                && !str_starts_with($method->getName(), 'add')
                && !str_starts_with($method->getName(), 'get')
                && !str_starts_with($method->getName(), 'list')
                && !str_starts_with($method->getName(), 'delete')
                && !str_starts_with($method->getName(), 'export')) {
                continue;
            }
            
            foreach ($method->getParameters() as $param) {
                // Check for #[Schema] attributes
                $attributes = $param->getAttributes();
                $hasSchema = false;
                
                foreach ($attributes as $attr) {
                    if ($attr->getName() === Schema::class) {
                        $hasSchema = true;
                        break;
                    }
                }
                
                $this->assertTrue(
                    $hasSchema,
                    "Parameter {$param->getName()} in {$method->getName()} missing Schema attribute"
                );
            }
        }
    }
    
    /**
     * Test Schema enum values
     */
    public function testLocationEnumSchema(): void {
        $reflection = new ReflectionClass(ModelTools::class);
        $method = $reflection->getMethod('addSoftwareSystem');
        $locationParam = null;
        
        foreach ($method->getParameters() as $param) {
            if ($param->getName() === 'location') {
                $locationParam = $param;
                break;
            }
        }
        
        $this->assertNotNull($locationParam);
        $attributes = $locationParam->getAttributes();
        
        $schemaAttr = null;
        foreach ($attributes as $attr) {
            if ($attr->getName() === Schema::class) {
                $schemaAttr = $attr->newInstance();
                break;
            }
        }
        
        $this->assertNotNull($schemaAttr);
        // Verify enum contains ['Internal', 'External']
    }
}
```

---

## Part 5: Integration Tests

### Test Structure

#### Workflow Integration Tests

**File**: `tests/Integration/WorkspaceWorkflowTest.php`

Test Complete Workflows (End-to-End within single test class):

```php
public function testCompleteWorkflowCreateToExport(): void {
    // 1. Create workspace via tool
    $workspaceResult = $this->tool->createWorkspace('E-Commerce', 'Online store');
    $workspaceId = $workspaceResult['workspaceId'];
    
    // 2. Add elements via tools
    $userResult = $this->tool->addPerson($workspaceId, 'Customer', '');
    $systemResult = $this->tool->addSoftwareSystem($workspaceId, 'E-Commerce System', '');
    
    // 3. Add container
    $containerResult = $this->tool->addContainer(
        $workspaceId,
        $systemResult['elementId'],
        'Web App',
        'React frontend'
    );
    
    // 4. Add relationship
    $relResult = $this->tool->addRelationship(
        $workspaceId,
        $userResult['elementId'],
        $systemResult['elementId'],
        'Uses',
        'HTTPS'
    );
    
    // 5. Create view
    $viewResult = $this->tool->createSystemContextView(
        $workspaceId,
        $systemResult['elementId'],
        'system-context'
    );
    
    // 6. Export to DSL
    $dslResult = $this->tool->exportToDsl($workspaceId);
    
    // Assertions throughout
    $this->assertNotEmpty($workspaceId);
    $this->assertArrayHasKey('dsl', $dslResult);
    $this->assertStringContainsString('workspace', $dslResult['dsl']);
}

public function testCompleteWorkflowImportAndModify(): void {
    // 1. Import DSL from fixture
    $dsl = file_get_contents(__DIR__ . '/../Fixtures/ecommerce.dsl');
    $result = $this->tool->importFromDsl($dsl);
    $workspaceId = $result['workspaceId'];
    
    // 2. Add elements to imported workspace
    $newElement = $this->tool->addPerson($workspaceId, 'Admin', 'Administrator');
    
    // 3. Export modified version
    $modified = $this->tool->exportToDsl($workspaceId);
    
    // Assertions
    $this->assertStringContainsString('Admin', $modified['dsl']);
}

public function testCompleteWorkflowValidateAndExportFormats(): void {
    // Create workspace
    $workspaceId = $this->createCompleteWorkspace();
    
    // Validate (once CliWrapper implemented)
    $valid = $this->tool->validateWorkspace($workspaceId);
    $this->assertTrue($valid['valid']);
    
    // Export to multiple formats
    $plantuml = $this->tool->exportToPlantUml($workspaceId);
    $mermaid = $this->tool->exportToMermaid($workspaceId);
    
    $this->assertStringContainsString('!define', $plantuml['plantuml']);
    $this->assertStringContainsString('graph', $mermaid['mermaid']);
}
```

#### Tools Integration Tests

**File**: `tests/Integration/ToolsIntegrationTest.php`

Test all tools work together:

```php
public function testAllToolsIntegration(): void {
    // Test each tool in isolation, then in combination
    
    // WorkspaceTools
    $workspace = $this->tools->createWorkspace('Test', 'Desc');
    $id = $workspace['workspaceId'];
    
    $list = $this->tools->listWorkspaces();
    $this->assertGreaterThan(0, $list['count']);
    
    // ModelTools
    $person = $this->tools->addPerson($id, 'User', '');
    $system = $this->tools->addSoftwareSystem($id, 'System', '');
    
    $get = $this->tools->getWorkspace($id);
    $this->assertArrayHasKey('model', $get);
    
    // Cleanup
    $delete = $this->tools->deleteWorkspace($id);
    $this->assertTrue($delete['success']);
}

public function testToolErrorHandling(): void {
    // Invalid workspace ID
    $this->expectException(WorkspaceNotFoundException::class);
    $this->tools->getWorkspace('invalid-id');
}

public function testToolDataConsistency(): void {
    // Create via tool
    $workspace = $this->tools->createWorkspace('Test', '');
    $id = $workspace['workspaceId'];
    
    // Add elements
    $person = $this->tools->addPerson($id, 'User', 'A user');
    
    // Retrieve and verify
    $get = $this->tools->getWorkspace($id, 'json');
    
    // Person should be in model
    $found = false;
    foreach ($get['model']['people'] ?? [] as $p) {
        if ($p['name'] === 'User') {
            $found = true;
            break;
        }
    }
    
    $this->assertTrue($found);
}
```

#### MCP Protocol Compliance Tests

**File**: `tests/Integration/MCP/ProtocolComplianceTest.php`

```php
public function testToolDiscoveryFormat(): void {
    // Verify tools are discoverable via MCP protocol
    $tools = /* Get from server discovery */;
    
    foreach ($tools as $tool) {
        $this->assertArrayHasKey('name', $tool);
        $this->assertArrayHasKey('description', $tool);
        $this->assertArrayHasKey('inputSchema', $tool);
    }
}

public function testToolInputSchemaValidation(): void {
    // Every tool must have schema
    $tools = /* Get tools */;
    
    foreach ($tools as $tool) {
        $schema = $tool['inputSchema'];
        $this->assertArrayHasKey('type', $schema);
        $this->assertArrayHasKey('properties', $schema);
    }
}

public function testRequiredParametersMarked(): void {
    // Verify required parameters
    $tools = /* Get tools */;
    
    $createWorkspaceTool = array_filter(
        $tools,
        fn($t) => $t['name'] === 'create_workspace'
    )[0] ?? null;
    
    $this->assertNotNull($createWorkspaceTool);
    $required = $createWorkspaceTool['inputSchema']['required'] ?? [];
    $this->assertContains('name', $required);
}
```

### Fixture Files

**Location**: `tests/Integration/Fixtures/`

1. **ecommerce-example.dsl** - Complete e-commerce workspace DSL
2. **microservices-example.dsl** - Microservices architecture DSL
3. **invalid.dsl** - Malformed DSL for error testing
4. **sample-workspace.json** - Pre-built workspace JSON
5. **expected-plantuml.txt** - Expected PlantUML output for validation

---

## Part 6: PHPUnit Configuration Review

### Current phpunit.xml ✅ Good, but can be enhanced:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnWarning="true"
         failOnRisky="true"
         beStrictAboutOutputDuringTests="true"
         cacheDirectory=".phpunit.cache">
    
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>

    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>

    <!-- ENHANCED COVERAGE REPORTING -->
    <coverage>
        <report>
            <html outputDirectory="coverage/html"/>
            <text outputFile="php://stdout" showUncoveredFiles="true"/>
        </report>
        <!-- ADD: Threshold for minimum coverage -->
        <report>
            <clover outputFile="coverage/clover.xml"/>
        </report>
    </coverage>
</phpunit>
```

### Recommended Enhancements:

```xml
<!-- Add after <coverage> section -->
<phpIni>
    <ini name="display_errors" value="On"/>
    <ini name="error_reporting" value="-1"/>
    <ini name="memory_limit" value="512M"/>
</phpIni>

<!-- Add bootstrap file -->
<!-- Create tests/bootstrap.php -->
```

**New tests/bootstrap.php**:
```php
<?php
declare(strict_types=1);

// Define test constants
define('TEST_ROOT_DIR', __DIR__);
define('TEMP_DIR', sys_get_temp_dir() . '/structurizr-tests');

// Create temp directory
if (!is_dir(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0755, true);
}

// Register test autoloader
$autoloader = require __DIR__ . '/../vendor/autoload.php';
$autoloader->addPsr4('StructurizrMcp\\Tests\\', __DIR__);
```

---

## Part 7: Test Validation Strategy for Each Priority

### Priority 1: Cache Setup
**Validation Tests**:
```php
public function testCacheInitializedInServer(): void {
    // Run server startup
    // Assert: Cache directory exists
    // Assert: PhpFilesAdapter initialized
    // Assert: Discovery uses cache
}

public function testCachePersistsBetweenRuns(): void {
    // Run server, add data
    // Restart server
    // Assert: Data loaded from cache
}

public function testCacheInvalidatesOnCodeChange(): void {
    // Modify source files
    // Assert: Cache properly invalidates
}
```

### Priorities 2-3: Schema Attributes
**Validation Tests**:
```php
public function testWorkspaceToolsSchemaAttributes(): void {
    // Reflection test: all parameters have #[Schema]
    // Test each schema constraint works
}

public function testModelToolsSchemaAttributes(): void {
    // 31 parameters checked
    // Enum values validated
    // minLength/maxLength tested
}

// These tests verify the fixes before integration
```

### Priority 4: ViewTools Implementation
**Validation Tests**:
```php
public function testViewToolsClassExists(): void {
    $this->assertTrue(class_exists(ViewTools::class));
}

public function testViewToolsDiscovered(): void {
    // Check all 4 tools appear in MCP discovery
}

public function testViewToolsWorkflow(): void {
    // Create system → Create all 3 view types → Verify DSL
}
```

### Priority 5: CliWrapper Implementation
**Validation Tests**:
```php
public function testCliWrapperExecutesValidation(): void {
    // Create CliWrapper
    // Call validate() with test DSL
    // Assert: Returns ValidationResult with valid flag
}

public function testCliWrapperExportsFormats(): void {
    // Test export to each format
    // Verify output format correct
}

public function testCliWrapperSecurityPrevention(): void {
    // Test path traversal prevention
    // Test command injection prevention
    // Test credential masking in logs
}
```

### Priority 6: ExportTools Implementation
**Validation Tests**:
```php
public function testExportToolsCreateAndDiscover(): void {
    // All 3 export methods discoverable
}

public function testExportOutputFormatting(): void {
    // PlantUML output valid
    // Mermaid output valid
    // JSON export structured
}

public function testImportFromDslWorks(): void {
    // Parse DSL string
    // Create workspace
    // Verify elements restored
}
```

### Priority 7: Test Suite Completeness
**Validation Tests**:
```php
public function testCoverageThreshold(): void {
    // Run: ./vendor/bin/phpunit --coverage-text
    // Assert: Overall coverage ≥75%
    // Assert: Core classes ≥90%
}

public function testAllTestsPass(): void {
    // ./vendor/bin/phpunit
    // Assert: All tests pass
    // Assert: No warnings
}

public function testPhpStanLevel8Pass(): void {
    // ./vendor/bin/phpstan analyse src --level=8
    // Assert: No errors
}
```

---

## Part 8: Test Execution Plan

### Running Tests

**Single command**:
```bash
./vendor/bin/phpunit
```

**By testsuite**:
```bash
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
```

**With coverage**:
```bash
./vendor/bin/phpunit --coverage-html coverage
```

**Specific file**:
```bash
./vendor/bin/phpunit tests/Unit/Structurizr/WorkspaceManagerTest.php
```

### CI/CD Integration

Add to `composer.json` scripts:
```json
"test": "phpunit",
"test:unit": "phpunit --testsuite Unit",
"test:integration": "phpunit --testsuite Integration",
"test:coverage": "phpunit --coverage-html coverage --coverage-text",
"test:quick": "phpunit --no-coverage",
"stan": "phpstan analyse src --level=8",
"cs-fix": "php-cs-fixer fix src"
```

### Pre-commit Hook (Recommended)

```bash
#!/bin/bash
# .git/hooks/pre-commit
./vendor/bin/phpunit --no-coverage
./vendor/bin/phpstan analyse src --level=8
exit $?
```

---

## Part 9: Test Metrics & Monitoring

### Coverage Targets

| Module | Target | Method |
|--------|--------|--------|
| src/Structurizr/ | 90% | Line coverage |
| src/Tools/ | 85% | Line coverage |
| src/Exception/ | 90% | Branch coverage |
| Overall | 75% | Line coverage |

### Key Metrics to Track

1. **Code Coverage**: Line, Branch, Method
2. **Test Execution Time**: Should be <10s
3. **Test Count**: 100+ tests total
4. **Failing Tests**: 0 (strict enforcement)
5. **Warnings**: 0 (PHPUnit strict mode)

### Coverage Report Interpretation

```
Project Coverage
================
Line Coverage: 85%       ✅ PASS (target: 75%)
Branch Coverage: 78%     ✅ PASS (target: 70%)
Method Coverage: 92%     ✅ PASS (target: 80%)

Low Coverage Areas:
- Exception handling: 65% (need more error cases)
- Edge cases: 72% (need boundary tests)
```

---

## Part 10: Test Maintenance Strategy

### As Code Evolves

1. **New Methods**: Add tests immediately
2. **Bug Fixes**: Add regression test first
3. **Refactoring**: Keep test count stable
4. **Dependencies**: Update mocks when dependencies change

### Annual Review

- Review test effectiveness
- Remove dead tests
- Consolidate similar tests
- Update fixtures

---

## Quick Start Summary

### Estimated Effort Breakdown

| Phase | Files | Tests | Hours | Priority |
|-------|-------|-------|-------|----------|
| Setup | 3 | 0 | 0.5 | 🔴 |
| WorkspaceManager | 1 | 18 | 3-4 | 🔴 |
| DslBuilder | 1 | 35 | 4-5 | 🔴 |
| Tools Unit | 2 | 60 | 5-6 | 🟡 |
| Integration | 2 | 25 | 4-5 | 🟡 |
| Schema Validation | 1 | 15 | 2-3 | 🔴 |
| **Total** | **10** | **153** | **19-24** | |

### Day-by-Day Breakdown

**Day 1 (6 hours)**:
- Create test structure
- WorkspaceManager unit tests (18 tests)
- PHPUnit verification

**Day 2-3 (12 hours)**:
- DslBuilder unit tests (35 tests)
- Tools unit tests (60 tests)
- Schema validation tests

**Day 4-5 (6 hours)**:
- Integration tests (25 tests)
- Workflow tests
- Coverage reporting

**Result**: ~153 tests, 75%+ coverage, production-ready test suite

