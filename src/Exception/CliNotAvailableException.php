<?php

declare(strict_types=1);

namespace StructurizrMcp\Exception;

/**
 * Exception thrown when no CLI executor is available
 *
 * Provides helpful installation instructions for users.
 */
class CliNotAvailableException extends StructurizrException
{
    /** @var array<string, string> */
    private array $installationInstructions;

    /**
     * @param array<string, string> $instructions Custom installation instructions (optional)
     */
    public function __construct(array $instructions = [])
    {
        $this->installationInstructions = $instructions ?: self::getDefaultInstructions();

        $message = "Structurizr CLI is not available.\n\n" .
                   "Installation options:\n" .
                   $this->formatInstructions();

        parent::__construct($message);
    }

    /**
     * Get installation instructions
     *
     * @return array<string, string>
     */
    public function getInstallationInstructions(): array
    {
        return $this->installationInstructions;
    }

    /**
     * Get default installation instructions
     *
     * @return array<string, string>
     */
    private static function getDefaultInstructions(): array
    {
        return [
            'Docker (recommended)' => 'docker pull structurizr/cli:latest',
            'macOS (Homebrew)' => 'brew install structurizr-cli',
            'Windows (Scoop)' => 'scoop bucket add extras && scoop install structurizr-cli',
            'Manual' => 'Download from https://github.com/structurizr/cli/releases (requires Java 17+)',
        ];
    }

    private function formatInstructions(): string
    {
        $lines = [];
        foreach ($this->installationInstructions as $method => $command) {
            $lines[] = "  {$method}:\n    {$command}";
        }

        return implode("\n\n", $lines);
    }
}
