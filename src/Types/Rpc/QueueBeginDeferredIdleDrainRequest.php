<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for beginning a deferred idle drain of the send queue.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class QueueBeginDeferredIdleDrainRequest implements Arrayable
{
    /**
     * @param  bool  $activeBackgroundWork  Whether background work is still active.
     */
    public function __construct(
        public bool $activeBackgroundWork,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            activeBackgroundWork: Arr::boolean($data, 'activeBackgroundWork', false),
        );
    }

    public function toArray(): array
    {
        return [
            'activeBackgroundWork' => $this->activeBackgroundWork,
        ];
    }
}
