<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A non-selected or error result from catalog.select.
 *
 * Unrecognized result fields are retained to support protocol evolution.
 *
 * @experimental
 */
readonly class CatalogSelectionResult implements Arrayable
{
    public array $data;

    public function __construct(
        public string $kind,
        array $data = [],
    ) {
        unset($data['kind']);
        $this->data = $data;
    }

    public static function fromArray(array $data): static
    {
        $kind = (string) ($data['kind'] ?? '');
        unset($data['kind']);

        return new static($kind, $data);
    }

    public function toArray(): array
    {
        return array_merge(['kind' => $this->kind], $this->data);
    }
}
