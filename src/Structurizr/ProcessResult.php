<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr;

/**
 * Data Transfer Object for CLI process execution results
 */
class ProcessResult
{
    /**
     * @param int $exitCode Process exit code (0 = success)
     * @param string $stdout Standard output from the process
     * @param string $stderr Standard error output from the process
     * @param bool $success Whether the process executed successfully
     */
    public function __construct(
        private readonly int $exitCode,
        private readonly string $stdout,
        private readonly string $stderr,
        private readonly bool $success
    ) {
    }

    /**
     * Get the process exit code
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Get the standard output
     */
    public function getStdout(): string
    {
        return $this->stdout;
    }

    /**
     * Get the standard error output
     */
    public function getStderr(): string
    {
        return $this->stderr;
    }

    /**
     * Check if the process was successful
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get combined output (stdout + stderr)
     */
    public function getOutput(): string
    {
        $output = trim($this->stdout);
        if (!empty($this->stderr)) {
            $output .= "\n" . trim($this->stderr);
        }
        return $output;
    }

    /**
     * Get error message if process failed
     */
    public function getErrorMessage(): string
    {
        if ($this->success) {
            return '';
        }

        return !empty($this->stderr) ? $this->stderr : $this->stdout;
    }
}
