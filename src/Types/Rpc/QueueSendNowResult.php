<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether the queued item was steered into the active turn.
 */
readonly class QueueSendNowResult implements Arrayable
{
    /**
     * @param  bool  $steered  Whether the queued item was steered into the active turn.
     */
    public function __construct(
        public bool $steered,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            steered: Arr::boolean($data, 'steered', false),
        );
    }

    public function toArray(): array
    {
        return [
            'steered' => $this->steered,
        ];
    }
}
