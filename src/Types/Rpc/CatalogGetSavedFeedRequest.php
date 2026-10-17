<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogTrustTier;

/**
 * Requests one saved catalog feed.
 *
 * @experimental
 */
readonly class CatalogGetSavedFeedRequest implements Arrayable
{
    /**
     * @param  CatalogClientContract  $contract  Requires catalog-feeds and catalog-search-credential-required. Session binding also requires catalog-search-session-bound.
     * @param  string  $feedId  Opaque saved feed ID. Treat it as an indivisible string.
     * @param  string|null  $policySessionId  Existing attached session to bind to; the runtime does not create or resume sessions.
     * @param  CatalogTrustTier[]|string[]|null  $trustTiers  Return resources at these tiers, retaining filtered entries in place.
     */
    public function __construct(
        public CatalogClientContract $contract,
        public string $feedId,
        public ?string $policySessionId = null,
        public ?array $trustTiers = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            feedId: $data['feedId'],
            policySessionId: $data['policySessionId'] ?? null,
            trustTiers: isset($data['trustTiers'])
                ? array_map(fn (string $tier) => CatalogTrustTier::from($tier), $data['trustTiers'])
                : null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'contract' => $this->contract->toArray(),
            'feedId' => $this->feedId,
        ];

        if ($this->policySessionId !== null) {
            $data['policySessionId'] = $this->policySessionId;
        }
        if ($this->trustTiers !== null) {
            $data['trustTiers'] = array_map(
                fn (CatalogTrustTier|string $tier) => $tier instanceof CatalogTrustTier ? $tier->value : $tier,
                $this->trustTiers,
            );
        }

        return $data;
    }
}
