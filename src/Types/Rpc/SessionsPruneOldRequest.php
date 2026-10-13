<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Age threshold and options for pruning persisted sessions.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsPruneOldRequest implements Arrayable
{
    /**
     * @param  ?array<string>  $excludeSessionIds
     */
    public function __construct(
        public int $olderThanDays,
        public ?bool $dryRun = null,
        public ?bool $includeNamed = null,
        public ?array $excludeSessionIds = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            olderThanDays: $data['olderThanDays'],
            dryRun: $data['dryRun'] ?? null,
            includeNamed: $data['includeNamed'] ?? null,
            excludeSessionIds: $data['excludeSessionIds'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'olderThanDays' => $this->olderThanDays,
            'dryRun' => $this->dryRun,
            'includeNamed' => $this->includeNamed,
            'excludeSessionIds' => $this->excludeSessionIds,
        ], fn ($value): bool => $value !== null);
    }
}
