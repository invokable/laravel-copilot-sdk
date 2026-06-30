<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Host-driven completion items for the current composer input.
 * Empty when the host returns no items or does not support completions.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CompletionsRequestResult implements Arrayable
{
    /**
     * @param  SessionCompletionItem[]  $items  Completion items in host-ranked order.
     */
    public function __construct(
        public array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn (array $item) => SessionCompletionItem::fromArray($item),
            Arr::array($data, 'items'),
        );

        return new self(items: $items);
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(fn (SessionCompletionItem $item) => $item->toArray(), $this->items),
        ];
    }
}
