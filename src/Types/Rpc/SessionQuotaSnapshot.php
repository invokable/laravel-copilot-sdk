<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Legacy-compatible quota snapshot within a session quota projection. */
readonly class SessionQuotaSnapshot implements Arrayable
{
    public function __construct(
        public bool $isUnlimitedEntitlement = false,
        public int|float $entitlementRequests = 0,
        public int|float $usedRequests = 0,
        public bool $usageAllowedWithExhaustedQuota = false,
        public int|float $overage = 0,
        public bool $overageAllowedWithExhaustedQuota = false,
        public int|float $remainingPercentage = 0,
        public ?int $resetDateEpochMs = null,
        public ?bool $resetDateEstimated = null,
        public ?bool $hasQuota = null,
        public ?bool $tokenBasedBilling = null,
        public int|float|null $overageEntitlement = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            isUnlimitedEntitlement: (bool) ($data['isUnlimitedEntitlement'] ?? false),
            entitlementRequests: self::number($data['entitlementRequests'] ?? 0),
            usedRequests: self::number($data['usedRequests'] ?? 0),
            usageAllowedWithExhaustedQuota: (bool) ($data['usageAllowedWithExhaustedQuota'] ?? false),
            overage: self::number($data['overage'] ?? 0),
            overageAllowedWithExhaustedQuota: (bool) ($data['overageAllowedWithExhaustedQuota'] ?? false),
            remainingPercentage: self::number($data['remainingPercentage'] ?? 0),
            resetDateEpochMs: isset($data['resetDateEpochMs']) ? (int) $data['resetDateEpochMs'] : null,
            resetDateEstimated: $data['resetDateEstimated'] ?? null,
            hasQuota: $data['hasQuota'] ?? null,
            tokenBasedBilling: $data['tokenBasedBilling'] ?? null,
            overageEntitlement: isset($data['overageEntitlement']) ? self::number($data['overageEntitlement']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'isUnlimitedEntitlement' => $this->isUnlimitedEntitlement,
            'entitlementRequests' => $this->entitlementRequests,
            'usedRequests' => $this->usedRequests,
            'usageAllowedWithExhaustedQuota' => $this->usageAllowedWithExhaustedQuota,
            'overage' => $this->overage,
            'overageAllowedWithExhaustedQuota' => $this->overageAllowedWithExhaustedQuota,
            'remainingPercentage' => $this->remainingPercentage,
            'resetDateEpochMs' => $this->resetDateEpochMs,
            'resetDateEstimated' => $this->resetDateEstimated,
            'hasQuota' => $this->hasQuota,
            'tokenBasedBilling' => $this->tokenBasedBilling,
            'overageEntitlement' => $this->overageEntitlement,
        ], fn ($value) => $value !== null);
    }

    private static function number(mixed $value): int|float
    {
        return is_numeric($value) ? $value + 0 : 0;
    }
}
