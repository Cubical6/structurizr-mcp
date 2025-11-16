<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr;

use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\CliExecutionException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Wrapper for Structurizr CLI operations
 *
 * Provides secure execution of Structurizr CLI commands with proper
 * error handling, logging, and validation.
 *
 * Security features:
 * - Uses Symfony Process with array form (prevents shell injection)
 * - Validates all file paths with realpath()
 * - Sanitizes credentials from logs
 * - Sets appropriate timeouts for different operations
 */
class CliWrapper
{
    private const TIMEOUT_VALIDATION = 30;
    private const TIMEOUT_EXPORT = 30;
    private const TIMEOUT_CLOUD_OPS = 60;

    private readonly string $cliPath;

    /**
     * @param string $cliPath Path to the Structurizr CLI executable
     * @param LoggerInterface $logger Logger instance
     * @throws CliExecutionException If CLI executable not found or not executable
     */
    public function __construct(
        string $cliPath,
        private readonly LoggerInterface $logger,
    ) {
        // Validate CLI path exists and is executable
        $resolvedPath = realpath($cliPath);
        if ($resolvedPath === false) {
            throw new CliExecutionException(
                'structurizr-cli',
                "CLI executable not found at path: {$cliPath}",
            );
        }

        if (!is_executable($resolvedPath)) {
            throw new CliExecutionException(
                'structurizr-cli',
                "CLI path is not executable: {$resolvedPath}",
            );
        }

        $this->cliPath = $resolvedPath;
        $this->logger->info('CliWrapper initialized', ['cliPath' => $this->cliPath]);
    }

    /**
     * Execute a Structurizr CLI command
     *
     * @param array<string> $args Command arguments (excluding the CLI executable)
     * @param int $timeout Timeout in seconds
     * @return ProcessResult
     * @throws CliExecutionException If command execution fails
     */
    public function executeCommand(array $args, int $timeout = self::TIMEOUT_VALIDATION): ProcessResult
    {
        // Build command array (ARRAY form for security)
        $command = array_merge([$this->cliPath], $args);

        // Sanitize command for logging (remove potential credentials)
        $sanitizedCommand = $this->sanitizeCommandForLogging($command);

        $this->logger->debug('Executing CLI command', [
            'command' => $sanitizedCommand,
            'timeout' => $timeout,
        ]);

        try {
            // Create process with array form (prevents shell injection)
            $process = new Process($command);
            $process->setTimeout($timeout);
            $process->run();

            $result = new ProcessResult(
                exitCode: $process->getExitCode() ?? 1,
                stdout: $process->getOutput(),
                stderr: $process->getErrorOutput(),
                success: $process->isSuccessful(),
            );

            if ($result->isSuccess()) {
                $this->logger->debug('CLI command successful', [
                    'exitCode' => $result->getExitCode(),
                ]);
            } else {
                $this->logger->warning('CLI command failed', [
                    'exitCode' => $result->getExitCode(),
                    'error' => $result->getErrorMessage(),
                ]);
            }

            return $result;
        } catch (ProcessFailedException $e) {
            $this->logger->error('CLI process failed', [
                'command' => $sanitizedCommand,
                'error' => $e->getMessage(),
            ]);

            throw new CliExecutionException(
                implode(' ', $sanitizedCommand),
                $e->getMessage(),
                $e,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Unexpected error during CLI execution', [
                'command' => $sanitizedCommand,
                'error' => $e->getMessage(),
            ]);

            throw new CliExecutionException(
                implode(' ', $sanitizedCommand),
                'Unexpected error: ' . $e->getMessage(),
                $e,
            );
        }
    }

    /**
     * Validate a Structurizr DSL file
     *
     * @param string $dslPath Path to the DSL file
     * @return ValidationResult
     * @throws CliExecutionException If validation fails or file not found
     */
    public function validate(string $dslPath): ValidationResult
    {
        // Validate and resolve path
        $resolvedPath = $this->validateFilePath($dslPath, 'DSL file');

        $this->logger->info('Validating DSL file', ['path' => $resolvedPath]);

        $result = $this->executeCommand(
            ['validate', '-workspace', $resolvedPath],
            self::TIMEOUT_VALIDATION,
        );

        return $this->parseValidationResult($result);
    }

    /**
     * Export workspace to various formats
     *
     * @param string $workspacePath Path to workspace (DSL or JSON)
     * @param string $format Export format (plantuml, mermaid, dot, ilograph, json, dsl, etc.)
     * @param string|null $outputPath Optional output path (if null, returns to stdout)
     * @return string Exported content or path to output file
     * @throws CliExecutionException If export fails
     */
    public function export(string $workspacePath, string $format, ?string $outputPath = null): string
    {
        // Validate and resolve workspace path
        $resolvedWorkspacePath = $this->validateFilePath($workspacePath, 'Workspace file');

        $this->logger->info('Exporting workspace', [
            'workspace' => $resolvedWorkspacePath,
            'format' => $format,
            'output' => $outputPath,
        ]);

        $args = ['export', '-workspace', $resolvedWorkspacePath, '-format', $format];

        if ($outputPath !== null) {
            // Validate output directory exists
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                throw new CliExecutionException(
                    'export',
                    "Output directory does not exist: {$outputDir}",
                );
            }

            $args[] = '-output';
            $args[] = $outputPath;
        }

        $result = $this->executeCommand($args, self::TIMEOUT_EXPORT);

        if (!$result->isSuccess()) {
            throw new CliExecutionException(
                'export',
                "Export failed: {$result->getErrorMessage()}",
            );
        }

        // Return output path if specified, otherwise return stdout
        return $outputPath ?? $result->getStdout();
    }

    /**
     * Push workspace to Structurizr cloud/on-premises
     *
     * @param string $workspacePath Path to workspace DSL file
     * @param int $workspaceId Structurizr workspace ID
     * @param string $apiKey Structurizr API key
     * @param string $apiSecret Structurizr API secret
     * @param string|null $apiUrl Optional API URL (default: https://api.structurizr.com)
     * @return ProcessResult
     * @throws CliExecutionException If push fails
     */
    public function push(
        string $workspacePath,
        int $workspaceId,
        string $apiKey,
        string $apiSecret,
        ?string $apiUrl = null,
    ): ProcessResult {
        // Validate and resolve workspace path
        $resolvedWorkspacePath = $this->validateFilePath($workspacePath, 'Workspace file');

        $this->logger->info('Pushing workspace to Structurizr', [
            'workspace' => $resolvedWorkspacePath,
            'workspaceId' => $workspaceId,
            'apiUrl' => $apiUrl ?? 'https://api.structurizr.com',
        ]);

        $args = [
            'push',
            '-id', (string)$workspaceId,
            '-key', $apiKey,
            '-secret', $apiSecret,
            '-workspace', $resolvedWorkspacePath,
        ];

        if ($apiUrl !== null) {
            $args[] = '-url';
            $args[] = $apiUrl;
        }

        $result = $this->executeCommand($args, self::TIMEOUT_CLOUD_OPS);

        if (!$result->isSuccess()) {
            throw new CliExecutionException(
                'push',
                "Push failed: {$result->getErrorMessage()}",
            );
        }

        $this->logger->info('Workspace pushed successfully', ['workspaceId' => $workspaceId]);

        return $result;
    }

    /**
     * Pull workspace from Structurizr cloud/on-premises
     *
     * @param int $workspaceId Structurizr workspace ID
     * @param string $apiKey Structurizr API key
     * @param string $apiSecret Structurizr API secret
     * @param string $outputPath Path to save the workspace JSON
     * @param string|null $apiUrl Optional API URL (default: https://api.structurizr.com)
     * @return ProcessResult
     * @throws CliExecutionException If pull fails
     */
    public function pull(
        int $workspaceId,
        string $apiKey,
        string $apiSecret,
        string $outputPath,
        ?string $apiUrl = null,
    ): ProcessResult {
        // Validate output directory exists
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            throw new CliExecutionException(
                'pull',
                "Output directory does not exist: {$outputDir}",
            );
        }

        $this->logger->info('Pulling workspace from Structurizr', [
            'workspaceId' => $workspaceId,
            'output' => $outputPath,
            'apiUrl' => $apiUrl ?? 'https://api.structurizr.com',
        ]);

        $args = [
            'pull',
            '-id', (string)$workspaceId,
            '-key', $apiKey,
            '-secret', $apiSecret,
            '-workspace', $outputPath,
        ];

        if ($apiUrl !== null) {
            $args[] = '-url';
            $args[] = $apiUrl;
        }

        $result = $this->executeCommand($args, self::TIMEOUT_CLOUD_OPS);

        if (!$result->isSuccess()) {
            throw new CliExecutionException(
                'pull',
                "Pull failed: {$result->getErrorMessage()}",
            );
        }

        $this->logger->info('Workspace pulled successfully', [
            'workspaceId' => $workspaceId,
            'output' => $outputPath,
        ]);

        return $result;
    }

    /**
     * Get CLI version information
     *
     * @return string Version string
     */
    public function getVersion(): string
    {
        $result = $this->executeCommand(['version'], 5);

        if (!$result->isSuccess()) {
            return 'unknown';
        }

        return trim($result->getStdout());
    }

    /**
     * Validate file path and return resolved absolute path
     *
     * @param string $path Path to validate
     * @param string $description Description for error messages
     * @return string Resolved absolute path
     * @throws CliExecutionException If path is invalid
     */
    private function validateFilePath(string $path, string $description): string
    {
        $resolvedPath = realpath($path);

        if ($resolvedPath === false) {
            throw new CliExecutionException(
                'validate-path',
                "{$description} not found: {$path}",
            );
        }

        if (!is_file($resolvedPath)) {
            throw new CliExecutionException(
                'validate-path',
                "{$description} is not a file: {$resolvedPath}",
            );
        }

        if (!is_readable($resolvedPath)) {
            throw new CliExecutionException(
                'validate-path',
                "{$description} is not readable: {$resolvedPath}",
            );
        }

        return $resolvedPath;
    }

    /**
     * Parse validation result from CLI output
     *
     * @param ProcessResult $result CLI process result
     * @return ValidationResult
     */
    private function parseValidationResult(ProcessResult $result): ValidationResult
    {
        $errors = [];
        $warnings = [];

        $output = $result->getOutput();
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Parse error lines (e.g., "ERROR: ...")
            if (stripos($line, 'ERROR') === 0 || stripos($line, '[ERROR]') !== false) {
                $errors[] = $this->extractMessage($line);
            }

            // Parse warning lines (e.g., "WARNING: ...")
            if (stripos($line, 'WARNING') === 0 || stripos($line, '[WARNING]') !== false) {
                $warnings[] = $this->extractMessage($line);
            }
        }

        // If process failed but no errors were parsed, add stderr as error
        if (!$result->isSuccess() && empty($errors)) {
            $errorMsg = $result->getErrorMessage();
            if (!empty($errorMsg)) {
                $errors[] = $errorMsg;
            }
        }

        $valid = $result->isSuccess() && empty($errors);

        return new ValidationResult(
            valid: $valid,
            errors: $errors,
            warnings: $warnings,
        );
    }

    /**
     * Extract message from log line
     *
     * @param string $line Log line
     * @return string Extracted message
     */
    private function extractMessage(string $line): string
    {
        // Remove common prefixes
        $patterns = [
            '/^\[?ERROR\]?:?\s*/i',
            '/^\[?WARNING\]?:?\s*/i',
            '/^\[?INFO\]?:?\s*/i',
        ];

        foreach ($patterns as $pattern) {
            $result = preg_replace($pattern, '', $line);
            if ($result !== null) {
                $line = $result;
            }
        }

        return trim($line);
    }

    /**
     * Sanitize command for logging (remove credentials)
     *
     * @param array<string> $command Command arguments
     * @return array<string> Sanitized command
     */
    private function sanitizeCommandForLogging(array $command): array
    {
        $sanitized = [];
        $redactNext = false;

        foreach ($command as $arg) {
            // If previous argument was a credential flag, redact this value
            if ($redactNext) {
                $sanitized[] = '[REDACTED]';
                $redactNext = false;

                continue;
            }

            // Check if this is a credential flag
            if (in_array($arg, ['-key', '-secret', '-apiKey', '-apiSecret'], true)) {
                $sanitized[] = $arg;
                $redactNext = true;

                continue;
            }

            $sanitized[] = $arg;
        }

        return $sanitized;
    }
}
