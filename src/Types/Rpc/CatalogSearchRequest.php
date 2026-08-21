<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A bounded catalog search request.
 *
 * @experimental
 */
readonly class CatalogSearchRequest implements Arrayable
{
    /**
     * @param  CatalogClientContract  $contract  The protocol version and capability set the caller requires.
     * @param  string  $query  Free-text search query.
     * @param  int|null  $limit  Maximum number of candidates to return. Defaults to 10 when omitted.
     * @param  string[]|null  $kinds  Restrict results to these candidate kinds.
     */
    public function __construct(
        public CatalogClientContract $contract,
        public string $query,
        public ?int $limit = null,
        public ?array $kinds = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            query: $data['query'],
            limit: isset($data['limit']) ? (int) $data['limit'] : null,
            kinds: $data['kinds'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'contract' => $this->contract->toArray(),
            'query' => $this->query,
        ];
        if ($this->limit !== null) {
            $arr['limit'] = $this->limit;
        }
        if ($this->kinds !== null) {
            $arr['kinds'] = $this->kinds;
        }

        return $arr;
    }
}
