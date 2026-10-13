<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Timestamped display line from task progress or recent activity.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskProgressLine implements Arrayable
{
    public function __construct(
        public string $message,
        public string $timestamp,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: Arr::string($data, 'message', ''),
            timestamp: Arr::string($data, 'timestamp', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'timestamp' => $this->timestamp,
        ];
    }
}
