<?php

declare(strict_types=1);

namespace StructurizrMcp;

/**
 * Configuration management for Structurizr MCP server
 */
class Configuration
{
    private array $config;

    public function __construct()
    {
        $this->config = $this->loadConfiguration();
    }

    private function loadConfiguration(): array
    {
        // Load .env file if it exists
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    if (str_starts_with(trim($line), '#')) {
                        continue;
                    }
                    [$key, $value] = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }

        return [
            'structurizr' => [
                'api_key' => $this->getEnv('STRUCTURIZR_API_KEY', ''),
                'api_secret' => $this->getEnv('STRUCTURIZR_API_SECRET', ''),
                'api_url' => $this->getEnv('STRUCTURIZR_API_URL', 'https://api.structurizr.com'),
                'workspace_id' => $this->getEnv('STRUCTURIZR_WORKSPACE_ID', ''),
                'cli_path' => $this->getEnv('STRUCTURIZR_CLI_PATH', './bin/structurizr-cli.sh'),
            ],
            'storage' => [
                'workspace_path' => $this->getEnv('WORKSPACE_STORAGE_PATH', __DIR__ . '/../workspaces'),
            ],
            'logging' => [
                'level' => $this->getEnv('LOG_LEVEL', 'DEBUG'),
                'path' => $this->getEnv('LOG_PATH', 'php://stderr'),
            ],
            'server' => [
                'name' => $this->getEnv('SERVER_NAME', 'structurizr-mcp-server'),
                'version' => $this->getEnv('SERVER_VERSION', '1.0.0'),
            ],
        ];
    }

    private function getEnv(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function getStructurizrApiKey(): string
    {
        return $this->get('structurizr.api_key', '');
    }

    public function getStructurizrApiSecret(): string
    {
        return $this->get('structurizr.api_secret', '');
    }

    public function getStructurizrApiUrl(): string
    {
        return $this->get('structurizr.api_url', 'https://api.structurizr.com');
    }

    public function getStructurizrCliPath(): string
    {
        return $this->get('structurizr.cli_path', './bin/structurizr-cli.sh');
    }

    public function getWorkspacePath(): string
    {
        return $this->get('storage.workspace_path', __DIR__ . '/../workspaces');
    }

    public function getLogLevel(): string
    {
        return $this->get('logging.level', 'DEBUG');
    }

    public function getLogPath(): string
    {
        return $this->get('logging.path', 'php://stderr');
    }

    public function getServerName(): string
    {
        return $this->get('server.name', 'structurizr-mcp-server');
    }

    public function getServerVersion(): string
    {
        return $this->get('server.version', '1.0.0');
    }
}
