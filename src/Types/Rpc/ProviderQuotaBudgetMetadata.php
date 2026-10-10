<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Authoritative quota budget measurements and policy metadata. */
readonly class ProviderQuotaBudgetMetadata implements Arrayable
{
    public function __construct(
        public int|float $consumed,
        public int|float $entitlement,
        public int|float $overage,
        public bool $overageAllowedWhenExhausted,
        public int|float|null $overageLimit = null,
        public int|float $remainingPercentage = 0,
        public int|float|null $resetAtEpochMs = null,
        public ?bool $resetEstimated = null,
        public ?bool $tokenBasedBilling = null,
        public bool $unlimited = false,
        public bool $usageAllowedWhenExhausted = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            consumed: self::number($data['consumed'] ?? 0),
            entitlement: self::number($data['entitlement'] ?? 0),
            overage: self::number($data['overage'] ?? 0),
            overageAllowedWhenExhausted: (bool) ($data['overageAllowedWhenExhausted'] ?? false),
            overageLimit: isset($data['overageLimit']) ? self::number($data['overageLimit']) : null,
            remainingPercentage: self::number($data['remainingPercentage'] ?? 0),
            resetAtEpochMs: isset($data['resetAtEpochMs']) ? self::number($data['resetAtEpochMs']) : null,
            resetEstimated: $data['resetEstimated'] ?? null,
            tokenBasedBilling: $data['tokenBasedBilling'] ?? null,
            unlimited: (bool) ($data['unlimited'] ?? false),
            usageAllowedWhenExhausted: (bool) ($data['usageAllowedWhenExhausted'] ?? false),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'consumed' => $this->consumed,
            'entitlement' => $this->entitlement,
            'overage' => $this->overage,
            'overageAllowedWhenExhausted' => $this->overageAllowedWhenExhausted,
            'overageLimit' => $this->overageLimit,
            'remainingPercentage' => $this->remainingPercentage,
            'resetAtEpochMs' => $this->resetAtEpochMs,
            'resetEstimated' => $this->resetEstimated,
            'tokenBasedBilling' => $this->tokenBasedBilling,
            'unlimited' => $this->unlimited,
            'usageAllowedWhenExhausted' => $this->usageAllowedWhenExhausted,
        ], fn ($value) => $value !== null);
    }

    private static function number(mixed $value): int|float
    {
        return is_numeric($value) ? $value + 0 : 0;
    }
}
