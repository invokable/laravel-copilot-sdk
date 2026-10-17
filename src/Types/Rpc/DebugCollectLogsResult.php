<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\DebugCollectLogsResultKind;

/**
 * Result of collecting a session's debug logs.
 *
 * @experimental
 */
readonly class DebugCollectLogsResult implements Arrayable
{
    /**
     * @param  DebugCollectLogsCollectedEntry[]  $entries
     * @param  DebugCollectLogsSkippedEntry[]|null  $skippedEntries
     */
    public function __construct(
        public DebugCollectLogsResultKind|string $kind,
        public string $path,
        public array $entries,
        public ?array $skippedEntries = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $kind = $data['kind'];

        return new self(
            kind: is_string($kind) ? DebugCollectLogsResultKind::tryFrom($kind) ?? $kind : $kind,
            path: $data['path'],
            entries: array_map(
                fn (array $entry) => DebugCollectLogsCollectedEntry::fromArray($entry),
                $data['entries'],
            ),
            skippedEntries: isset($data['skippedEntries'])
                ? array_map(
                    fn (array $entry) => DebugCollectLogsSkippedEntry::fromArray($entry),
                    $data['skippedEntries'],
                )
                : null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'kind' => $this->kind instanceof DebugCollectLogsResultKind ? $this->kind->value : $this->kind,
            'path' => $this->path,
            'entries' => array_map(
                fn (DebugCollectLogsCollectedEntry $entry) => $entry->toArray(),
                $this->entries,
            ),
        ];

        if ($this->skippedEntries !== null) {
            $data['skippedEntries'] = array_map(
                fn (DebugCollectLogsSkippedEntry $entry) => $entry->toArray(),
                $this->skippedEntries,
            );
        }

        return $data;
    }
}
