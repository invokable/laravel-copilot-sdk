<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ProviderMonthlyUsageScope;
use Revolution\Copilot\Enums\ProviderMonthlyUsageState;
use Revolution\Copilot\Enums\ProviderQuotaUnit;

/** Authoritative monthly usage reading from a quota provider. */
readonly class ProviderMonthlyUsage implements Arrayable
{
    public function __construct(
        public int|float|null $consumedQuantity = null,
        public ?string $cycleStart = null,
        public ?string $queriedAt = null,
        public ?string $resetOn = null,
        public ProviderMonthlyUsageScope|string $scope = ProviderMonthlyUsageScope::UNKNOWN,
        public ProviderMonthlyUsageState|string $state = ProviderMonthlyUsageState::UNKNOWN,
        public ProviderQuotaUnit|string $unit = ProviderQuotaUnit::UNKNOWN,
    ) {}

    public static function fromArray(array $data): self
    {
        $scope = $data['scope'] ?? 'unknown';
        $state = $data['state'] ?? 'unknown';
        $unit = $data['unit'] ?? 'unknown';

        return new self(
            consumedQuantity: isset($data['consumedQuantity']) ? self::number($data['consumedQuantity']) : null,
            cycleStart: $data['cycleStart'] ?? null,
            queriedAt: $data['queriedAt'] ?? null,
            resetOn: $data['resetOn'] ?? null,
            scope: ProviderMonthlyUsageScope::tryFrom($scope) ?? $scope,
            state: ProviderMonthlyUsageState::tryFrom($state) ?? $state,
            unit: ProviderQuotaUnit::tryFrom($unit) ?? $unit,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'consumedQuantity' => $this->consumedQuantity,
            'cycleStart' => $this->cycleStart,
            'queriedAt' => $this->queriedAt,
            'resetOn' => $this->resetOn,
            'scope' => $this->scope instanceof ProviderMonthlyUsageScope ? $this->scope->value : $this->scope,
            'state' => $this->state instanceof ProviderMonthlyUsageState ? $this->state->value : $this->state,
            'unit' => $this->unit instanceof ProviderQuotaUnit ? $this->unit->value : $this->unit,
        ], fn ($value) => $value !== null);
    }

    private static function number(mixed $value): int|float
    {
        return is_numeric($value) ? $value + 0 : 0;
    }
}
