<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of starting fleet mode.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FleetStartResult implements Arrayable
{
    /**
     * @param  bool  $started  Whether fleet mode was successfully activated
     */
    public function __construct(
        public bool $started,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            started: Arr::boolean($data, 'started'),
        );
    }

    public function toArray(): array
    {
        return [
            'started' => $this->started,
        ];
    }
}
