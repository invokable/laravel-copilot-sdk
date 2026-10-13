<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for immediately sending a queued item.
 */
readonly class QueueSendNowRequest implements Arrayable
{
    /**
     * @param  string  $id  Identifier of the queued item to send now.
     */
    public function __construct(
        public string $id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
