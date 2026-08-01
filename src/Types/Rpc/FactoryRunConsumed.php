<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Resources consumed by a factory run so far.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryRunConsumed implements Arrayable
{
    /**
     * @param  int  $activeMs  Accumulated active execution time, in milliseconds.
     * @param  int  $subagents  Total number of subagents admitted so far.
     * @param  int  $nanoAiu  Accumulated AI-credit charge, in nano AI units.
     */
    public function __construct(
        public int $activeMs,
        public int $subagents,
        public int $nanoAiu,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            activeMs: Arr::integer($data, 'activeMs', 0),
            subagents: Arr::integer($data, 'subagents', 0),
            nanoAiu: Arr::integer($data, 'nanoAiu', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'activeMs' => $this->activeMs,
            'subagents' => $this->subagents,
            'nanoAiu' => $this->nanoAiu,
        ];
    }
}
