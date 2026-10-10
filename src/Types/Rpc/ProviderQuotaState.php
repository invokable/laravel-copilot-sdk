<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ProviderQuotaAccessState;
use Revolution\Copilot\Enums\ProviderQuotaAcquisitionStatus;
use Revolution\Copilot\Enums\ProviderQuotaCapacityState;
use Revolution\Copilot\Enums\ProviderQuotaObservationKind;
use Revolution\Copilot\Enums\ProviderQuotaQuantityKind;
use Revolution\Copilot\Enums\ProviderQuotaUnit;

/** Provider-owned account quota observation; missing quantities are not zero. */
readonly class ProviderQuotaState implements Arrayable
{
    /**
     * @param  string[]  $presentFields  Fields explicitly present on the wire; used to preserve nullable observations.
     */
    public function __construct(
        public ProviderQuotaAccessState|string $accessState,
        public ProviderQuotaCapacityState|string $capacityState,
        public ModelProviderRef|array $provider,
        public string $quotaId,
        public ProviderQuotaUnit|string $unit,
        public ?string $acquisitionError = null,
        public ProviderQuotaAcquisitionStatus|string|null $acquisitionStatus = null,
        public int|float|null $availableQuantity = null,
        public ProviderQuotaBudgetMetadata|array|null $budgetMetadata = null,
        public ?string $compatibilityKey = null,
        public int|float|null $entitledQuantity = null,
        public ?bool $hasQuota = null,
        public ?int $httpStatus = null,
        public ProviderMonthlyUsage|array|null $monthlyUsage = null,
        public ProviderQuotaObservationKind|string|null $observationKind = null,
        public ?string $observedAt = null,
        public ProviderQuotaQuantityKind|string|null $quantityKind = null,
        public ?string $reason = null,
        public ?string $service = null,
        public ?string $source = null,
        private array $presentFields = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $accessState = $data['accessState'] ?? 'unknown';
        $capacityState = $data['capacityState'] ?? 'unknown';
        $unit = $data['unit'] ?? 'unknown';
        $acquisitionStatus = $data['acquisitionStatus'] ?? null;
        $observationKind = $data['observationKind'] ?? null;
        $quantityKind = $data['quantityKind'] ?? null;

        return new self(
            accessState: ProviderQuotaAccessState::tryFrom($accessState) ?? $accessState,
            capacityState: ProviderQuotaCapacityState::tryFrom($capacityState) ?? $capacityState,
            provider: isset($data['provider'])
                ? ($data['provider'] instanceof ModelProviderRef
                    ? $data['provider']
                    : ModelProviderRef::fromArray($data['provider']))
                : ModelProviderRef::fromArray([]),
            quotaId: $data['quotaId'] ?? '',
            unit: ProviderQuotaUnit::tryFrom($unit) ?? $unit,
            acquisitionError: $data['acquisitionError'] ?? null,
            acquisitionStatus: $acquisitionStatus === null
                ? null
                : (ProviderQuotaAcquisitionStatus::tryFrom($acquisitionStatus) ?? $acquisitionStatus),
            availableQuantity: array_key_exists('availableQuantity', $data) && $data['availableQuantity'] !== null
                ? self::number($data['availableQuantity'])
                : null,
            budgetMetadata: isset($data['budgetMetadata'])
                ? ($data['budgetMetadata'] instanceof ProviderQuotaBudgetMetadata
                    ? $data['budgetMetadata']
                    : ProviderQuotaBudgetMetadata::fromArray($data['budgetMetadata']))
                : null,
            compatibilityKey: $data['compatibilityKey'] ?? null,
            entitledQuantity: array_key_exists('entitledQuantity', $data) && $data['entitledQuantity'] !== null
                ? self::number($data['entitledQuantity'])
                : null,
            hasQuota: $data['hasQuota'] ?? null,
            httpStatus: isset($data['httpStatus']) ? (int) $data['httpStatus'] : null,
            monthlyUsage: isset($data['monthlyUsage'])
                ? ($data['monthlyUsage'] instanceof ProviderMonthlyUsage
                    ? $data['monthlyUsage']
                    : ProviderMonthlyUsage::fromArray($data['monthlyUsage']))
                : null,
            observationKind: $observationKind === null
                ? null
                : (ProviderQuotaObservationKind::tryFrom($observationKind) ?? $observationKind),
            observedAt: $data['observedAt'] ?? null,
            quantityKind: $quantityKind === null
                ? null
                : (ProviderQuotaQuantityKind::tryFrom($quantityKind) ?? $quantityKind),
            reason: $data['reason'] ?? null,
            service: $data['service'] ?? null,
            source: $data['source'] ?? null,
            presentFields: array_keys($data),
        );
    }

    public function toArray(): array
    {
        $data = [
            'accessState' => self::enumValue($this->accessState),
            'capacityState' => self::enumValue($this->capacityState),
            'provider' => $this->provider instanceof ModelProviderRef ? $this->provider->toArray() : $this->provider,
            'quotaId' => $this->quotaId,
            'unit' => self::enumValue($this->unit),
            'acquisitionError' => $this->acquisitionError,
            'acquisitionStatus' => $this->acquisitionStatus === null ? null : self::enumValue($this->acquisitionStatus),
            'availableQuantity' => $this->availableQuantity,
            'budgetMetadata' => $this->budgetMetadata instanceof ProviderQuotaBudgetMetadata
                ? $this->budgetMetadata->toArray()
                : $this->budgetMetadata,
            'compatibilityKey' => $this->compatibilityKey,
            'entitledQuantity' => $this->entitledQuantity,
            'hasQuota' => $this->hasQuota,
            'httpStatus' => $this->httpStatus,
            'monthlyUsage' => $this->monthlyUsage instanceof ProviderMonthlyUsage
                ? $this->monthlyUsage->toArray()
                : $this->monthlyUsage,
            'observationKind' => $this->observationKind === null ? null : self::enumValue($this->observationKind),
            'observedAt' => $this->observedAt,
            'quantityKind' => $this->quantityKind === null ? null : self::enumValue($this->quantityKind),
            'reason' => $this->reason,
            'service' => $this->service,
            'source' => $this->source,
        ];

        return array_filter(
            $data,
            fn ($value, $key) => $value !== null || in_array($key, $this->presentFields, true),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    private static function enumValue(\BackedEnum|string $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : $value;
    }

    private static function number(mixed $value): int|float
    {
        return is_numeric($value) ? $value + 0 : 0;
    }
}
