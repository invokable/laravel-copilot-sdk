<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Atomic patch for client-owned session metadata. Operations apply in clear, remove,
 * then set order. The resulting bag must satisfy the ClientMetadata entry and
 * serialized-size limits.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataUpdateClientMetadataRequest implements Arrayable
{
    /**
     * @param  ?bool  $clear  Remove every existing client metadata entry before applying remove and set. Defaults to false.
     * @param  ?array  $remove  Case-sensitive keys to remove. Missing keys are ignored.
     * @param  ?array  $set  String entries to add or replace. Set wins when a key also appears in remove.
     */
    public function __construct(
        public ?bool $clear = null,
        public ?array $remove = null,
        public ?array $set = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            clear: Arr::get($data, 'clear'),
            remove: $data['remove'] ?? null,
            set: $data['set'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'clear' => $this->clear,
            'remove' => $this->remove,
            'set' => $this->set,
        ], fn ($value) => $value !== null);
    }
}
