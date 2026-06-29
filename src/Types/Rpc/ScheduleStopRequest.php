<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Identifier of the scheduled prompt to remove.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleStopRequest implements Arrayable
{
    /**
     * @param  int  $id  Id of the scheduled prompt to remove
     */
    public function __construct(
        public int $id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::integer($data, 'id', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
