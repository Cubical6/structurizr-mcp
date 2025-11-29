<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr\Executor;

use Psr\Log\LoggerInterface;
use StructurizrMcp\Structurizr\ProcessResult;
use Symfony\Component\Process\Process;

/**
 * Executor for Structurizr CLI via Docker
 *
 * Uses the official structurizr/cli Docker image when no local CLI is available.
 * Automatically handles volume mounting and path transformation.
 *
 * Usage contract:
 * - Callers MUST call isAvailable() before execute()
 * - Paths in arguments are automatically transformed to container paths
 * - Supports both short (-workspace) and long (--workspace) flag forms
 * - Supports equals syntax (-workspace=path, --workspace=path)
 */
class DockerCliExecutor implements CliExecutorInterface
{
    private const DEFAULT_IMAGE = 'structurizr/cli:latest';
    private const CONTAINER_WORKSPACE = '/usr/local/structurizr';

    private bool $dockerAvailable = false;
    private bool $checkedAvailability = false;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $image = self::DEFAULT_IMAGE,
    ) {
    }

    public function execute(array $args, int $timeout, ?string $workingDirectory = null): ProcessResult
    {
        $workingDirectory ??= getcwd() ?: sys_get_temp_dir();

        // Build docker command
        $command = [
            'docker', 'run',
            '--rm',                                                        // Remove container after execution
            '-v', "{$workingDirectory}:" . self::CONTAINER_WORKSPACE,      // Mount working directory
            '-w', self::CONTAINER_WORKSPACE,                               // Set container working directory
            $this->image,
        ];

        // Transform file paths in args to container paths
        $transformedArgs = $this->transformArguments($args, $workingDirectory);
        $command = array_merge($command, $transformedArgs);

        $this->logger->debug('Executing Docker CLI command', [
            'command' => $command,
            'workingDirectory' => $workingDirectory,
        ]);

        $process = new Process($command);
        $process->setTimeout($timeout);
        $process->run();

        return new ProcessResult(
            exitCode: $process->getExitCode() ?? 1,
            stdout: $process->getOutput(),
            stderr: $process->getErrorOutput(),
            success: $process->isSuccessful(),
        );
    }

    public function isAvailable(): bool
    {
        if ($this->checkedAvailability) {
            return $this->dockerAvailable;
        }

        $this->checkedAvailability = true;

        // Check if docker command exists
        $process = new Process(['docker', '--version']);
        $process->setTimeout(5);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->debug('Docker not available: docker command not found');

            return false;
        }

        // Check if we can access docker (daemon running, permissions OK)
        $process = new Process(['docker', 'info']);
        $process->setTimeout(10);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->debug('Docker not available: cannot connect to daemon', [
                'error' => $process->getErrorOutput(),
            ]);

            return false;
        }

        $this->dockerAvailable = true;
        $this->logger->info('Docker executor available', ['image' => $this->image]);

        return true;
    }

    public function getName(): string
    {
        return 'docker';
    }

    /**
     * Path flags that require transformation (short and long forms)
     *
     * @var array<string>
     */
    private const PATH_FLAGS = [
        '-workspace', '--workspace',
        '-output', '--output',
        '-w', '-o',
    ];

    /**
     * Transform file path arguments to container paths
     *
     * Handles multiple argument formats:
     * - Separate flag and value: -workspace /path/to/file
     * - Equals syntax: -workspace=/path/to/file, --workspace=/path/to/file
     *
     * @param array<string> $args Original arguments
     * @param string $workingDirectory Host working directory
     * @return array<string> Transformed arguments
     */
    private function transformArguments(array $args, string $workingDirectory): array
    {
        $transformed = [];
        $expectPath = false;

        foreach ($args as $arg) {
            if ($expectPath) {
                // Transform absolute path to container path
                $transformed[] = $this->toContainerPath($arg, $workingDirectory);
                $expectPath = false;

                continue;
            }

            // Check for equals syntax: -workspace=/path or --workspace=/path
            $equalsTransformed = $this->transformEqualsArg($arg, $workingDirectory);
            if ($equalsTransformed !== null) {
                $transformed[] = $equalsTransformed;

                continue;
            }

            // Check if this is a path flag (next arg will be the path)
            if ($this->isPathFlag($arg)) {
                $expectPath = true;
            }

            $transformed[] = $arg;
        }

        return $transformed;
    }

    /**
     * Check if argument is a path flag
     */
    private function isPathFlag(string $arg): bool
    {
        return in_array($arg, self::PATH_FLAGS, true);
    }

    /**
     * Transform argument with equals syntax (e.g., -workspace=/path)
     *
     * @return string|null Transformed argument or null if not equals syntax
     */
    private function transformEqualsArg(string $arg, string $workingDirectory): ?string
    {
        foreach (self::PATH_FLAGS as $flag) {
            $prefix = $flag . '=';
            if (str_starts_with($arg, $prefix)) {
                $path = substr($arg, strlen($prefix));
                $containerPath = $this->toContainerPath($path, $workingDirectory);

                return $flag . '=' . $containerPath;
            }
        }

        return null;
    }

    /**
     * Convert host path to container path
     */
    private function toContainerPath(string $hostPath, string $workingDirectory): string
    {
        // If path is within working directory, make it relative to container workspace
        $realHostPath = realpath($hostPath);
        $realWorkingDir = realpath($workingDirectory);

        if ($realHostPath !== false && $realWorkingDir !== false) {
            if (str_starts_with($realHostPath, $realWorkingDir)) {
                $relativePath = substr($realHostPath, strlen($realWorkingDir));
                $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);

                return self::CONTAINER_WORKSPACE . '/' . $relativePath;
            }
        }

        // For paths outside working directory, use basename (file must be copied first)
        // This is a limitation - log warning
        $this->logger->warning('Path outside working directory may not be accessible in Docker', [
            'path' => $hostPath,
            'workingDirectory' => $workingDirectory,
        ]);

        return self::CONTAINER_WORKSPACE . '/' . basename($hostPath);
    }
}
