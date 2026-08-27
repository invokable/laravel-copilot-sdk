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
     * @param  ?int  $inFlightSteeringCount  Leading steering messages already folded into the running turn
     */
    public function __construct(
        public array $items = [],
        public array $steeringMessages = [],
        public ?int $inFlightSteeringCount = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            items: array_map(
                fn (array $item) => QueuePendingItems::fromArray($item),
                $data['items'] ?? [],
            ),
            steeringMessages: Arr::array($data, 'steeringMessages', []),
            inFlightSteeringCount: isset($data['inFlightSteeringCount']) ? (int) $data['inFlightSteeringCount'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'items' => array_map(fn ($item) => $item->toArray(), $this->items),
            'steeringMessages' => $this->steeringMessages,
            'inFlightSteeringCount' => $this->inFlightSteeringCount,
        ], fn ($value) => $value !== null);
    }
}
