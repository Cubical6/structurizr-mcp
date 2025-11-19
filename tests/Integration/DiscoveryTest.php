<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Integration;

use Mcp\Capability\Registry;
use Mcp\Capability\Registry\Loader\DiscoveryLoader;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Integration tests for MCP auto-discovery mechanism
 *
 * Tests that the discovery mechanism correctly finds all tools, resources, and prompts
 * using PHP attributes (#[McpTool], #[McpResource], #[McpResourceTemplate], #[McpPrompt]).
 *
 * Uses the Registry and DiscoveryLoader directly to test discovery without requiring
 * a full server instance.
 *
 * @group integration
 */
class DiscoveryTest extends TestCase
{
    private string $tempCacheDir;
    private CacheInterface $cache;
    private string $basePath;
    private NullLogger $logger;

    protected function setUp(): void
    {
        // Create temporary cache directory
        $this->tempCacheDir = sys_get_temp_dir() . '/structurizr-discovery-test-' . uniqid();
        mkdir($this->tempCacheDir, 0o755, true);

        // Initialize PSR-16 cache
        $phpFileCache = new PhpFilesAdapter(
            directory: $this->tempCacheDir,
            namespace: 'discovery-test',
            defaultLifetime: 3600
        );
        $this->cache = new Psr16Cache($phpFileCache);

        // Set base path to project root
        $this->basePath = dirname(__DIR__, 2);

        // Initialize logger
        $this->logger = new NullLogger();
    }

    protected function tearDown(): void
    {
        // Clean up temporary cache directory
        if (is_dir($this->tempCacheDir)) {
            $this->recursiveRemoveDirectory($this->tempCacheDir);
        }
    }

    /**
     * Create and load a registry with discovery
     *
     * @param array<string> $scanDirs
     * @param array<string> $excludeDirs
     */
    private function createRegistryWithDiscovery(
        array $scanDirs = ['src'],
        array $excludeDirs = ['vendor', 'tests', 'cache', 'sessions', 'workspaces', 'docs', 'examples'],
        ?CacheInterface $cache = null
    ): Registry {
        $registry = new Registry(logger: $this->logger);

        $loader = new DiscoveryLoader(
            basePath: $this->basePath,
            scanDirs: $scanDirs,
            excludeDirs: $excludeDirs,
            logger: $this->logger,
            cache: $cache ?? $this->cache
        );

        $loader->load($registry);

        return $registry;
    }

    /**
     * Test that discovery finds all 23 tool attributes
     */
    public function testDiscoveryFindsAllToolAttributes(): void
    {
        $registry = $this->createRegistryWithDiscovery();

        // Get tools from registry (Page object with references)
        $toolsPage = $registry->getTools();
        $tools = $toolsPage->references;

        // Should find exactly 23 tools
        $this->assertCount(23, $tools, 'Expected to discover exactly 23 MCP tools');

        // Verify all expected tool names are present
        $expectedTools = [
            // Workspace tools (4)
            'create_workspace',
            'get_workspace',
            'list_workspaces',
            'delete_workspace',
            // Model tools (5)
            'add_person',
            'add_software_system',
            'add_container',
            'add_component',
            'add_relationship',
            // View tools (5)
            'create_system_context_view',
            'create_container_view',
            'create_component_view',
            'create_dynamic_view',
            'apply_auto_layout',
            // Documentation tools (2)
            'add_documentation_section',
            'add_adr',
            // Export tools (4)
            'export_to_dsl',
            'export_to_plantuml',
            'export_to_mermaid',
            'import_from_dsl',
            // Analysis tools (3)
            'analyze_dependencies',
            'find_element',
            'validate_workspace',
        ];

        $toolNames = array_map(fn ($tool) => $tool->name, $tools);

        foreach ($expectedTools as $expectedTool) {
            $this->assertContains(
                $expectedTool,
                $toolNames,
                "Expected tool '{$expectedTool}' was not discovered"
            );
        }
    }

    /**
     * Test that discovery finds all 7 resource attributes (1 static + 6 dynamic)
     */
    public function testDiscoveryFindsAllResourceAttributes(): void
    {
        $registry = $this->createRegistryWithDiscovery();

        // Get static resources
        $resourcesPage = $registry->getResources();
        $resources = $resourcesPage->references;

        // Get resource templates
        $templatesPage = $registry->getResourceTemplates();
        $templates = $templatesPage->references;

        // Total should be 7 (1 static + 6 templates)
        $totalCount = count($resources) + count($templates);
        $this->assertEquals(7, $totalCount, 'Expected to discover exactly 7 MCP resources (1 static + 6 templates)');

        // Verify static resource
        $resourceNames = array_map(fn ($resource) => $resource->name, $resources);
        $this->assertContains('server_config', $resourceNames, 'Expected static resource "server_config" was not discovered');

        // Verify resource template names (dynamic resources)
        $expectedTemplates = [
            'workspace_full',
            'workspace_model',
            'workspace_views',
            'workspace_dsl',
            'workspace_element',
            'workspace_view',
        ];

        $templateNames = array_map(fn ($template) => $template->name, $templates);

        foreach ($expectedTemplates as $expectedTemplate) {
            $this->assertContains(
                $expectedTemplate,
                $templateNames,
                "Expected resource template '{$expectedTemplate}' was not discovered"
            );
        }
    }

    /**
     * Test that discovery finds all 7 prompt attributes
     */
    public function testDiscoveryFindsAllPromptAttributes(): void
    {
        $registry = $this->createRegistryWithDiscovery();

        // Get prompts from registry
        $promptsPage = $registry->getPrompts();
        $prompts = $promptsPage->references;

        // Should find exactly 7 prompts
        $this->assertCount(7, $prompts, 'Expected to discover exactly 7 MCP prompts');

        // Verify all expected prompt names are present
        $expectedPrompts = [
            // Analysis prompts (3)
            'analyze_architecture',
            'review_security',
            'suggest_improvements',
            // Generation prompts (4)
            'generate_system_context',
            'create_from_description',
            'explain_c4_model',
            'create_example_workspace',
        ];

        $promptNames = array_map(fn ($prompt) => $prompt->name, $prompts);

        foreach ($expectedPrompts as $expectedPrompt) {
            $this->assertContains(
                $expectedPrompt,
                $promptNames,
                "Expected prompt '{$expectedPrompt}' was not discovered"
            );
        }
    }

    /**
     * Test that discovery respects excludeDirs configuration
     */
    public function testDiscoveryRespectsExcludeDirs(): void
    {
        // Verify that excluded directories (like 'tests', 'vendor') are actually excluded
        // by confirming that real source tools are found but test files are not

        // Use fresh cache to avoid cached results from other tests
        $freshCache = new Psr16Cache(new ArrayAdapter());
        $registry = $this->createRegistryWithDiscovery(
            scanDirs: ['src', 'tests', 'vendor'], // Scan all directories including tests and vendor
            excludeDirs: ['tests', 'vendor'], // But exclude tests and vendor
            cache: $freshCache
        );

        $toolsPage = $registry->getTools();
        $tools = $toolsPage->references;
        $toolNames = array_map(fn ($tool) => $tool->name, $tools);

        // Should find real tools from src
        $this->assertContains('create_workspace', $toolNames, 'Discovery should find tools from src directory');

        // Should have exactly 23 tools (all from src, none from tests or vendor)
        $this->assertCount(23, $tools, 'Discovery should only find tools from non-excluded directories');

        // Verify none of the tool names contain patterns that would indicate they're from test files
        foreach ($toolNames as $toolName) {
            $this->assertStringNotContainsStringIgnoringCase('fake', $toolName, 'Should not discover fake/test tools');
            $this->assertStringNotContainsStringIgnoringCase('mock', $toolName, 'Should not discover mock tools');
        }
    }

    /**
     * Test that discovery respects scanDirs configuration
     */
    public function testDiscoveryRespectsScanDirs(): void
    {
        // Create temporary directory with a test tool
        $tempDir = $this->tempCacheDir . '/custom-tools';
        mkdir($tempDir, 0o755, true);

        $toolFile = $tempDir . '/CustomTool.php';
        file_put_contents(
            $toolFile,
            <<<'PHP'
<?php
namespace CustomTools;
use Mcp\Capability\Attribute\McpTool;
class CustomTool {
    #[McpTool(name: 'custom_tool', description: 'Custom test tool')]
    public function customTool(): array { return ['custom' => true]; }
}
PHP
        );

        // Discovery should NOT find this tool because scanDirs only includes 'src'
        $registry = $this->createRegistryWithDiscovery(
            scanDirs: ['src'], // Only scan src, not our temp directory
            excludeDirs: ['vendor', 'tests']
        );

        $toolsPage = $registry->getTools();
        $tools = $toolsPage->references;
        $toolNames = array_map(fn ($tool) => $tool->name, $tools);

        $this->assertNotContains('custom_tool', $toolNames, 'Discovery should only scan configured directories');
    }

    /**
     * Test that discovery cache works correctly
     */
    public function testDiscoveryCacheWorks(): void
    {
        // First discovery - should scan and cache
        $registry1 = $this->createRegistryWithDiscovery();
        $toolsPage1 = $registry1->getTools();
        $tools1 = $toolsPage1->references;
        $toolCount1 = count($tools1);

        // Second discovery with same cache - should use cached results
        $registry2 = $this->createRegistryWithDiscovery();
        $toolsPage2 = $registry2->getTools();
        $tools2 = $toolsPage2->references;
        $toolCount2 = count($tools2);

        // Should find same number of tools
        $this->assertEquals($toolCount1, $toolCount2, 'Cached discovery should return same results');
        $this->assertCount(23, $tools2, 'Cached discovery should find all 23 tools');
    }

    /**
     * Test that cache invalidation works correctly
     */
    public function testDiscoveryCacheInvalidation(): void
    {
        // First discovery with cache
        $registry1 = $this->createRegistryWithDiscovery();

        // Clear cache
        $this->cache->clear();

        // Second discovery - should re-scan since cache is cleared
        $registry2 = $this->createRegistryWithDiscovery();

        $toolsPage = $registry2->getTools();
        $tools = $toolsPage->references;

        // Should still find all tools after cache invalidation and re-scan
        $this->assertCount(23, $tools, 'Re-discovery after cache clear should find all 23 tools');
    }

    /**
     * Test that discovery works correctly with different base paths
     */
    public function testDiscoveryWithBasePath(): void
    {
        // Use project root as base path with more specific scan directory
        $registry = $this->createRegistryWithDiscovery(
            scanDirs: ['src/Tools'], // More specific scan directory
            excludeDirs: []
        );

        $toolsPage = $registry->getTools();
        $tools = $toolsPage->references;

        // Should find all 23 tools even with specific scan directory
        $this->assertCount(23, $tools, 'Discovery should work with specific base path and scan directories');
    }

    /**
     * Test that discovery handles missing directories gracefully
     */
    public function testDiscoveryHandlesMissingDirectory(): void
    {
        // Create registry with non-existent scan directory
        $registry = $this->createRegistryWithDiscovery(
            scanDirs: ['src', 'nonexistent-directory'], // Include non-existent dir
            excludeDirs: ['vendor', 'tests']
        );

        $toolsPage = $registry->getTools();
        $tools = $toolsPage->references;

        // Should still find tools from existing directories
        $this->assertGreaterThan(0, count($tools), 'Discovery should handle missing directories gracefully');
    }

    /**
     * Test that discovery handles invalid PHP files gracefully
     */
    public function testDiscoveryHandlesInvalidPhpFile(): void
    {
        // Create a temporary directory with an invalid PHP file
        $tempScanDir = $this->basePath . '/src/InvalidTest';
        mkdir($tempScanDir, 0o755, true);

        $invalidFile = $tempScanDir . '/InvalidSyntax.php';
        file_put_contents($invalidFile, '<?php this is not valid PHP syntax {{{{');

        try {
            // Discovery should handle the invalid file gracefully
            $freshCache = new Psr16Cache(new ArrayAdapter()); // Use fresh cache
            $registry = $this->createRegistryWithDiscovery(
                scanDirs: ['src'],
                excludeDirs: ['vendor', 'tests'],
                cache: $freshCache
            );

            $toolsPage = $registry->getTools();
            $tools = $toolsPage->references;

            // Should find valid tools despite invalid file
            $this->assertGreaterThan(0, count($tools), 'Discovery should continue despite invalid PHP files');
        } finally {
            // Clean up
            if (file_exists($invalidFile)) {
                unlink($invalidFile);
            }
            if (is_dir($tempScanDir)) {
                rmdir($tempScanDir);
            }
        }
    }

    /**
     * Test that all discovered tools have unique names
     */
    public function testAllToolsHaveUniqueNames(): void
    {
        $registry = $this->createRegistryWithDiscovery();

        $toolsPage = $registry->getTools();
        $tools = $toolsPage->references;
        $toolNames = array_map(fn ($tool) => $tool->name, $tools);

        // Check for uniqueness
        $uniqueNames = array_unique($toolNames);
        $this->assertEquals(
            count($toolNames),
            count($uniqueNames),
            'All tool names must be unique. Duplicates found: ' . json_encode(array_diff_assoc($toolNames, $uniqueNames))
        );
    }

    /**
     * Test that all discovered resources have unique URI templates or URIs
     */
    public function testAllResourcesHaveUniqueUriTemplates(): void
    {
        $registry = $this->createRegistryWithDiscovery();

        // Get static resources
        $resourcesPage = $registry->getResources();
        $resources = $resourcesPage->references;

        // Get resource templates
        $templatesPage = $registry->getResourceTemplates();
        $templates = $templatesPage->references;

        // Extract URIs or URI templates
        $uris = [];
        foreach ($resources as $resource) {
            if (isset($resource->uri)) {
                $uris[] = $resource->uri;
            }
        }
        foreach ($templates as $template) {
            if (isset($template->uriTemplate)) {
                $uris[] = $template->uriTemplate;
            }
        }

        // Check for uniqueness
        $uniqueUris = array_unique($uris);
        $this->assertEquals(
            count($uris),
            count($uniqueUris),
            'All resource URIs/templates must be unique. Duplicates found: ' . json_encode(array_diff_assoc($uris, $uniqueUris))
        );

        // Verify expected URIs are present
        $this->assertContains('structurizr://config', $uris, 'Static config resource URI should be present');
        $this->assertContains('structurizr://workspace/{workspaceId}', $uris, 'Workspace resource template should be present');
    }

    /**
     * Test that all discovered prompts have unique names
     */
    public function testAllPromptsHaveUniqueNames(): void
    {
        $registry = $this->createRegistryWithDiscovery();

        $promptsPage = $registry->getPrompts();
        $prompts = $promptsPage->references;
        $promptNames = array_map(fn ($prompt) => $prompt->name, $prompts);

        // Check for uniqueness
        $uniqueNames = array_unique($promptNames);
        $this->assertEquals(
            count($promptNames),
            count($uniqueNames),
            'All prompt names must be unique. Duplicates found: ' . json_encode(array_diff_assoc($promptNames, $uniqueNames))
        );

        // Verify expected prompts
        $this->assertContains('analyze_architecture', $promptNames);
        $this->assertContains('review_security', $promptNames);
        $this->assertContains('generate_system_context', $promptNames);
        $this->assertContains('create_from_description', $promptNames);
    }

    /**
     * Recursively remove a directory and its contents
     */
    private function recursiveRemoveDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $scanned = scandir($directory);
        if ($scanned === false) {
            return;
        }

        $files = array_diff($scanned, ['.', '..']);
        foreach ($files as $file) {
            $path = $directory . '/' . $file;
            if (is_dir($path)) {
                $this->recursiveRemoveDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($directory);
    }
}
