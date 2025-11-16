<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Tools;

use Mcp\Exception\ToolCallException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Tools\DocumentationTools;

/**
 * Unit tests for DocumentationTools
 *
 * @covers \StructurizrMcp\Tools\DocumentationTools
 */
class DocumentationToolsTest extends TestCase
{
    private WorkspaceManager&MockObject $workspaceManager;
    private LoggerInterface&MockObject $logger;
    private DocumentationTools $tools;

    protected function setUp(): void
    {
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tools = new DocumentationTools(
            $this->workspaceManager,
            $this->logger
        );
    }

    private function createTestWorkspace(string $id = 'ws_test', array $model = []): Workspace
    {
        return new Workspace(
            id: $id,
            name: 'Test Workspace',
            description: 'Test description',
            model: $model,
            views: [],
            dsl: '',
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    // =========================================================================
    // Tests for addDocumentationSection()
    // =========================================================================

    public function testAddDocumentationSection(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['documentation' => []]);

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $logMessages = [];
        $this->logger
            ->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function ($message) use (&$logMessages) {
                $logMessages[] = $message;
            });

        $result = $this->tools->addDocumentationSection(
            'ws_123',
            'Overview',
            'This is the system overview'
        );

        // Verify result structure
        $this->assertTrue($result['success']);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('section', $result);
        $this->assertStringStartsWith('doc_', $result['section']['id']);
        $this->assertEquals('Overview', $result['section']['title']);
        $this->assertEquals('Markdown', $result['section']['format']);
        $this->assertArrayHasKey('createdAt', $result['section']);
        $this->assertEquals("Documentation section 'Overview' added successfully", $result['message']);

        // Verify workspace was updated correctly
        $this->assertNotNull($savedWorkspace);
        $model = $savedWorkspace->model;
        $this->assertArrayHasKey('documentation', $model);
        $this->assertCount(1, $model['documentation']);
        $this->assertEquals('Overview', $model['documentation'][0]['title']);
        $this->assertEquals('This is the system overview', $model['documentation'][0]['content']);
        $this->assertEquals('Markdown', $model['documentation'][0]['format']);

        // Verify log messages
        $this->assertCount(2, $logMessages);
        $this->assertEquals("Adding documentation section 'Overview' to workspace: ws_123", $logMessages[0]);
        $this->assertEquals("Successfully added documentation section 'Overview' to workspace: ws_123", $logMessages[1]);
    }

    public function testAddDocumentationSectionWithMarkdown(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['documentation' => []]);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $markdownContent = <<<'MARKDOWN'
# System Architecture

## Overview
This is a **comprehensive** overview of the system.

### Key Components
- Component A
- Component B
- Component C

```java
public class Example {
    // Code example
}
```
MARKDOWN;

        $result = $this->tools->addDocumentationSection(
            'ws_123',
            'Architecture',
            $markdownContent
        );

        $this->assertTrue($result['success']);
        $this->assertNotNull($savedWorkspace);
        $this->assertEquals($markdownContent, $savedWorkspace->model['documentation'][0]['content']);
    }

    public function testAddDocumentationSectionWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->tools->addDocumentationSection('nonexistent', 'Title', 'Content');
    }

    public function testAddDocumentationSectionGeneratesId(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['documentation' => []]);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $result = $this->tools->addDocumentationSection('ws_123', 'Section 1', 'Content 1');

        // Verify ID is generated and follows expected pattern
        $this->assertStringStartsWith('doc_', $result['section']['id']);
        $this->assertMatchesRegularExpression('/^doc_[a-f0-9]{16}$/', $result['section']['id']);

        // Verify ID is stored in the model
        $this->assertNotNull($savedWorkspace);
        $this->assertEquals($result['section']['id'], $savedWorkspace->model['documentation'][0]['id']);
    }

    public function testAddDocumentationSectionAddsTimestamp(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['documentation' => []]);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $beforeTime = new \DateTimeImmutable();
        $result = $this->tools->addDocumentationSection('ws_123', 'Title', 'Content');
        $afterTime = new \DateTimeImmutable();

        // Verify timestamp is present and valid
        $this->assertArrayHasKey('createdAt', $result['section']);
        $createdAt = new \DateTimeImmutable($result['section']['createdAt']);

        $this->assertGreaterThanOrEqual($beforeTime->getTimestamp(), $createdAt->getTimestamp());
        $this->assertLessThanOrEqual($afterTime->getTimestamp(), $createdAt->getTimestamp());

        // Verify timestamp is in the saved workspace
        $this->assertNotNull($savedWorkspace);
        $this->assertArrayHasKey('createdAt', $savedWorkspace->model['documentation'][0]);
    }

    public function testAddDocumentationSectionUpdatesWorkspace(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['documentation' => []]);

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Workspace $ws) {
                return $ws->id === 'ws_123'
                    && isset($ws->model['documentation'])
                    && count($ws->model['documentation']) === 1;
            }));

        $this->tools->addDocumentationSection('ws_123', 'Title', 'Content');
    }

    public function testAddDocumentationMultipleSections(): void
    {
        // Start with empty documentation array
        $currentWorkspace = $this->createTestWorkspace('ws_123', ['documentation' => []]);

        // Mock load to return the current workspace state
        $this->workspaceManager
            ->method('load')
            ->willReturnCallback(function () use (&$currentWorkspace) {
                return $currentWorkspace;
            });

        // Mock save to update the current workspace
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$currentWorkspace) {
                $currentWorkspace = $ws;
            });

        // Add first section
        $result1 = $this->tools->addDocumentationSection('ws_123', 'Section 1', 'Content 1');
        $this->assertTrue($result1['success']);
        $this->assertCount(1, $currentWorkspace->model['documentation']);

        // Add second section
        $result2 = $this->tools->addDocumentationSection('ws_123', 'Section 2', 'Content 2');
        $this->assertTrue($result2['success']);

        // Verify both sections are present
        $this->assertCount(2, $currentWorkspace->model['documentation']);
        $this->assertEquals('Section 1', $currentWorkspace->model['documentation'][0]['title']);
        $this->assertEquals('Section 2', $currentWorkspace->model['documentation'][1]['title']);
    }

    public function testAddDocumentationSectionInitializesArrayIfNotExists(): void
    {
        // Create workspace without documentation array
        $workspace = $this->createTestWorkspace('ws_123', []);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $result = $this->tools->addDocumentationSection('ws_123', 'Title', 'Content');

        $this->assertTrue($result['success']);
        $this->assertNotNull($savedWorkspace);
        $this->assertArrayHasKey('documentation', $savedWorkspace->model);
        $this->assertIsArray($savedWorkspace->model['documentation']);
        $this->assertCount(1, $savedWorkspace->model['documentation']);
    }

    // =========================================================================
    // Tests for addAdr()
    // =========================================================================

    public function testAddAdr(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['adrs' => []]);

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $logMessages = [];
        $this->logger
            ->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function ($message) use (&$logMessages) {
                $logMessages[] = $message;
            });

        $result = $this->tools->addAdr(
            'ws_123',
            '001',
            '2024-01-15',
            'Use microservices architecture',
            'Accepted',
            'We will use microservices to improve scalability and maintainability.'
        );

        // Verify result structure
        $this->assertTrue($result['success']);
        $this->assertEquals('ws_123', $result['workspaceId']);
        $this->assertArrayHasKey('adr', $result);
        $this->assertEquals('001', $result['adr']['id']);
        $this->assertEquals('2024-01-15', $result['adr']['date']);
        $this->assertEquals('Use microservices architecture', $result['adr']['title']);
        $this->assertEquals('Accepted', $result['adr']['status']);
        $this->assertEquals('Markdown', $result['adr']['format']);
        $this->assertArrayHasKey('createdAt', $result['adr']);
        $this->assertEquals("ADR 001 'Use microservices architecture' added successfully with status 'Accepted'", $result['message']);

        // Verify workspace was updated correctly
        $this->assertNotNull($savedWorkspace);
        $model = $savedWorkspace->model;
        $this->assertArrayHasKey('adrs', $model);
        $this->assertCount(1, $model['adrs']);
        $this->assertEquals('001', $model['adrs'][0]['id']);
        $this->assertEquals('2024-01-15', $model['adrs'][0]['date']);
        $this->assertEquals('Use microservices architecture', $model['adrs'][0]['title']);
        $this->assertEquals('Accepted', $model['adrs'][0]['status']);
        $this->assertEquals('Markdown', $model['adrs'][0]['format']);

        // Verify log messages
        $this->assertCount(2, $logMessages);
        $this->assertEquals("Adding ADR 001 'Use microservices architecture' to workspace: ws_123", $logMessages[0]);
        $this->assertEquals("Successfully added ADR 001 'Use microservices architecture' to workspace: ws_123", $logMessages[1]);
    }

    /**
     * Data provider for ADR status values
     */
    public static function adrStatusProvider(): array
    {
        return [
            'Proposed' => ['Proposed'],
            'Accepted' => ['Accepted'],
            'Rejected' => ['Rejected'],
            'Deprecated' => ['Deprecated'],
            'Superseded' => ['Superseded'],
        ];
    }

    /**
     * @dataProvider adrStatusProvider
     */
    public function testAddAdrWithAllStatuses(string $status): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['adrs' => []]);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $result = $this->tools->addAdr(
            'ws_123',
            '001',
            '2024-01-15',
            'Test ADR',
            $status,
            'Content for ADR'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals($status, $result['adr']['status']);
        $this->assertNotNull($savedWorkspace);
        $this->assertEquals($status, $savedWorkspace->model['adrs'][0]['status']);
    }

    public function testAddAdrDuplicateId(): void
    {
        // Create workspace with existing ADR
        $workspace = $this->createTestWorkspace('ws_123', [
            'adrs' => [
                [
                    'id' => '001',
                    'date' => '2024-01-01',
                    'title' => 'Existing ADR',
                    'status' => 'Accepted',
                    'content' => 'Existing content',
                    'format' => 'Markdown',
                    'createdAt' => '2024-01-01T12:00:00+00:00',
                ],
            ],
        ]);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage("ADR with ID '001' already exists in workspace 'ws_123'");

        $this->tools->addAdr(
            'ws_123',
            '001',
            '2024-01-15',
            'Duplicate ADR',
            'Proposed',
            'This should fail'
        );
    }

    public function testAddAdrWorkspaceNotFound(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('Workspace not found: nonexistent');

        $this->tools->addAdr(
            'nonexistent',
            '001',
            '2024-01-15',
            'Title',
            'Accepted',
            'Content'
        );
    }

    public function testAddAdrSortsByIdNumeric(): void
    {
        // Create workspace with existing ADRs (intentionally out of order)
        $workspace = $this->createTestWorkspace('ws_123', [
            'adrs' => [
                [
                    'id' => '005',
                    'date' => '2024-01-05',
                    'title' => 'ADR 5',
                    'status' => 'Accepted',
                    'content' => 'Content 5',
                    'format' => 'Markdown',
                    'createdAt' => '2024-01-05T12:00:00+00:00',
                ],
                [
                    'id' => '002',
                    'date' => '2024-01-02',
                    'title' => 'ADR 2',
                    'status' => 'Accepted',
                    'content' => 'Content 2',
                    'format' => 'Markdown',
                    'createdAt' => '2024-01-02T12:00:00+00:00',
                ],
                [
                    'id' => '010',
                    'date' => '2024-01-10',
                    'title' => 'ADR 10',
                    'status' => 'Accepted',
                    'content' => 'Content 10',
                    'format' => 'Markdown',
                    'createdAt' => '2024-01-10T12:00:00+00:00',
                ],
            ],
        ]);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        // Add ADR with ID 003 (should be sorted between 2 and 5)
        $this->tools->addAdr(
            'ws_123',
            '003',
            '2024-01-03',
            'ADR 3',
            'Proposed',
            'Content 3'
        );

        // Verify ADRs are sorted numerically
        $this->assertNotNull($savedWorkspace);
        $adrs = $savedWorkspace->model['adrs'];
        $this->assertCount(4, $adrs);
        $this->assertEquals('002', $adrs[0]['id']);
        $this->assertEquals('003', $adrs[1]['id']);
        $this->assertEquals('005', $adrs[2]['id']);
        $this->assertEquals('010', $adrs[3]['id']);
    }

    public function testAddAdrAddsTimestamp(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['adrs' => []]);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $beforeTime = new \DateTimeImmutable();
        $result = $this->tools->addAdr(
            'ws_123',
            '001',
            '2024-01-15',
            'Title',
            'Accepted',
            'Content'
        );
        $afterTime = new \DateTimeImmutable();

        // Verify timestamp is present and valid
        $this->assertArrayHasKey('createdAt', $result['adr']);
        $createdAt = new \DateTimeImmutable($result['adr']['createdAt']);

        $this->assertGreaterThanOrEqual($beforeTime->getTimestamp(), $createdAt->getTimestamp());
        $this->assertLessThanOrEqual($afterTime->getTimestamp(), $createdAt->getTimestamp());

        // Verify timestamp is in the saved workspace
        $this->assertNotNull($savedWorkspace);
        $this->assertArrayHasKey('createdAt', $savedWorkspace->model['adrs'][0]);
    }

    public function testAddAdrUpdatesWorkspace(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['adrs' => []]);

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws_123')
            ->willReturn($workspace);

        $this->workspaceManager
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Workspace $ws) {
                return $ws->id === 'ws_123'
                    && isset($ws->model['adrs'])
                    && count($ws->model['adrs']) === 1;
            }));

        $this->tools->addAdr(
            'ws_123',
            '001',
            '2024-01-15',
            'Title',
            'Accepted',
            'Content'
        );
    }

    public function testAddAdrInitializesArrayIfNotExists(): void
    {
        // Create workspace without adrs array
        $workspace = $this->createTestWorkspace('ws_123', []);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $result = $this->tools->addAdr(
            'ws_123',
            '001',
            '2024-01-15',
            'Title',
            'Accepted',
            'Content'
        );

        $this->assertTrue($result['success']);
        $this->assertNotNull($savedWorkspace);
        $this->assertArrayHasKey('adrs', $savedWorkspace->model);
        $this->assertIsArray($savedWorkspace->model['adrs']);
        $this->assertCount(1, $savedWorkspace->model['adrs']);
    }

    public function testAddAdrWithLongContent(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['adrs' => []]);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $longContent = <<<'MARKDOWN'
# Context

We need to decide on a database technology for our new microservices architecture.

## Options Considered

1. PostgreSQL
2. MongoDB
3. Cassandra

## Decision

We will use PostgreSQL as our primary database for the following reasons:

- ACID compliance
- Strong ecosystem
- JSON support for flexible schemas
- Proven scalability

## Consequences

### Positive
- Data integrity guarantees
- Mature tooling and monitoring
- Team expertise

### Negative
- May need additional NoSQL for specific use cases
- Horizontal scaling requires more effort than some NoSQL solutions

## References
- [PostgreSQL Documentation](https://postgresql.org)
- Internal benchmark results (Q4 2023)
MARKDOWN;

        $result = $this->tools->addAdr(
            'ws_123',
            '042',
            '2024-01-15',
            'Choose PostgreSQL as primary database',
            'Accepted',
            $longContent
        );

        $this->assertTrue($result['success']);
        $this->assertNotNull($savedWorkspace);
        $this->assertEquals($longContent, $savedWorkspace->model['adrs'][0]['content']);
    }

    // =========================================================================
    // Additional Tests
    // =========================================================================

    public function testLoggingBehavior(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', []);

        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->method('save');

        // Test addDocumentationSection logging
        $logMessages = [];
        $this->logger
            ->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function ($message) use (&$logMessages) {
                $logMessages[] = $message;
            });

        $this->tools->addDocumentationSection('ws_123', 'Test Section', 'Content');

        $this->assertCount(2, $logMessages);
        $this->assertEquals("Adding documentation section 'Test Section' to workspace: ws_123", $logMessages[0]);
        $this->assertEquals("Successfully added documentation section 'Test Section' to workspace: ws_123", $logMessages[1]);

        // Reset mocks for next test
        $this->setUp();
        $workspace = $this->createTestWorkspace('ws_123', []);
        $this->workspaceManager->method('load')->willReturn($workspace);
        $this->workspaceManager->method('save');

        // Test addAdr logging
        $logMessages = [];
        $this->logger
            ->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function ($message) use (&$logMessages) {
                $logMessages[] = $message;
            });

        $this->tools->addAdr('ws_123', '001', '2024-01-15', 'Test ADR', 'Accepted', 'Content');

        $this->assertCount(2, $logMessages);
        $this->assertEquals("Adding ADR 001 'Test ADR' to workspace: ws_123", $logMessages[0]);
        $this->assertEquals("Successfully added ADR 001 'Test ADR' to workspace: ws_123", $logMessages[1]);
    }

    public function testWorkspaceModelInitialization(): void
    {
        // Test that methods properly initialize model arrays when they don't exist
        $workspace = $this->createTestWorkspace('ws_123', []);

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspaces = [];
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspaces) {
                $savedWorkspaces[] = $ws;
            });

        // Add documentation section to empty model
        $this->tools->addDocumentationSection('ws_123', 'Section', 'Content');

        $this->assertCount(1, $savedWorkspaces);
        $this->assertArrayHasKey('documentation', $savedWorkspaces[0]->model);
        $this->assertIsArray($savedWorkspaces[0]->model['documentation']);

        // Reset for ADR test
        $workspace = $this->createTestWorkspace('ws_456', []);
        $this->workspaceManager->method('load')->willReturn($workspace);

        // Add ADR to empty model
        $this->tools->addAdr('ws_456', '001', '2024-01-15', 'Title', 'Accepted', 'Content');

        $this->assertCount(2, $savedWorkspaces);
        $this->assertArrayHasKey('adrs', $savedWorkspaces[1]->model);
        $this->assertIsArray($savedWorkspaces[1]->model['adrs']);
    }

    public function testMultipleAdrsWithNumericSorting(): void
    {
        $workspace = $this->createTestWorkspace('ws_123', ['adrs' => []]);

        $this->workspaceManager->method('load')->willReturnCallback(function () use (&$workspace) {
            return $workspace;
        });

        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$workspace) {
                $workspace = $ws;
            });

        // Add ADRs in non-sequential order
        $this->tools->addAdr('ws_123', '010', '2024-01-10', 'ADR 10', 'Accepted', 'Content 10');
        $this->tools->addAdr('ws_123', '002', '2024-01-02', 'ADR 2', 'Accepted', 'Content 2');
        $this->tools->addAdr('ws_123', '001', '2024-01-01', 'ADR 1', 'Accepted', 'Content 1');
        $this->tools->addAdr('ws_123', '020', '2024-01-20', 'ADR 20', 'Accepted', 'Content 20');
        $this->tools->addAdr('ws_123', '005', '2024-01-05', 'ADR 5', 'Accepted', 'Content 5');

        // Verify final ordering is numeric
        $adrs = $workspace->model['adrs'];
        $this->assertCount(5, $adrs);
        $this->assertEquals('001', $adrs[0]['id']);
        $this->assertEquals('002', $adrs[1]['id']);
        $this->assertEquals('005', $adrs[2]['id']);
        $this->assertEquals('010', $adrs[3]['id']);
        $this->assertEquals('020', $adrs[4]['id']);
    }

    public function testAddDocumentationSectionPreservesWorkspaceMetadata(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');
        $workspace = new Workspace(
            id: 'ws_meta',
            name: 'Original Name',
            description: 'Original Description',
            model: ['documentation' => []],
            views: ['existing' => 'view'],
            dsl: 'existing dsl',
            createdAt: $createdAt,
            updatedAt: new \DateTimeImmutable('2024-01-01 10:00:00'),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $this->tools->addDocumentationSection('ws_meta', 'New Section', 'Content');

        $this->assertNotNull($savedWorkspace);
        $this->assertEquals('ws_meta', $savedWorkspace->id);
        $this->assertEquals('Original Name', $savedWorkspace->name);
        $this->assertEquals('Original Description', $savedWorkspace->description);
        $this->assertEquals($createdAt, $savedWorkspace->createdAt);
        // Updated timestamp should change
        $this->assertGreaterThan($workspace->updatedAt, $savedWorkspace->updatedAt);
    }

    public function testAddAdrPreservesWorkspaceMetadata(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-01 10:00:00');
        $workspace = new Workspace(
            id: 'ws_meta',
            name: 'Original Name',
            description: 'Original Description',
            model: ['adrs' => []],
            views: ['existing' => 'view'],
            dsl: 'existing dsl',
            createdAt: $createdAt,
            updatedAt: new \DateTimeImmutable('2024-01-01 10:00:00'),
        );

        $this->workspaceManager->method('load')->willReturn($workspace);

        $savedWorkspace = null;
        $this->workspaceManager
            ->method('save')
            ->willReturnCallback(function (Workspace $ws) use (&$savedWorkspace) {
                $savedWorkspace = $ws;
            });

        $this->tools->addAdr('ws_meta', '001', '2024-01-15', 'Title', 'Accepted', 'Content');

        $this->assertNotNull($savedWorkspace);
        $this->assertEquals('ws_meta', $savedWorkspace->id);
        $this->assertEquals('Original Name', $savedWorkspace->name);
        $this->assertEquals('Original Description', $savedWorkspace->description);
        $this->assertEquals($createdAt, $savedWorkspace->createdAt);
        // Updated timestamp should change
        $this->assertGreaterThan($workspace->updatedAt, $savedWorkspace->updatedAt);
    }
}
