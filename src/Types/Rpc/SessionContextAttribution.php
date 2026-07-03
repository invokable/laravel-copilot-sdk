<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Per-source context-window attribution, or null if the session has not yet been initialized.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionContextAttribution implements Arrayable
{
    /**
     * @param  int  $totalTokens  Total token count of the current context window. Divide an entry's `tokens` by this to derive its share.
     * @param  SessionContextAttributionEntry[]  $entries  Flat list of per-source attribution entries. Group by `kind`; nesting via `parentId`.
     * @param  array{count: int}  $compactions  Successful compaction history for the session.
     */
    public function __construct(
        public int $totalTokens,
        public array $entries,
        public array $compactions,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalTokens: Arr::integer($data, 'totalTokens', 0),
            entries: array_map(
                fn (array $entry) => SessionContextAttributionEntry::fromArray($entry),
                $data['entries'] ?? [],
            ),
            compactions: $data['compactions'] ?? ['count' => 0],
        );
    }

    public function toArray(): array
    {
        return [
            'totalTokens' => $this->totalTokens,
            'entries' => array_map(fn ($e) => $e->toArray(), $this->entries),
            'compactions' => $this->compactions,
        ];
    }
}
