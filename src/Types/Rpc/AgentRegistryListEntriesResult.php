<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Live agent metadata without connection credentials.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentRegistryListEntriesResult implements Arrayable
{
    /**
     * @param  AgentRegistryLiveTargetEntry[]  $entries
     */
    public function __construct(
        public array $entries,
        public ?AgentRegistryError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entries: array_map(
                fn (array $entry) => AgentRegistryLiveTargetEntry::fromArray($entry),
                $data['entries'] ?? [],
            ),
            error: isset($data['error']) ? AgentRegistryError::fromArray($data['error']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'entries' => array_map(fn (AgentRegistryLiveTargetEntry $entry) => $entry->toArray(), $this->entries),
            'error' => $this->error?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
