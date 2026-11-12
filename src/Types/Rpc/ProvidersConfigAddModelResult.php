<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class ProvidersConfigAddModelResult implements Arrayable
{
    public function __construct(
        public bool $providerAdded,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            providerAdded: Arr::boolean($data, 'providerAdded'),
        );
    }

    public function toArray(): array
    {
        return ['providerAdded' => $this->providerAdded];
    }
}
