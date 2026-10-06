<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Checkpoint number to read.
 *
 * @experimental
 */
readonly class WorkspacesReadCheckpointRequest implements Arrayable
{
    public function __construct(
        public int $number,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(number: Arr::integer($data, 'number'));
    }

    public function toArray(): array
    {
        return ['number' => $this->number];
    }
}
