<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for completing a deferred idle drain.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class QueueFinishDeferredIdleDrainRequest implements Arrayable
{
    /**
     * @param  bool  $activeBackgroundWork  Whether background work is still active.
     * @param  bool  $hasPending  Whether native queued work remains.
     */
    public function __construct(
        public bool $activeBackgroundWork,
        public bool $hasPending,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            activeBackgroundWork: Arr::boolean($data, 'activeBackgroundWork', false),
            hasPending: Arr::boolean($data, 'hasPending', false),
        );
    }

    public function toArray(): array
    {
        return [
            'activeBackgroundWork' => $this->activeBackgroundWork,
            'hasPending' => $this->hasPending,
        ];
    }
}
