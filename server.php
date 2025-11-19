#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Mcp\Capability\Registry\Container;
use StructurizrMcp\Structurizr\CliWrapperInterface;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Configuration;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Structurizr\CliWrapper;
use StructurizrMcp\Structurizr\NullCliWrapper;
use StructurizrMcp\Exception\CliExecutionException;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Load configuration
$config = new Configuration();

// Setup logging (MUST use STDERR for MCP servers, STDOUT is for protocol messages)
$logger = new Logger('structurizr-mcp');
$logLevel = match (strtoupper($config->getLogLevel())) {
    'DEBUG' => Logger::DEBUG,
    'INFO' => Logger::INFO,
    'WARNING' => Logger::WARNING,
    'ERROR' => Logger::ERROR,
    default => Logger::DEBUG,
};
$logger->pushHandler(new StreamHandler('php://stderr', $logLevel));

$logger->info('Structurizr MCP Server starting...');
$logger->debug('Configuration loaded', [
    'workspacePath' => $config->getWorkspacePath(),
    'serverName' => $config->getServerName(),
    'serverVersion' => $config->getServerVersion(),
]);

// Ensure cache directory exists
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0o755, true);
}

try {
    // Initialize workspace manager
    $workspaceManager = new WorkspaceManager(
        storagePath: $config->getWorkspacePath(),
        logger: $logger
    );

    // Initialize PSR-16 cache for discovery
    $phpFileCache = new PhpFilesAdapter(
        directory: __DIR__ . '/cache',
        namespace: 'structurizr-mcp',
        defaultLifetime: 3600
    );
    $cache = new Psr16Cache($phpFileCache);

    $logger->debug('Cache initialized', ['cacheDir' => __DIR__ . '/cache']);

    // Initialize CliWrapper with graceful degradation to NullCliWrapper
    $cliWrapper = null;
    $cliPath = $config->getStructurizrCliPath();
    if (!empty($cliPath)) {
        try {
            $cliWrapper = new CliWrapper($cliPath, $logger);
            $logger->info('CliWrapper initialized successfully', ['cliPath' => $cliPath]);
        } catch (CliExecutionException $e) {
            $logger->warning('CliWrapper initialization failed - using NullCliWrapper fallback', [
                'cliPath' => $cliPath,
                'error' => $e->getMessage(),
            ]);
            $cliWrapper = new NullCliWrapper($logger);
        }
    } else {
        $logger->warning('CLI path not configured - using NullCliWrapper fallback');
        $cliWrapper = new NullCliWrapper($logger);
    }

    // Create PSR-11 container for dependency injection
    $container = new Container();

    // Register core dependencies
    $container->set(LoggerInterface::class, $logger);
    $container->set(WorkspaceManager::class, $workspaceManager);
    $container->set(Configuration::class, $config);

    // Register CliWrapperInterface (or NullCliWrapper) - always an object, never null
    $container->set(CliWrapperInterface::class, $cliWrapper);
    $logger->debug('CliWrapperInterface registered in container', [
        'type' => $cliWrapper instanceof CliWrapper ? 'CliWrapper' : 'NullCliWrapper'
    ]);

    $logger->debug('Dependency injection container configured');

    // Build MCP server with automatic discovery
    $server = Server::builder()
        ->setServerInfo(
            name: $config->getServerName(),
            version: $config->getServerVersion(),
            description: 'MCP server for Structurizr - Create and manage C4 architecture diagrams as code'
        )
        ->setInstructions(
            'Use this server to create and manage Structurizr workspaces, ' .
            'add architectural elements (people, systems, containers, components), ' .
            'create relationships, and generate C4 diagrams. ' .
            'Start by creating a workspace, then add elements to build your architecture model.'
        )
        ->setLogger($logger)
        ->setContainer($container)
        ->setDiscovery(
            basePath: __DIR__,
            scanDirs: ['src'],
            excludeDirs: ['vendor', 'tests', 'cache', 'sessions', 'workspaces', 'docs', 'examples'],
            cache: $cache
        )
        ->build();

    $logger->info('MCP Server built successfully');
    $logger->debug('Server capabilities registered via auto-discovery');

    // Run server with STDIO transport
    $transport = new StdioTransport(logger: $logger);

    $logger->info('Starting MCP server with STDIO transport');
    $logger->info('Server is ready to accept connections');

    $exitCode = $server->run($transport);

    $logger->info("Server stopped with exit code: {$exitCode}");
    exit($exitCode);

} catch (\Throwable $e) {
    $logger->error('Fatal error starting server', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    // Output error to stderr for debugging
    fwrite(STDERR, "FATAL ERROR: " . $e->getMessage() . "\n");
    fwrite(STDERR, "File: " . $e->getFile() . ":" . $e->getLine() . "\n");

    exit(1);
}
