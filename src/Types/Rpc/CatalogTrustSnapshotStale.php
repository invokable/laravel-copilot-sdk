<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogTrustEligibility;

/**
 * Discriminator: the authority explicitly marked its assessment stale.
 *
 * @experimental
 */
readonly class CatalogTrustSnapshotStale implements Arrayable
{
    public string $schemaVersion;

    public string $status;

    public function __construct(
        public CatalogTrustEligibility $eligibility,
        public CatalogTrustProvenance $provenance,
    ) {
        $this->schemaVersion = 'v1';
        $this->status = 'stale';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            eligibility: CatalogTrustEligibility::from($data['eligibility']),
            provenance: CatalogTrustProvenance::fromArray($data['provenance'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'status' => $this->status,
            'eligibility' => $this->eligibility->value,
            'provenance' => $this->provenance->toArray(),
        ];
    }
}
