<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Find persisted session candidates by friendly name.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsFindByNameRequest implements Arrayable
{
    public function __construct(
        public string $name,
        public ?string $configDir = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            configDir: $data['configDir'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'configDir' => $this->configDir,
        ], fn ($value): bool => $value !== null);
    }
}
