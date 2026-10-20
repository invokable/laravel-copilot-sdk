<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SecretsAddFilterValuesResult implements Arrayable
{
    public function __construct(
        public bool $ok,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ok: Arr::boolean($data, 'ok'),
        );
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
        ];
    }
}
