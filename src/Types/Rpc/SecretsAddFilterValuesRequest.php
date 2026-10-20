<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** @experimental */
readonly class SecretsAddFilterValuesRequest implements Arrayable
{
    /**
     * @param  array<int, string>  $values
     */
    public function __construct(
        public array $values,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            values: $data['values'],
        );
    }

    public function toArray(): array
    {
        return [
            'values' => $this->values,
        ];
    }
}
