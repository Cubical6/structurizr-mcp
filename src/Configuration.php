<?php

declare(strict_types=1);

namespace StructurizrMcp;

/**
 * Configuration management for Structurizr MCP server
 */
class Configuration
{
    /** Default Structurizr API URL */
    private const DEFAULT_API_URL = 'https://api.structurizr.com';

    /** Default Docker image for CLI */
    private const DEFAULT_DOCKER_IMAGE = 'structurizr/cli:latest';

    /** Default MCP server name */
    private const DEFAULT_SERVER_NAME = 'structurizr-mcp-server';

    /** Default MCP server version */
    private const DEFAULT_SERVER_VERSION = '1.0.0';

    /** Default log level */
    private const DEFAULT_LOG_LEVEL = 'DEBUG';

    /**
     * Configuration array loaded from environment variables and .env file
     *
     * @var array<string, mixed>
     */
    private array $config;

    public function __construct()
    {
        $this->config = $this->loadConfiguration();
    }

    /**
     * Load configuration from environment variables and .env file
     *
     * @return array<string, array<string, string|null>>
     */
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
                    $parts = explode('=', $line, 2);
                    if (count($parts) === 2) {
                        [$key, $value] = $parts;
                        $_ENV[trim($key)] = trim($value);
                    }
                }
            }
        }

        return [
            'structurizr' => [
                'api_key' => $this->getEnv('STRUCTURIZR_API_KEY', ''),
                'api_secret' => $this->getEnv('STRUCTURIZR_API_SECRET', ''),
                'api_url' => $this->getEnv('STRUCTURIZR_API_URL', self::DEFAULT_API_URL),
                'workspace_id' => $this->getEnv('STRUCTURIZR_WORKSPACE_ID', ''),
                'cli_path' => $this->getEnvOrNull('STRUCTURIZR_CLI_PATH'),
                'docker_image' => $this->getEnv('STRUCTURIZR_DOCKER_IMAGE', self::DEFAULT_DOCKER_IMAGE),
            ],
            'storage' => [
                'workspace_path' => $this->getEnv('WORKSPACE_STORAGE_PATH', __DIR__ . '/../workspaces'),
            ],
            'logging' => [
                'level' => $this->getEnv('LOG_LEVEL', self::DEFAULT_LOG_LEVEL),
                'path' => $this->getEnv('LOG_PATH', 'php://stderr'),
            ],
            'server' => [
                'name' => $this->getEnv('SERVER_NAME', self::DEFAULT_SERVER_NAME),
                'version' => $this->getEnv('SERVER_VERSION', self::DEFAULT_SERVER_VERSION),
            ],
        ];
    }

    private function getEnv(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Get environment variable or null if not set
     *
     * @param string $key Environment variable name
     * @return string|null Value of the environment variable, or null if not set
     */
    private function getEnvOrNull(string $key): ?string
    {
        $value = $_ENV[$key] ?? getenv($key) ?: null;

        return $value !== null && $value !== '' ? $value : null;
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
        return $this->get('structurizr.api_url', self::DEFAULT_API_URL);
    }

    /**
     * Get CLI path or null for auto-detection
     */
    public function getStructurizrCliPath(): ?string
    {
        return $this->get('structurizr.cli_path');
    }

    /**
     * Get Docker image for CLI
     */
    public function getDockerImage(): string
    {
        return $this->get('structurizr.docker_image', self::DEFAULT_DOCKER_IMAGE);
    }

    public function getWorkspacePath(): string
    {
        return $this->get('storage.workspace_path', __DIR__ . '/../workspaces');
    }

    public function getLogLevel(): string
    {
        return $this->get('logging.level', self::DEFAULT_LOG_LEVEL);
    }

    public function getLogPath(): string
    {
        return $this->get('logging.path', 'php://stderr');
    }

    public function getServerName(): string
    {
        return $this->get('server.name', self::DEFAULT_SERVER_NAME);
    }

    public function getServerVersion(): string
    {
        return $this->get('server.version', self::DEFAULT_SERVER_VERSION);
    }
}
