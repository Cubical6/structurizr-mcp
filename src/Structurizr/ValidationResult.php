<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr;

/**
 * Data Transfer Object for DSL validation results
 */
class ValidationResult
{
    /**
     * @param bool $valid Whether the DSL is valid
     * @param array<string> $errors List of validation errors
     * @param array<string> $warnings List of validation warnings
     */
    public function __construct(
        private readonly bool $valid,
        private readonly array $errors = [],
        private readonly array $warnings = []
    ) {
    }

    /**
     * Check if the validation passed
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Get all validation errors
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get all validation warnings
     *
     * @return array<string>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Check if there are any errors
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Check if there are any warnings
     */
    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    /**
     * Get warning count
     */
    public function getWarningCount(): int
    {
        return count($this->warnings);
    }

    /**
     * Get a formatted summary of the validation result
     */
    public function getSummary(): string
    {
        if ($this->valid && !$this->hasWarnings()) {
            return 'Validation successful';
        }

        $parts = [];

        if (!$this->valid) {
            $parts[] = sprintf('%d error(s)', $this->getErrorCount());
        }

        if ($this->hasWarnings()) {
            $parts[] = sprintf('%d warning(s)', $this->getWarningCount());
        }

        return 'Validation ' . ($this->valid ? 'successful' : 'failed') . ' with ' . implode(' and ', $parts);
    }

    /**
     * Get all messages (errors and warnings combined)
     *
     * @return array<string>
     */
    public function getAllMessages(): array
    {
        return array_merge(
            array_map(fn ($error) => 'ERROR: ' . $error, $this->errors),
            array_map(fn ($warning) => 'WARNING: ' . $warning, $this->warnings)
        );
    }
}
