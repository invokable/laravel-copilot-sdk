<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Snapshot of the session's pending queued items and immediate-steering messages.
 */
readonly class QueuePendingItemsResult implements Arrayable
{
    /**
     * @param  array<QueuePendingItems>  $items  Pending queued items in submission order
     * @param  array<string>  $steeringMessages  Display text for messages currently in the immediate steering queue
     */
    public function __construct(
        public array $items = [],
        public array $steeringMessages = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            items: array_map(
                fn (array $item) => QueuePendingItems::fromArray($item),
                $data['items'] ?? [],
            ),
            steeringMessages: Arr::array($data, 'steeringMessages', []),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(fn ($item) => $item->toArray(), $this->items),
            'steeringMessages' => $this->steeringMessages,
        ];
    }
}
