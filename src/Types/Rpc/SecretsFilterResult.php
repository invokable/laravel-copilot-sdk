<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SecretsFilterResult implements Arrayable
{
    public function __construct(
        public string $value,
        public bool $changed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            value: Arr::string($data, 'value'),
            changed: Arr::boolean($data, 'changed'),
        );
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'changed' => $this->changed,
        ];
    }
}
