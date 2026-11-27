<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * An available context tier for model selection.
 *
 * @experimental
 */
readonly class ModelContextTierOption implements Arrayable
{
    public function __construct(
        public string $value,
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            value: Arr::string($data, 'value'),
            name: Arr::string($data, 'name'),
        );
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'name' => $this->name,
        ];
    }
}
