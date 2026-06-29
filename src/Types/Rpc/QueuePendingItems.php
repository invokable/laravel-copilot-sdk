<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\QueuePendingItemsKind;

/**
 * A single pending queued item.
 */
readonly class QueuePendingItems implements Arrayable
{
    /**
     * @param  string  $displayText  Human-readable text to display for this queue entry in the UI
     * @param  QueuePendingItemsKind  $kind  Whether this item is a queued user message or a queued slash command / model change
     */
    public function __construct(
        public string $displayText,
        public QueuePendingItemsKind $kind,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            displayText: Arr::string($data, 'displayText', ''),
            kind: QueuePendingItemsKind::from($data['kind'] ?? QueuePendingItemsKind::Message->value),
        );
    }

    public function toArray(): array
    {
        return [
            'displayText' => $this->displayText,
            'kind' => $this->kind->value,
        ];
    }
}
