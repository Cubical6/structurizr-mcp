<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr;

/**
 * Interface for CLI wrapper implementations
 *
 * Defines the contract for Structurizr CLI operations.
 * Implemented by CliWrapper (real CLI) and NullCliWrapper (graceful degradation).
 */
interface CliWrapperInterface
{
    /**
     * Validate a Structurizr DSL file
     *
     * @param string $dslPath Path to the DSL file
     * @return ValidationResult
     */
    public function validate(string $dslPath): ValidationResult;

    /**
     * Export workspace to various formats
     *
     * @param string $workspacePath Path to workspace (DSL or JSON)
     * @param string $format Export format (plantuml, mermaid, dot, ilograph, json, dsl, etc.)
     * @param string|null $outputPath Optional output path (if null, returns to stdout)
     * @return string Exported content or path to output file
     */
    public function export(string $workspacePath, string $format, ?string $outputPath = null): string;

    /**
     * Push workspace to Structurizr cloud/on-premises
     *
     * @param string $workspacePath Path to workspace DSL file
     * @param int $workspaceId Structurizr workspace ID
     * @param string $apiKey Structurizr API key
     * @param string $apiSecret Structurizr API secret
     * @param string|null $apiUrl Optional API URL
     * @return ProcessResult
     */
    public function push(
        string $workspacePath,
        int $workspaceId,
        string $apiKey,
        string $apiSecret,
        ?string $apiUrl = null,
    ): ProcessResult;

    /**
     * Pull workspace from Structurizr cloud/on-premises
     *
     * @param int $workspaceId Structurizr workspace ID
     * @param string $apiKey Structurizr API key
     * @param string $apiSecret Structurizr API secret
     * @param string $outputPath Path to save the workspace JSON
     * @param string|null $apiUrl Optional API URL
     * @return ProcessResult
     */
    public function pull(
        int $workspaceId,
        string $apiKey,
        string $apiSecret,
        string $outputPath,
        ?string $apiUrl = null,
    ): ProcessResult;

    /**
     * Get CLI version information
     *
     * @return string Version string
     */
    public function getVersion(): string;
}
