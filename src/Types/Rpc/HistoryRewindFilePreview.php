<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HistoryRewindChangeType;

/**
 * Aggregate file change represented by a rewind preview.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryRewindFilePreview implements Arrayable
{
    /**
     * @param  string  $path  Path of the affected file.
     * @param  HistoryRewindChangeType|string  $changeType  How the discarded turns changed the file.
     * @param  int  $linesAdded  Lines added by the discarded turns.
     * @param  int  $linesRemoved  Lines removed by the discarded turns.
     */
    public function __construct(
        public string $path,
        public HistoryRewindChangeType|string $changeType,
        public int $linesAdded,
        public int $linesRemoved,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: Arr::string($data, 'path', ''),
            changeType: $data['changeType'] instanceof HistoryRewindChangeType
                ? $data['changeType']
                : HistoryRewindChangeType::from($data['changeType']),
            linesAdded: Arr::integer($data, 'linesAdded', 0),
            linesRemoved: Arr::integer($data, 'linesRemoved', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'changeType' => $this->changeType instanceof HistoryRewindChangeType ? $this->changeType->value : $this->changeType,
            'linesAdded' => $this->linesAdded,
            'linesRemoved' => $this->linesRemoved,
        ];
    }
}
