<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Quota snapshot for a specific type.
 */
readonly class QuotaSnapshot implements Arrayable
{
    /**
     * @param  int  $entitlementRequests  Number of requests included in the entitlement
     * @param  int  $usedRequests  Number of requests used so far this period
     * @param  float  $remainingPercentage  Percentage of entitlement remaining
     * @param  float  $overage  Number of overage requests made this period
     * @param  bool  $overageAllowedWithExhaustedQuota  Whether pay-per-request usage is allowed when quota is exhausted
     * @param  bool  $isUnlimitedEntitlement  Whether the user has an unlimited usage entitlement
     * @param  bool  $usageAllowedWithExhaustedQuota  Whether usage is still permitted after quota exhaustion
     * @param  ?string  $resetDate  Date when the quota resets (ISO 8601)
     */
    public function __construct(
        public int $entitlementRequests,
        public int $usedRequests,
        public float $remainingPercentage,
        public float $overage,
        public bool $overageAllowedWithExhaustedQuota,
        public bool $isUnlimitedEntitlement = false,
        public bool $usageAllowedWithExhaustedQuota = false,
        public ?string $resetDate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entitlementRequests: Arr::integer($data, 'entitlementRequests'),
            usedRequests: Arr::integer($data, 'usedRequests'),
            remainingPercentage: Arr::float($data, 'remainingPercentage'),
            overage: is_int($data['overage'] ?? null) ? (float) Arr::integer($data, 'overage') : Arr::float($data, 'overage'),
            overageAllowedWithExhaustedQuota: Arr::boolean($data, 'overageAllowedWithExhaustedQuota'),
            isUnlimitedEntitlement: Arr::boolean($data, 'isUnlimitedEntitlement', false),
            usageAllowedWithExhaustedQuota: Arr::boolean($data, 'usageAllowedWithExhaustedQuota', false),
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
            'isUnlimitedEntitlement' => $this->isUnlimitedEntitlement,
            'usageAllowedWithExhaustedQuota' => $this->usageAllowedWithExhaustedQuota,
            'resetDate' => $this->resetDate,
        ], fn ($v) => $v !== null);
    }
}
