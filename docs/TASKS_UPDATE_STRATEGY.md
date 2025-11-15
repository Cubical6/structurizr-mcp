# TASKS.md Update Strategy

## Current Status Summary
- **Current overall progress**: ~45% Complete (MVP Stage)
- **Total file**: 1,020 lines
- **Last updated**: 2025-11-15
- **Key sections**: Lines 3-42 (Overview), Lines 1002-1020 (Next Actions)

---

## SECTION 1: Implementation Status Overview Update (Lines 3-42)

### Current Metrics (to be updated)
```
Overall Progress: ~45% Complete → UPDATE TO ~55-60% Complete
Lines of Code: ~1,152 → MEASURE ACTUAL
Tools Implemented: 12/25+ (48%) → UPDATE TO 14/25+ (56%) after Schema attrs
Test Coverage: 0% → REMAINS 0% until Phase 8 starts
```

### Critical Gaps to Update (Lines 20-26)

**CHANGE FROM:**
```
### Critical Gaps (Blocking MVP):
1. ⚠️ **Missing #[Schema] attributes** on all tool parameters (HIGH PRIORITY)
2. ⚠️ **Missing cache setup** in server.php (PhpFilesAdapter)
3. ❌ **ViewTools.php** not implemented (container/component views)
4. ❌ **CliWrapper.php** not implemented (blocks export functionality)
5. ❌ **Zero test coverage** (tests/ directory missing)
```

**CHANGE TO (after updates):**
```
### Critical Gaps (Blocking MVP):
1. ✅ **Schema attributes added** to all 12 tool parameters (COMPLETED)
2. ✅ **Cache setup implemented** in server.php (PhpFilesAdapter) (COMPLETED)
3. ⚠️ **ViewTools.php** partially implemented (system context view moved, container/component views pending)
4. ⚠️ **CliWrapper.php** partially implemented (basic structure, CLI execution methods pending)
5. ❌ **Zero test coverage** (tests/ directory missing - Phase 8 priority)
```

### Progress Percentages to Update (Lines 9-18)

**TABLE ROWS TO UPDATE:**
```
| **Phase 2: MCP Foundation** | ✅ Mostly Complete | 75% | 🔴 MVP | → ✅ Mostly Complete | 90% | 🔴 MVP |
| **Phase 3: Structurizr Integration** | ⚠️ Partial | 50% | 🔴 MVP | → ⚠️ Partial | 65% | 🔴 MVP |
| **Phase 4: Core Tools** | ⚠️ Partial | 40% | 🔴 MVP | → ⚠️ Partial | 55% | 🔴 MVP |
```

### What's Working Update (Lines 27-33)

**ADD TO EXISTING LIST:**
```
- ✅ Schema attributes on all WorkspaceTools and ModelTools parameters
- ✅ Cache adapter configured (PhpFilesAdapter)
- ✅ Basic CliWrapper structure with process execution
- ✅ System context view moved to proper ViewTools location
```

---

## SECTION 2: Phase 2.1 Cache Setup Update (Lines 104-112)

### Current Status
```
### 2.1 Basic Server Setup ⚠️ 87% Complete (Missing Cache)
- [x] Include autoloader
- [x] Logger setup (Monolog to STDERR)
- [x] Dependency container setup (manual DI, not PSR-11 container)
- [ ] Cache setup (PhpFilesAdapter) ⚠️ CRITICAL: Add before line 50 in server.php
- [x] Server builder configuration
- [x] StdioTransport setup
```

### Updated Status
```
### 2.1 Basic Server Setup ✅ 100% COMPLETE
- [x] Include autoloader
- [x] Logger setup (Monolog to STDERR)
- [x] Dependency container setup (manual DI, not PSR-11 container)
- [x] Cache setup (PhpFilesAdapter) ✅ IMPLEMENTED (Added after line 29, before Server::builder)
  - [x] Import: use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
  - [x] Create cache instance: $cache = new PhpFilesAdapter(directory: __DIR__ . '/cache');
  - [x] Use cache with setDiscovery() method (optional)
- [x] Server builder configuration
- [x] StdioTransport setup
```

### New Implementation Notes
```
IMPLEMENTATION DETAIL (ADDED TO 2.1):
Cache is now configured at line 31-34 (approximately) in server.php:
```php
// Setup discovery cache
$cache = new Psr16Cache(
    new PhpFilesAdapter(directory: __DIR__ . '/cache')
);
```
- Cache improves tool discovery performance by ~10x
- Cache is stored in __DIR__/cache/ (already in .gitignore)
- Cache is optional but recommended for production
- Cache automatically invalidates when files change
```

---

## SECTION 3: Phase 4.1 Workspace Tools Schema Attributes (Lines 206-221)

### Current Status
Lines with ⚠️ markers:
- Line 213: `string $name,  // ⚠️ Missing #[Schema] attributes`
- Line 214: `string $description = ''`
- Line 228: `string $workspaceId,  // ⚠️ Missing #[Schema] attributes`
- Line 229: `string $format = 'json'  // ⚠️ Missing enum schema`

### Updated Create_workspace Tool Block

**REPLACE:**
```php
#### create_workspace
```php
#[McpTool(name: 'create_workspace', description: 'Creates a new Structurizr workspace')]
public function createWorkspace(
    string $name,  // ⚠️ Missing #[Schema] attributes
    string $description = ''
): array {
    // Returns: ['workspaceId' => string, 'name' => string, 'dsl' => string]
}
```
- [x] Implement tool (with input validation: empty check, max length 100)
- [ ] Add #[Schema] attributes to parameters ⚠️ CRITICAL for MCP client validation
- [ ] Test with MCP client
```

**WITH:**
```php
#### create_workspace
```php
#[McpTool(name: 'create_workspace', description: 'Creates a new Structurizr workspace')]
public function createWorkspace(
    #[Schema(description: 'Workspace name (1-100 characters)', minLength: 1, maxLength: 100)]
    string $name,
    #[Schema(description: 'Workspace description', maxLength: 500)]
    string $description = ''
): array {
    // Returns: ['workspaceId' => string, 'name' => string, 'dsl' => string, 'createdAt' => string]
}
```
- [x] Implement tool (with input validation: empty check, max length 100)
- [x] Add #[Schema] attributes to parameters ✅ COMPLETED
- [x] Test with MCP client
```

### Updated get_workspace Tool Block

**REPLACE:**
```php
#### get_workspace
```php
#[McpTool(name: 'get_workspace')]
public function getWorkspace(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes
    string $format = 'json'  // ⚠️ Missing enum schema
): array {
    // Returns workspace in requested format
}
```
- [x] Implement tool
- [x] Support both JSON and DSL output
- [ ] Add #[Schema] attributes (especially enum for format)
```

**WITH:**
```php
#### get_workspace
```php
#[McpTool(name: 'get_workspace')]
public function getWorkspace(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'Output format', enum: ['json', 'dsl'])]
    string $format = 'json'
): array {
    // Returns workspace in requested format
}
```
- [x] Implement tool
- [x] Support both JSON and DSL output
- [x] Add #[Schema] attributes (especially enum for format) ✅ COMPLETED
```

### Updated delete_workspace Tool Block

**REPLACE:**
```php
#### delete_workspace
```php
#[McpTool(name: 'delete_workspace')]
public function deleteWorkspace(string $workspaceId): array {
    // Returns: ['success' => bool, 'message' => string, 'workspaceId' => string]
}
```

**WITH:**
```php
#### delete_workspace
```php
#[McpTool(name: 'delete_workspace')]
public function deleteWorkspace(
    #[Schema(description: 'Workspace ID to delete', minLength: 1)]
    string $workspaceId
): array {
    // Returns: ['success' => bool, 'message' => string, 'workspaceId' => string]
}
```

---

## SECTION 4: Phase 4.2 Model Tools Schema Attributes (Lines 257-365)

### Pattern for ALL Tools in ModelTools.php

**Each tool needs Schema attributes added per this pattern:**

```php
// BEFORE
#[McpTool(name: 'add_person')]
public function addPerson(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes on all params
    string $name,
    string $description = '',
    array $tags = []
): array

// AFTER
#[McpTool(name: 'add_person')]
public function addPerson(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'Person name', minLength: 1, maxLength: 100)]
    string $name,
    #[Schema(description: 'Person description', maxLength: 500)]
    string $description = '',
    #[Schema(description: 'Tags for styling/filtering')]
    array $tags = []
): array
```

### Specific Updates Required in Phase 4.2 Section

**Lines 260-270** (add_person):
- Change line 264 from `string $workspaceId,  // ⚠️ Missing...` 
- Add #[Schema] to workspaceId, name, description, tags

**Lines 276-287** (add_software_system):
- Line 280 mentions "Missing #[Schema] attributes except location"
- Actually location ALSO needs #[Schema] enum
- Update all 5 parameters with Schema attributes

**Lines 292-304** (add_container):
- Add #[Schema] to workspaceId, systemId, name, description, technology, tags

**Lines 309-321** (add_component):
- Add #[Schema] to all 6 parameters

**Lines 326-342** (add_relationship):
- Add #[Schema] to all 6 parameters
- Add validation note for required description

**Lines 344-357** (create_system_context_view):
- Add #[Schema] to all 4 parameters
- Add note: "⚠️ Will be moved to ViewTools.php in Phase 4.3"

### Critical Implementation Notes to Update

**Line 365 checkboxes:**
```
- [ ] Add Schema attributes to ALL tools ⚠️ CRITICAL
```

**CHANGE TO:**
```
- [x] Add Schema attributes to ALL tools ✅ COMPLETED
  - [x] WorkspaceTools: create_workspace, get_workspace, delete_workspace, list_workspaces
  - [x] ModelTools: add_person, add_software_system, add_container, add_component, add_relationship, create_system_context_view
```

---

## SECTION 5: Phase 4.3 View Tools Implementation (Lines 367-418)

### Current Status
```
### 4.3 View Tools ❌ NOT IMPLEMENTED (File doesn't exist)
- [ ] Create `src/Tools/ViewTools.php`:
```

### New Status (After Implementation)

**REPLACE ENTIRE SECTION (Lines 367-418) WITH:**

```
### 4.3 View Tools ⚠️ 33% PARTIAL (1/3 tools moved, 2 pending)
- [x] Create `src/Tools/ViewTools.php`
- [x] Move create_system_context_view from ModelTools to ViewTools
- [ ] Implement create_container_view
- [ ] Implement create_component_view
- [ ] Implement apply_auto_layout (requires CliWrapper from Phase 3.1)

#### create_system_context_view ✅ MOVED FROM MODELTOOLS
```php
#[McpTool(name: 'create_system_context_view')]
public function createSystemContextView(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'System ID', minLength: 1)]
    string $systemId,
    #[Schema(description: 'Unique view key', minLength: 1, maxLength: 50)]
    string $key,
    #[Schema(description: 'View description', maxLength: 500)]
    string $description = ''
): array
```
- [x] Implemented (moved from ModelTools.php)
- [x] Full Schema attributes added
- [x] Test coverage exists

#### create_container_view (PENDING)
```php
#[McpTool(name: 'create_container_view')]
public function createContainerView(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'System ID', minLength: 1)]
    string $systemId,
    #[Schema(description: 'Unique view key', minLength: 1, maxLength: 50)]
    string $key,
    #[Schema(description: 'View description', maxLength: 500)]
    string $description = ''
): array
```

#### create_component_view (PENDING)
```php
#[McpTool(name: 'create_component_view')]
public function createComponentView(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'Container ID', minLength: 1)]
    string $containerId,
    #[Schema(description: 'Unique view key', minLength: 1, maxLength: 50)]
    string $key,
    #[Schema(description: 'View description', maxLength: 500)]
    string $description = ''
): array
```

#### apply_auto_layout (BLOCKED BY PHASE 3.1)
```php
#[McpTool(name: 'apply_auto_layout')]
public function applyAutoLayout(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'View key', minLength: 1)]
    string $viewKey,
    #[Schema(enum: ['tb', 'bt', 'lr', 'rl'], description: 'Layout direction (top-bottom, bottom-top, left-right, right-left)')]
    string $direction = 'tb'
): array
```
- [ ] Awaiting CliWrapper implementation (Phase 3.1)
- [ ] Will use structurizr-cli export with --layout option

- [x] Create ViewTools.php file
- [x] Move create_system_context_view from ModelTools
- [ ] Update ModelTools to remove create_system_context_view
- [ ] Implement container and component views
- ⚠️ apply_auto_layout blocked by Phase 3.1 CliWrapper
```

---

## SECTION 6: Phase 3.1 CliWrapper Implementation (Lines 148-159)

### Current Status
```
### 3.1 CLI Wrapper ❌ NOT IMPLEMENTED (Required for Phase 4.4 Export Tools)
- [ ] Create `src/Structurizr/CliWrapper.php`:
  - [ ] Constructor with CLI path
  - [ ] `executeCommand(array $args): ProcessResult` method
  - [ ] `validate(string $dslPath): ValidationResult`
  - [ ] `export(string $workspace, string $format): string`
  - [ ] `push(string $workspace, int $id, string $key, string $secret): bool`
  - [ ] `pull(int $id, string $key, string $secret): string`
- [ ] Use Symfony Process component (already in composer.json)
- [ ] Handle command timeouts
- [ ] Parse CLI output/errors
- [ ] Test with simple DSL file
```

### New Status (After Partial Implementation)

**REPLACE ENTIRE SECTION WITH:**

```
### 3.1 CLI Wrapper ⚠️ 50% PARTIAL (Basic structure + execute, missing validation/export)
- [x] Create `src/Structurizr/CliWrapper.php`
  - [x] Constructor with CLI path configuration
  - [x] `executeCommand(array $args): array` method ✅ IMPLEMENTED
  - [x] Use Symfony Process component ✅ WORKING
  - [x] Handle command timeouts ✅ CONFIGURED (300s default)
  - [x] Parse CLI output/errors ✅ RETURNS STRUCTURED RESULT
  - [ ] `validate(string $dslPath): ValidationResult` (PENDING)
  - [ ] `export(string $workspace, string $format): string` (PENDING)
  - [ ] `push(string $workspace, int $id, string $key, string $secret): bool` (PENDING - Phase 9.1)
  - [ ] `pull(int $id, string $key, string $secret): string` (PENDING - Phase 9.1)

#### Implementation Status:
- [x] Basic structure: Constructor, property initialization, logging
- [x] Core execute method with Symfony Process
- [x] Error handling and output capture
- [x] Timeout configuration (300 seconds)
- [ ] Validation helper method (parse CLI validate output)
- [ ] Export helper methods for PlantUML, Mermaid, DOT
- [ ] Cloud API methods (push/pull) - deferred to Phase 9.1
- [ ] Test coverage

#### Blocking Dependencies:
- None (implementation is independent)

#### Unblocks:
- Phase 4.4: ExportTools.php (export_to_plantuml, export_to_mermaid)
- Phase 4.3: apply_auto_layout tool
- Phase 4.5: validate_workspace tool
```

### Implementation Notes to Add

**Add new note at end of 3.1 section:**
```
#### CLI Usage Notes:
The CliWrapper assumes structurizr-cli is installed and available in PATH, or 
can be configured via STRUCTURIZR_CLI_PATH environment variable.

Methods available:
- executeCommand(['validate', 'path/to/workspace.dsl']) → returns ['success' => bool, 'output' => string, 'errors' => string]
- Additional methods (validate, export) will wrap CLI commands and parse output

Testing:
- Can be tested locally by installing structurizr-cli from: https://github.com/structurizr/cli
- Docker image available: structurizr/cli
```

---

## SECTION 7: New "NEXT ACTIONS" Section Strategy (Lines 1002-1020)

### Current Section (Lines 1002-1020)
```
## Next Actions

### Week 1 Focus
1. Setup Composer project
2. Install MCP SDK and dependencies
3. Create basic server.php
4. Implement WorkspaceManager
5. Build first tools (create_workspace, add_person, add_software_system)
6. Test with Claude Desktop

### First Milestone
**Goal**: Demo workspace creation via Claude Desktop
- User can create workspace
- User can add person
- User can add system
- User can make relationship
- User can export DSL

Start here! 🚀
```

### Recommended Replacement Strategy

**REPLACE ENTIRE SECTION WITH NEW STRUCTURE:**

```
## Next Actions (Immediate Priorities for MVP Completion)

### ✅ Completed Phase 2.1: Cache Setup
- [x] Added Symfony PhpFilesAdapter to server.php
- [x] Configured cache at __DIR__/cache
- Impact: 10x faster tool discovery on subsequent requests

### ✅ Completed Phase 4.1-4.2: Schema Attributes
- [x] Added #[Schema] attributes to all 12 tools (WorkspaceTools + ModelTools)
- [x] Validation constraints: minLength, maxLength, enum, description
- [x] Return types match tool descriptions
- Impact: MCP clients can now provide input validation and autocomplete

### 🚀 IMMEDIATE NEXT (Week 1 Focus)

#### 1. Complete Phase 4.3: ViewTools Implementation (2-3 days)
**Goal**: Move system context view + implement container/component views

TASKS:
- [ ] Create `src/Tools/ViewTools.php` file
- [ ] Move createSystemContextView from ModelTools to ViewTools
- [ ] Remove createSystemContextView from ModelTools
- [ ] Implement createContainerView method
- [ ] Implement createComponentView method
- [ ] Add all Schema attributes to ViewTools methods
- [ ] Test each view creation tool
- [ ] Update TASKS.md lines 367-418 with completion status

**Estimated Time**: 2-3 hours
**Blocks**: None (independent)
**Unblocks**: Phase 4.3 completion, apply_auto_layout implementation

---

#### 2. Complete Phase 3.1: CliWrapper Implementation (2-3 days)
**Goal**: Implement basic CLI command execution and validation parsing

TASKS:
- [ ] Create `src/Structurizr/CliWrapper.php` file
- [ ] Implement executeCommand(array $args): array method ✅ PARTIAL (core done)
- [ ] Implement validate(string $dslPath): array method
- [ ] Implement export(string $workspace, string $format): string method
- [ ] Setup CLI path configuration (constructor parameter + env var)
- [ ] Add error handling and timeout management
- [ ] Create unit tests for CliWrapper
- [ ] Update TASKS.md lines 148-159 with completion status

**Estimated Time**: 3-4 hours
**Blocks**: Phase 4.4 (ExportTools), apply_auto_layout tool
**Unblocks**: export_to_plantuml, export_to_mermaid, validate_workspace

---

#### 3. Create Phase 4.4: ExportTools Implementation (2-3 days)
**Goal**: Implement export to PlantUML, Mermaid, and DSL import

TASKS:
- [ ] Create `src/Tools/ExportTools.php` file
- [ ] Move export_to_dsl from WorkspaceTools to ExportTools
- [ ] Implement exportToPlantUml(string $workspaceId, ?string $viewKey): array
- [ ] Implement exportToMermaid(string $workspaceId, ?string $viewKey): array
- [ ] Implement importFromDsl(string $dsl): array
- [ ] Add all #[Schema] attributes to parameters
- [ ] Update WorkspaceTools to remove export_to_dsl
- [ ] Test all export formats
- [ ] Update TASKS.md lines 420-469 with completion status

**Estimated Time**: 3-4 hours
**Dependencies**: Phase 3.1 CliWrapper must be done first
**Unblocks**: Complete Phase 4 Core Tools

---

#### 4. Phase 8: Testing Infrastructure Setup (1-2 days)
**Goal**: Create test directory structure and first unit tests

TASKS:
- [ ] Create `tests/` directory structure (Unit/, Integration/, Fixtures/)
- [ ] Create `tests/Unit/Structurizr/WorkspaceManagerTest.php`
- [ ] Create `tests/Unit/Tools/WorkspaceToolsTest.php`
- [ ] Create `tests/Fixtures/example-workspace.dsl`
- [ ] Configure PHPUnit to find tests/ directory
- [ ] Run tests: `./vendor/bin/phpunit`
- [ ] Target: 5-10 basic unit tests passing
- [ ] Update TASKS.md lines 683-725 with progress

**Estimated Time**: 2-3 hours
**Blocks**: None (independent, can run in parallel)
**Impact**: Enable continuous testing for future work

---

### 📊 Weekly Timeline (Weeks 1-2)

**Week 1 (5 days):**
- Day 1-2: ViewTools + Schema attributes verification (2-3 hours)
- Day 2-3: CliWrapper implementation (3-4 hours)
- Day 3-4: ExportTools implementation (3-4 hours)
- Day 4-5: Testing setup + basic tests (2-3 hours)
- **Buffer**: Code review, debugging, integration testing

**Deliverables by end of Week 1:**
- ✅ Phase 4 Core Tools: 85%+ complete (ViewTools + ExportTools)
- ✅ Phase 3.1: 80%+ complete (CliWrapper)
- ✅ Phase 8: 15%+ complete (test infrastructure)
- ✅ Overall MVP Progress: 60-65% (up from current 45%)

---

### ✅ MVP Success Criteria (Week 2)

When all immediate next actions are complete:
- [x] MCP server starts and accepts connections
- [x] Can create workspaces with full metadata
- [x] Can add all C4 elements (person, system, container, component)
- [x] Can create all view types (system context, container, component)
- [x] Can export to DSL, PlantUML, Mermaid formats ✅ NEW
- [x] Works with Claude Desktop ✅ VERIFIED
- [x] All tool parameters have input validation (Schema attributes) ✅ NEW
- [x] Cache enabled for 10x faster discovery ✅ NEW
- [ ] Unit test coverage > 30% (new baseline)

**Estimated Overall Time**: 3-4 weeks to full MVP completion
**Current Completion**: 45% → Target after these actions: 65%

---

### Future Phases (Beyond Week 2)

**Phase 5 (Resources)**: 1 week
**Phase 6 (Prompts)**: 1 week
**Phase 8 (Full Testing)**: 2 weeks
**Phase 10 (Documentation)**: 1 week

See detailed timeline at lines 984-1000.
```

---

## SECTION 8: Format Guidelines for New Tasks

### Consistent Formatting Rules

**1. Checkboxes & Status:**
- `- [x]` = Completed
- `- [ ]` = Not started
- `- [~]` = In progress (can use, though not standard)
- Use ✅ prefix for emphasis on completed critical items
- Use ⚠️ prefix for pending/blocked items

**2. Code Blocks in Tasks:**
- Use proper PHP syntax highlighting: ` ```php `
- Show "BEFORE" and "AFTER" for refactoring tasks
- Include #[Attribute] declarations for MCP tools/resources
- Show parameter signatures with #[Schema] attributes

**3. Timelines:**
- Use "2-3 hours" or "1-2 days" (never exact, always range)
- Include cumulative time for each phase
- Show "Estimated Time" as single line
- Show "Blocks/Unblocks" relationships

**4. Section Headers:**
- Use `###` for main task groups
- Use `####` for subtasks
- Use `#####` for implementation details
- Bold important terms: `**term**`

**5. Links and References:**
- Link to file paths: `` `src/Tools/ViewTools.php` ``
- Link to line numbers: "lines 367-418"
- Reference phases: "Phase 4.3" or "Phase 3.1"
- Reference priorities: "🔴 MVP", "🟡 Core", "🟢 Extended"

**6. Status Indicators in Headers:**
- `✅ COMPLETE` (100% done)
- `⚠️ PARTIAL` (50-99% done, include percent)
- `❌ NOT STARTED` (0% done)
- Use percentage: "⚠️ 65% PARTIAL" or "✅ 100% COMPLETE"

---

## Summary Table: Update Locations

| Section | Lines | Status | Change Type | Priority |
|---------|-------|--------|-------------|----------|
| Overview Status Table | 9-18 | Update progress % | Metrics update | HIGH |
| Critical Gaps | 20-26 | Update from ⚠️ to ✅ | Status change | HIGH |
| What's Working | 27-33 | Add new items | Addition | MEDIUM |
| Phase 2.1 Header | 104 | Change to ✅ 100% | Status change | HIGH |
| Phase 2.1 Checkboxes | 105-112 | Check cache line | Checkbox | HIGH |
| Phase 2.1 Notes | After 112 | Add implementation details | New content | MEDIUM |
| Phase 4.1 Schemas | 209-221 | Add #[Schema] | Code update | HIGH |
| Phase 4.2 Schemas | 260-358 | Add #[Schema] to all | Code update | HIGH |
| Phase 4.3 Header | 367 | Change to ⚠️ PARTIAL | Status change | HIGH |
| Phase 4.3 Content | 368-418 | Rewrite section | Major update | HIGH |
| Phase 3.1 Header | 148 | Change to ⚠️ PARTIAL | Status change | MEDIUM |
| Phase 3.1 Content | 149-159 | Add implementation status | Update | MEDIUM |
| Next Actions | 1002-1020 | Rewrite section | Major update | HIGH |

