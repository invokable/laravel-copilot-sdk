<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\CatalogTrustEligibility;
use Revolution\Copilot\Enums\CatalogTrustTier;

/**
 * A recognised current Agent Finder T1 or T2 trust tier.
 *
 * @experimental
 */
readonly class CatalogTrustSnapshotCurrent implements Arrayable
{
    public string $schemaVersion;

    public string $status;

    public function __construct(
        public CatalogTrustTier $tier,
        public CatalogTrustEligibility $eligibility,
        public CatalogTrustProvenance $provenance,
    ) {
        $this->schemaVersion = 'v1';
        $this->status = 'current';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            tier: CatalogTrustTier::from(Arr::string($data, 'tier')),
            eligibility: CatalogTrustEligibility::from(Arr::string($data, 'eligibility')),
            provenance: CatalogTrustProvenance::fromArray($data['provenance'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'status' => $this->status,
            'tier' => $this->tier->value,
            'eligibility' => $this->eligibility->value,
            'provenance' => $this->provenance->toArray(),
        ];
    }
}
