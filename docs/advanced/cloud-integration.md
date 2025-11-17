# Cloud Integration

- [Introduction](#introduction)
- [API Authentication](#api-authentication)
- [Push/Pull Operations](#pushpull-operations)
- [Sync Strategies](#sync-strategies)
- [Conflict Resolution](#conflict-resolution)
- [Offline Mode](#offline-mode)
- [Migration Guide](#migration-guide)

---

## Introduction

> **Status:** This feature is planned for a future release. This documentation describes the intended design and architecture for Structurizr Cloud integration.

Cloud integration will enable synchronization between local MCP workspaces and Structurizr Cloud, providing:
- **Centralized storage** - Share workspaces across teams
- **Version history** - Track changes over time
- **Collaboration** - Multiple users editing the same workspace
- **Backup** - Cloud-based workspace backup
- **Publishing** - Direct publishing to Structurizr Cloud for viewing

> **Current Status:** The server currently operates in local-only mode with file-based workspace storage.

---

## API Authentication

Cloud integration will use Structurizr's REST API with HMAC authentication.

### Configuration

Set up cloud credentials via environment variables:

```bash
# Structurizr Cloud API credentials
export STRUCTURIZR_API_KEY="your-api-key"
export STRUCTURIZR_API_SECRET="your-api-secret"
export STRUCTURIZR_API_URL="https://api.structurizr.com"

# Default workspace for operations
export STRUCTURIZR_WORKSPACE_ID="12345"
```

### HMAC Authentication

The server will implement HMAC-based authentication:

```php
<?php

namespace StructurizrMcp\Cloud;

class AuthenticationService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret
    ) {}

    public function generateAuthHeaders(
        string $method,
        string $path,
        string $body = ''
    ): array {
        $nonce = (string) time();
        $contentMd5 = base64_encode(md5($body, true));

        $message = implode("\n", [
            $method,
            $path,
            $contentMd5,
            'application/json',
            $nonce
        ]);

        $hmac = base64_encode(
            hash_hmac('sha256', $message, $this->apiSecret, true)
        );

        return [
            'X-Authorization' => "{$this->apiKey}:{$hmac}",
            'Nonce' => $nonce,
            'Content-MD5' => $contentMd5,
            'Content-Type' => 'application/json',
        ];
    }

    public function verifyCredentials(): bool
    {
        try {
            $client = new ApiClient($this->apiKey, $this->apiSecret);
            $response = $client->getWorkspace($this->getDefaultWorkspaceId());

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

### Credential Management

Secure credential storage and rotation:

```php
class CredentialManager
{
    public function rotateCredentials(
        string $oldApiKey,
        string $newApiKey,
        string $newApiSecret
    ): void {
        // Verify new credentials
        $auth = new AuthenticationService($newApiKey, $newApiSecret);

        if (!$auth->verifyCredentials()) {
            throw new AuthenticationException('New credentials are invalid');
        }

        // Update environment
        putenv("STRUCTURIZR_API_KEY={$newApiKey}");
        putenv("STRUCTURIZR_API_SECRET={$newApiSecret}");

        // Update configuration file if used
        $this->updateConfigFile($newApiKey, $newApiSecret);

        $this->logger->info('Credentials rotated successfully');
    }
}
```

---

## Push/Pull Operations

Synchronize workspaces between local storage and Structurizr Cloud.

### Push to Cloud

Upload local workspace to cloud:

```php
#[McpTool(
    name: 'push_to_cloud',
    description: 'Pushes a local workspace to Structurizr Cloud'
)]
public function pushToCloud(
    #[Schema(description: 'Local workspace ID')]
    string $workspaceId,

    #[Schema(description: 'Cloud workspace ID (optional, uses default if not specified)')]
    string $cloudWorkspaceId = '',

    #[Schema(description: 'Merge strategy: overwrite, merge, or cancel_if_conflict')]
    string $mergeStrategy = 'cancel_if_conflict'
): array {
    // Load local workspace
    $workspace = $this->workspaceManager->getWorkspace($workspaceId);

    if ($workspace === null) {
        throw new WorkspaceNotFoundException("Workspace '{$workspaceId}' not found");
    }

    // Get cloud workspace ID
    $cloudId = $cloudWorkspaceId ?: $this->config->getDefaultCloudWorkspaceId();

    // Check for conflicts
    $cloudWorkspace = $this->cloudClient->getWorkspace($cloudId);
    $conflict = $this->detectConflict($workspace, $cloudWorkspace);

    if ($conflict && $mergeStrategy === 'cancel_if_conflict') {
        return [
            'success' => false,
            'conflict' => true,
            'message' => 'Cloud workspace has been modified. Pull changes first or use merge strategy.'
        ];
    }

    // Apply merge strategy
    $mergedWorkspace = match($mergeStrategy) {
        'overwrite' => $workspace,
        'merge' => $this->mergeWorkspaces($workspace, $cloudWorkspace),
        default => throw new \InvalidArgumentException("Unknown merge strategy: {$mergeStrategy}")
    };

    // Push to cloud
    $result = $this->cloudClient->putWorkspace($cloudId, $mergedWorkspace);

    // Update local workspace with cloud version info
    $workspace->setCloudVersion($result['version']);
    $workspace->setLastSyncTime(time());
    $this->workspaceManager->saveWorkspace($workspace);

    return [
        'success' => true,
        'workspaceId' => $workspaceId,
        'cloudWorkspaceId' => $cloudId,
        'version' => $result['version'],
        'message' => 'Workspace pushed to cloud successfully'
    ];
}
```

### Pull from Cloud

Download workspace from cloud to local storage:

```php
#[McpTool(
    name: 'pull_from_cloud',
    description: 'Pulls a workspace from Structurizr Cloud to local storage'
)]
public function pullFromCloud(
    #[Schema(description: 'Cloud workspace ID')]
    string $cloudWorkspaceId,

    #[Schema(description: 'Local workspace ID (creates new if not exists)')]
    string $localWorkspaceId = '',

    #[Schema(description: 'Overwrite local changes')]
    bool $force = false
): array {
    // Check if local workspace exists
    $localId = $localWorkspaceId ?: $cloudWorkspaceId;
    $localWorkspace = $this->workspaceManager->getWorkspace($localId);

    // Check for local modifications
    if ($localWorkspace && $localWorkspace->isModified() && !$force) {
        return [
            'success' => false,
            'modified' => true,
            'message' => 'Local workspace has uncommitted changes. Use force=true to overwrite.'
        ];
    }

    // Pull from cloud
    $cloudWorkspace = $this->cloudClient->getWorkspace($cloudWorkspaceId);

    // Save locally
    $workspace = $this->workspaceManager->createOrUpdateWorkspace(
        $localId,
        $cloudWorkspace
    );

    $workspace->setCloudWorkspaceId($cloudWorkspaceId);
    $workspace->setCloudVersion($cloudWorkspace['version'] ?? 0);
    $workspace->setLastSyncTime(time());
    $this->workspaceManager->saveWorkspace($workspace);

    return [
        'success' => true,
        'localWorkspaceId' => $localId,
        'cloudWorkspaceId' => $cloudWorkspaceId,
        'version' => $cloudWorkspace['version'] ?? 0,
        'message' => 'Workspace pulled from cloud successfully'
    ];
}
```

### Sync Status

Check synchronization status:

```php
#[McpTool(
    name: 'sync_status',
    description: 'Checks synchronization status between local and cloud workspaces'
)]
public function syncStatus(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId
): array {
    $workspace = $this->getWorkspace($workspaceId);
    $cloudId = $workspace->getCloudWorkspaceId();

    if (!$cloudId) {
        return [
            'synced' => false,
            'message' => 'Workspace is not linked to cloud'
        ];
    }

    try {
        $cloudWorkspace = $this->cloudClient->getWorkspace($cloudId);

        $localVersion = $workspace->getCloudVersion();
        $cloudVersion = $cloudWorkspace['version'] ?? 0;
        $lastSync = $workspace->getLastSyncTime();

        return [
            'synced' => $localVersion === $cloudVersion,
            'localVersion' => $localVersion,
            'cloudVersion' => $cloudVersion,
            'lastSync' => date('Y-m-d H:i:s', $lastSync),
            'status' => match(true) {
                $localVersion === $cloudVersion => 'up_to_date',
                $localVersion > $cloudVersion => 'local_ahead',
                $localVersion < $cloudVersion => 'cloud_ahead',
            },
            'needsPush' => $workspace->isModified(),
            'needsPull' => $cloudVersion > $localVersion,
        ];
    } catch (ApiException $e) {
        return [
            'synced' => false,
            'error' => $e->getMessage(),
            'message' => 'Failed to check cloud workspace status'
        ];
    }
}
```

---

## Sync Strategies

Different synchronization strategies for various use cases.

### Manual Sync

User explicitly triggers push/pull operations:

```php
class ManualSyncStrategy implements SyncStrategyInterface
{
    public function shouldSync(Workspace $workspace): bool
    {
        // Only sync when explicitly requested
        return false;
    }

    public function sync(Workspace $workspace): SyncResult
    {
        throw new \LogicException('Manual sync requires explicit user action');
    }
}
```

### Auto-Pull on Startup

Automatically pull latest version on server startup:

```php
class AutoPullStrategy implements SyncStrategyInterface
{
    public function onServerStartup(): void
    {
        $workspaces = $this->workspaceManager->listWorkspaces();

        foreach ($workspaces as $workspace) {
            if ($workspace->hasCloudLink()) {
                try {
                    $this->pullFromCloud($workspace->getId());
                    $this->logger->info("Pulled workspace: {$workspace->getId()}");
                } catch (\Exception $e) {
                    $this->logger->warning(
                        "Failed to pull workspace {$workspace->getId()}: {$e->getMessage()}"
                    );
                }
            }
        }
    }
}
```

### Auto-Push on Save

Automatically push to cloud after local modifications:

```php
class AutoPushStrategy implements SyncStrategyInterface
{
    public function afterWorkspaceSave(Workspace $workspace): void
    {
        if (!$workspace->hasCloudLink()) {
            return;
        }

        try {
            $this->pushToCloud(
                $workspace->getId(),
                $workspace->getCloudWorkspaceId(),
                mergeStrategy: 'merge'
            );

            $this->logger->info("Auto-pushed workspace: {$workspace->getId()}");
        } catch (\Exception $e) {
            $this->logger->error(
                "Failed to auto-push workspace {$workspace->getId()}: {$e->getMessage()}"
            );

            // Queue for retry
            $this->syncQueue->add($workspace->getId());
        }
    }
}
```

### Periodic Sync

Sync at regular intervals:

```php
class PeriodicSyncStrategy implements SyncStrategyInterface
{
    private int $intervalSeconds = 300; // 5 minutes

    public function startPeriodicSync(): void
    {
        while (true) {
            $this->syncAllWorkspaces();
            sleep($this->intervalSeconds);
        }
    }

    private function syncAllWorkspaces(): void
    {
        $workspaces = $this->workspaceManager->listWorkspaces();

        foreach ($workspaces as $workspace) {
            if (!$workspace->hasCloudLink()) {
                continue;
            }

            try {
                $status = $this->syncStatus($workspace->getId());

                if ($status['needsPush']) {
                    $this->pushToCloud($workspace->getId());
                } elseif ($status['needsPull']) {
                    $this->pullFromCloud($workspace->getCloudWorkspaceId());
                }
            } catch (\Exception $e) {
                $this->logger->error(
                    "Periodic sync failed for {$workspace->getId()}: {$e->getMessage()}"
                );
            }
        }
    }
}
```

---

## Conflict Resolution

Handle conflicts when local and cloud versions diverge.

### Conflict Detection

Detect if workspace has been modified in both locations:

```php
class ConflictDetector
{
    public function detectConflict(
        Workspace $localWorkspace,
        array $cloudWorkspace
    ): ?Conflict {
        $localVersion = $localWorkspace->getCloudVersion();
        $cloudVersion = $cloudWorkspace['version'] ?? 0;
        $lastSync = $localWorkspace->getLastSyncTime();

        // No conflict if versions match
        if ($localVersion === $cloudVersion) {
            return null;
        }

        // Conflict if both have been modified since last sync
        if ($localWorkspace->isModifiedSince($lastSync) && $cloudVersion > $localVersion) {
            return new Conflict(
                localWorkspace: $localWorkspace,
                cloudWorkspace: $cloudWorkspace,
                conflictType: ConflictType::BOTH_MODIFIED
            );
        }

        return null;
    }

    public function findConflictingElements(
        Workspace $localWorkspace,
        array $cloudWorkspace
    ): array {
        $conflicts = [];

        $localElements = $this->indexElements($localWorkspace->getElements());
        $cloudElements = $this->indexElements($cloudWorkspace['model']['elements'] ?? []);

        foreach ($localElements as $id => $localElement) {
            if (!isset($cloudElements[$id])) {
                $conflicts[] = new ElementConflict(
                    elementId: $id,
                    type: ConflictType::DELETED_IN_CLOUD,
                    localVersion: $localElement,
                    cloudVersion: null
                );
                continue;
            }

            $cloudElement = $cloudElements[$id];

            if ($this->elementsAreDifferent($localElement, $cloudElement)) {
                $conflicts[] = new ElementConflict(
                    elementId: $id,
                    type: ConflictType::MODIFIED_IN_BOTH,
                    localVersion: $localElement,
                    cloudVersion: $cloudElement
                );
            }
        }

        return $conflicts;
    }
}
```

### Resolution Strategies

Different strategies for resolving conflicts:

```php
interface ConflictResolutionStrategy
{
    public function resolve(Conflict $conflict): Workspace;
}

class KeepLocalStrategy implements ConflictResolutionStrategy
{
    public function resolve(Conflict $conflict): Workspace
    {
        // Keep all local changes, discard cloud changes
        return $conflict->getLocalWorkspace();
    }
}

class KeepCloudStrategy implements ConflictResolutionStrategy
{
    public function resolve(Conflict $conflict): Workspace
    {
        // Keep all cloud changes, discard local changes
        return Workspace::fromArray($conflict->getCloudWorkspace());
    }
}

class MergeStrategy implements ConflictResolutionStrategy
{
    public function resolve(Conflict $conflict): Workspace
    {
        $local = $conflict->getLocalWorkspace();
        $cloud = $conflict->getCloudWorkspace();

        // Three-way merge based on common ancestor
        $merged = new Workspace(
            id: $local->getId(),
            name: $cloud['name'], // Prefer cloud for metadata
            description: $cloud['description']
        );

        // Merge elements (union of both, cloud wins on conflicts)
        $elements = $this->mergeElements(
            $local->getElements(),
            $cloud['model']['elements'] ?? []
        );

        $merged->setElements($elements);

        // Merge relationships
        $relationships = $this->mergeRelationships(
            $local->getRelationships(),
            $cloud['model']['relationships'] ?? []
        );

        $merged->setRelationships($relationships);

        // Merge views (keep both)
        $views = array_merge(
            $local->getViews(),
            $cloud['views'] ?? []
        );

        $merged->setViews($this->deduplicateViews($views));

        return $merged;
    }

    private function mergeElements(array $localElements, array $cloudElements): array
    {
        $merged = [];

        // Index by ID
        $localById = $this->indexById($localElements);
        $cloudById = $this->indexById($cloudElements);

        // All IDs from both sides
        $allIds = array_unique([
            ...array_keys($localById),
            ...array_keys($cloudById)
        ]);

        foreach ($allIds as $id) {
            if (isset($cloudById[$id])) {
                // Cloud version wins in conflicts
                $merged[$id] = $cloudById[$id];
            } else {
                // Local-only element
                $merged[$id] = $localById[$id];
            }
        }

        return array_values($merged);
    }
}
```

### Interactive Resolution

Let user choose how to resolve conflicts:

```php
#[McpTool(
    name: 'resolve_conflict',
    description: 'Resolves synchronization conflicts between local and cloud workspaces'
)]
public function resolveConflict(
    #[Schema(description: 'Workspace ID')]
    string $workspaceId,

    #[Schema(
        description: 'Resolution strategy',
        enum: ['keep_local', 'keep_cloud', 'merge', 'interactive']
    )]
    string $strategy = 'interactive'
): array {
    $workspace = $this->getWorkspace($workspaceId);
    $cloudWorkspace = $this->cloudClient->getWorkspace(
        $workspace->getCloudWorkspaceId()
    );

    $conflict = $this->conflictDetector->detectConflict($workspace, $cloudWorkspace);

    if (!$conflict) {
        return [
            'success' => true,
            'conflict' => false,
            'message' => 'No conflicts detected'
        ];
    }

    if ($strategy === 'interactive') {
        $conflicts = $this->conflictDetector->findConflictingElements(
            $workspace,
            $cloudWorkspace
        );

        return [
            'success' => false,
            'conflict' => true,
            'conflicts' => $conflicts,
            'message' => 'Conflicts require user resolution',
            'nextAction' => 'Use resolve_conflict with specific strategy'
        ];
    }

    // Apply automatic resolution strategy
    $resolver = match($strategy) {
        'keep_local' => new KeepLocalStrategy(),
        'keep_cloud' => new KeepCloudStrategy(),
        'merge' => new MergeStrategy(),
    };

    $resolved = $resolver->resolve($conflict);

    // Save resolved workspace and push to cloud
    $this->workspaceManager->saveWorkspace($resolved);
    $this->pushToCloud($workspaceId, force: true);

    return [
        'success' => true,
        'strategy' => $strategy,
        'message' => 'Conflict resolved successfully'
    ];
}
```

---

## Offline Mode

Gracefully handle scenarios when cloud is unavailable.

### Offline Detection

```php
class OfflineDetector
{
    private const HEALTH_CHECK_INTERVAL = 60; // seconds
    private bool $isOnline = true;
    private int $lastCheck = 0;

    public function isOnline(): bool
    {
        if (time() - $this->lastCheck < self::HEALTH_CHECK_INTERVAL) {
            return $this->isOnline;
        }

        try {
            $this->isOnline = $this->cloudClient->healthCheck();
        } catch (\Exception $e) {
            $this->isOnline = false;
        }

        $this->lastCheck = time();

        return $this->isOnline;
    }
}
```

### Offline Queue

Queue operations for later execution when back online:

```php
class OfflineQueue
{
    private array $queue = [];

    public function enqueue(Operation $operation): void
    {
        $this->queue[] = [
            'operation' => $operation,
            'timestamp' => time(),
            'retries' => 0,
        ];

        $this->persistQueue();
    }

    public function processQueue(): array
    {
        $results = [];

        foreach ($this->queue as $index => $item) {
            try {
                $result = $item['operation']->execute();
                $results[] = ['success' => true, 'operation' => $item['operation']];

                // Remove from queue
                unset($this->queue[$index]);
            } catch (\Exception $e) {
                $item['retries']++;

                if ($item['retries'] >= 3) {
                    $results[] = [
                        'success' => false,
                        'operation' => $item['operation'],
                        'error' => $e->getMessage()
                    ];

                    unset($this->queue[$index]);
                } else {
                    $this->queue[$index] = $item;
                }
            }
        }

        $this->queue = array_values($this->queue);
        $this->persistQueue();

        return $results;
    }
}
```

---

## Migration Guide

Guide for migrating from local-only to cloud-synchronized workspaces.

### Initial Setup

```bash
# 1. Set up cloud credentials
export STRUCTURIZR_API_KEY="your-key"
export STRUCTURIZR_API_SECRET="your-secret"

# 2. Verify credentials
php bin/console cloud:verify-credentials

# 3. Create cloud workspace (if needed)
# Visit https://structurizr.com and create a workspace

# 4. Link local workspace to cloud
php bin/console cloud:link local-workspace-id cloud-workspace-id
```

### Migration Tool

```php
#[McpTool(
    name: 'migrate_to_cloud',
    description: 'Migrates a local workspace to Structurizr Cloud'
)]
public function migrateToCloud(
    #[Schema(description: 'Local workspace ID')]
    string $workspaceId,

    #[Schema(description: 'Create new cloud workspace or use existing')]
    bool $createNew = true,

    #[Schema(description: 'Existing cloud workspace ID (if createNew=false)')]
    string $cloudWorkspaceId = ''
): array {
    $workspace = $this->getWorkspace($workspaceId);

    if ($createNew) {
        // Create new cloud workspace
        $cloudId = $this->cloudClient->createWorkspace(
            $workspace->getName(),
            $workspace->getDescription()
        );
    } else {
        $cloudId = $cloudWorkspaceId;
    }

    // Initial push
    $this->pushToCloud($workspaceId, $cloudId, mergeStrategy: 'overwrite');

    // Link workspace
    $workspace->setCloudWorkspaceId($cloudId);
    $this->workspaceManager->saveWorkspace($workspace);

    return [
        'success' => true,
        'workspaceId' => $workspaceId,
        'cloudWorkspaceId' => $cloudId,
        'message' => 'Workspace migrated to cloud successfully',
        'url' => "https://structurizr.com/workspace/{$cloudId}"
    ];
}
```

---

<p align="right">
  <strong>Next:</strong> <a href="batch-operations.md">Batch Operations →</a>
</p>
