<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

/**
 * A versioned, bounded trust observation carried unchanged with a catalog candidate and its
 * private handle context. Current observations require a recognised T1/T2 tier; every
 * non-current state structurally forbids a tier. Eligibility remains `unknown` while Agent
 * Finder supplies no exposure decision.
 *
 * @experimental
 */
readonly class CatalogTrustSnapshot
{
    /**
     * Create the appropriate variant instance based on the `status` discriminator field.
     */
    public static function fromArray(array $data): CatalogTrustSnapshotCurrent|CatalogTrustSnapshotAbsent|CatalogTrustSnapshotStale|CatalogTrustSnapshotDowngraded|CatalogTrustSnapshotRevoked|CatalogTrustSnapshotUnsupported|CatalogTrustSnapshotMalformed
    {
        return match ($data['status'] ?? null) {
            'current' => CatalogTrustSnapshotCurrent::fromArray($data),
            'absent' => CatalogTrustSnapshotAbsent::fromArray($data),
            'stale' => CatalogTrustSnapshotStale::fromArray($data),
            'downgraded' => CatalogTrustSnapshotDowngraded::fromArray($data),
            'revoked' => CatalogTrustSnapshotRevoked::fromArray($data),
            'unsupported' => CatalogTrustSnapshotUnsupported::fromArray($data),
            default => CatalogTrustSnapshotMalformed::fromArray($data),
        };
    }
}
