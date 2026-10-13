<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of interrupting the current main agent turn.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class InterruptMainTurnResult implements Arrayable
{
    public function __construct(
        public bool $interrupted,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            interrupted: Arr::boolean($data, 'interrupted', false),
        );
    }

    public function toArray(): array
    {
        return [
            'interrupted' => $this->interrupted,
        ];
    }
}
