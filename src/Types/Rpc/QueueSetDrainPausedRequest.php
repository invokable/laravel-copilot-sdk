<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for acquiring or releasing the queued-lane drain pause.
 */
readonly class QueueSetDrainPausedRequest implements Arrayable
{
    /**
     * @param  bool  $paused  Whether to acquire or release the queue drain pause.
     */
    public function __construct(
        public bool $paused,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            paused: Arr::boolean($data, 'paused', false),
        );
    }

    public function toArray(): array
    {
        return [
            'paused' => $this->paused,
        ];
    }
}
