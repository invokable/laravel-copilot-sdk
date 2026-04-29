<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of promoting a task to background mode.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksPromoteToBackgroundResult implements Arrayable
{
    /**
     * @param  bool  $promoted  Whether the task was successfully promoted to background mode
     */
    public function __construct(
        public bool $promoted,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            promoted: (bool) ($data['promoted'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'promoted' => $this->promoted,
        ];
    }
}
