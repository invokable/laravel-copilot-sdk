<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A successful catalog search result.
 *
 * @experimental
 */
readonly class CatalogSearchSucceeded implements Arrayable
{
    /** @var string */
    public string $kind;

    /**
     * @param  CatalogMcpServerCandidate[]|CatalogAiSkillCandidate[]  $candidates
     */
    public function __construct(
        public string $searchId,
        public array $candidates,
        public bool $truncated,
        public CatalogNegotiatedContract $negotiated,
    ) {
        $this->kind = 'succeeded';
    }

    public static function fromArray(array $data): static
    {
        $candidates = array_map(
            fn (array $c) => ($c['kind'] ?? '') === 'mcp-server'
                ? CatalogMcpServerCandidate::fromArray($c)
                : CatalogAiSkillCandidate::fromArray($c),
            $data['candidates'] ?? [],
        );

        return new static(
            searchId: $data['searchId'],
            candidates: $candidates,
            truncated: (bool) ($data['truncated'] ?? false),
            negotiated: CatalogNegotiatedContract::fromArray($data['negotiated']),
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'searchId' => $this->searchId,
            'candidates' => array_map(fn ($c) => $c->toArray(), $this->candidates),
            'truncated' => $this->truncated,
            'negotiated' => $this->negotiated->toArray(),
        ];
    }
}
