<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * List of pending permission requests.
 */
readonly class PendingPermissionRequestList implements Arrayable
{
    /**
     * @param  list<PendingPermissionRequest>  $items  Pending permission requests
     */
    public function __construct(
        public array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            items: array_map(
                static fn (array $item): PendingPermissionRequest => PendingPermissionRequest::fromArray($item),
                $data['items'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(
                static fn (PendingPermissionRequest $item): array => $item->toArray(),
                $this->items,
            ),
        ];
    }
}
