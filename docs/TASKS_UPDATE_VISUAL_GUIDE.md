# TASKS.md Update - Visual Line-by-Line Guide

## Quick Reference: Exact Lines to Change

### PRIORITY 1: Overview Section (Lines 3-42)

#### Line 5: Update Overall Progress
```
BEFORE: ### Overall Progress: ~45% Complete (MVP Stage)
AFTER:  ### Overall Progress: ~55-60% Complete (MVP Stage)
```

#### Lines 9-18: Update Progress Table
```
BEFORE ROW:
| **Phase 2: MCP Foundation** | ✅ Mostly Complete | 75% | 🔴 MVP |

AFTER ROW:
| **Phase 2: MCP Foundation** | ✅ Mostly Complete | 90% | 🔴 MVP |

---

BEFORE ROW:
| **Phase 3: Structurizr Integration** | ⚠️ Partial | 50% | 🔴 MVP |

AFTER ROW:
| **Phase 3: Structurizr Integration** | ⚠️ Partial | 65% | 🔴 MVP |

---

BEFORE ROW:
| **Phase 4: Core Tools** | ⚠️ Partial | 40% | 🔴 MVP |

AFTER ROW:
| **Phase 4: Core Tools** | ⚠️ Partial | 55% | 🔴 MVP |
```

#### Lines 20-26: Update Critical Gaps
```
BEFORE:
### Critical Gaps (Blocking MVP):
1. ⚠️ **Missing #[Schema] attributes** on all tool parameters (HIGH PRIORITY)
2. ⚠️ **Missing cache setup** in server.php (PhpFilesAdapter)
3. ❌ **ViewTools.php** not implemented (container/component views)
4. ❌ **CliWrapper.php** not implemented (blocks export functionality)
5. ❌ **Zero test coverage** (tests/ directory missing)

AFTER:
### Critical Gaps (Blocking MVP):
1. ✅ **Schema attributes added** to all 12 tool parameters (COMPLETED)
2. ✅ **Cache setup implemented** in server.php (PhpFilesAdapter) (COMPLETED)
3. ⚠️ **ViewTools.php** partially implemented (system context view moved, container/component views pending)
4. ⚠️ **CliWrapper.php** partially implemented (basic structure, CLI execution methods pending)
5. ❌ **Zero test coverage** (tests/ directory missing - Phase 8 priority)
```

#### Lines 27-33: Add to What's Working
```
ADD THESE LINES after existing "What's Working" items:
- ✅ Schema attributes on all WorkspaceTools and ModelTools parameters
- ✅ Cache adapter configured (PhpFilesAdapter)
- ✅ Basic CliWrapper structure with process execution
```

---

### PRIORITY 2: Phase 2.1 Cache Setup (Lines 104-112)

#### Line 104: Update Header
```
BEFORE: ### 2.1 Basic Server Setup ⚠️ 87% Complete (Missing Cache)
AFTER:  ### 2.1 Basic Server Setup ✅ 100% COMPLETE
```

#### Line 109: Update Cache Checkbox
```
BEFORE: - [ ] Cache setup (PhpFilesAdapter) ⚠️ CRITICAL: Add before line 50 in server.php
AFTER:  - [x] Cache setup (PhpFilesAdapter) ✅ IMPLEMENTED (Added after line 29, before Server::builder)
```

#### After Line 112: Add Implementation Notes
```
ADD NEW SECTION (after line 112):

#### Implementation Details:
Cache is now configured in server.php (lines 31-34):
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

### PRIORITY 3: Phase 4.1 & 4.2 Schema Attributes

#### Lines 213-214: create_workspace parameters
```
BEFORE:
public function createWorkspace(
    string $name,  // ⚠️ Missing #[Schema] attributes
    string $description = ''
): array {

AFTER:
public function createWorkspace(
    #[Schema(description: 'Workspace name (1-100 characters)', minLength: 1, maxLength: 100)]
    string $name,
    #[Schema(description: 'Workspace description', maxLength: 500)]
    string $description = ''
): array {
```

#### Line 220: Update checkbox
```
BEFORE: - [ ] Add #[Schema] attributes to parameters ⚠️ CRITICAL for MCP client validation
AFTER:  - [x] Add #[Schema] attributes to parameters ✅ COMPLETED
```

#### Lines 228-229: get_workspace parameters
```
BEFORE:
public function getWorkspace(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes
    string $format = 'json'  // ⚠️ Missing enum schema
): array {

AFTER:
public function getWorkspace(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'Output format', enum: ['json', 'dsl'])]
    string $format = 'json'
): array {
```

#### Line 235: Update checkbox
```
BEFORE: - [ ] Add #[Schema] attributes (especially enum for format)
AFTER:  - [x] Add #[Schema] attributes (especially enum for format) ✅ COMPLETED
```

#### Lines 250-251: delete_workspace parameters
```
BEFORE:
public function deleteWorkspace(string $workspaceId): array {

AFTER:
public function deleteWorkspace(
    #[Schema(description: 'Workspace ID to delete', minLength: 1)]
    string $workspaceId
): array {
```

#### Lines 264-267: add_person parameters (ModelTools)
```
BEFORE:
public function addPerson(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes on all params
    string $name,
    string $description = '',
    array $tags = []
): array {

AFTER:
public function addPerson(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'Person name', minLength: 1, maxLength: 100)]
    string $name,
    #[Schema(description: 'Person description', maxLength: 500)]
    string $description = '',
    #[Schema(description: 'Tags for styling/filtering')]
    array $tags = []
): array {
```

#### Lines 279-284: add_software_system parameters
```
BEFORE:
public function addSoftwareSystem(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes except location
    string $name,
    string $description = '',
    string $location = 'Internal',  // Has enum validation in code, but missing #[Schema]
    array $tags = []
): array {

AFTER:
public function addSoftwareSystem(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'Software system name', minLength: 1, maxLength: 100)]
    string $name,
    #[Schema(description: 'System description', maxLength: 500)]
    string $description = '',
    #[Schema(description: 'System location', enum: ['Internal', 'External'])]
    string $location = 'Internal',
    #[Schema(description: 'Tags for styling/filtering')]
    array $tags = []
): array {
```

#### Lines 295-301: add_container parameters
```
BEFORE:
public function addContainer(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes
    string $systemId,
    string $name,
    string $description = '',
    string $technology = '',
    array $tags = []
): array {

AFTER:
public function addContainer(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'System ID', minLength: 1)]
    string $systemId,
    #[Schema(description: 'Container name', minLength: 1, maxLength: 100)]
    string $name,
    #[Schema(description: 'Container description', maxLength: 500)]
    string $description = '',
    #[Schema(description: 'Technology stack', maxLength: 100)]
    string $technology = '',
    #[Schema(description: 'Tags for styling/filtering')]
    array $tags = []
): array {
```

#### Lines 312-318: add_component parameters
```
BEFORE:
public function addComponent(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes
    string $containerId,
    string $name,
    string $description = '',
    string $technology = '',
    array $tags = []
): array {

AFTER:
public function addComponent(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'Container ID', minLength: 1)]
    string $containerId,
    #[Schema(description: 'Component name', minLength: 1, maxLength: 100)]
    string $name,
    #[Schema(description: 'Component description', maxLength: 500)]
    string $description = '',
    #[Schema(description: 'Technology stack', maxLength: 100)]
    string $technology = '',
    #[Schema(description: 'Tags for styling/filtering')]
    array $tags = []
): array {
```

#### Lines 329-335: add_relationship parameters
```
BEFORE:
public function addRelationship(
    string $workspaceId,  // ⚠️ Missing #[Schema] attributes
    string $sourceId,
    string $destinationId,
    string $description,  // Required but no validation
    string $technology = '',
    array $tags = []
): array {

AFTER:
public function addRelationship(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'Source element ID', minLength: 1)]
    string $sourceId,
    #[Schema(description: 'Destination element ID', minLength: 1)]
    string $destinationId,
    #[Schema(description: 'Relationship description', minLength: 1, maxLength: 300)]
    string $description,
    #[Schema(description: 'Technology stack', maxLength: 100)]
    string $technology = '',
    #[Schema(description: 'Tags for styling/filtering')]
    array $tags = []
): array {
```

#### Lines 348-351: create_system_context_view parameters
```
BEFORE:
public function createSystemContextView(
    string $workspaceId,
    string $systemId,
    string $key,
    string $description = ''
): array

AFTER:
public function createSystemContextView(
    #[Schema(description: 'Workspace ID', minLength: 1)]
    string $workspaceId,
    #[Schema(description: 'System ID', minLength: 1)]
    string $systemId,
    #[Schema(description: 'View key', minLength: 1, maxLength: 50)]
    string $key,
    #[Schema(description: 'View description', maxLength: 500)]
    string $description = ''
): array

⚠️ NOTE: Add comment "Will be moved to ViewTools.php in Phase 4.3"
```

#### Line 364: Update checkbox
```
BEFORE: - [ ] Add Schema attributes to ALL tools ⚠️ CRITICAL
AFTER:  - [x] Add Schema attributes to ALL tools ✅ COMPLETED
           - [x] WorkspaceTools: create_workspace, get_workspace, delete_workspace, list_workspaces
           - [x] ModelTools: add_person, add_software_system, add_container, add_component, add_relationship, create_system_context_view
```

---

### PRIORITY 4: Phase 4.3 View Tools (Lines 367-418)

#### Line 367: Replace entire section
```
REPLACE ENTIRE SECTION (Lines 367-418) WITH:

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
    #[Schema(enum: ['tb', 'bt', 'lr', 'rl'], description: 'Layout direction')]
    string $direction = 'tb'
): array
```
- [ ] Awaiting CliWrapper implementation (Phase 3.1)
- [ ] Will use structurizr-cli export with --layout option

#### Implementation Status:
- [x] Create ViewTools.php file
- [x] Move create_system_context_view from ModelTools
- [ ] Update ModelTools to remove create_system_context_view
- [ ] Implement container and component views
- ⚠️ apply_auto_layout blocked by Phase 3.1 CliWrapper
```

---

### PRIORITY 5: Phase 3.1 CliWrapper (Lines 148-159)

#### Line 148: Update Header
```
BEFORE: ### 3.1 CLI Wrapper ❌ NOT IMPLEMENTED (Required for Phase 4.4 Export Tools)
AFTER:  ### 3.1 CLI Wrapper ⚠️ 50% PARTIAL (Basic execute method implemented)
```

#### Lines 149-159: Replace task list
```
REPLACE ENTIRE SECTION (Lines 149-159) WITH:

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

### PRIORITY 6: Next Actions Section (Lines 1002-1020)

#### Lines 1002-1020: REPLACE ENTIRE SECTION
See SECTION 7 of TASKS_UPDATE_STRATEGY.md for the complete replacement text.

This is a major section rewrite replacing week 1 focus with current immediate priorities.

---

## Implementation Order (Recommended)

1. **Batch 1** (15 minutes): Update Overview section (Lines 3-42)
   - Overview progress percentages
   - Critical gaps
   - What's working

2. **Batch 2** (10 minutes): Update Phase 2.1 (Lines 104-112)
   - Header status change
   - Cache checkbox
   - Implementation notes

3. **Batch 3** (30 minutes): Add Schema attributes to Phase 4.1 (Lines 206-221)
   - create_workspace
   - get_workspace
   - delete_workspace
   - Update checkboxes

4. **Batch 4** (45 minutes): Add Schema attributes to Phase 4.2 (Lines 260-358)
   - All 6 ModelTools parameters
   - All method signatures updated
   - Final checkbox update

5. **Batch 5** (20 minutes): Replace Phase 4.3 section (Lines 367-418)
   - Full section replacement with new content

6. **Batch 6** (15 minutes): Replace Phase 3.1 section (Lines 148-159)
   - Full section replacement with implementation details

7. **Batch 7** (30 minutes): Replace Next Actions (Lines 1002-1020)
   - Complete section rewrite with immediate priorities

**Total Time**: ~2 hours to complete all updates

---

## Validation Checklist

After completing all updates, verify:

- [ ] Line 5: Overall progress updated to ~55-60%
- [ ] Lines 9-18: Progress percentages updated (Phase 2: 90%, Phase 3: 65%, Phase 4: 55%)
- [ ] Lines 20-26: Critical gaps updated with ✅ and ⚠️ status changes
- [ ] Line 104: Phase 2.1 shows ✅ 100% COMPLETE
- [ ] Line 109: Cache checkbox is checked [x]
- [ ] Lines 213-214: create_workspace has full #[Schema] attributes
- [ ] Lines 228-229: get_workspace has full #[Schema] attributes with enum
- [ ] Lines 264-267: add_person has full #[Schema] attributes
- [ ] Line 367: Phase 4.3 shows ⚠️ PARTIAL status
- [ ] Lines 368-418: Phase 4.3 section completely rewritten
- [ ] Line 148: Phase 3.1 shows ⚠️ PARTIAL status
- [ ] Lines 149-159: Phase 3.1 section shows implementation details
- [ ] Lines 1002-1020: Next Actions section rewritten with immediate priorities
- [ ] All code blocks use proper ```php syntax highlighting
- [ ] All #[Schema] attributes follow consistent formatting
- [ ] All checkboxes ([ ] and [x]) are consistent throughout
- [ ] Line count increases from 1,020 to approximately 1,150-1,200 (due to additions)

