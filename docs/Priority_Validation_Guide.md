# Priority Validation Guide - Testing Each Priority After Completion

This guide shows HOW to validate each priority has been properly implemented using both manual testing and automated tests.

---

## Priority 1: Cache Setup Validation

### What was changed?
- Added cache import statements
- Added cache directory creation
- Initialized PhpFilesAdapter with Psr16Cache
- Updated setDiscovery() with cache parameter

### Validation Steps

#### Step 1: Check File Modifications
```bash
git diff server.php | grep -E "^[\+\-]" | head -20
```

Expected: Should see additions for cache imports and initialization

#### Step 2: Syntax Verification
```bash
php -l server.php
```

Expected: `No syntax errors detected`

#### Step 3: Run Server and Monitor Cache
```bash
# Terminal 1: Watch cache directory
watch -n 1 'ls -lah cache/'

# Terminal 2: Start server
php server.php < /dev/null

# Verify: cache/structurizr-mcp should contain .php files
ls -la cache/
```

Expected: Cache directory has files after server starts

#### Step 4: Performance Test (Optional)
```bash
# Time first run (populates cache)
time php server.php < /dev/null

# Time second run (uses cache)
time php server.php < /dev/null
```

Expected: Second run significantly faster

#### Step 5: Automated Test
```php
// tests/Integration/CacheInitializationTest.php
public function testCacheDirectoryCreated(): void {
    $cacheDir = __DIR__ . '/../../cache';
    $this->assertTrue(is_dir($cacheDir), 'Cache directory does not exist');
    
    // List cache files
    $files = scandir($cacheDir);
    $phpFiles = array_filter($files, fn($f) => str_ends_with($f, '.php'));
    
    $this->assertGreaterThan(0, count($phpFiles), 'No cache files found');
}
```

Run:
```bash
./vendor/bin/phpunit tests/Integration/CacheInitializationTest.php
```

---

## Priority 2: Schema Attributes on WorkspaceTools

### What was changed?
- Added Schema import to WorkspaceTools
- Added #[Schema(...)] attributes to 6 parameters across 5 methods
- createWorkspace: $name, $description
- getWorkspace: $workspaceId, $format
- deleteWorkspace: $workspaceId
- exportToDsl: $workspaceId

### Validation Steps

#### Step 1: Visual Code Review
```bash
grep -n "#\[Schema" src/Tools/WorkspaceTools.php
```

Expected: 6 matches (one per parameter that needed it)

#### Step 2: Syntax Check
```bash
php -l src/Tools/WorkspaceTools.php
```

Expected: `No syntax errors detected`

#### Step 3: PHPStan Analysis
```bash
./vendor/bin/phpstan analyse src/Tools/WorkspaceTools.php
```

Expected: `No errors`

#### Step 4: Reflection Test - Verify All Parameters Have Schema
```php
// tests/Unit/Tools/WorkspaceToolsSchemaTest.php
use StructurizrMcp\Tools\WorkspaceTools;
use Mcp\Capability\Attribute\Schema;

public function testAllWorkspaceToolParametersHaveSchema(): void {
    $reflection = new ReflectionClass(WorkspaceTools::class);
    
    $toolMethods = [
        'createWorkspace',
        'getWorkspace',
        'listWorkspaces',
        'deleteWorkspace',
        'exportToDsl'
    ];
    
    foreach ($toolMethods as $methodName) {
        $method = $reflection->getMethod($methodName);
        
        foreach ($method->getParameters() as $param) {
            $attributes = $param->getAttributes(Schema::class);
            
            $this->assertNotEmpty(
                $attributes,
                "Parameter '{$param->getName()}' in '{$methodName}' missing #[Schema] attribute"
            );
        }
    }
}

public function testFormatEnumSchema(): void {
    // getWorkspace should have enum for format
    $reflection = new ReflectionClass(WorkspaceTools::class);
    $method = $reflection->getMethod('getWorkspace');
    
    foreach ($method->getParameters() as $param) {
        if ($param->getName() === 'format') {
            $attributes = $param->getAttributes(Schema::class);
            $this->assertNotEmpty($attributes);
            
            // Could also verify enum values if needed
            $schema = $attributes[0]->newInstance();
            // Additional assertions on schema properties
        }
    }
}
```

Run:
```bash
./vendor/bin/phpunit tests/Unit/Tools/WorkspaceToolsSchemaTest.php
```

#### Step 5: MCP Server Integration Test
```php
// Verify server can still start and discover tools
public function testServerStartsWithSchemaAttrs(): void {
    // This would be an integration test that:
    // 1. Starts the server
    // 2. Calls initialize on MCP
    // 3. Verifies tools are discovered with proper schema
}
```

---

## Priority 3: Schema Attributes on ModelTools

### What was changed?
- Added Schema import to ModelTools
- Added #[Schema(...)] attributes to 31 parameters across 7 methods:
  - addPerson: 4 params
  - addSoftwareSystem: 5 params
  - addContainer: 6 params
  - addComponent: 6 params
  - addRelationship: 6 params
  - createSystemContextView: 4 params

### Validation Steps

#### Step 1: Count Schema Attributes
```bash
grep -c "#\[Schema" src/Tools/ModelTools.php
```

Expected: `31` (exactly 31 schema attributes)

#### Step 2: Syntax and Analysis
```bash
php -l src/Tools/ModelTools.php && \
./vendor/bin/phpstan analyse src/Tools/ModelTools.php
```

Expected: No errors on both

#### Step 3: Reflection Test - All Parameters Have Schema
```php
// tests/Unit/Tools/ModelToolsSchemaTest.php
public function testAllModelToolParametersHaveSchema(): void {
    $reflection = new ReflectionClass(ModelTools::class);
    
    $toolMethods = [
        'addPerson',
        'addSoftwareSystem',
        'addContainer',
        'addComponent',
        'addRelationship',
        'createSystemContextView'
    ];
    
    $parameterCount = 0;
    foreach ($toolMethods as $methodName) {
        $method = $reflection->getMethod($methodName);
        
        foreach ($method->getParameters() as $param) {
            $parameterCount++;
            
            $attributes = $param->getAttributes(Schema::class);
            $this->assertNotEmpty(
                $attributes,
                "Parameter '{$param->getName()}' in '{$methodName}' missing Schema"
            );
        }
    }
    
    // Verify we found all 31 parameters
    $this->assertEquals(31, $parameterCount, 'Not all 31 parameters counted');
}

public function testLocationEnumSchema(): void {
    // addSoftwareSystem location param should have enum
    $reflection = new ReflectionClass(ModelTools::class);
    $method = $reflection->getMethod('addSoftwareSystem');
    
    foreach ($method->getParameters() as $param) {
        if ($param->getName() === 'location') {
            $attributes = $param->getAttributes(Schema::class);
            $this->assertCount(1, $attributes);
            
            $schema = $attributes[0]->newInstance();
            // Verify enum constraints
            $this->assertTrue(
                isset($schema->enum) && 
                in_array('Internal', $schema->enum) &&
                in_array('External', $schema->enum)
            );
        }
    }
}
```

Run:
```bash
./vendor/bin/phpunit tests/Unit/Tools/ModelToolsSchemaTest.php
```

#### Step 4: Verify Pattern Constraint
```php
public function testViewKeyPatternSchema(): void {
    // createSystemContextView key param should have pattern
    $reflection = new ReflectionClass(ModelTools::class);
    $method = $reflection->getMethod('createSystemContextView');
    
    foreach ($method->getParameters() as $param) {
        if ($param->getName() === 'key') {
            $attributes = $param->getAttributes(Schema::class);
            $this->assertNotEmpty($attributes);
            
            $schema = $attributes[0]->newInstance();
            $this->assertNotNull($schema->pattern);
            // Pattern should be: ^[a-zA-Z0-9_-]+$
        }
    }
}
```

---

## Priority 4: ViewTools Implementation

### What was added?
- New file: `src/Tools/ViewTools.php`
- 4 tool methods:
  - createSystemContextView (moved from ModelTools)
  - createContainerView (new)
  - createComponentView (new)
  - applyAutoLayout (new, MVP version)
- All with Schema attributes

### Validation Steps

#### Step 1: File Exists and Syntactically Valid
```bash
php -l src/Tools/ViewTools.php
```

Expected: `No syntax errors detected`

#### Step 2: Class is Discoverable
```php
// tests/Unit/Tools/ViewToolsBootstrapTest.php
public function testViewToolsClassExists(): void {
    $this->assertTrue(class_exists('StructurizrMcp\Tools\ViewTools'));
}

public function testViewToolsHasRequiredMethods(): void {
    $reflection = new ReflectionClass(ViewTools::class);
    
    $methods = [
        'createSystemContextView',
        'createContainerView',
        'createComponentView',
        'applyAutoLayout'
    ];
    
    foreach ($methods as $method) {
        $this->assertTrue(
            $reflection->hasMethod($method),
            "ViewTools missing method: {$method}"
        );
    }
}
```

#### Step 3: PHPStan and Code Quality
```bash
./vendor/bin/phpstan analyse src/Tools/ViewTools.php
php-cs-fixer fix src/Tools/ViewTools.php --dry-run
```

Expected: No errors

#### Step 4: Tool Discovery Test
```php
// tests/Integration/ViewToolsDiscoveryTest.php
public function testViewToolsAppearInMCPDiscovery(): void {
    // This would use actual MCP discovery
    $server = new MCPServer();
    $tools = $server->getDiscoveredTools();
    
    $viewToolNames = [
        'create_system_context_view',
        'create_container_view',
        'create_component_view',
        'apply_auto_layout'
    ];
    
    $discoveredToolNames = array_column($tools, 'name');
    
    foreach ($viewToolNames as $toolName) {
        $this->assertContains(
            $toolName,
            $discoveredToolNames,
            "Tool '{$toolName}' not discovered"
        );
    }
}
```

#### Step 5: Functional Workflow Test
```php
// tests/Integration/ViewToolsWorkflowTest.php
public function testCompleteViewWorkflow(): void {
    $workspaceId = $this->createTestWorkspace();
    $systemId = $this->addTestSystem($workspaceId);
    
    // Create system context view
    $result1 = $this->viewTools->createSystemContextView(
        $workspaceId,
        $systemId,
        'system-context'
    );
    $this->assertArrayHasKey('viewKey', $result1);
    $this->assertEquals('system-context', $result1['viewKey']);
    
    // Create container view
    $containerId = $this->addTestContainer($workspaceId, $systemId);
    $result2 = $this->viewTools->createContainerView(
        $workspaceId,
        $systemId,
        'containers'
    );
    $this->assertArrayHasKey('viewKey', $result2);
    
    // Create component view
    $result3 = $this->viewTools->createComponentView(
        $workspaceId,
        $containerId,
        'components'
    );
    $this->assertArrayHasKey('viewKey', $result3);
    
    // Apply auto layout
    $result4 = $this->viewTools->applyAutoLayout(
        $workspaceId,
        'system-context',
        'lr'
    );
    $this->assertTrue($result4['success']);
}
```

#### Step 6: Check ModelTools No Longer Has createSystemContextView
```bash
grep -c "createSystemContextView" src/Tools/ModelTools.php
```

Expected: `0` (method was moved)

---

## Priority 5: CliWrapper Implementation

### What was added?
- New file: `src/Structurizr/CliWrapper.php`
- New file: `src/Structurizr/ProcessResult.php` (DTO)
- New file: `src/Structurizr/ValidationResult.php` (DTO)
- 6 core methods: executeCommand, validate, export, push, pull

### Validation Steps

#### Step 1: Files Exist
```bash
ls -la src/Structurizr/{CliWrapper,ProcessResult,ValidationResult}.php
```

Expected: All 3 files exist

#### Step 2: Syntax Validation
```bash
php -l src/Structurizr/CliWrapper.php && \
php -l src/Structurizr/ProcessResult.php && \
php -l src/Structurizr/ValidationResult.php
```

Expected: No syntax errors

#### Step 3: PHPStan Level 8
```bash
./vendor/bin/phpstan analyse src/Structurizr/CliWrapper.php --level=8
```

Expected: No errors

#### Step 4: DTO Tests
```php
// tests/Unit/Structurizr/ProcessResultTest.php
public function testProcessResultCreation(): void {
    $result = new ProcessResult(0, 'output', '', true);
    $this->assertTrue($result->isSuccess());
    $this->assertEquals('output', $result->getStdout());
}

// tests/Unit/Structurizr/ValidationResultTest.php
public function testValidationResultWithErrors(): void {
    $result = new ValidationResult(false, ['Error 1', 'Error 2'], []);
    $this->assertFalse($result->isValid());
    $this->assertCount(2, $result->getErrors());
}
```

#### Step 5: CliWrapper Core Functionality
```php
// tests/Unit/Structurizr/CliWrapperTest.php
public function testCliWrapperCanValidateDsl(): void {
    $wrapper = new CliWrapper('/path/to/cli', $logger);
    
    // Copy test DSL to temp location
    $testDslPath = tempnam(sys_get_temp_dir(), 'test.dsl');
    file_put_contents($testDslPath, $this->getValidDslContent());
    
    $result = $wrapper->validate($testDslPath);
    
    $this->assertTrue($result->isValid());
    unlink($testDslPath);
}

public function testCliWrapperHandlesInvalidDsl(): void {
    $wrapper = new CliWrapper('/path/to/cli', $logger);
    
    $testDslPath = tempnam(sys_get_temp_dir(), 'test.dsl');
    file_put_contents($testDslPath, 'invalid { dsl');
    
    $result = $wrapper->validate($testDslPath);
    
    $this->assertFalse($result->isValid());
    $this->assertNotEmpty($result->getErrors());
    unlink($testDslPath);
}

public function testCliWrapperMasksCredentials(): void {
    // Ensure credentials never appear in logs
    $wrapper = new CliWrapper('/path/to/cli', $logger);
    
    // Call push with credentials
    // Verify logs don't contain API key/secret
}

public function testCliWrapperSecurityChecks(): void {
    // Path traversal prevention
    $wrapper = new CliWrapper('/path/to/cli', $logger);
    
    $this->expectException(SecurityException::class);
    $wrapper->validate('../../../etc/passwd');
}
```

#### Step 6: Integration Test with Real CLI
```php
// tests/Integration/CliWrapperRealTest.php
public function testExportToPlantUmlFormat(): void {
    // Only runs if Structurizr CLI is available
    if (!file_exists('/usr/local/bin/structurizr-cli')) {
        $this->markTestSkipped('Structurizr CLI not installed');
    }
    
    $wrapper = new CliWrapper('/usr/local/bin/structurizr-cli', $logger);
    
    $dslPath = __DIR__ . '/../Fixtures/ecommerce.dsl';
    $result = $wrapper->export($dslPath, 'plantuml');
    
    $this->assertStringContainsString('!define', $result);
}

public function testExportToMermaidFormat(): void {
    if (!file_exists('/usr/local/bin/structurizr-cli')) {
        $this->markTestSkipped('Structurizr CLI not installed');
    }
    
    $wrapper = new CliWrapper('/usr/local/bin/structurizr-cli', $logger);
    $dslPath = __DIR__ . '/../Fixtures/ecommerce.dsl';
    
    $result = $wrapper->export($dslPath, 'mermaid');
    
    $this->assertStringContainsString('graph', $result);
}
```

---

## Priority 6: ExportTools Implementation

### What was added?
- New file: `src/Tools/ExportTools.php`
- 3 new tool methods:
  - exportToPlantUml (moved from WorkspaceTools)
  - exportToMermaid (new)
  - importFromDsl (new)
- All with Schema attributes

### Validation Steps

#### Step 1: File Exists
```bash
php -l src/Tools/ExportTools.php
```

Expected: No syntax errors

#### Step 2: Tool Discovery
```php
public function testExportToolsDiscovered(): void {
    $tools = /* Get from server discovery */;
    $toolNames = array_column($tools, 'name');
    
    $this->assertContains('export_to_dsl', $toolNames);
    $this->assertContains('export_to_plantuml', $toolNames);
    $this->assertContains('export_to_mermaid', $toolNames);
    $this->assertContains('import_from_dsl', $toolNames);
}
```

#### Step 3: Export Output Format Validation
```php
public function testExportToPlantUmlFormat(): void {
    $workspaceId = $this->createTestWorkspace();
    
    $result = $this->exportTools->exportToPlantUml($workspaceId);
    
    $this->assertArrayHasKey('plantuml', $result);
    $this->assertStringContainsString('!define', $result['plantuml']);
    // Could validate PlantUML syntax
}

public function testExportToMermaidFormat(): void {
    $workspaceId = $this->createTestWorkspace();
    
    $result = $this->exportTools->exportToMermaid($workspaceId);
    
    $this->assertArrayHasKey('mermaid', $result);
    $this->assertStringContainsString('graph', $result['mermaid']);
}
```

#### Step 4: Import from DSL
```php
public function testImportFromDslCreatesWorkspace(): void {
    $dslContent = file_get_contents(__DIR__ . '/../Fixtures/sample.dsl');
    
    $result = $this->exportTools->importFromDsl($dslContent);
    
    $this->assertArrayHasKey('workspaceId', $result);
    
    // Verify workspace was created
    $workspace = $this->workspaceManager->load($result['workspaceId']);
    $this->assertNotNull($workspace);
}

public function testImportFromDslPreservesElements(): void {
    $dslContent = file_get_contents(__DIR__ . '/../Fixtures/ecommerce.dsl');
    
    $result = $this->exportTools->importFromDsl($dslContent);
    $workspaceId = $result['workspaceId'];
    
    // Get workspace and verify elements exist
    $workspace = $this->workspaceManager->load($workspaceId);
    
    // Check that persons, systems, containers exist
    $this->assertGreaterThan(0, count($workspace->getModel()->getPeople() ?? []));
}
```

---

## Priority 7: Test Suite Completeness

### What was added?
- tests/Unit/ directory with 6 test classes
- tests/Integration/ directory with 3 test classes
- tests/Fixtures/ with sample DSL files
- 150+ tests total
- 75%+ code coverage

### Validation Steps

#### Step 1: Test Directory Structure
```bash
find tests -type f -name "*Test.php" | wc -l
```

Expected: 10+ test files

#### Step 2: Run All Tests
```bash
./vendor/bin/phpunit
```

Expected: All tests pass, 0 failures, 0 warnings

#### Step 3: Check Coverage Threshold
```bash
./vendor/bin/phpunit --coverage-text
```

Expected output should show:
```
Code Coverage Report
====================
Lines: 85% (600/700)
Functions and Methods: 90%
Classes and Traits: 95%
```

Must have at least:
- Overall: 75%
- Core classes (WorkspaceManager, DslBuilder): 90%
- Tools: 85%

#### Step 4: Verify No Warnings
```bash
./vendor/bin/phpunit 2>&1 | grep -i "warning"
```

Expected: No output (0 warnings)

#### Step 5: Test Specific Modules
```bash
# Unit tests only
./vendor/bin/phpunit --testsuite Unit

# Integration tests only
./vendor/bin/phpunit --testsuite Integration

# Specific class
./vendor/bin/phpunit tests/Unit/Structurizr/WorkspaceManagerTest.php
```

All should pass

#### Step 6: Coverage Report HTML
```bash
./vendor/bin/phpunit --coverage-html coverage
open coverage/index.html  # View in browser
```

Expected: Visual coverage report showing:
- Green (>90% coverage)
- Yellow (70-90% coverage)
- Red (<70% coverage)

Verify no large red areas in core classes

#### Step 7: PHPStan Level 8 on Tests (Optional)
```bash
./vendor/bin/phpstan analyse tests --level=5
```

Expected: Minimal errors in tests

---

## Master Validation Checklist

After implementing all 7 priorities, run this checklist:

```bash
# 1. Code Syntax
php -l src/Tools/*.php && echo "✓ Tools syntax OK"
php -l src/Structurizr/*.php && echo "✓ Structurizr syntax OK"

# 2. Static Analysis
./vendor/bin/phpstan analyse src --level=8 && echo "✓ PHPStan clean"

# 3. Code Style
php-cs-fixer fix src --dry-run && echo "✓ Code style OK"

# 4. Tests
./vendor/bin/phpunit && echo "✓ All tests pass"

# 5. Coverage
./vendor/bin/phpunit --coverage-text 2>&1 | tail -5 && echo "✓ Coverage report generated"

# 6. Git Status
git status && echo "✓ Git status clean"

# 7. Run Server
timeout 3 php server.php < /dev/null && echo "✓ Server starts"
```

Expected: All 7 checks pass with ✓

---

## Quick Reference: Test Commands

```bash
# Run all tests
composer test

# Run only unit tests
composer test:unit

# Run only integration tests
composer test:integration

# Generate coverage report
composer test:coverage

# Quick test (no coverage)
composer test:quick

# Static analysis
composer stan

# Code style fix
composer cs-fix

# Run specific test
./vendor/bin/phpunit tests/Unit/Structurizr/WorkspaceManagerTest.php

# Run specific test method
./vendor/bin/phpunit tests/Unit/Structurizr/WorkspaceManagerTest.php::testCreateWorkspaceSuccessfully

# Watch tests (if installed: composer require --dev pcov)
./vendor/bin/phpunit --watch
```

