<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A retrieved generated feed. Entries retain the feed's order.
 *
 * @experimental
 */
readonly class CatalogGetGeneratedFeedSucceeded implements Arrayable
{
    public string $kind;

    /**
     * @param  CatalogFeedEntry[]  $entries
     */
    public function __construct(
        public string $searchId,
        public CatalogGeneratedFeedDescription $feed,
        public array $entries,
        public CatalogNegotiatedContract $negotiated,
    ) {
        $this->kind = 'succeeded';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            searchId: $data['searchId'],
            feed: CatalogGeneratedFeedDescription::fromArray($data['feed']),
            entries: array_map(fn (array $entry) => CatalogFeedEntry::fromArray($entry), $data['entries']),
            negotiated: CatalogNegotiatedContract::fromArray($data['negotiated']),
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'searchId' => $this->searchId,
            'feed' => $this->feed->toArray(),
            'entries' => array_map(fn (CatalogFeedEntry $entry) => $entry->toArray(), $this->entries),
            'negotiated' => $this->negotiated->toArray(),
        ];
    }
}
