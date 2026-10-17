<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Opaque handle to a connection-owned Azure ML family mutation lease.
 *
 * @experimental
 */
readonly class AzureMlAcquireFamilyResult implements Arrayable
{
    public function __construct(
        public string $leaseId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(leaseId: $data['leaseId'] ?? '');
    }

    public function toArray(): array
    {
        return [
            'leaseId' => $this->leaseId,
        ];
    }
}
