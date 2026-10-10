<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** External tool calls still waiting for a result. */
readonly class PendingExternalToolRequestList implements Arrayable
{
    /** @param array<PendingExternalToolRequest> $items */
    public function __construct(
        public array $items = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            items: array_map(
                static fn (PendingExternalToolRequest|array $item) => $item instanceof PendingExternalToolRequest
                    ? $item
                    : PendingExternalToolRequest::fromArray($item),
                $data['items'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                static fn (PendingExternalToolRequest $item) => $item->toArray(),
                $this->items,
            ),
        ];
    }
}
