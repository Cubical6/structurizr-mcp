<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr\Executor;

use Psr\Log\LoggerInterface;
use StructurizrMcp\Structurizr\ProcessResult;
use Symfony\Component\Process\Process;

/**
 * Executor for locally installed Structurizr CLI
 *
 * Uses a locally installed CLI executable at a specified path.
 * This is the preferred executor when available as it has no Docker overhead.
 *
 * Usage contract:
 * - Callers MUST call isAvailable() before execute()
 * - If isAvailable() returns false, execute() will throw RuntimeException
 */
class LocalCliExecutor implements CliExecutorInterface
{
    private ?string $resolvedPath = null;

    public function __construct(
        private readonly ?string $cliPath,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(array $args, int $timeout, ?string $workingDirectory = null): ProcessResult
    {
        $command = array_merge([$this->getResolvedPath()], $args);

        $this->logger->debug('Executing local CLI command', [
            'command' => $command,
            'timeout' => $timeout,
            'workingDirectory' => $workingDirectory,
        ]);

        $process = new Process($command);
        $process->setTimeout($timeout);

        if ($workingDirectory !== null) {
            $process->setWorkingDirectory($workingDirectory);
        }

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
        if ($this->cliPath === null || $this->cliPath === '') {
            return false;
        }

        $resolved = realpath($this->cliPath);
        if ($resolved === false || !is_executable($resolved)) {
            return false;
        }

        $this->resolvedPath = $resolved;
        $this->logger->debug('Local CLI executor available', ['path' => $resolved]);

        return true;
    }

    public function getName(): string
    {
        return 'local';
    }

    private function getResolvedPath(): string
    {
        if ($this->resolvedPath === null) {
            throw new \RuntimeException('LocalCliExecutor is not available');
        }

        return $this->resolvedPath;
    }
}
