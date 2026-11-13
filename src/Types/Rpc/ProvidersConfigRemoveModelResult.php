<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class ProvidersConfigRemoveModelResult implements Arrayable
{
    public function __construct(public bool $providerRemoved) {}

    public static function fromArray(array $data): self
    {
        return new self(providerRemoved: Arr::boolean($data, 'providerRemoved'));
    }

    public function toArray(): array
    {
        return ['providerRemoved' => $this->providerRemoved];
    }
}
