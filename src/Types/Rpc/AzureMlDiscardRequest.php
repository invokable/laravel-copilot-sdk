<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Selects an inactive Azure ML version for cleanup.
 *
 * @experimental
 */
readonly class AzureMlDiscardRequest implements Arrayable
{
    public function __construct(
        public string $destination,
        public ?string $leaseId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            destination: $data['destination'] ?? '',
            leaseId: $data['leaseId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'destination' => $this->destination,
            'leaseId' => $this->leaseId,
        ], fn ($value) => $value !== null);
    }
}
