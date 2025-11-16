<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr;

use StructurizrMcp\Exception\WorkspaceNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;

/**
 * Manages local workspace storage and retrieval
 */
class WorkspaceManager
{
    private Filesystem $filesystem;

    public function __construct(
        private readonly string $storagePath,
        private readonly LoggerInterface $logger
    ) {
        $this->filesystem = new Filesystem();

        // Ensure storage directory exists
        if (!is_dir($this->storagePath)) {
            $this->filesystem->mkdir($this->storagePath, 0755);
            $this->logger->info("Created workspace storage directory: {$this->storagePath}");
        }
    }

    /**
     * Create a new workspace
     */
    public function create(string $name, string $description = ''): Workspace
    {
        $id = $this->generateWorkspaceId();
        $now = new \DateTimeImmutable();

        $workspace = new Workspace(
            id: $id,
            name: $name,
            description: $description,
            model: [],
            views: [],
            dsl: '',
            createdAt: $now,
            updatedAt: $now,
        );

        $this->save($workspace);
        $this->logger->info("Created workspace: {$id} - {$name}");

        return $workspace;
    }

    /**
     * Load a workspace by ID
     */
    public function load(string $id): Workspace
    {
        $filepath = $this->getWorkspacePath($id);

        if (!file_exists($filepath)) {
            throw new WorkspaceNotFoundException($id);
        }

        $content = file_get_contents($filepath);
        if ($content === false) {
            throw new \RuntimeException("Failed to read workspace file: {$id}");
        }

        $data = json_decode($content, true);

        if ($data === null) {
            throw new \RuntimeException("Failed to parse workspace JSON: {$id}");
        }

        $this->logger->debug("Loaded workspace: {$id}");

        return Workspace::fromArray($data);
    }

    /**
     * Save a workspace
     */
    public function save(Workspace $workspace): void
    {
        $filepath = $this->getWorkspacePath($workspace->id);
        $data = json_encode($workspace->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($data === false) {
            throw new \RuntimeException("Failed to encode workspace: {$workspace->id}");
        }

        file_put_contents($filepath, $data);
        $this->logger->debug("Saved workspace: {$workspace->id}");
    }

    /**
     * Delete a workspace
     */
    public function delete(string $id): void
    {
        $filepath = $this->getWorkspacePath($id);

        if (!file_exists($filepath)) {
            throw new WorkspaceNotFoundException($id);
        }

        unlink($filepath);
        $this->logger->info("Deleted workspace: {$id}");
    }

    /**
     * List all workspaces
     */
    public function list(): array
    {
        $workspaces = [];
        $files = glob($this->storagePath . '/*.json');

        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            try {
                $content = file_get_contents($file);
                if ($content === false) {
                    continue;
                }

                $data = json_decode($content, true);
                if ($data !== null && isset($data['id'])) {
                    $workspaces[] = [
                        'id' => $data['id'],
                        'name' => $data['name'] ?? '',
                        'description' => $data['description'] ?? '',
                        'createdAt' => $data['createdAt'] ?? null,
                        'updatedAt' => $data['updatedAt'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                $this->logger->warning("Failed to read workspace file: {$file}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $workspaces;
    }

    /**
     * Check if a workspace exists
     */
    public function exists(string $id): bool
    {
        return file_exists($this->getWorkspacePath($id));
    }

    /**
     * Update workspace DSL
     */
    public function updateDsl(string $id, string $dsl): Workspace
    {
        $workspace = $this->load($id);
        $updated = $workspace->withDsl($dsl);
        $this->save($updated);

        $this->logger->info("Updated DSL for workspace: {$id}");

        return $updated;
    }

    /**
     * Get workspace file path
     */
    private function getWorkspacePath(string $id): string
    {
        // Sanitize workspace ID to prevent directory traversal
        $sanitizedId = preg_replace('/[^a-zA-Z0-9_-]/', '', $id);
        return $this->storagePath . '/' . $sanitizedId . '.json';
    }

    /**
     * Generate unique workspace ID
     */
    private function generateWorkspaceId(): string
    {
        do {
            $id = 'ws_' . bin2hex(random_bytes(8));
        } while ($this->exists($id));

        return $id;
    }
}
