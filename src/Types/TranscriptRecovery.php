<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Transcript repair details returned when resuming a session. */
readonly class TranscriptRecovery implements Arrayable
{
    /**
     * @param  string  $plannedBackupPath  Path where the transcript backup will be written on the next append.
     * @param  array<int>  $invalidLineNumbers  One-based physical line numbers removed from the transcript.
     */
    public function __construct(
        public string $plannedBackupPath,
        public array $invalidLineNumbers = [],
        public bool $sessionStartMoved = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plannedBackupPath: Arr::string($data, 'plannedBackupPath', ''),
            invalidLineNumbers: Arr::array($data, 'invalidLineNumbers', []),
            sessionStartMoved: Arr::boolean($data, 'sessionStartMoved', false),
        );
    }

    public function toArray(): array
    {
        return [
            'plannedBackupPath' => $this->plannedBackupPath,
            'invalidLineNumbers' => $this->invalidLineNumbers,
            'sessionStartMoved' => $this->sessionStartMoved,
        ];
    }
}
