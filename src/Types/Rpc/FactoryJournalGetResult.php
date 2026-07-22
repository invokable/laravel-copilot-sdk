<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of reading a factory journal entry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryJournalGetResult implements Arrayable
{
    /**
     * @param  bool  $hit  Whether the journal contained the requested key.
     * @param  mixed  $resultJson  Cached JSON result. The hit field distinguishes a cached JSON null from a miss.
     */
    public function __construct(
        public bool $hit,
        public mixed $resultJson = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hit: Arr::boolean($data, 'hit'),
            resultJson: $data['resultJson'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'hit' => $this->hit,
            'resultJson' => $this->resultJson,
        ], fn ($v) => $v !== null);
    }
}
