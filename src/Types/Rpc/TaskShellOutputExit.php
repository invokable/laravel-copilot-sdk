<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\TaskStatus;

/**
 * Exit information for a detached shell task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskShellOutputExit implements Arrayable
{
    public function __construct(
        public TaskStatus $status,
        public ?int $exitCode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: TaskStatus::from($data['status'] ?? 'running'),
            exitCode: isset($data['exitCode']) ? (int) $data['exitCode'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status->value,
            'exitCode' => $this->exitCode,
        ], fn ($value) => $value !== null);
    }
}
