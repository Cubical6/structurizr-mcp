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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'views' => $this->views,
            'dsl' => $this->dsl,
            'createdAt' => $this->createdAt?->format('c'),
            'updatedAt' => $this->updatedAt?->format('c'),
        ];
    }

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
