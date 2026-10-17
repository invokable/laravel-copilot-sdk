<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AzureMlRegistry;

/**
 * Selects an immutable Azure ML model version for local use.
 *
 * @experimental
 */
readonly class AzureMlResolveRequest implements Arrayable
{
    public function __construct(
        public AzureMlRegistry|string $registry,
        public string $name,
        public string $version,
    ) {}

    public static function fromArray(array $data): self
    {
        $registry = $data['registry'] ?? '';

        return new self(
            registry: is_string($registry) ? (AzureMlRegistry::tryFrom($registry) ?? $registry) : '',
            name: $data['name'] ?? '',
            version: $data['version'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'registry' => $this->registry instanceof AzureMlRegistry ? $this->registry->value : $this->registry,
            'name' => $this->name,
            'version' => $this->version,
        ];
    }
}
