<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for re-arming a self-paced scheduled prompt.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleRearmSelfPacedRequest implements Arrayable
{
    /**
     * @param  int  $id  Identifier of the self-paced scheduled prompt.
     * @param  int  $at  Epoch milliseconds when the prompt should next fire.
     */
    public function __construct(
        public int $id,
        public int $at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::integer($data, 'id'),
            at: Arr::integer($data, 'at'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'at' => $this->at,
        ];
    }
}
