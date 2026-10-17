<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AzureMlRegistry;

/**
 * Releases a family mutation lease owned by the current connection.
 *
 * @experimental
 */
readonly class AzureMlReleaseFamilyRequest implements Arrayable
{
    public function __construct(
        public string $leaseId,
        public AzureMlRegistry|string $registry,
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        $registry = $data['registry'] ?? '';

        return new self(
            leaseId: $data['leaseId'] ?? '',
            registry: is_string($registry) ? (AzureMlRegistry::tryFrom($registry) ?? $registry) : '',
            name: $data['name'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'leaseId' => $this->leaseId,
            'registry' => $this->registry instanceof AzureMlRegistry ? $this->registry->value : $this->registry,
            'name' => $this->name,
        ];
    }
}
