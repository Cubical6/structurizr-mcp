<?php

declare(strict_types=1);

namespace StructurizrMcp\Structurizr;

/**
 * Value object representing a Structurizr workspace
 */
class Workspace
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly array $model = [],
        public readonly array $views = [],
        public readonly string $dsl = '',
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {
    }

    /**
     * Convert workspace to array representation
     *
     * @return array<string, mixed> Workspace data as associative array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'views' => $this->views,
            'dsl' => $this->dsl,
            'createdAt' => $this->createdAt?->format('Y-m-d\TH:i:s.uP'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d\TH:i:s.uP'),
        ];
    }

    /**
     * Create workspace from array representation
     *
     * @param array<string, mixed> $data Workspace data as associative array
     * @return self New workspace instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            description: $data['description'] ?? '',
            model: $data['model'] ?? [],
            views: $data['views'] ?? [],
            dsl: $data['dsl'] ?? '',
            createdAt: isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
            updatedAt: isset($data['updatedAt']) ? new \DateTimeImmutable($data['updatedAt']) : null,
        );
    }

    /**
     * Create a new workspace instance with updated DSL
     *
     * Returns a new workspace instance with the DSL updated and the updatedAt timestamp refreshed.
     * This maintains immutability of the workspace object.
     *
     * @param string $dsl The new DSL content
     * @return self New workspace instance with updated DSL
     */
    public function withDsl(string $dsl): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            description: $this->description,
            model: $this->model,
            views: $this->views,
            dsl: $dsl,
            createdAt: $this->createdAt,
            updatedAt: new \DateTimeImmutable(),
        );
    }

    /**
     * Create a new workspace instance with updated model
     *
     * Returns a new workspace instance with the model updated and the updatedAt timestamp refreshed.
     * This maintains immutability of the workspace object.
     *
     * @param array<string, mixed> $model The new model data
     * @return self New workspace instance with updated model
     */
    public function withModel(array $model): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            description: $this->description,
            model: $model,
            views: $this->views,
            dsl: $this->dsl,
            createdAt: $this->createdAt,
            updatedAt: new \DateTimeImmutable(),
        );
    }

    /**
     * Create a new workspace instance with updated views
     *
     * Returns a new workspace instance with the views updated and the updatedAt timestamp refreshed.
     * This maintains immutability of the workspace object.
     *
     * @param array<string, mixed> $views The new views data
     * @return self New workspace instance with updated views
     */
    public function withViews(array $views): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            description: $this->description,
            model: $this->model,
            views: $views,
            dsl: $this->dsl,
            createdAt: $this->createdAt,
            updatedAt: new \DateTimeImmutable(),
        );
    }
}
