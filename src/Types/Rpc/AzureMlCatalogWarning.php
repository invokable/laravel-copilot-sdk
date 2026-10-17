<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AzureMlRegistry;

/**
 * Redacted warning for a skipped model or registry.
 *
 * @experimental
 */
readonly class AzureMlCatalogWarning implements Arrayable
{
    public function __construct(
        public AzureMlRegistry|string $registry,
        public string $modelName,
        public string $kind,
        public ?string $version = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $registry = $data['registry'];

        return new self(
            registry: is_string($registry) ? (AzureMlRegistry::tryFrom($registry) ?? $registry) : '',
            modelName: $data['modelName'],
            kind: $data['kind'],
            version: $data['version'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'registry' => $this->registry instanceof AzureMlRegistry ? $this->registry->value : $this->registry,
            'modelName' => $this->modelName,
            'version' => $this->version,
            'kind' => $this->kind,
        ], fn ($value) => $value !== null);
    }
}
