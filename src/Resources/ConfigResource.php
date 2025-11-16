<?php

declare(strict_types=1);

namespace StructurizrMcp\Resources;

use Mcp\Capability\Attribute\McpResource;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Configuration;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * MCP Resource for server configuration information
 *
 * Provides static configuration data about the Structurizr MCP server,
 * including server metadata, paths, and workspace statistics.
 */
class ConfigResource
{
    /**
     * Constructor
     *
     * @param Configuration $config Server configuration
     * @param WorkspaceManager $workspaceManager Manager for workspace operations
     * @param LoggerInterface $logger Logger for debugging and info messages
     */
    public function __construct(
        private readonly Configuration $config,
        private readonly WorkspaceManager $workspaceManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Get server configuration
     *
     * Returns comprehensive server configuration including:
     * - Server name and version
     * - Available workspace count
     * - Structurizr CLI path (if configured)
     * - Log level
     * - Storage path
     *
     * @return array<string, mixed> Server configuration data
     */
    #[McpResource(
        uri: 'structurizr://config',
        name: 'server_config',
        description: 'Server configuration and metadata',
        mimeType: 'application/json'
    )]
    public function getConfig(): array
    {
        $this->logger->debug('Retrieving server configuration');

        $workspaces = $this->workspaceManager->list();
        $workspaceCount = count($workspaces);

        return [
            'server' => [
                'name' => $this->config->getServerName(),
                'version' => $this->config->getServerVersion(),
            ],
            'structurizr' => [
                'cliPath' => $this->config->getStructurizrCliPath(),
                'apiUrl' => $this->config->getStructurizrApiUrl(),
            ],
            'storage' => [
                'workspacePath' => $this->config->getWorkspacePath(),
                'workspaceCount' => $workspaceCount,
            ],
            'logging' => [
                'level' => $this->config->getLogLevel(),
                'path' => $this->config->getLogPath(),
            ],
        ];
    }
}
