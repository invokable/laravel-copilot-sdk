<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Durable metadata describing who initiated a factory pause.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryPauseInfo implements Arrayable
{
    /**
     * @param  string  $type  Factory pause initiator discriminator ("user" or "checkpoint").
     * @param  ?string  $key  Stable author-defined checkpoint key that initiated the pause. Only present when `type` is "checkpoint".
     */
    public function __construct(
        public string $type,
        public ?string $key = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: Arr::string($data, 'type'),
            key: $data['key'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'key' => $this->key,
        ], fn ($value) => $value !== null);
    }
}
