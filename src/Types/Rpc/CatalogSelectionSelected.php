<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A selected catalog candidate transferred into a fresh install handle.
 *
 * @experimental
 */
readonly class CatalogSelectionSelected implements Arrayable
{
    public function __construct(
        public string $candidateHandle,
        public string $searchId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            candidateHandle: $data['candidateHandle'],
            searchId: $data['searchId'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => 'selected',
            'candidateHandle' => $this->candidateHandle,
            'searchId' => $this->searchId,
        ];
    }
}
