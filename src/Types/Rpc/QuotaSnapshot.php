<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Quota snapshot for a specific type.
 */
readonly class QuotaSnapshot implements Arrayable
{
    public function __construct(
        /** Number of requests included in the entitlement */
        public int $entitlementRequests,
        /** Number of requests used so far this period */
        public int $usedRequests,
        /** Percentage of entitlement remaining */
        public float $remainingPercentage,
        /** Number of overage requests made this period */
        public int $overage,
        /** Whether pay-per-request usage is allowed when quota is exhausted */
        public bool $overageAllowedWithExhaustedQuota,
        /** Date when the quota resets (ISO 8601) */
        public ?string $resetDate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entitlementRequests: $data['entitlementRequests'],
            usedRequests: $data['usedRequests'],
            remainingPercentage: $data['remainingPercentage'],
            overage: $data['overage'],
            overageAllowedWithExhaustedQuota: $data['overageAllowedWithExhaustedQuota'],
            resetDate: $data['resetDate'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'entitlementRequests' => $this->entitlementRequests,
            'usedRequests' => $this->usedRequests,
            'remainingPercentage' => $this->remainingPercentage,
            'overage' => $this->overage,
            'overageAllowedWithExhaustedQuota' => $this->overageAllowedWithExhaustedQuota,
            'resetDate' => $this->resetDate,
        ], fn ($v) => $v !== null);
    }
}
