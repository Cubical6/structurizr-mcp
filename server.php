#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use StructurizrMcp\Configuration;
use StructurizrMcp\Structurizr\WorkspaceManager;
use StructurizrMcp\Tools\WorkspaceTools;
use StructurizrMcp\Tools\ModelTools;
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
    mkdir($cacheDir, 0755, true);
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

    // Initialize tool classes
    $workspaceTools = new WorkspaceTools($workspaceManager, $logger);
    $modelTools = new ModelTools($workspaceManager, $logger);

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
