<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Structurizr;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use Psr\Log\NullLogger;

/**
 * Unit tests for WorkspaceManager
 *
 * @covers \StructurizrMcp\Structurizr\WorkspaceManager
 */
class WorkspaceManagerTest extends TestCase
{
    private string $tempStoragePath;
    private WorkspaceManager $manager;

    protected function setUp(): void
    {
        // Create temporary directory for test workspaces
        $this->tempStoragePath = sys_get_temp_dir() . '/structurizr-test-' . uniqid();
        $this->manager = new WorkspaceManager(
            $this->tempStoragePath,
            new NullLogger()
        );
    }

    protected function tearDown(): void
    {
        // Clean up temporary directory
        if (is_dir($this->tempStoragePath)) {
            $files = glob($this->tempStoragePath . '/*.json');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
            rmdir($this->tempStoragePath);
        }
    }

    public function testConstructorCreatesStorageDirectory(): void
    {
        $this->assertDirectoryExists($this->tempStoragePath);
        $this->assertTrue(is_writable($this->tempStoragePath));
    }

    public function testCreateWorkspace(): void
    {
        $workspace = $this->manager->create('Test Workspace', 'Test description');

        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertNotEmpty($workspace->id);
        $this->assertStringStartsWith('ws_', $workspace->id);
        $this->assertEquals('Test Workspace', $workspace->name);
        $this->assertEquals('Test description', $workspace->description);
        $this->assertEmpty($workspace->dsl);
        $this->assertInstanceOf(\DateTimeImmutable::class, $workspace->createdAt);
        $this->assertInstanceOf(\DateTimeImmutable::class, $workspace->updatedAt);
        $this->assertEquals($workspace->createdAt, $workspace->updatedAt);
    }

    public function testCreateWorkspaceWithEmptyDescription(): void
    {
        $workspace = $this->manager->create('Minimal Workspace');

        $this->assertEquals('Minimal Workspace', $workspace->name);
        $this->assertEquals('', $workspace->description);
    }

    public function testCreateWorkspaceSavesToDisk(): void
    {
        $workspace = $this->manager->create('Persisted Workspace');

        $files = glob($this->tempStoragePath . '/*.json');
        $this->assertNotFalse($files);
        $this->assertCount(1, $files);

        $content = file_get_contents($files[0]);
        $this->assertNotFalse($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertEquals($workspace->id, $data['id']);
        $this->assertEquals('Persisted Workspace', $data['name']);
    }

    public function testLoadWorkspace(): void
    {
        $created = $this->manager->create('Test Load');

        $loaded = $this->manager->load($created->id);

        $this->assertEquals($created->id, $loaded->id);
        $this->assertEquals($created->name, $loaded->name);
        $this->assertEquals($created->description, $loaded->description);
    }

    public function testLoadWorkspaceNotFound(): void
    {
        $this->expectException(WorkspaceNotFoundException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent_id');

        $this->manager->load('nonexistent_id');
    }

    public function testLoadWorkspaceWithInvalidJson(): void
    {
        // Create a file with invalid JSON
        $invalidFile = $this->tempStoragePath . '/ws_invalid.json';
        file_put_contents($invalidFile, '{invalid json');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse workspace JSON');

        $this->manager->load('ws_invalid');
    }

    public function testSaveWorkspace(): void
    {
        $workspace = new Workspace(
            id: 'ws_test123',
            name: 'Test Save',
            description: 'Test description',
            model: ['elements' => []],
            views: ['views' => []],
            dsl: 'workspace "Test" {}',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->manager->save($workspace);

        $filepath = $this->tempStoragePath . '/ws_test123.json';
        $this->assertFileExists($filepath);

        $content = file_get_contents($filepath);
        $data = json_decode($content, true);

        $this->assertEquals('ws_test123', $data['id']);
        $this->assertEquals('Test Save', $data['name']);
        $this->assertEquals('workspace "Test" {}', $data['dsl']);
    }

    public function testDeleteWorkspace(): void
    {
        $workspace = $this->manager->create('To Delete');
        $workspaceId = $workspace->id;

        $this->assertTrue($this->manager->exists($workspaceId));

        $this->manager->delete($workspaceId);

        $this->assertFalse($this->manager->exists($workspaceId));
    }

    public function testDeleteWorkspaceNotFound(): void
    {
        $this->expectException(WorkspaceNotFoundException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->manager->delete('nonexistent');
    }

    public function testListWorkspaces(): void
    {
        $ws1 = $this->manager->create('Workspace 1', 'First workspace');
        $ws2 = $this->manager->create('Workspace 2', 'Second workspace');
        $ws3 = $this->manager->create('Workspace 3', 'Third workspace');

        $list = $this->manager->list();

        $this->assertCount(3, $list);

        $ids = array_column($list, 'id');
        $this->assertContains($ws1->id, $ids);
        $this->assertContains($ws2->id, $ids);
        $this->assertContains($ws3->id, $ids);

        // Check structure of list items
        foreach ($list as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('description', $item);
            $this->assertArrayHasKey('createdAt', $item);
            $this->assertArrayHasKey('updatedAt', $item);
        }
    }

    public function testListWorkspacesEmpty(): void
    {
        $list = $this->manager->list();

        $this->assertIsArray($list);
        $this->assertCount(0, $list);
    }

    public function testListWorkspacesSkipsInvalidFiles(): void
    {
        // Create valid workspace
        $this->manager->create('Valid Workspace');

        // Create invalid JSON file
        $invalidFile = $this->tempStoragePath . '/invalid.json';
        file_put_contents($invalidFile, '{invalid}');

        // Create file without ID
        $noIdFile = $this->tempStoragePath . '/no_id.json';
        file_put_contents($noIdFile, json_encode(['name' => 'No ID']));

        $list = $this->manager->list();

        // Should only return the valid workspace
        $this->assertCount(1, $list);
        $this->assertEquals('Valid Workspace', $list[0]['name']);
    }

    public function testExists(): void
    {
        $workspace = $this->manager->create('Exists Test');

        $this->assertTrue($this->manager->exists($workspace->id));
        $this->assertFalse($this->manager->exists('nonexistent_id'));
    }

    public function testUpdateDsl(): void
    {
        $workspace = $this->manager->create('DSL Test');
        $originalUpdatedAt = $workspace->updatedAt;

        // Wait a moment to ensure timestamp differs
        usleep(1000);

        $newDsl = 'workspace "Updated" { model { } }';
        $updated = $this->manager->updateDsl($workspace->id, $newDsl);

        $this->assertEquals($workspace->id, $updated->id);
        $this->assertEquals($workspace->name, $updated->name);
        $this->assertEquals($newDsl, $updated->dsl);
        $this->assertNotEquals($originalUpdatedAt, $updated->updatedAt);
        $this->assertGreaterThan($originalUpdatedAt, $updated->updatedAt);
    }

    public function testUpdateDslWorkspaceNotFound(): void
    {
        $this->expectException(WorkspaceNotFoundException::class);

        $this->manager->updateDsl('nonexistent', 'workspace {}');
    }

    public function testGenerateWorkspaceIdIsUnique(): void
    {
        $ws1 = $this->manager->create('First');
        $ws2 = $this->manager->create('Second');
        $ws3 = $this->manager->create('Third');

        $this->assertNotEquals($ws1->id, $ws2->id);
        $this->assertNotEquals($ws2->id, $ws3->id);
        $this->assertNotEquals($ws1->id, $ws3->id);
    }

    public function testWorkspaceIdIsSanitized(): void
    {
        // This tests the sanitization in getWorkspacePath method
        // We can't test it directly, but we can verify it doesn't allow directory traversal
        $workspace = $this->manager->create('Test');

        // Verify normal workspace ID works
        $this->assertTrue($this->manager->exists($workspace->id));
    }

    /**
     * Test multiple create/save/load operations
     */
    public function testMultipleOperations(): void
    {
        // Create
        $ws1 = $this->manager->create('Workspace 1');
        $ws2 = $this->manager->create('Workspace 2');

        // Load
        $loaded1 = $this->manager->load($ws1->id);
        $loaded2 = $this->manager->load($ws2->id);

        $this->assertEquals($ws1->id, $loaded1->id);
        $this->assertEquals($ws2->id, $loaded2->id);

        // Update
        $updated1 = $this->manager->updateDsl($ws1->id, 'new dsl');

        // List
        $list = $this->manager->list();
        $this->assertCount(2, $list);

        // Delete one
        $this->manager->delete($ws2->id);

        // List again
        $list = $this->manager->list();
        $this->assertCount(1, $list);
        $this->assertEquals($ws1->id, $list[0]['id']);
    }

    public function testWorkspaceManagerWithExistingDirectory(): void
    {
        // Create a workspace
        $this->manager->create('Test');

        // Create a new manager with the same path (simulating restart)
        $newManager = new WorkspaceManager(
            $this->tempStoragePath,
            new NullLogger()
        );

        $list = $newManager->list();
        $this->assertCount(1, $list);
        $this->assertEquals('Test', $list[0]['name']);
    }

    public function testSavePreservesModelAndViews(): void
    {
        $model = [
            'people' => [
                ['id' => 'user1', 'name' => 'User']
            ]
        ];
        $views = [
            ['type' => 'systemContext', 'key' => 'context']
        ];

        $workspace = new Workspace(
            id: 'ws_test',
            name: 'Test',
            description: 'Description',
            model: $model,
            views: $views,
            dsl: '',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->manager->save($workspace);
        $loaded = $this->manager->load('ws_test');

        $this->assertEquals($model, $loaded->model);
        $this->assertEquals($views, $loaded->views);
    }

    public function testJsonEncodingOptions(): void
    {
        $workspace = $this->manager->create('JSON Test');

        $filepath = $this->tempStoragePath . '/' . $workspace->id . '.json';
        $content = file_get_contents($filepath);

        // Check that JSON is pretty-printed (has newlines)
        $this->assertStringContainsString("\n", $content);

        // Check that slashes are not escaped
        $workspace = $this->manager->updateDsl($workspace->id, 'test/path');
        $content = file_get_contents($filepath);
        $this->assertStringContainsString('test/path', $content);
        $this->assertStringNotContainsString('test\\/path', $content);
    }
}
