<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for collecting a session's debug logs.
 *
 * @experimental
 */
readonly class DebugCollectLogsRequest implements Arrayable
{
    /**
     * @param  DebugCollectLogsEntry[]|array[]|null  $additionalEntries
     */
    public function __construct(
        public DebugCollectLogsDestination|array $destination,
        public DebugCollectLogsInclude|array|null $include = null,
        public ?array $additionalEntries = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            destination: DebugCollectLogsDestination::fromArray($data['destination']),
            include: isset($data['include']) ? DebugCollectLogsInclude::fromArray($data['include']) : null,
            additionalEntries: isset($data['additionalEntries'])
                ? array_map(
                    fn (array $entry) => DebugCollectLogsEntry::fromArray($entry),
                    $data['additionalEntries'],
                )
                : null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'destination' => $this->destination instanceof DebugCollectLogsDestination
                ? $this->destination->toArray()
                : DebugCollectLogsDestination::fromArray($this->destination)->toArray(),
        ];

        if ($this->include !== null) {
            $include = $this->include instanceof DebugCollectLogsInclude
                ? $this->include
                : DebugCollectLogsInclude::fromArray($this->include);
            $data['include'] = $include->toArray();
        }

        if ($this->additionalEntries !== null) {
            $data['additionalEntries'] = array_map(
                fn (DebugCollectLogsEntry|array $entry) => $entry instanceof DebugCollectLogsEntry
                    ? $entry->toArray()
                    : DebugCollectLogsEntry::fromArray($entry)->toArray(),
                $this->additionalEntries,
            );
        }

        return $data;
    }
}
