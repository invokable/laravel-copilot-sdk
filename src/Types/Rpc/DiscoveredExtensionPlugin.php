<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Installed plugin contributing a discovered extension.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class DiscoveredExtensionPlugin implements Arrayable
{
    public function __construct(
        public string $name,
        public ?string $id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'id' => $this->id,
        ], fn ($value) => $value !== null);
    }
}
