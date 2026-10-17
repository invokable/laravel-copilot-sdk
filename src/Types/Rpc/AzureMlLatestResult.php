<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AzureMlRegistry;

/**
 * Latest eligible Azure ML model version and provider metadata.
 *
 * @experimental
 */
readonly class AzureMlLatestResult implements Arrayable
{
    public function __construct(
        public AzureMlRegistry|string $registry,
        public string $name,
        public string $version,
        public AzureMlProfile $profile,
        public mixed $metadata,
    ) {}

    public static function fromArray(array $data): self
    {
        $registry = $data['registry'] ?? '';

        return new self(
            registry: is_string($registry) ? (AzureMlRegistry::tryFrom($registry) ?? $registry) : '',
            name: $data['name'] ?? '',
            version: $data['version'] ?? '',
            profile: AzureMlProfile::fromArray($data['profile']),
            metadata: $data['metadata'],
        );
    }

    public function toArray(): array
    {
        return [
            'registry' => $this->registry instanceof AzureMlRegistry ? $this->registry->value : $this->registry,
            'name' => $this->name,
            'version' => $this->version,
            'profile' => $this->profile->toArray(),
            'metadata' => $this->metadata,
        ];
    }
}
