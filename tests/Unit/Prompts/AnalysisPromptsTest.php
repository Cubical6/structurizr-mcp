<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Prompts;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Prompts\AnalysisPrompts;
use StructurizrMcp\Structurizr\Workspace;
use StructurizrMcp\Structurizr\WorkspaceManager;

class AnalysisPromptsTest extends TestCase
{
    private WorkspaceManager $workspaceManager;
    private LoggerInterface $logger;
    private AnalysisPrompts $analysisPrompts;

    protected function setUp(): void
    {
        $this->workspaceManager = $this->createMock(WorkspaceManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->analysisPrompts = new AnalysisPrompts($this->workspaceManager, $this->logger);
    }

    public function testAnalyzeArchitectureReturnsValidMessageStructure(): void
    {
        $workspace = new Workspace(
            id: 'test-workspace',
            name: 'Test Workspace',
            description: 'A test workspace for analysis',
            model: [
                'people' => [
                    ['id' => 'user1', 'name' => 'User']
                ],
                'softwareSystems' => [
                    ['id' => 'sys1', 'name' => 'System']
                ]
            ],
            views: [
                'systemContextViews' => [
                    ['key' => 'context1']
                ]
            ],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('test-workspace')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->analyzeArchitecture('test-workspace');

        // Verify structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('messages', $result);
        $this->assertIsArray($result['messages']);
        $this->assertCount(3, $result['messages']);

        // Verify first message (introduction)
        $this->assertArrayHasKey('role', $result['messages'][0]);
        $this->assertEquals('user', $result['messages'][0]['role']);
        $this->assertArrayHasKey('content', $result['messages'][0]);
        $this->assertIsArray($result['messages'][0]['content']);
        $this->assertCount(1, $result['messages'][0]['content']);
        $this->assertEquals('text', $result['messages'][0]['content'][0]['type']);
        $this->assertStringContainsString('Test Workspace', $result['messages'][0]['content'][0]['text']);

        // Verify second message (resource)
        $this->assertEquals('user', $result['messages'][1]['role']);
        $this->assertEquals('resource', $result['messages'][1]['content'][0]['type']);
        $this->assertArrayHasKey('resource', $result['messages'][1]['content'][0]);
        $this->assertEquals('structurizr://workspace/test-workspace', $result['messages'][1]['content'][0]['resource']['uri']);
        $this->assertEquals('application/json', $result['messages'][1]['content'][0]['resource']['mimeType']);

        // Verify third message (instructions)
        $this->assertEquals('user', $result['messages'][2]['role']);
        $this->assertEquals('text', $result['messages'][2]['content'][0]['type']);
        $this->assertStringContainsString('Analysis Requirements', $result['messages'][2]['content'][0]['text']);
    }

    public function testAnalyzeArchitectureThrowsExceptionForNonExistentWorkspace(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(WorkspaceNotFoundException::class);

        $this->analysisPrompts->analyzeArchitecture('nonexistent');
    }

    public function testAnalyzeArchitectureIncludesWorkspaceData(): void
    {
        $workspace = new Workspace(
            id: 'ws1',
            name: 'My System',
            description: 'System description',
            model: [
                'people' => [
                    ['id' => 'p1', 'name' => 'Person 1'],
                    ['id' => 'p2', 'name' => 'Person 2']
                ],
                'softwareSystems' => [
                    ['id' => 's1', 'name' => 'System 1']
                ]
            ],
            views: [
                'containerViews' => [
                    ['key' => 'view1'],
                    ['key' => 'view2']
                ]
            ],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('ws1')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->analyzeArchitecture('ws1');

        // Verify embedded resource contains workspace data
        $resourceText = $result['messages'][1]['content'][0]['resource']['text'];
        $decodedData = json_decode($resourceText, true);

        $this->assertIsArray($decodedData);
        $this->assertEquals('ws1', $decodedData['id']);
        $this->assertEquals('My System', $decodedData['name']);
        $this->assertArrayHasKey('model', $decodedData);
        $this->assertArrayHasKey('views', $decodedData);
    }

    public function testReviewSecurityReturnsValidMessageStructure(): void
    {
        $workspace = new Workspace(
            id: 'sec-test',
            name: 'Security Test',
            description: 'Workspace for security testing',
            model: [
                'softwareSystems' => [
                    ['id' => 'sys1', 'name' => 'Internal System', 'location' => 'Internal'],
                    ['id' => 'sys2', 'name' => 'External API', 'location' => 'External']
                ]
            ],
            views: [],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('sec-test')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->reviewSecurity('sec-test');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('messages', $result);
        $this->assertCount(3, $result['messages']);

        // Verify introduction mentions external systems
        $this->assertStringContainsString('External Systems', $result['messages'][0]['content'][0]['text']);

        // Verify resource contains model data
        $this->assertEquals('resource', $result['messages'][1]['content'][0]['type']);
        $resourceUri = $result['messages'][1]['content'][0]['resource']['uri'];
        $this->assertEquals('structurizr://workspace/sec-test/model', $resourceUri);

        // Verify security checklist is included
        $checklistText = $result['messages'][2]['content'][0]['text'];
        $this->assertStringContainsString('Security Review Checklist', $checklistText);
        $this->assertStringContainsString('Authentication & Authorization', $checklistText);
        $this->assertStringContainsString('Data Protection', $checklistText);
        $this->assertStringContainsString('OWASP Top 10', $checklistText);
    }

    public function testReviewSecurityCountsExternalSystems(): void
    {
        $workspace = new Workspace(
            id: 'multi-ext',
            name: 'Multi External',
            description: 'System with multiple external dependencies',
            model: [
                'softwareSystems' => [
                    ['id' => 's1', 'name' => 'Internal', 'location' => 'Internal'],
                    ['id' => 's2', 'name' => 'External 1', 'location' => 'External'],
                    ['id' => 's3', 'name' => 'External 2', 'location' => 'External'],
                    ['id' => 's4', 'name' => 'External 3', 'location' => 'External']
                ]
            ],
            views: [],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('multi-ext')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->reviewSecurity('multi-ext');

        // Should mention external systems count
        $introText = $result['messages'][0]['content'][0]['text'];
        $this->assertStringContainsString('External Systems', $introText);
        $this->assertStringContainsString('3', $introText);
    }

    public function testReviewSecurityThrowsExceptionForNonExistentWorkspace(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(WorkspaceNotFoundException::class);

        $this->analysisPrompts->reviewSecurity('nonexistent');
    }

    public function testSuggestImprovementsWithDefaultFocusArea(): void
    {
        $workspace = new Workspace(
            id: 'improve-test',
            name: 'Improvement Test',
            description: 'Test workspace',
            model: [],
            views: [],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('improve-test')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->suggestImprovements('improve-test');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('messages', $result);

        // Default focus should be 'all'
        $introText = $result['messages'][0]['content'][0]['text'];
        $this->assertStringContainsString('comprehensive suggestions', $introText);

        // Should include all categories
        $promptText = $result['messages'][2]['content'][0]['text'];
        $this->assertStringContainsString('Scalability', $promptText);
        $this->assertStringContainsString('Maintainability', $promptText);
        $this->assertStringContainsString('Performance', $promptText);
        $this->assertStringContainsString('Documentation', $promptText);
    }

    public function testSuggestImprovementsWithScalabilityFocus(): void
    {
        $workspace = new Workspace(
            id: 'scale-test',
            name: 'Scalability Test',
            description: 'Test workspace',
            model: [],
            views: [],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('scale-test')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->suggestImprovements('scale-test', 'scalability');

        $introText = $result['messages'][0]['content'][0]['text'];
        $this->assertStringContainsString('scalability improvements', $introText);

        $promptText = $result['messages'][2]['content'][0]['text'];
        $this->assertStringContainsString('Horizontal Scaling', $promptText);
        $this->assertStringContainsString('Bottlenecks', $promptText);
        $this->assertStringContainsString('Caching Strategy', $promptText);
        $this->assertStringContainsString('Load Balancing', $promptText);
    }

    public function testSuggestImprovementsWithMaintainabilityFocus(): void
    {
        $workspace = new Workspace(
            id: 'maint-test',
            name: 'Maintainability Test',
            description: 'Test workspace',
            model: [],
            views: [],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('maint-test')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->suggestImprovements('maint-test', 'maintainability');

        $introText = $result['messages'][0]['content'][0]['text'];
        $this->assertStringContainsString('maintainability', $introText);

        $promptText = $result['messages'][2]['content'][0]['text'];
        $this->assertStringContainsString('Modularity', $promptText);
        $this->assertStringContainsString('Coupling', $promptText);
        $this->assertStringContainsString('Cohesion', $promptText);
        $this->assertStringContainsString('Technical Debt', $promptText);
    }

    public function testSuggestImprovementsWithPerformanceFocus(): void
    {
        $workspace = new Workspace(
            id: 'perf-test',
            name: 'Performance Test',
            description: 'Test workspace',
            model: [],
            views: [],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('perf-test')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->suggestImprovements('perf-test', 'performance');

        $introText = $result['messages'][0]['content'][0]['text'];
        $this->assertStringContainsString('performance optimizations', $introText);

        $promptText = $result['messages'][2]['content'][0]['text'];
        $this->assertStringContainsString('Latency', $promptText);
        $this->assertStringContainsString('Throughput', $promptText);
        $this->assertStringContainsString('Database Queries', $promptText);
    }

    public function testSuggestImprovementsWithDocumentationFocus(): void
    {
        $workspace = new Workspace(
            id: 'doc-test',
            name: 'Documentation Test',
            description: 'Test workspace',
            model: [],
            views: [],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('doc-test')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->suggestImprovements('doc-test', 'documentation');

        $introText = $result['messages'][0]['content'][0]['text'];
        $this->assertStringContainsString('documentation quality', $introText);

        $promptText = $result['messages'][2]['content'][0]['text'];
        $this->assertStringContainsString('Element Descriptions', $promptText);
        $this->assertStringContainsString('Missing Views', $promptText);
        $this->assertStringContainsString('Decision Records', $promptText);
    }

    public function testSuggestImprovementsThrowsExceptionForNonExistentWorkspace(): void
    {
        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('nonexistent')
            ->willThrowException(new WorkspaceNotFoundException('nonexistent'));

        $this->expectException(WorkspaceNotFoundException::class);

        $this->analysisPrompts->suggestImprovements('nonexistent', 'all');
    }

    public function testSuggestImprovementsIncludesWorkspaceInResource(): void
    {
        $workspace = new Workspace(
            id: 'res-test',
            name: 'Resource Test',
            description: 'Test resource embedding',
            model: [
                'people' => [['id' => 'p1', 'name' => 'User']]
            ],
            views: [],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->expects($this->once())
            ->method('load')
            ->with('res-test')
            ->willReturn($workspace);

        $result = $this->analysisPrompts->suggestImprovements('res-test', 'all');

        // Verify resource embedding
        $resource = $result['messages'][1]['content'][0];
        $this->assertEquals('resource', $resource['type']);
        $this->assertEquals('structurizr://workspace/res-test', $resource['resource']['uri']);
        $this->assertEquals('application/json', $resource['resource']['mimeType']);

        $decodedData = json_decode($resource['resource']['text'], true);
        $this->assertEquals('res-test', $decodedData['id']);
        $this->assertEquals('Resource Test', $decodedData['name']);
    }

    public function testAllPromptsLogCorrectly(): void
    {
        $workspace = new Workspace(
            id: 'log-test',
            name: 'Log Test',
            description: 'Test logging',
            model: [],
            views: [],
            dsl: 'workspace { }'
        );

        $this->workspaceManager
            ->method('load')
            ->willReturn($workspace);

        // Test analyze_architecture logging
        $this->logger
            ->expects($this->exactly(3))
            ->method('info');

        $this->analysisPrompts->analyzeArchitecture('log-test');
        $this->analysisPrompts->reviewSecurity('log-test');
        $this->analysisPrompts->suggestImprovements('log-test', 'all');
    }
}
