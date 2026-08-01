<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A point in session history a rewind can target.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryRewindPoint implements Arrayable
{
    /**
     * @param  string  $eventId  Event id the rewind would target.
     * @param  string  $userMessage  User message that began the turn.
     * @param  string  $timestamp  ISO 8601 timestamp of the turn.
     * @param  bool  $canRestoreFiles  Whether captured files can be restored for this point.
     * @param  int  $fileCount  Number of files changed by the discarded turns.
     * @param  bool  $turnChangedFiles  Whether the turn changed any files.
     * @param  int  $linesAdded  Total lines added by the discarded turns.
     * @param  int  $linesRemoved  Total lines removed by the discarded turns.
     * @param  bool  $isAutopilotContinuation  Whether the point is an autopilot continuation.
     */
    public function __construct(
        public string $eventId,
        public string $userMessage,
        public string $timestamp,
        public bool $canRestoreFiles,
        public int $fileCount,
        public bool $turnChangedFiles,
        public int $linesAdded,
        public int $linesRemoved,
        public bool $isAutopilotContinuation,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventId: Arr::string($data, 'eventId', ''),
            userMessage: Arr::string($data, 'userMessage', ''),
            timestamp: Arr::string($data, 'timestamp', ''),
            canRestoreFiles: Arr::boolean($data, 'canRestoreFiles', false),
            fileCount: Arr::integer($data, 'fileCount', 0),
            turnChangedFiles: Arr::boolean($data, 'turnChangedFiles', false),
            linesAdded: Arr::integer($data, 'linesAdded', 0),
            linesRemoved: Arr::integer($data, 'linesRemoved', 0),
            isAutopilotContinuation: Arr::boolean($data, 'isAutopilotContinuation', false),
        );
    }

    public function toArray(): array
    {
        return [
            'eventId' => $this->eventId,
            'userMessage' => $this->userMessage,
            'timestamp' => $this->timestamp,
            'canRestoreFiles' => $this->canRestoreFiles,
            'fileCount' => $this->fileCount,
            'turnChangedFiles' => $this->turnChangedFiles,
            'linesAdded' => $this->linesAdded,
            'linesRemoved' => $this->linesRemoved,
            'isAutopilotContinuation' => $this->isAutopilotContinuation,
        ];
    }
}
