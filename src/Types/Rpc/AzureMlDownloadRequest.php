<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AzureMlRegistry;

/**
 * Starts a connection-owned Azure ML model download.
 *
 * @experimental
 */
readonly class AzureMlDownloadRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public AzureMlRegistry|string $registry,
        public string $name,
        public string $version,
        public string $destination,
        public ?string $leaseId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $registry = $data['registry'] ?? '';

        return new self(
            requestId: $data['requestId'] ?? '',
            registry: is_string($registry) ? (AzureMlRegistry::tryFrom($registry) ?? $registry) : '',
            name: $data['name'] ?? '',
            version: $data['version'] ?? '',
            destination: $data['destination'] ?? '',
            leaseId: $data['leaseId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'registry' => $this->registry instanceof AzureMlRegistry ? $this->registry->value : $this->registry,
            'name' => $this->name,
            'version' => $this->version,
            'destination' => $this->destination,
            'leaseId' => $this->leaseId,
        ], fn ($value) => $value !== null);
    }
}
