<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting account quota.
 */
readonly class AccountGetQuotaResult implements Arrayable
{
    /**
     * @param  array<string, QuotaSnapshot>  $quotaSnapshots  Quota snapshots keyed by type
     */
    public function __construct(
        public array $quotaSnapshots,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            quotaSnapshots: array_map(
                fn (array $snapshot) => QuotaSnapshot::fromArray($snapshot),
                $data['quotaSnapshots'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'quotaSnapshots' => array_map(
                fn (QuotaSnapshot $snapshot) => $snapshot->toArray(),
                $this->quotaSnapshots,
            ),
        ];
    }
}
