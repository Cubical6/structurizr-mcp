# Structurizr MCP - Comprehensive Improvement Plan

**Generated:** 2025-11-16
**Analysis Tools:** 5 Parallel Exploration Agents
**Total Issues Found:** 120+

---

## Priority 0: Critical Bugs (MUST FIX)

### 1. Fix DSL Builder Content Loss [CRITICAL]
**Files:** `src/Tools/ModelTools.php:276-288`, `src/Tools/ViewTools.php:203-215`
**Issue:** `createBuilderFromWorkspace()` starts fresh, losing all existing workspace content
**Impact:** Incremental model building impossible - each addition overwrites workspace
**Solution:**
- Option A: Parse existing DSL to rebuild DslBuilder state
- Option B: Append to existing DSL string instead of rebuilding
- **Recommended:** Implement DSL parser in DslBuilder

**Acceptance Criteria:**
- Adding person to existing workspace preserves existing elements
- Adding container preserves existing systems and persons
- Integration tests pass without @group incomplete

---

### 2. Add Comprehensive Error Handling
**Files:** All Tool classes
**Issue:** No try-catch blocks wrapping WorkspaceManager or DslBuilder calls
**Impact:** Uncaught exceptions, poor error messages to MCP clients

**ModelTools.php - 5 methods need error handling:**
- `addPerson()` (lines 48-67)
- `addSoftwareSystem()` (lines 96-118)
- `addContainer()` (lines 150-169)
- `addComponent()` (lines 201-220)
- `addRelationship()` (lines 252-270)

**ViewTools.php - 4 methods need error handling:**
- `createSystemContextView()` (lines 49-66)
- `createContainerView()` (lines 93-110)
- `createComponentView()` (lines 137-154)
- `applyAutoLayout()` (lines 177-197)

**WorkspaceTools.php - Fix inconsistent error handling:**
- `deleteWorkspace()` returns `success: false` instead of throwing
- Other methods need try-catch blocks

**ExportTools.php - 2 methods need error handling:**
- `exportToDsl()` (line 38-48)
- Improve error handling in `importFromDsl()`

**Solution Pattern:**
```php
try {
    $workspace = $this->workspaceManager->load($workspaceId);
    // ... business logic ...
    $this->workspaceManager->save($workspace);
    return ['success' => true, 'data' => ...];
} catch (WorkspaceNotFoundException $e) {
    throw new ToolCallException("Workspace not found: {$workspaceId}");
} catch (\Exception $e) {
    throw new ToolCallException("Failed to X: " . $e->getMessage());
}
```

**Acceptance Criteria:**
- All tool methods have try-catch blocks
- Exceptions wrapped in ToolCallException
- Clear error messages for users

---

### 3. Fix Unsafe Array Destructuring
**File:** `src/Configuration.php:30`
**Issue:** `explode()` might not return 2 elements, causing undefined array key
**Code:**
```php
[$key, $value] = explode('=', $line, 2);  // No validation
```

**Solution:**
```php
$parts = explode('=', $line, 2);
if (count($parts) !== 2) {
    continue;  // Skip malformed lines
}
[$key, $value] = $parts;
```

**Acceptance Criteria:**
- .env parsing handles malformed lines gracefully
- Add test case for malformed .env file

---

## Priority 1: Code Quality Improvements

### 4. Extract Magic Numbers to Constants

**Configuration.php:**
```php
private const DEFAULT_API_URL = 'https://api.structurizr.com';
private const DEFAULT_CLI_PATH = './bin/structurizr-cli.sh';
private const DEFAULT_SERVER_NAME = 'structurizr-mcp-server';
private const DEFAULT_SERVER_VERSION = '1.0.0';
private const DEFAULT_LOG_LEVEL = 'DEBUG';
```

**CliWrapper.php:**
```php
private const CREDENTIAL_CHECK_TIMEOUT_SECONDS = 5;
private const DEFAULT_API_URL = 'https://api.structurizr.com';
```

**DslBuilder.php:**
```php
private const COLOR_SOFTWARE_SYSTEM_BG = '#1168bd';
private const COLOR_SOFTWARE_SYSTEM_FG = '#ffffff';
private const COLOR_PERSON_BG = '#08427b';
private const COLOR_PERSON_FG = '#ffffff';
private const ELEMENT_TYPE_SOFTWARE_SYSTEM = 'Software System';
private const ELEMENT_TYPE_PERSON = 'Person';
```

**ExportTools.php:**
```php
private const TEMP_FILE_PREFIX = 'ws_export_';
private const DEFAULT_WORKSPACE_NAME = 'Imported Workspace';
```

**WorkspaceManager.php:**
```php
private const DIRECTORY_PERMISSIONS = 0755;
private const WORKSPACE_ID_PATTERN = '/[^a-zA-Z0-9_-]/';
private const WORKSPACE_ID_PREFIX = 'ws_';
private const WORKSPACE_ID_RANDOM_BYTES = 8;
private const MAX_NAME_LENGTH = 100;
```

**Acceptance Criteria:**
- No hard-coded strings/numbers in methods
- All constants properly documented
- PHPStan level 8 passes

---

### 5. Add Missing PHPDoc Blocks

**Properties needing PHPDoc:**
- Configuration.php: `$config` (line 12)
- DslBuilder.php: All 6 private properties (lines 12-17)
- WorkspaceManager.php: `$filesystem` (line 16)

**Constructors needing PHPDoc:**
- All Tool classes
- All Exception classes

**Private methods needing PHPDoc:**
- ExportTools.php: `extractWorkspaceName()`, `extractWorkspaceDescription()`
- ModelTools.php: `createBuilderFromWorkspace()`
- ViewTools.php: `createBuilderFromWorkspace()`

**Standard PHPDoc template:**
```php
/**
 * Brief description of method/property
 *
 * @param Type $param Parameter description
 * @return Type Return value description
 * @throws ExceptionType When this exception is thrown
 */
```

**Acceptance Criteria:**
- All properties have PHPDoc
- All constructors documented
- All private methods documented
- PHPStan level 8 passes

---

### 6. Eliminate Code Duplication

#### 6.1 Extract Shared `createBuilderFromWorkspace()`
**Current:** Duplicated in ModelTools.php and ViewTools.php
**Solution:** Create shared base class or move to WorkspaceManager

**Option A - Base Class:**
```php
// src/Tools/AbstractWorkspaceTool.php
abstract class AbstractWorkspaceTool
{
    protected function createBuilderFromWorkspace(Workspace $workspace): DslBuilder
    {
        // Shared implementation
    }
}
```

**Option B - Move to WorkspaceManager:**
```php
// WorkspaceManager.php
public function createBuilder(Workspace $workspace): DslBuilder
{
    // Implementation
}
```

**Recommended:** Option A (Base Class)

#### 6.2 Extract Tag Formatting in DslBuilder
**Current:** Duplicated 5 times
**Solution:**
```php
private function formatTags(array $tags): string
{
    return !empty($tags) ? ' "' . implode(',', $tags) . '"' : '';
}

private function formatTechnology(?string $technology): string
{
    return $technology ? " \"{$technology}\"" : '';
}
```

#### 6.3 Extract Temp File Handling in ExportTools
**Current:** Duplicated in `exportToPlantUml()` and `exportToMermaid()`
**Solution:**
```php
private function exportWithTempFile(
    Workspace $workspace,
    string $format,
    ?string $viewKey = null
): string {
    $tempPath = sys_get_temp_dir() . '/' . uniqid(self::TEMP_FILE_PREFIX, true) . '.dsl';
    try {
        file_put_contents($tempPath, $workspace->dsl);
        return $this->cliWrapper->export($tempPath, $format, $viewKey);
    } finally {
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
}
```

**Acceptance Criteria:**
- No duplicate code
- All tests still pass
- PHPStan level 8 passes

---

### 7. Remove Redundant Validation

**Issue:** Schema attributes already enforce validation, but code duplicates it

**ModelTools.php line 98-100:**
```php
// REMOVE - Schema already validates this
if (!in_array($location, ['Internal', 'External'], true)) {
    throw new \InvalidArgumentException("Location must be 'Internal' or 'External'");
}
```

**ViewTools.php line 179-181:**
```php
// REMOVE - Schema already validates this
if (!in_array($direction, ['tb', 'bt', 'lr', 'rl'], true)) {
    throw new \InvalidArgumentException("Invalid layout direction: {$direction}");
}
```

**WorkspaceTools.php line 80-82:**
```php
// REMOVE - Schema already validates this
if (!in_array($format, ['json', 'dsl'], true)) {
    throw new \InvalidArgumentException("Format must be 'json' or 'dsl'");
}
```

**WorkspaceTools.php line 47-48:**
```php
// REMOVE - Schema already validates length
if (strlen($name) > 100) {
    throw new \InvalidArgumentException('Workspace name cannot exceed 100 characters');
}
```

**Acceptance Criteria:**
- Remove all redundant validation
- Verify MCP SDK Schema attributes work correctly
- Add tests to verify Schema validation

---

## Priority 2: Missing Features

### 8. Implement Missing Tools

#### 8.1 Create Dynamic View Tool
**File:** `src/Tools/ViewTools.php`
**Tool:** `create_dynamic_view(elementId, key, description?)`

```php
#[McpTool(
    name: 'create_dynamic_view',
    description: 'Creates a dynamic view showing runtime behavior'
)]
public function createDynamicView(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,

    #[Schema(description: 'Scope element ID', minLength: 1)]
    string $elementId,

    #[Schema(description: 'Unique view key', minLength: 1)]
    string $key,

    #[Schema(description: 'View description')]
    string $description = ''
): array
```

#### 8.2 Create Documentation Tools
**File:** `src/Tools/DocumentationTools.php` (NEW)

**Tools:**
- `add_documentation_section(workspaceId, title, content)`
- `add_adr(workspaceId, id, date, title, status, content)`

#### 8.3 Create Analysis Tools
**File:** `src/Tools/AnalysisTools.php` (NEW)

**Tools:**
- `analyze_dependencies(workspaceId, elementId?)`
- `find_element(workspaceId, name)`
- `validate_workspace(workspaceId)`

**Acceptance Criteria:**
- All 6 missing tools implemented
- Comprehensive tests for each tool
- Documentation updated in CLAUDE.md

---

### 9. Add Missing Test Coverage

#### 9.1 Create ExportToolsTest
**File:** `tests/Unit/Tools/ExportToolsTest.php`

**Test cases:**
- `testExportToDslReturnsWorkspaceDsl()`
- `testExportToPlantUmlWithoutViewKey()`
- `testExportToPlantUmlWithViewKey()`
- `testExportToMermaidWithoutViewKey()`
- `testExportToMermaidWithViewKey()`
- `testExportCleansUpTempFileOnSuccess()`
- `testExportCleansUpTempFileOnException()`
- `testExportThrowsExceptionWhenWorkspaceNotFound()`
- `testImportFromDslCreatesWorkspace()`
- `testImportFromDslInvalidDsl()`

**Target:** >90% code coverage

#### 9.2 Create CliWrapperTest
**File:** `tests/Unit/Structurizr/CliWrapperTest.php`

**Test cases:**
- `testConstructorValidatesCliPath()`
- `testConstructorValidatesExecutable()`
- `testExportCommandSuccess()`
- `testExportCommandTimeout()`
- `testExportCommandFailure()`
- `testCommandSanitizationForLogging()`
- `testCredentialRemovalFromLogs()`

**Mock:** Symfony Process component
**Target:** >80% code coverage

#### 9.3 Create ConfigurationTest
**File:** `tests/Unit/ConfigurationTest.php`

**Test cases:**
- `testLoadFromEnvironmentVariables()`
- `testLoadFromDotEnvFile()`
- `testDefaultValues()`
- `testMalformedEnvFileHandling()`

**Acceptance Criteria:**
- Overall code coverage >85%
- All critical paths tested
- Edge cases covered

---

### 10. Add Missing Configuration Files

#### 10.1 Create .editorconfig
**File:** `.editorconfig`

```ini
root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
trim_trailing_whitespace = true

[*.php]
indent_style = space
indent_size = 4

[*.{json,yml,yaml}]
indent_style = space
indent_size = 2

[*.md]
trim_trailing_whitespace = false
```

#### 10.2 Create .php-cs-fixer.dist.php
**File:** `.php-cs-fixer.dist.php`

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
    ])
    ->setFinder($finder);
```

**Acceptance Criteria:**
- EditorConfig works in common editors
- PHP CS Fixer script runs without errors
- Code style consistent across project

---

## Priority 3: Documentation Updates

### 11. Update CLAUDE.md

**Sections to fix:**

#### 11.1 Tool Signatures
- Change `workspaceId` from `integer` to `string`
- Update example code to use `WorkspaceManager` instead of `StructurizrCliWrapper`
- Document actual return structures

#### 11.2 Project Structure
- Add `Configuration.php`
- Add `ProcessResult.php`, `ValidationResult.php`
- Add `DslBuilder.php`, `Workspace.php`
- Remove non-existent `ApiClient.php`
- Mark Resources and Prompts as "Planned" not implemented

#### 11.3 Dependencies
- Update `mcp/sdk` to `"dev-main"`
- Update Symfony components to `"^6.0|^7.0"`
- Add `psr/simple-cache`
- Add `symfony/filesystem`
- Add `friendsofphp/php-cs-fixer` to dev dependencies

#### 11.4 Server.php Example
- Update to show `Configuration` class usage
- Show auto-discovery pattern
- Remove Container and Session Store (not currently used)
- Add error handling example

#### 11.5 Missing Tools Documentation
- Mark 6 missing tools as "Planned" or implement them
- Add implementation status table

#### 11.6 Claude Desktop Configuration
- Document all supported environment variables:
  - STRUCTURIZR_API_KEY
  - STRUCTURIZR_API_SECRET
  - STRUCTURIZR_WORKSPACE_ID
  - STRUCTURIZR_API_URL
  - STRUCTURIZR_CLI_PATH
  - WORKSPACE_STORAGE_PATH
  - LOG_LEVEL
  - LOG_PATH
  - SERVER_NAME
  - SERVER_VERSION

**Acceptance Criteria:**
- All code examples are accurate and runnable
- All documented tools exist
- All implemented features documented
- No outdated information

---

## Execution Strategy (Parallel Workstreams)

### Workstream A: Critical Fixes (Agent 1)
- Task 1: Fix DSL builder content loss
- Task 2: Add error handling to ModelTools
- Task 3: Fix Configuration.php array destructuring

### Workstream B: Error Handling (Agent 2)
- Task 4: Add error handling to ViewTools
- Task 5: Add error handling to WorkspaceTools
- Task 6: Add error handling to ExportTools

### Workstream C: Code Quality (Agent 3)
- Task 7: Extract magic numbers to constants
- Task 8: Add missing PHPDoc blocks
- Task 9: Remove redundant validation

### Workstream D: Refactoring (Agent 4)
- Task 10: Create AbstractWorkspaceTool base class
- Task 11: Extract shared helper methods in DslBuilder
- Task 12: Extract temp file handling in ExportTools

### Workstream E: Testing (Agent 5)
- Task 13: Create ExportToolsTest
- Task 14: Create CliWrapperTest
- Task 15: Create ConfigurationTest

### Workstream F: Missing Features (Agent 6)
- Task 16: Implement create_dynamic_view
- Task 17: Create DocumentationTools
- Task 18: Create AnalysisTools

---

## Validation Steps

After all improvements:

1. **Run PHPStan:**
   ```bash
   composer stan
   ```
   Expected: No errors at level 8

2. **Run PHPUnit:**
   ```bash
   composer test
   ```
   Expected: All tests pass, coverage >85%

3. **Run PHP CS Fixer:**
   ```bash
   composer cs-fix
   ```
   Expected: No style violations

4. **Manual Testing:**
   - Start server: `composer server`
   - Test with MCP client
   - Verify incremental model building works
   - Verify error messages are clear

5. **Integration Test:**
   - Remove `@group incomplete` from integration tests
   - Verify all integration tests pass

---

## Success Metrics

- [ ] Zero P0 critical bugs
- [ ] All tools have error handling
- [ ] Code coverage >85%
- [ ] PHPStan level 8 passes with 0 errors
- [ ] All documented tools implemented
- [ ] Documentation 100% accurate
- [ ] All integration tests pass
- [ ] Zero code duplication
- [ ] Consistent code style throughout

**Estimated Effort:** 8-12 hours with parallel execution
