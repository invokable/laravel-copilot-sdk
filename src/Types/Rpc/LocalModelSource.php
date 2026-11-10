<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Catalog source and its display order.
 *
 * @experimental
 */
readonly class LocalModelSource implements Arrayable
{
    public function __construct(
        public string $id,
        public string $label,
        public int $ordering,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            label: $data['label'] ?? '',
            ordering: (int) ($data['ordering'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'ordering' => $this->ordering,
        ];
    }
}
