<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Candidate whose card reference arrived inline.
 *
 * @experimental
 */
readonly class CatalogCandidateSourceEmbedded implements Arrayable
{
    /** @var string */
    public string $kind;

    public function __construct()
    {
        $this->kind = 'embedded';
    }

    public static function fromArray(array $data): static
    {
        return new static;
    }

    public function toArray(): array
    {
        return ['kind' => $this->kind];
    }
}
