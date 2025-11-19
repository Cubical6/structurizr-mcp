# Test Helper Traits

This directory contains reusable test helper traits for the Structurizr MCP Server project.

## Overview

The test helper traits provide factory methods and utilities for creating test dependencies, building test servers, and managing test environments. They promote code reuse, consistency, and maintainability across the test suite.

## Files

### 1. ContainerTestTrait.php
**263 lines** | **8.7 KB**

Provides helper methods for creating test dependencies and configuration.

**Key Methods:**
- `createTestConfiguration(array $overrides = []): Configuration` - Create test configuration
- `createTestLogger(): LoggerInterface` - Create null logger for testing
- `createTestLoggerWithHandler(): array` - Create logger with test handler for verification
- `createTestWorkspaceManager(): WorkspaceManager` - Create workspace manager
- `createTestCliWrapper(): CliWrapper` - Create CLI wrapper
- `createTestCache(): CacheInterface` - Create in-memory cache
- `resetTestEnvironment(): void` - Clean up environment variables
- `createTestWorkspaceData(): array` - Create test workspace data structure
- `createTestElementData(): array` - Create test element data structure

**Use Cases:**
- Unit tests requiring mocked dependencies
- Integration tests needing real components with test configuration
- Tests that need to verify log output
- Tests requiring test data structures

### 2. ServerTestTrait.php
**358 lines** | **12 KB**

Provides utilities for MCP server testing and temporary directory management.

**Key Methods:**
- `buildTestServer(array $config = []): Server` - Build configured test server
- `getServerCapabilities(Server $server): array` - Extract server capabilities via reflection
- `createTempWorkspaceDir(?string $prefix = null): string` - Create temporary workspace directory
- `cleanupTempWorkspaceDir(string $path): void` - Remove temporary directory
- `createInMemoryCache(): CacheInterface` - Create in-memory PSR-16 cache
- `assertServerHasTool(Server $server, string $toolName): void` - Assert tool registration
- `assertServerHasResource(Server $server, string $resourceUri): void` - Assert resource registration
- `assertServerHasPrompt(Server $server, string $promptName): void` - Assert prompt registration
- `getTestBasePath(): string` - Get project root path
- `createTestWorkspaceFiles(string $storagePath, int $count): array` - Create test workspace files
- `assertMethodExists(object $object, string $methodName): void` - Assert method exists

**Use Cases:**
- Server integration tests
- Testing MCP capability registration
- Testing with multiple workspace files
- Temporary file/directory management

### 3. ExampleTraitUsageTest.php
**286 lines** | **9.1 KB**

Example test class demonstrating proper usage of both traits.

**Features:**
- 15 test methods showing different usage patterns
- Examples of using traits individually and together
- Demonstrates proper setup/teardown procedures
- Shows how to verify server capabilities
- Illustrates workspace and element creation

**Test Categories:**
- Configuration creation (Examples 1)
- Logger creation and verification (Examples 2-3)
- Dependency creation (Examples 4, 14)
- Test data creation (Examples 5-6)
- Server building and inspection (Examples 7-9)
- File management (Example 10)
- Integration testing (Example 11)
- Helper utilities (Examples 12-13)
- Environment cleanup (Example 15)

### 4. USAGE_EXAMPLES.md
**574 lines** | **16 KB**

Comprehensive documentation with usage examples.

**Contents:**
- 10 detailed code examples
- Best practices guide
- Common patterns reference
- Real-world usage scenarios
- Tips for test isolation and performance

## Quick Start

### Basic Usage - Unit Test

```php
<?php

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;

class MyTest extends TestCase
{
    use ContainerTestTrait;

    public function testSomething(): void
    {
        $logger = $this->createTestLogger();
        $manager = $this->createTestWorkspaceManager($logger);

        // Use the manager in your test
        $workspace = $manager->create('Test', 'Description');
        $this->assertEquals('Test', $workspace->name);
    }
}
```

### Basic Usage - Server Test

```php
<?php

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ServerTestTrait;

class ServerTest extends TestCase
{
    use ServerTestTrait;

    public function testServerCapabilities(): void
    {
        $server = $this->buildTestServer();

        $this->assertServerHasTool($server, 'create_workspace');
        $this->assertServerHasResource($server, 'structurizr://config');
    }
}
```

### Combined Usage - Integration Test

```php
<?php

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tests\Helpers\ContainerTestTrait;
use StructurizrMcp\Tests\Helpers\ServerTestTrait;

class IntegrationTest extends TestCase
{
    use ContainerTestTrait;
    use ServerTestTrait;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = $this->createTempWorkspaceDir();
    }

    protected function tearDown(): void
    {
        $this->cleanupTempWorkspaceDir($this->tempDir);
        $this->resetTestEnvironment();
    }

    public function testCompleteWorkflow(): void
    {
        // Create dependencies
        $logger = $this->createTestLogger();
        $manager = $this->createTestWorkspaceManager($logger, $this->tempDir);

        // Build server
        $server = $this->buildTestServer(['logger' => $logger]);

        // Test workflow
        $workspace = $manager->create('Integration Test');
        $this->assertServerHasTool($server, 'create_workspace');
    }
}
```

## Design Principles

### 1. **Reusability**
All methods are designed to be reused across multiple test classes and scenarios.

### 2. **Isolation**
Each method creates independent instances to prevent test interference.

### 3. **Flexibility**
Methods accept optional parameters for customization while providing sensible defaults.

### 4. **Cleanliness**
Helper methods for cleanup ensure tests don't leave artifacts.

### 5. **Documentation**
Comprehensive docblocks explain purpose, parameters, and return values.

## Best Practices

### ✅ DO

- Use `ContainerTestTrait` for creating test dependencies
- Use `ServerTestTrait` for server-related testing
- Always clean up temporary directories in `tearDown()`
- Reset environment variables after configuration tests
- Use `NullLogger` by default, `TestHandler` only when verifying logs
- Create unique temporary directories for each test
- Use descriptive test names

### ❌ DON'T

- Don't share temporary directories between tests
- Don't forget to call cleanup methods in `tearDown()`
- Don't modify global state without resetting it
- Don't create real files in the project directory during tests
- Don't use real Structurizr CLI for unit tests (mock it)

## Testing the Helpers

To verify the helper traits work correctly, run the example test:

```bash
# Run the example test
./vendor/bin/phpunit tests/Helpers/ExampleTraitUsageTest.php

# Run with verbose output
./vendor/bin/phpunit --testdox tests/Helpers/ExampleTraitUsageTest.php

# Run specific test
./vendor/bin/phpunit --filter testCombinedTraitUsage tests/Helpers/ExampleTraitUsageTest.php
```

## Integration with Existing Tests

The traits can be progressively integrated into existing test classes:

1. Add `use ContainerTestTrait;` or `use ServerTestTrait;` to test class
2. Replace manual dependency creation with trait methods
3. Update `setUp()` and `tearDown()` to use trait cleanup methods
4. Refactor test data creation to use trait factory methods

## Performance Considerations

- **In-memory cache**: Faster than file-based cache for tests
- **NullLogger**: No I/O overhead for logging
- **Temporary directories**: Created in system temp with unique IDs
- **Array adapter**: Fast PSR-16 cache for testing

## File Size Summary

| File | Lines | Size | Purpose |
|------|-------|------|---------|
| ContainerTestTrait.php | 263 | 8.7 KB | Dependency creation |
| ServerTestTrait.php | 358 | 12 KB | Server testing utilities |
| ExampleTraitUsageTest.php | 286 | 9.1 KB | Usage examples (runnable) |
| USAGE_EXAMPLES.md | 574 | 16 KB | Documentation examples |
| README.md | This file | - | Overview and guide |
| **Total** | **1,481** | **~46 KB** | Complete helper suite |

## Related Documentation

- [Main Test README](../README.md) - Overall test suite documentation
- [USAGE_EXAMPLES.md](./USAGE_EXAMPLES.md) - Detailed code examples
- [PHPUnit Documentation](https://phpunit.de/documentation.html) - PHPUnit framework
- [PSR-3 Logger](https://www.php-fig.org/psr/psr-3/) - Logger interface standard
- [PSR-16 Cache](https://www.php-fig.org/psr/psr-16/) - Simple Cache standard

## Contributing

When adding new helper methods:

1. Follow PSR-12 coding standards
2. Add comprehensive docblocks
3. Include usage examples in USAGE_EXAMPLES.md
4. Add test cases to ExampleTraitUsageTest.php
5. Update this README with method descriptions

## License

This code is part of the Structurizr MCP Server project and follows the same license.

## Support

For questions or issues with the test helpers:
1. Check USAGE_EXAMPLES.md for code examples
2. Run ExampleTraitUsageTest.php to verify functionality
3. Review existing test classes for real-world usage patterns
4. Consult the main project documentation

---

**Version**: 1.0.0
**Last Updated**: 2025-11-17
**Maintainer**: Structurizr MCP Server Team
