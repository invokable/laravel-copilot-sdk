<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether a queued item's text was updated.
 */
readonly class QueueUpdateTextResult implements Arrayable
{
    /**
     * @param  bool  $updated  Whether the queued item's text was updated.
     */
    public function __construct(
        public bool $updated,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            updated: Arr::boolean($data, 'updated', false),
        );
    }

    public function toArray(): array
    {
        return [
            'updated' => $this->updated,
        ];
    }
}
