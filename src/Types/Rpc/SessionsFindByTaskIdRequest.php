<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Look up a persisted session by its GitHub task ID.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsFindByTaskIdRequest implements Arrayable
{
    public function __construct(
        public string $taskId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(taskId: $data['taskId']);
    }

    public function toArray(): array
    {
        return ['taskId' => $this->taskId];
    }
}
