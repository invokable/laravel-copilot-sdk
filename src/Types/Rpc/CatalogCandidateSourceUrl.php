<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Candidate whose card is retrieved from a URL.
 *
 * @experimental
 */
readonly class CatalogCandidateSourceUrl implements Arrayable
{
    /** @var string */
    public string $kind;

    public function __construct(
        public string $url,
    ) {
        $this->kind = 'url';
    }

    public static function fromArray(array $data): static
    {
        return new static(url: $data['url']);
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'url' => $this->url,
        ];
    }
}
