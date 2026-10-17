<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to read output from a detached shell task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksReadOutputRequest implements Arrayable
{
    public function __construct(
        public string $id,
        public ?string $cursor = null,
        public ?int $waitMs = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id', ''),
            cursor: $data['cursor'] ?? null,
            waitMs: isset($data['waitMs']) ? (int) $data['waitMs'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'cursor' => $this->cursor,
            'waitMs' => $this->waitMs,
        ], fn ($value) => $value !== null);
    }
}
