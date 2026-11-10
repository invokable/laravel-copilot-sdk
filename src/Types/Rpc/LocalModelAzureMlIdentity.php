<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Exact registry identity of a downloadable model.
 *
 * @experimental
 */
readonly class LocalModelAzureMlIdentity implements Arrayable
{
    public function __construct(
        public string $registry,
        public string $name,
        public string $version,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            registry: $data['registry'] ?? '',
            name: $data['name'] ?? '',
            version: $data['version'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'registry' => $this->registry,
            'name' => $this->name,
            'version' => $this->version,
        ];
    }
}
