<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ModelProviderAdapterOperationDescriptor implements Arrayable
{
    public function __construct(
        public string $name,
        public mixed $inputSchema = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            inputSchema: $data['inputSchema'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'inputSchema' => $this->inputSchema,
        ], static fn ($value) => $value !== null);
    }
}
